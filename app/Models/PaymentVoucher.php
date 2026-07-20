<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class PaymentVoucher extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'voucher_no',
        'date',
        'amount',
        'paid_to_type',
        'owner_id',
        'party_id',
        'tenant_id',
        'landlord_id',
        'unit_id',
        'other_name',
        'is_advance',
        'payment_method',
        'payment_account_id',
        'to_payment_account_id',
        'reference',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'is_advance' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($voucher) {
            if (empty($voucher->voucher_no)) {
                $voucher->voucher_no = 'TEMP-PV-' . \Illuminate\Support\Str::random(12);
            }
        });

        static::created(function ($voucher) {
            if (strpos($voucher->voucher_no, 'TEMP-PV-') === 0) {
                $voucher->voucher_no = 'PM-PV-' . str_pad($voucher->id, 5, '0', STR_PAD_LEFT);
                $voucher->saveQuietly();
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class)->withTrashed();
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class)->withTrashed();
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class)->withTrashed();
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class)->withTrashed();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function toPaymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class, 'to_payment_account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
