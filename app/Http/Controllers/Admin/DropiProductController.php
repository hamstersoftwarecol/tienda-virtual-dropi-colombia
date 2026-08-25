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
        $search = $request->input('search', '');

        // Fetch products from Dropi API
        $apiResult = $this->dropiApi->fetchDropiProducts($search, (int) $request->input('page', 1));
        $dropiProducts = $apiResult['products'] ?? [];
        $apiMessage = $apiResult['message'] ?? '';
        $apiSuccess = $apiResult['success'] ?? false;

        // Local imported products
        $importedDropiIds = Product::whereNotNull('dropi_id')
            ->pluck('dropi_id')
            ->toArray();

        $importedCount = count($importedDropiIds);

        return view('admin.dropi.products', compact(
            'dropiToken',
            'categories',
            'dropiProducts',
            'importedDropiIds',
            'importedCount',
            'search',
            'apiMessage',
            'apiSuccess'
        ));
    }

    public function import(Request $request)
    {
        $request->validate([
            'product' => 'required',
            'product_name' => 'required|string|max:255',
            'product_price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $result = $this->dropiApi->importProduct($request->all());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
