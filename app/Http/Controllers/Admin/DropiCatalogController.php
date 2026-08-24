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
        $filters = $request->only(['q', 'supplier_id', 'category', 'is_imported', 'min_price', 'max_price', 'sort']);
        $supplierProducts = $this->dropiService->searchSupplierProducts($filters);
        $suppliers = Supplier::where('is_active', true)->get();
        $categories = Category::all();
        $settings = DropiSetting::getSettings();

        return view('admin.dropi.catalog', compact('supplierProducts', 'suppliers', 'categories', 'settings'));
    }

    public function import(Request $request, int $id)
    {
        $request->validate([
            'sale_price' => 'nullable|numeric|min:0',
            'markup_percent' => 'nullable|integer|min:1|max:500',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $product = $this->dropiService->importProduct(
            $id,
            $request->sale_price,
            $request->markup_percent,
            $request->category_id
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "¡Producto '{$product->name}' importado con éxito al catálogo!",
                'product' => $product,
            ]);
        }

        return back()->with('success', "¡Producto '{$product->name}' importado exitosamente a tu tienda virtual!");
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
