<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get all units on this floor.
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
}
