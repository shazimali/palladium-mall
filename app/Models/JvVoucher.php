<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActivity;

class JvVoucher extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'voucher_no',
        'expense_head_id',
        'amount',
        'date',
        'status',
        'payment_account_id',
        'payment_method',
        'paid_date',
        'reference',
        'notes',
        'receipt',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'paid_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($voucher) {
            if (empty($voucher->voucher_no)) {
                $voucher->voucher_no = 'TEMP-JV-' . \Illuminate\Support\Str::random(12);
            }
        });

        static::created(function ($voucher) {
            if (strpos($voucher->voucher_no, 'TEMP-JV-') === 0) {
                $voucher->voucher_no = 'PM-JV-' . str_pad($voucher->id, 5, '0', STR_PAD_LEFT);
                $voucher->saveQuietly();
            }
        });
    }

    public static function getNextVoucherNo(): string
    {
        $maxId = static::withTrashed()->max('id') ?? 0;
        return 'PM-JV-' . str_pad($maxId + 1, 5, '0', STR_PAD_LEFT);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function expenseHead(): BelongsTo
    {
        return $this->belongsTo(ExpenseHead::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt ? Storage::disk('public')->url($this->receipt) : null;
    }
}
