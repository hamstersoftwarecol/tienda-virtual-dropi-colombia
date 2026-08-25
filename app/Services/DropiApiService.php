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
     * Generate WordPress & WooCommerce headers to emulate native Dropify plugin requests
     */
    public function getWordPressHeaders(string $token): array
    {
        $tokenRecord = $this->getActiveToken();
        $storeUrl = !empty($tokenRecord?->integration_url) ? $tokenRecord->integration_url : 'https://tienda.hamstersoftware.com';
        
        if (!Str::startsWith($storeUrl, 'http')) {
            $storeUrl = 'https://' . ltrim($storeUrl, '/');
        }

        $cleanToken = trim($token);

        return [
            'User-Agent' => "WordPress/6.6.1; {$storeUrl}",
            'Referer' => "{$storeUrl}/wp-admin/admin.php?page=dropi-products",
            'Origin' => $storeUrl,
            'Content-Type' => 'application/json;charset=UTF-8',
            'dropi-integration-key' => $cleanToken,
            'Authorization' => 'Bearer ' . $cleanToken,
            'token' => $cleanToken,
            'Accept' => 'application/json, text/plain, */*',
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }

    /**
     * Fetch products directly from Dropi API with WooCommerce headers & fallbacks
     */
    public function getProducts(
        int $perPage = 32,
        int $currentPage = 0,
        ?string $search = '',
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
        $cleanSearch = trim((string)$search);

        $endpoints = [
            "https://api.dropi.co/integrations/products/index",
            "https://api-v2.dropi.co/integrations/products/index",
            "https://api.dropi.co/api/products/index",
            "https://api-v2.dropi.co/api/products/index",
        ];

        $postData = [
            'startData' => $currentPage,
            'pageSize' => $perPage,
            'order_type' => $order,
            'order_by' => $orderBy,
            'keywords' => $cleanSearch,
            'active' => true,
            'integration' => true,
        ];

        if (!empty($categoryFilter) && $categoryFilter !== 'all') {
            $postData['category'] = $categoryFilter;
        }

        $lastError = 'No se pudo obtener respuesta de la API de Dropi.';

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders($this->getWordPressHeaders($token))
                    ->timeout(25)
                    ->connectTimeout(8)
                    ->post($endpoint, $postData);

                if ($response->successful()) {
                    $json = $response->json();
                    $objects = $json['objects'] ?? ($json['data'] ?? ($json['products'] ?? ($json['result'] ?? [])));

                    if (is_array($objects) && count($objects) > 0) {
                        $normalized = $this->normalizeDropiApiProducts($objects, $storeName);

                        return [
                            'success' => true,
                            'source' => 'api_live',
                            'endpoint' => $endpoint,
                            'products' => $normalized,
                            'total' => count($normalized),
                            'message' => empty($normalized) && !empty($cleanSearch) 
                                ? "No se encontraron productos en Dropi con la palabra clave '{$cleanSearch}'."
                                : 'Productos sincronizados en tiempo real con la API oficial de Dropi Colombia.',
                        ];
                    } elseif (isset($json['isSuccess']) && $json['isSuccess'] === true) {
                        return [
                            'success' => true,
                            'source' => 'api_live',
                            'endpoint' => $endpoint,
                            'products' => [],
                            'total' => 0,
                            'message' => !empty($cleanSearch)
                                ? "No se encontraron productos en Dropi con la palabra clave '{$cleanSearch}'."
                                : 'No hay productos disponibles en este momento en el catálogo de Dropi.',
                        ];
                    }
                } else {
                    $json = $response->json();
                    $lastError = $json['message'] ?? ($json['error'] ?? "HTTP {$response->status()} en {$endpoint}");
                }
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::info("Dropi products query [{$endpoint}] exception: " . $e->getMessage());
            }
        }

        return [
            'success' => false,
            'source' => 'api_error',
            'products' => [],
            'total' => 0,
            'message' => "Dropi API: {$lastError}. Por favor verifica que tu token JWT en 'Configuración Dropi' esté activo.",
        ];
    }

    /**
     * Get single product data directly from Dropi API with multi-endpoint fallback
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

        $endpoints = [
            "https://api.dropi.co/integrations/products/v2/{$id}",
            "https://api-v2.dropi.co/integrations/products/v2/{$id}",
            "https://api.dropi.co/integrations/products/detail/{$id}",
            "https://api.dropi.co/api/products/{$id}",
            "https://api-v2.dropi.co/api/products/{$id}",
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders($this->getWordPressHeaders($token))
                    ->timeout(20)
                    ->connectTimeout(6)
                    ->get($endpoint);

                if ($response->successful()) {
                    $json = $response->json();
                    $obj = $json['objects'] ?? ($json['data'] ?? ($json['product'] ?? ($json['result'] ?? null)));
                    if (!empty($obj) && is_array($obj)) {
                        return $obj;
                    }
                }
            } catch (\Exception $e) {
                Log::info("Error fetching single Dropi product [{$endpoint}]: " . $e->getMessage());
            }
        }

        // Fallback: Search in product catalog index by ID
        try {
            $res = $this->getProducts(10, 0, (string)$id);
            if (!empty($res['products']) && is_array($res['products'])) {
                foreach ($res['products'] as $prod) {
                    if ((string)($prod['id'] ?? '') === (string)$id) {
                        return $prod;
                    }
                }
                return $res['products'][0] ?? null;
            }
        } catch (\Exception $e) {
            // Ignore
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
        $wholesale = (float) ($dropiProduct['sale_price'] ?? ($dropiProduct['price'] ?? ($dropiProduct['wholesale_price'] ?? 0)));
        $suggested = (float) ($dropiProduct['suggested_price'] ?? ($wholesale * 1.5));
        $salePrice = $customPrice && $customPrice > 0 ? $customPrice : ($suggested > 0 ? $suggested : ($wholesale * 1.5));
        $comparePrice = $salePrice > $wholesale ? round($salePrice * 1.25) : null;
        $profit = max(0, $salePrice - $wholesale);

        // Calculate Stock
        $stock = 20;
        if (!empty($dropiProduct['warehouse_product']) && is_array($dropiProduct['warehouse_product'])) {
            $totalStock = 0;
            foreach ($dropiProduct['warehouse_product'] as $w) {
                $totalStock += (int) ($w['stock'] ?? 0);
            }
            if ($totalStock > 0) {
                $stock = $totalStock;
            }
        } elseif (isset($dropiProduct['stock'])) {
            $stock = (int) $dropiProduct['stock'];
        }

        $description = $dropiProduct['description'] ?? '';
        
        // Localize any images & animated GIFs inside the description
        $description = $this->localizeDescriptionMedia($description, $dropiId);
        $shortDesc = Str::limit(strip_tags($description), 180);

        // Images & GIFs handling - Download and store locally
        $images = [];
        $photoList = !empty($dropiProduct['photos']) ? $dropiProduct['photos'] : (!empty($dropiProduct['gallery']) ? $dropiProduct['gallery'] : []);
        
        $imgIndex = 0;
        foreach ($photoList as $p) {
            $imgIndex++;
            $urlS3 = is_array($p) ? ($p['urlS3'] ?? '') : (is_object($p) ? ($p->urlS3 ?? '') : '');
            $urlDirect = is_array($p) ? ($p['url'] ?? '') : (is_object($p) ? ($p->url ?? '') : (is_string($p) ? $p : ''));

            $targetUrl = '';
            if (!empty($urlS3)) {
                $targetUrl = Str::startsWith($urlS3, 'http') ? $urlS3 : "https://d39ru7awumhhs2.cloudfront.net/" . ltrim($urlS3, '/');
            } elseif (!empty($urlDirect)) {
                $targetUrl = Str::startsWith($urlDirect, 'http') ? $urlDirect : "https://api.dropi.co/" . ltrim($urlDirect, '/');
            }

            if (!empty($targetUrl)) {
                $localImgUrl = $this->downloadAndSaveDropiMedia($targetUrl, $dropiId, "gallery_{$imgIndex}");
                $images[] = $localImgUrl;
            }
        }

        if (empty($images) && !empty($dropiProduct['image'])) {
            $localImgUrl = $this->downloadAndSaveDropiMedia($dropiProduct['image'], $dropiId, 'main');
            $images[] = $localImgUrl;
        }

        $mainImage = $images[0] ?? 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800';

        // Resolve Category
        if (!$categoryId) {
            $categoryName = 'General';
            if (!empty($dropiProduct['categories']) && is_array($dropiProduct['categories'])) {
                $firstCat = $dropiProduct['categories'][0] ?? null;
                if (is_array($firstCat) && !empty($firstCat['name'])) {
                    $categoryName = $firstCat['name'];
                } elseif (is_object($firstCat) && !empty($firstCat->name)) {
                    $categoryName = $firstCat->name;
                }
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
                'is_active' => ($stock > 0),
            ]);

            $this->notifyDropiImport($dropiId, $product->id, $token);

            return [
                'success' => true,
                'action' => 'updated',
                'product' => $product,
                'message' => "¡Producto '{$product->name}' (Dropi ID #{$dropiId}) actualizado con éxito en tu tienda con imágenes/GIFs descargados y stock sincronizado ({$stock} unid.)!",
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
            'is_active' => ($stock > 0),
        ]);

        $this->notifyDropiImport($dropiId, $product->id, $token);

        return [
            'success' => true,
            'action' => 'created',
            'product' => $product,
            'message' => "¡Producto '{$product->name}' importado con éxito desde Dropi con imágenes/GIFs locales y stock de {$stock} unidades!",
        ];
    }

    /**
     * Download and store image or animated GIF locally
     */
    public function downloadAndSaveDropiMedia(string $url, string|int $dropiId, string $prefix = 'media'): string
    {
        if (empty($url) || !Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        try {
            $response = Http::timeout(15)->connectTimeout(5)->get($url);
            if ($response->successful()) {
                $contentType = strtolower($response->header('Content-Type') ?? '');
                
                // Detect extension (GIF, WebP, PNG, JPG)
                $ext = 'jpg';
                if (str_contains($contentType, 'gif') || str_ends_with(strtolower(parse_url($url, PHP_URL_PATH) ?? ''), '.gif')) {
                    $ext = 'gif';
                } elseif (str_contains($contentType, 'webp') || str_ends_with(strtolower(parse_url($url, PHP_URL_PATH) ?? ''), '.webp')) {
                    $ext = 'webp';
                } elseif (str_contains($contentType, 'png') || str_ends_with(strtolower(parse_url($url, PHP_URL_PATH) ?? ''), '.png')) {
                    $ext = 'png';
                } elseif (str_contains($contentType, 'jpeg') || str_contains($contentType, 'jpg')) {
                    $ext = 'jpg';
                }

                $filename = "{$prefix}_" . substr(md5($url), 0, 10) . ".{$ext}";
                $storagePath = "products/{$dropiId}/{$filename}";

                \Illuminate\Support\Facades\Storage::disk('public')->put($storagePath, $response->body());
                return "/storage/{$storagePath}";
            }
        } catch (\Exception $e) {
            Log::warning("Could not download Dropi media [{$url}]: " . $e->getMessage());
        }

        // Fallback to remote URL if download fails
        return $url;
    }

    /**
     * Parse HTML description and download all embedded images/GIFs locally
     */
    public function localizeDescriptionMedia(string $html, string|int $dropiId): string
    {
        if (empty($html)) {
            return $html;
        }

        return preg_replace_callback('/<img[^>]+src=["\']([^"\']+)["\']/i', function ($matches) use ($dropiId) {
            $originalSrc = $matches[1];
            if (Str::startsWith($originalSrc, ['http://', 'https://'])) {
                $localSrc = $this->downloadAndSaveDropiMedia($originalSrc, $dropiId, 'desc');
                return str_replace($originalSrc, $localSrc, $matches[0]);
            }
            return $matches[0];
        }, $html);
    }

    /**
     * Alias for getProduct
     */
    public function getProductById(string|int $id, ?string $token = null): ?array
    {
        return $this->getProduct($id, $token);
    }

    /**
     * Synchronize stock for a single Dropi product
     */
    public function syncProductStock(Product $product): array
    {
        if (empty($product->dropi_id)) {
            return [
                'success' => false,
                'message' => "El producto '{$product->name}' no está vinculado a Dropi.",
            ];
        }

        $dropiData = $this->getProduct($product->dropi_id);
        if (empty($dropiData)) {
            return [
                'success' => false,
                'message' => "No se pudo obtener la información actualizada de Dropi para el producto ID #{$product->dropi_id}.",
            ];
        }

        // Calculate latest stock
        $newStock = 0;
        if (!empty($dropiData['warehouse_product']) && is_array($dropiData['warehouse_product'])) {
            foreach ($dropiData['warehouse_product'] as $w) {
                $newStock += (int) ($w['stock'] ?? 0);
            }
        } elseif (isset($dropiData['stock'])) {
            $newStock = (int) $dropiData['stock'];
        }

        $previousStock = $product->stock;
        $product->stock = max(0, $newStock);
        
        // Update wholesale and suggested prices if provided
        if (!empty($dropiData['sale_price']) || !empty($dropiData['price'])) {
            $wholesale = (float) ($dropiData['sale_price'] ?? $dropiData['price']);
            if ($wholesale > 0) {
                $product->wholesale_price = $wholesale;
                $product->suggested_price = (float) ($dropiData['suggested_price'] ?? ($wholesale * 1.5));
                $product->profit_margin = max(0, $product->price - $wholesale);
            }
        }

        $product->save();

        $statusMessage = ($product->stock === 0)
            ? "⚠️ El producto '{$product->name}' se encuentra AGOTADO en Dropi (Stock: 0)."
            : "✅ Stock actualizado para '{$product->name}': {$product->stock} unidades disponibles.";

        return [
            'success' => true,
            'product_id' => $product->id,
            'name' => $product->name,
            'dropi_id' => $product->dropi_id,
            'previous_stock' => $previousStock,
            'current_stock' => $product->stock,
            'is_in_stock' => $product->stock > 0,
            'message' => $statusMessage,
        ];
    }

    /**
     * Synchronize stock for all imported Dropi products in the store
     */
    public function syncAllProductsStock(): array
    {
        $products = Product::whereNotNull('dropi_id')->get();
        $total = $products->count();
        $synced = 0;
        $outOfStock = 0;
        $details = [];

        foreach ($products as $prod) {
            $res = $this->syncProductStock($prod);
            if ($res['success']) {
                $synced++;
                if (($res['current_stock'] ?? 0) === 0) {
                    $outOfStock++;
                }
                $details[] = $res;
            }
        }

        return [
            'success' => true,
            'total_products' => $total,
            'synced_count' => $synced,
            'out_of_stock_count' => $outOfStock,
            'details' => $details,
            'message' => "Sincronización completada: {$synced} productos actualizados. ({$outOfStock} agotados en Dropi).",
        ];
    }

    /**
     * Notify Dropi that product was imported into the store
     */
    protected function notifyDropiImport(string|int $dropiId, int $localProductId, string $token): void
    {
        if (empty($token)) return;

        try {
            Http::withHeaders($this->getWordPressHeaders($token))
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
            $wholesale = (float) ($item['sale_price'] ?? ($item['price'] ?? ($item['wholesale_price'] ?? 0)));
            $suggested = (float) ($item['suggested_price'] ?? ($wholesale * 1.5));

            // Skip invalid or incomplete draft products
            if ($wholesale <= 0) {
                continue;
            }
            
            // Stock calculation from warehouse_product
            $stock = 20;
            if (!empty($item['warehouse_product']) && is_array($item['warehouse_product'])) {
                $totalStock = 0;
                foreach ($item['warehouse_product'] as $w) {
                    $totalStock += (int) ($w['stock'] ?? 0);
                }
                if ($totalStock > 0) {
                    $stock = $totalStock;
                }
            } elseif (isset($item['stock'])) {
                $stock = (int) $item['stock'];
            }

            // Image resolution from CloudFront CDN
            $img = '';
            $photoList = !empty($item['gallery']) ? $item['gallery'] : (!empty($item['photos']) ? $item['photos'] : []);
            foreach ($photoList as $p) {
                $urlS3 = is_array($p) ? ($p['urlS3'] ?? '') : (is_object($p) ? ($p->urlS3 ?? '') : '');
                $urlDirect = is_array($p) ? ($p['url'] ?? '') : (is_object($p) ? ($p->url ?? '') : (is_string($p) ? $p : ''));

                if (!empty($urlS3)) {
                    $img = Str::startsWith($urlS3, 'http') ? $urlS3 : "https://d39ru7awumhhs2.cloudfront.net/" . ltrim($urlS3, '/');
                    break;
                } elseif (!empty($urlDirect)) {
                    $img = Str::startsWith($urlDirect, 'http') ? $urlDirect : "https://api.dropi.co/" . ltrim($urlDirect, '/');
                    break;
                }
            }

            if (empty($img)) {
                $img = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800';
            }

            // Category resolution
            $category = 'General';
            if (!empty($item['categories']) && is_array($item['categories'])) {
                $firstCat = $item['categories'][0] ?? null;
                if (is_array($firstCat) && !empty($firstCat['name'])) {
                    $category = $firstCat['name'];
                } elseif (is_object($firstCat) && !empty($firstCat->name)) {
                    $category = $firstCat->name;
                }
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
                'provider' => $item['user']['store_name'] ?? null,
            ];
        }
        return $normalized;
    }
}
