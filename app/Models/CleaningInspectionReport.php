<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CleaningInspectionReport extends Model
{
    protected $fillable = [
        'report_date', 'reported_by', 'overall_remarks',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CleaningInspectionReportItem::class);
    }

    public function cleanCount(): int
    {
        return $this->items->where('status', true)->count();
    }

    public function issueCount(): int
    {
        return $this->items->where('status', false)->count();
    }

    public function totalCount(): int
    {
        return $this->items->count();
    }
}
