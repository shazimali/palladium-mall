<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OwnerController extends Controller
{
    /**
     * Display a listing of the owners.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        $owners = Owner::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $totalShare = Owner::sum('partnership_percentage');

        return view('owners.index', [
            'title'      => 'Managing Owners / Partners',
            'owners'     => $owners,
            'totalShare' => $totalShare,
        ]);
    }

    /**
     * Show the form for creating a new owner.
     */
    public function create(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('owners.create', [
            'title' => 'Add Managing Owner',
        ]);
    }

    /**
     * Store a newly created owner in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.create')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'phone'                  => ['nullable', 'string', 'max:50'],
            'email'                  => ['nullable', 'email', 'max:255'],
            'partnership_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
        ]);

        // Optional duplicate checks or total percentage checks
        $currentTotal = Owner::sum('partnership_percentage');
        if ($currentTotal + $data['partnership_percentage'] > 100.01) {
            return back()->withInput()->withErrors([
                'partnership_percentage' => 'Total partnership percentage would exceed 100% (Current total is ' . $currentTotal . '%).'
            ]);
        }

        Owner::create($data);

        return redirect()->route('owners.index')
            ->with('success', 'Managing owner added successfully.');
    }

    /**
     * Show the form for editing the specified owner.
     */
    public function edit(Owner $owner): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.edit')) {
            abort(403, 'Unauthorized action.');
        }

        return view('owners.edit', [
            'title' => 'Edit Owner — ' . $owner->name,
            'owner' => $owner,
        ]);
    }

    /**
     * Update the specified owner in storage.
     */
    public function update(Request $request, Owner $owner): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'phone'                  => ['nullable', 'string', 'max:50'],
            'email'                  => ['nullable', 'email', 'max:255'],
            'partnership_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
        ]);

        $currentTotal = Owner::where('id', '!=', $owner->id)->sum('partnership_percentage');
        if ($currentTotal + $data['partnership_percentage'] > 100.01) {
            return back()->withInput()->withErrors([
                'partnership_percentage' => 'Total partnership percentage would exceed 100% (Other owners total ' . $currentTotal . '%).'
            ]);
        }

        $owner->update($data);

        return redirect()->route('owners.index')
            ->with('success', 'Owner details updated successfully.');
    }

    /**
     * Remove the specified owner from storage.
     */
    public function destroy(Owner $owner): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if ($owner->vouchers()->exists()) {
            return redirect()->route('owners.index')
                ->with('error', 'Cannot delete owner because there are receiving vouchers associated with this owner.');
        }

        $owner->delete();

        return redirect()->route('owners.index')
            ->with('success', 'Owner deleted successfully.');
    }
}
