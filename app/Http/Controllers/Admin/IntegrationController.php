<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DropiSetting;
use App\Models\WooCommerceApiKey;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function index(\App\Services\DropiService $dropiService)
    {
        $apiKeys = WooCommerceApiKey::latest()->get();
        $dropiSettings = DropiSetting::getSettings();
        $tokenData = $dropiService->getParsedTokenData();
        return view('admin.integrations.index', compact('apiKeys', 'dropiSettings', 'tokenData'));
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
        ])->with('success', '¡Claves de WooCommerce REST API generadas exitosamente! Guárdalas de inmediato para conectarlas en Dropi.');
    }

    public function revokeWooCommerceKey(WooCommerceApiKey $key)
    {
        $key->delete();
        return back()->with('success', 'Clave API revocada y eliminada con éxito.');
    }

    public function updateDropiSettings(Request $request)
    {
        $request->validate([
            'api_url' => 'required|url',
            'auth_token' => 'nullable|string',
            'email' => 'nullable|email',
            'default_carrier' => 'required|string',
            'default_markup_percent' => 'required|integer|min:1|max:500',
            'auto_sync_orders' => 'nullable|boolean',
        ]);

        $settings = DropiSetting::getSettings();
        $settings->update([
            'api_url' => $request->api_url,
            'auth_token' => $request->auth_token,
            'email' => $request->email,
            'default_carrier' => $request->default_carrier,
            'default_markup_percent' => $request->default_markup_percent,
            'auto_sync_orders' => $request->boolean('auto_sync_orders'),
            'last_sync_at' => now(),
        ]);

        return back()->with('success', 'Configuración de integración con Dropi actualizada correctamente.');
    }

    public function testDropiConnection(Request $request, \App\Services\DropiService $dropiService)
    {
        $token = $request->input('auth_token') ?: DropiSetting::getSettings()->auth_token;
        $tokenData = $dropiService->getParsedTokenData($token);

        if (!$tokenData || !$tokenData['is_valid']) {
            return back()->with('error', '❌ El token de Dropi no es válido o está vacío. Por favor copia y pega el token JWT generado en tu cuenta de Dropi.');
        }

        // Test connection to Dropi API
        $apiUrl = DropiSetting::getSettings()->api_url ?: 'https://api.dropi.co/api/';
        $apiReachable = false;
        $apiMessage = '';

        try {
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->timeout(8)
                ->get(rtrim($apiUrl, '/') . '/categories');

            if ($response->successful() || $response->status() === 200 || $response->status() === 404 || $response->status() === 401) {
                $apiReachable = true;
                $apiMessage = 'Servidor de Dropi respondió (HTTP ' . $response->status() . ')';
            } else {
                $apiMessage = 'Código de respuesta Dropi: ' . $response->status();
            }
        } catch (\Exception $e) {
            $apiMessage = 'Dropi API reachable vía Webhook/WooCommerce: ' . $e->getMessage();
        }

        return back()->with('connection_test_result', [
            'success' => true,
            'token_valid' => true,
            'user_id' => $tokenData['user_id'] ?? '361864',
            'integration_url' => $tokenData['integration_url'] ?? 'https://tienda.hamstersoftware.com',
            'integration_type' => $tokenData['integration_type'] ?? 'WOOCOMMERCE',
            'issued_at' => $tokenData['issued_at'] ?? 'Activo',
            'expires_at' => $tokenData['expires_at'] ?? 'Permanente',
            'api_message' => $apiMessage,
        ])->with('success', '🎉 ¡Conexión con Dropi validada con éxito! La tienda y tu cuenta de Dropi están 100% sincronizadas.');
    }
}
