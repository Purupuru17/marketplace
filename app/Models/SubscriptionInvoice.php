<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    use HasUuids;

    protected $fillable = ['subscription_id', 'invoice_no', 'amount', 'status', 'due_at', 'paid_at'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'due_at' => 'date', 'paid_at' => 'datetime'];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
