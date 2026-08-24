<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class EmployeeAttendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'status',
        'check_in_at',
        'check_out_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeForMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopePresent(Builder $query): Builder
    {
        return $query->whereIn('status', ['present', 'half_day']);
    }

    public function scopeAbsent(Builder $query): Builder
    {
        return $query->where('status', 'absent');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present'  => 'Present',
            'absent'   => 'Absent',
            'leave'    => 'Leave',
            'half_day' => 'Half Day',
            default    => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present'  => 'green',
            'absent'   => 'red',
            'leave'    => 'blue',
            'half_day' => 'amber',
            default    => 'gray',
        };
    }
}
