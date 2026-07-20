<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class Withdrawal extends Model
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
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($withdrawal) {
            if (empty($withdrawal->voucher_no)) {
                $withdrawal->voucher_no = 'TEMP-WD-' . \Illuminate\Support\Str::random(12);
            }
        });

        static::created(function ($withdrawal) {
            if (strpos($withdrawal->voucher_no, 'TEMP-WD-') === 0) {
                $withdrawal->voucher_no = 'PM-WD-' . str_pad($withdrawal->id, 5, '0', STR_PAD_LEFT);
                $withdrawal->saveQuietly();
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class)->withTrashed();
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
