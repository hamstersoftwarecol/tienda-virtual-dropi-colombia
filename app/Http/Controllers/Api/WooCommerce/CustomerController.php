<?php

namespace App\Http\Controllers\Api\WooCommerce;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customers = User::where('is_admin', false)->latest()->paginate(20);

        $data = $customers->map(function ($c) {
            $nameParts = explode(' ', trim($c->name), 2);
            return [
                'id' => $c->id,
                'date_created' => $c->created_at?->toIso8601String(),
                'email' => $c->email,
                'first_name' => $nameParts[0] ?? '',
                'last_name' => $nameParts[1] ?? '',
                'role' => 'customer',
                'username' => $c->email,
                'billing' => [
                    'first_name' => $nameParts[0] ?? '',
                    'last_name' => $nameParts[1] ?? '',
                    'address_1' => $c->address ?: '',
                    'city' => $c->city ?: 'Bogotá D.C.',
                    'state' => $c->department ?: 'Cundinamarca',
                    'postcode' => $c->postal_code ?: '',
                    'country' => 'CO',
                    'email' => $c->email,
                    'phone' => $c->phone ?: '',
                ],
                'shipping' => [
                    'first_name' => $nameParts[0] ?? '',
                    'last_name' => $nameParts[1] ?? '',
                    'address_1' => $c->address ?: '',
                    'city' => $c->city ?: 'Bogotá D.C.',
                    'state' => $c->department ?: 'Cundinamarca',
                    'postcode' => $c->postal_code ?: '',
                    'country' => 'CO',
                    'phone' => $c->phone ?: '',
                ],
                'is_paying_customer' => $c->orders()->count() > 0,
                'orders_count' => $c->orders()->count(),
                'total_spent' => (string) $c->orders()->sum('total'),
            ];
        });

        return response()->json($data);
    }
}
