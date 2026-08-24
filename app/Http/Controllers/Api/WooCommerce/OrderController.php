<?php

namespace App\Http\Controllers\Api\WooCommerce;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Format Laravel Order into WooCommerce v3 Order JSON
     */
    protected function formatWooCommerceOrder(Order $order): array
    {
        $nameParts = explode(' ', trim($order->customer_name), 2);
        $firstName = $nameParts[0] ?? 'Cliente';
        $lastName = $nameParts[1] ?? '';

        $wcStatus = match ($order->status) {
            'pending' => 'pending',
            'processing' => 'processing',
            'shipped' => 'on-hold',
            'delivered' => 'completed',
            'cancelled' => 'cancelled',
            default => 'processing',
        };

        $lineItems = $order->items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->product_name,
                'product_id' => $item->product_id ?: 0,
                'variation_id' => 0,
                'quantity' => $item->quantity,
                'tax_class' => '',
                'subtotal' => (string) $item->total,
                'subtotal_tax' => '0.00',
                'total' => (string) $item->total,
                'total_tax' => '0.00',
                'taxes' => [],
                'meta_data' => [],
                'sku' => $item->product_sku ?: '',
                'price' => (float) $item->price,
                'image' => [
                    'id' => 0,
                    'src' => $item->product_image ?: '',
                ],
            ];
        })->toArray();

        $billing = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'company' => '',
            'address_1' => $order->shipping_address,
            'address_2' => '',
            'city' => $order->shipping_city,
            'state' => $order->shipping_department ?: 'Cundinamarca',
            'postcode' => $order->shipping_postal_code ?: '110111',
            'country' => 'CO',
            'email' => $order->customer_email,
            'phone' => $order->customer_phone,
        ];

        $shipping = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'company' => '',
            'address_1' => $order->shipping_address,
            'address_2' => '',
            'city' => $order->shipping_city,
            'state' => $order->shipping_department ?: 'Cundinamarca',
            'postcode' => $order->shipping_postal_code ?: '110111',
            'country' => 'CO',
            'phone' => $order->customer_phone,
        ];

        return [
            'id' => $order->id,
            'parent_id' => 0,
            'number' => $order->order_number,
            'order_key' => 'wc_order_' . Str::random(13),
            'created_via' => 'rest-api',
            'version' => '9.0.2',
            'status' => $wcStatus,
            'currency' => 'COP',
            'date_created' => $order->created_at?->toIso8601String(),
            'date_created_gmt' => $order->created_at?->toIso8601String(),
            'date_modified' => $order->updated_at?->toIso8601String(),
            'discount_total' => (string) ($order->discount ?: '0.00'),
            'discount_tax' => '0.00',
            'shipping_total' => (string) ($order->shipping_cost ?: '0.00'),
            'shipping_tax' => '0.00',
            'cart_tax' => (string) ($order->tax ?: '0.00'),
            'total' => (string) $order->total,
            'total_tax' => (string) ($order->tax ?: '0.00'),
            'prices_include_tax' => false,
            'customer_id' => $order->user_id ?: 0,
            'customer_ip_address' => '127.0.0.1',
            'customer_user_agent' => 'Dropi WooCommerce Integration',
            'customer_note' => $order->order_notes ?: '',
            'billing' => $billing,
            'shipping' => $shipping,
            'payment_method' => $order->payment_method ?: 'cod',
            'payment_method_title' => $order->payment_method_label ?: 'Pago Contra Entrega',
            'transaction_id' => $order->dropi_order_id ?: '',
            'date_paid' => $order->payment_status === 'paid' ? $order->created_at?->toIso8601String() : null,
            'date_completed' => $order->status === 'delivered' ? $order->updated_at?->toIso8601String() : null,
            'cart_hash' => md5($order->order_number),
            'meta_data' => [
                ['id' => 1, 'key' => '_billing_dni', 'value' => (string) $order->recipient_dni],
                ['id' => 2, 'key' => '_shipping_dni', 'value' => (string) $order->recipient_dni],
                ['id' => 3, 'key' => '_dropi_order_id', 'value' => (string) $order->dropi_order_id],
                ['id' => 4, 'key' => '_dropi_status', 'value' => (string) $order->dropi_status],
                ['id' => 5, 'key' => '_dropi_guide_number', 'value' => (string) $order->dropi_guide_number],
                ['id' => 6, 'key' => '_shipping_carrier', 'value' => (string) $order->shipping_carrier],
            ],
            'line_items' => $lineItems,
            'tax_lines' => [],
            'shipping_lines' => [
                [
                    'id' => 1,
                    'method_title' => 'Envío Nacional (' . ($order->shipping_carrier ?: 'Coordinadora') . ')',
                    'method_id' => 'flat_rate',
                    'total' => (string) ($order->shipping_cost ?: '0.00'),
                    'total_tax' => '0.00',
                    'taxes' => [],
                    'meta_data' => [],
                ],
            ],
            'fee_lines' => [],
            'coupon_lines' => $order->coupon_code ? [
                [
                    'id' => 1,
                    'code' => $order->coupon_code,
                    'discount' => (string) $order->discount,
                    'discount_tax' => '0.00',
                    'meta_data' => [],
                ],
            ] : [],
            'refunds' => [],
            '_links' => [
                'self' => [['href' => url('/wp-json/wc/v3/orders/' . $order->id)]],
                'collection' => [['href' => url('/wp-json/wc/v3/orders')]],
            ],
        ];
    }

    /**
     * List orders (GET /wp-json/wc/v3/orders)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with('items');

        if ($request->has('status') && $request->status != '') {
            $st = match ($request->status) {
                'pending' => 'pending',
                'processing' => 'processing',
                'on-hold' => 'shipped',
                'completed' => 'delivered',
                'cancelled' => 'cancelled',
                default => $request->status,
            };
            $query->where('status', $st);
        }

        if ($request->has('customer') && $request->customer > 0) {
            $query->where('user_id', $request->customer);
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 10)));
        $page = max(1, (int) $request->input('page', 1));

        $total = $query->count();
        $orders = $query->latest()->forPage($page, $perPage)->get();
        $totalPages = ceil($total / $perPage);

        $data = $orders->map(fn($o) => $this->formatWooCommerceOrder($o));

        return response()->json($data)
            ->header('X-WP-Total', $total)
            ->header('X-WP-TotalPages', $totalPages);
    }

    /**
     * Get single order (GET /wp-json/wc/v3/orders/{id})
     */
    public function show(int $id): JsonResponse
    {
        $order = Order::with('items')->findOrFail($id);
        return response()->json($this->formatWooCommerceOrder($order));
    }

    /**
     * Create order via WooCommerce API (POST /wp-json/wc/v3/orders)
     */
    public function store(Request $request): JsonResponse
    {
        $billing = $request->input('billing', []);
        $shipping = $request->input('shipping', []);
        $lineItems = $request->input('line_items', []);

        $customerName = trim(($billing['first_name'] ?? 'Cliente') . ' ' . ($billing['last_name'] ?? ''));
        $email = $billing['email'] ?? 'cliente@dropi.co';
        $phone = $billing['phone'] ?? '+57 300 000 0000';
        $address = $shipping['address_1'] ?? ($billing['address_1'] ?? 'Dirección');
        $city = $shipping['city'] ?? ($billing['city'] ?? 'Bogotá D.C.');
        $dept = $shipping['state'] ?? ($billing['state'] ?? 'Cundinamarca');
        $postcode = $shipping['postcode'] ?? ($billing['postcode'] ?? '110111');

        $orderNumber = 'ORD-' . strtoupper(Str::random(6)) . '-' . date('Ymd');
        $paymentMethod = $request->input('payment_method', 'cash_on_delivery');

        $subtotal = 0;
        $itemsData = [];

        foreach ($lineItems as $item) {
            $productId = $item['product_id'] ?? null;
            $qty = (int) ($item['quantity'] ?? 1);
            $price = (float) ($item['price'] ?? 0);

            $product = $productId ? Product::find($productId) : null;
            if ($product) {
                $price = $price > 0 ? $price : $product->price;
                $name = $product->name;
                $sku = $product->sku;
                $img = $product->image;
            } else {
                $name = $item['name'] ?? 'Producto Dropi';
                $sku = $item['sku'] ?? 'DRP-ITEM';
                $img = null;
            }

            $lineTotal = $price * $qty;
            $subtotal += $lineTotal;

            $itemsData[] = [
                'product_id' => $productId,
                'product_name' => $name,
                'product_sku' => $sku,
                'product_image' => $img,
                'price' => $price,
                'quantity' => $qty,
                'total' => $lineTotal,
            ];
        }

        $shippingCost = (float) ($request->input('shipping_total', 0));
        $tax = (float) ($request->input('total_tax', round($subtotal * 0.19, 0)));
        $total = (float) ($request->input('total', $subtotal + $shippingCost + $tax));

        $order = Order::create([
            'order_number' => $orderNumber,
            'status' => 'pending',
            'subtotal' => $subtotal,
            'discount' => 0,
            'shipping_cost' => $shippingCost,
            'tax' => $tax,
            'total' => $total,
            'customer_name' => $customerName,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'shipping_address' => $address,
            'shipping_city' => $city,
            'shipping_department' => $dept,
            'shipping_postal_code' => $postcode,
            'shipping_carrier' => 'Coordinadora',
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentMethod === 'credit_card' ? 'paid' : 'pending',
            'order_notes' => $request->input('customer_note', ''),
        ]);

        foreach ($itemsData as $it) {
            $it['order_id'] = $order->id;
            OrderItem::create($it);
        }

        return response()->json($this->formatWooCommerceOrder($order->fresh(['items'])), 201);
    }

    /**
     * Update order (PUT /wp-json/wc/v3/orders/{id})
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $order = Order::with('items')->findOrFail($id);

        if ($request->has('status')) {
            $order->status = match ($request->status) {
                'pending' => 'pending',
                'processing' => 'processing',
                'on-hold' => 'shipped',
                'completed' => 'delivered',
                'cancelled' => 'cancelled',
                default => $order->status,
            };
        }

        // Handle Dropi metadata updates (tracking guide, carrier, etc.)
        $meta = $request->input('meta_data', []);
        foreach ($meta as $m) {
            if (isset($m['key'])) {
                if ($m['key'] === '_dropi_guide_number' || $m['key'] === 'guide_number') {
                    $order->dropi_guide_number = $m['value'];
                }
                if ($m['key'] === '_shipping_carrier' || $m['key'] === 'carrier') {
                    $order->shipping_carrier = $m['value'];
                }
                if ($m['key'] === '_dropi_status') {
                    $order->dropi_status = $m['value'];
                }
            }
        }

        $order->save();

        return response()->json($this->formatWooCommerceOrder($order));
    }
}
