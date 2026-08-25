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
            ->take(8)
            ->get();

        if ($featuredCategories->isEmpty()) {
            $featuredCategories = Category::where('is_active', true)->orderBy('name')->take(8)->get();
        }

        $allActiveProducts = Product::with('category')
            ->active()
            ->latest()
            ->take(24)
            ->get();

        $featuredProducts = Product::with('category')
            ->active()
            ->featured()
            ->latest()
            ->take(8)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = $allActiveProducts->take(8);
        }

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
            ->take(8)
            ->get();

        if ($bestDeals->isEmpty()) {
            $bestDeals = $allActiveProducts->take(6);
        }

        $bestSellers = Product::with('category')
            ->active()
            ->orderBy('sales_count', 'desc')
            ->take(8)
            ->get();

        if ($bestSellers->isEmpty()) {
            $bestSellers = $allActiveProducts->take(6);
        }

        // Hero Spotlight Product
        $heroSpotlight = $featuredProducts->first() ?: $allActiveProducts->first();

        return view('home', compact(
            'featuredCategories',
            'featuredProducts',
            'newArrivals',
            'bestDeals',
            'bestSellers',
            'heroSpotlight',
            'allActiveProducts'
        ));
    }
}
