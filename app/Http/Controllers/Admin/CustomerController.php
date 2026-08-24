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
}
