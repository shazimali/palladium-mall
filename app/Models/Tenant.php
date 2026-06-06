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

use App\Traits\LogsActivity;

class Tenant extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
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
                'description' => $p->type_label . ' — ' . $p->month->format('F Y'),
                'category'    => in_array($p->type, ['electricity', 'water', 'gas']) ? 'utility' : 'payment',
                'type'        => $p->type,
                'amount_due'  => (float) $p->amount,
                'amount_paid' => (float) $p->amount_paid,
                'status'      => $p->status,
                'method'      => $p->payment_method,
                'reference'   => $p->reference,
                'paid_at'     => $p->paid_at,
                'source_id'   => $p->id,
                'source_type' => 'payment',
            ])
            ->sortBy('date')
            ->values();

        $runningBalance = 0;

        return $payments->map(function ($entry) use (&$runningBalance) {
            $runningBalance += $entry['amount_due'] - $entry['amount_paid'];
            $entry['balance'] = $runningBalance;
            return $entry;
        });
    }
}