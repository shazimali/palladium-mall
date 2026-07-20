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

    public function transferredOutGeneralReceivingVouchers(): HasMany
    {
        return $this->hasMany(GeneralReceivingVoucher::class, 'from_payment_account_id');
    }

    public function paymentVouchers(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class);
    }

    public function receivedPaymentVouchers(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class, 'to_payment_account_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
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

        $inflowTransfers = array_key_exists('received_payment_vouchers_sum_amount', $this->attributes)
            ? (float) $this->attributes['received_payment_vouchers_sum_amount']
            : ($this->relationLoaded('receivedPaymentVouchers') ? (float) $this->receivedPaymentVouchers->sum('amount') : (float) $this->receivedPaymentVouchers()->sum('amount'));

        $outflowPVs = array_key_exists('payment_vouchers_sum_amount', $this->attributes)
            ? (float) $this->attributes['payment_vouchers_sum_amount']
            : ($this->relationLoaded('paymentVouchers') ? (float) $this->paymentVouchers->sum('amount') : (float) $this->paymentVouchers()->sum('amount'));

        $outflowGRVTransfers = array_key_exists('transferred_out_general_receiving_vouchers_sum_amount', $this->attributes)
            ? (float) $this->attributes['transferred_out_general_receiving_vouchers_sum_amount']
            : ($this->relationLoaded('transferredOutGeneralReceivingVouchers') ? (float) $this->transferredOutGeneralReceivingVouchers->sum('amount') : (float) $this->transferredOutGeneralReceivingVouchers()->sum('amount'));

        $outflowExpenses = array_key_exists('expenses_sum_amount', $this->attributes)
            ? (float) $this->attributes['expenses_sum_amount']
            : ($this->relationLoaded('expenses') ? (float) $this->expenses->sum('amount') : (float) $this->expenses()->sum('amount'));

        $outflowWithdrawals = array_key_exists('withdrawals_sum_amount', $this->attributes)
            ? (float) $this->attributes['withdrawals_sum_amount']
            : ($this->relationLoaded('withdrawals') ? (float) $this->withdrawals->sum('amount') : (float) $this->withdrawals()->sum('amount'));

        return $opening + $inflowRVs + $inflowGRVs + $inflowTransfers - $outflowPVs - $outflowGRVTransfers - $outflowExpenses - $outflowWithdrawals;
    }
}
