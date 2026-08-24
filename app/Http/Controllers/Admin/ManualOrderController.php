<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\DropiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManualOrderController extends Controller
{
    protected DropiService $dropiService;

    public function __construct(DropiService $dropiService)
    {
        $this->dropiService = $dropiService;
    }

    public function create()
    {
        $products = Product::where('is_active', true)->where('stock', '>', 0)->get();
        $customers = User::where('is_admin', false)->latest()->get();
        return view('admin.orders.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:50',
            'recipient_dni' => 'required|string|max:30',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_department' => 'required|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:20',
            'shipping_carrier' => 'required|string',
            'payment_method' => 'required|string',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'shipping_cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'order_notes' => 'nullable|string|max:1000',
            'dispatch_to_dropi' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $subtotal = 0;
            $itemsToCreate = [];

            foreach ($request->products as $item) {
                $product = Product::findOrFail($item['id']);
                $qty = (int) $item['quantity'];
                $price = !empty($item['custom_price']) ? (float) $item['custom_price'] : $product->price;
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'product_image' => $product->image,
                    'price' => $price,
                    'quantity' => $qty,
                    'total' => $lineTotal,
                ];

                // Decrement stock
                $product->decrement('stock', $qty);
                $product->increment('sales_count', $qty);
            }

            $discount = (float) $request->input('discount', 0);
            $shippingCost = (float) $request->input('shipping_cost', ($subtotal >= 150000 ? 0 : 12000));
            $taxableSubtotal = max(0, $subtotal - $discount);
            $tax = round($taxableSubtotal * 0.19, 0);
            $total = $taxableSubtotal + $shippingCost + $tax;

            // Link to user if email matches
            $existingUser = User::where('email', $request->customer_email)->first();

            $orderNumber = 'ORD-' . strtoupper(Str::random(6)) . '-' . date('Ymd');

            $order = Order::create([
                'user_id' => $existingUser?->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'total' => $total,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'recipient_dni' => $request->recipient_dni,
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_department' => $request->shipping_department,
                'shipping_postal_code' => $request->shipping_postal_code,
                'shipping_carrier' => $request->shipping_carrier,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'credit_card' ? 'paid' : 'pending',
                'order_notes' => $request->order_notes,
            ]);

            foreach ($itemsToCreate as $it) {
                $it['order_id'] = $order->id;
                OrderItem::create($it);
            }

            DB::commit();

            // Auto dispatch to Dropi if selected
            if ($request->boolean('dispatch_to_dropi')) {
                $this->dropiService->dispatchOrderToDropi($order, $request->shipping_carrier);
            }

            return redirect()->route('admin.orders.show', $order->id)
                ->with('success', "¡Pedido #{$order->order_number} creado exitosamente con sus datos de comprador y transportadora!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al generar el pedido: ' . $e->getMessage());
        }
    }
}
