<?php

namespace App\Http\Controllers;

use App\Models\FlatInspectionReport;
use App\Models\FlatInspectionReportItem;
use App\Models\InspectionHead;
use App\Models\InspectionPerson;
use App\Models\InspectionReport;
use App\Models\InspectionReportItem;
use App\Models\ReportType;
use App\Models\ReportTypeRemark;
use App\Models\ReportTypeMember;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InspectionReportController extends Controller
{
    private function resolveReportType(string $type): ReportType
    {
        $reportType = ReportType::where('key', $type)->first();
        if (!$reportType) {
            abort(404, "Report type '{$type}' not found.");
        }
        return $reportType;
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

        $employees = User::employees()->where('is_active', true)->orderBy('name')->get();
        $employeeId = $request->query('employee_id', $request->query('reported_by'));

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
            ->when(!empty($employeeId), function ($q) use ($employeeId) {
                $q->where(function ($sq) use ($employeeId) {
                    $sq->where('inspected_by', $employeeId)
                       ->orWhere('inspection_person_id', $employeeId);
                });
            })
            ->latest('inspected_at')
            ->latest('id');

            $reports = $query->paginate(20)->withQueryString();
            $units = Unit::with(['floor', 'block'])->orderBy('unit_number')->get();

            return view('inspection_reports.flat_index', compact('reportType', 'reports', 'units', 'employees'));
        }

        $query = InspectionReport::with(['reporter', 'member', 'items'])
            ->where('report_type_id', $reportType->id)
            ->orderByDesc('report_date')
            ->orderByDesc('created_at');

        if ($request->filled('member_id')) {
            $query->where('report_type_member_id', $request->member_id);
        }
        if ($request->filled('date_from')) {
            $query->where('report_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('report_date', '<=', $request->date_to);
        }
        if (!empty($employeeId)) {
            $query->where('reported_by', $employeeId);
        }

        $reports = $query->paginate(20)->withQueryString();
        $isWithinWindow = $reportType->isWithinAllowedTimeWindow();
        $members = $reportType->members;

        return view('inspection_reports.index', compact('reportType', 'reports', 'isWithinWindow', 'members', 'employees'));
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

        if ($reportType->is_daily && !$isWithinWindow && !Auth::user()->isSuperAdmin()) {
            return redirect()->route('inspection-reports.index', $type)
                ->with('error', "{$reportType->name} reports can only be created between {$reportType->time_window_display}.");
        }

        $hasMembers = $reportType->hasMembers();
        $activeMembers = $reportType->activeMembers;

        // If daily report mode and NO members, check if current user already submitted today's report
        if ($reportType->is_daily && $reportType->one_per_user_daily && !$hasMembers) {
            $existing = InspectionReport::where('report_type_id', $reportType->id)
                ->where('report_date', $today)
                ->where('reported_by', Auth::id())
                ->first();

            if ($existing) {
                return redirect()->route('inspection-reports.edit', ['type' => $type, 'report' => $existing->id])
                    ->with('info', "You have already created today's {$reportType->name} report. You can view or edit it below.");
            }
        }

        // Track member daily submissions if is_daily = true and hasMembers
        $todayMemberReportIds = [];
        if ($reportType->is_daily && $hasMembers) {
            $todayMemberReportIds = InspectionReport::where('report_type_id', $reportType->id)
                ->where('report_date', $today)
                ->whereNotNull('report_type_member_id')
                ->pluck('id', 'report_type_member_id')
                ->toArray();
        }

        $heads = InspectionHead::active()->forType($type)->orderBy('sort_order')->orderBy('name')->get();
        $systemRemarks = $reportType->activeRemarks;

        return view('inspection_reports.create', compact('reportType', 'heads', 'systemRemarks', 'activeMembers', 'hasMembers', 'todayMemberReportIds', 'today', 'isWithinWindow') + ['report' => null]);
    }

    public function store(Request $request, string $type)
    {
        $this->authorizeInspection('create', $type);
        $reportType = $this->resolveReportType($type);
        $isSuperAdmin = Auth::user()?->isSuperAdmin();

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

            if ($isSuperAdmin) {
                $validationRules['admin_remarks']        = 'nullable|string|max:2000';
                $validationRules['admin_rating']         = 'nullable|in:good,bad';
                $validationRules['admin_photo']          = 'nullable|image|max:200';
                $validationRules['items.*.admin_rating']  = 'nullable|in:good,bad';
                $validationRules['items.*.admin_remarks'] = 'nullable|string|max:1000';
                $validationRules['items.*.admin_photo']   = 'nullable|image|max:200';
            }

            $customMessages = [
                'unit_id.required'                       => 'Please select a vacant flat/shop.',
                'inspection_person_id.required'          => 'Inspection Person / Officer is mandatory.',
                'flat_condition.required'                => 'Flat condition is mandatory.',
                'remarks.required'                       => 'Overall inspection remarks are mandatory.',
                'items.*.status.required'                => 'Status (Pass / Fail / N/A) is mandatory for every checklist item.',
                'items.*.report_type_remark_id.required' => 'System remark selection is mandatory for every checklist item.',
                'items.*.remarks.required'               => 'Additional remarks are mandatory for every checklist item.',
                'items.*.image.max'                      => 'Each photo must not exceed 200 KB.',
                'admin_photo.max'                        => 'Admin feedback photo must not exceed 200 KB.',
            ];

            $request->validate($validationRules, $customMessages);

            $adminPhotoPath = null;
            if ($isSuperAdmin && $request->hasFile('admin_photo')) {
                $adminPhotoPath = $request->file('admin_photo')->store('inspection_photos', 'public');
            }

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
                'admin_remarks'        => $isSuperAdmin ? $request->admin_remarks : null,
                'admin_rating'         => $isSuperAdmin ? $request->admin_rating : null,
                'admin_photo'          => $adminPhotoPath,
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

                $itemAdminPhoto = null;
                if ($isSuperAdmin && $request->hasFile("items.{$headId}.admin_photo")) {
                    $itemAdminPhoto = $request->file("items.{$headId}.admin_photo")->store('inspection_photos/flat/items', 'public');
                }

                FlatInspectionReportItem::create([
                    'flat_inspection_report_id' => $report->id,
                    'inspection_head_id'        => $headId,
                    'status'                    => $statusVal,
                    'report_type_remark_id'     => $itemData['report_type_remark_id'] ?? null,
                    'remarks'                   => $itemData['remarks'] ?? null,
                    'image_path'                => $imagePath,
                    'admin_rating'              => $isSuperAdmin ? ($itemData['admin_rating'] ?? null) : null,
                    'admin_remarks'             => $isSuperAdmin ? ($itemData['admin_remarks'] ?? null) : null,
                    'admin_photo'               => $itemAdminPhoto,
                ]);
            }

            return redirect()->route('inspection-reports.index', 'flat_inspection')
                ->with('success', 'Vacant Flat Inspection recorded successfully.');
        }

        // Daily Time Window check (bypassed for Super Admin)
        if ($reportType->is_daily && !$reportType->isWithinAllowedTimeWindow() && !Auth::user()->isSuperAdmin()) {
            return redirect()->route('inspection-reports.index', $type)
                ->with('error', "{$reportType->name} reports can only be generated between {$reportType->time_window_display}.");
        }

        $hasMembers = $reportType->hasMembers();
        $reportDate = $reportType->is_daily ? now()->toDateString() : $request->report_date;

        // Daily 1-Report-per-Member check (if has members and is_daily)
        if ($reportType->is_daily && $hasMembers) {
            $memberId = $request->input('report_type_member_id');
            if ($memberId) {
                $exists = InspectionReport::where('report_type_id', $reportType->id)
                    ->where('report_type_member_id', $memberId)
                    ->where('report_date', $reportDate)
                    ->exists();

                if ($exists) {
                    $member = ReportTypeMember::find($memberId);
                    $memberName = $member ? $member->member_name : 'Selected member';
                    return redirect()->route('inspection-reports.index', $type)
                        ->with('error', "A daily {$reportType->name} report for {$memberName} has already been submitted for today.");
                }
            }
        } elseif ($reportType->is_daily && $reportType->one_per_user_daily && !$hasMembers) {
            // Daily 1-Report-per-User check (fallback if no members)
            $exists = InspectionReport::where('report_type_id', $reportType->id)
                ->where('report_date', $reportDate)
                ->where('reported_by', Auth::id())
                ->exists();

            if ($exists) {
                return redirect()->route('inspection-reports.index', $type)
                    ->with('error', "You have already submitted a {$reportType->name} report for today.");
            }
        }

        $hasSystemRemarks = $reportType->activeRemarks()->exists();

        $validationRules = [
            'overall_remarks'              => 'nullable|string|max:2000',
            'items'                        => 'required|array|min:1',
            'items.*.status'               => 'required|in:yes,no,na',
            'items.*.report_type_remark_id'=> $hasSystemRemarks ? 'required|exists:report_type_remarks,id' : 'nullable',
            'items.*.remarks'              => 'required|string|max:1000',
            'items.*.image'                => 'nullable|image|max:200', // 200 KB
        ];

        if ($isSuperAdmin) {
            $validationRules['admin_remarks']        = 'nullable|string|max:2000';
            $validationRules['admin_rating']         = 'nullable|in:good,bad';
            $validationRules['admin_photo']          = 'nullable|image|max:200';
            $validationRules['items.*.admin_rating']  = 'nullable|in:good,bad';
            $validationRules['items.*.admin_remarks'] = 'nullable|string|max:1000';
            $validationRules['items.*.admin_photo']   = 'nullable|image|max:200';
        }

        if ($hasMembers) {
            $validationRules['report_type_member_id'] = 'required|exists:report_type_members,id';
        }

        if (!$reportType->is_daily) {
            $validationRules['report_date'] = 'required|date';
        }

        $customMessages = [
            'report_type_member_id.required'         => 'Please select an active member.',
            'items.*.status.required'                => 'Status is mandatory for every checklist item.',
            'items.*.report_type_remark_id.required' => 'System remark selection is mandatory for every checklist item.',
            'items.*.remarks.required'               => 'Additional remarks are mandatory for every checklist item.',
            'items.*.image.max'                      => 'Each photo must not exceed 200 KB.',
            'admin_photo.max'                        => 'Admin feedback photo must not exceed 200 KB.',
        ];

        $request->validate($validationRules, $customMessages);

        $adminPhotoPath = null;
        if ($isSuperAdmin && $request->hasFile('admin_photo')) {
            $adminPhotoPath = $request->file('admin_photo')->store('inspection_photos', 'public');
        }

        $report = InspectionReport::create([
            'report_type_id'        => $reportType->id,
            'report_type_member_id' => $hasMembers ? $request->report_type_member_id : null,
            'report_date'           => $reportDate,
            'reported_by'           => Auth::id(),
            'overall_remarks'       => $request->overall_remarks,
            'admin_remarks'         => $isSuperAdmin ? $request->admin_remarks : null,
            'admin_rating'          => $isSuperAdmin ? $request->admin_rating : null,
            'admin_photo'           => $adminPhotoPath,
            'status'                => 'completed',
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

        $report = InspectionReport::with(['items.head', 'items.systemRemark', 'reporter', 'reportType', 'member'])
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

        $report = InspectionReport::with(['items', 'member'])->findOrFail($reportId);
        $isWithinWindow = $reportType->isWithinAllowedTimeWindow();

        if ($reportType->is_daily && !$isWithinWindow && !Auth::user()->isSuperAdmin()) {
            return redirect()->route('inspection-reports.show', ['type' => $type, 'report' => $report->id])
                ->with('error', "{$reportType->name} reports can only be edited during the allowed time window ({$reportType->time_window_display}).");
        }

        $report->load('items');
        $heads = InspectionHead::active()->forType($type)->orderBy('sort_order')->orderBy('name')->get();
        $systemRemarks = $reportType->activeRemarks;
        $activeMembers = $reportType->activeMembers;
        $hasMembers = $reportType->hasMembers();
        $existingItems = $report->items->keyBy('inspection_head_id');

        return view('inspection_reports.edit', compact('reportType', 'report', 'heads', 'systemRemarks', 'activeMembers', 'hasMembers', 'existingItems', 'isWithinWindow'));
    }

    public function update(Request $request, string $type, $reportId)
    {
        $this->authorizeInspection('edit', $type);
        $reportType = $this->resolveReportType($type);
        $isSuperAdmin = Auth::user()?->isSuperAdmin();

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

            if ($isSuperAdmin) {
                $validationRules['admin_remarks']        = 'nullable|string|max:2000';
                $validationRules['admin_rating']         = 'nullable|in:good,bad';
                $validationRules['admin_photo']          = 'nullable|image|max:200';
                $validationRules['remove_admin_photo']   = 'nullable|boolean';
                $validationRules['items.*.admin_rating']  = 'nullable|in:good,bad';
                $validationRules['items.*.admin_remarks'] = 'nullable|string|max:1000';
                $validationRules['items.*.admin_photo']   = 'nullable|image|max:200';
            }

            $customMessages = [
                'unit_id.required'                       => 'Please select a unit/flat.',
                'inspection_person_id.required'          => 'Inspection Person / Officer is mandatory.',
                'flat_condition.required'                => 'Flat condition is mandatory.',
                'remarks.required'                       => 'Overall inspection remarks are mandatory.',
                'items.*.status.required'                => 'Status (Pass / Fail / N/A) is mandatory for every checklist item.',
                'items.*.report_type_remark_id.required' => 'System remark selection is mandatory for every checklist item.',
                'items.*.remarks.required'               => 'Additional remarks are mandatory for every checklist item.',
                'items.*.image.max'                      => 'Each photo must not exceed 200 KB.',
                'admin_photo.max'                        => 'Admin feedback photo must not exceed 200 KB.',
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

            if ($isSuperAdmin) {
                $updateData['admin_remarks'] = $request->admin_remarks;
                $updateData['admin_rating']  = $request->admin_rating;

                if ($request->boolean('remove_admin_photo')) {
                    if ($report->admin_photo && Storage::disk('public')->exists($report->admin_photo)) {
                        Storage::disk('public')->delete($report->admin_photo);
                    }
                    $updateData['admin_photo'] = null;
                }

                if ($request->hasFile('admin_photo')) {
                    if ($report->admin_photo && Storage::disk('public')->exists($report->admin_photo)) {
                        Storage::disk('public')->delete($report->admin_photo);
                    }
                    $updateData['admin_photo'] = $request->file('admin_photo')->store('inspection_photos', 'public');
                }
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

                $itemAdminRating = $isSuperAdmin ? ($itemData['admin_rating'] ?? null) : $existing?->admin_rating;
                $itemAdminRemarks = $isSuperAdmin ? ($itemData['admin_remarks'] ?? null) : $existing?->admin_remarks;
                $itemAdminPhoto = $existing?->admin_photo;

                if ($isSuperAdmin) {
                    if (!empty($itemData['remove_admin_photo'])) {
                        if ($itemAdminPhoto && Storage::disk('public')->exists($itemAdminPhoto)) {
                            Storage::disk('public')->delete($itemAdminPhoto);
                        }
                        $itemAdminPhoto = null;
                    }
                    if ($request->hasFile("items.{$headId}.admin_photo")) {
                        if ($itemAdminPhoto && Storage::disk('public')->exists($itemAdminPhoto)) {
                            Storage::disk('public')->delete($itemAdminPhoto);
                        }
                        $itemAdminPhoto = $request->file("items.{$headId}.admin_photo")->store('inspection_photos/flat/items', 'public');
                    }
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
                        'admin_rating'          => $itemAdminRating,
                        'admin_remarks'         => $itemAdminRemarks,
                        'admin_photo'           => $itemAdminPhoto,
                    ]
                );
            }

            return redirect()->route('inspection-reports.index', 'flat_inspection')
                ->with('success', 'Flat inspection updated successfully.');
        }

        $report = InspectionReport::findOrFail($reportId);

        if ($reportType->is_daily && !$reportType->isWithinAllowedTimeWindow() && !Auth::user()->isSuperAdmin()) {
            return redirect()->route('inspection-reports.show', ['type' => $type, 'report' => $report->id])
                ->with('error', "{$reportType->name} reports can only be edited during the allowed time window ({$reportType->time_window_display}).");
        }

        $hasMembers = $reportType->hasMembers();
        $hasSystemRemarks = $reportType->activeRemarks()->exists();

        $validationRules = [
            'overall_remarks'              => 'nullable|string|max:2000',
            'items'                        => 'required|array|min:1',
            'items.*.status'               => 'required|in:yes,no,na',
            'items.*.report_type_remark_id'=> $hasSystemRemarks ? 'required|exists:report_type_remarks,id' : 'nullable',
            'items.*.remarks'              => 'required|string|max:1000',
            'items.*.image'                => 'nullable|image|max:200',
        ];

        if ($isSuperAdmin) {
            $validationRules['admin_remarks']        = 'nullable|string|max:2000';
            $validationRules['admin_rating']         = 'nullable|in:good,bad';
            $validationRules['admin_photo']          = 'nullable|image|max:200';
            $validationRules['remove_admin_photo']   = 'nullable|boolean';
            $validationRules['items.*.admin_rating']  = 'nullable|in:good,bad';
            $validationRules['items.*.admin_remarks'] = 'nullable|string|max:1000';
            $validationRules['items.*.admin_photo']   = 'nullable|image|max:200';
        }

        if ($hasMembers) {
            $validationRules['report_type_member_id'] = 'required|exists:report_type_members,id';
        }

        if (!$reportType->is_daily) {
            $validationRules['report_date'] = 'required|date';
        }

        $customMessages = [
            'report_type_member_id.required'         => 'Please select an active member.',
            'items.*.status.required'                => 'Status is mandatory for every checklist item.',
            'items.*.report_type_remark_id.required' => 'System remark selection is mandatory for every checklist item.',
            'items.*.remarks.required'               => 'Additional remarks are mandatory for every checklist item.',
            'items.*.image.max'                      => 'Each photo must not exceed 200 KB.',
            'admin_photo.max'                        => 'Admin feedback photo must not exceed 200 KB.',
        ];

        $request->validate($validationRules, $customMessages);

        $updateData = [
            'overall_remarks' => $request->overall_remarks,
        ];
        if ($hasMembers && $request->filled('report_type_member_id')) {
            $updateData['report_type_member_id'] = $request->report_type_member_id;
        }
        if (!$reportType->is_daily && $request->filled('report_date')) {
            $updateData['report_date'] = $request->report_date;
        }

        if ($isSuperAdmin) {
            $updateData['admin_remarks'] = $request->admin_remarks;
            $updateData['admin_rating']  = $request->admin_rating;

            if ($request->boolean('remove_admin_photo')) {
                if ($report->admin_photo && Storage::disk('public')->exists($report->admin_photo)) {
                    Storage::disk('public')->delete($report->admin_photo);
                }
                $updateData['admin_photo'] = null;
            }

            if ($request->hasFile('admin_photo')) {
                if ($report->admin_photo && Storage::disk('public')->exists($report->admin_photo)) {
                    Storage::disk('public')->delete($report->admin_photo);
                }
                $updateData['admin_photo'] = $request->file('admin_photo')->store('inspection_photos', 'public');
            }
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
                if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
                    Storage::disk('public')->delete($item->image_path);
                }
            }
            $report->delete();

            return redirect()->route('inspection-reports.index', 'flat_inspection')
                ->with('success', 'Flat inspection deleted successfully.');
        }

        $report = InspectionReport::findOrFail($reportId);

        foreach ($report->items as $item) {
            if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
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

        $report = InspectionReport::with(['items.head', 'items.systemRemark', 'reporter', 'reportType', 'member'])
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

            $isSuperAdmin = Auth::user()?->isSuperAdmin();
            $adminRating = $isSuperAdmin ? ($itemData['admin_rating'] ?? null) : $existing?->admin_rating;
            $adminRemarks = $isSuperAdmin ? ($itemData['admin_remarks'] ?? null) : $existing?->admin_remarks;
            $adminPhoto = $existing?->admin_photo;

            if ($isSuperAdmin) {
                if (!empty($itemData['remove_admin_photo'])) {
                    if ($adminPhoto && Storage::disk('public')->exists($adminPhoto)) {
                        Storage::disk('public')->delete($adminPhoto);
                    }
                    $adminPhoto = null;
                }
                if ($request->hasFile("items.{$headId}.admin_photo")) {
                    if ($adminPhoto && Storage::disk('public')->exists($adminPhoto)) {
                        Storage::disk('public')->delete($adminPhoto);
                    }
                    $adminPhoto = $request->file("items.{$headId}.admin_photo")->store("inspection_photos/{$type}/items", 'public');
                }
            }

            InspectionReportItem::updateOrCreate(
                ['inspection_report_id' => $report->id, 'inspection_head_id' => $headId],
                [
                    'status'                => $itemData['status'] ?? 'na',
                    'report_type_remark_id' => $itemData['report_type_remark_id'] ?? null,
                    'remarks'               => $itemData['remarks'] ?? null,
                    'image_path'            => $imagePath,
                    'admin_rating'          => $adminRating,
                    'admin_remarks'         => $adminRemarks,
                    'admin_photo'           => $adminPhoto,
                ]
            );
        }
    }
}
