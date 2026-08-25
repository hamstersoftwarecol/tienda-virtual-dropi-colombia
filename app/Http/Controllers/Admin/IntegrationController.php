<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WooCommerceApiKey;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function index()
    {
        $apiKeys = WooCommerceApiKey::latest()->get();
        return view('admin.integrations.index', compact('apiKeys'));
    }

    public function generateWooCommerceKey(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'permissions' => 'required|in:read,write,read_write',
        ]);

        $key = WooCommerceApiKey::generate(
            $request->description,
            auth()->id(),
            $request->permissions
        );

        return back()->with('key_generated', [
            'description' => $key->description,
            'consumer_key' => $key->consumer_key,
            'consumer_secret' => $key->consumer_secret,
        ])->with('success', '¡Claves de WooCommerce REST API generadas exitosamente! Guárdalas de inmediato para conectarlas en tus plataformas externas.');
    }

    public function revokeWooCommerceKey(WooCommerceApiKey $key)
    {
        $key->delete();
        return back()->with('success', 'Clave API revocada y eliminada con éxito.');
    }
}
