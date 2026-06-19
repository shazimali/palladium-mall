<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtherTenantUnitHistory extends Model
{
    use HasFactory;

    protected $table = 'other_tenant_unit_history';

    protected $fillable = [
        'other_tenant_id',
        'unit_id',
        'attached_at',
        'detached_at',
    ];

    protected $casts = [
        'attached_at' => 'date',
        'detached_at' => 'date',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function otherTenant(): BelongsTo
    {
        return $this->belongsTo(OtherTenant::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    public function isCurrent(): bool
    {
        return is_null($this->detached_at);
    }
}
