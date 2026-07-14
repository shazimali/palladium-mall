<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;

class Owner extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'partnership_percentage',
        'notes',
    ];

    protected $casts = [
        'partnership_percentage' => 'decimal:2',
    ];

    /**
     * Get the receiving vouchers recorded from/for this owner.
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(ReceivingVoucher::class);
    }

    /**
     * Get the payables logged for this owner.
     */
    public function payables(): HasMany
    {
        return $this->hasMany(OwnerPayable::class);
    }

    /**
     * Get the receivables logged for this owner.
     */
    public function receivables(): HasMany
    {
        return $this->hasMany(OwnerReceivable::class);
    }

    /**
     * Total income share DUE to this owner (all time).
     * = (Total income collected × partnership %) + Owner Receivables (Capital Deposits / Loans Inflow)
     * Excludes owner capital deposits (received_from_type = 'owner').
     */
    public function totalIncomeDue(): float
    {
        $tenantIncome = (float) ReceivingVoucher::where('received_from_type', 'tenant')->sum('amount');
        $partyIncome  = (float) GeneralReceivingVoucher::sum('amount');
        $totalIncome  = $tenantIncome + $partyIncome;

        $share = $totalIncome * ((float) $this->partnership_percentage / 100);
        $receivables = (float) $this->receivables()->sum('amount');

        return round($share + $receivables, 2);
    }

    /**
     * Total amount already paid out to this owner.
     * = Payment Vouchers + Owner Payables (cash outflows to owner)
     */
    public function totalPaid(): float
    {
        $vouchers = (float) PaymentVoucher::where('paid_to_type', 'owner')
            ->where('owner_id', $this->id)
            ->sum('amount');

        $payables = (float) $this->payables()->sum('amount');

        return round($vouchers + $payables, 2);
    }

    /**
     * Remaining pending balance owed to this owner.
     * = totalIncomeDue − totalPaid
     */
    public function pendingBalance(): float
    {
        return max(0.00, round($this->totalIncomeDue() - $this->totalPaid(), 2));
    }
}
