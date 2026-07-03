<?php

namespace App\Http\Controllers;

use App\Models\LandlordPayable;
use App\Models\Landlord;
use Illuminate\Http\Request;

class LandlordPayableController extends Controller
{
    public function index(Request $request)
    {
        $query = LandlordPayable::query()->with(['landlord.ownerships', 'landlord.payables', 'unit']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('notes', 'like', "%{$term}%")
                  ->orWhereHas('landlord', function($l) use ($term) {
                      $l->where('name', 'like', "%{$term}%");
                  })
                  ->orWhereHas('unit', function($u) use ($term) {
                      $u->where('unit_number', 'like', "%{$term}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('landlord_id')) {
            $query->where('landlord_id', $request->landlord_id);
        }

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $payables = $query->latest()->paginate(15)->withQueryString();
        $landlords = Landlord::with(['ownerships', 'payables'])->orderBy('name')->get();

        return view('landlord_payables.index', compact('payables', 'landlords'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'landlord_id' => 'required|exists:landlords,id',
            'unit_id' => 'nullable|exists:units,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        LandlordPayable::create($validated);

        return back()->with('success', 'Landlord Payable created successfully.');
    }

    public function update(Request $request, LandlordPayable $landlordPayable)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $landlordPayable->update($validated);

        return back()->with('success', 'Landlord Payable updated successfully.');
    }

    public function destroy(LandlordPayable $landlordPayable)
    {
        $landlordPayable->delete();
        return back()->with('success', 'Landlord Payable deleted successfully.');
    }
}
