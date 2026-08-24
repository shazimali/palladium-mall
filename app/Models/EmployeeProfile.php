<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'employee_code',
        'designation',
        'department',
        'joined_at',
        'basic_salary',
        'fuel_allowance',
        'attendance_incentive',
        'collection_incentive_pct',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'joined_at'                 => 'date',
            'basic_salary'              => 'decimal:2',
            'fuel_allowance'            => 'decimal:2',
            'attendance_incentive'      => 'decimal:2',
            'collection_incentive_pct'  => 'decimal:2',
            'is_active'                 => 'boolean',
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

    // -----------------------------------------------------------------------
    // Computed Attributes
    // -----------------------------------------------------------------------

    /**
     * Fixed monthly total (without task performance component).
     */
    public function getTotalBasicAttribute(): float
    {
        return (float) $this->basic_salary
            + (float) $this->fuel_allowance
            + (float) $this->attendance_incentive;
    }

    /**
     * Collection incentive amount given a performance percentage.
     */
    public function collectionIncentiveAmount(float $performancePct): float
    {
        return round((float) $this->basic_salary * ((float) $this->collection_incentive_pct / 100) * ($performancePct / 100), 2);
    }
}
