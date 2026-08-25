<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DropiToken;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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

    public function test_admin_can_access_dropi_products_page_and_see_api_products(): void
    {
        Http::fake([
            'https://api.dropi.co/integrations/products/index' => Http::response([
                'isSuccess' => true,
                'objects' => [
                    [
                        'id' => 99182,
                        'name' => 'Producto Real Dropi API 100%',
                        'price' => 50000.00,
                        'suggested_price' => 95000.00,
                        'stock' => 50,
                        'photos' => [
                            ['urlS3' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30']
                        ],
                        'categories' => [
                            ['name' => 'Tecnología']
                        ],
                    ]
                ]
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dropi/products');
        $response->assertStatus(200);
        $response->assertSee('Catálogo en Vivo de Dropi');
        $response->assertSee('Producto Real Dropi API 100%');
        $response->assertSee('99182');
    }

    public function test_admin_can_import_real_api_product(): void
    {
        Http::fake([
            'https://api.dropi.co/integrations/products/v2/99182' => Http::response([
                'isSuccess' => true,
                'objects' => [
                    'id' => 99182,
                    'name' => 'Producto Real Dropi API 100%',
                    'price' => 50000.00,
                    'suggested_price' => 95000.00,
                    'stock' => 50,
                    'description' => 'Descripción directa de Dropi API',
                    'photos' => [
                        ['urlS3' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30']
                    ],
                    'categories' => [
                        ['name' => 'Tecnología']
                    ],
                ]
            ], 200),
            'https://api.dropi.co/integrations/importlist/importstore/1' => Http::response(['isSuccess' => true], 200),
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/dropi/products/import', [
            'dropi_id' => '99182',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'dropi_id' => '99182',
            'name' => 'Producto Real Dropi API 100%',
            'price' => 95000.00,
            'wholesale_price' => 50000.00,
            'is_dropi_product' => true,
            'is_dropshipping' => true,
        ]);
    }

    public function test_admin_can_access_dropi_products_with_empty_or_null_search(): void
    {
        Http::fake([
            'https://api.dropi.co/integrations/products/index' => Http::response([
                'isSuccess' => true,
                'objects' => []
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dropi/products?search=');
        $response->assertStatus(200);

        $response2 = $this->actingAs($this->admin)->get('/admin/dropi/products?search=waflera');
        $response2->assertStatus(200);
    }
}
