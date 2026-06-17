<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Block;
use App\Models\Floor;
use App\Models\Landlord;
use App\Http\Requests\StoreLandlordRequest;
use App\Http\Requests\UpdateLandlordRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LandlordController extends Controller
{
    public function index(Request $request): View
    {
        $landlords = Landlord::query()
            ->with(['units'])
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
            'title'     => 'Landlords',
            'landlords' => $landlords,
        ]);
    }

    public function create(): View
    {
        return view('landlords.create', [
            'title'   => 'Add New Landlord',
            'floors'  => Floor::orderBy('name')->get(),
            'blocks'  => Block::orderBy('name')->get(),
            'areas'   => Area::orderBy('name')->get(),
            'allLandlords' => Landlord::orderBy('name')->get(), // for transfer modal
        ]);
    }

    public function store(StoreLandlordRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('landlords/photos', 'public');
        } else {
            unset($data['photo']);
        }

        Landlord::create($data);

        return redirect()->route('landlords.index')
            ->with('success', 'Landlord created successfully.');
    }

    public function show(Landlord $landlord): View
    {
        $landlord->load([
            'units.floor',
            'units.block',
            'units.area',
            'units.currentOwnership',
            'ownerships.unit',
            'ownerships.landlord',
        ]);

        return view('landlords.show', [
            'title'    => 'Landlord — ' . $landlord->name,
            'landlord' => $landlord,
        ]);
    }

    public function edit(Landlord $landlord): View
    {
        $landlord->load([
            'units.floor',
            'units.block',
            'units.area',
            'units.currentOwnership',
        ]);

        return view('landlords.edit', [
            'title'        => 'Edit Landlord — ' . $landlord->name,
            'landlord'     => $landlord,
            'floors'       => Floor::orderBy('name')->get(),
            'blocks'       => Block::orderBy('name')->get(),
            'areas'        => Area::orderBy('name')->get(),
            'allLandlords' => Landlord::where('id', '!=', $landlord->id)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateLandlordRequest $request, Landlord $landlord): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($landlord->photo) {
                Storage::disk('public')->delete($landlord->photo);
            }
            $data['photo'] = $request->file('photo')->store('landlords/photos', 'public');
        } else {
            unset($data['photo']); // keep existing photo
        }

        $landlord->update($data);

        return redirect()->route('landlords.index')
            ->with('success', 'Landlord updated successfully.');
    }

    public function destroy(Landlord $landlord): RedirectResponse
    {
        if ($landlord->photo) {
            Storage::disk('public')->delete($landlord->photo);
        }

        $landlord->delete();

        return redirect()->route('landlords.index')
            ->with('success', 'Landlord deleted successfully.');
    }
}
