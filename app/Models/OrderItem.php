<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id', 'product_id', 'variant_id', 'name_snapshot', 'sku_snapshot',
        'variant_snapshot', 'original_price_snapshot', 'discount_snapshot',
        'final_price_snapshot', 'qty', 'subtotal_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'original_price_snapshot' => 'decimal:2',
            'discount_snapshot' => 'decimal:2',
            'final_price_snapshot' => 'decimal:2',
            'subtotal_snapshot' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(ProductRating::class);
    }
}
