<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class OtherOwnedRentPurchaseVoucher extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'voucher_no',
        'landlord_id',
        'unit_id',
        'other_tenant_id',
        'month',
        'amount',
        'date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'month'  => 'date',
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($voucher) {
            if (empty($voucher->voucher_no)) {
                $voucher->voucher_no = 'TEMP-ORP-' . \Illuminate\Support\Str::random(12);
            }
        });

        static::created(function ($voucher) {
            if (strpos($voucher->voucher_no, 'TEMP-ORP-') === 0) {
                $voucher->voucher_no = 'PM-ORP-' . str_pad($voucher->id, 5, '0', STR_PAD_LEFT);
                $voucher->saveQuietly();
            }
        });
    }

    public static function getNextVoucherNo(): string
    {
        $maxId = static::withTrashed()->max('id') ?? 0;
        return 'PM-ORP-' . str_pad($maxId + 1, 5, '0', STR_PAD_LEFT);
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class)->withTrashed();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    public function otherTenant(): BelongsTo
    {
        return $this->belongsTo(OtherTenant::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
