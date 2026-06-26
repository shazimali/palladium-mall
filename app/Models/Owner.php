<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class Owner extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'partnership_percentage',
        'notes',
    ];

    protected $casts = [
        'partnership_percentage' => 'decimal:2',
    ];

    /**
     * Get the receiving vouchers recorded from/for this owner.
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(ReceivingVoucher::class);
    }
}
