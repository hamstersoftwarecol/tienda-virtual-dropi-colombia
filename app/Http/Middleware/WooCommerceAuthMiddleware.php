<?php

namespace App\Http\Middleware;

use App\Models\WooCommerceApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WooCommerceAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check Query Params (?consumer_key=ck_...&consumer_secret=cs_...)
        $consumerKey = $request->query('consumer_key');
        $consumerSecret = $request->query('consumer_secret');

        // 2. Check Basic Auth (username: ck_..., password: cs_...)
        if (!$consumerKey && $request->getUser()) {
            $consumerKey = $request->getUser();
            $consumerSecret = $request->getPassword();
        }

        // 3. Check Custom Headers
        if (!$consumerKey) {
            $consumerKey = $request->header('X-WC-Consumer-Key') ?: $request->header('X-Consumer-Key');
            $consumerSecret = $request->header('X-WC-Consumer-Secret') ?: $request->header('X-Consumer-Secret');
        }

        // 4. Check Bearer Token if matches Consumer Key
        if (!$consumerKey) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = trim($matches[1]);
                $keyRecord = WooCommerceApiKey::where('consumer_key', $token)->where('is_active', true)->first();
                if ($keyRecord) {
                    $keyRecord->update(['last_access_at' => now()]);
                    $request->attributes->set('wc_api_key', $keyRecord);
                    return $next($request);
                }
            }
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
                'message' => 'Lo sentimos, las credenciales de WooCommerce API (Consumer Key / Consumer Secret) no son válidas o están ausentes.',
                'data' => [
                    'status' => 401,
                ],
            ], 401);
        }

        // Fallback: If no keys generated yet, allow setup & inspection
        return $next($request);
    }
}
