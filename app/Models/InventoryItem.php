<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'unit_of_measure',
        'current_quantity',
        'min_stock_level',
    ];

    protected $casts = [
        'current_quantity' => 'decimal:2',
        'min_stock_level'  => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($item) {
            if (empty($item->code)) {
                $item->code = 'TEMP-ITEM-' . \Illuminate\Support\Str::random(12);
            }
        });

        static::created(function ($item) {
            if (strpos($item->code, 'TEMP-ITEM-') === 0) {
                $item->code = 'ITEM-' . str_pad($item->id, 5, '0', STR_PAD_LEFT);
                $item->saveQuietly();
            }
        });
    }
}
