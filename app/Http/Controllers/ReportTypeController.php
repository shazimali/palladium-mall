<?php

namespace App\Http\Controllers;

use App\Models\ReportType;
use App\Models\ReportTypeRemark;
use App\Models\InspectionHead;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $types = ReportType::with('remarks')->withCount('inspectionHeads')->ordered()->get();
            return response()->json([
                'success' => true,
                'data'    => $types,
            ]);
        }

        $reportTypes = ReportType::with('remarks')->withCount('inspectionHeads')->ordered()->paginate(20);
        return view('report_types.index', compact('reportTypes'));
    }

    public function create()
    {
        $reportType = new ReportType([
            'is_daily'           => false,
            'daily_start_time'   => '09:00:00',
            'daily_end_time'     => '20:00:00',
            'one_per_user_daily' => true,
            'is_active'          => true,
            'sort_order'         => ReportType::count() + 1,
        ]);

        return view('report_types.create', compact('reportType'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'key'                => 'nullable|string|max:100|unique:report_types,key',
            'description'        => 'nullable|string|max:500',
            'is_daily'           => 'nullable|boolean',
            'daily_start_time'   => 'nullable|string',
            'daily_end_time'     => 'nullable|string',
            'one_per_user_daily' => 'nullable|boolean',
            'sort_order'         => 'nullable|integer|min:0',
            'is_active'          => 'nullable|boolean',
        ]);

        $key = $validated['key'] ?? Str::slug($validated['name'], '_');
        if (empty($key)) {
            $key = 'type_' . time();
        }

        // Ensure key uniqueness if auto-generated
        $originalKey = $key;
        $counter = 1;
        while (ReportType::where('key', $key)->exists()) {
            $key = $originalKey . '_' . $counter++;
        }

        $validated['key']                = $key;
        $validated['is_daily']           = $request->boolean('is_daily', false);
        $validated['daily_start_time']   = $validated['daily_start_time'] ? substr($validated['daily_start_time'], 0, 5) . ':00' : '09:00:00';
        $validated['daily_end_time']     = $validated['daily_end_time'] ? substr($validated['daily_end_time'], 0, 5) . ':00' : '20:00:00';
        $validated['one_per_user_daily'] = $request->boolean('one_per_user_daily', true);
        $validated['is_active']          = $request->boolean('is_active', true);
        $validated['sort_order']         = $validated['sort_order'] ?? 0;

        $reportType = ReportType::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Report type created successfully.',
                'data'    => $reportType,
            ]);
        }

        return redirect()->route('report-types.index')->with('success', 'Report type created successfully.');
    }

    public function show(ReportType $reportType)
    {
        $reportType->load('remarks')->loadCount('inspectionHeads');
        return view('report_types.show', compact('reportType'));
    }

    public function edit(ReportType $reportType)
    {
        $reportType->load('remarks')->loadCount('inspectionHeads');
        return view('report_types.edit', compact('reportType'));
    }

    public function update(Request $request, ReportType $reportType)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'key'                => 'nullable|string|max:100|unique:report_types,key,' . $reportType->id,
            'description'        => 'nullable|string|max:500',
            'is_daily'           => 'nullable|boolean',
            'daily_start_time'   => 'nullable|string',
            'daily_end_time'     => 'nullable|string',
            'one_per_user_daily' => 'nullable|boolean',
            'sort_order'         => 'nullable|integer|min:0',
            'is_active'          => 'nullable|boolean',
        ]);

        $oldKey = $reportType->key;
        $newKey = $validated['key'] ?? Str::slug($validated['name'], '_');
        if (empty($newKey)) {
            $newKey = $oldKey;
        }

        $validated['key']                = $newKey;
        $validated['is_daily']           = $request->boolean('is_daily', false);
        $validated['daily_start_time']   = $validated['daily_start_time'] ? substr($validated['daily_start_time'], 0, 5) . ':00' : '09:00:00';
        $validated['daily_end_time']     = $validated['daily_end_time'] ? substr($validated['daily_end_time'], 0, 5) . ':00' : '20:00:00';
        $validated['one_per_user_daily'] = $request->boolean('one_per_user_daily', true);
        $validated['is_active']          = $request->boolean('is_active');
        $validated['sort_order']         = $validated['sort_order'] ?? $reportType->sort_order;

        if ($oldKey !== $newKey) {
            $heads = InspectionHead::forType($oldKey)->get();
            foreach ($heads as $h) {
                $typesList = $h->types_list;
                $updatedTypes = array_map(fn($k) => $k === $oldKey ? $newKey : $k, $typesList);
                $h->update([
                    'type'  => $h->type === $oldKey ? $newKey : $h->type,
                    'types' => array_values(array_unique($updatedTypes)),
                ]);
            }
        }

        $reportType->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Report type updated successfully.',
                'data'    => $reportType,
            ]);
        }

        return redirect()->route('report-types.index')->with('success', 'Report type updated successfully.');
    }

    public function destroy(Request $request, ReportType $reportType)
    {
        $headsCount = InspectionHead::forType($reportType->key)->count();
        if ($headsCount > 0) {
            $msg = "Cannot delete '{$reportType->name}' because it has {$headsCount} linked inspection head(s).";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $reportType->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Report type deleted successfully.',
            ]);
        }

        return redirect()->route('report-types.index')->with('success', 'Report type deleted successfully.');
    }

    public function toggleStatus(Request $request, ReportType $reportType)
    {
        $reportType->update(['is_active' => !$reportType->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $reportType->is_active,
            'message'   => 'Status updated successfully.',
        ]);
    }

    // ── Dedicated Remarks Management Screen ───────────────────────────────────

    public function remarks(ReportType $reportType)
    {
        $reportType->load('remarks');
        return view('report_types.remarks', compact('reportType'));
    }

    public function addRemark(Request $request, ReportType $reportType)
    {
        $validated = $request->validate([
            'remark'     => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $remark = $reportType->remarks()->create([
            'remark'     => $validated['remark'],
            'sort_order' => $validated['sort_order'] ?? ($reportType->remarks()->count() + 1),
            'is_active'  => true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'System remark added.',
                'data'    => $remark,
            ]);
        }

        return redirect()->back()->with('success', 'System remark added.');
    }

    public function deleteRemark(Request $request, ReportType $reportType, ReportTypeRemark $remark)
    {
        $remark->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'System remark deleted.',
            ]);
        }

        return redirect()->back()->with('success', 'System remark deleted.');
    }
}
