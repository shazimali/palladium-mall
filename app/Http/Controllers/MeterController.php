<?php

namespace App\Http\Controllers;

use App\Models\Meter;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MeterController extends Controller
{
    /**
     * Store or update a meter for a unit (upsert by unit_id + type).
     * Called via AJAX from the Unit create/edit form.
     * Accepts multipart/form-data to support meter_image upload.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'unit_id'           => ['required', 'exists:units,id'],
            'type'              => ['required', 'in:electricity,water,gas'],
            'meter_ref_no'      => ['required', 'string', 'max:100'],
            'meter_consumer_id' => ['nullable', 'string', 'max:100'],
            'is_active'         => ['required', 'boolean'],
            'meter_image'       => ['nullable', 'image', 'max:2048'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('meter_image')) {
            $imagePath = $request->file('meter_image')->store('meters', 'public');
        }

        $updateData = array_filter([
            'meter_ref_no'      => $data['meter_ref_no'],
            'meter_consumer_id' => $data['meter_consumer_id'] ?? null,
            'is_active'         => $data['is_active'],
            'notes'             => $data['notes'] ?? null,
            'deleted_at'        => null,
        ], fn($v) => $v !== null || array_key_exists('meter_consumer_id', $data));

        if ($imagePath) {
            $updateData['meter_image'] = $imagePath;
        }

        $meter = Meter::withTrashed()->updateOrCreate(
            ['unit_id' => $data['unit_id'], 'type' => $data['type']],
            array_merge([
                'meter_ref_no'      => $data['meter_ref_no'],
                'meter_consumer_id' => $data['meter_consumer_id'] ?? null,
                'is_active'         => $data['is_active'],
                'notes'             => $data['notes'] ?? null,
                'deleted_at'        => null,
            ], $imagePath ? ['meter_image' => $imagePath] : [])
        );

        return response()->json([
            'success'     => true,
            'meter'       => $meter->fresh(),
            'image_url'   => $meter->meter_image ? Storage::url($meter->meter_image) : null,
            'message'     => ucfirst($data['type']) . ' meter saved.',
        ]);
    }

    /**
     * Update an existing meter.
     */
    public function update(Request $request, Meter $meter): JsonResponse
    {
        $data = $request->validate([
            'meter_ref_no'      => ['required', 'string', 'max:100'],
            'meter_consumer_id' => ['nullable', 'string', 'max:100'],
            'is_active'         => ['required', 'boolean'],
            'meter_image'       => ['nullable', 'image', 'max:2048'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ]);

        $updateData = [
            'meter_ref_no'      => $data['meter_ref_no'],
            'meter_consumer_id' => $data['meter_consumer_id'] ?? null,
            'is_active'         => $data['is_active'],
            'notes'             => $data['notes'] ?? null,
        ];

        if ($request->hasFile('meter_image')) {
            // Delete old image
            if ($meter->meter_image) {
                Storage::disk('public')->delete($meter->meter_image);
            }
            $updateData['meter_image'] = $request->file('meter_image')->store('meters', 'public');
        }

        $meter->update($updateData);
        $fresh = $meter->fresh();

        return response()->json([
            'success'   => true,
            'meter'     => $fresh,
            'image_url' => $fresh->meter_image ? Storage::url($fresh->meter_image) : null,
            'message'   => ucfirst($meter->type) . ' meter updated.',
        ]);
    }

    /**
     * Soft-delete a meter.
     */
    public function destroy(Meter $meter): JsonResponse
    {
        // Delete image file too
        if ($meter->meter_image) {
            Storage::disk('public')->delete($meter->meter_image);
        }

        $meter->delete();

        return response()->json([
            'success' => true,
            'message' => ucfirst($meter->type) . ' meter removed.',
        ]);
    }

    /**
     * Return all meters for a given unit (used when editing).
     */
    public function byUnit(Unit $unit): JsonResponse
    {
        $meters = $unit->meters()->get()->keyBy('type');

        return response()->json([
            'success' => true,
            'meters'  => $meters,
        ]);
    }
}
