<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionHead extends Model
{
    protected $fillable = ['name', 'key', 'type', 'types', 'is_active', 'sort_order'];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
        'types'      => 'array',
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

    public function scopeForType($query, string $type)
    {
        return $query->where(function ($q) use ($type) {
            $q->whereJsonContains('types', $type)
              ->orWhere('type', $type);
        });
    }

    public function scopeFlatInspection($query)
    {
        return $this->scopeForType($query, 'flat_inspection');
    }

    public function scopeCleaning($query)
    {
        return $this->scopeForType($query, 'cleaning');
    }

    public function reportType()
    {
        return $this->belongsTo(ReportType::class, 'type', 'key');
    }

    public function getTypesListAttribute(): array
    {
        if (!empty($this->types) && is_array($this->types)) {
            return $this->types;
        }
        return $this->type ? [$this->type] : [];
    }

    public function getTypeLabelsAttribute(): array
    {
        $keys = $this->types_list;
        $reportTypes = ReportType::whereIn('key', $keys)->pluck('name', 'key')->toArray();

        $labels = [];
        foreach ($keys as $k) {
            $labels[] = $reportTypes[$k] ?? ucwords(str_replace('_', ' ', $k));
        }

        return $labels;
    }

    public function getTypeLabelAttribute(): string
    {
        $labels = $this->type_labels;
        return !empty($labels) ? implode(', ', $labels) : '—';
    }
}
