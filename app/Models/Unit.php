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

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_number',
        'floor_id',
        'block_id',
        'area_id',
        'type',
        'status',
        'area_sqft',
        'notes',
    ];

    protected $casts = [
        'area_sqft' => 'decimal:2',
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

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class)->where('status', 'active');
    }

    public function hasActiveTenant(): bool
    {
        return $this->tenant()->exists();
    }

    public function utilityReadings(): HasMany
    {
        return $this->hasMany(UtilityReading::class);
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


    public function ledgerEntries(
        ?string $from = null,
        ?string $to = null,
        ?string $type = null,
        ?string $status = null
    ): Collection {
        // All payments for this unit
        $payments = Payment::with('tenant')
            ->where('unit_id', $this->id)
            ->when($from, fn($q) => $q->where('month', '>=', $from))
            ->when($to, fn($q) => $q->where('month', '<=', $to))
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($status, fn($q) => $q->where('status', $status))
            ->get()
            ->map(fn($p) => [
                'date' => $p->due_date,
                'month' => $p->month,
                'description' => ucfirst($p->type) . ' — ' . $p->month->format('F Y'),
                'tenant' => $p->tenant->name,
                'category' => 'payment',
                'type' => $p->type,
                'amount_due' => (float) $p->amount,
                'amount_paid' => (float) $p->amount_paid,
                'status' => $p->status,
                'paid_at' => $p->paid_at,
                'source_type' => 'payment',
            ]);

        // All utility readings for this unit
        $utilities = UtilityReading::with('tenant')
            ->where('unit_id', $this->id)
            ->when($from, fn($q) => $q->where('month', '>=', $from))
            ->when($to, fn($q) => $q->where('month', '<=', $to))
            ->when(
                $type && in_array($type, ['electricity', 'water', 'gas']),
                fn($q) => $q->where('type', $type)
            )
            ->when($status, fn($q) => $q->where('status', $status))
            ->get()
            ->map(fn($u) => [
                'date' => $u->due_date,
                'month' => $u->month,
                'description' => ucfirst($u->type) . ' — ' . $u->month->format('F Y'),
                'tenant' => $u->tenant->name,
                'category' => 'utility',
                'type' => $u->type,
                'amount_due' => (float) $u->bill_amount,
                'amount_paid' => $u->isPaid() ? (float) $u->bill_amount : 0,
                'status' => $u->status,
                'paid_at' => $u->paid_at,
                'source_type' => 'utility',
            ]);

        $merged = $payments->concat($utilities)
            ->sortBy('date')
            ->values();

        $runningBalance = 0;

        return $merged->map(function ($entry) use (&$runningBalance) {
            $runningBalance += $entry['amount_due'] - $entry['amount_paid'];
            $entry['balance'] = $runningBalance;
            return $entry;
        });
    }
}