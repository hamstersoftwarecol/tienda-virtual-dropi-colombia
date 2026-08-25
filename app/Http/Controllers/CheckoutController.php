<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $items = $this->cartService->getItems();
        if (empty($items)) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío.');
        }

        $subtotal = $this->cartService->getSubtotal();
        $coupon = $this->cartService->getCoupon();
        $discount = $this->cartService->getDiscount();
        $shipping = $this->cartService->getShipping();
        $tax = $this->cartService->getTax();
        $total = $this->cartService->getTotal();
        $user = auth()->user();

        return view('checkout.index', compact(
            'items',
            'subtotal',
            'coupon',
            'discount',
            'shipping',
            'tax',
            'total',
            'user'
        ));
    }

    public function process(Request $request)
    {
        $items = $this->cartService->getItems();
        if (empty($items)) {
            return redirect()->route('cart.index')->with('error', 'El carrito está vacío.');
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:50',
            'recipient_dni' => 'required|string|max:30',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_department' => 'required|string|max:100',
            'payment_method' => 'required|in:cash_on_delivery,bre_b,credit_card,pse,nequi,bank_transfer',
            'order_notes' => 'nullable|string|max:1000',
        ]);

        $subtotal = $this->cartService->getSubtotal();
        $coupon = $this->cartService->getCoupon();
        $discount = $this->cartService->getDiscount();
        $shipping = $this->cartService->getShipping();
        $tax = $this->cartService->getTax();
        $total = $this->cartService->getTotal();

        DB::beginTransaction();
        try {
            // Generate unique Order Number
            $orderNumber = 'ORD-' . strtoupper(Str::random(6)) . '-' . date('Ymd');
            $carrier = $request->shipping_carrier ?: 'Nacional (Coordinadora / Servientrega)';
            $postalCode = $request->shipping_postal_code ?: '110111';

            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => $orderNumber,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'coupon_code' => $coupon ? $coupon->code : null,
                'shipping_cost' => $shipping,
                'tax' => $tax,
                'total' => $total,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'recipient_dni' => $request->recipient_dni,
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_department' => $request->shipping_department,
                'shipping_postal_code' => $postalCode,
                'shipping_carrier' => $carrier,
                'order_notes' => $request->order_notes,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'product_sku' => $item['sku'] ?? null,
                    'product_image' => $item['image'] ?? null,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['total'],
                ]);

                // Reduce product stock & increment sales count
                $product = Product::find($item['id']);
                if ($product) {
                    $product->decrement('stock', $item['quantity']);
                    $product->increment('sales_count', $item['quantity']);
                }
            }

            DB::commit();

            // Clear Cart
            $this->cartService->clear();

            return redirect()->route('checkout.success', $order->order_number)
                ->with('success', '¡Tu orden ha sido procesada con éxito y registrada para despacho!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Ocurrió un error al procesar el pedido: ' . $e->getMessage());
        }
    }

    public function success(string $orderNumber)
    {
        $order = Order::with('items.product')->where('order_number', $orderNumber)->firstOrFail();

        // If authenticated and doesn't belong to current user (unless admin)
        if (auth()->check() && $order->user_id && $order->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403);
        }

        return view('checkout.success', compact('order'));
    }
}
