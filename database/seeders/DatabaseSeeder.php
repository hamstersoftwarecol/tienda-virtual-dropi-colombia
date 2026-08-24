<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\DropiSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\User;
use App\Models\WooCommerceApiKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users & Customers
        $admin = User::create([
            'name' => 'Admin Principal',
            'email' => 'admin@tienda.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'phone' => '+57 310 123 4567',
            'dni' => '1020304050',
            'address' => 'Carrera 7 # 71-21, Torre A',
            'city' => 'Bogotá D.C.',
            'department' => 'Cundinamarca',
            'postal_code' => '110221',
            'email_verified_at' => now(),
        ]);

        $customer = User::create([
            'name' => 'Carlos Mendoza',
            'email' => 'cliente@tienda.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'phone' => '+57 300 987 6543',
            'dni' => '71234567',
            'address' => 'Calle 10 # 43E-12, El Poblado',
            'city' => 'Medellín',
            'department' => 'Antioquia',
            'postal_code' => '050021',
            'email_verified_at' => now(),
        ]);

        $customer2 = User::create([
            'name' => 'Mariana Ruiz',
            'email' => 'mariana@tienda.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'phone' => '+57 315 555 8899',
            'dni' => '1037890123',
            'address' => 'Av. San Martín # 5-100, Bocagrande',
            'city' => 'Cartagena',
            'department' => 'Bolívar',
            'postal_code' => '130001',
            'email_verified_at' => now(),
        ]);

        $customer3 = User::create([
            'name' => 'Alejandro Morales',
            'email' => 'alejandro@correo.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'phone' => '+57 311 444 2233',
            'dni' => '98765432',
            'address' => 'Av. 6 Norte # 24N-15, Santa Mónica',
            'city' => 'Cali',
            'department' => 'Valle del Cauca',
            'postal_code' => '760001',
            'email_verified_at' => now(),
        ]);

        // 2. Categories
        $categoriesData = [
            [
                'name' => 'Tecnología & Gadgets',
                'slug' => 'tecnologia-gadgets',
                'description' => 'Los últimos smartphones, audio de alta fidelidad, laptops y accesorios inteligentes.',
                'icon' => 'bi-laptop',
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Moda & Tendencias',
                'slug' => 'moda-tendencias',
                'description' => 'Prendas exclusivas, chaquetas de diseño, ropa casual y estilo urbano.',
                'icon' => 'bi-bag-heart',
                'image' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Calzado & Sneakers',
                'slug' => 'calzado-sneakers',
                'description' => 'Zapatillas deportivas, calzado urbano y botas de máxima calidad.',
                'icon' => 'bi-fire',
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Relojes & Accesorios',
                'slug' => 'relojes-accesorios',
                'description' => 'Relojes premium, gafas de sol polarizadas, mochilas y billeteras de cuero.',
                'icon' => 'bi-smartwatch',
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Hogar & Decoración',
                'slug' => 'hogar-decoracion',
                'description' => 'Lámparas modernas, aromaterapia, decoración minimalista y comodidad.',
                'icon' => 'bi-house-heart',
                'image' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=800&auto=format&fit=crop&q=80',
                'is_featured' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Audio & Sonido Pro',
                'slug' => 'audio-sonido-pro',
                'description' => 'Auriculares inalámbricos con cancelación de ruido y altavoces portátiles.',
                'icon' => 'bi-headphones',
                'image' => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=800&auto=format&fit=crop&q=80',
                'is_featured' => true,
                'sort_order' => 6,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['slug']] = Category::create($c);
        }

        // 3. Suppliers & Bodegas (Dropi Colombia)
        $supplier1 = Supplier::create([
            'name' => 'Bodega Mayorista Medellín Tech',
            'slug' => 'bodega-mayorista-medellin-tech',
            'city' => 'Medellín',
            'department' => 'Antioquia',
            'phone' => '+57 314 888 9900',
            'email' => 'ventas@bodegamedellin.co',
            'rating' => 4.9,
            'logo' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=300',
            'warehouse_address' => 'Zona Industrial Guayabal, Bodega 45',
            'description' => 'Especialistas en electrónica, gadgets, smartwatches y accesorios para celular con despacho el mismo día.',
            'is_verified' => true,
            'is_active' => true,
            'total_products_count' => 6,
        ]);

        $supplier2 = Supplier::create([
            'name' => 'Importadora Bogotá Express',
            'slug' => 'importadora-bogota-express',
            'city' => 'Bogotá D.C.',
            'department' => 'Cundinamarca',
            'phone' => '+57 310 777 6655',
            'email' => 'contacto@bogotaexpress.co',
            'rating' => 4.8,
            'logo' => 'https://images.unsplash.com/photo-1553413077-190dd305871c?w=300',
            'warehouse_address' => 'Parque Industrial Fontibón, Módulo C',
            'description' => 'Importación directa de audio pro, cámaras 4K y tecnología de alta fidelidad para dropshipping.',
            'is_verified' => true,
            'is_active' => true,
            'total_products_count' => 5,
        ]);

        $supplier3 = Supplier::create([
            'name' => 'MegaBodega Cali Moda & Hogar',
            'slug' => 'megabodega-cali-moda-hogar',
            'city' => 'Cali',
            'department' => 'Valle del Cauca',
            'phone' => '+57 318 333 2211',
            'email' => 'pedidos@calimoda.co',
            'rating' => 4.7,
            'logo' => 'https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=300',
            'warehouse_address' => 'Acopi Yumbo, Calle 15 # 20-50',
            'description' => 'Calzado urbano, mochilas antirrobo, chaquetas impermeables y artículos para el hogar.',
            'is_verified' => true,
            'is_active' => true,
            'total_products_count' => 5,
        ]);

        // 4. Products in Store (linked to suppliers)
        $productsData = [
            [
                'category_id' => $categories['audio-sonido-pro']->id,
                'supplier_id' => $supplier2->id,
                'dropi_id' => 'DRP-PROD-101',
                'name' => 'Auriculares Pro Wireless Studio 4',
                'slug' => 'auriculares-pro-wireless-studio-4',
                'sku' => 'AUD-PRO-001',
                'short_description' => 'Cancelación activa de ruido híbrida, audio espacial 3D y 40 horas de autonomía continua.',
                'description' => 'Sumérgete en un sonido cristalino con los Auriculares Pro Wireless Studio 4. Diseñados con transductores de neodimio de 40mm para bajos profundos y agudos precisos.',
                'wholesale_price' => 240000.00,
                'profit_margin' => 149900.00,
                'price' => 389900.00,
                'compare_price' => 499900.00,
                'stock' => 25,
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=80',
                'badge' => 'OFERTA',
                'rating' => 4.9,
                'reviews_count' => 38,
                'sales_count' => 124,
                'is_featured' => true,
                'is_dropshipping' => true,
                'is_active' => true,
            ],
            [
                'category_id' => $categories['tecnologia-gadgets']->id,
                'supplier_id' => $supplier1->id,
                'dropi_id' => 'DRP-PROD-102',
                'name' => 'Smartwatch Ultra AMOLED Titanium',
                'slug' => 'smartwatch-ultra-amoled-titanium',
                'sku' => 'WAT-ULTRA-02',
                'short_description' => 'Caja de titanio aeroespacial, pantalla AMOLED Always-On de 1.96", GPS dual y resistencia 5 ATM.',
                'description' => 'El smartwatch definitivo para aventuras y rendimiento diario. Monitoreo cardíaco 24/7, oxímetro SpO2, sensor de temperatura y más de 120 modos deportivos.',
                'wholesale_price' => 380000.00,
                'profit_margin' => 219000.00,
                'price' => 599000.00,
                'compare_price' => 750000.00,
                'stock' => 18,
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80',
                'badge' => 'DESTACADO',
                'rating' => 4.8,
                'reviews_count' => 29,
                'sales_count' => 87,
                'is_featured' => true,
                'is_dropshipping' => true,
                'is_active' => true,
            ],
            [
                'category_id' => $categories['calzado-sneakers']->id,
                'supplier_id' => $supplier3->id,
                'dropi_id' => 'DRP-PROD-103',
                'name' => 'Sneakers Urban Runner Neon Red',
                'slug' => 'sneakers-urban-runner-neon-red',
                'sku' => 'SNK-RED-003',
                'short_description' => 'Zapatillas deportivas ultra ligeras con suela reactiva de amortiguación dinámica.',
                'description' => 'Estilo icónico y máxima comodidad. Fabricadas con tejido transpirable FlyWeave, suela de tracción antideslizante y cápsula de aire acolchada.',
                'wholesale_price' => 160000.00,
                'profit_margin' => 99000.00,
                'price' => 259000.00,
                'compare_price' => 320000.00,
                'stock' => 30,
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&auto=format&fit=crop&q=80',
                'badge' => 'MÁS VENDIDO',
                'rating' => 5.0,
                'reviews_count' => 56,
                'sales_count' => 210,
                'is_featured' => true,
                'is_dropshipping' => true,
                'is_active' => true,
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $p) {
            $createdProducts[] = Product::create($p);
        }

        // 5. Supplier Products in Dropi Catalog (Ready for 1-Click Import)
        $supplierProducts = [
            [
                'dropi_id' => 'DRP-CAT-001',
                'supplier_id' => $supplier1->id,
                'name' => 'Trípode Profesional con Anillo de Luz LED 12 Pulgadas',
                'slug' => 'tripode-profesional-anillo-luz-led',
                'sku' => 'DRP-LED-12',
                'short_description' => 'Ideal para creadores de contenido, tiktokers y videollamadas con soporte de celular giratorio.',
                'description' => 'Anillo de luz LED regulable con 3 modos de temperatura de color y 10 niveles de brillo. Trípode ajustable en altura de 45cm a 160cm con control remoto bluetooth.',
                'wholesale_price' => 45000.00,
                'suggested_price' => 79000.00,
                'stock' => 85,
                'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=800&auto=format&fit=crop&q=80',
                'category_name' => 'Tecnología & Gadgets',
                'is_imported' => false,
            ],
            [
                'dropi_id' => 'DRP-CAT-002',
                'supplier_id' => $supplier1->id,
                'name' => 'Power Bank Solar 20.000 mAh Carga Rápida 22.5W',
                'slug' => 'power-bank-solar-20000-mah',
                'sku' => 'DRP-PWR-20K',
                'short_description' => 'Batería externa impermeable con panel solar, linterna LED doble y 3 puertos USB.',
                'description' => 'La batería portátil más resistente para viajes y camping. Capacidad real para cargar hasta 5 veces un smartphone de última generación.',
                'wholesale_price' => 62000.00,
                'suggested_price' => 110000.00,
                'stock' => 120,
                'image' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=800&auto=format&fit=crop&q=80',
                'category_name' => 'Tecnología & Gadgets',
                'is_imported' => false,
            ],
            [
                'dropi_id' => 'DRP-CAT-003',
                'supplier_id' => $supplier2->id,
                'name' => 'Micrófono Inalámbrico Solapa Lavalier Tipo C / iPhone',
                'slug' => 'microfono-inalambrico-solapa-lavalier',
                'sku' => 'DRP-MIC-LAV',
                'short_description' => 'Grabación de audio limpia con reducción de ruido inteligente y 20 metros de alcance.',
                'description' => 'Plug and play, no requiere aplicaciones. Perfecto para entrevistas, directos de Instagram, YouTube y clases virtuales.',
                'wholesale_price' => 38000.00,
                'suggested_price' => 69900.00,
                'stock' => 150,
                'image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80',
                'category_name' => 'Audio & Sonido Pro',
                'is_imported' => false,
            ],
            [
                'dropi_id' => 'DRP-CAT-004',
                'supplier_id' => $supplier3->id,
                'name' => 'Humidificador Volcán con Efecto Llama y Aromaterapia',
                'slug' => 'humidificador-volcan-efecto-llama',
                'sku' => 'DRP-VOLC-01',
                'short_description' => 'Simulación de fuego relajante con 2 modos de niebla y luz ambiental LED.',
                'description' => 'Aromatiza y purifica cualquier habitación con aceites esenciales. Diseño exclusivo que genera un espectáculo visual en tu sala o dormitorio.',
                'wholesale_price' => 55000.00,
                'suggested_price' => 95000.00,
                'stock' => 90,
                'image' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?w=800&auto=format&fit=crop&q=80',
                'category_name' => 'Hogar & Decoración',
                'is_imported' => false,
            ],
            [
                'dropi_id' => 'DRP-CAT-005',
                'supplier_id' => $supplier3->id,
                'name' => 'Gafas de Sol Estilo Retro Steampunk Polarizadas',
                'slug' => 'gafas-sol-retro-steampunk-polarizadas',
                'sku' => 'DRP-SUN-STM',
                'short_description' => 'Montura metálica redonda con protectores laterales y cristales con filtro UV400.',
                'description' => 'Un diseño único y vanguardista que resalta en cualquier ocasión. Incluye estuche de cuero protector y paño de microfibra.',
                'wholesale_price' => 42000.00,
                'suggested_price' => 85000.00,
                'stock' => 75,
                'image' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=800&auto=format&fit=crop&q=80',
                'category_name' => 'Relojes & Accesorios',
                'is_imported' => false,
            ],
            [
                'dropi_id' => 'DRP-CAT-006',
                'supplier_id' => $supplier1->id,
                'name' => 'Mini Proyector Portátil HD 1080P Smart Cinema',
                'slug' => 'mini-proyector-portatil-hd-1080p',
                'sku' => 'DRP-PROY-MINI',
                'short_description' => 'Cine en casa de hasta 100 pulgadas con altavoz integrado y conexión HDMI/USB.',
                'description' => 'Proyecta películas, series y videojuegos en cualquier pared o techo. Compatible con Chromecast, Fire Stick, consolas y smartphones.',
                'wholesale_price' => 195000.00,
                'suggested_price' => 320000.00,
                'stock' => 40,
                'image' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800&auto=format&fit=crop&q=80',
                'category_name' => 'Tecnología & Gadgets',
                'is_imported' => false,
            ],
        ];

        foreach ($supplierProducts as $sp) {
            SupplierProduct::create($sp);
        }

        // 6. Coupons
        Coupon::create([
            'code' => 'DESCUENTO10',
            'type' => 'percent',
            'value' => 10,
            'min_amount' => 100000,
            'is_active' => true,
            'expires_at' => now()->addMonths(6),
        ]);

        Coupon::create([
            'code' => 'PROMO20',
            'type' => 'percent',
            'value' => 20,
            'min_amount' => 200000,
            'is_active' => true,
            'expires_at' => now()->addMonths(3),
        ]);

        // 7. Orders in Dropi System
        $order1 = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-10928-20260824',
            'status' => 'delivered',
            'dropi_order_id' => 'DRP-ORD-982144',
            'dropi_status' => 'delivered',
            'dropi_guide_number' => 'COORD-88912344-CO',
            'subtotal' => 648900.00,
            'discount' => 64890.00,
            'coupon_code' => 'DESCUENTO10',
            'shipping_cost' => 0.00,
            'tax' => 110962.00,
            'total' => 694972.00,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'recipient_dni' => $customer->dni,
            'shipping_address' => $customer->address,
            'shipping_city' => $customer->city,
            'shipping_department' => $customer->department,
            'shipping_postal_code' => $customer->postal_code,
            'shipping_carrier' => 'Coordinadora',
            'payment_method' => 'credit_card',
            'payment_status' => 'paid',
            'created_at' => now()->subDays(5),
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $createdProducts[0]->id,
            'product_name' => $createdProducts[0]->name,
            'product_sku' => $createdProducts[0]->sku,
            'product_image' => $createdProducts[0]->image,
            'price' => $createdProducts[0]->price,
            'quantity' => 1,
            'total' => $createdProducts[0]->price,
        ]);

        $order2 = Order::create([
            'user_id' => $customer2->id,
            'order_number' => 'ORD-28471-20260824',
            'status' => 'processing',
            'dropi_order_id' => 'DRP-ORD-441920',
            'dropi_status' => 'in_preparation',
            'dropi_guide_number' => 'SERV-77123910-CO',
            'subtotal' => 599000.00,
            'discount' => 0.00,
            'coupon_code' => null,
            'shipping_cost' => 0.00,
            'tax' => 113810.00,
            'total' => 712810.00,
            'customer_name' => $customer2->name,
            'customer_email' => $customer2->email,
            'customer_phone' => $customer2->phone,
            'recipient_dni' => $customer2->dni,
            'shipping_address' => $customer2->address,
            'shipping_city' => $customer2->city,
            'shipping_department' => $customer2->department,
            'shipping_postal_code' => $customer2->postal_code,
            'shipping_carrier' => 'Servientrega',
            'payment_method' => 'pse',
            'payment_status' => 'paid',
            'created_at' => now()->subDays(1),
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $createdProducts[1]->id,
            'product_name' => $createdProducts[1]->name,
            'product_sku' => $createdProducts[1]->sku,
            'product_image' => $createdProducts[1]->image,
            'price' => $createdProducts[1]->price,
            'quantity' => 1,
            'total' => $createdProducts[1]->price,
        ]);

        // 8. WooCommerce REST API Keys (Pre-configured for Dropi integration)
        WooCommerceApiKey::create([
            'user_id' => $admin->id,
            'description' => 'Integración Dropi.co Oficial',
            'permissions' => 'read_write',
            'consumer_key' => 'ck_dropi_' . Str::random(32),
            'consumer_secret' => 'cs_dropi_' . Str::random(32),
            'truncated_key' => 'dropi99',
            'is_active' => true,
            'last_access_at' => now(),
        ]);

        // 9. Dropi Default Settings
        DropiSetting::firstOrCreate([], [
            'api_url' => 'https://api.dropi.co/api/',
            'auth_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYXBwLmRyb3BpLmNvOjgwIiwiaWF0IjoxNzg3NTk3NTMzLCJleHAiOjQ5NDMyNzExMzMsIm5iZiI6MTc4NzU5NzUzMywianRpIjoiWWxUb3NqczNCQVF5N1Q0QiIsInN1YiI6IjM2MTg2NCIsInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlYzk3MTUzYTE0ZTBiMDQ3NTQ2YWEiLCJhdWQiOiJXT09DT01FUkNFIiwidG9rZW5fdHlwZSI6IklOVEVHUkFUSU9OUyIsIndiX2lkIjoxLCJpbnRlZ3JhdGlvbl90eXBlIjoiV09PQ09NRVJDRSIsImludGVncmF0aW9uX3R5cGVfaWQiOjEsImlwX3VybCI6W10sImludGVncmF0aW9uX3VybCI6Imh0dHBzOi8vdGllbmRhLmhhbXN0ZXJzb2Z0d2FyZS5jb20ifQ.OwRhf_UySrddaA7EPnn_69MnYq_I0WdcIGedQezpig8',
            'email' => 'admin@tienda.com',
            'auto_sync_orders' => true,
            'default_markup_percent' => 40,
            'default_carrier' => 'Coordinadora',
            'last_sync_at' => now(),
        ]);
    }
}
