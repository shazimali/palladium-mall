<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActivity;

class Expense extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'voucher_no',
        'expense_head_id',
        'amount',
        'date',
        'payment_method',
        'payment_account_id',
        'reference',
        'notes',
        'receipt',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($expense) {
            if (empty($expense->voucher_no)) {
                $expense->voucher_no = 'TEMP-EV-' . \Illuminate\Support\Str::random(12);
            }
        });

        static::created(function ($expense) {
            if (strpos($expense->voucher_no, 'TEMP-EV-') === 0) {
                $expense->voucher_no = 'PM-EV-' . str_pad($expense->id, 5, '0', STR_PAD_LEFT);
                $expense->saveQuietly();
            }
        });
    }

    /**
     * Get the expense head/category associated with this expense.
     */
    public function expenseHead(): BelongsTo
    {
        return $this->belongsTo(ExpenseHead::class);
    }

    /**
     * Get the payment account associated with this expense.
     */
    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    /**
     * Get the user who recorded this expense.
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
