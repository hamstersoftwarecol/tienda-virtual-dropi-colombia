<?php

namespace Database\Seeders;

use App\Models\DropiSetting;
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

        // 2. WooCommerce REST API Keys (Pre-configured for Dropi integration)
        if (WooCommerceApiKey::count() === 0) {
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
        }

        // 3. Dropi Default Settings (Configured with official Dropi JWT Token)
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
