<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Landlord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'cnic',
        'address',
        'notes',
        'photo',
    ];

    /**
     * Passport-size photo URL (or null).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }

    /**
     * Get the units owned by this landlord (current, via units table).
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * All ownership records (current + historical) for this landlord.
     */
    public function ownerships(): HasMany
    {
        return $this->hasMany(UnitOwnership::class)->orderBy('start_date', 'desc');
    }

    /**
     * Only active (current) ownership records.
     */
    public function currentOwnerships(): HasMany
    {
        return $this->hasMany(UnitOwnership::class)->where('is_current', true);
    }

    /**
     * Payables/Installments due from this landlord.
     */
    public function payables(): HasMany
    {
        return $this->hasMany(LandlordPayable::class)->orderBy('due_date', 'asc');
    }

    /**
     * Payouts / refunds paid to this landlord via Payment Vouchers.
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class)->where('paid_to_type', 'landlord')->orderBy('date', 'desc');
    }

    /**
     * Get the remaining opening balance (Opening Balance - Payables).
     */
    public function getRemainingOpeningBalanceAttribute(): float
    {
        return (float) ($this->ownerships->sum('credit_amount') - $this->payables->sum('amount'));
    }

    /**
     * General receiving vouchers received from this landlord.
     */
    public function generalReceivingVouchers(): HasMany
    {
        return $this->hasMany(GeneralReceivingVoucher::class)->orderBy('date', 'desc');
    }

    /**
     * Calculate current outstanding balance of the landlord.
     * Outstanding Balance = Total Value Owed - Vouchers Paid - GRV Paid - Tenant Extra Payments Paid + Mall Payouts to Landlord.
     */
    public function currentBalance(): float
    {
        $opening = (float) $this->ownerships->sum('credit_amount');
        $vouchersPaid = (float) ReceivingVoucher::where('received_from_type', 'owner')
            ->where('owner_id', $this->id)
            ->sum('amount');
        $grvPaid = (float) $this->generalReceivingVouchers()->sum('amount');
        $extraPaid = (float) Payment::where('landlord_id', $this->id)
            ->where('type', 'extra_payment')
            ->sum('amount_paid');
        $payouts = (float) $this->payouts()->sum('amount');

        return $opening - $vouchersPaid - $grvPaid - $extraPaid + $payouts;
    }
}
