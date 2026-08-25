<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActivity;

class InspectionReport extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'report_type_id',
        'report_type_member_id',
        'report_date',
        'reported_by',
        'overall_remarks',
        'admin_remarks',
        'admin_rating',
        'admin_photo',
        'status',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    protected $appends = [
        'admin_photo_url',
    ];

    public function getAdminPhotoUrlAttribute(): ?string
    {
        if (!$this->admin_photo) {
            return null;
        }
        return Storage::url($this->admin_photo);
    }

    public function reportType(): BelongsTo
    {
        return $this->belongsTo(ReportType::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(ReportTypeMember::class, 'report_type_member_id');
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
