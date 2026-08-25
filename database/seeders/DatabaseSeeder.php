<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WooCommerceApiKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@tienda.com'],
            [
                'name' => 'Admin Principal',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'phone' => '+57 310 123 4567',
                'dni' => '1020304050',
                'address' => 'Carrera 7 # 71-21, Torre A',
                'city' => 'Bogotá D.C.',
                'department' => 'Cundinamarca',
                'postal_code' => '110221',
                'email_verified_at' => now(),
            ]
        );

        // 2. WooCommerce REST API Keys
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
    }
}
