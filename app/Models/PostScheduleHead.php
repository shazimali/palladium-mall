<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class PostScheduleHead extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'color',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(PostSchedule::class)->orderBy('sort_order')->orderBy('start_time');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->color) {
            'emerald' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700',
            'amber'   => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-300 dark:border-amber-700',
            'purple'  => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border-purple-300 dark:border-purple-700',
            'rose'    => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border-rose-300 dark:border-rose-700',
            'indigo'  => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 border-indigo-300 dark:border-indigo-700',
            'cyan'    => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/40 dark:text-cyan-300 border-cyan-300 dark:border-cyan-700',
            'gray'    => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-300 dark:border-gray-700',
            default   => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-300 dark:border-blue-700',
        };
    }
}
