<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    use HasUuids;

    protected $fillable = [
        'customer_id', 'location_node_id', 'label', 'recipient_name',
        'phone', 'full_address', 'lat', 'lng', 'is_default',
    ];

    protected function casts(): array
    {
        return ['is_default' => 'boolean', 'lat' => 'decimal:7', 'lng' => 'decimal:7'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function locationNode(): BelongsTo
    {
        return $this->belongsTo(LocationNode::class);
    }
}
