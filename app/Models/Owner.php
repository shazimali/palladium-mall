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
     * Total income share DUE to this owner (all time).
     * = Total income collected (Tenant RVs + General RVs) × partnership %
     * Excludes owner capital deposits (received_from_type = 'owner').
     */
    public function totalIncomeDue(): float
    {
        $tenantIncome = (float) ReceivingVoucher::where('received_from_type', 'tenant')->sum('amount');
        $partyIncome  = (float) GeneralReceivingVoucher::sum('amount');
        $totalIncome  = $tenantIncome + $partyIncome;

        return round($totalIncome * ((float) $this->partnership_percentage / 100), 2);
    }

    /**
     * Total profit share DUE to this owner (all time).
     * = Net Mall Profit (Tenant RVs + General RVs - Expenses) * partnership %
     */
    public function totalProfitShare(): float
    {
        $tenantIncome = (float) ReceivingVoucher::where('received_from_type', 'tenant')->sum('amount');
        $partyIncome  = (float) GeneralReceivingVoucher::sum('amount');
        $totalIncome  = $tenantIncome + $partyIncome;

        $totalExpenses = (float) Expense::sum('amount');
        $netProfit     = max(0.00, $totalIncome - $totalExpenses);

        return round($netProfit * ((float) $this->partnership_percentage / 100), 2);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Total amount already withdrawn by this owner via Withdrawal vouchers.
     */
    public function totalPaid(): float
    {
        return (float) $this->withdrawals()->sum('amount');
    }

    /**
     * Remaining pending balance owed to this owner.
     * = totalProfitShare − totalPaid
     */
    public function pendingBalance(): float
    {
        return max(0.00, round($this->totalProfitShare() - $this->totalPaid(), 2));
    }
}
