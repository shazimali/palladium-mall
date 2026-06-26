<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class StockEntry extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'entry_no',
        'date',
        'type',
        'payment_account_id',
        'expense_id',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($entry) {
            if (empty($entry->entry_no)) {
                $entry->entry_no = 'TEMP-SI-' . \Illuminate\Support\Str::random(12);
            }
        });

        static::created(function ($entry) {
            if (strpos($entry->entry_no, 'TEMP-SI-') === 0) {
                $entry->entry_no = 'PM-SI-' . str_pad($entry->id, 5, '0', STR_PAD_LEFT);
                $entry->saveQuietly();
            }
        });

        static::deleting(function ($entry) {
            // Delete associated expense if it exists
            if ($entry->expense_id) {
                $expense = Expense::find($entry->expense_id);
                if ($expense) {
                    $expense->delete();
                }
            }

            // Loop and delete stock entry items so their deleting/deleted events run to rollback stock quantities
            $entry->items()->get()->each->delete();
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockEntryItem::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
