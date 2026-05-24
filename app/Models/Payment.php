<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'unit_id',
        'agreement_id',
        'type',
        'month',
        'amount',
        'amount_paid',
        'payment_method',
        'reference',
        'receipt',
        'status',
        'due_date',
        'paid_at',
        'notes',
        'maintenance_charge'
    ];

    protected $casts = [
        'month' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
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

    public function scopePartial(Builder $query): Builder
    {
        return $query->where('status', 'partial');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForMonth(Builder $query, string $month): Builder
    {
        return $query->where('month', $month);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', now());
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->whereHas('tenant', fn($t) => $t->where('name', 'like', "%{$term}%"))
                ->orWhereHas('unit', fn($u) => $u->where('unit_number', 'like', "%{$term}%"))
                ->orWhere('reference', 'like', "%{$term}%");
        });
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, ['unpaid', 'partial'])
            && $this->due_date->isPast();
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->amount - (float) $this->amount_paid);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'partial' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
            'unpaid' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'rent' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'maintenance' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
            'fine' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
            'other' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt
            ? Storage::temporaryUrl($this->receipt, now()->addMinutes(30))
            : null;
    }

    // -----------------------------------------------------------------------
    // Status calculation
    // -----------------------------------------------------------------------

    public static function calculateStatus(float $amount, float $amountPaid): string
    {
        if ($amountPaid <= 0) {
            return 'unpaid';
        }

        if ($amountPaid >= $amount) {
            return 'paid';
        }

        return 'partial';
    }
}