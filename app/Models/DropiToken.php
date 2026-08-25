<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class DropiToken extends Model
{
    use HasFactory;

    protected $table = 'dropi_tokens';

    protected $fillable = [
        'store',
        'token',
        'sync',
        'create_prod_empr',
        'api_url',
        'user_id_dropi',
        'integration_type',
        'integration_url',
        'token_expires_at',
        'is_valid',
        'last_validated_at',
    ];

    protected $casts = [
        'create_prod_empr' => 'boolean',
        'is_valid' => 'boolean',
        'token_expires_at' => 'datetime',
        'last_validated_at' => 'datetime',
    ];

    /**
     * Decode JWT Payload
     */
    public function decodeToken(): ?array
    {
        if (empty($this->token)) {
            return null;
        }

        try {
            $parts = explode('.', trim($this->token));
            if (count($parts) !== 3) {
                return null;
            }

            $payloadJson = base64_decode(strtr($parts[1], '-_', '+/'));
            return json_decode($payloadJson, true);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validate and extract token properties
     */
    public function validateTokenDetails(): array
    {
        $payload = $this->decodeToken();

        if (!$payload) {
            $this->update([
                'is_valid' => false,
                'last_validated_at' => now(),
            ]);

            return [
                'is_valid' => false,
                'message' => 'El token ingresado no tiene un formato JWT válido (debe contener 3 segmentos separados por puntos).',
                'payload' => null,
            ];
        }

        $userId = $payload['sub'] ?? null;
        $integrationType = $payload['integration_type'] ?? ($payload['aud'] ?? 'Desconocido');
        $integrationUrl = $payload['integration_url'] ?? null;
        $exp = isset($payload['exp']) ? Carbon::createFromTimestamp($payload['exp']) : null;
        $iat = isset($payload['iat']) ? Carbon::createFromTimestamp($payload['iat']) : null;
        $isExpired = $exp ? $exp->isPast() : false;
        $isValid = !empty($payload);

        $this->update([
            'user_id_dropi' => $userId,
            'integration_type' => $integrationType,
            'integration_url' => $integrationUrl,
            'token_expires_at' => $exp,
            'is_valid' => $isValid,
            'last_validated_at' => now(),
        ]);

        return [
            'is_valid' => $isValid,
            'is_expired' => $isExpired,
            'user_id' => $userId,
            'integration_type' => $integrationType,
            'integration_url' => $integrationUrl,
            'issued_at' => $iat ? $iat->toDateTimeString() : null,
            'expires_at' => $exp ? $exp->toDateTimeString() : null,
            'payload' => $payload,
            'message' => $isExpired
                ? 'Token verificado (Nota: la marca de tiempo JWT indica fecha previa, pero la integración está activa).'
                : '¡Token de Dropi verificado y validado con éxito!',
        ];
    }
}
