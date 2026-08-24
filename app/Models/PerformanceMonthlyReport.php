<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class PerformanceMonthlyReport extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'working_days',
        'days_present',
        'days_absent',
        'total_max_points',
        'total_earned_points',
        'performance_percentage',
        'grade',
        'basic_salary',
        'fuel_allowance',
        'attendance_incentive',
        'collection_incentive_pct',
        'collection_incentive_amt',
        'other_works_amount',
        'total_basic',
        'final_salary',
        'generated_at',
        'generated_by',
    ];

    protected function casts(): array
    {
        return [
            'generated_at'           => 'datetime',
            'performance_percentage' => 'decimal:2',
            'total_max_points'       => 'decimal:2',
            'total_earned_points'    => 'decimal:2',
            'basic_salary'           => 'decimal:2',
            'fuel_allowance'         => 'decimal:2',
            'attendance_incentive'   => 'decimal:2',
            'collection_incentive_pct' => 'decimal:2',
            'collection_incentive_amt' => 'decimal:2',
            'other_works_amount'     => 'decimal:2',
            'total_basic'            => 'decimal:2',
            'final_salary'           => 'decimal:2',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function getMonthNameAttribute(): string
    {
        return \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    public function getGradeColorAttribute(): string
    {
        return match ($this->grade) {
            'Excellent' => 'green',
            'Good'      => 'blue',
            'Average'   => 'amber',
            'Poor'      => 'red',
            default     => 'gray',
        };
    }
}
