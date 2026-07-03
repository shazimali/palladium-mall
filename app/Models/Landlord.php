<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Landlord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'cnic',
        'address',
        'notes',
        'photo',
    ];

    /**
     * Passport-size photo URL (or null).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }

    /**
     * Get the units owned by this landlord (current, via units table).
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * All ownership records (current + historical) for this landlord.
     */
    public function ownerships(): HasMany
    {
        return $this->hasMany(UnitOwnership::class)->orderBy('start_date', 'desc');
    }

    /**
     * Only active (current) ownership records.
     */
    public function currentOwnerships(): HasMany
    {
        return $this->hasMany(UnitOwnership::class)->where('is_current', true);
    }

    /**
     * Payables/Installments due from this landlord.
     */
    public function payables(): HasMany
    {
        return $this->hasMany(LandlordPayable::class)->orderBy('due_date', 'asc');
    }

    /**
     * Get the remaining opening balance (Opening Balance - Payables).
     */
    public function getRemainingOpeningBalanceAttribute(): float
    {
        return (float) ($this->ownerships->sum('credit_amount') - $this->payables->sum('amount'));
    }
}
