<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatePassItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'gate_pass_id',
        'inventory_item_id',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function ($line) {
            if ($line->gatePass && $line->gatePass->status === 'Issued' && $line->inventoryItem) {
                $line->inventoryItem->decrement('current_quantity', $line->quantity);
            }
        });

        static::deleted(function ($line) {
            if ($line->gatePass && $line->gatePass->status === 'Issued' && $line->inventoryItem) {
                $line->inventoryItem->increment('current_quantity', $line->quantity);
            }
        });
    }

    public function gatePass(): BelongsTo
    {
        return $this->belongsTo(GatePass::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
