<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'dropi_order_id',
        'status',
        'dropi_status',
        'dropi_guide_number',
        'subtotal',
        'discount',
        'coupon_code',
        'shipping_cost',
        'tax',
        'total',
        'customer_name',
        'customer_email',
        'customer_phone',
        'recipient_dni',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_department',
        'shipping_postal_code',
        'shipping_carrier',
        'order_notes',
        'payment_method',
        'payment_status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-warning text-dark',
            'processing' => 'bg-info text-dark',
            'shipped' => 'bg-primary text-white',
            'delivered' => 'bg-success text-white',
            'cancelled' => 'bg-danger text-white',
            default => 'bg-secondary text-white',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'processing' => 'En Proceso',
            'shipped' => 'Enviado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            default => ucfirst($this->status),
        };
    }

    public function getDropiStatusBadgeAttribute(): string
    {
        return match($this->dropi_status) {
            'generated', 'created' => 'bg-info text-white',
            'in_preparation' => 'bg-warning text-dark',
            'dispatched', 'in_transit' => 'bg-primary text-white',
            'delivered' => 'bg-success text-white',
            'returned' => 'bg-danger text-white',
            default => 'bg-secondary text-white',
        };
    }

    public function getDropiStatusLabelAttribute(): string
    {
        return match($this->dropi_status) {
            'generated', 'created' => 'Guía Generada',
            'in_preparation' => 'En Preparación Dropi',
            'dispatched', 'in_transit' => 'En Tránsito (Transportadora)',
            'delivered' => 'Entregado',
            'returned' => 'Devuelto',
            'unassigned' => 'Sin Despachar a Dropi',
            default => ucfirst($this->dropi_status ?? 'Sin Despachar'),
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'cash_on_delivery' => 'Pago Contra Entrega en Efectivo',
            'bre_b' => 'Bre-B (@ALM143)',
            'credit_card' => 'Tarjeta de Crédito / Débito',
            'pse' => 'PSE (Transferencia Bancaria)',
            'nequi' => 'Nequi / Daviplata',
            'bank_transfer' => 'Transferencia Bancaria',
            'paypal' => 'PayPal',
            default => strtoupper((string)$this->payment_method),
        };
    }
}
