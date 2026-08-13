<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlatInspectionReport extends Model
{
    protected $fillable = [
        'agreement_id', 'tenant_id', 'type',
        'inspected_by', 'inspection_member', 'inspection_person_id',
        'inspected_at', 'flat_condition', 'remarks',
    ];

    protected $casts = [
        'inspected_at' => 'date',
    ];

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function inspectionPerson(): BelongsTo
    {
        return $this->belongsTo(InspectionPerson::class, 'inspection_person_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FlatInspectionReportItem::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'move_in' ? 'Move In' : 'Move Out';
    }

    public function passCount(): int
    {
        return $this->items->where('status', true)->count();
    }

    public function failCount(): int
    {
        return $this->items->where('status', false)->count();
    }

    public function totalCount(): int
    {
        return $this->items->count();
    }
}
