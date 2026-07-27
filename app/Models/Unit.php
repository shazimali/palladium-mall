<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\UnitOwnership;
use App\Models\OtherTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

use App\Traits\LogsActivity;

class Unit extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'unit_number',
        'floor_id',
        'block_id',
        'area_id',
        'type',
        'status',
        'breaker_status',
        'is_self',
        'default_maintenance_charge',
        'default_monthly_rent',
        'file_no',
        'area_sqft',
        'notes',
        'landlord_id',
        'date',
    ];

    protected $casts = [
        'area_sqft'                  => 'decimal:2',
        'date'                       => 'date',
        'is_self'                    => 'boolean',
        'default_maintenance_charge' => 'decimal:2',
        'default_monthly_rent'       => 'decimal:2',
    ];

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeVacant(Builder $query): Builder
    {
        return $query->where('status', 'vacant');
    }

    public function scopeRented(Builder $query): Builder
    {
        return $query->where('status', 'rented');
    }

    public function scopeSelf(Builder $query): Builder
    {
        return $query->where('status', 'self');
    }

    public function scopeIsSelf(Builder $query): Builder
    {
        return $query->where('is_self', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('unit_number', 'like', "%{$term}%")
                ->orWhereHas('floor', fn($f) => $f->where('name', 'like', "%{$term}%"))
                ->orWhereHas('block', fn($b) => $b->where('name', 'like', "%{$term}%"))
                ->orWhereHas('area', fn($a) => $a->where('name', 'like', "%{$term}%"));
        });
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function isVacant(): bool
    {
        return $this->status === 'vacant';
    }

    public function isRented(): bool
    {
        return $this->status === 'rented';
    }

    public function isSelf(): bool
    {
        return $this->status === 'self';
    }

    public function isSelfOwned(): bool
    {
        return $this->is_self === true;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'rented' => 'badge-success',
            'vacant' => 'badge-warning',
            'self' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class);
    }

    public function agreements(): HasMany
    {
        return $this->hasMany(Agreement::class)->orderBy('start_date', 'desc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('due_date', 'desc');
    }

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class)->where('status', 'active');
    }

    public function hasActiveTenant(): bool
    {
        return $this->tenant()->exists();
    }

    public function meters(): HasMany
    {
        return $this->hasMany(Meter::class);
    }

    /**
     * All ownership records for this unit (history, newest first).
     */
    public function ownerships(): HasMany
    {
        return $this->hasMany(UnitOwnership::class)->orderBy('start_date', 'desc');
    }

    /**
     * The currently active ownership record.
     */
    public function currentOwnership(): HasOne
    {
        return $this->hasOne(UnitOwnership::class)->where('is_current', true);
    }

    public function activeAgreement(): HasOne
    {
        return $this->hasOne(Agreement::class)->where('status', 'active');
    }

    public function electricityMeter(): HasOne
    {
        return $this->hasOne(Meter::class)->where('type', 'electricity');
    }

    public function waterMeter(): HasOne
    {
        return $this->hasOne(Meter::class)->where('type', 'water');
    }

    public function gasMeter(): HasOne
    {
        return $this->hasOne(Meter::class)->where('type', 'gas');
    }

    public function otherTenant(): HasOne
    {
        return $this->hasOne(OtherTenant::class);
    }

    public function otherTenantHistory(): HasMany
    {
        return $this->hasMany(OtherTenantUnitHistory::class)->orderBy('attached_at', 'desc');
    }

    public function breakerInspections(): HasMany
    {
        return $this->hasMany(UnitBreakerInspection::class)->orderBy('inspected_at', 'desc')->orderBy('id', 'desc');
    }

    public function latestBreakerInspection(): HasOne
    {
        return $this->hasOne(UnitBreakerInspection::class)->latestOfMany();
    }

    public function isBreakerOn(): bool
    {
        return $this->breaker_status === 'on';
    }

    public function isBreakerOff(): bool
    {
        return $this->breaker_status === 'off';
    }

    public function hasVacantBreakerWarning(): bool
    {
        $isEffectiveVacant = $this->status === 'vacant' && !($this->is_self && $this->otherTenant);
        return $isEffectiveVacant && $this->isBreakerOn();
    }

    public function getLatestMeterReading(): ?float
    {
        // 1. Check latest electricity payment for this unit with current_reading
        $latestPaymentReading = Payment::where('unit_id', $this->id)
            ->where(function ($q) {
                $q->where('type', 'electricity')->orWhere('type', 'meter');
            })
            ->whereNotNull('current_reading')
            ->where('current_reading', '>', 0)
            ->latest('id')
            ->value('current_reading');

        if ($latestPaymentReading !== null) {
            return (float) $latestPaymentReading;
        }

        // 2. Check latest Breaker Inspection
        $latestInspectionReading = $this->breakerInspections()
            ->whereNotNull('meter_reading')
            ->where('meter_reading', '>', 0)
            ->value('meter_reading');

        if ($latestInspectionReading !== null) {
            return (float) $latestInspectionReading;
        }

        // 3. Check latest Agreement final or initial meter reading for this unit
        $latestAgreementReading = Agreement::where('unit_id', $this->id)
            ->where(function ($q) {
                $q->whereNotNull('final_meter_reading')->orWhereNotNull('initial_meter_reading');
            })
            ->latest('id')
            ->first();

        if ($latestAgreementReading) {
            if ($latestAgreementReading->final_meter_reading > 0) {
                return (float) $latestAgreementReading->final_meter_reading;
            }
            if ($latestAgreementReading->initial_meter_reading > 0) {
                return (float) $latestAgreementReading->initial_meter_reading;
            }
        }

        // 4. Check Meter model payments if exists for this unit
        $meterReading = Payment::whereHas('meter', function ($q) {
                $q->where('unit_id', $this->id)->where('type', 'electricity');
            })
            ->whereNotNull('current_reading')
            ->where('current_reading', '>', 0)
            ->latest('id')
            ->value('current_reading');

        return $meterReading ? (float) $meterReading : null;
    }
}