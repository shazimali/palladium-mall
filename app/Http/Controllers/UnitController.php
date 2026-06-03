<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Floor;
use App\Models\Block;
use App\Models\Area;
use App\Models\Meter;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $units = Unit::query()
            ->with(['floor', 'block', 'area'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->floor_id, fn($q) => $q->where('floor_id', $request->floor_id))
            ->when($request->block_id, fn($q) => $q->where('block_id', $request->block_id))
            ->when($request->area_id, fn($q) => $q->where('area_id', $request->area_id))
            ->orderBy('unit_number')
            ->paginate(20)
            ->withQueryString();

        $floors = Floor::orderBy('name')->get();
        $blocks = Block::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        return view('units.index', [
            'title' => 'Flat / Shop Master',
            'units' => $units,
            'floors' => $floors,
            'blocks' => $blocks,
            'areas' => $areas,
        ]);
    }

    public function create(): View
    {
        return view('units.create', [
            'title' => 'Add New Unit',
            'floors' => Floor::orderBy('name')->get(),
            'blocks' => Block::orderBy('name')->get(),
            'areas' => Area::orderBy('name')->get(),
        ]);
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'Flat/Shop created successfully.');
    }

    public function show(Unit $unit): View
    {
        $unit->load(['floor', 'block', 'area', 'meters']);

        return view('units.show', [
            'title' => 'Unit — ' . $unit->unit_number,
            'unit' => $unit,
            'meters' => $unit->meters->keyBy('type'),
        ]);
    }

    public function edit(Unit $unit): View
    {
        $unit->load('meters');

        return view('units.edit', [
            'title' => 'Edit Unit — ' . $unit->unit_number,
            'unit' => $unit,
            'floors' => Floor::orderBy('name')->get(),
            'blocks' => Block::orderBy('name')->get(),
            'areas' => Area::orderBy('name')->get(),
            'existingMeters' => $unit->meters->keyBy('type'),
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'Flat/Shop updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        // Soft delete — record is preserved for audit
        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'Flat/Shop removed successfully.');
    }
}