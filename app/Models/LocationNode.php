<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LocationNode extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'lat', 'lng', 'status'];

    protected function casts(): array
    {
        return ['lat' => 'decimal:7', 'lng' => 'decimal:7'];
    }

    public function distancesFrom(): HasMany
    {
        return $this->hasMany(LocationDistance::class, 'origin_node_id');
    }

    public function distancesTo(): HasMany
    {
        return $this->hasMany(LocationDistance::class, 'destination_node_id');
    }
}
