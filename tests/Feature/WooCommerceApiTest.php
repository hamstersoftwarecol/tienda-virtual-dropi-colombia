<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\WooCommerceApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WooCommerceApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected WooCommerceApiKey $apiKey;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $this->apiKey = WooCommerceApiKey::create([
            'user_id' => $this->admin->id,
            'description' => 'Dropi Test Key',
            'permissions' => 'read_write',
            'consumer_key' => 'ck_test_1234567890abcdef',
            'consumer_secret' => 'cs_test_1234567890abcdef',
            'truncated_key' => 'cdef',
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Tecnología Dropi',
            'slug' => 'tecnologia-dropi',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Micrófono Inalámbrico Dropi Pro',
            'slug' => 'microfono-inalambrico-dropi-pro',
            'sku' => 'MIC-DRP-01',
            'price' => 75000.00,
            'stock' => 50,
            'image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df',
            'is_active' => true,
        ]);
    }

    public function test_wordpress_rest_index_is_accessible(): void
    {
        $response = $this->getJson('/wp-json');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'name',
            'description',
            'namespaces',
            'routes',
        ]);
        $this->assertContains('wc/v3', $response->json('namespaces'));
    }

    public function test_woocommerce_system_status_endpoint(): void
    {
        $response = $this->getJson('/wp-json/wc/v3/system_status?consumer_key=' . $this->apiKey->consumer_key . '&consumer_secret=' . $this->apiKey->consumer_secret);
        $response->assertStatus(200);
        $response->assertJsonPath('environment.version', '9.0.2');
        $response->assertJsonPath('environment.wp_version', '6.5.4');
        $response->assertJsonPath('settings.currency', 'COP');
    }

    public function test_woocommerce_products_endpoint_lists_products(): void
    {
        $response = $this->getJson('/wp-json/wc/v3/products?consumer_key=' . $this->apiKey->consumer_key . '&consumer_secret=' . $this->apiKey->consumer_secret);
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Micrófono Inalámbrico Dropi Pro',
            'sku' => 'MIC-DRP-01',
            'price' => '75000.00',
        ]);
    }

    public function test_woocommerce_products_create_endpoint(): void
    {
        $response = $this->postJson('/wp-json/wc/v3/products?consumer_key=' . $this->apiKey->consumer_key . '&consumer_secret=' . $this->apiKey->consumer_secret, [
            'name' => 'Smartwatch Dropi Connect',
            'regular_price' => '120000',
            'sale_price' => '99000',
            'stock_quantity' => 25,
            'sku' => 'SMT-WC-02',
            'meta_data' => [
                ['key' => '_dropi_id', 'value' => 'DRP-998811'],
                ['key' => '_wholesale_price', 'value' => '60000'],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('name', 'Smartwatch Dropi Connect');
        $response->assertJsonPath('price', '99000.00');

        $this->assertDatabaseHas('products', [
            'name' => 'Smartwatch Dropi Connect',
            'sku' => 'SMT-WC-02',
        ]);
    }

    public function test_woocommerce_orders_create_and_list_endpoints(): void
    {
        // 1. Create order via WooCommerce API (as Dropi would)
        $response = $this->postJson('/wp-json/wc/v3/orders?consumer_key=' . $this->apiKey->consumer_key . '&consumer_secret=' . $this->apiKey->consumer_secret, [
            'payment_method' => 'cod',
            'billing' => [
                'first_name' => 'Julián',
                'last_name' => 'Restrepo',
                'email' => 'julian@correo.com',
                'phone' => '+57 312 333 4455',
                'address_1' => 'Calle 85 # 11-20',
                'city' => 'Bogotá D.C.',
                'state' => 'Cundinamarca',
            ],
            'shipping' => [
                'first_name' => 'Julián',
                'last_name' => 'Restrepo',
                'address_1' => 'Calle 85 # 11-20',
                'city' => 'Bogotá D.C.',
                'state' => 'Cundinamarca',
            ],
            'line_items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'price' => $this->product->price,
                ],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('billing.first_name', 'Julián');

        $this->assertDatabaseHas('orders', [
            'customer_email' => 'julian@correo.com',
            'shipping_city' => 'Bogotá D.C.',
        ]);

        // 2. List orders
        $listResponse = $this->getJson('/wp-json/wc/v3/orders?consumer_key=' . $this->apiKey->consumer_key . '&consumer_secret=' . $this->apiKey->consumer_secret);
        $listResponse->assertStatus(200);
        $listResponse->assertJsonFragment(['email' => 'julian@correo.com']);
    }

    public function test_unauthenticated_request_is_rejected_when_keys_exist(): void
    {
        $response = $this->getJson('/wp-json/wc/v3/products');
        $response->assertStatus(401);
        $response->assertJsonPath('code', 'woocommerce_rest_cannot_view');
    }
}
