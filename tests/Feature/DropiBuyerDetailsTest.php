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

        \App\Models\DropiToken::create([
            'store' => 'Tienda Test',
            'token' => 'header.eyJzdWIiOiIzNjE4NjQiLCJ1c2VyX2lkIjozNjE4NjR9.signature',
            'is_valid' => true,
            'user_id_dropi' => '361864',
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
            'has_history' => true,
            'phone' => '3129876543',
            'delivery_probability' => 'Segura',
            'delivered_count' => 1,
            'buyer_type' => 'Comprador Esporádico',
            'in_store_orders' => 1,
        ]);
    }

    public function test_buyer_details_returns_no_history_message_for_unknown_phone(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://api-v2.dropi.co/bff/customers/fingerprint/v2*' => \Illuminate\Support\Facades\Http::response([
                'isSuccess' => true,
                'data' => [
                    'total_orders' => 0,
                    'delivered_orders' => 0,
                    'returns_orders' => 0,
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->getJson('/admin/customers/buyer-details?phone=3122154598');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'has_history' => false,
            'phone' => '3122154598',
            'message' => 'No se encontró historial de compras para este número de teléfono.',
        ]);
    }

    public function test_buyer_details_fetches_positive_profile_from_dropi_bff_api(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://api-v2.dropi.co/bff/customers/fingerprint/v2*' => \Illuminate\Support\Facades\Http::response([
                'isSuccess' => true,
                'data' => [
                    'buyer_type' => 'Comprador Esporádico',
                    'score' => 'Segura',
                    'total_orders' => 1,
                    'delivered_orders' => 1,
                    'returns_orders' => 0,
                    'in_transit_orders' => 0,
                    'in_other_stores_orders' => 1,
                    'carriers_breakdown' => [
                        ['name' => 'TCC', 'in_transit' => 0, 'returns' => 0, 'delivered' => 1],
                    ],
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->getJson('/admin/customers/buyer-details?phone=3103761814');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'has_history' => true,
            'phone' => '3103761814',
            'buyer_type' => 'Comprador Esporádico',
            'delivery_probability' => 'Segura',
            'delivered_count' => 1,
        ]);
    }

    public function test_buyer_details_fetches_negative_profile_from_dropi_bff_api(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://api-v2.dropi.co/bff/customers/fingerprint/v2*' => \Illuminate\Support\Facades\Http::response([
                'isSuccess' => true,
                'data' => [
                    'buyer_type' => 'Comprador Frecuente',
                    'score' => 'Riesgosa',
                    'total_orders' => 11,
                    'delivered_orders' => 0,
                    'returns_orders' => 11,
                    'in_transit_orders' => 0,
                    'in_other_stores_orders' => 11,
                    'carriers_breakdown' => [
                        ['name' => 'ENVIA', 'in_transit' => 0, 'returns' => 6, 'delivered' => 0],
                        ['name' => 'INTERRAPIDISIMO', 'in_transit' => 0, 'returns' => 3, 'delivered' => 0],
                    ],
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->getJson('/admin/customers/buyer-details?phone=3208901234');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'has_history' => true,
            'phone' => '3208901234',
            'buyer_type' => 'Comprador Frecuente',
            'delivery_probability' => 'Riesgosa',
            'returns_count' => 11,
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
