<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DropiToken;
use App\Models\Product;
use App\Services\DropiApiService;
use Illuminate\Http\Request;

class DropiProductController extends Controller
{
    protected DropiApiService $dropiApi;

    public function __construct(DropiApiService $dropiApi)
    {
        $this->dropiApi = $dropiApi;
    }

    public function index(Request $request)
    {
        $dropiToken = DropiToken::where('is_valid', true)->latest()->first() ?: DropiToken::latest()->first();
        $categories = Category::active()->get();
        $search = (string) ($request->input('search') ?? '');
        $selectedCategory = $request->input('category', 'all');

        // Fetch Dropi products catalog
        $apiResult = $this->dropiApi->getProducts(50, 0, $search, 'id', 'DESC', $selectedCategory);
        $dropiProducts = $apiResult['products'] ?? [];
        $apiMessage = $apiResult['message'] ?? '';
        $apiSource = $apiResult['source'] ?? 'catalog';

        // Local imported products
        $importedProducts = Product::whereNotNull('dropi_id')
            ->pluck('name', 'dropi_id')
            ->toArray();

        $importedDropiIds = array_keys($importedProducts);
        $importedCount = count($importedDropiIds);
        $totalDropiProducts = count($dropiProducts);

        return view('admin.dropi.products', compact(
            'dropiToken',
            'categories',
            'dropiProducts',
            'importedDropiIds',
            'importedCount',
            'totalDropiProducts',
            'search',
            'selectedCategory',
            'apiMessage',
            'apiSource'
        ));
    }

    public function import(Request $request)
    {
        $request->validate([
            'dropi_id' => 'required|string',
            'custom_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $customPrice = $request->filled('custom_price') ? (float) $request->custom_price : null;
        $categoryId = $request->filled('category_id') ? (int) $request->category_id : null;

        $result = $this->dropiApi->importProductById(
            $request->dropi_id,
            $customPrice,
            $categoryId
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function importAll(Request $request)
    {
        $result = $this->dropiApi->importAll();
        return back()->with('success', $result['message']);
    }
}
