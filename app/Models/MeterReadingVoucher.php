<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Traits\LogsActivity;

class MeterReadingVoucher extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'voucher_no',
        'unit_id',
        'date',
        'due_date',
        'meter_ref_no',
        'current_reading',
        'amount',
        'meter_image',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'date'            => 'date',
        'due_date'        => 'date',
        'amount'          => 'decimal:2',
        'current_reading' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($voucher) {
            if (empty($voucher->voucher_no)) {
                $voucher->voucher_no = 'TEMP-MRV-' . \Illuminate\Support\Str::random(12);
            }
        });

        static::created(function ($voucher) {
            if (strpos($voucher->voucher_no, 'TEMP-MRV-') === 0) {
                $voucher->voucher_no = 'PM-MRV-' . str_pad($voucher->id, 5, '0', STR_PAD_LEFT);
                $voucher->saveQuietly();
            }
        });
    }

    public static function getNextVoucherNo(): string
    {
        $maxId = static::withTrashed()->max('id') ?? 0;
        return 'PM-MRV-' . str_pad($maxId + 1, 5, '0', STR_PAD_LEFT);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMeterImageUrlAttribute(): ?string
    {
        return $this->meter_image ? Storage::disk('public')->url($this->meter_image) : null;
    }
}
