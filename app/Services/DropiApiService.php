<?php

namespace App\Services;

use App\Models\Category;
use App\Models\DropiToken;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DropiApiService
{
    /**
     * Get active token record from database
     */
    public function getActiveToken(): ?DropiToken
    {
        return DropiToken::where('is_valid', true)->latest()->first() ?: DropiToken::latest()->first();
    }

    /**
     * Fetch products from Dropi API using stored token
     */
    public function fetchDropiProducts(string $search = '', int $page = 1, int $pageSize = 20): array
    {
        $tokenRecord = $this->getActiveToken();

        if (!$tokenRecord || empty($tokenRecord->token)) {
            return [
                'success' => false,
                'message' => 'No hay ningún Token de Dropi configurado. Por favor ingresa a "Configuración Dropi" y guarda tu token.',
                'products' => [],
                'total' => 0,
            ];
        }

        $apiUrl = rtrim($tokenRecord->api_url ?: 'https://api.dropi.co/api/', '/');
        $token = trim($tokenRecord->token);

        try {
            $response = Http::withToken($token)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-Dropi-Store' => $tokenRecord->store,
                ])
                ->timeout(12)
                ->get("{$apiUrl}/products", array_filter([
                    'search' => $search ?: null,
                    'page' => $page,
                    'pageSize' => $pageSize,
                ]));

            if ($response->successful()) {
                $json = $response->json();
                $items = $json['data'] ?? ($json['products'] ?? (is_array($json) && isset($json[0]) ? $json : []));
                $total = $json['total'] ?? count($items);

                return [
                    'success' => true,
                    'message' => 'Productos obtenidos exitosamente desde la API de Dropi.',
                    'products' => $items,
                    'total' => $total,
                ];
            } else {
                $errorMsg = $response->json('message') ?: "Error HTTP {$response->status()} al consultar la API de Dropi.";
                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'products' => [],
                    'total' => 0,
                    'status_code' => $response->status(),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Dropi API connection exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'No se pudo conectar con el servidor de Dropi: ' . $e->getMessage(),
                'products' => [],
                'total' => 0,
            ];
        }
    }

    /**
     * Import a product from Dropi into NovaStore
     */
    public function importProduct(array $data): array
    {
        $tokenRecord = $this->getActiveToken();
        $storeName = $data['store'] ?? ($tokenRecord?->store ?? 'Tienda 1');

        $dropiId = (string) ($data['product'] ?? ($data['dropi_id'] ?? Str::random(8)));
        $name = trim($data['sob_nombre'] ?? ($data['product_name'] ?? ($data['name'] ?? 'Producto Dropi')));
        $price = (float) ($data['sob_precio'] ?? ($data['product_price'] ?? ($data['price'] ?? 0)));
        $wholesalePrice = (float) ($data['wholesale_price'] ?? ($price * 0.6));
        $suggestedPrice = (float) ($data['suggested_price'] ?? ($price * 1.3));
        $comparePrice = !empty($data['compare_price']) ? (float) $data['compare_price'] : ($suggestedPrice > $price ? $suggestedPrice : null);
        $stock = isset($data['sob_stock']) ? (int) $data['sob_stock'] : (isset($data['stock']) ? (int) $data['stock'] : 20);
        $description = $data['sob_descripcion'] ?? ($data['product_description'] ?? ($data['description'] ?? ''));
        $shortDescription = $data['short_description'] ?? Str::limit(strip_tags($description), 200);

        // Images handling
        $images = $data['sob_images'] ?? ($data['images'] ?? []);
        if (is_string($images)) {
            $decoded = json_decode($images, true);
            $images = is_array($decoded) ? $decoded : [$images];
        }
        $mainImage = !empty($images[0]) ? $images[0] : ($data['image'] ?? 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800');

        // Category handling
        $categoryId = $data['category_id'] ?? null;
        if (!$categoryId) {
            $catName = $data['category_name'] ?? 'General';
            $cat = Category::firstOrCreate(
                ['slug' => Str::slug($catName)],
                ['name' => $catName, 'is_active' => true]
            );
            $categoryId = $cat->id;
        }

        // Find if already imported
        $product = Product::where('dropi_id', $dropiId)->first();

        if ($product) {
            $product->update([
                'name' => $name,
                'price' => $price,
                'compare_price' => $comparePrice,
                'wholesale_price' => $wholesalePrice,
                'profit_margin' => max(0, $price - $wholesalePrice),
                'suggested_price' => $suggestedPrice,
                'stock' => $stock,
                'description' => $description,
                'short_description' => $shortDescription,
                'image' => $mainImage,
                'images' => $images,
                'category_id' => $categoryId,
                'dropi_store' => $storeName,
                'is_dropi_product' => true,
                'is_dropshipping' => true,
            ]);

            return [
                'success' => true,
                'action' => 'updated',
                'product' => $product,
                'message' => "¡Producto '{$product->name}' (Dropi ID #{$dropiId}) actualizado correctamente en tu tienda!",
            ];
        }

        // Create new product
        $sku = 'DRP-' . strtoupper(Str::random(6));
        $product = Product::create([
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(4),
            'sku' => $sku,
            'dropi_id' => $dropiId,
            'dropi_store' => $storeName,
            'is_dropi_product' => true,
            'is_dropshipping' => true,
            'short_description' => $shortDescription,
            'description' => $description,
            'price' => $price,
            'compare_price' => $comparePrice,
            'wholesale_price' => $wholesalePrice,
            'profit_margin' => max(0, $price - $wholesalePrice),
            'suggested_price' => $suggestedPrice,
            'stock' => $stock,
            'image' => $mainImage,
            'images' => $images ?: [$mainImage],
            'is_active' => true,
        ]);

        return [
            'success' => true,
            'action' => 'created',
            'product' => $product,
            'message' => "¡Producto '{$product->name}' importado exitosamente desde Dropi con precio " . number_format($price, 0, ',', '.') . " COP!",
        ];
    }
}
