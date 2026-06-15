<?php

namespace App\Http\Controllers;

use App\Models\InspectionPerson;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InspectionPersonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $inspectionPersons = InspectionPerson::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('designation', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('inspection_persons.index', [
            'title'             => 'Inspection Persons',
            'inspectionPersons' => $inspectionPersons,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('inspection_persons.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('inspection_persons.create', [
            'title' => 'Add New Inspection Person',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('inspection_persons.create')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'email'       => ['nullable', 'email', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        InspectionPerson::create($data);

        return redirect()->route('inspection-persons.index')
            ->with('success', 'Inspection person created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InspectionPerson $inspectionPerson): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('inspection_persons.edit')) {
            abort(403, 'Unauthorized action.');
        }

        return view('inspection_persons.edit', [
            'title'            => 'Edit Inspection Person — ' . $inspectionPerson->name,
            'inspectionPerson' => $inspectionPerson,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InspectionPerson $inspectionPerson): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('inspection_persons.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:50'],
            'email'       => ['nullable', 'email', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $inspectionPerson->update($data);

        return redirect()->route('inspection-persons.index')
            ->with('success', 'Inspection person updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InspectionPerson $inspectionPerson): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('inspection_persons.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $inspectionPerson->delete();

        return redirect()->route('inspection-persons.index')
            ->with('success', 'Inspection person deleted successfully.');
    }
}
