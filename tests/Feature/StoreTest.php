<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Category $category;
    protected Product $product;
    protected Coupon $coupon;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@tienda.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $this->customer = User::create([
            'name' => 'Cliente Test',
            'email' => 'cliente@tienda.com',
            'password' => bcrypt('password'),
            'is_admin' => false,
            'phone' => '+57 300 123 4567',
            'address' => 'Carrera 15 # 85-30',
            'city' => 'Bogotá D.C.',
            'postal_code' => '110221',
        ]);

        $this->category = Category::create([
            'name' => 'Tecnología',
            'slug' => 'tecnologia',
            'description' => 'Dispositivos electrónicos',
            'is_featured' => true,
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Audífonos Bluetooth Pro',
            'slug' => 'audifonos-bluetooth-pro',
            'sku' => 'AUD-001',
            'short_description' => 'Audífonos de alta fidelidad',
            'description' => 'Descripción completa del producto con specs.',
            'price' => 250000.00,
            'compare_price' => 320000.00,
            'stock' => 15,
            'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e',
            'is_featured' => true,
            'is_active' => true,
        ]);

        $this->coupon = Coupon::create([
            'code' => 'DESCUENTO10',
            'type' => 'percent',
            'value' => 10,
            'min_amount' => 100000,
            'is_active' => true,
        ]);
    }

    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('NovaStore');
        $response->assertSee('Audífonos Bluetooth Pro');
    }

    public function test_shop_catalog_and_filters_work(): void
    {
        $response = $this->get('/shop');
        $response->assertStatus(200);
        $response->assertSee('Audífonos Bluetooth Pro');

        // Filter by category
        $responseCat = $this->get('/shop?category=tecnologia');
        $responseCat->assertStatus(200);
        $responseCat->assertSee('Audífonos Bluetooth Pro');

        // Filter by search
        $responseSearch = $this->get('/shop?q=Bluetooth');
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Audífonos Bluetooth Pro');
    }

    public function test_product_detail_page_loads(): void
    {
        $response = $this->get('/product/' . $this->product->slug);
        $response->assertStatus(200);
        $response->assertSee('Audífonos Bluetooth Pro');
        $response->assertSee('AUD-001');
    }

    public function test_cart_operations(): void
    {
        // Add to cart
        $response = $this->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
        $response->assertSessionHas('success');

        // View Cart
        $cartView = $this->get('/cart');
        $cartView->assertStatus(200);
        $cartView->assertSee('Audífonos Bluetooth Pro');

        // Apply Coupon
        $couponRes = $this->post('/cart/coupon', [
            'code' => 'DESCUENTO10',
        ]);
        $couponRes->assertSessionHas('success');

        // Update quantity
        $updateRes = $this->post('/cart/update', [
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);
        $updateRes->assertSessionHas('success');
    }

    public function test_checkout_and_order_creation_flow(): void
    {
        $cart = [
            $this->product->id => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'price' => (float) $this->product->price,
                'image' => $this->product->image,
                'sku' => $this->product->sku,
                'quantity' => 1,
                'total' => (float) $this->product->price,
            ]
        ];

        // Place Order with PSE
        $response = $this->actingAs($this->customer)
            ->withSession(['cart_items' => $cart])
            ->post('/checkout/process', [
                'customer_name' => 'Cliente Test',
                'customer_email' => 'cliente@tienda.com',
                'customer_phone' => '+57 300 123 4567',
                'recipient_dni' => '1020304050',
                'shipping_address' => 'Carrera 15 # 85-30',
                'shipping_city' => 'Bogotá D.C.',
                'shipping_department' => 'Cundinamarca',
                'shipping_postal_code' => '110221',
                'payment_method' => 'pse',
            ]);

        $this->assertDatabaseHas('orders', [
            'customer_email' => 'cliente@tienda.com',
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'payment_method' => 'pse',
        ]);

        $order = Order::where('customer_email', 'cliente@tienda.com')->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('checkout.success', $order->order_number));

        // Order success page
        $successResponse = $this->actingAs($this->customer)->get(route('checkout.success', $order->order_number));
        $successResponse->assertStatus(200);
        $successResponse->assertSee($order->order_number);
    }

    public function test_admin_dashboard_and_product_crud(): void
    {
        // Non-admin cannot access admin panel
        $nonAdminRes = $this->actingAs($this->customer)->get('/admin');
        $nonAdminRes->assertStatus(403);

        // Admin can access
        $adminRes = $this->actingAs($this->admin)->get('/admin');
        $adminRes->assertStatus(200);
        $adminRes->assertSee('Panel de Control');

        // Admin create product in COP
        $createRes = $this->actingAs($this->admin)->post('/admin/products', [
            'category_id' => $this->category->id,
            'name' => 'Nuevo Producto Test',
            'price' => 175000.00,
            'stock' => 20,
            'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30',
        ]);
        $createRes->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', ['name' => 'Nuevo Producto Test']);
    }
}
