<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'has_variants' => 'boolean',
        'is_active' => 'boolean',
        'track_inventory' => 'boolean',
        'base_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function getTotalQtyAttribute(): int
    {
        return (int) $this->inventories()->sum('quantity');
    }
}
