<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'type',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'rent'        => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'maintenance' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
            'electricity' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
            'water'       => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
            'gas'         => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
            'fine'        => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
            default       => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
        ];
    }
}