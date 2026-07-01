<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Party extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'whatsapp_number',
    ];

    public function dues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PartyDue::class);
    }

    public function receivingVouchers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GeneralReceivingVoucher::class);
    }

    public function paymentVouchers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentVoucher::class);
    }
}
