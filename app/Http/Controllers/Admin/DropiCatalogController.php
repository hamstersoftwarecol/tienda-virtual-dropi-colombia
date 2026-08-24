<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DropiSetting;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Services\DropiService;
use Illuminate\Http\Request;

class DropiCatalogController extends Controller
{
    protected DropiService $dropiService;

    public function __construct(DropiService $dropiService)
    {
        $this->dropiService = $dropiService;
    }

    public function index(Request $request)
    {
        $this->dropiService->ensureCatalogPopulated();

        $filters = $request->only(['q', 'supplier_id', 'category', 'is_imported', 'min_price', 'max_price', 'sort']);
        $supplierProducts = $this->dropiService->searchSupplierProducts($filters);
        $suppliers = Supplier::where('is_active', true)->get();
        $categories = Category::all();
        $settings = DropiSetting::getSettings();

        return view('admin.dropi.catalog', compact('supplierProducts', 'suppliers', 'categories', 'settings'));
    }

    public function syncApi(Request $request)
    {
        $result = $this->dropiService->syncFromDropiApi();

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('warning', $result['message']);
    }

    public function syncProduct(SupplierProduct $supplierProduct)
    {
        $result = $this->dropiService->syncSingleProduct($supplierProduct);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('warning', $result['message']);
    }

    public function import(Request $request, int $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'sale_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'markup_percent' => 'nullable|integer|min:1|max:500',
            'category_id' => 'nullable',
            'category_name' => 'nullable|string|max:100',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|string',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'sku' => 'nullable|string',
        ]);

        $customDetails = $request->only([
            'name',
            'image',
            'wholesale_price',
            'category_name',
            'stock',
            'short_description',
            'description',
            'sku',
        ]);

        $categoryId = is_numeric($request->category_id) && $request->category_id > 0 ? (int) $request->category_id : null;

        $product = $this->dropiService->importProduct(
            $id,
            $request->sale_price,
            $request->markup_percent,
            $categoryId,
            $customDetails
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "¡Producto '{$product->name}' importado con éxito al catálogo!",
                'product' => $product,
            ]);
        }

        return back()->with('success', "¡Producto '{$product->name}' importado exitosamente a tu tienda virtual con precio " . format_cop($product->price) . "!");
    }

    public function importCustom(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sale_price' => 'required|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'category_id' => 'nullable',
            'category_name' => 'nullable|string|max:100',
            'image' => 'nullable|string',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'sku' => 'nullable|string',
        ]);

        $product = $this->dropiService->importDirectProduct($request->all());

        return back()->with('success', "¡Producto Dropi '{$product->name}' importado y publicado exitosamente en tu tienda!");
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:supplier_products,id',
            'markup_percent' => 'nullable|integer|min:1|max:500',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $markup = $request->input('markup_percent', 40);
        $catId = $request->input('category_id');
        $count = $this->dropiService->bulkImport($request->product_ids, $markup, $catId);

        return back()->with('success', "¡Se importaron exitosamente {$count} productos a tu catálogo con un {$markup}% de margen!");
    }

    public function importAll(Request $request)
    {
        $request->validate([
            'markup_percent' => 'nullable|integer|min:1|max:500',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $markup = $request->input('markup_percent', 40);
        $catId = $request->input('category_id');
        $count = $this->dropiService->importAll($markup, $catId);

        return back()->with('success', "🚀 ¡Éxito! Se han importado todos los {$count} productos de Dropi a tu tienda virtual con un {$markup}% de margen de ganancia.");
    }
}
