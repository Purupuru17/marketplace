<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id', 'provider', 'payment_method', 'bank_snapshot', 'provider_order_id',
        'provider_transaction_id', 'amount', 'status', 'paid_at', 'expired_at', 'raw_response',
        'payment_proof_path',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'bank_snapshot' => 'array',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(PaymentWebhookLog::class);
    }
}
