<?php

namespace App\Http\Middleware;

use App\Models\DropiSetting;
use App\Models\WooCommerceApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WooCommerceAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check Dropi Bearer Token or Custom Header
        $authHeader = $request->header('Authorization');
        $bearerToken = null;
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $bearerToken = trim($matches[1]);
        }
        $dropiToken = $bearerToken ?: ($request->header('X-Dropi-Token') ?: $request->query('dropi_token'));

        if ($dropiToken) {
            $settings = DropiSetting::getSettings();
            if (!empty($settings->auth_token) && hash_equals($settings->auth_token, $dropiToken)) {
                $request->attributes->set('auth_provider', 'dropi_jwt');
                return $next($request);
            }

            // Also check decoded JWT payload if integration matches
            try {
                $parts = explode('.', $dropiToken);
                if (count($parts) === 3) {
                    $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                    if ($payload && isset($payload['aud']) && $payload['aud'] === 'WOOCOMMERCE') {
                        $request->attributes->set('auth_provider', 'dropi_jwt');
                        $request->attributes->set('dropi_payload', $payload);
                        return $next($request);
                    }
                }
            } catch (\Exception $e) {
                // Continue to WooCommerce Key check
            }
        }

        // 2. Check Query Params (?consumer_key=ck_...&consumer_secret=cs_...)
        $consumerKey = $request->query('consumer_key');
        $consumerSecret = $request->query('consumer_secret');

        // 3. Check Basic Auth (username: ck_..., password: cs_...)
        if (!$consumerKey && $request->getUser()) {
            $consumerKey = $request->getUser();
            $consumerSecret = $request->getPassword();
        }

        // 4. Check Custom Headers
        if (!$consumerKey) {
            $consumerKey = $request->header('X-WC-Consumer-Key') ?: $request->header('X-Consumer-Key');
            $consumerSecret = $request->header('X-WC-Consumer-Secret') ?: $request->header('X-Consumer-Secret');
        }

        // If credentials provided, validate against database
        if ($consumerKey && $consumerSecret) {
            $keyRecord = WooCommerceApiKey::where('consumer_key', $consumerKey)
                ->where('is_active', true)
                ->first();

            if ($keyRecord && hash_equals($keyRecord->consumer_secret, $consumerSecret)) {
                $keyRecord->update(['last_access_at' => now()]);
                $request->attributes->set('wc_api_key', $keyRecord);
                return $next($request);
            }
        }

        // If no credentials provided or invalid, but there are active keys in system
        if (WooCommerceApiKey::where('is_active', true)->exists()) {
            // For public /wp-json/ or /wp-json/wc/v3 system info, allow read
            if ($request->is('wp-json') || $request->is('wp-json/wc/v3')) {
                return $next($request);
            }

            return response()->json([
                'code' => 'woocommerce_rest_cannot_view',
                'message' => 'Lo sentimos, las credenciales de WooCommerce API (Consumer Key o Token Dropi) no son válidas o están ausentes.',
                'data' => [
                    'status' => 401,
                ],
            ], 401);
        }

        // Fallback: If no keys generated yet, allow setup & inspection
        return $next($request);
    }
}
