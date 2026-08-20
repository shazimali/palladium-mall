<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Traits\LogsActivity;

class ReportType extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'key',
        'description',
        'is_daily',
        'daily_start_time',
        'daily_end_time',
        'one_per_user_daily',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_daily'           => 'boolean',
        'one_per_user_daily' => 'boolean',
        'is_active'          => 'boolean',
        'sort_order'         => 'integer',
    ];

    public function inspectionHeads(): HasMany
    {
        return $this->hasMany(InspectionHead::class, 'type', 'key');
    }

    public function remarks(): HasMany
    {
        return $this->hasMany(ReportTypeRemark::class)->ordered();
    }

    public function activeRemarks(): HasMany
    {
        return $this->hasMany(ReportTypeRemark::class)->active()->ordered();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(InspectionReport::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Check if the current local time falls within the allowed generation window for daily reports.
     */
    public function isWithinAllowedTimeWindow(): bool
    {
        if (!$this->is_daily) {
            return true;
        }

        $now = now()->format('H:i:s');
        $start = $this->daily_start_time ?? '09:00:00';
        $end = $this->daily_end_time ?? '20:00:00';

        return ($now >= $start && $now <= $end);
    }

    /**
     * Human-friendly formatted time window, e.g. "09:00 AM - 08:00 PM"
     */
    public function getTimeWindowDisplayAttribute(): string
    {
        if (!$this->is_daily) {
            return 'Anytime';
        }

        $start = Carbon::createFromTimeString($this->daily_start_time ?? '09:00:00')->format('h:i A');
        $end = Carbon::createFromTimeString($this->daily_end_time ?? '20:00:00')->format('h:i A');

        return "{$start} - {$end}";
    }
}
