<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerLevel extends Model
{
    protected $fillable = ['name', 'minimum_points', 'sort_order', 'benefit', 'status'];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
