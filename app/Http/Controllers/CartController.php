<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $items = $this->cartService->getItems();
        $subtotal = $this->cartService->getSubtotal();
        $coupon = $this->cartService->getCoupon();
        $discount = $this->cartService->getDiscount();
        $shipping = $this->cartService->getShipping();
        $tax = $this->cartService->getTax();
        $total = $this->cartService->getTotal();

        return view('cart.index', compact(
            'items',
            'subtotal',
            'coupon',
            'discount',
            'shipping',
            'tax',
            'total'
        ));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $quantity = (int) ($request->quantity ?? 1);

        if ($product->stock < 1) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lo sentimos, este producto está agotado.',
                ], 422);
            }
            return back()->with('error', 'Lo sentimos, este producto está agotado.');
        }

        $this->cartService->add($product, $quantity);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "¡{$product->name} agregado al carrito!",
                'cartCount' => $this->cartService->count(),
                'subtotal' => $this->cartService->getSubtotal(),
                'total' => $this->cartService->getTotal(),
                'items' => $this->cartService->getItems(),
            ]);
        }

        return back()->with('success', "¡{$product->name} agregado al carrito!");
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        $this->cartService->update((int) $request->product_id, (int) $request->quantity);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Carrito actualizado con éxito.',
                'cartCount' => $this->cartService->count(),
                'subtotal' => $this->cartService->getSubtotal(),
                'discount' => $this->cartService->getDiscount(),
                'shipping' => $this->cartService->getShipping(),
                'tax' => $this->cartService->getTax(),
                'total' => $this->cartService->getTotal(),
                'items' => $this->cartService->getItems(),
            ]);
        }

        return back()->with('success', 'Carrito actualizado con éxito.');
    }

    public function remove($id, Request $request)
    {
        $this->cartService->remove((int) $id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado del carrito.',
                'cartCount' => $this->cartService->count(),
                'subtotal' => $this->cartService->getSubtotal(),
                'discount' => $this->cartService->getDiscount(),
                'shipping' => $this->cartService->getShipping(),
                'tax' => $this->cartService->getTax(),
                'total' => $this->cartService->getTotal(),
            ]);
        }

        return back()->with('success', 'Producto eliminado del carrito.');
    }

    public function clear()
    {
        $this->cartService->clear();
        return redirect()->route('cart.index')->with('success', 'El carrito ha sido vaciado.');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        if ($this->cartService->applyCoupon($request->code)) {
            return back()->with('success', "¡Cupón '{$request->code}' aplicado con éxito!");
        }

        return back()->with('error', 'El cupón no es válido o no cumple con el monto mínimo de compra.');
    }

    public function removeCoupon()
    {
        $this->cartService->removeCoupon();
        return back()->with('success', 'Cupón removido del carrito.');
    }

    public function getMiniCart()
    {
        return response()->json([
            'cartCount' => $this->cartService->count(),
            'items' => array_values($this->cartService->getItems()),
            'subtotal' => $this->cartService->getSubtotal(),
            'discount' => $this->cartService->getDiscount(),
            'shipping' => $this->cartService->getShipping(),
            'tax' => $this->cartService->getTax(),
            'total' => $this->cartService->getTotal(),
        ]);
    }
}
