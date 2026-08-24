<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropiSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_url',
        'auth_token',
        'email',
        'auto_sync_orders',
        'default_markup_percent',
        'default_carrier',
        'last_sync_at',
    ];

    protected $casts = [
        'auto_sync_orders' => 'boolean',
        'default_markup_percent' => 'integer',
        'last_sync_at' => 'datetime',
    ];

    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'api_url' => 'https://api.dropi.co/api/',
            'auth_token' => null,
            'email' => 'admin@tienda.com',
            'auto_sync_orders' => true,
            'default_markup_percent' => 40,
            'default_carrier' => 'Coordinadora',
        ]);
    }
}
