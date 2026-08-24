<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        if (Supplier::count() === 0) {
            Supplier::firstOrCreate(
                ['slug' => 'bodega-proveedor-dropi-441247'],
                [
                    'name' => 'Bodega Proveedor Dropi #441247',
                    'city' => 'Bogotá / Medellín',
                    'department' => 'Colombia',
                    'phone' => '+57 310 000 0000',
                    'email' => 'contacto@dropi.co',
                    'warehouse_address' => 'Bodega Oficial Dropi Colombia (ID: 441247)',
                    'description' => 'Bodega nacional vinculada a tu cuenta de Dropi mediante API de proveedor (user_id: 441247).',
                    'logo' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=300',
                    'rating' => 5.0,
                    'is_verified' => true,
                    'is_active' => true,
                ]
            );
        }

        $query = Supplier::withCount('catalogProducts', 'products');

        if ($request->has('q') && $request->q != '') {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%")
                    ->orWhere('department', 'like', "%{$q}%");
            });
        }

        $suppliers = $query->latest()->paginate(12);
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['catalogProducts' => function ($q) {
            $q->latest();
        }]);

        return view('admin.suppliers.show', compact('supplier'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'department' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'warehouse_address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|url',
        ]);

        Supplier::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(4),
            'city' => $request->city,
            'department' => $request->department ?: 'Antioquia',
            'phone' => $request->phone,
            'email' => $request->email,
            'warehouse_address' => $request->warehouse_address,
            'description' => $request->description,
            'logo' => $request->logo ?: 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=300',
            'rating' => 5.0,
            'is_verified' => true,
            'is_active' => true,
        ]);

        return back()->with('success', 'Proveedor registrado exitosamente en la plataforma.');
    }

    public function syncDropi(\App\Services\DropiService $dropiService)
    {
        $result = $dropiService->fetchSuppliersFromApi();

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('warning', $result['message']);
    }
}
