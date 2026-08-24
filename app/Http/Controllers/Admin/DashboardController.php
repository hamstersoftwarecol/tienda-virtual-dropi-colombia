<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSales = (float) Order::where('status', '!=', 'cancelled')->sum('total');
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalProducts = Product::count();
        $lowStockProducts = Product::where('stock', '<=', 5)->count();
        $totalCustomers = User::where('is_admin', false)->count();

        $recentOrders = Order::with('user')
            ->latest()
            ->take(6)
            ->get();

        $topSellingProducts = Product::with('category')
            ->orderBy('sales_count', 'desc')
            ->take(5)
            ->get();

        // Monthly sales summary for chart
        $monthlySales = Order::where('status', '!=', 'cancelled')
            ->selectRaw('strftime("%Y-%m", created_at) as month, SUM(total) as revenue, COUNT(id) as orders')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(6)
            ->get()
            ->reverse()
            ->values();

        return view('admin.dashboard', compact(
            'totalSales',
            'totalOrders',
            'pendingOrders',
            'totalProducts',
            'lowStockProducts',
            'totalCustomers',
            'recentOrders',
            'topSellingProducts',
            'monthlySales'
        ));
    }
}
