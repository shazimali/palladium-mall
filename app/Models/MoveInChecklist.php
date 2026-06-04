<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoveInChecklist extends Model
{
    protected $fillable = [
        'tenant_id', 'agreement_id',
        'inspection_member', 'checklist_date', 'type',
        // 1. General Cleanliness
        'rooms_cleaned', 'kitchen_cleaned', 'bathrooms_cleaned', 'no_garbage',
        // 2. Walls, Paint & Fixtures
        'no_wall_damage', 'paint_condition_ok', 'light_fixtures_ok', 'electric_wiring_ok', 'no_breaker_issues',
        // 3. Furniture & Appliances
        'furniture_ok', 'ac_working', 'kitchen_appliances_ok', 'stove_clean', 'keys_returned',
        // 4. Doors & Windows
        'doors_locks_ok', 'windows_ok', 'balcony_doors_ok',
        // 5. Utilities & Dues
        'water_supply_ok', 'electricity_supply_ok', 'gas_supply_ok',
        'no_pending_utility_bills', 'no_pending_maintenance', 'no_pending_rent',
        // 6. Damage
        'damage_notes',
        // 7. Inventory
        'fixtures_available', 'no_missing_items', 'inventory_notes',
        // 8. Final
        'access_cards_returned', 'no_pending_requests', 'move_out_form_signed',
        'flat_condition', 'deposit_deduction', 'final_remarks',
    ];

    protected $casts = [
        'checklist_date'          => 'date',
        'rooms_cleaned'           => 'boolean',
        'kitchen_cleaned'         => 'boolean',
        'bathrooms_cleaned'       => 'boolean',
        'no_garbage'              => 'boolean',
        'no_wall_damage'          => 'boolean',
        'paint_condition_ok'      => 'boolean',
        'light_fixtures_ok'       => 'boolean',
        'electric_wiring_ok'      => 'boolean',
        'no_breaker_issues'       => 'boolean',
        'furniture_ok'            => 'boolean',
        'ac_working'              => 'boolean',
        'kitchen_appliances_ok'   => 'boolean',
        'stove_clean'             => 'boolean',
        'keys_returned'           => 'boolean',
        'doors_locks_ok'          => 'boolean',
        'windows_ok'              => 'boolean',
        'balcony_doors_ok'        => 'boolean',
        'water_supply_ok'         => 'boolean',
        'electricity_supply_ok'   => 'boolean',
        'gas_supply_ok'           => 'boolean',
        'no_pending_utility_bills'=> 'boolean',
        'no_pending_maintenance'  => 'boolean',
        'no_pending_rent'         => 'boolean',
        'fixtures_available'      => 'boolean',
        'no_missing_items'        => 'boolean',
        'access_cards_returned'   => 'boolean',
        'no_pending_requests'     => 'boolean',
        'move_out_form_signed'    => 'boolean',
        'deposit_deduction'       => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    public function countChecked(): int
    {
        $booleans = array_keys(array_filter($this->casts, fn($t) => $t === 'boolean'));
        return collect($booleans)->filter(fn($col) => (bool) $this->{$col})->count();
    }

    public function countTotal(): int
    {
        return count(array_filter($this->casts, fn($t) => $t === 'boolean'));
    }
}
