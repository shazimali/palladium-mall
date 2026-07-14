<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActivity;

class OwnerReceivable extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'voucher_no',
        'owner_id',
        'amount',
        'date',
        'payment_account_id',
        'reference',
        'notes',
        'receipt',
        'user_id',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($voucher) {
            if (empty($voucher->voucher_no)) {
                $voucher->voucher_no = 'TEMP-ORV-' . \Illuminate\Support\Str::random(12);
            }
        });

        static::created(function ($voucher) {
            if (strpos($voucher->voucher_no, 'TEMP-ORV-') === 0) {
                $voucher->voucher_no = 'PM-ORV-' . str_pad($voucher->id, 5, '0', STR_PAD_LEFT);
                $voucher->saveQuietly();
            }
        });
    }

    /**
     * Get the owner associated with this receivable.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class)->withTrashed();
    }

    /**
     * Get the payment account associated with this receivable.
     */
    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    /**
     * Get the user who recorded this receivable.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the URL for the receipt attachment.
     */
    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt ? Storage::disk('public')->url($this->receipt) : null;
    }
}
