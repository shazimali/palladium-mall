<?php

namespace App\Http\Controllers;

use App\Models\UtilityReading;
use App\Models\Unit;
use App\Models\Tenant;
use App\Http\Requests\StoreUtilityReadingRequest;
use App\Http\Requests\UpdateUtilityReadingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UtilityReadingController extends Controller
{
    public function index(Request $request): View
    {
        $readings = UtilityReading::with(['unit', 'tenant'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->type, fn($q) => $q->ofType($request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->month, fn($q) => $q->forMonth($request->month))
            ->latest('month')
            ->paginate(20)
            ->withQueryString();

        return view('utilities.index', [
            'title' => 'Utilities',
            'readings' => $readings,
        ]);
    }

    public function create(Request $request): View
    {
        $units = Unit::where('status', 'occupied')
            ->orderBy('unit_number')
            ->get();

        return view('utilities.create', [
            'title' => 'Add Utility Reading',
            'units' => $units,
        ]);
    }

    public function store(StoreUtilityReadingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Calculate units consumed
        $data['units_consumed'] = $data['current_reading'] - $data['previous_reading'];

        // Store month as first day of the selected month
        $data['month'] = \Carbon\Carbon::parse($data['month'])->startOfMonth()->toDateString();

        UtilityReading::create($data);

        return redirect()
            ->route('utilities.index')
            ->with('success', 'Utility reading saved successfully.');
    }

    public function show(UtilityReading $utility): View
    {
        $utility->load(['unit', 'tenant']);

        return view('utilities.show', [
            'title' => 'Utility Reading — ' . $utility->unit->unit_number,
            'reading' => $utility,
        ]);
    }

    public function edit(UtilityReading $utility): View
    {
        $utility->load(['unit', 'tenant']);

        $units = Unit::where('status', 'occupied')
            ->orWhere('id', $utility->unit_id)
            ->orderBy('unit_number')
            ->get();

        return view('utilities.edit', [
            'title' => 'Edit Utility Reading',
            'reading' => $utility,
            'units' => $units,
        ]);
    }

    public function update(UpdateUtilityReadingRequest $request, UtilityReading $utility): RedirectResponse
    {
        $data = $request->validated();

        // Recalculate units consumed
        $data['units_consumed'] = $data['current_reading'] - $data['previous_reading'];
        $data['month'] = \Carbon\Carbon::parse($data['month'])->startOfMonth()->toDateString();

        $utility->update($data);

        return redirect()
            ->route('utilities.show', $utility)
            ->with('success', 'Utility reading updated successfully.');
    }

    public function destroy(UtilityReading $utility): RedirectResponse
    {
        if ($utility->bill_proof) {
            Storage::disk('local')->delete($utility->bill_proof);
        }

        $utility->delete();

        return redirect()
            ->route('utilities.index')
            ->with('success', 'Utility reading removed successfully.');
    }

    // -----------------------------------------------------------------------
    // Mark as paid
    // -----------------------------------------------------------------------

    public function markPaid(Request $request, UtilityReading $utility): RedirectResponse
    {
        $request->validate([
            'bill_proof' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ]);

        // Delete old proof if exists
        if ($utility->bill_proof) {
            Storage::disk('local')->delete($utility->bill_proof);
        }

        $proofPath = null;
        if ($request->hasFile('bill_proof')) {
            $proofPath = $request->file('bill_proof')
                ->store('utilities/proofs', 'local');
        }

        $utility->update([
            'status' => 'paid',
            'paid_at' => now(),
            'bill_proof' => $proofPath ?? $utility->bill_proof,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Bill marked as paid.');
    }

    // -----------------------------------------------------------------------
    // AJAX — get tenant for selected unit
    // -----------------------------------------------------------------------

    public function getTenantByUnit(Request $request): JsonResponse
    {
        $unit = Unit::with(['tenant'])->find($request->unit_id);

        if (!$unit || !$unit->tenant) {
            return response()->json(['tenant' => null]);
        }

        return response()->json([
            'tenant' => [
                'id' => $unit->tenant->id,
                'name' => $unit->tenant->name,
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // AJAX — get previous reading for unit + type
    // -----------------------------------------------------------------------

    public function getPreviousReading(Request $request): JsonResponse
    {
        $previousReading = UtilityReading::lastReading(
            $request->unit_id,
            $request->type
        );

        return response()->json(['previous_reading' => $previousReading]);
    }
}