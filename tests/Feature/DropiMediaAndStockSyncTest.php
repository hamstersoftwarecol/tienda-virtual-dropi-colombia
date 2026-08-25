<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DropiToken;
use App\Models\Product;
use App\Models\User;
use App\Services\DropiApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DropiMediaAndStockSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_downloads_and_saves_images_and_gifs_locally(): void
    {
        Http::fake([
            'https://example.com/demo.gif' => Http::response('fake-gif-content', 200, ['Content-Type' => 'image/gif']),
            'https://example.com/photo.jpg' => Http::response('fake-jpg-content', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $service = app(DropiApiService::class);

        $localGifUrl = $service->downloadAndSaveDropiMedia('https://example.com/demo.gif', 9999, 'anim');
        $this->assertStringStartsWith('/storage/products/9999/anim_', $localGifUrl);
        $this->assertStringEndsWith('.gif', $localGifUrl);

        $localJpgUrl = $service->downloadAndSaveDropiMedia('https://example.com/photo.jpg', 9999, 'main');
        $this->assertStringStartsWith('/storage/products/9999/main_', $localJpgUrl);
        $this->assertStringEndsWith('.jpg', $localJpgUrl);
    }

    public function test_localizes_gifs_and_images_in_description_html(): void
    {
        Http::fake([
            'https://example.com/demo.gif' => Http::response('fake-gif-content', 200, ['Content-Type' => 'image/gif']),
        ]);

        $service = app(DropiApiService::class);
        $html = '<p>Excelente producto</p><img src="https://example.com/demo.gif" alt="Demo">';

        $localizedHtml = $service->localizeDescriptionMedia($html, 8888);

        $this->assertStringNotContainsString('https://example.com/demo.gif', $localizedHtml);
        $this->assertStringContainsString('/storage/products/8888/desc_', $localizedHtml);
        $this->assertStringContainsString('.gif', $localizedHtml);
    }

    public function test_syncs_product_stock_and_marks_out_of_stock_if_zero(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'General', 'slug' => 'general', 'is_active' => true]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cepillo Vapor Mascota',
            'slug' => 'cepillo-vapor-mascota',
            'sku' => 'DRP-CEP01',
            'dropi_id' => '2236371',
            'price' => 35000,
            'stock' => 15,
            'is_active' => true,
        ]);

        DropiToken::create([
            'token' => 'fake_token',
            'store' => 'tienda.hamstersoftware.com',
            'is_valid' => true,
        ]);

        // Fake Dropi API response with 0 stock
        Http::fake([
            'https://api.dropi.co/integrations/products/v2/2236371' => Http::response([
                'isSuccess' => true,
                'objects' => [
                    'id' => 2236371,
                    'name' => 'Cepillo Vapor Mascota',
                    'sale_price' => 13500,
                    'suggested_price' => 33500,
                    'stock' => 0,
                    'warehouse_product' => [
                        ['stock' => 0],
                    ],
                ]
            ], 200),
        ]);

        $service = app(DropiApiService::class);
        $syncResult = $service->syncProductStock($product);

        $this->assertTrue($syncResult['success']);
        $this->assertEquals(0, $syncResult['current_stock']);

        $product->refresh();
        $this->assertEquals(0, $product->stock);

        // Test web route for sync
        $response = $this->actingAs($admin)->post(route('admin.dropi.products.sync_stock'));
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
