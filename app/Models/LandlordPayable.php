<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LandlordPayable extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'landlord_id',
        'unit_id',
        'title',
        'amount',
        'amount_paid',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class)->withTrashed();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function receivingVouchers(): BelongsToMany
    {
        return $this->belongsToMany(ReceivingVoucher::class, 'receiving_voucher_landlord_payables')
            ->withPivot('amount_allocated')
            ->withTimestamps();
    }
}
