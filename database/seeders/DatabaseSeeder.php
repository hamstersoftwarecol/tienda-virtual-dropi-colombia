<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::firstOrCreate(
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
    }
}
