<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    /**
     * Get the units owned by this landlord.
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
}
