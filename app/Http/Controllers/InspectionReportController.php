<?php

namespace App\Http\Controllers;

use App\Models\InspectionReport;
use App\Models\InspectionReportItem;
use App\Models\InspectionHead;
use App\Models\ReportType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InspectionReportController extends Controller
{
    private function resolveReportType(string $typeKey): ReportType
    {
        return ReportType::where('key', $typeKey)->firstOrFail();
    }

    private function authorizeInspection(string $action, string $type): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }
        if ($user->isSuperAdmin()) {
            return;
        }

        $permPrefix = ($type === 'flat_inspection') ? 'flat_inspections' : 'inspection_reports';
        $permission = "{$permPrefix}.{$action}";

        if (!$user->can($permission)) {
            abort(403, "You do not have permission to {$action} {$type} inspection reports ({$permission}).");
        }
    }

    public function index(Request $request, string $type)
    {
        $this->authorizeInspection('view', $type);
        $reportType = $this->resolveReportType($type);

        $query = InspectionReport::with(['reporter', 'items'])
            ->where('report_type_id', $reportType->id)
            ->orderByDesc('report_date')
            ->orderByDesc('created_at');

        if ($request->filled('date_from')) {
            $query->where('report_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('report_date', '<=', $request->date_to);
        }
        if ($request->filled('reported_by')) {
            $query->where('reported_by', $request->reported_by);
        }

        $reports = $query->paginate(20)->withQueryString();
        $isWithinWindow = $reportType->isWithinAllowedTimeWindow();

        return view('inspection_reports.index', compact('reportType', 'reports', 'isWithinWindow'));
    }

    public function create(string $type)
    {
        $this->authorizeInspection('create', $type);
        $reportType = $this->resolveReportType($type);
        $today = now()->toDateString();
        $isWithinWindow = $reportType->isWithinAllowedTimeWindow();

        if ($reportType->is_daily && !$isWithinWindow) {
            return redirect()->route('inspection-reports.index', $type)
                ->with('error', "{$reportType->name} reports can only be created between {$reportType->time_window_display}.");
        }

        // If daily report mode, check if current user already submitted today's report
        if ($reportType->is_daily && $reportType->one_per_user_daily) {
            $existing = InspectionReport::where('report_type_id', $reportType->id)
                ->where('report_date', $today)
                ->where('reported_by', Auth::id())
                ->first();

            if ($existing) {
                return redirect()->route('inspection-reports.edit', ['type' => $type, 'report' => $existing->id])
                    ->with('info', "You have already created today's {$reportType->name} report. You can view or edit it below.");
            }
        }

        $heads = InspectionHead::active()->forType($type)->orderBy('sort_order')->orderBy('name')->get();
        $systemRemarks = $reportType->activeRemarks;

        return view('inspection_reports.create', compact('reportType', 'heads', 'systemRemarks', 'today', 'isWithinWindow') + ['report' => null]);
    }

    public function store(Request $request, string $type)
    {
        $this->authorizeInspection('create', $type);
        $reportType = $this->resolveReportType($type);

        // Daily Time Window check
        if ($reportType->is_daily && !$reportType->isWithinAllowedTimeWindow()) {
            return redirect()->route('inspection-reports.index', $type)
                ->with('error', "{$reportType->name} reports can only be generated between {$reportType->time_window_display}.");
        }

        // Daily 1-Report-per-User check
        if ($reportType->is_daily && $reportType->one_per_user_daily) {
            $exists = InspectionReport::where('report_type_id', $reportType->id)
                ->where('report_date', now()->toDateString())
                ->where('reported_by', Auth::id())
                ->exists();

            if ($exists) {
                return redirect()->route('inspection-reports.index', $type)
                    ->with('error', "You have already submitted a {$reportType->name} report for today.");
            }
        }

        $hasSystemRemarks = $reportType->activeRemarks()->exists();

        $validationRules = [
            'overall_remarks'              => 'required|string|max:2000',
            'items'                        => 'required|array|min:1',
            'items.*.status'               => 'required|in:yes,no,na',
            'items.*.report_type_remark_id'=> $hasSystemRemarks ? 'required|exists:report_type_remarks,id' : 'nullable',
            'items.*.remarks'              => 'required|string|max:1000',
            'items.*.image'                => 'nullable|image|max:200', // 200 KB
        ];

        if (!$reportType->is_daily) {
            $validationRules['report_date'] = 'required|date';
        }

        $customMessages = [
            'overall_remarks.required'               => 'Overall remarks are mandatory.',
            'items.*.status.required'                => 'Status is mandatory for every checklist item.',
            'items.*.report_type_remark_id.required' => 'System remark selection is mandatory for every checklist item.',
            'items.*.remarks.required'               => 'Additional remarks are mandatory for every checklist item.',
            'items.*.image.max'                      => 'Each photo must not exceed 200 KB.',
        ];

        $request->validate($validationRules, $customMessages);

        $reportDate = $reportType->is_daily ? now()->toDateString() : $request->report_date;

        $report = InspectionReport::create([
            'report_type_id'  => $reportType->id,
            'report_date'     => $reportDate,
            'reported_by'     => Auth::id(),
            'overall_remarks' => $request->overall_remarks,
            'status'          => 'completed',
        ]);

        $this->saveItems($report, $request, $type);

        return redirect()->route('inspection-reports.index', $type)
            ->with('success', "{$reportType->name} report saved successfully.");
    }

    public function show(string $type, InspectionReport $report)
    {
        $this->authorizeInspection('view', $type);
        $reportType = $this->resolveReportType($type);
        $report->load(['items.head', 'items.systemRemark', 'reporter', 'reportType']);

        return view('inspection_reports.show', compact('reportType', 'report'));
    }

    public function edit(string $type, InspectionReport $report)
    {
        $this->authorizeInspection('edit', $type);
        $reportType = $this->resolveReportType($type);
        $isWithinWindow = $reportType->isWithinAllowedTimeWindow();

        if ($reportType->is_daily && !$isWithinWindow) {
            return redirect()->route('inspection-reports.show', ['type' => $type, 'report' => $report->id])
                ->with('error', "{$reportType->name} reports can only be edited during the allowed time window ({$reportType->time_window_display}).");
        }

        $report->load('items');
        $heads = InspectionHead::active()->forType($type)->orderBy('sort_order')->orderBy('name')->get();
        $systemRemarks = $reportType->activeRemarks;
        $existingItems = $report->items->keyBy('inspection_head_id');

        return view('inspection_reports.edit', compact('reportType', 'report', 'heads', 'systemRemarks', 'existingItems', 'isWithinWindow'));
    }

    public function update(Request $request, string $type, InspectionReport $report)
    {
        $this->authorizeInspection('edit', $type);
        $reportType = $this->resolveReportType($type);

        if ($reportType->is_daily && !$reportType->isWithinAllowedTimeWindow()) {
            return redirect()->route('inspection-reports.show', ['type' => $type, 'report' => $report->id])
                ->with('error', "{$reportType->name} reports can only be edited during the allowed time window ({$reportType->time_window_display}).");
        }

        $hasSystemRemarks = $reportType->activeRemarks()->exists();

        $validationRules = [
            'overall_remarks'              => 'required|string|max:2000',
            'items'                        => 'required|array|min:1',
            'items.*.status'               => 'required|in:yes,no,na',
            'items.*.report_type_remark_id'=> $hasSystemRemarks ? 'required|exists:report_type_remarks,id' : 'nullable',
            'items.*.remarks'              => 'required|string|max:1000',
            'items.*.image'                => 'nullable|image|max:200',
        ];

        if (!$reportType->is_daily) {
            $validationRules['report_date'] = 'required|date';
        }

        $customMessages = [
            'overall_remarks.required'               => 'Overall remarks are mandatory.',
            'items.*.status.required'                => 'Status is mandatory for every checklist item.',
            'items.*.report_type_remark_id.required' => 'System remark selection is mandatory for every checklist item.',
            'items.*.remarks.required'               => 'Additional remarks are mandatory for every checklist item.',
            'items.*.image.max'                      => 'Each photo must not exceed 200 KB.',
        ];

        $request->validate($validationRules, $customMessages);

        $updateData = [
            'overall_remarks' => $request->overall_remarks,
        ];
        if (!$reportType->is_daily && $request->filled('report_date')) {
            $updateData['report_date'] = $request->report_date;
        }

        $report->update($updateData);
        $this->saveItems($report, $request, $type);

        return redirect()->route('inspection-reports.index', $type)
            ->with('success', "{$reportType->name} report updated successfully.");
    }

    public function destroy(string $type, InspectionReport $report)
    {
        $this->authorizeInspection('delete', $type);
        $reportType = $this->resolveReportType($type);

        foreach ($report->items as $item) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
        }
        $report->delete();

        return redirect()->route('inspection-reports.index', $type)
            ->with('success', "{$reportType->name} report deleted.");
    }

    public function print(string $type, InspectionReport $report)
    {
        $this->authorizeInspection('view', $type);
        $reportType = $this->resolveReportType($type);
        $report->load(['items.head', 'items.systemRemark', 'reporter', 'reportType']);

        return view('inspection_reports.print', compact('reportType', 'report'));
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function saveItems(InspectionReport $report, Request $request, string $type): void
    {
        $report->load('items');

        foreach ($request->input('items', []) as $headId => $itemData) {
            $existing  = $report->items->where('inspection_head_id', $headId)->first();
            $imagePath = $existing?->image_path;

            if ($request->hasFile("items.{$headId}.image")) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = $request->file("items.{$headId}.image")->store("inspection_images/{$type}", 'public');
            }

            InspectionReportItem::updateOrCreate(
                ['inspection_report_id' => $report->id, 'inspection_head_id' => $headId],
                [
                    'status'                => $itemData['status'] ?? 'na',
                    'report_type_remark_id' => $itemData['report_type_remark_id'] ?? null,
                    'remarks'               => $itemData['remarks'] ?? null,
                    'image_path'            => $imagePath,
                ]
            );
        }
    }
}
