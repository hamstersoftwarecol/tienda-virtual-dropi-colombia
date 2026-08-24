<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'dropi_id',
        'supplier_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'wholesale_price',
        'suggested_price',
        'stock',
        'image',
        'images',
        'category_name',
        'is_imported',
        'imported_product_id',
        'imported_at',
    ];

    protected $casts = [
        'wholesale_price' => 'decimal:2',
        'suggested_price' => 'decimal:2',
        'is_imported' => 'boolean',
        'images' => 'array',
        'imported_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function localProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'imported_product_id');
    }

    public function getPotentialProfitAttribute(): float
    {
        return (float) ($this->suggested_price - $this->wholesale_price);
    }

    public function getMarginPercentAttribute(): int
    {
        if ($this->wholesale_price <= 0) return 0;
        return (int) round((($this->suggested_price - $this->wholesale_price) / $this->wholesale_price) * 100);
    }
}
