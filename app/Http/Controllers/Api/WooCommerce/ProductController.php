<?php

namespace App\Http\Controllers\Api\WooCommerce;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Format a Laravel Product model into WooCommerce v3 JSON object
     */
    protected function formatWooCommerceProduct(Product $product): array
    {
        $images = [];
        if ($product->image) {
            $images[] = [
                'id' => $product->id * 10,
                'date_created' => $product->created_at?->toIso8601String(),
                'src' => $product->image,
                'name' => $product->name,
                'alt' => $product->name,
            ];
        }

        if ($product->images && is_array($product->images)) {
            foreach ($product->images as $idx => $img) {
                if ($img !== $product->image) {
                    $images[] = [
                        'id' => ($product->id * 10) + $idx + 1,
                        'date_created' => $product->created_at?->toIso8601String(),
                        'src' => $img,
                        'name' => $product->name . ' - ' . ($idx + 1),
                        'alt' => $product->name,
                    ];
                }
            }
        }

        $categories = [];
        if ($product->category) {
            $categories[] = [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ];
        }

        $regularPrice = (string) ($product->compare_price ?: $product->price);
        $salePrice = $product->compare_price ? (string) $product->price : '';

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'permalink' => url('/product/' . $product->slug),
            'date_created' => $product->created_at?->toIso8601String(),
            'date_created_gmt' => $product->created_at?->toIso8601String(),
            'date_modified' => $product->updated_at?->toIso8601String(),
            'type' => 'simple',
            'status' => $product->is_active ? 'publish' : 'draft',
            'featured' => (bool) $product->is_featured,
            'catalog_visibility' => 'visible',
            'description' => $product->description ?: '',
            'short_description' => $product->short_description ?: '',
            'sku' => $product->sku ?: '',
            'price' => (string) $product->price,
            'regular_price' => $regularPrice,
            'sale_price' => $salePrice,
            'date_on_sale_from' => null,
            'date_on_sale_to' => null,
            'price_html' => '<span class="woocommerce-Price-amount amount">$ ' . number_format($product->price, 0, ',', '.') . ' COP</span>',
            'on_sale' => (bool) ($product->compare_price && $product->compare_price > $product->price),
            'purchasable' => true,
            'total_sales' => $product->sales_count ?: 0,
            'virtual' => false,
            'downloadable' => false,
            'downloads' => [],
            'download_limit' => -1,
            'download_expiry' => -1,
            'external_url' => '',
            'button_text' => '',
            'tax_status' => 'taxable',
            'tax_class' => '',
            'manage_stock' => true,
            'stock_quantity' => $product->stock,
            'in_stock' => $product->stock > 0,
            'stock_status' => $product->stock > 0 ? 'instock' : 'outofstock',
            'backorders' => 'no',
            'backorders_allowed' => false,
            'backordered' => false,
            'weight' => '0.5',
            'dimensions' => [
                'length' => '15',
                'width' => '10',
                'height' => '5',
            ],
            'shipping_required' => true,
            'shipping_taxable' => true,
            'shipping_class' => '',
            'shipping_class_id' => 0,
            'reviews_allowed' => true,
            'average_rating' => (string) ($product->rating ?: '5.00'),
            'rating_count' => $product->reviews_count ?: 0,
            'related_ids' => [],
            'upsell_ids' => [],
            'cross_sell_ids' => [],
            'parent_id' => 0,
            'purchase_note' => '',
            'categories' => $categories,
            'tags' => [],
            'images' => $images,
            'attributes' => [],
            'default_attributes' => [],
            'variations' => [],
            'grouped_products' => [],
            'menu_order' => 0,
            'meta_data' => [
                ['id' => 1, 'key' => '_wholesale_price', 'value' => (string) ($product->wholesale_price ?: $product->price * 0.6)],
                ['id' => 2, 'key' => '_dropi_id', 'value' => (string) $product->dropi_id],
                ['id' => 3, 'key' => '_supplier_id', 'value' => (string) $product->supplier_id],
                ['id' => 4, 'key' => '_is_dropshipping', 'value' => $product->is_dropshipping ? 'yes' : 'no'],
            ],
            '_links' => [
                'self' => [['href' => url('/wp-json/wc/v3/products/' . $product->id)]],
                'collection' => [['href' => url('/wp-json/wc/v3/products')]],
            ],
        ];
    }

    /**
     * List products (GET /wp-json/wc/v3/products)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category', 'supplier');

        if ($request->has('search') && $request->search != '') {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%");
            });
        }

        if ($request->has('sku') && $request->sku != '') {
            $query->where('sku', $request->sku);
        }

        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        if ($request->has('status') && $request->status === 'publish') {
            $query->where('is_active', true);
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 10)));
        $page = max(1, (int) $request->input('page', 1));

        $total = $query->count();
        $products = $query->forPage($page, $perPage)->get();

        $totalPages = ceil($total / $perPage);

        $data = $products->map(fn($p) => $this->formatWooCommerceProduct($p));

        return response()->json($data)
            ->header('X-WP-Total', $total)
            ->header('X-WP-TotalPages', $totalPages);
    }

    /**
     * Get single product (GET /wp-json/wc/v3/products/{id})
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with('category', 'supplier')->findOrFail($id);
        return response()->json($this->formatWooCommerceProduct($product));
    }

    /**
     * Create product via WooCommerce API (POST /wp-json/wc/v3/products)
     */
    public function store(Request $request): JsonResponse
    {
        $name = $request->input('name', 'Producto WooCommerce');
        $price = (float) ($request->input('regular_price') ?: $request->input('price', 0));
        $salePrice = (float) $request->input('sale_price', 0);
        $stock = (int) $request->input('stock_quantity', 10);
        $sku = $request->input('sku', 'WC-' . strtoupper(Str::random(8)));
        $description = $request->input('description', '');
        $shortDescription = $request->input('short_description', '');

        // Images handling
        $images = $request->input('images', []);
        $mainImage = !empty($images[0]['src']) ? $images[0]['src'] : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800';
        $imageList = array_column($images, 'src');

        // Category handling
        $categories = $request->input('categories', []);
        $categoryId = null;
        if (!empty($categories[0]['name'])) {
            $cat = Category::firstOrCreate(
                ['slug' => Str::slug($categories[0]['name'])],
                ['name' => $categories[0]['name'], 'is_active' => true]
            );
            $categoryId = $cat->id;
        } elseif (!empty($categories[0]['id'])) {
            $categoryId = $categories[0]['id'];
        }

        if (!$categoryId) {
            $defaultCat = Category::firstOrCreate(
                ['slug' => 'general'],
                ['name' => 'General', 'is_active' => true]
            );
            $categoryId = $defaultCat->id;
        }

        // Check meta_data for Dropi keys
        $meta = $request->input('meta_data', []);
        $dropiId = null;
        $wholesalePrice = null;
        foreach ($meta as $m) {
            if (isset($m['key']) && $m['key'] === '_dropi_id') {
                $dropiId = $m['value'];
            }
            if (isset($m['key']) && $m['key'] === '_wholesale_price') {
                $wholesalePrice = (float) $m['value'];
            }
        }

        $finalPrice = $salePrice > 0 ? $salePrice : $price;
        $comparePrice = $salePrice > 0 ? $price : null;

        $product = Product::create([
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(5),
            'sku' => $sku,
            'dropi_id' => $dropiId,
            'short_description' => $shortDescription,
            'description' => $description,
            'price' => $finalPrice,
            'compare_price' => $comparePrice,
            'wholesale_price' => $wholesalePrice ?: ($finalPrice * 0.6),
            'profit_margin' => $finalPrice - ($wholesalePrice ?: ($finalPrice * 0.6)),
            'stock' => $stock,
            'image' => $mainImage,
            'images' => $imageList ?: [$mainImage],
            'is_dropshipping' => (bool) $dropiId,
            'is_active' => true,
        ]);

        return response()->json($this->formatWooCommerceProduct($product), 201);
    }

    /**
     * Update product via WooCommerce API (PUT /wp-json/wc/v3/products/{id})
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        if ($request->has('name')) $product->name = $request->name;
        if ($request->has('price')) $product->price = (float) $request->price;
        if ($request->has('regular_price')) $product->compare_price = (float) $request->regular_price;
        if ($request->has('stock_quantity')) $product->stock = (int) $request->stock_quantity;
        if ($request->has('description')) $product->description = $request->description;
        if ($request->has('short_description')) $product->short_description = $request->short_description;
        if ($request->has('sku')) $product->sku = $request->sku;

        $product->save();

        return response()->json($this->formatWooCommerceProduct($product));
    }

    /**
     * Delete product (DELETE /wp-json/wc/v3/products/{id})
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $data = $this->formatWooCommerceProduct($product);
        $product->delete();

        return response()->json($data);
    }
}
