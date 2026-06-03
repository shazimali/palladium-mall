<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class UtilityReading extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'meter_id',
        'tenant_id',
        'type',
        'month',
        'previous_reading',
        'current_reading',
        'units_consumed',
        'rate_per_unit',
        'bill_amount',
        'due_date',
        'status',
        'bill_proof',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'month' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'previous_reading' => 'decimal:2',
        'current_reading' => 'decimal:2',
        'units_consumed' => 'decimal:2',
        'rate_per_unit' => 'decimal:2',
        'bill_amount' => 'decimal:2',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function meter(): BelongsTo
    {
        return $this->belongsTo(Meter::class);
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('status', 'unpaid');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForMonth(Builder $query, string $month): Builder
    {
        return $query->where('month', $month);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->whereHas('unit', fn($u) => $u->where('unit_number', 'like', "%{$term}%"))
                ->orWhereHas('tenant', fn($t) => $t->where('name', 'like', "%{$term}%"));
        });
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'unpaid'
            && $this->due_date->isPast();
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'electricity' => '⚡',
            'water' => '💧',
            'gas' => '🔥',
            default => '📋',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return ucfirst($this->type);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'unpaid' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getBillProofUrlAttribute(): ?string
    {
        return $this->bill_proof
            ? Storage::temporaryUrl($this->bill_proof, now()->addMinutes(30))
            : null;
    }

    // -----------------------------------------------------------------------
    // Static helpers
    // -----------------------------------------------------------------------

    /**
     * Get the last reading for a unit + type combination.
     * Used to auto-fill previous_reading on new entry.
     */
    public static function lastReading(int $unitId, string $type): float
    {
        return (float) self::where('unit_id', $unitId)
            ->where('type', $type)
            ->latest('month')
            ->value('current_reading') ?? 0;
    }
}