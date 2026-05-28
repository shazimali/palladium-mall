<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'name',
        'cnic',
        'phone',
        'email',
        'address',
        'occupation',
        'dependents',
        'cnic_front_image',
        'cnic_back_image',
        'status',
        'notes',
    ];

    protected $casts = [
        'dependents' => 'integer',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('cnic', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhereHas('unit', fn($u) => $u->where('unit_number', 'like', "%{$term}%"));
        });
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getCnicFrontUrlAttribute(): ?string
    {
        return $this->cnic_front_image
            ? Storage::disk('public')->url($this->cnic_front_image)
            : null;
    }

    public function getCnicBackUrlAttribute(): ?string
    {
        return $this->cnic_back_image
            ? Storage::disk('public')->url($this->cnic_back_image)
            : null;
    }

    public function agreements(): HasMany
    {
        return $this->hasMany(Agreement::class);
    }

    public function activeAgreement(): HasOne
    {
        return $this->hasOne(Agreement::class)->where('status', 'active')->latestOfMany();
    }

    public function utilityReadings(): HasMany
    {
        return $this->hasMany(UtilityReading::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ledgerEntries(
        ?string $from = null,
        ?string $to = null,
        ?string $type = null,
        ?string $status = null
    ): Collection {
        // ── Payments (rent, maintenance, fine, other) ──────────────────
        $payments = $this->payments()
            ->when($from, fn($q) => $q->where('month', '>=', $from))
            ->when($to, fn($q) => $q->where('month', '<=', $to))
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($status, fn($q) => $q->where('status', $status))
            ->get()
            ->map(fn($p) => [
                'date' => $p->due_date,
                'month' => $p->month,
                'description' => ucfirst($p->type) . ' — ' . $p->month->format('F Y'),
                'category' => 'payment',
                'type' => $p->type,
                'amount_due' => (float) $p->amount,
                'amount_paid' => (float) $p->amount_paid,
                'status' => $p->status,
                'method' => $p->payment_method,
                'reference' => $p->reference,
                'paid_at' => $p->paid_at,
                'source_id' => $p->id,
                'source_type' => 'payment',
            ]);

        // ── Utility readings ───────────────────────────────────────────
        $utilities = $this->utilityReadings()
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
                'category' => 'utility',
                'type' => $u->type,
                'amount_due' => (float) $u->bill_amount,
                'amount_paid' => $u->isPaid() ? (float) $u->bill_amount : 0,
                'status' => $u->status,
                'method' => null,
                'reference' => null,
                'paid_at' => $u->paid_at,
                'source_id' => $u->id,
                'source_type' => 'utility',
            ]);

        // ── Merge, sort by date, add running balance ───────────────────
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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}