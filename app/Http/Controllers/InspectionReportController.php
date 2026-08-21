<?php

namespace App\Http\Controllers;

use App\Models\FlatInspectionReport;
use App\Models\FlatInspectionReportItem;
use App\Models\InspectionHead;
use App\Models\InspectionPerson;
use App\Models\InspectionReport;
use App\Models\InspectionReportItem;
use App\Models\ReportType;
use App\Models\Unit;
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

        if ($type === 'flat_inspection') {
            $query = FlatInspectionReport::with([
                'unit.floor', 'unit.block',
                'agreement.unit.floor', 'agreement.unit.block',
                'agreement.tenant', 'tenant',
                'inspector', 'inspectionPerson', 'items'
            ])
            ->when($request->filled('unit_id'), function ($q) use ($request) {
                $q->where('unit_id', $request->unit_id)
                  ->orWhereHas('agreement', fn($aq) => $aq->where('unit_id', $request->unit_id));
            })
            ->when($request->filled('stage'), fn($q) => $q->where('type', $request->stage))
            ->when($request->filled('date_from'), fn($q) => $q->where('inspected_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->where('inspected_at', '<=', $request->date_to))
            ->latest('inspected_at')
            ->latest('id');

            $reports = $query->paginate(20)->withQueryString();
            $units = Unit::with(['floor', 'block'])->orderBy('unit_number')->get();

            return view('inspection_reports.flat_index', compact('reportType', 'reports', 'units'));
        }

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

        if ($type === 'flat_inspection') {
            // Load vacant units and units without active agreements
            $units = Unit::with(['floor', 'block'])
                ->where('status', 'vacant')
                ->orWhereDoesntHave('agreements', function ($q) {
                    $q->where('status', 'active');
                })
                ->orderBy('unit_number')
                ->get();

            $heads = InspectionHead::active()->flatInspection()->orderBy('sort_order')->orderBy('name')->get();
            $systemRemarks = $reportType->activeRemarks;
            $inspectionPersons = InspectionPerson::where('is_active', true)->orderBy('name')->get();

            return view('inspection_reports.flat_create', compact('reportType', 'units', 'heads', 'systemRemarks', 'inspectionPersons', 'today'));
        }

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

        if ($type === 'flat_inspection') {
            $hasSystemRemarks = $reportType->activeRemarks()->exists();

            $validationRules = [
                'unit_id'                       => 'required|exists:units,id',
                'inspected_at'                  => 'required|date',
                'inspection_person_id'          => 'required|exists:inspection_persons,id',
                'inspection_member'             => 'nullable|string|max:255',
                'flat_condition'                => 'required|in:good,average,poor',
                'remarks'                       => 'required|string|max:2000',
                'items'                         => 'required|array|min:1',
                'items.*.status'                => 'required|in:pass,fail,na,yes,no',
                'items.*.report_type_remark_id' => $hasSystemRemarks ? 'required|exists:report_type_remarks,id' : 'nullable',
                'items.*.remarks'               => 'required|string|max:1000',
                'items.*.image'                 => 'nullable|image|max:200',
            ];

            $customMessages = [
                'unit_id.required'                       => 'Please select a vacant flat/shop.',
                'inspection_person_id.required'          => 'Inspection Person / Officer is mandatory.',
                'flat_condition.required'                => 'Flat condition is mandatory.',
                'remarks.required'                       => 'Overall inspection remarks are mandatory.',
                'items.*.status.required'                => 'Status (Pass / Fail / N/A) is mandatory for every checklist item.',
                'items.*.report_type_remark_id.required' => 'System remark selection is mandatory for every checklist item.',
                'items.*.remarks.required'               => 'Additional remarks are mandatory for every checklist item.',
                'items.*.image.max'                      => 'Each photo must not exceed 200 KB.',
            ];

            $request->validate($validationRules, $customMessages);

            $report = FlatInspectionReport::create([
                'unit_id'              => $request->unit_id,
                'agreement_id'         => null,
                'tenant_id'            => null,
                'type'                 => 'vacant',
                'inspected_by'         => Auth::id(),
                'inspection_member'    => $request->inspection_member,
                'inspection_person_id' => $request->inspection_person_id,
                'inspected_at'         => $request->inspected_at,
                'flat_condition'       => $request->flat_condition,
                'remarks'              => $request->remarks,
            ]);

            foreach ($request->input('items', []) as $headId => $itemData) {
                $statusVal = match ($itemData['status'] ?? 'na') {
                    'pass', 'yes' => true,
                    'fail', 'no'  => false,
                    default       => null,
                };

                $imagePath = null;
                if ($request->hasFile("items.{$headId}.image")) {
                    $imagePath = $request->file("items.{$headId}.image")->store('inspection_images/flat', 'public');
                }

                FlatInspectionReportItem::create([
                    'flat_inspection_report_id' => $report->id,
                    'inspection_head_id'        => $headId,
                    'status'                    => $statusVal,
                    'report_type_remark_id'     => $itemData['report_type_remark_id'] ?? null,
                    'remarks'                   => $itemData['remarks'] ?? null,
                    'image_path'                => $imagePath,
                ]);
            }

            return redirect()->route('inspection-reports.index', 'flat_inspection')
                ->with('success', 'Vacant Flat Inspection recorded successfully.');
        }

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

    public function show(string $type, $reportId)
    {
        $this->authorizeInspection('view', $type);
        $reportType = $this->resolveReportType($type);

        if ($type === 'flat_inspection') {
            $report = FlatInspectionReport::with([
                'unit.floor', 'unit.block',
                'agreement.unit.floor', 'agreement.unit.block',
                'agreement.tenant', 'tenant',
                'inspector', 'inspectionPerson',
                'items.head'
            ])->findOrFail($reportId);

            return view('flat_inspection_reports.show', compact('report', 'reportType'));
        }

        $report = InspectionReport::with(['items.head', 'items.systemRemark', 'reporter', 'reportType'])
            ->findOrFail($reportId);

        return view('inspection_reports.show', compact('reportType', 'report'));
    }

    public function edit(string $type, $reportId)
    {
        $this->authorizeInspection('edit', $type);
        $reportType = $this->resolveReportType($type);

        if ($type === 'flat_inspection') {
            $report = FlatInspectionReport::with(['items', 'unit', 'agreement.unit'])->findOrFail($reportId);
            $heads = InspectionHead::active()->flatInspection()->orderBy('sort_order')->orderBy('name')->get();
            $systemRemarks = $reportType->activeRemarks;
            $inspectionPersons = InspectionPerson::where('is_active', true)->orderBy('name')->get();
            $existingItems = $report->items->keyBy('inspection_head_id');
            $today = $report->inspected_at?->toDateString() ?? now()->toDateString();
            $units = Unit::with(['floor', 'block'])->orderBy('unit_number')->get();

            return view('inspection_reports.flat_edit', compact('reportType', 'report', 'units', 'heads', 'systemRemarks', 'inspectionPersons', 'today', 'existingItems'));
        }

        $report = InspectionReport::findOrFail($reportId);
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

    public function update(Request $request, string $type, $reportId)
    {
        $this->authorizeInspection('edit', $type);
        $reportType = $this->resolveReportType($type);

        if ($type === 'flat_inspection') {
            $report = FlatInspectionReport::with('items')->findOrFail($reportId);
            $hasSystemRemarks = $reportType->activeRemarks()->exists();

            $validationRules = [
                'unit_id'                       => 'required|exists:units,id',
                'inspected_at'                  => 'required|date',
                'inspection_person_id'          => 'required|exists:inspection_persons,id',
                'inspection_member'             => 'nullable|string|max:255',
                'flat_condition'                => 'required|in:good,average,poor',
                'remarks'                       => 'required|string|max:2000',
                'items'                         => 'required|array|min:1',
                'items.*.status'                => 'required|in:pass,fail,na,yes,no',
                'items.*.report_type_remark_id' => $hasSystemRemarks ? 'required|exists:report_type_remarks,id' : 'nullable',
                'items.*.remarks'               => 'required|string|max:1000',
                'items.*.image'                 => 'nullable|image|max:200',
            ];

            $customMessages = [
                'unit_id.required'                       => 'Please select a unit/flat.',
                'inspection_person_id.required'          => 'Inspection Person / Officer is mandatory.',
                'flat_condition.required'                => 'Flat condition is mandatory.',
                'remarks.required'                       => 'Overall inspection remarks are mandatory.',
                'items.*.status.required'                => 'Status (Pass / Fail / N/A) is mandatory for every checklist item.',
                'items.*.report_type_remark_id.required' => 'System remark selection is mandatory for every checklist item.',
                'items.*.remarks.required'               => 'Additional remarks are mandatory for every checklist item.',
                'items.*.image.max'                      => 'Each photo must not exceed 200 KB.',
            ];

            $request->validate($validationRules, $customMessages);

            $updateData = [
                'inspected_at'         => $request->inspected_at,
                'inspection_person_id' => $request->inspection_person_id,
                'inspection_member'    => $request->inspection_member,
                'flat_condition'       => $request->flat_condition,
                'remarks'              => $request->remarks,
            ];

            if ($request->filled('unit_id') && !$report->agreement_id) {
                $updateData['unit_id'] = $request->unit_id;
            }

            $report->update($updateData);

            foreach ($request->input('items', []) as $headId => $itemData) {
                $statusVal = match ($itemData['status'] ?? 'na') {
                    'pass', 'yes' => true,
                    'fail', 'no'  => false,
                    default       => null,
                };

                $existing = $report->items->where('inspection_head_id', $headId)->first();
                $imagePath = $existing?->image_path;

                if ($request->hasFile("items.{$headId}.image")) {
                    if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                        Storage::disk('public')->delete($imagePath);
                    }
                    $imagePath = $request->file("items.{$headId}.image")->store('inspection_images/flat', 'public');
                }

                FlatInspectionReportItem::updateOrCreate(
                    [
                        'flat_inspection_report_id' => $report->id,
                        'inspection_head_id'        => $headId,
                    ],
                    [
                        'status'                => $statusVal,
                        'report_type_remark_id' => $itemData['report_type_remark_id'] ?? null,
                        'remarks'               => $itemData['remarks'] ?? null,
                        'image_path'            => $imagePath,
                    ]
                );
            }

            return redirect()->route('inspection-reports.index', 'flat_inspection')
                ->with('success', 'Flat inspection updated successfully.');
        }

        $report = InspectionReport::findOrFail($reportId);

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

    public function destroy(string $type, $reportId)
    {
        $this->authorizeInspection('delete', $type);
        $reportType = $this->resolveReportType($type);

        if ($type === 'flat_inspection') {
            $report = FlatInspectionReport::findOrFail($reportId);
            foreach ($report->items as $item) {
                if ($item->image_path) {
                    Storage::disk('public')->delete($item->image_path);
                }
            }
            $report->delete();

            return redirect()->route('inspection-reports.index', 'flat_inspection')
                ->with('success', 'Flat inspection deleted successfully.');
        }

        $report = InspectionReport::findOrFail($reportId);
        foreach ($report->items as $item) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
        }
        $report->delete();

        return redirect()->route('inspection-reports.index', $type)
            ->with('success', "{$reportType->name} report deleted.");
    }

    public function print(string $type, $reportId)
    {
        $this->authorizeInspection('view', $type);
        $reportType = $this->resolveReportType($type);

        if ($type === 'flat_inspection') {
            $report = FlatInspectionReport::with([
                'unit.floor', 'unit.block',
                'agreement.unit.floor', 'agreement.unit.block',
                'agreement.tenant', 'tenant',
                'inspector', 'inspectionPerson',
                'items.head'
            ])->findOrFail($reportId);

            return view('flat_inspection_reports.print', [
                'report'    => $report,
                'agreement' => $report->agreement,
                'tenant'    => $report->tenant,
            ]);
        }

        $report = InspectionReport::with(['items.head', 'items.systemRemark', 'reporter', 'reportType'])
            ->findOrFail($reportId);

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
