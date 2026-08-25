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
        $response->assertSee('Visualizar');
        $response->assertSee('Importar Productos Dropi');
        $response->assertSee('DRP-10145');
        $response->assertSee('Smartwatch Ultra 8');
    }

    public function test_admin_can_import_dropi_product_with_one_click(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/dropi/products/import', [
            'dropi_id' => 'DRP-10145',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'dropi_id' => 'DRP-10145',
            'price' => 89900.00,
            'wholesale_price' => 45000.00,
            'profit_margin' => 44900.00,
            'is_dropi_product' => true,
            'is_dropshipping' => true,
        ]);
    }

    public function test_admin_can_import_all_dropi_products_at_once(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/dropi/products/import-all');
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $count = Product::where('is_dropi_product', true)->count();
        $this->assertGreaterThan(5, $count);
    }
}
