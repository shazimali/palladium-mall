<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
use App\Traits\LogsActivity;

class PaymentAccount extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;
 
    protected $fillable = [
        'name',
        'account_number',
        'account_holder',
        'bank_name',
        'is_active',
        'notes',
        'type',
        'opening_balance',
    ];
 
    protected $casts = [
        'is_active' => 'boolean',
        'opening_balance' => 'decimal:2',
    ];
 
    /**
     * Get the payments associated with this payment account.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function receivingVouchers(): HasMany
    {
        return $this->hasMany(ReceivingVoucher::class);
    }

    public function generalReceivingVouchers(): HasMany
    {
        return $this->hasMany(GeneralReceivingVoucher::class);
    }

    public function paymentVouchers(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function ownerPayables(): HasMany
    {
        return $this->hasMany(OwnerPayable::class);
    }

    public function ownerReceivables(): HasMany
    {
        return $this->hasMany(OwnerReceivable::class);
    }

    /**
     * Get the computed current balance of the payment account.
     */
    public function getCurrentBalanceAttribute(): float
    {
        $opening = (float) $this->opening_balance;

        $inflowRVs = array_key_exists('receiving_vouchers_sum_amount', $this->attributes)
            ? (float) $this->attributes['receiving_vouchers_sum_amount']
            : ($this->relationLoaded('receivingVouchers') ? (float) $this->receivingVouchers->sum('amount') : (float) $this->receivingVouchers()->sum('amount'));

        $inflowGRVs = array_key_exists('general_receiving_vouchers_sum_amount', $this->attributes)
            ? (float) $this->attributes['general_receiving_vouchers_sum_amount']
            : ($this->relationLoaded('generalReceivingVouchers') ? (float) $this->generalReceivingVouchers->sum('amount') : (float) $this->generalReceivingVouchers()->sum('amount'));

        $inflowORVs = array_key_exists('owner_receivables_sum_amount', $this->attributes)
            ? (float) $this->attributes['owner_receivables_sum_amount']
            : ($this->relationLoaded('ownerReceivables') ? (float) $this->ownerReceivables->sum('amount') : (float) $this->ownerReceivables()->sum('amount'));

        $outflowPVs = array_key_exists('payment_vouchers_sum_amount', $this->attributes)
            ? (float) $this->attributes['payment_vouchers_sum_amount']
            : ($this->relationLoaded('paymentVouchers') ? (float) $this->paymentVouchers->sum('amount') : (float) $this->paymentVouchers()->sum('amount'));

        $outflowExpenses = array_key_exists('expenses_sum_amount', $this->attributes)
            ? (float) $this->attributes['expenses_sum_amount']
            : ($this->relationLoaded('expenses') ? (float) $this->expenses->sum('amount') : (float) $this->expenses()->sum('amount'));

        $outflowOPVs = array_key_exists('owner_payables_sum_amount', $this->attributes)
            ? (float) $this->attributes['owner_payables_sum_amount']
            : ($this->relationLoaded('ownerPayables') ? (float) $this->ownerPayables->sum('amount') : (float) $this->ownerPayables()->sum('amount'));

        return $opening + $inflowRVs + $inflowGRVs + $inflowORVs - $outflowPVs - $outflowExpenses - $outflowOPVs;
    }
}
