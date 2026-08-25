<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DropiToken;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DropiProductImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@tienda.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Tecnología',
            'slug' => 'tecnologia',
            'is_active' => true,
        ]);

        DropiToken::create([
            'store' => 'Tienda Principal',
            'token' => 'sample.token.dropi',
            'sync' => 'AUTOMÁTICAMENTE',
            'create_prod_empr' => true,
            'is_valid' => true,
        ]);
    }

    public function test_admin_can_access_dropi_products_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dropi/products');
        $response->assertStatus(200);
        $response->assertSee('Explorador');
        $response->assertSee('Importador de Productos Dropi');
    }

    public function test_admin_can_import_dropi_product(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/dropi/products/import', [
            'product' => 'DRP-5599',
            'product_name' => 'Micrófono Lavalier Inalámbrico',
            'product_price' => 85000.00,
            'wholesale_price' => 42000.00,
            'suggested_price' => 89000.00,
            'sob_stock' => 30,
            'category_id' => $this->category->id,
            'image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df',
            'product_description' => 'Micrófono de alta sensibilidad compatible con iPhone y Android.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Micrófono Lavalier Inalámbrico',
            'dropi_id' => 'DRP-5599',
            'price' => 85000.00,
            'wholesale_price' => 42000.00,
            'profit_margin' => 43000.00,
            'is_dropi_product' => true,
            'is_dropshipping' => true,
        ]);
    }

    public function test_reimporting_updates_existing_dropi_product(): void
    {
        // First import
        $this->actingAs($this->admin)->post('/admin/dropi/products/import', [
            'product' => 'DRP-8800',
            'product_name' => 'Aro de Luz LED 12 Pulgadas',
            'product_price' => 60000.00,
            'category_id' => $this->category->id,
        ]);

        // Second import with new price & name
        $updateResponse = $this->actingAs($this->admin)->post('/admin/dropi/products/import', [
            'product' => 'DRP-8800',
            'product_name' => 'Aro de Luz LED 12 Pulgadas Pro',
            'product_price' => 75000.00,
            'category_id' => $this->category->id,
        ]);

        $updateResponse->assertRedirect();

        $this->assertDatabaseHas('products', [
            'dropi_id' => 'DRP-8800',
            'name' => 'Aro de Luz LED 12 Pulgadas Pro',
            'price' => 75000.00,
        ]);

        $this->assertEquals(1, Product::where('dropi_id', 'DRP-8800')->count());
    }
}
