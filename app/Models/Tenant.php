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
        'dependents' => 'integer',
        'adults_count' => 'integer',
        'children_count' => 'integer',
        'date_of_birth' => 'date',
        'monthly_income' => 'decimal:2',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function guarantors(): HasMany
    {
        return $this->hasMany(Guarantor::class);
    }

    /**
     * Backward-compat: returns first guarantor (or null)
     */
    public function getGuarantorAttribute(): ?Guarantor
    {
        return $this->guarantors->first();
    }

    public function getGuarantorsAttribute(): Collection
    {
        $agreement = $this->activeAgreement ?: $this->agreements()->latest()->first();
        if ($agreement) {
            return $agreement->guarantors()->get();
        }
        return $this->guarantors()->get();
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function getEmergencyContactsAttribute(): Collection
    {
        $agreement = $this->activeAgreement ?: $this->agreements()->latest()->first();
        if ($agreement) {
            return $agreement->emergencyContacts()->get();
        }
        return $this->emergencyContacts()->get();
    }

    public function partners(): HasMany
    {
        return $this->hasMany(TenantPartner::class);
    }

    public function getPartnersAttribute(): Collection
    {
        $agreement = $this->activeAgreement ?: $this->agreements()->latest()->first();
        if ($agreement) {
            return $agreement->partners()->get();
        }
        return $this->partners()->get();
    }

    public function documentChecklist(): HasOne
    {
        return $this->hasOne(TenantDocumentChecklist::class);
    }

    public function getDocumentChecklistAttribute(): ?TenantDocumentChecklist
    {
        $agreement = $this->activeAgreement ?: $this->agreements()->latest()->first();
        if ($agreement) {
            return $agreement->documentChecklist;
        }
        return $this->documentChecklist;
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
        return $this->hasOne(Agreement::class)->where('status', 'active');
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
        if (!$this->name)
            return 1;

        $draft = $this->agreements()->where('status', 'draft')->latest()->first();
        if (!$draft) {
            if (!$this->guarantors()->exists())
                return 2;
            if (!$this->agreements()->exists())
                return 3;
            if (!$this->documentChecklist()->exists())
                return 4;
            if (!$this->moveInChecklists()->exists())
                return 5;
            return 6;
        }

        if (!$draft->guarantors()->exists())
            return 2;
        if (!$draft->start_date)
            return 3;
        if (!$draft->documentChecklist()->exists())
            return 4;
        if (!$draft->moveInChecklist()->exists())
            return 5;
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

}