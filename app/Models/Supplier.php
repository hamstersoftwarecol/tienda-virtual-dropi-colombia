<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'city',
        'department',
        'phone',
        'email',
        'rating',
        'logo',
        'description',
        'warehouse_address',
        'is_verified',
        'is_active',
        'total_products_count',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function catalogProducts(): HasMany
    {
        return $this->hasMany(SupplierProduct::class);
    }
}
