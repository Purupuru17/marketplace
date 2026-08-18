<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Store extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'store_level_id', 'location_node_id', 'store_code', 'store_name',
        'slug', 'description', 'logo', 'banner', 'phone', 'email',
        'bank_name', 'account_number', 'account_name',
        'lat', 'lng',
        'rate_per_km', 'min_free_distance_km', 'max_radius_km', 'status',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'rate_per_km' => 'decimal:2',
            'min_free_distance_km' => 'decimal:2',
            'max_radius_km' => 'decimal:2',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(StoreLevel::class, 'store_level_id');
    }

    public function locationNode(): BelongsTo
    {
        return $this->belongsTo(LocationNode::class, 'location_node_id');
    }

    public function operatingHours(): HasMany
    {
        return $this->hasMany(StoreOperatingHour::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
