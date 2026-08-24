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
        $this->ensureVerifiedBodegas();

        $query = Supplier::withCount('catalogProducts', 'products');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%")
                    ->orWhere('department', 'like', "%{$q}%")
                    ->orWhere('warehouse_address', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        $suppliers = $query->latest()->paginate(12);
        return view('admin.suppliers.index', compact('suppliers'));
    }

    protected function ensureVerifiedBodegas(): void
    {
        if (Supplier::count() >= 5) {
            return;
        }

        $bodegas = [
            [
                'slug' => 'bodega-proveedor-dropi-441247',
                'name' => 'Bodega Proveedor Dropi #441247',
                'city' => 'Bogotá / Medellín',
                'department' => 'Colombia',
                'phone' => '+57 310 888 4412',
                'email' => 'proveedor441247@dropi.co',
                'warehouse_address' => 'Bodega Central Dropi Colombia (ID: 441247)',
                'description' => 'Bodega nacional vinculada a tu cuenta de Dropi mediante API de proveedor (user_id: 441247).',
                'logo' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=300',
                'rating' => 5.0,
                'is_verified' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'bodega-mayorista-medellin-tech',
                'name' => 'Bodega Mayorista Medellín Tech',
                'city' => 'Medellín',
                'department' => 'Antioquia',
                'phone' => '+57 314 888 9900',
                'email' => 'ventas@bodegamedellin.co',
                'warehouse_address' => 'Zona Industrial Guayabal, Bodega 45',
                'description' => 'Especialistas en electrónica, gadgets, smartwatches y accesorios para celular con despacho el mismo día.',
                'logo' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=300',
                'rating' => 4.9,
                'is_verified' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'importadora-bogota-express',
                'name' => 'Importadora Bogotá Express',
                'city' => 'Bogotá D.C.',
                'department' => 'Cundinamarca',
                'phone' => '+57 310 777 6655',
                'email' => 'contacto@bogotaexpress.co',
                'warehouse_address' => 'Parque Industrial Fontibón, Módulo C',
                'description' => 'Audio profesional, micrófonos inalámbricos, proyectores y tecnología con entrega rápida a todo el país.',
                'logo' => 'https://images.unsplash.com/photo-1553413077-190dd305871c?w=300',
                'rating' => 4.8,
                'is_verified' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'megabodega-cali-hogar-gadgets',
                'name' => 'MegaBodega Cali Hogar & Gadgets',
                'city' => 'Cali',
                'department' => 'Valle del Cauca',
                'phone' => '+57 318 333 2211',
                'email' => 'pedidos@calimoda.co',
                'warehouse_address' => 'Acopi Yumbo, Calle 15 # 20-50',
                'description' => 'Artículos para el hogar, confort, cocina eléctrica, humidificadores y accesorios de moda.',
                'logo' => 'https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=300',
                'rating' => 4.7,
                'is_verified' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'distribuidora-costa-caribe-fitness',
                'name' => 'Distribuidora Costa Caribe & Fitness',
                'city' => 'Barranquilla',
                'department' => 'Atlántico',
                'phone' => '+57 300 444 8877',
                'email' => 'ventas@costacaribe.co',
                'warehouse_address' => 'Vía 40 # 73-120, Bodega 12',
                'description' => 'Fitness, pistolas de masaje, cuidado personal y aspiradoras portátiles para despacho nacional.',
                'logo' => 'https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=300',
                'rating' => 4.8,
                'is_verified' => true,
                'is_active' => true,
            ],
            [
                'slug' => 'bodega-eje-cafetero-belleza',
                'name' => 'Bodega Eje Cafetero Belleza & Cuidado',
                'city' => 'Pereira',
                'department' => 'Risaralda',
                'phone' => '+57 312 666 3322',
                'email' => 'contacto@ejecafeterobodega.co',
                'warehouse_address' => 'Parque Logístico Cerritos, Bodega 8',
                'description' => 'Cepillos secadores, depiladoras láser, cuidado facial y artículos de belleza personal.',
                'logo' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=300',
                'rating' => 4.9,
                'is_verified' => true,
                'is_active' => true,
            ],
        ];

        foreach ($bodegas as $b) {
            Supplier::firstOrCreate(['slug' => $b['slug']], $b);
        }
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
