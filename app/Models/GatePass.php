<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class GatePass extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'gatepass_no',
        'date',
        'issued_to',
        'purpose',
        'unit_id',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($gatepass) {
            if (empty($gatepass->gatepass_no)) {
                $gatepass->gatepass_no = 'TEMP-GP-' . \Illuminate\Support\Str::random(12);
            }
        });

        static::created(function ($gatepass) {
            if (strpos($gatepass->gatepass_no, 'TEMP-GP-') === 0) {
                $gatepass->gatepass_no = 'PM-GP-' . str_pad($gatepass->id, 5, '0', STR_PAD_LEFT);
                $gatepass->saveQuietly();
            }
        });

        static::updating(function ($gatepass) {
            if ($gatepass->isDirty('status')) {
                $oldStatus = $gatepass->getOriginal('status');
                $newStatus = $gatepass->status;

                if ($oldStatus === 'Issued' && $newStatus === 'Cancelled') {
                    // Add back stock
                    foreach ($gatepass->items as $item) {
                        if ($item->inventoryItem) {
                            $item->inventoryItem->increment('current_quantity', $item->quantity);
                        }
                    }
                } elseif ($oldStatus === 'Cancelled' && $newStatus === 'Issued') {
                    // Deduct stock
                    foreach ($gatepass->items as $item) {
                        if ($item->inventoryItem) {
                            $item->inventoryItem->decrement('current_quantity', $item->quantity);
                        }
                    }
                }
            }
        });

        static::deleting(function ($gatepass) {
            // Restore stock if the gatepass is currently Issued
            if ($gatepass->status === 'Issued') {
                foreach ($gatepass->items as $item) {
                    if ($item->inventoryItem) {
                        $item->inventoryItem->increment('current_quantity', $item->quantity);
                    }
                }
            }

            // Delete child items
            $gatepass->items()->delete();
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(GatePassItem::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
