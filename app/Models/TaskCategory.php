<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Tasks that belong to this category.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'category_id');
    }

    /**
     * Scope: only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
