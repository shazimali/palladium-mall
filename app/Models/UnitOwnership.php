<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitOwnership extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unit_id',
        'landlord_id',
        'is_current',
        'start_date',
        'end_date',
        // Nominee
        'nominee_name',
        'nominee_relation_type',
        'nominee_relation_name',
        // Financial
        'total_amount',
        'received_amount',
        'credit_amount',
        'received_from',
        // Office
        'approved_by',
        'received_by',
        'approved_date',
        'notes',
    ];

    protected $casts = [
        'is_current'      => 'boolean',
        'start_date'      => 'date',
        'end_date'        => 'date',
        'approved_date'   => 'date',
        'total_amount'    => 'decimal:2',
        'received_amount' => 'decimal:2',
        'credit_amount'   => 'decimal:2',
    ];

    // ──────────────────────────────────────────────────────────────
    // Auto-compute credit_amount before every save
    // ──────────────────────────────────────────────────────────────
    protected static function booted(): void
    {
        static::saving(function (UnitOwnership $ownership) {
            if ($ownership->total_amount !== null && $ownership->received_amount !== null) {
                $ownership->credit_amount = $ownership->total_amount - $ownership->received_amount;
            }
        });
    }

    // ──────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class)->withTrashed();
    }

    // ──────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * S/O, D/O, W/O label for display in views and documents.
     */
    public function getRelationLabelAttribute(): string
    {
        return match ($this->nominee_relation_type) {
            'son_of'      => 'S/O',
            'daughter_of' => 'D/O',
            'wife_of'     => 'W/O',
            default       => '',
        };
    }

    /**
     * Full nominee line, e.g. "Ali Khan S/O Muhammad Khan".
     */
    public function getNomineeFullLineAttribute(): string
    {
        if (! $this->nominee_name) {
            return '—';
        }

        $parts = [$this->nominee_name];

        if ($this->nominee_relation_type && $this->nominee_relation_name) {
            $parts[] = $this->relation_label;
            $parts[] = $this->nominee_relation_name;
        }

        return implode(' ', $parts);
    }

    /**
     * Ownership period as a human-readable string.
     */
    public function getPeriodAttribute(): string
    {
        $start = $this->start_date ? $this->start_date->format('d M Y') : '—';

        if ($this->is_current) {
            return $start . ' → Present';
        }

        $end = $this->end_date ? $this->end_date->format('d M Y') : '—';
        return $start . ' → ' . $end;
    }
}
