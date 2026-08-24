<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class PerformanceDailyEntry extends Model
{
    protected $fillable = [
        'user_id',
        'template_id',
        'date',
        'is_done',
        'points_earned',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date'          => 'date',
            'is_done'       => 'boolean',
            'points_earned' => 'decimal:2',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(PerformanceTaskTemplate::class, 'template_id');
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeForMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->where('date', $date);
    }

    public function scopeDone(Builder $query): Builder
    {
        return $query->where('is_done', true);
    }
}
