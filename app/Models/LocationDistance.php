<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationDistance extends Model
{
    use HasUuids;

    protected $fillable = ['origin_node_id', 'destination_node_id', 'distance_km'];

    protected function casts(): array
    {
        return ['distance_km' => 'decimal:2'];
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(LocationNode::class, 'origin_node_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(LocationNode::class, 'destination_node_id');
    }
}
