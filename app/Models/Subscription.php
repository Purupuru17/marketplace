<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasUuids;

    protected $fillable = ['store_id', 'store_level_id', 'starts_at', 'ends_at', 'status', 'auto_renew'];

    protected function casts(): array
    {
        return ['starts_at' => 'date', 'ends_at' => 'date', 'auto_renew' => 'boolean'];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function storeLevel(): BelongsTo
    {
        return $this->belongsTo(StoreLevel::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }
}
