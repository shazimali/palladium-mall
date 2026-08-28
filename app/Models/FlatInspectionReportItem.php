<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FlatInspectionReportItem extends Model
{
    protected $fillable = [
        'flat_inspection_report_id',
        'inspection_head_id',
        'status',
        'report_type_remark_id',
        'image_path',
        'remarks',
        'admin_rating',
        'admin_remarks',
        'admin_photo',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(FlatInspectionReport::class, 'flat_inspection_report_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(InspectionHead::class, 'inspection_head_id');
    }

    public function systemRemark(): BelongsTo
    {
        return $this->belongsTo(ReportTypeRemark::class, 'report_type_remark_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    public function getAdminPhotoUrlAttribute(): ?string
    {
        return $this->admin_photo ? Storage::url($this->admin_photo) : null;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === null) return 'N/A';
        return $this->status ? '✅ Pass' : '❌ Fail';
    }
}
