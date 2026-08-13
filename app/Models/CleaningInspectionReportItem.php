<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CleaningInspectionReportItem extends Model
{
    protected $fillable = [
        'cleaning_inspection_report_id', 'inspection_head_id',
        'status', 'image_path', 'remarks',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(CleaningInspectionReport::class, 'cleaning_inspection_report_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(InspectionHead::class, 'inspection_head_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === null) return 'N/A';
        return $this->status ? '✅ Clean' : '❌ Issue';
    }
}
