<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'area_sqft',
        'notes',
        'landlord_id',
        'date',
    ];

    protected $casts = [
        'area_sqft' => 'decimal:2',
        'date' => 'date',
    ];

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeVacant(Builder $query): Builder
    {
        return $query->where('status', 'vacant');
    }

    public function scopeOccupied(Builder $query): Builder
    {
        return $query->where('status', 'occupied');
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

    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'occupied' => 'badge-success',
            'vacant' => 'badge-warning',
            'sold' => 'badge-secondary',
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


}