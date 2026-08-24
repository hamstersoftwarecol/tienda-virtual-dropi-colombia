<?php

namespace App\Http\Controllers\Api\WooCommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            [
                'id' => 1,
                'name' => 'Dropi Order Created Webhook',
                'status' => 'active',
                'topic' => 'order.created',
                'resource' => 'order',
                'event' => 'created',
                'delivery_url' => 'https://api.dropi.co/api/webhooks/orders',
                'date_created' => now()->toIso8601String(),
                'date_modified' => now()->toIso8601String(),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'id' => rand(10, 999),
            'name' => $request->input('name', 'Dropi Integration Hook'),
            'status' => 'active',
            'topic' => $request->input('topic', 'order.created'),
            'resource' => 'order',
            'event' => 'created',
            'delivery_url' => $request->input('delivery_url', 'https://api.dropi.co/api/webhooks/orders'),
            'date_created' => now()->toIso8601String(),
            'date_modified' => now()->toIso8601String(),
        ], 201);
    }
}
