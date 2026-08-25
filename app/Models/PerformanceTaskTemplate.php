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
        'type',
        'report_type_id',
        'task_id',
        'monthly_points',
        'is_daily',
        'target_count',
        'sort_order',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'monthly_points' => 'decimal:2',
            'is_daily'       => 'boolean',
            'target_count'   => 'integer',
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

    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class, 'report_type_id');
    }

    public function linkedTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
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
        return $days > 0 ? round((float) $this->monthly_points / $days, 10) : 0.0;
    }

    /**
     * Point value per unit (per day if daily, or per task if count-based non-daily).
     */
    public function unitPoints(int $month, int $year): float
    {
        if ($this->is_daily) {
            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            return $days > 0 ? round((float) $this->monthly_points / $days, 2) : 0.0;
        }

        $count = max(1, (int) ($this->target_count ?? 1));
        return round((float) $this->monthly_points / $count, 2);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'dynamic_report' => 'Dynamic Report',
            'task'           => 'Assigned Task',
            default          => 'Custom Task',
        };
    }
}
