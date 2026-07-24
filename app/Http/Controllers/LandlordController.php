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
    public function index(Request $request)
    {
        $query = Landlord::query()
            ->with(['units'])
            ->withCount('units')
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('cnic', 'like', "%{$search}%")
                        ->orWhereHas('units', function ($uq) use ($search) {
                            $uq->where('unit_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->has_properties === 'with_units', function ($q) {
                $q->has('units');
            })
            ->when($request->has_properties === 'without_units', function ($q) {
                $q->doesntHave('units');
            })
            ->when($request->floor_id, function ($q) use ($request) {
                $q->whereHas('units', fn($uq) => $uq->where('floor_id', $request->floor_id));
            })
            ->when($request->block_id, function ($q) use ($request) {
                $q->whereHas('units', fn($uq) => $uq->where('block_id', $request->block_id));
            });

        // Counts calculation
        $counts = [
            'total' => (clone $query)->count(),
            'with_units' => (clone $query)->has('units')->count(),
            'without_units' => (clone $query)->doesntHave('units')->count(),
        ];

        $landlords = $query
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $search = $request->input('search');
        $highlight = function($text) use ($search) {
            if (empty($text)) return '';
            if (empty($search)) {
                return e($text);
            }
            $escapedSearch = preg_quote($search, '/');
            return preg_replace('/(' . $escapedSearch . ')/i', '<mark class="bg-amber-100 text-amber-900 rounded px-0.5 dark:bg-amber-950/70 dark:text-amber-300 font-medium">$1</mark>', e($text));
        };

        if ($request->ajax() || $request->has('ajax')) {
            return view('landlords._table', [
                'landlords' => $landlords,
                'highlight' => $highlight,
            ])->render();
        }

        $floors = Floor::orderBy('name')->get();
        $blocks = Block::orderBy('name')->get();

        return view('landlords.index', [
            'title'     => 'Landlords',
            'landlords' => $landlords,
            'counts'    => $counts,
            'floors'    => $floors,
            'blocks'    => $blocks,
            'highlight' => $highlight,
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
