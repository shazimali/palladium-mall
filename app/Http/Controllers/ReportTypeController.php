<?php

namespace App\Http\Controllers;

use App\Models\ReportType;
use App\Models\ReportTypeRemark;
use App\Models\ReportTypeMember;
use App\Models\InspectionHead;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $types = ReportType::with(['remarks', 'members'])->withCount(['inspectionHeads', 'members'])->ordered()->get();
            return response()->json([
                'success' => true,
                'data'    => $types,
            ]);
        }

        $reportTypes = ReportType::with(['remarks', 'members'])->withCount(['inspectionHeads', 'members'])->ordered()->paginate(20);
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
            'name'                        => 'required|string|max:255',
            'key'                         => 'nullable|string|max:100|unique:report_types,key',
            'description'                 => 'nullable|string|max:500',
            'is_daily'                    => 'nullable|boolean',
            'daily_start_time'            => 'nullable|string',
            'daily_end_time'              => 'nullable|string',
            'one_per_user_daily'          => 'nullable|boolean',
            'satisfactory_threshold_pct'  => 'nullable|numeric|min:0|max:100',
            'below_threshold_score_pct'   => 'nullable|numeric|min:0|max:100',
            'satisfactory_score_pct'      => 'nullable|numeric|min:0|max:100',
            'unsatisfactory_score_pct'    => 'nullable|numeric|min:0|max:100',
            'sort_order'                  => 'nullable|integer|min:0',
            'is_active'                   => 'nullable|boolean',
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

        $validated['key']                         = $key;
        $validated['is_daily']                    = $request->boolean('is_daily', false);
        $validated['daily_start_time']            = $validated['daily_start_time'] ? substr($validated['daily_start_time'], 0, 5) . ':00' : '09:00:00';
        $validated['daily_end_time']              = $validated['daily_end_time'] ? substr($validated['daily_end_time'], 0, 5) . ':00' : '20:00:00';
        $validated['one_per_user_daily']          = $request->boolean('one_per_user_daily', true);
        $validated['satisfactory_threshold_pct']  = isset($validated['satisfactory_threshold_pct']) && $validated['satisfactory_threshold_pct'] !== '' ? (float) $validated['satisfactory_threshold_pct'] : 50.00;
        $validated['below_threshold_score_pct']   = isset($validated['below_threshold_score_pct']) && $validated['below_threshold_score_pct'] !== '' ? (float) $validated['below_threshold_score_pct'] : 50.00;
        $validated['satisfactory_score_pct']      = isset($validated['satisfactory_score_pct']) && $validated['satisfactory_score_pct'] !== '' ? (float) $validated['satisfactory_score_pct'] : 100.00;
        $validated['unsatisfactory_score_pct']    = isset($validated['unsatisfactory_score_pct']) && $validated['unsatisfactory_score_pct'] !== '' ? (float) $validated['unsatisfactory_score_pct'] : 0.00;
        $validated['is_active']                   = $request->boolean('is_active', true);
        $validated['sort_order']                  = $validated['sort_order'] ?? 0;

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
        $reportType->load(['remarks', 'members'])->loadCount(['inspectionHeads', 'members']);
        return view('report_types.show', compact('reportType'));
    }

    public function edit(ReportType $reportType)
    {
        $reportType->load(['remarks', 'members'])->loadCount(['inspectionHeads', 'members']);
        return view('report_types.edit', compact('reportType'));
    }

    public function update(Request $request, ReportType $reportType)
    {
        $validated = $request->validate([
            'name'                        => 'required|string|max:255',
            'key'                         => 'nullable|string|max:100|unique:report_types,key,' . $reportType->id,
            'description'                 => 'nullable|string|max:500',
            'is_daily'                    => 'nullable|boolean',
            'daily_start_time'            => 'nullable|string',
            'daily_end_time'              => 'nullable|string',
            'one_per_user_daily'          => 'nullable|boolean',
            'satisfactory_threshold_pct'  => 'nullable|numeric|min:0|max:100',
            'below_threshold_score_pct'   => 'nullable|numeric|min:0|max:100',
            'satisfactory_score_pct'      => 'nullable|numeric|min:0|max:100',
            'unsatisfactory_score_pct'    => 'nullable|numeric|min:0|max:100',
            'sort_order'                  => 'nullable|integer|min:0',
            'is_active'                   => 'nullable|boolean',
        ]);

        $oldKey = $reportType->key;
        $newKey = $validated['key'] ?? Str::slug($validated['name'], '_');
        if (empty($newKey)) {
            $newKey = $oldKey;
        }

        $validated['key']                         = $newKey;
        $validated['is_daily']                    = $request->boolean('is_daily', false);
        $validated['daily_start_time']            = $validated['daily_start_time'] ? substr($validated['daily_start_time'], 0, 5) . ':00' : '09:00:00';
        $validated['daily_end_time']              = $validated['daily_end_time'] ? substr($validated['daily_end_time'], 0, 5) . ':00' : '20:00:00';
        $validated['one_per_user_daily']          = $request->boolean('one_per_user_daily', true);
        $validated['satisfactory_threshold_pct']  = isset($validated['satisfactory_threshold_pct']) && $validated['satisfactory_threshold_pct'] !== '' ? (float) $validated['satisfactory_threshold_pct'] : 50.00;
        $validated['below_threshold_score_pct']   = isset($validated['below_threshold_score_pct']) && $validated['below_threshold_score_pct'] !== '' ? (float) $validated['below_threshold_score_pct'] : 50.00;
        $validated['satisfactory_score_pct']      = isset($validated['satisfactory_score_pct']) && $validated['satisfactory_score_pct'] !== '' ? (float) $validated['satisfactory_score_pct'] : 100.00;
        $validated['unsatisfactory_score_pct']    = isset($validated['unsatisfactory_score_pct']) && $validated['unsatisfactory_score_pct'] !== '' ? (float) $validated['unsatisfactory_score_pct'] : 0.00;
        $validated['is_active']                   = $request->boolean('is_active');
        $validated['sort_order']                  = $validated['sort_order'] ?? $reportType->sort_order;

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

    // ── Dedicated Members Management Screen ───────────────────────────────────

    public function members(ReportType $reportType)
    {
        $reportType->load('members');
        return view('report_types.members', compact('reportType'));
    }

    public function addMember(Request $request, ReportType $reportType)
    {
        $validated = $request->validate([
            'member_name' => 'required|string|max:255',
            'status'      => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $member = $reportType->members()->create([
            'member_name' => $validated['member_name'],
            'status'      => $request->boolean('status', true),
            'sort_order'  => $validated['sort_order'] ?? ($reportType->members()->count() + 1),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Member added successfully.',
                'data'    => $member,
            ]);
        }

        return redirect()->back()->with('success', 'Member added successfully.');
    }

    public function updateMember(Request $request, ReportType $reportType, ReportTypeMember $member)
    {
        $validated = $request->validate([
            'member_name' => 'required|string|max:255',
            'status'      => 'required|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $member->update([
            'member_name' => $validated['member_name'],
            'status'      => (bool)$validated['status'],
            'sort_order'  => $validated['sort_order'] ?? $member->sort_order,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Member updated successfully.',
                'data'    => $member,
            ]);
        }

        return redirect()->back()->with('success', 'Member updated successfully.');
    }

    public function toggleMemberStatus(Request $request, ReportType $reportType, ReportTypeMember $member)
    {
        $member->update(['status' => !$member->status]);

        return response()->json([
            'success' => true,
            'status'  => $member->status,
            'message' => 'Member status updated successfully.',
        ]);
    }
}
