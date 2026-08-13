<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionHead extends Model
{
    protected $fillable = ['name', 'key', 'type', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function flatInspectionItems(): HasMany
    {
        return $this->hasMany(FlatInspectionReportItem::class);
    }

    public function cleaningInspectionItems(): HasMany
    {
        return $this->hasMany(CleaningInspectionReportItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFlatInspection($query)
    {
        return $query->where('type', 'flat_inspection');
    }

    public function scopeCleaning($query)
    {
        return $query->where('type', 'cleaning');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'flat_inspection' => 'Flat Inspection',
            'cleaning'        => 'Cleaning',
            default           => ucfirst($this->type),
        };
    }
}
