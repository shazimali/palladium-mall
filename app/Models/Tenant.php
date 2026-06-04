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
        'father_name',
        'cnic',
        'date_of_birth',
        'gender',
        'marital_status',
        'phone',
        'whatsapp_number',
        'email',
        'address',
        'occupation',
        'monthly_income',
        'tenancy_type',
        'dependents',
        'adults_count',
        'children_count',
        'cnic_front_image',
        'cnic_back_image',
        'passport_photo',
        'status',
        'notes',
    ];

    protected $casts = [
        'dependents'     => 'integer',
        'adults_count'   => 'integer',
        'children_count' => 'integer',
        'date_of_birth'  => 'date',
        'monthly_income' => 'decimal:2',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function guarantor(): HasOne
    {
        return $this->hasOne(Guarantor::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function documentChecklist(): HasOne
    {
        return $this->hasOne(TenantDocumentChecklist::class);
    }

    public function moveInChecklists(): HasMany
    {
        return $this->hasMany(MoveInChecklist::class);
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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
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

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
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

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function wizardStep(): int
    {
        if (!$this->name) return 1;
        if (!$this->guarantor()->exists()) return 2;
        if (!$this->agreements()->exists()) return 3;
        if (!$this->documentChecklist()->exists()) return 4;
        if (!$this->moveInChecklists()->exists()) return 5;
        return 6;
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

    public function getPassportPhotoUrlAttribute(): ?string
    {
        return $this->passport_photo
            ? Storage::disk('public')->url($this->passport_photo)
            : null;
    }

    public function ledgerEntries(
        ?string $from = null,
        ?string $to = null,
        ?string $type = null,
        ?string $status = null
    ): Collection {
        $payments = $this->payments()
            ->when($from, fn($q) => $q->where('month', '>=', $from))
            ->when($to, fn($q) => $q->where('month', '<=', $to))
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($status, fn($q) => $q->where('status', $status))
            ->get()
            ->map(fn($p) => [
                'date'        => $p->due_date,
                'month'       => $p->month,
                'description' => ucfirst($p->type) . ' — ' . $p->month->format('F Y'),
                'category'    => 'payment',
                'type'        => $p->type,
                'amount_due'  => (float) $p->amount,
                'amount_paid' => (float) $p->amount_paid,
                'status'      => $p->status,
                'method'      => $p->payment_method,
                'reference'   => $p->reference,
                'paid_at'     => $p->paid_at,
                'source_id'   => $p->id,
                'source_type' => 'payment',
            ]);

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
                'date'        => $u->due_date,
                'month'       => $u->month,
                'description' => ucfirst($u->type) . ' — ' . $u->month->format('F Y'),
                'category'    => 'utility',
                'type'        => $u->type,
                'amount_due'  => (float) $u->bill_amount,
                'amount_paid' => $u->isPaid() ? (float) $u->bill_amount : 0,
                'status'      => $u->status,
                'method'      => null,
                'reference'   => null,
                'paid_at'     => $u->paid_at,
                'source_id'   => $u->id,
                'source_type' => 'utility',
            ]);

        $merged = $payments->concat($utilities)->sortBy('date')->values();
        $runningBalance = 0;

        return $merged->map(function ($entry) use (&$runningBalance) {
            $runningBalance += $entry['amount_due'] - $entry['amount_paid'];
            $entry['balance'] = $runningBalance;
            return $entry;
        });
    }
}