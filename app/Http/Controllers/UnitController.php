<?php

namespace App\Http\Controllers;

use App\Models\Unit;
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
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->block, fn($q) => $q->where('block', $request->block))
            ->orderBy('unit_number')
            ->paginate(20)
            ->withQueryString();

        $blocks = Unit::select('block')
            ->distinct()
            ->whereNotNull('block')
            ->pluck('block');

        return view('units.index', [
            'title' => 'Flat / Shop Master',
            'units' => $units,
            'blocks' => $blocks,
        ]);
    }

    public function create(): View
    {
        return view('units.create', ['title' => 'Add New Unit']);
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit created successfully.');
    }

    public function show(Unit $unit): View
    {
        return view('units.show', [
            'title' => 'Unit — ' . $unit->unit_number,
            'unit' => $unit,
        ]);
    }

    public function edit(Unit $unit): View
    {
        return view('units.edit', [
            'title' => 'Edit Unit — ' . $unit->unit_number,
            'unit' => $unit,
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        // Soft delete — record is preserved for audit
        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit removed successfully.');
    }
}