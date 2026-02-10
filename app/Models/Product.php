<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = ['store_id', 'shopify_product_id', 'title', 'description', 'price', 'inventory_quantity', 'status'];

    protected $casts = [
        'price' => 'decimal:2',
        'inventory_quantity' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? strtoupper($value) : null,
            set: fn (?string $value) => $value ? strtoupper($value) : null,
        );
    }
}
