<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DropiBuyerDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@tienda.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_query_buyer_details_by_phone(): void
    {
        $customer = User::create([
            'name' => 'Marcela Gomez',
            'email' => 'marcela@example.com',
            'phone' => '3129876543',
            'dni' => '1098765432',
            'city' => 'Bucaramanga',
            'address' => 'Calle 45 # 28-10',
            'password' => bcrypt('password'),
            'is_admin' => false,
        ]);

        Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-2026-9901',
            'subtotal' => 150000,
            'shipping_cost' => 0,
            'tax' => 0,
            'total' => 150000,
            'payment_method' => 'contraentrega',
            'payment_status' => 'paid',
            'status' => 'delivered',
            'customer_name' => 'Marcela Gomez',
            'customer_email' => 'marcela@example.com',
            'customer_phone' => '3129876543',
            'shipping_address' => 'Calle 45 # 28-10',
            'shipping_city' => 'Bucaramanga',
            'shipping_department' => 'Santander',
        ]);

        $response = $this->actingAs($this->admin)->getJson('/admin/customers/buyer-details?phone=3129876543');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'phone' => '3129876543',
            'delivery_probability' => 'Segura',
            'delivered_count' => 1,
            'buyer_type' => 'Comprador Esporádico',
            'in_store_orders' => 1,
        ]);
    }

    public function test_buyer_details_validates_phone_number(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/customers/buyer-details?phone=123');

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Debes ingresar un número de celular válido para poder ver el historial del comprador.',
        ]);
    }
}
