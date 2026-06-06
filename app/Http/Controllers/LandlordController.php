<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Http\Requests\StoreLandlordRequest;
use App\Http\Requests\UpdateLandlordRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandlordController extends Controller
{
    public function index(Request $request): View
    {
        $landlords = Landlord::query()
            ->withCount('units')
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%")
                    ->orWhere('cnic', 'like', "%{$request->search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('landlords.index', [
            'title' => 'Landlords',
            'landlords' => $landlords,
        ]);
    }

    public function create(): View
    {
        return view('landlords.create', [
            'title' => 'Add New Landlord',
        ]);
    }

    public function store(StoreLandlordRequest $request): RedirectResponse
    {
        Landlord::create($request->validated());

        return redirect()->route('landlords.index')
            ->with('success', 'Landlord created successfully.');
    }

    public function show(Landlord $landlord): View
    {
        $landlord->load('units.floor', 'units.block', 'units.area');

        return view('landlords.show', [
            'title' => 'Landlord — ' . $landlord->name,
            'landlord' => $landlord,
        ]);
    }

    public function edit(Landlord $landlord): View
    {
        return view('landlords.edit', [
            'title' => 'Edit Landlord — ' . $landlord->name,
            'landlord' => $landlord,
        ]);
    }

    public function update(UpdateLandlordRequest $request, Landlord $landlord): RedirectResponse
    {
        $landlord->update($request->validated());

        return redirect()->route('landlords.index')
            ->with('success', 'Landlord updated successfully.');
    }

    public function destroy(Landlord $landlord): RedirectResponse
    {
        $landlord->delete();

        return redirect()->route('landlords.index')
            ->with('success', 'Landlord deleted successfully.');
    }
}
