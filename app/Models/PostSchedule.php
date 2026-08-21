<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class PostSchedule extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public const DAYS = [
        'monday'    => 'Monday',
        'tuesday'   => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday'  => 'Thursday',
        'friday'    => 'Friday',
        'saturday'  => 'Saturday',
        'sunday'    => 'Sunday',
    ];

    protected $fillable = [
        'post_schedule_head_id',
        'day_of_week',
        'employee_name',
        'user_id',
        'location',
        'start_time',
        'end_time',
        'task_title',
        'duties',
        'notes',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function head(): BelongsTo
    {
        return $this->belongsTo(PostScheduleHead::class, 'post_schedule_head_id')->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay($query, string $day)
    {
        return $query->where('day_of_week', strtolower($day));
    }

    public function getDayNameAttribute(): string
    {
        return self::DAYS[strtolower($this->day_of_week)] ?? ucfirst($this->day_of_week);
    }

    public function getShiftDisplayAttribute(): string
    {
        if ($this->start_time && $this->end_time) {
            $start = \Carbon\Carbon::parse($this->start_time)->format('h:i A');
            $end = \Carbon\Carbon::parse($this->end_time)->format('h:i A');
            return "{$start} - {$end}";
        }
        if ($this->start_time) {
            return \Carbon\Carbon::parse($this->start_time)->format('h:i A');
        }
        return '—';
    }
}
