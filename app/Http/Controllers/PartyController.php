<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Http\Requests\StorePartyRequest;
use App\Http\Requests\UpdatePartyRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PartyController extends Controller
{
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('parties.view')) {
            abort(403, 'Unauthorized action.');
        }

        $parties = Party::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%")
                    ->orWhere('whatsapp_number', 'like', "%{$request->search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('parties.index', [
            'title'   => 'Party Heads',
            'parties' => $parties,
        ]);
    }

    public function create(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('parties.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('parties.create', [
            'title' => 'Add New Party Head',
        ]);
    }

    public function store(StorePartyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        Party::create($data);

        return redirect()->route('parties.index')
            ->with('success', 'Party created successfully.');
    }

    public function edit(Party $party): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('parties.edit')) {
            abort(403, 'Unauthorized action.');
        }

        return view('parties.edit', [
            'title' => 'Edit Party Head — ' . $party->name,
            'party' => $party,
        ]);
    }

    public function update(UpdatePartyRequest $request, Party $party): RedirectResponse
    {
        $data = $request->validated();
        $party->update($data);

        return redirect()->route('parties.index')
            ->with('success', 'Party updated successfully.');
    }

    public function destroy(Party $party): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('parties.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $party->delete();

        return redirect()->route('parties.index')
            ->with('success', 'Party deleted successfully.');
    }
}
