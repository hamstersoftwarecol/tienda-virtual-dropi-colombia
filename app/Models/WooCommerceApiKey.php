<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WooCommerceApiKey extends Model
{
    use HasFactory;

    protected $table = 'woocommerce_api_keys';

    protected $fillable = [
        'user_id',
        'description',
        'permissions',
        'consumer_key',
        'consumer_secret',
        'truncated_key',
        'last_access_at',
        'is_active',
    ];

    protected $casts = [
        'last_access_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generate(string $description, ?int $userId = null, string $permissions = 'read_write'): self
    {
        $consumerKey = 'ck_' . Str::random(40);
        $consumerSecret = 'cs_' . Str::random(40);
        $truncatedKey = substr($consumerKey, -7);

        return self::create([
            'user_id' => $userId,
            'description' => $description,
            'permissions' => $permissions,
            'consumer_key' => $consumerKey,
            'consumer_secret' => $consumerSecret,
            'truncated_key' => $truncatedKey,
            'is_active' => true,
        ]);
    }
}
