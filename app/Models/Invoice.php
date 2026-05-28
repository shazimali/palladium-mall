<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'unit_id',
        'agreement_id',
        'invoice_number',
        'month',
        'due_date',
        'subtotal',
        'total',
        'status',
        'notes',
        'pdf_path',
        'sent_at',
    ];

    protected $casts = [
        'month' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
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

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('invoice_number', 'like', "%{$term}%")
                ->orWhereHas('tenant', fn($t) => $t->where('name', 'like', "%{$term}%"))
                ->orWhereHas('unit', fn($u) => $u->where('unit_number', 'like', "%{$term}%"));
        });
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
            'sent' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'paid' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path
            ? Storage::temporaryUrl($this->pdf_path, now()->addMinutes(30))
            : null;
    }

    // -----------------------------------------------------------------------
    // Auto-generate invoice number
    // -----------------------------------------------------------------------

    public static function generateNumber(): string
    {
        $year = now()->year;
        $prefix = 'INV-' . $year . '-';

        $last = self::withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next = $last
            ? (int) str($last)->afterLast('-')->toString() + 1
            : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}