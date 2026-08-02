<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreLevel extends Model
{
    protected $fillable = [
        'name', 'price', 'max_products', 'max_discount',
        'can_run_campaign', 'sort_order', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'can_run_campaign' => 'boolean',
        ];
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }
}
