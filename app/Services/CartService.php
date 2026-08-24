<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected string $sessionKey = 'cart_items';
    protected string $couponKey = 'cart_coupon';

    public function getItems(): array
    {
        return Session::get($this->sessionKey, []);
    }

    public function count(): int
    {
        $items = $this->getItems();
        $count = 0;
        foreach ($items as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    public function add(Product $product, int $quantity = 1): array
    {
        $items = $this->getItems();
        $id = $product->id;

        if (isset($items[$id])) {
            $newQuantity = $items[$id]['quantity'] + $quantity;
            if ($newQuantity > $product->stock) {
                $newQuantity = $product->stock;
            }
            $items[$id]['quantity'] = $newQuantity;
            $items[$id]['total'] = $items[$id]['quantity'] * $items[$id]['price'];
        } else {
            $qty = min($quantity, $product->stock > 0 ? $product->stock : 1);
            $items[$id] = [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'price' => (float) $product->price,
                'compare_price' => (float) $product->compare_price,
                'image' => $product->image,
                'category' => $product->category ? $product->category->name : 'General',
                'quantity' => $qty,
                'total' => (float) ($product->price * $qty),
                'max_stock' => $product->stock,
            ];
        }

        Session::put($this->sessionKey, $items);
        return $items;
    }

    public function update(int $productId, int $quantity): array
    {
        $items = $this->getItems();

        if (isset($items[$productId])) {
            $product = Product::find($productId);
            $maxStock = $product ? $product->stock : 99;

            if ($quantity <= 0) {
                unset($items[$productId]);
            } else {
                $qty = min($quantity, $maxStock);
                $items[$productId]['quantity'] = $qty;
                $items[$productId]['total'] = $qty * $items[$productId]['price'];
            }
            Session::put($this->sessionKey, $items);
        }

        return $items;
    }

    public function remove(int $productId): array
    {
        $items = $this->getItems();
        if (isset($items[$productId])) {
            unset($items[$productId]);
            Session::put($this->sessionKey, $items);
        }
        return $items;
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey);
        Session::forget($this->couponKey);
    }

    public function getSubtotal(): float
    {
        $items = $this->getItems();
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['total'];
        }
        return round($subtotal, 2);
    }

    public function getCoupon(): ?Coupon
    {
        $code = Session::get($this->couponKey);
        if (!$code) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();
        if ($coupon && $coupon->isValidForAmount($this->getSubtotal())) {
            return $coupon;
        }

        Session::forget($this->couponKey);
        return null;
    }

    public function applyCoupon(string $code): bool
    {
        $coupon = Coupon::where('code', strtoupper(trim($code)))->first();
        if ($coupon && $coupon->isValidForAmount($this->getSubtotal())) {
            Session::put($this->couponKey, $coupon->code);
            return true;
        }
        return false;
    }

    public function removeCoupon(): void
    {
        Session::forget($this->couponKey);
    }

    public function getDiscount(): float
    {
        $coupon = $this->getCoupon();
        if ($coupon) {
            return $coupon->calculateDiscount($this->getSubtotal());
        }
        return 0.0;
    }

    public function getShipping(): float
    {
        $subtotal = $this->getSubtotal();
        if ($subtotal == 0) {
            return 0.0;
        }
        // Envío gratis en compras superiores a $150.000 COP
        return $subtotal >= 150000 ? 0.0 : 12000.00;
    }

    public function getTax(): float
    {
        // 19% IVA Colombia
        $subtotal = $this->getSubtotal() - $this->getDiscount();
        return round(max(0, $subtotal) * 0.19, 0);
    }

    public function getTotal(): float
    {
        $subtotal = $this->getSubtotal();
        if ($subtotal <= 0) {
            return 0.0;
        }
        $discount = $this->getDiscount();
        $shipping = $this->getShipping();
        $tax = $this->getTax();

        $total = ($subtotal - $discount) + $shipping + $tax;
        return round(max(0, $total), 2);
    }
}
