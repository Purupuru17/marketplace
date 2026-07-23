<?php

namespace IdCore\CoreStarter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'url',
        'icon',
        'actions',
        'sort_by',
        'is_active',
    ];

    protected $casts = [
        'actions' => 'array',
        'is_active' => 'boolean',
    ];

     /**
     * Relasi ke Anak Menu (Submenus) - Terurut otomatis
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id')
            ->where('is_active', true)
            ->orderBy('sort_by', 'asc');
    }

    /**
     * Relasi ke Induk Menu (Parent)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }
}
