<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use App\Traits\LogsActivity;

class Agreement extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected static function booted()
    {
        static::saved(function ($agreement) {
            if ($agreement->status === 'active' && (float) $agreement->security_deposit > 0) {
                $secPayment = Payment::where('agreement_id', $agreement->id)
                    ->where('type', 'security_deposit')
                    ->first();

                if (!$secPayment && $agreement->tenant_id) {
                    $month = $agreement->start_date->copy()->startOfMonth()->toDateString();
                    $dueDate = $agreement->start_date->toDateString();

                    Payment::create([
                        'tenant_id'    => $agreement->tenant_id,
                        'unit_id'      => $agreement->unit_id,
                        'agreement_id' => $agreement->id,
                        'type'         => 'security_deposit',
                        'month'        => $month,
                        'amount'       => $agreement->security_deposit,
                        'amount_paid'  => 0,
                        'status'       => 'unpaid',
                        'due_date'     => $dueDate,
                    ]);
                } else if ($secPayment) {
                    if ($secPayment->status === 'unpaid' && (float) $secPayment->amount !== (float) $agreement->security_deposit) {
                        $secPayment->update([
                            'amount' => $agreement->security_deposit,
                        ]);
                    }
                }
            }
        });
    }

    protected $fillable = [
        'tenant_id',
        'unit_id',
        'start_date',
        'end_date',
        'monthly_rent',
        'maintenance_charge',
        'security_deposit',
        'payment_due_day',
        'grace_period_days',
        'notice_period_months',
        'fine_per_day',
        'terms',
        'status',
        'document',
        'govt_document',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_rent' => 'decimal:2',
        'maintenance_charge' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'fine_per_day' => 'decimal:2',
        'grace_period_days' => 'integer',
        'payment_due_day' => 'integer',
        'notice_period_months' => 'integer',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class)->withTrashed();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'expired');
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->where('status', 'active')
            ->whereBetween('end_date', [
                Carbon::today(),
                Carbon::today()->addDays($days),
            ]);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->whereHas('tenant', fn($t) => $t->where('name', 'like', "%{$term}%")
                ->orWhere('cnic', 'like', "%{$term}%"))
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

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isTerminated(): bool
    {
        return $this->status === 'terminated';
    }

    public function durationInMonths(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        return (int) $this->start_date->diffInMonths($this->end_date);
    }

    public function daysRemaining(): int
    {
        if (!$this->isActive() || !$this->end_date) {
            return 0;
        }

        return (int) max(0, Carbon::today()->diffInDays($this->end_date, false));
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->isActive() && $this->daysRemaining() <= $days;
    }

    public function getDocumentUrlAttribute(): ?string
    {
        return $this->document
            ? Storage::temporaryUrl($this->document, now()->addMinutes(30))
            : null;
    }

    public function getGovtDocumentUrlAttribute(): ?string
    {
        return $this->govt_document
            ? Storage::disk('public')->url($this->govt_document)
            : null;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'expired' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
            'terminated' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function partners(): HasMany
    {
        return $this->hasMany(TenantPartner::class);
    }

    public function guarantors(): HasMany
    {
        return $this->hasMany(Guarantor::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function documentChecklist(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TenantDocumentChecklist::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(MoveInChecklist::class);
    }

    public function moveInChecklist(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MoveInChecklist::class)->where('type', 'move_in');
    }

    public function moveOutChecklist(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MoveInChecklist::class)->where('type', 'move_out');
    }
}