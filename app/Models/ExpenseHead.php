<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class ExpenseHead extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the expenses associated with this expense head.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
