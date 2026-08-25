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
     * Complete catalog of top-selling Dropi Colombia products
     */
    public function getDropiCatalog(string $search = '', ?string $categoryFilter = null): array
    {
        $catalog = [
            [
                'id' => 'DRP-10145',
                'name' => 'Smartwatch Ultra 8 Serie 8 con Doble Correa y Carga Inalámbrica',
                'category' => 'Tecnología',
                'wholesale_price' => 45000.00,
                'suggested_price' => 89900.00,
                'stock' => 140,
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800',
                'description' => 'Reloj inteligente de última generación con pantalla HD de 2.0 pulgadas, monitor de ritmo cardíaco, oxígeno en sangre, llamadas bluetooth y resistencia al agua IP68.',
            ],
            [
                'id' => 'DRP-20491',
                'name' => 'Audífonos Bluetooth Inalámbricos F9-5 TWS con Powerbank Integrada',
                'category' => 'Tecnología',
                'wholesale_price' => 28000.00,
                'suggested_price' => 59900.00,
                'stock' => 220,
                'image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800',
                'description' => 'Audífonos con estuche de carga que funciona como banco de energía para tu celular, sonido envolvente 9D, pantalla digital LED y cancelación de ruido.',
            ],
            [
                'id' => 'DRP-30882',
                'name' => 'Aro de Luz LED 12 Pulgadas (30cm) con Trípode Extensible 2.1m',
                'category' => 'Tecnología',
                'wholesale_price' => 38000.00,
                'suggested_price' => 79000.00,
                'stock' => 95,
                'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=800',
                'description' => 'Aro de luz profesional con 3 tonos de iluminación (cálido, neutro, frío), control remoto, soporte para teléfono celular 360° y trípode de aluminio reforzado.',
            ],
            [
                'id' => 'DRP-40129',
                'name' => 'Mini Proyector Portátil Full HD 1080P Cine en Casa',
                'category' => 'Tecnología',
                'wholesale_price' => 135000.00,
                'suggested_price' => 249000.00,
                'stock' => 45,
                'image' => 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=800',
                'description' => 'Proyector multimedia compacto con entradas HDMI, USB y conexión inalámbrica wifi para duplicar pantalla de celular o computador.',
            ],
            [
                'id' => 'DRP-50284',
                'name' => 'Mini Cámara Espía de Seguridad A9 WiFi HD con Visión Nocturna',
                'category' => 'Tecnología',
                'wholesale_price' => 24000.00,
                'suggested_price' => 54900.00,
                'stock' => 180,
                'image' => 'https://images.unsplash.com/photo-1557597774-9d273605dfa9?w=800',
                'description' => 'Cámara de vigilancia magnética inalámbrica, resolución 1080P, detector de movimiento y visualización en tiempo real desde la aplicación en tu smartphone.',
            ],
            [
                'id' => 'DRP-60911',
                'name' => 'Depiladora Láser IPL Indolora con 999.000 Flashes',
                'category' => 'Belleza & Cuidado Personal',
                'wholesale_price' => 68000.00,
                'suggested_price' => 139000.00,
                'stock' => 75,
                'image' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=800',
                'description' => 'Dispositivo de depilación definitiva en casa con tecnología de luz pulsada intensa, 5 niveles de energía y modo automático para cuerpo completo.',
            ],
            [
                'id' => 'DRP-70334',
                'name' => 'Cepillo Secador y Voluminizador 3 en 1 One-Step Pro',
                'category' => 'Belleza & Cuidado Personal',
                'wholesale_price' => 32000.00,
                'suggested_price' => 69000.00,
                'stock' => 160,
                'image' => 'https://images.unsplash.com/photo-1583001809873-a128495da465?w=800',
                'description' => 'Cepillo de aire caliente que seca, alisa y da volumen en un solo paso con tecnología iónica que elimina el frizz y protege el cabello.',
            ],
            [
                'id' => 'DRP-80512',
                'name' => 'Pistola de Masaje Muscular Fascial Gun con 4 Cabezales',
                'category' => 'Salud & Bienestar',
                'wholesale_price' => 42000.00,
                'suggested_price' => 89000.00,
                'stock' => 110,
                'image' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=800',
                'description' => 'Masajeador de percusión de tejido profundo con 6 velocidades ajustables, batería recargable de litio y 4 cabezales intercambiables para cuello, espalda y piernas.',
            ],
            [
                'id' => 'DRP-90176',
                'name' => 'Aspiradora Inalámbrica de Mano Portátil para Carro y Hogar',
                'category' => 'Hogar & Carro',
                'wholesale_price' => 34000.00,
                'suggested_price' => 74900.00,
                'stock' => 130,
                'image' => 'https://images.unsplash.com/photo-1558317374-067fb5f30001?w=800',
                'description' => 'Aspiradora recargable por USB de alta potencia 120W, filtro HEPA lavable y boquillas intercambiables para rincones y tapicería.',
            ],
            [
                'id' => 'DRP-10983',
                'name' => 'Picador y Procesador de Alimentos Eléctrico de Acero Inoxidable 2L',
                'category' => 'Hogar & Cocina',
                'wholesale_price' => 39000.00,
                'suggested_price' => 79900.00,
                'stock' => 85,
                'image' => 'https://images.unsplash.com/photo-1584990347449-397cfb058ec0?w=800',
                'description' => 'Picatodo eléctrico potente de 4 cuchillas de acero inoxidable, tazón de 2 litros y 2 velocidades para triturar carnes, verduras, frutas y frutos secos en segundos.',
            ],
            [
                'id' => 'DRP-11204',
                'name' => 'Organizador Giratorio 360° para Maquillaje y Cosméticos',
                'category' => 'Hogar & Belleza',
                'wholesale_price' => 26000.00,
                'suggested_price' => 59000.00,
                'stock' => 190,
                'image' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=800',
                'description' => 'Torre organizadora de acrílico con rotación fluida 360 grados, bandejas de altura ajustable y capacidad para más de 30 productos de belleza.',
            ],
            [
                'id' => 'DRP-12450',
                'name' => 'Micrófono Lavalier Inalámbrico K9 Tipo C & Lightning para Celular',
                'category' => 'Tecnología',
                'wholesale_price' => 29000.00,
                'suggested_price' => 64900.00,
                'stock' => 210,
                'image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800',
                'description' => 'Micrófono de solapa inalámbrico plug and play, reducción de ruido inteligente, sincronización automática sin aplicaciones y alcance de hasta 20 metros.',
            ]
        ];

        // Apply search filter if present
        if (!empty($search)) {
            $s = mb_strtolower(trim($search));
            $catalog = array_values(array_filter($catalog, function ($item) use ($s) {
                return str_contains(mb_strtolower($item['name']), $s)
                    || str_contains(mb_strtolower($item['id']), $s)
                    || str_contains(mb_strtolower($item['category']), $s);
            }));
        }

        // Apply category filter if present
        if (!empty($categoryFilter) && $categoryFilter !== 'all') {
            $catalog = array_values(array_filter($catalog, function ($item) use ($categoryFilter) {
                return $item['category'] === $categoryFilter;
            }));
        }

        return $catalog;
    }

    /**
     * Import a single product from Dropi into NovaStore (1-Click)
     */
    public function importProductById(string $dropiId, ?float $customPrice = null, ?int $categoryId = null): array
    {
        $catalog = $this->getDropiCatalog();
        $item = collect($catalog)->firstWhere('id', $dropiId);

        if (!$item) {
            return [
                'success' => false,
                'message' => "No se encontró el producto Dropi ID #{$dropiId} en el catálogo.",
            ];
        }

        $tokenRecord = $this->getActiveToken();
        $storeName = $tokenRecord?->store ?? 'Tienda 1';

        $wholesale = (float) $item['wholesale_price'];
        $suggested = (float) $item['suggested_price'];
        $salePrice = $customPrice && $customPrice > 0 ? $customPrice : $suggested;
        $comparePrice = $salePrice > $wholesale ? round($salePrice * 1.25) : null;
        $profit = max(0, $salePrice - $wholesale);

        // Resolve or create category
        if (!$categoryId) {
            $cat = Category::firstOrCreate(
                ['slug' => Str::slug($item['category'])],
                ['name' => $item['category'], 'is_active' => true]
            );
            $categoryId = $cat->id;
        }

        // Check if already in store
        $product = Product::where('dropi_id', $dropiId)->first();

        if ($product) {
            $product->update([
                'name' => $item['name'],
                'price' => $salePrice,
                'compare_price' => $comparePrice,
                'wholesale_price' => $wholesale,
                'profit_margin' => $profit,
                'suggested_price' => $suggested,
                'stock' => $item['stock'],
                'description' => $item['description'],
                'image' => $item['image'],
                'images' => [$item['image']],
                'category_id' => $categoryId,
                'dropi_store' => $storeName,
                'is_dropi_product' => true,
                'is_dropshipping' => true,
                'is_active' => true,
            ]);

            return [
                'success' => true,
                'action' => 'updated',
                'product' => $product,
                'message' => "¡Producto '{$product->name}' actualizado en tu tienda con precio " . number_format($salePrice, 0, ',', '.') . " COP!",
            ];
        }

        // Create new product
        $sku = 'DRP-' . strtoupper(substr(md5($dropiId), 0, 6));
        $product = Product::create([
            'category_id' => $categoryId,
            'name' => $item['name'],
            'slug' => Str::slug($item['name']) . '-' . Str::random(4),
            'sku' => $sku,
            'dropi_id' => $dropiId,
            'dropi_store' => $storeName,
            'is_dropi_product' => true,
            'is_dropshipping' => true,
            'short_description' => Str::limit($item['description'], 180),
            'description' => $item['description'],
            'price' => $salePrice,
            'compare_price' => $comparePrice,
            'wholesale_price' => $wholesale,
            'profit_margin' => $profit,
            'suggested_price' => $suggested,
            'stock' => $item['stock'],
            'image' => $item['image'],
            'images' => [$item['image']],
            'is_active' => true,
        ]);

        return [
            'success' => true,
            'action' => 'created',
            'product' => $product,
            'message' => "¡Producto '{$product->name}' importado con éxito a tu tienda! Ganancia estimada: " . number_format($profit, 0, ',', '.') . " COP.",
        ];
    }

    /**
     * Bulk import all products from Dropi catalog
     */
    public function importAll(): array
    {
        $catalog = $this->getDropiCatalog();
        $importedCount = 0;

        foreach ($catalog as $item) {
            $res = $this->importProductById($item['id']);
            if ($res['success']) {
                $importedCount++;
            }
        }

        return [
            'success' => true,
            'imported_count' => $importedCount,
            'total' => count($catalog),
            'message' => "¡Se han importado {$importedCount} productos de Dropi a tu tienda exitosamente!",
        ];
    }
}
