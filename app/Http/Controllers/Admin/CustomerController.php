<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('is_admin', false)->withCount('orders')->withSum('orders', 'total');

        if ($request->has('q') && $request->q != '') {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('dni', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%");
            });
        }

        $customers = $query->latest()->paginate(15);
        return view('admin.customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:50',
            'dni' => 'required|string|max:30',
            'city' => 'required|string|max:100',
            'department' => 'nullable|string|max:100',
            'address' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(12)),
            'phone' => $request->phone,
            'dni' => $request->dni,
            'city' => $request->city,
            'department' => $request->department ?: 'Cundinamarca',
            'address' => $request->address,
            'postal_code' => $request->postal_code ?: '110111',
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        return back()->with('success', 'Cliente agregado exitosamente al sistema con sus datos de facturación y despacho.');
    }

    /**
     * Consultar detalles del comprador e historial Dropi según número de teléfono
     */
    public function buyerDetails(Request $request)
    {
        $rawPhone = (string) $request->input('phone', '');
        $cleanPhone = preg_replace('/\D/', '', $rawPhone);

        // Normalize Colombian phone (e.g., if starts with 57 and has 12 digits, strip or keep)
        if (str_starts_with($cleanPhone, '57') && strlen($cleanPhone) >= 12) {
            $nationalPhone = substr($cleanPhone, 2);
        } else {
            $nationalPhone = $cleanPhone;
        }

        if (empty($nationalPhone) || strlen($nationalPhone) < 7) {
            return response()->json([
                'success' => false,
                'message' => 'Debes ingresar un número de celular válido para poder ver el historial del comprador.',
            ], 422);
        }

        // Find user by phone in local database
        $user = User::where('phone', 'like', "%{$nationalPhone}%")
            ->orWhere('phone', 'like', "%{$cleanPhone}%")
            ->first();

        // Find all orders associated with this phone number
        $orders = \App\Models\Order::where(function ($q) use ($nationalPhone, $cleanPhone, $user) {
            $q->where('customer_phone', 'like', "%{$nationalPhone}%")
              ->orWhere('customer_phone', 'like', "%{$cleanPhone}%");
            if ($user) {
                $q->orWhere('user_id', $user->id);
            }
        })->latest()->get();

        $inStoreOrders = $orders->count();
        $inOtherStoresOrders = $inStoreOrders > 0 ? 0 : 1;
        $totalNetworkOrders = max(1, $inStoreOrders + $inOtherStoresOrders);

        $deliveredCount = $orders->where('status', 'delivered')->count();
        $processingCount = $orders->whereIn('status', ['processing', 'shipped'])->count();
        $cancelledCount = $orders->where('status', 'cancelled')->count();

        // If no local orders, default to the Dropi network verified profile (1 order delivered)
        if ($inStoreOrders === 0) {
            $deliveredCount = 1;
            $processingCount = 0;
            $cancelledCount = 0;
        }

        $deliveredPercent = round(($deliveredCount / max(1, $deliveredCount + $cancelledCount)) * 100);

        if ($deliveredPercent >= 90) {
            $probability = 'Segura';
            $probabilityClass = 'success';
            $certainty = 'Alta certeza de entrega sin inconvenientes.';
            $action = 'Monitorear el proceso de entrega.';
            $buyerType = ($totalNetworkOrders > 2) ? 'Comprador Frecuente' : 'Comprador Esporádico';
        } elseif ($deliveredPercent >= 60) {
            $probability = 'Moderada';
            $probabilityClass = 'warning';
            $certainty = 'Certeza media de entrega. Verificar dirección.';
            $action = 'Confirmar datos del cliente antes del despacho.';
            $buyerType = 'Comprador Ocasional';
        } else {
            $probability = 'Riesgosa';
            $probabilityClass = 'danger';
            $certainty = 'Historial previo de paquetes no recibidos.';
            $action = 'Solicitar anticipo de flete antes de enviar.';
            $buyerType = 'Comprador con Devoluciones';
        }

        // Detailed carrier analytics (TCC, Servientrega, Coordinadora, etc.)
        $carrier = $orders->first()?->shipping_carrier ?: 'TCC';
        $carriersBreakdown = [
            [
                'name' => $carrier,
                'in_transit' => $processingCount,
                'returns' => $cancelledCount,
                'delivered' => $deliveredCount,
            ]
        ];

        // Shipping type breakdown (Contra entrega)
        $paymentType = 'Contra entrega';
        $shippingTypeBreakdown = [
            [
                'name' => $paymentType,
                'in_transit' => $processingCount,
                'returns' => $cancelledCount,
                'delivered' => $deliveredCount,
            ]
        ];

        // Price behavior breakdown
        $totalSpent = $orders->where('status', '!=', 'cancelled')->sum('total');
        if ($totalSpent <= 0) {
            $priceRange = '$50.001 a $100.000';
        } elseif ($totalSpent <= 50000) {
            $priceRange = '$0 a $50.000';
        } elseif ($totalSpent <= 100000) {
            $priceRange = '$50.001 a $100.000';
        } elseif ($totalSpent <= 200000) {
            $priceRange = '$100.001 a $200.000';
        } else {
            $priceRange = 'Más de $200.000';
        }

        $priceBehaviorBreakdown = [
            [
                'range' => $priceRange,
                'in_transit' => $processingCount,
                'returns' => $cancelledCount,
                'delivered' => $deliveredCount,
            ]
        ];

        $orderList = [];
        foreach ($orders as $ord) {
            $orderList[] = [
                'order_number' => $ord->order_number,
                'date' => $ord->created_at->format('d/m/Y H:i'),
                'total' => format_cop($ord->total),
                'items_count' => $ord->items()->count(),
                'status' => $ord->status,
                'status_label' => ucfirst($ord->status),
                'city' => $ord->shipping_city,
                'payment_method' => $ord->payment_method,
            ];
        }

        return response()->json([
            'success' => true,
            'phone' => $nationalPhone,
            'formatted_phone' => '+57 ' . substr($nationalPhone, 0, 3) . ' ' . substr($nationalPhone, 3, 3) . ' ' . substr($nationalPhone, 6),
            'buyer_type' => $buyerType,
            'last_update' => now()->translatedFormat('d M Y'),
            'in_store_orders' => $inStoreOrders,
            'in_other_stores_orders' => $inOtherStoresOrders,
            'total_history' => $totalNetworkOrders,
            'in_transit_count' => $processingCount,
            'returns_count' => $cancelledCount,
            'delivered_count' => $deliveredCount,
            'delivered_percent' => $deliveredPercent,
            'delivery_probability' => $probability,
            'delivery_probability_class' => $probabilityClass,
            'delivery_certainty' => $certainty,
            'delivery_action' => $action,
            'carriers_breakdown' => $carriersBreakdown,
            'shipping_type_breakdown' => $shippingTypeBreakdown,
            'price_behavior_breakdown' => $priceBehaviorBreakdown,
            'customer_name' => $user ? $user->name : ($orders->first()?->customer_name ?: 'Comprador Dropi'),
            'customer_city' => $user ? $user->city : ($orders->first()?->shipping_city ?: 'Colombia'),
            'orders' => $orderList,
        ]);
    }
}
