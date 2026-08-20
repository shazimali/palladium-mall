<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class InspectionReport extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'report_type_id',
        'report_date',
        'reported_by',
        'overall_remarks',
        'status',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InspectionReportItem::class);
    }

    public function passCount(): int
    {
        return $this->items->where('status', 'yes')->count();
    }

    public function failCount(): int
    {
        return $this->items->where('status', 'no')->count();
    }

    public function naCount(): int
    {
        return $this->items->where('status', 'na')->count();
    }
}
