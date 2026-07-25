<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActivity;

class UnitBreakerInspection extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'unit_id',
        'agreement_id',
        'inspection_person_id',
        'breaker_status',
        'meter_reading',
        'meter_image',
        'inspection_officer_name',
        'officer_statement',
        'signed_inspection_doc',
        'inspected_at',
    ];

    protected $casts = [
        'meter_reading' => 'decimal:2',
        'inspected_at'  => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    public function inspectionPerson(): BelongsTo
    {
        return $this->belongsTo(InspectionPerson::class, 'inspection_person_id');
    }

    public function getMeterImageUrlAttribute(): ?string
    {
        return $this->meter_image ? Storage::disk('public')->url($this->meter_image) : null;
    }

    public function getSignedInspectionDocUrlAttribute(): ?string
    {
        return $this->signed_inspection_doc ? Storage::disk('public')->url($this->signed_inspection_doc) : null;
    }
}
