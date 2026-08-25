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
     * Fetch products exclusively from Dropi API
     * (POST https://api.dropi.co/integrations/products/index with dropi-integration-key header)
     */
    public function getProducts(
        int $perPage = 24,
        int $currentPage = 0,
        string $search = '',
        string $orderBy = 'id',
        string $order = 'DESC',
        ?string $categoryFilter = null
    ): array {
        $tokenRecord = $this->getActiveToken();

        if (!$tokenRecord || empty($tokenRecord->token)) {
            return [
                'success' => false,
                'source' => 'no_token',
                'products' => [],
                'total' => 0,
                'message' => 'No tienes un Token JWT de Dropi configurado. Por favor ve a "Configuración Dropi" y pega tu token.',
            ];
        }

        $token = trim($tokenRecord->token);
        $storeName = $tokenRecord->store ?: 'Tienda 1';
        
        // Exact official endpoint from Dropify
        $endpoint = "https://api.dropi.co/integrations/products/index";

        $postData = [
            'startData' => $currentPage,
            'pageSize' => $perPage,
            'order_type' => $order,
            'order_by' => $orderBy,
            'keywords' => $search,
            'active' => true,
            'no_count' => true,
            'integration' => true,
            'get_stock' => false,
        ];

        if (!empty($categoryFilter) && $categoryFilter !== 'all') {
            $postData['category'] = $categoryFilter;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json;charset=UTF-8',
                'dropi-integration-key' => $token,
                'User-Agent' => 'Dropify/1.0',
            ])
            ->timeout(12)
            ->post($endpoint, $postData);

            $json = $response->json();

            if ($response->successful() && !empty($json['isSuccess']) && isset($json['objects'])) {
                $objects = is_array($json['objects']) ? $json['objects'] : [];
                $normalized = $this->normalizeDropiApiProducts($objects, $storeName);

                return [
                    'success' => true,
                    'source' => 'api_live',
                    'products' => $normalized,
                    'total' => count($normalized),
                    'message' => 'Productos sincronizados en tiempo real con la API de Dropi.',
                ];
            } else {
                $errorMessage = $json['message'] ?? ($json['error'] ?? "Respuesta HTTP {$response->status()} de la API de Dropi.");
                
                return [
                    'success' => false,
                    'source' => 'api_error',
                    'products' => [],
                    'total' => 0,
                    'message' => "Dropi API: {$errorMessage}",
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Dropi API connection exception: ' . $e->getMessage());
            
            return [
                'success' => false,
                'source' => 'connection_error',
                'products' => [],
                'total' => 0,
                'message' => 'No se pudo conectar con el servidor de Dropi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get single product data directly from Dropi API (GET products/v2/{id})
     */
    public function getProduct(string|int $id, ?string $token = null): ?array
    {
        if (empty($token)) {
            $tokenRecord = $this->getActiveToken();
            $token = $tokenRecord ? trim($tokenRecord->token) : '';
        }

        if (empty($token)) {
            return null;
        }

        $endpoint = "https://api.dropi.co/integrations/products/v2/{$id}";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json;charset=UTF-8',
                'dropi-integration-key' => $token,
                'User-Agent' => 'Dropify/1.0',
            ])
            ->timeout(10)
            ->get($endpoint);

            if ($response->successful()) {
                $json = $response->json();
                if (!empty($json['isSuccess']) && !empty($json['objects'])) {
                    return (array) $json['objects'];
                }
            }
        } catch (\Exception $e) {
            Log::warning("Error fetching single Dropi product #{$id}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Import a single product from Dropi into NovaStore (1-Click)
     */
    public function importProductById(string|int $dropiId, ?float $customPrice = null, ?int $categoryId = null): array
    {
        $tokenRecord = $this->getActiveToken();
        $token = $tokenRecord ? trim($tokenRecord->token) : '';
        $storeName = $tokenRecord ? $tokenRecord->store : 'Tienda 1';

        // Fetch real product data from Dropi API
        $dropiProduct = $this->getProduct($dropiId, $token);

        if (!$dropiProduct) {
            return [
                'success' => false,
                'message' => "No se pudo obtener la información del producto Dropi #{$dropiId} desde la API.",
            ];
        }

        $name = $dropiProduct['name'] ?? 'Producto Dropi';
        $wholesale = (float) ($dropiProduct['price'] ?? ($dropiProduct['wholesale_price'] ?? 0));
        $suggested = (float) ($dropiProduct['suggested_price'] ?? ($wholesale * 1.5));
        $salePrice = $customPrice && $customPrice > 0 ? $customPrice : ($suggested > 0 ? $suggested : ($wholesale * 1.5));
        $comparePrice = $salePrice > $wholesale ? round($salePrice * 1.25) : null;
        $profit = max(0, $salePrice - $wholesale);

        $stock = (int) ($dropiProduct['stock'] ?? 25);
        $description = $dropiProduct['description'] ?? '';
        $shortDesc = Str::limit(strip_tags($description), 180);

        // Images handling
        $images = [];
        if (!empty($dropiProduct['photos']) && is_array($dropiProduct['photos'])) {
            foreach ($dropiProduct['photos'] as $p) {
                $url = is_array($p) ? ($p['urlS3'] ?? ($p['url'] ?? '')) : (is_object($p) ? ($p->urlS3 ?? ($p->url ?? '')) : (string)$p);
                if (!empty($url)) {
                    $images[] = Str::startsWith($url, 'http') ? $url : "https://dropi.co/{$url}";
                }
            }
        } elseif (!empty($dropiProduct['image'])) {
            $images[] = $dropiProduct['image'];
        }

        $mainImage = $images[0] ?? 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800';

        // Resolve Category
        if (!$categoryId) {
            $categoryName = 'Tecnología';
            if (!empty($dropiProduct['categories'][0])) {
                $categoryName = is_array($dropiProduct['categories'][0]) ? ($dropiProduct['categories'][0]['name'] ?? 'General') : (is_object($dropiProduct['categories'][0]) ? ($dropiProduct['categories'][0]->name ?? 'General') : 'General');
            } elseif (!empty($dropiProduct['category'])) {
                $categoryName = $dropiProduct['category'];
            }

            $cat = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'is_active' => true]
            );
            $categoryId = $cat->id;
        }

        // Check if already in store
        $product = Product::where('dropi_id', (string)$dropiId)->first();

        if ($product) {
            $product->update([
                'name' => $name,
                'price' => $salePrice,
                'compare_price' => $comparePrice,
                'wholesale_price' => $wholesale,
                'profit_margin' => $profit,
                'suggested_price' => $suggested,
                'stock' => $stock,
                'description' => $description,
                'short_description' => $shortDesc,
                'image' => $mainImage,
                'images' => $images ?: [$mainImage],
                'category_id' => $categoryId,
                'dropi_store' => $storeName,
                'is_dropi_product' => true,
                'is_dropshipping' => true,
                'is_active' => true,
            ]);

            $this->notifyDropiImport($dropiId, $product->id, $token);

            return [
                'success' => true,
                'action' => 'updated',
                'product' => $product,
                'message' => "¡Producto '{$product->name}' (Dropi ID #{$dropiId}) actualizado con éxito en tu tienda!",
            ];
        }

        // Create new product
        $sku = !empty($dropiProduct['sku']) ? $dropiProduct['sku'] : ('DRP-' . strtoupper(substr(md5($dropiId), 0, 6)));
        $product = Product::create([
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(4),
            'sku' => $sku,
            'dropi_id' => (string)$dropiId,
            'dropi_store' => $storeName,
            'is_dropi_product' => true,
            'is_dropshipping' => true,
            'short_description' => $shortDesc,
            'description' => $description,
            'price' => $salePrice,
            'compare_price' => $comparePrice,
            'wholesale_price' => $wholesale,
            'profit_margin' => $profit,
            'suggested_price' => $suggested,
            'stock' => $stock,
            'image' => $mainImage,
            'images' => $images ?: [$mainImage],
            'is_active' => true,
        ]);

        $this->notifyDropiImport($dropiId, $product->id, $token);

        return [
            'success' => true,
            'action' => 'created',
            'product' => $product,
            'message' => "¡Producto '{$product->name}' importado con éxito desde Dropi a tu tienda!",
        ];
    }

    /**
     * Notify Dropi that product was imported into the store
     */
    protected function notifyDropiImport(string|int $dropiId, int $localProductId, string $token): void
    {
        if (empty($token)) return;

        try {
            Http::withHeaders([
                'Content-Type' => 'application/json;charset=UTF-8',
                'dropi-integration-key' => $token,
            ])
            ->timeout(5)
            ->put("https://api.dropi.co/integrations/importlist/importstore/1", [
                'products_id' => $dropiId,
                'imported_to_store' => true,
                'woocomerse_id' => $localProductId,
                'woocomerse_url' => url("/product/{$localProductId}"),
            ]);
        } catch (\Exception $e) {
            // Silently ignore
        }
    }

    /**
     * Bulk import all products returned from Dropi API
     */
    public function importAll(): array
    {
        $res = $this->getProducts(50);
        $products = $res['products'] ?? [];
        $importedCount = 0;

        foreach ($products as $item) {
            $r = $this->importProductById($item['id']);
            if (!empty($r['success'])) {
                $importedCount++;
            }
        }

        return [
            'success' => true,
            'imported_count' => $importedCount,
            'total' => count($products),
            'message' => "¡Se han importado {$importedCount} productos de Dropi a tu tienda exitosamente!",
        ];
    }

    /**
     * Normalize Dropi API objects into standard array format
     */
    protected function normalizeDropiApiProducts(array $objects, string $storeName): array
    {
        $normalized = [];
        foreach ($objects as $obj) {
            $item = (array) $obj;
            $id = (string) ($item['id'] ?? Str::random(6));
            $name = $item['name'] ?? 'Producto Dropi';
            $wholesale = (float) ($item['price'] ?? ($item['wholesale_price'] ?? 0));
            $suggested = (float) ($item['suggested_price'] ?? ($wholesale * 1.5));
            $stock = (int) ($item['stock'] ?? 20);

            $img = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800';
            if (!empty($item['photos']) && is_array($item['photos'])) {
                $p = (array) $item['photos'][0];
                $img = $p['urlS3'] ?? ($p['url'] ?? $img);
                if (!Str::startsWith($img, 'http')) {
                    $img = "https://dropi.co/{$img}";
                }
            }

            $category = 'General';
            if (!empty($item['categories']) && is_array($item['categories'])) {
                $c = (array) $item['categories'][0];
                $category = $c['name'] ?? 'General';
            }

            $normalized[] = [
                'id' => $id,
                'name' => $name,
                'category' => $category,
                'wholesale_price' => $wholesale,
                'suggested_price' => $suggested,
                'stock' => $stock,
                'image' => $img,
                'description' => $item['description'] ?? '',
                'store' => $storeName,
            ];
        }
        return $normalized;
    }
}
