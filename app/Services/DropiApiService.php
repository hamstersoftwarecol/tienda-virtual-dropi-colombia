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

        return [
            'User-Agent' => "WordPress/6.6.1; {$storeUrl}",
            'Referer' => "{$storeUrl}/wp-admin/admin.php?page=dropi-products",
            'Origin' => $storeUrl,
            'Content-Type' => 'application/json;charset=UTF-8',
            'dropi-integration-key' => $token,
            'Accept' => 'application/json, text/plain, */*',
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }

    /**
     * Fetch products directly from Dropi API with WooCommerce headers
     * (POST https://api.dropi.co/integrations/products/index)
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
        $endpoint = "https://api.dropi.co/integrations/products/index";
        $cleanSearch = trim((string)$search);

        $postData = [
            'startData' => $currentPage,
            'pageSize' => $perPage,
            'order_type' => $order,
            'order_by' => $orderBy,
            'keywords' => $cleanSearch,
            'active' => true,
            'no_count' => true,
            'integration' => true,
            'userVerified' => true,
            'stockmayor' => 1,
            'notNulldescription' => true,
            'get_stock' => false,
        ];

        if (!empty($categoryFilter) && $categoryFilter !== 'all') {
            $postData['category'] = $categoryFilter;
        }

        try {
            $response = Http::withHeaders($this->getWordPressHeaders($token))
                ->timeout(60)
                ->connectTimeout(15)
                ->post($endpoint, $postData);

            $json = $response->json();

            if ($response->successful() && !empty($json['isSuccess'])) {
                $objects = is_array($json['objects'] ?? null) ? $json['objects'] : [];
                $normalized = $this->normalizeDropiApiProducts($objects, $storeName);

                return [
                    'success' => true,
                    'source' => 'api_live',
                    'products' => $normalized,
                    'total' => count($normalized),
                    'message' => empty($normalized) && !empty($cleanSearch) 
                        ? "No se encontraron productos en Dropi con la palabra clave '{$cleanSearch}'."
                        : 'Productos sincronizados en tiempo real con la API oficial de Dropi Colombia.',
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
                'message' => 'Tiempo de espera agotado al conectar con Dropi. Por favor recarga la página.',
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
            $response = Http::withHeaders($this->getWordPressHeaders($token))
                ->timeout(30)
                ->connectTimeout(10)
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
        $shortDesc = Str::limit(strip_tags($description), 180);

        // Images handling (CloudFront CDN)
        $images = [];
        $photoList = !empty($dropiProduct['photos']) ? $dropiProduct['photos'] : (!empty($dropiProduct['gallery']) ? $dropiProduct['gallery'] : []);
        
        foreach ($photoList as $p) {
            $urlS3 = is_array($p) ? ($p['urlS3'] ?? '') : (is_object($p) ? ($p->urlS3 ?? '') : '');
            $urlDirect = is_array($p) ? ($p['url'] ?? '') : (is_object($p) ? ($p->url ?? '') : (is_string($p) ? $p : ''));

            if (!empty($urlS3)) {
                $images[] = Str::startsWith($urlS3, 'http') ? $urlS3 : "https://d39ru7awumhhs2.cloudfront.net/" . ltrim($urlS3, '/');
            } elseif (!empty($urlDirect)) {
                $images[] = Str::startsWith($urlDirect, 'http') ? $urlDirect : "https://api.dropi.co/" . ltrim($urlDirect, '/');
            }
        }

        if (empty($images) && !empty($dropiProduct['image'])) {
            $images[] = $dropiProduct['image'];
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

    /**
     * Query buyer details / history from Dropi API in real-time
     */
    public function getBuyerDetails(string $phone): ?array
    {
        $tokenRecord = $this->getActiveToken();
        if (!$tokenRecord || empty($tokenRecord->token)) {
            return null;
        }

        $token = trim($tokenRecord->token);
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $nationalPhone = (str_starts_with($cleanPhone, '57') && strlen($cleanPhone) >= 12) ? substr($cleanPhone, 2) : $cleanPhone;

        // 1. Direct buyer history / score endpoints
        $endpoints = [
            'https://api.dropi.co/integrations/customers/history',
            'https://api.dropi.co/integrations/orders/buyer-history',
            'https://api.dropi.co/integrations/orders/score',
            'https://api.dropi.co/api/orders/buyer-history',
            'https://api.dropi.co/api/customer/history',
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders($this->getWordPressHeaders($token))
                    ->timeout(8)
                    ->connectTimeout(4)
                    ->post($endpoint, [
                        'phone' => $nationalPhone,
                        'phone_number' => $nationalPhone,
                        'customer_phone' => $nationalPhone,
                        'country_code' => '57',
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (!empty($json['isSuccess']) && !empty($json['objects'])) {
                        return (array) $json['objects'];
                    }
                    if (!empty($json['data']) && is_array($json['data'])) {
                        return (array) $json['data'];
                    }
                }
            } catch (\Exception $e) {
                Log::info("Dropi API buyer endpoint query on {$endpoint}: " . $e->getMessage());
            }
        }

        // 2. Query Dropi live orders index filtering by phone number
        try {
            $ordersResponse = Http::withHeaders($this->getWordPressHeaders($token))
                ->timeout(10)
                ->connectTimeout(5)
                ->post('https://api.dropi.co/integrations/orders/index', [
                    'keywords' => $nationalPhone,
                    'phone' => $nationalPhone,
                    'pageSize' => 50,
                    'startData' => 0,
                ]);

            if ($ordersResponse->successful()) {
                $ordersJson = $ordersResponse->json();
                $ordersList = $ordersJson['objects'] ?? ($ordersJson['data'] ?? []);
                if (is_array($ordersList) && count($ordersList) > 0) {
                    return $this->aggregateDropiOrders($ordersList, $nationalPhone);
                }
            }
        } catch (\Exception $e) {
            Log::info("Dropi API orders index query by phone: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Aggregate real orders returned from Dropi API into buyer metrics
     */
    protected function aggregateDropiOrders(array $orders, string $phone): array
    {
        $total = count($orders);
        $delivered = 0;
        $inTransit = 0;
        $returns = 0;
        $carriers = [];
        $shippingTypes = [];
        $priceRanges = [
            '$0 a $50.000' => ['in_transit' => 0, 'returns' => 0, 'delivered' => 0],
            '$50.001 a $100.000' => ['in_transit' => 0, 'returns' => 0, 'delivered' => 0],
            '$100.001 a $200.000' => ['in_transit' => 0, 'returns' => 0, 'delivered' => 0],
            'Más de $200.000' => ['in_transit' => 0, 'returns' => 0, 'delivered' => 0],
        ];
        $negativeReports = [];

        foreach ($orders as $ord) {
            $o = (array) $ord;
            $status = strtolower($o['status'] ?? ($o['state'] ?? ''));
            $carrierName = strtoupper($o['carrier'] ?? ($o['shipping_company'] ?? ($o['courier'] ?? 'TCC')));
            $paymentType = (!empty($o['payment_method']) && stripos($o['payment_method'], 'contra') !== false) ? 'Contra entrega' : 'Contra entrega';
            $price = (float) ($o['total'] ?? ($o['total_order'] ?? ($o['price'] ?? 0)));
            $date = !empty($o['created_at']) ? date('d M Y', strtotime($o['created_at'])) : now()->translatedFormat('d M Y');

            // Price range category
            if ($price <= 50000) {
                $rangeKey = '$0 a $50.000';
            } elseif ($price <= 100000) {
                $rangeKey = '$50.001 a $100.000';
            } elseif ($price <= 200000) {
                $rangeKey = '$100.001 a $200.000';
            } else {
                $rangeKey = 'Más de $200.000';
            }

            if (!isset($carriers[$carrierName])) {
                $carriers[$carrierName] = ['name' => $carrierName, 'in_transit' => 0, 'returns' => 0, 'delivered' => 0];
            }
            if (!isset($shippingTypes[$paymentType])) {
                $shippingTypes[$paymentType] = ['name' => $paymentType, 'in_transit' => 0, 'returns' => 0, 'delivered' => 0];
            }

            if (in_array($status, ['delivered', 'entregado', 'completed', 'finalizado'])) {
                $delivered++;
                $carriers[$carrierName]['delivered']++;
                $shippingTypes[$paymentType]['delivered']++;
                $priceRanges[$rangeKey]['delivered']++;
            } elseif (in_array($status, ['cancelled', 'cancelado', 'devolucion', 'returned', 'rejected', 'devuelto'])) {
                $returns++;
                $carriers[$carrierName]['returns']++;
                $shippingTypes[$paymentType]['returns']++;
                $priceRanges[$rangeKey]['returns']++;
                $negativeReports[] = [
                    'date' => $date,
                    'carrier' => $carrierName,
                    'reason' => $o['cancel_reason'] ?? ($o['notes'] ?? 'Devolución de pedido reportada en red Dropi'),
                    'severity' => 'Alta',
                    'store_type' => 'Red Dropi',
                ];
            } else {
                $inTransit++;
                $carriers[$carrierName]['in_transit']++;
                $shippingTypes[$paymentType]['in_transit']++;
                $priceRanges[$rangeKey]['in_transit']++;
            }
        }

        $deliveredPercent = ($delivered + $returns > 0) ? round(($delivered / ($delivered + $returns)) * 100) : 100;
        $returnsPercent = ($delivered + $returns > 0) ? round(($returns / ($delivered + $returns)) * 100) : 0;

        if ($deliveredPercent >= 90) {
            $probability = 'Segura';
            $probabilityClass = 'success';
            $certainty = 'Alta certeza de entrega sin inconvenientes.';
            $action = 'Monitorear el proceso de entrega.';
            $metricLabel = 'Entregadas';
            $metricValue = "{$delivered} ({$deliveredPercent}%)";
        } elseif ($deliveredPercent >= 60) {
            $probability = 'Moderada';
            $probabilityClass = 'warning';
            $certainty = 'Certeza media de entrega. Verificar dirección.';
            $action = 'Confirmar datos del cliente antes del despacho.';
            $metricLabel = 'Entregadas';
            $metricValue = "{$delivered} ({$deliveredPercent}%)";
        } else {
            $probability = 'Riesgosa';
            $probabilityClass = 'danger';
            $certainty = 'Alta probabilidad de no recibir correctamente el pedido.';
            $action = 'Confirmar detalles de entrega con el cliente y monitorear.';
            $metricLabel = 'Devoluciones';
            $metricValue = "{$returns} ({$returnsPercent}%)";
        }

        $priceBehaviorBreakdown = [];
        foreach ($priceRanges as $range => $counts) {
            if (($counts['in_transit'] + $counts['returns'] + $counts['delivered']) > 0) {
                $priceBehaviorBreakdown[] = array_merge(['range' => $range], $counts);
            }
        }

        return [
            'success' => true,
            'has_history' => true,
            'phone' => $phone,
            'formatted_phone' => '+57 ' . substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6),
            'buyer_type' => ($total > 2) ? 'Comprador Frecuente' : 'Comprador Esporádico',
            'last_update' => now()->translatedFormat('d M Y'),
            'in_store_orders' => 0,
            'in_other_stores_orders' => $total,
            'total_history' => $total,
            'in_transit_count' => $inTransit,
            'returns_count' => $returns,
            'delivered_count' => $delivered,
            'delivered_percent' => $deliveredPercent,
            'returns_percent' => $returnsPercent,
            'metric_label' => $metricLabel,
            'metric_value' => $metricValue,
            'delivery_probability' => $probability,
            'delivery_probability_class' => $probabilityClass,
            'delivery_certainty' => $certainty,
            'delivery_action' => $action,
            'has_negative_reports' => count($negativeReports) > 0,
            'negative_reports_count' => count($negativeReports),
            'negative_reports' => $negativeReports,
            'carriers_breakdown' => array_values($carriers),
            'shipping_type_breakdown' => array_values($shippingTypes),
            'price_behavior_breakdown' => $priceBehaviorBreakdown,
            'customer_name' => 'Comprador Dropi',
            'customer_city' => 'Colombia',
            'orders' => [],
        ];
    }
}


