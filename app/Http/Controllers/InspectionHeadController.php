<?php

namespace App\Http\Controllers;

use App\Models\InspectionHead;
use App\Models\ReportType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InspectionHeadController extends Controller
{
    public function index(Request $request)
    {
        $query = InspectionHead::query()->orderBy('sort_order')->orderBy('name');

        if ($request->filled('type')) {
            $query->forType($request->type);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $heads = $query->paginate(20)->withQueryString();
        $reportTypes = ReportType::ordered()->get();

        return view('inspection_heads.index', compact('heads', 'reportTypes'));
    }

    public function create()
    {
        $reportTypes = ReportType::active()->ordered()->get();
        return view('inspection_heads.create', compact('reportTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'key'        => 'nullable|string|max:255|unique:inspection_heads,key',
            'types'      => 'required|array|min:1',
            'types.*'    => 'required|string|max:100',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'types.required' => 'Please select at least one report type.',
            'types.min'      => 'Please select at least one report type.',
        ]);

        $validated['type']       = $validated['types'][0] ?? 'flat_inspection';
        $validated['key']        = $validated['key'] ?? Str::slug($validated['name'], '_');
        $validated['is_active']  = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        InspectionHead::create($validated);

        return redirect()->route('inspection-heads.index')
            ->with('success', 'Inspection head created successfully.');
    }

    public function edit(InspectionHead $inspectionHead)
    {
        $reportTypes = ReportType::ordered()->get();
        return view('inspection_heads.edit', ['head' => $inspectionHead, 'reportTypes' => $reportTypes]);
    }

    public function update(Request $request, InspectionHead $inspectionHead)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'key'        => 'nullable|string|max:255|unique:inspection_heads,key,' . $inspectionHead->id,
            'types'      => 'required|array|min:1',
            'types.*'    => 'required|string|max:100',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'types.required' => 'Please select at least one report type.',
            'types.min'      => 'Please select at least one report type.',
        ]);

        $validated['type']       = $validated['types'][0] ?? $inspectionHead->type;
        $validated['key']        = $validated['key'] ?? Str::slug($validated['name'], '_');
        $validated['is_active']  = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? $inspectionHead->sort_order;

        $inspectionHead->update($validated);

        return redirect()->route('inspection-heads.index')
            ->with('success', 'Inspection head updated successfully.');
    }

    public function destroy(InspectionHead $inspectionHead)
    {
        $inspectionHead->delete();

        return redirect()->route('inspection-heads.index')
            ->with('success', 'Inspection head deleted.');
    }

    public function toggleStatus(InspectionHead $inspectionHead)
    {
        $inspectionHead->update(['is_active' => !$inspectionHead->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $inspectionHead->is_active,
        ]);
    }
}
