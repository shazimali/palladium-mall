<?php

namespace App\Http\Controllers;

use App\Models\InspectionPerson;
use App\Models\Unit;
use App\Models\UnitBreakerInspection;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UnitBreakerInspectionController extends Controller
{
    public function store(Request $request, Unit $unit): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('units.edit')) {
            abort(403, 'Unauthorized action.');
        }

        // Sanitize meter_reading comma input
        if ($request->has('meter_reading') && $request->input('meter_reading') !== null) {
            $request->merge(['meter_reading' => str_replace(',', '', $request->input('meter_reading'))]);
        }

        $validated = $request->validate([
            'breaker_status'        => ['required', 'in:on,off'],
            'meter_reading'         => ['required', 'numeric', 'min:0'],
            'inspection_person_id'  => ['required', 'exists:inspection_persons,id'],
            'officer_statement'     => ['required', 'string', 'max:1000'],
            'meter_image'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'signed_inspection_doc' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'inspected_at'          => ['nullable', 'date'],
        ], [
            'meter_reading.required'        => 'Meter reading value is required.',
            'inspection_person_id.required' => 'Please select an inspection officer from the list.',
            'officer_statement.required'    => 'Officer statement/verification notes are required.',
        ]);

        $inspector = InspectionPerson::findOrFail($validated['inspection_person_id']);

        $meterImagePath = null;
        if ($request->hasFile('meter_image')) {
            $meterImagePath = $request->file('meter_image')->store('breaker_inspections', 'public');
        }

        $signedDocPath = null;
        if ($request->hasFile('signed_inspection_doc')) {
            $signedDocPath = $request->file('signed_inspection_doc')->store('breaker_inspections/signed_docs', 'public');
        }

        $inspectedAt = !empty($validated['inspected_at']) ? Carbon::parse($validated['inspected_at']) : Carbon::now();

        // Create inspection record
        UnitBreakerInspection::create([
            'unit_id'                 => $unit->id,
            'inspection_person_id'    => $inspector->id,
            'breaker_status'          => $validated['breaker_status'],
            'meter_reading'           => $validated['meter_reading'],
            'meter_image'             => $meterImagePath,
            'signed_inspection_doc'   => $signedDocPath,
            'inspection_officer_name' => $inspector->name,
            'officer_statement'       => $validated['officer_statement'],
            'inspected_at'            => $inspectedAt,
        ]);

        // Update unit breaker status
        $unit->update([
            'breaker_status' => $validated['breaker_status'],
        ]);

        $statusUpper = strtoupper($validated['breaker_status']);
        return redirect()->back()
            ->with('success', "Breaker status updated to {$statusUpper} by Inspector {$inspector->name} with reading ({$validated['meter_reading']} kWh) successfully.");
    }
}
