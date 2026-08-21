<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class FlatInspectionReport extends Model
{
    use LogsActivity;

    protected $fillable = [
        'unit_id',
        'agreement_id',
        'tenant_id',
        'type', // vacant, move_in, move_out, routine
        'inspected_by',
        'inspection_member',
        'inspection_person_id',
        'inspected_at',
        'flat_condition',
        'remarks',
    ];

    protected $casts = [
        'inspected_at' => 'date',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

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

    public function getEffectiveUnitAttribute(): ?Unit
    {
        return $this->unit ?: $this->agreement?->unit;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'vacant'   => 'Vacant Inspection',
            'move_in'  => 'Move In',
            'move_out' => 'Move Out',
            'routine'  => 'Routine Inspection',
            default    => ucfirst(str_replace('_', ' ', $this->type ?? 'Flat Inspection')),
        };
    }

    public function getStageBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'vacant'   => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-300 dark:border-amber-700',
            'move_in'  => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border-green-300 dark:border-green-700',
            'move_out' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 border-red-300 dark:border-red-700',
            default    => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-300 dark:border-blue-700',
        };
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
