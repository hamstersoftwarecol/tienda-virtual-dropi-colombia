<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DropiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Supplier $supplier;
    protected SupplierProduct $supplierProduct;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@tienda.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Bodega Central Medellín',
            'slug' => 'bodega-central-medellin',
            'city' => 'Medellín',
            'department' => 'Antioquia',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Tecnología',
            'slug' => 'tecnologia',
            'is_active' => true,
        ]);

        $this->supplierProduct = SupplierProduct::create([
            'dropi_id' => 'DRP-9900',
            'supplier_id' => $this->supplier->id,
            'name' => 'Trípode LED Selfie Pro',
            'slug' => 'tripode-led-selfie-pro',
            'sku' => 'TRP-001',
            'short_description' => 'Trípode con aro de luz LED',
            'wholesale_price' => 40000.00,
            'suggested_price' => 70000.00,
            'stock' => 50,
            'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32',
            'category_name' => 'Tecnología',
            'is_imported' => false,
        ]);
    }

    public function test_admin_can_view_suppliers_and_catalog(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/suppliers');
        $response->assertStatus(200);
        $response->assertSee('Bodega Central Medellín');

        $catalogResponse = $this->actingAs($this->admin)->get('/admin/dropi/catalog');
        $catalogResponse->assertStatus(200);
        $catalogResponse->assertSee('Trípode LED Selfie Pro');
    }

    public function test_admin_can_import_supplier_product_with_custom_margin(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/dropi/catalog/import/' . $this->supplierProduct->id, [
            'sale_price' => 85000.00,
            'category_id' => $this->category->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'name' => 'Trípode LED Selfie Pro',
            'price' => 85000.00,
            'wholesale_price' => 40000.00,
            'profit_margin' => 45000.00,
            'is_dropshipping' => true,
        ]);

        $this->assertTrue($this->supplierProduct->fresh()->is_imported);
    }

    public function test_admin_can_generate_manual_order_with_buyer_details(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Producto Prueba',
            'slug' => 'producto-prueba',
            'price' => 100000.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/orders/create', [
            'customer_name' => 'Laura Gómez',
            'customer_email' => 'laura@correo.com',
            'customer_phone' => '+57 311 987 6543',
            'recipient_dni' => '1020304050',
            'shipping_address' => 'Carrera 43A # 1-50',
            'shipping_city' => 'Medellín',
            'shipping_department' => 'Antioquia',
            'shipping_carrier' => 'Coordinadora',
            'payment_method' => 'cash_on_delivery',
            'dispatch_to_dropi' => true,
            'products' => [
                [
                    'id' => $product->id,
                    'quantity' => 1,
                ]
            ],
        ]);

        $order = Order::where('customer_email', 'laura@correo.com')->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('admin.orders.show', $order->id));

        $this->assertEquals('1020304050', $order->recipient_dni);
        $this->assertEquals('Coordinadora', $order->shipping_carrier);
        $this->assertNotNull($order->dropi_guide_number);
    }

    public function test_dropi_order_tracking_advancement(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-99',
            'status' => 'pending',
            'subtotal' => 100000.00,
            'total' => 119000.00,
            'customer_name' => 'Comprador Test',
            'customer_email' => 'test@correo.com',
            'customer_phone' => '3001234567',
            'shipping_address' => 'Calle 10',
            'shipping_city' => 'Bogotá',
            'dropi_status' => 'in_preparation',
            'dropi_guide_number' => 'COORD-1234-CO',
            'shipping_carrier' => 'Coordinadora',
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/dropi/orders/' . $order->id . '/sync');
        $response->assertRedirect();

        $order->refresh();
        $this->assertEquals('dispatched', $order->dropi_status);
        $this->assertEquals('shipped', $order->status);
    }
}
