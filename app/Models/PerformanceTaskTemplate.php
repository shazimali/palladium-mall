<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceTaskTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'monthly_points',
        'sort_order',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'monthly_points' => 'decimal:2',
            'is_active'      => 'boolean',
            'sort_order'     => 'integer',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dailyEntries(): HasMany
    {
        return $this->hasMany(PerformanceDailyEntry::class, 'template_id');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Daily point value for this task in the given month/year.
     * Formula: monthly_points ÷ number of calendar days in that month.
     */
    public function dailyPoints(int $month, int $year): float
    {
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        return round((float) $this->monthly_points / $days, 10);
    }
}
