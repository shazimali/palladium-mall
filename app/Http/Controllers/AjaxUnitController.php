<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Block;
use App\Models\Floor;
use App\Models\Landlord;
use App\Models\Unit;
use App\Models\UnitOwnership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AjaxUnitController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // Shared validation rules for ownership fields
    // ──────────────────────────────────────────────────────────────
    private function ownershipRules(): array
    {
        return [
            'nominee_name'          => ['nullable', 'string', 'max:255'],
            'nominee_relation_type' => ['nullable', 'in:son_of,daughter_of,wife_of'],
            'nominee_relation_name' => ['nullable', 'string', 'max:255'],
            'total_amount'          => ['nullable', 'numeric', 'min:0'],
            'received_amount'       => ['nullable', 'numeric', 'min:0'],
            'received_from'         => ['nullable', 'string', 'max:255'],
            'approved_by'           => ['nullable', 'string', 'max:255'],
            'received_by'           => ['nullable', 'string', 'max:255'],
            'approved_date'         => ['nullable', 'date'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // GET ajax/landlord-units/{landlord}
    // Returns all current units for a landlord with ownership data.
    // ──────────────────────────────────────────────────────────────
    public function byLandlord(Landlord $landlord): JsonResponse
    {
        $landlord->load([
            'units' => function ($q) {
                $q->with(['floor', 'block', 'area', 'currentOwnership']);
            },
        ]);

        return response()->json([
            'success' => true,
            'units'   => $landlord->units->map(fn($u) => $this->formatUnit($u)),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // POST ajax/landlord-units
    // Create a new unit + initial ownership record.
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        if (! auth()->user()->isSuperAdmin() && ! auth()->user()->hasPermission('units.create')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $data = $request->validate(array_merge([
            'landlord_id' => ['required', 'exists:landlords,id'],
            'unit_number' => ['required', 'string', 'max:20', Rule::unique('units', 'unit_number')],
            'type'        => ['required', 'in:flat,shop,office'],
            'floor_id'    => ['required', 'exists:floors,id'],
            'block_id'    => ['required', 'exists:blocks,id'],
            'area_id'     => ['nullable', 'exists:areas,id'],
            'area_sqft'   => ['nullable', 'numeric', 'min:0'],
            'file_no'     => ['nullable', 'string', 'max:100', Rule::unique('units', 'file_no')],
            'date'        => ['nullable', 'date'],
            'is_self'                 => ['boolean'],
            'self_maintenance_charge' => ['nullable', 'numeric', 'min:0'],
        ], $this->ownershipRules()));

        // Create the unit
        $unit = Unit::create([
            'unit_number'             => $data['unit_number'],
            'type'                    => $data['type'],
            'floor_id'                => $data['floor_id'] ?? null,
            'block_id'                => $data['block_id'] ?? null,
            'area_id'                 => $data['area_id'] ?? null,
            'area_sqft'               => $data['area_sqft'] ?? null,
            'file_no'                 => $data['file_no'] ?? null,
            'date'                    => $data['date'] ?? now()->toDateString(),
            'status'                  => 'vacant',
            'landlord_id'             => $data['landlord_id'],
            'is_self'                 => $data['is_self'] ?? false,
            'self_maintenance_charge' => ($data['is_self'] ?? false) ? ($data['self_maintenance_charge'] ?? 2500) : null,
        ]);

        // Create the initial ownership record
        UnitOwnership::create([
            'unit_id'               => $unit->id,
            'landlord_id'           => $data['landlord_id'],
            'is_current'            => true,
            'start_date'            => $data['date'] ?? now()->toDateString(),
            'nominee_name'          => $data['nominee_name'] ?? null,
            'nominee_relation_type' => $data['nominee_relation_type'] ?? null,
            'nominee_relation_name' => $data['nominee_relation_name'] ?? null,
            'total_amount'          => $data['total_amount'] ?? null,
            'received_amount'       => $data['received_amount'] ?? null,
            'received_from'         => $data['received_from'] ?? null,
            'approved_by'           => $data['approved_by'] ?? null,
            'received_by'           => $data['received_by'] ?? null,
            'approved_date'         => $data['approved_date'] ?? null,
            'notes'                 => $data['notes'] ?? null,
        ]);

        $unit->load(['floor', 'block', 'area', 'currentOwnership']);

        return response()->json([
            'success' => true,
            'unit'    => $this->formatUnit($unit),
            'message' => "Unit {$unit->unit_number} created successfully.",
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // PUT ajax/landlord-units/{unit}
    // Update unit structural fields + current ownership record.
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, Unit $unit): JsonResponse
    {
        if (! auth()->user()->isSuperAdmin() && ! auth()->user()->hasPermission('units.edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $ownershipId = $unit->currentOwnership?->id;

        $data = $request->validate(array_merge([
            'unit_number' => ['required', 'string', 'max:20', Rule::unique('units', 'unit_number')->ignore($unit->id)],
            'type'        => ['required', 'in:flat,shop,office'],
            'floor_id'    => ['required', 'exists:floors,id'],
            'block_id'    => ['required', 'exists:blocks,id'],
            'area_id'     => ['nullable', 'exists:areas,id'],
            'area_sqft'   => ['nullable', 'numeric', 'min:0'],
            'file_no'     => ['nullable', 'string', 'max:100', Rule::unique('units', 'file_no')->ignore($unit->id)],
            'date'        => ['nullable', 'date'],
            'is_self'                 => ['boolean'],
            'self_maintenance_charge' => ['nullable', 'numeric', 'min:0'],
        ], $this->ownershipRules()));

        // Update structural unit fields
        $unit->update([
            'unit_number'             => $data['unit_number'],
            'type'                    => $data['type'],
            'floor_id'                => $data['floor_id'] ?? null,
            'block_id'                => $data['block_id'] ?? null,
            'area_id'                 => $data['area_id'] ?? null,
            'area_sqft'               => $data['area_sqft'] ?? null,
            'file_no'                 => $data['file_no'] ?? null,
            'date'                    => $data['date'] ?? $unit->date,
            'is_self'                 => $data['is_self'] ?? false,
            'self_maintenance_charge' => ($data['is_self'] ?? false) ? ($data['self_maintenance_charge'] ?? 2500) : null,
        ]);

        // Update or create current ownership record
        UnitOwnership::updateOrCreate(
            ['unit_id' => $unit->id, 'is_current' => true],
            [
                'landlord_id'           => $unit->landlord_id,
                'nominee_name'          => $data['nominee_name'] ?? null,
                'nominee_relation_type' => $data['nominee_relation_type'] ?? null,
                'nominee_relation_name' => $data['nominee_relation_name'] ?? null,
                'total_amount'          => $data['total_amount'] ?? null,
                'received_amount'       => $data['received_amount'] ?? null,
                'received_from'         => $data['received_from'] ?? null,
                'approved_by'           => $data['approved_by'] ?? null,
                'received_by'           => $data['received_by'] ?? null,
                'approved_date'         => $data['approved_date'] ?? null,
                'notes'                 => $data['notes'] ?? null,
            ]
        );

        $unit->load(['floor', 'block', 'area', 'currentOwnership']);

        return response()->json([
            'success' => true,
            'unit'    => $this->formatUnit($unit),
            'message' => "Unit {$unit->unit_number} updated successfully.",
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // DELETE ajax/landlord-units/{unit}
    // Soft-delete the unit (ownerships cascade via model events).
    // ──────────────────────────────────────────────────────────────
    public function destroy(Unit $unit): JsonResponse
    {
        if (! auth()->user()->isSuperAdmin() && ! auth()->user()->hasPermission('units.delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $unitNumber = $unit->unit_number;
        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => "Unit {$unitNumber} removed.",
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // POST ajax/landlord-units/{unit}/transfer
    // Transfer unit ownership to a new landlord.
    // ──────────────────────────────────────────────────────────────
    public function transfer(Request $request, Unit $unit): JsonResponse
    {
        if (! auth()->user()->isSuperAdmin() && ! auth()->user()->hasPermission('units.edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $data = $request->validate([
            'new_landlord_id' => ['required', 'exists:landlords,id', 'different:' . $unit->landlord_id],
            'transfer_date'   => ['required', 'date'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        // Close the current ownership record
        UnitOwnership::where('unit_id', $unit->id)
            ->where('is_current', true)
            ->update([
                'is_current' => false,
                'end_date'   => $data['transfer_date'],
            ]);

        // Open new ownership record for new landlord
        UnitOwnership::create([
            'unit_id'     => $unit->id,
            'landlord_id' => $data['new_landlord_id'],
            'is_current'  => true,
            'start_date'  => $data['transfer_date'],
            'notes'       => $data['notes'] ?? null,
        ]);

        // Update the denormalized landlord_id on units table
        $unit->update(['landlord_id' => $data['new_landlord_id']]);

        $newLandlord = Landlord::find($data['new_landlord_id']);

        return response()->json([
            'success' => true,
            'message' => "Unit {$unit->unit_number} transferred to {$newLandlord->name}.",
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // POST ajax/landlord-units/check-unique
    // Check if unit number or file no is unique in database.
    // ──────────────────────────────────────────────────────────────
    public function checkUnique(Request $request): JsonResponse
    {
        $data = $request->validate([
            'unit_number' => ['nullable', 'string', 'max:20'],
            'file_no'     => ['nullable', 'string', 'max:100'],
            'ignore_id'   => ['nullable', 'integer'],
        ]);

        $unitNumber = $data['unit_number'] ?? null;
        $fileNo = $data['file_no'] ?? null;
        $ignoreId = $data['ignore_id'] ?? null;

        $errors = [];

        if ($unitNumber) {
            $exists = Unit::where('unit_number', $unitNumber)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists();
            if ($exists) {
                $errors['unit_number'] = "Flat/Shop No. {$unitNumber} is already registered.";
            }
        }

        if ($fileNo) {
            $exists = Unit::where('file_no', $fileNo)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists();
            if ($exists) {
                $errors['file_no'] = "File No. {$fileNo} is already registered.";
            }
        }

        return response()->json([
            'success' => true,
            'is_unique' => empty($errors),
            'errors' => $errors,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Helper — format a unit for JSON response
    // ──────────────────────────────────────────────────────────────
    private function formatUnit(Unit $unit): array
    {
        $o = $unit->currentOwnership;

        return [
            'id'                      => $unit->id,
            'unit_number'             => $unit->unit_number,
            'type'                    => $unit->type,
            'status'                  => $unit->status,
            'is_self'                 => (bool) $unit->is_self,
            'self_maintenance_charge' => $unit->self_maintenance_charge,
            'area_sqft'               => $unit->area_sqft,
            'date'                    => $unit->date?->toDateString(),
            'floor_id'                => $unit->floor_id,
            'block_id'                => $unit->block_id,
            'area_id'                 => $unit->area_id,
            'floor_name'              => $unit->floor?->name ?? '—',
            'block_name'              => $unit->block?->name ?? '—',
            'area_name'               => $unit->area?->name  ?? '—',
            // Ownership fields
            'ownership_id'            => $o?->id,
            'nominee_name'            => $o?->nominee_name,
            'nominee_relation_type'   => $o?->nominee_relation_type,
            'nominee_relation_name'   => $o?->nominee_relation_name,
            'relation_label'          => $o?->relation_label ?? '',
            'total_amount'            => $o?->total_amount,
            'received_amount'         => $o?->received_amount,
            'credit_amount'           => $o?->credit_amount,
            'received_from'           => $o?->received_from,
            'file_no'                 => $unit->file_no,
            'approved_by'             => $o?->approved_by,
            'received_by'             => $o?->received_by,
            'approved_date'           => $o?->approved_date?->toDateString(),
            'notes'                   => $o?->notes,
            'start_date'              => $o?->start_date?->toDateString(),
        ];
    }
}
