<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class InspectionReportItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_report_id',
        'inspection_head_id',
        'status',
        'report_type_remark_id',
        'remarks',
        'image_path',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(InspectionReport::class, 'inspection_report_id');
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
        if (!$this->image_path) {
            return null;
        }
        return Storage::url($this->image_path);
    }
}
