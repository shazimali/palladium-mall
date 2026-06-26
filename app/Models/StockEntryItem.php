<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockEntryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_entry_id',
        'inventory_item_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function ($line) {
            if ($line->inventoryItem) {
                $line->inventoryItem->increment('current_quantity', $line->quantity);
            }
        });

        static::deleted(function ($line) {
            if ($line->inventoryItem) {
                $line->inventoryItem->decrement('current_quantity', $line->quantity);
            }
        });
    }

    public function stockEntry(): BelongsTo
    {
        return $this->belongsTo(StockEntry::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
