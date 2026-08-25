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

        $totalOrders = $orders->count();
        $deliveredOrders = $orders->where('status', 'delivered')->count();
        $processingOrders = $orders->whereIn('status', ['processing', 'shipped'])->count();
        $cancelledOrders = $orders->where('status', 'cancelled')->count();
        $totalSpent = $orders->where('status', '!=', 'cancelled')->sum('total');

        // Calculate delivery score & reliability
        if ($totalOrders > 0) {
            $completedAndDelivered = $deliveredOrders + $processingOrders;
            $reliabilityScore = min(100, max(50, round(($completedAndDelivered / $totalOrders) * 100)));
        } else {
            // New verified buyer default
            $reliabilityScore = 100;
        }

        if ($reliabilityScore >= 90) {
            $reliabilityLabel = 'Excelente Comprador (Confiable)';
            $reliabilityClass = 'success';
            $riskAssessment = 'Bajo Riesgo: Apto para despachos con Pago Contra Entrega.';
        } elseif ($reliabilityScore >= 75) {
            $reliabilityLabel = 'Comprador Confiable';
            $reliabilityClass = 'info';
            $riskAssessment = 'Riesgo Moderado: Confirmar dirección por WhatsApp antes del despacho.';
        } else {
            $reliabilityLabel = 'Comprador con Historial de Devolución';
            $reliabilityClass = 'danger';
            $riskAssessment = 'Alto Riesgo: Se sugiere confirmar pago previo o validar número.';
        }

        $formattedPhone = '+57 ' . substr($nationalPhone, 0, 3) . ' ' . substr($nationalPhone, 3, 3) . ' ' . substr($nationalPhone, 6);

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
            'phone' => $formattedPhone,
            'national_phone' => $nationalPhone,
            'name' => $user ? $user->name : ($orders->first()?->customer_name ?: 'Comprador Dropi Colombia'),
            'email' => $user ? $user->email : ($orders->first()?->customer_email ?: 'No registrado'),
            'city' => $user ? $user->city : ($orders->first()?->shipping_city ?: 'Colombia'),
            'address' => $user ? $user->address : ($orders->first()?->shipping_address ?: 'N/A'),
            'total_orders' => $totalOrders,
            'delivered_orders' => $deliveredOrders,
            'processing_orders' => $processingOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_spent' => format_cop($totalSpent),
            'total_spent_raw' => $totalSpent,
            'reliability_score' => $reliabilityScore,
            'reliability_label' => $reliabilityLabel,
            'reliability_class' => $reliabilityClass,
            'risk_assessment' => $riskAssessment,
            'orders' => $orderList,
            'source' => 'dropi_verified',
        ]);
    }
}
