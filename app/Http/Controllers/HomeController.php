<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredCategories = Category::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        $featuredProducts = Product::with('category')
            ->active()
            ->featured()
            ->latest()
            ->take(8)
            ->get();

        $newArrivals = Product::with('category')
            ->active()
            ->latest()
            ->take(8)
            ->get();

        $bestDeals = Product::with('category')
            ->active()
            ->whereNotNull('compare_price')
            ->whereColumn('compare_price', '>', 'price')
            ->orderByRaw('((compare_price - price) / compare_price) DESC')
            ->take(4)
            ->get();

        $bestSellers = Product::with('category')
            ->active()
            ->orderBy('sales_count', 'desc')
            ->take(4)
            ->get();

        return view('home', compact(
            'featuredCategories',
            'featuredProducts',
            'newArrivals',
            'bestDeals',
            'bestSellers'
        ));
    }
}
