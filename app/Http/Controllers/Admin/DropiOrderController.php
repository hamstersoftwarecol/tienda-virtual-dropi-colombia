<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DropiService;
use Illuminate\Http\Request;

class DropiOrderController extends Controller
{
    protected DropiService $dropiService;

    public function __construct(DropiService $dropiService)
    {
        $this->dropiService = $dropiService;
    }

    public function index(Request $request)
    {
        $query = Order::with('items');

        if ($request->has('dropi_status') && $request->dropi_status != '') {
            $query->where('dropi_status', $request->dropi_status);
        }

        if ($request->has('q') && $request->q != '') {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('order_number', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('recipient_dni', 'like', "%{$q}%")
                    ->orWhere('dropi_guide_number', 'like', "%{$q}%");
            });
        }

        $orders = $query->latest()->paginate(15);
        return view('admin.dropi.orders', compact('orders'));
    }

    public function dispatch(Request $request, Order $order)
    {
        $carrier = $request->input('carrier', $order->shipping_carrier ?: 'Coordinadora');
        $result = $this->dropiService->dispatchOrderToDropi($order, $carrier);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }

    public function syncTracking(Request $request, Order $order)
    {
        $nextStatus = $this->dropiService->advanceDropiTracking($order);
        return back()->with('success', "Estado Dropi actualizado a: {$order->dropi_status_label} (Guía: {$order->dropi_guide_number})");
    }
}
