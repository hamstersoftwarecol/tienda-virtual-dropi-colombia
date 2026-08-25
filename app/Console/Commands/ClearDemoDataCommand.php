<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\WooCommerceApiKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClearDemoDataCommand extends Command
{
    protected $signature = 'store:clear-demo {--admin-email=admin@tienda.com} {--admin-password=password}';
    protected $description = 'Borra todos los datos demo de la tienda (productos, pedidos, clientes, cupones) conservando únicamente al Administrador y las claves de integración';

    public function handle(): int
    {
        $this->info('Iniciando limpieza de datos de demostración...');

        DB::statement('PRAGMA foreign_keys = OFF;'); // SQLite
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;'); // MySQL
        } catch (\Exception $e) {
            // Ignore if SQLite
        }

        // 1. Borrar Pedidos e Ítems
        OrderItem::truncate();
        Order::truncate();
        $this->line('✓ Pedidos e ítems de pedido eliminados.');

        // 2. Borrar Reseñas y Calificaciones
        Review::truncate();
        $this->line('✓ Reseñas eliminadas.');

        // 3. Borrar Productos de la Tienda
        Product::truncate();
        $this->line('✓ Productos en tienda eliminados.');

        // 4. Borrar Categorías
        Category::truncate();
        $this->line('✓ Categorías eliminadas.');

        // 5. Borrar Cupones
        Coupon::truncate();
        $this->line('✓ Cupones eliminados.');

        // 6. Borrar todos los clientes no administradores
        User::where('is_admin', false)->delete();
        $this->line('✓ Clientes y compradores demo eliminados.');

        // 7. Asegurar que el usuario Administrador existe y no se borre
        $adminEmail = $this->option('admin-email');
        $adminPassword = $this->option('admin-password');

        $admin = User::where('is_admin', true)->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin Principal',
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'is_admin' => true,
                'phone' => '+57 310 123 4567',
                'dni' => '1020304050',
                'address' => 'Dirección Principal',
                'city' => 'Bogotá D.C.',
                'department' => 'Cundinamarca',
                'email_verified_at' => now(),
            ]);
            $this->info("✓ Usuario Administrador creado: {$admin->email}");
        } else {
            $this->info("✓ Usuario Administrador conservado: {$admin->email}");
        }

        // 8. Asegurar claves de WooCommerce
        if (WooCommerceApiKey::count() === 0) {
            WooCommerceApiKey::create([
                'user_id' => $admin->id,
                'description' => 'Integración WooCommerce REST API',
                'permissions' => 'read_write',
                'consumer_key' => 'ck_' . Str::random(32),
                'consumer_secret' => 'cs_' . Str::random(32),
                'truncated_key' => 'api99',
                'is_active' => true,
                'last_access_at' => now(),
            ]);
        }
        $this->line('✓ Claves de WooCommerce conservadas.');

        DB::statement('PRAGMA foreign_keys = ON;'); // SQLite
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;'); // MySQL
        } catch (\Exception $e) {
            // Ignore if SQLite
        }

        $this->info('');
        $this->info('🎉 ¡Todos los datos de demostración han sido eliminados con éxito!');
        $this->info("Tu tienda está limpia y lista para producción. Puedes ingresar con el Administrador: {$admin->email}");

        return 0;
    }
}
