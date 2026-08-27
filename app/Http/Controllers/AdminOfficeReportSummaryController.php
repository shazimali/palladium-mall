<?php

namespace App\Http\Controllers;

use App\Models\FlatInspectionReport;
use App\Models\InspectionReport;
use App\Models\ReportType;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AdminOfficeReportSummaryController extends Controller
{
    /**
     * Display the Admin Office Reports Summary Module.
     */
    public function index(Request $request): View
    {
        $this->authorizeAccess();

        $reportTypes = ReportType::active()->ordered()->get();
        $employees   = User::employees()->where('is_active', true)->orderBy('name')->get();
        
        // Date & Filter parameters
        $dateFrom = $request->query('date_from', now()->subDays(6)->format('Y-m-d'));
        $dateTo   = $request->query('date_to', now()->format('Y-m-d'));
        $selectedReportType = $request->query('report_type'); // null or key (e.g. 'cleaning', 'flat_inspection')
        $groupBy = $request->query('group_by', 'day'); // 'day' (Day-wise sections) or 'report' (Department-wise sections)
        $employeeId = $request->query('employee_id');

        $summaryData = $this->buildSummaryData($reportTypes, $selectedReportType, $dateFrom, $dateTo, $employeeId ? (int)$employeeId : null);

        return view('admin_office_reports.summary', array_merge([
            'title'              => 'Admin Office Reports Summary',
            'reportTypes'        => $reportTypes,
            'employees'          => $employees,
            'employeeId'         => $employeeId,
            'selectedReportType' => $selectedReportType,
            'dateFrom'           => $dateFrom,
            'dateTo'             => $dateTo,
            'groupBy'            => $groupBy,
        ], $summaryData));
    }

    /**
     * Display printable statement for the report summary.
     */
    public function print(Request $request): View
    {
        $this->authorizeAccess();

        $reportTypes = ReportType::active()->ordered()->get();
        $employees   = User::employees()->where('is_active', true)->orderBy('name')->get();
        $dateFrom = $request->query('date_from', now()->subDays(6)->format('Y-m-d'));
        $dateTo   = $request->query('date_to', now()->format('Y-m-d'));
        $selectedReportType = $request->query('report_type');
        $groupBy = $request->query('group_by', 'day');
        $employeeId = $request->query('employee_id');

        $summaryData = $this->buildSummaryData($reportTypes, $selectedReportType, $dateFrom, $dateTo, $employeeId ? (int)$employeeId : null);

        return view('admin_office_reports.summary_print', array_merge([
            'title'              => 'Admin Office Reports Summary Statement',
            'reportTypes'        => $reportTypes,
            'employees'          => $employees,
            'employeeId'         => $employeeId,
            'selectedReportType' => $selectedReportType,
            'dateFrom'           => $dateFrom,
            'dateTo'             => $dateTo,
            'groupBy'            => $groupBy,
        ], $summaryData));
    }

    /**
     * Authorize user access to reports summary.
     */
    private function authorizeAccess(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }
        if ($user->isSuperAdmin() || 
            $user->can('admin_office_reports_summary.view') ||
            $user->can('inspection_reports.view') || 
            $user->can('flat_inspections.view')) {
            return;
        }

        abort(403, 'Unauthorized action.');
    }

    /**
     * Build aggregated summary data based on selected filters.
     */
    private function buildSummaryData($reportTypes, ?string $selectedKey, string $dateFrom, string $dateTo, ?int $employeeId = null): array
    {
        try {
            $carbonFrom = Carbon::parse($dateFrom)->startOfDay();
            $carbonTo   = Carbon::parse($dateTo)->endOfDay();
        } catch (\Exception $e) {
            $carbonFrom = now()->subDays(6)->startOfDay();
            $carbonTo   = now()->endOfDay();
        }

        // Generate date period array (ordered descending for display)
        $period = CarbonPeriod::create($carbonFrom->copy()->startOfDay(), $carbonTo->copy()->startOfDay());
        $datesList = [];
        foreach ($period as $date) {
            $datesList[] = $date->format('Y-m-d');
        }
        $datesListDesc = array_reverse($datesList);
        $totalDays = count($datesListDesc);

        $currentReportTypeModel = null;
        if ($selectedKey) {
            $currentReportTypeModel = $reportTypes->firstWhere('key', $selectedKey);
        }

        // Mode 1: Single Selected Report Type
        if ($selectedKey && ($currentReportTypeModel || $selectedKey === 'flat_inspection')) {
            return $this->buildSingleReportSummary($selectedKey, $currentReportTypeModel, $carbonFrom, $carbonTo, $datesListDesc, $totalDays, $employeeId);
        }

        // Mode 2: All Reports Overview
        return $this->buildAllReportsSummary($reportTypes, $carbonFrom, $carbonTo, $datesListDesc, $totalDays, $employeeId);
    }

    /**
     * Aggregation for a specific selected report type.
     */
    private function buildSingleReportSummary(string $key, ?ReportType $reportType, Carbon $from, Carbon $to, array $datesListDesc, int $totalDays, ?int $employeeId = null): array
    {
        if ($key === 'flat_inspection') {
            $reports = FlatInspectionReport::with([
                'unit.floor', 'unit.block', 'tenant', 'agreement.tenant', 'inspector', 'inspectionPerson', 'items'
            ])
            ->whereDate('inspected_at', '>=', $from->format('Y-m-d'))
            ->whereDate('inspected_at', '<=', $to->format('Y-m-d'))
            ->when($employeeId, function ($q) use ($employeeId) {
                $q->where(function ($sq) use ($employeeId) {
                    $sq->where('inspected_by', $employeeId)
                       ->orWhere('inspection_person_id', $employeeId);
                });
            })
            ->orderByDesc('inspected_at')
            ->orderByDesc('id')
            ->get();

            $totalReports = $reports->count();
            $totalPassItems = 0;
            $totalFailItems = 0;
            $inspectorCounts = [];

            $dayWiseGroups = [];
            foreach ($datesListDesc as $d) {
                $dayWiseGroups[$d] = [
                    'date'         => $d,
                    'carbon'       => Carbon::parse($d),
                    'count'        => 0,
                    'pass_items'   => 0,
                    'fail_items'   => 0,
                    'reports'      => [],
                ];
            }

            foreach ($reports as $r) {
                $rDate = $r->inspected_at ? Carbon::parse($r->inspected_at)->format('Y-m-d') : null;
                $pCount = $r->items->where('status', 'yes')->count();
                $fCount = $r->items->where('status', 'no')->count();
                $totalPassItems += $pCount;
                $totalFailItems += $fCount;

                $inspectorName = $r->inspector?->name ?? ($r->inspectionPerson?->name ?? 'N/A');
                $inspectorCounts[$inspectorName] = ($inspectorCounts[$inspectorName] ?? 0) + 1;

                if ($rDate && isset($dayWiseGroups[$rDate])) {
                    $dayWiseGroups[$rDate]['count']++;
                    $dayWiseGroups[$rDate]['pass_items'] += $pCount;
                    $dayWiseGroups[$rDate]['fail_items'] += $fCount;
                    $dayWiseGroups[$rDate]['reports'][] = [
                        'id'              => $r->id,
                        'report_key'      => 'flat_inspection',
                        'report_name'     => 'Flat Inspection',
                        'date'            => $rDate,
                        'day_name'        => Carbon::parse($rDate)->format('l'),
                        'time'            => $r->inspected_at ? Carbon::parse($r->inspected_at)->format('h:i A') : '—',
                        'unit_number'     => $r->unit?->unit_number ?? 'N/A',
                        'tenant_name'     => $r->tenant?->name ?? ($r->agreement?->tenant?->name ?? 'Vacant'),
                        'member_or_unit'  => ($r->unit ? 'Flat ' . $r->unit->unit_number : 'Unit') . ($r->tenant ? ' (' . $r->tenant->name . ')' : ''),
                        'stage'           => ucfirst($r->type ?? 'Inspection'),
                        'reported_by'     => $inspectorName,
                        'pass_count'      => $pCount,
                        'fail_count'      => $fCount,
                        'admin_rating'    => null,
                        'overall_remarks' => $r->remarks ?? '',
                        'admin_photo_url' => null,
                        'view_url'        => route('inspection-reports.show', ['type' => 'flat_inspection', 'report' => $r->id]),
                    ];
                }
            }

            return [
                'isSingleReport'   => true,
                'totalDays'        => $totalDays,
                'activeReportName' => 'Flat Inspection',
                'activeReportKey'  => 'flat_inspection',
                'activeReportType' => null,
                'totalReports'     => $totalReports,
                'totalPassItems'   => $totalPassItems,
                'totalFailItems'   => $totalFailItems,
                'avgRating'        => null,
                'reporterCounts'   => $inspectorCounts,
                'dayWiseGroups'    => array_values($dayWiseGroups),
            ];
        }

        // Standard Inspection Reports
        $reports = InspectionReport::with(['reporter', 'member', 'items'])
            ->where('report_type_id', $reportType->id)
            ->whereDate('report_date', '>=', $from->format('Y-m-d'))
            ->whereDate('report_date', '<=', $to->format('Y-m-d'))
            ->when($employeeId, fn($q) => $q->where('reported_by', $employeeId))
            ->orderByDesc('report_date')
            ->orderByDesc('created_at')
            ->get();

        $totalReports = $reports->count();
        $totalPassItems = 0;
        $totalFailItems = 0;
        $ratings = [];
        $reporterCounts = [];
        $memberCounts = [];

        $dayWiseGroups = [];
        foreach ($datesListDesc as $d) {
            $dayWiseGroups[$d] = [
                'date'         => $d,
                'carbon'       => Carbon::parse($d),
                'count'        => 0,
                'pass_items'   => 0,
                'fail_items'   => 0,
                'ratings'      => [],
                'reports'      => [],
            ];
        }

        foreach ($reports as $r) {
            $rDate = $r->report_date ? Carbon::parse($r->report_date)->format('Y-m-d') : null;
            $pCount = $r->passCount();
            $fCount = $r->failCount();
            $totalPassItems += $pCount;
            $totalFailItems += $fCount;

            if ($r->admin_rating !== null && $r->admin_rating > 0) {
                $ratings[] = (float) $r->admin_rating;
            }

            $reporterName = $r->reporter?->name ?? 'Staff';
            $reporterCounts[$reporterName] = ($reporterCounts[$reporterName] ?? 0) + 1;

            if ($r->member) {
                $memberName = $r->member->name;
                $memberCounts[$memberName] = ($memberCounts[$memberName] ?? 0) + 1;
            }

            if ($rDate && isset($dayWiseGroups[$rDate])) {
                $dayWiseGroups[$rDate]['count']++;
                $dayWiseGroups[$rDate]['pass_items'] += $pCount;
                $dayWiseGroups[$rDate]['fail_items'] += $fCount;
                if ($r->admin_rating > 0) {
                    $dayWiseGroups[$rDate]['ratings'][] = $r->admin_rating;
                }
                $dayWiseGroups[$rDate]['reports'][] = [
                    'id'              => $r->id,
                    'report_key'      => $reportType->key,
                    'report_name'     => $reportType->name,
                    'date'            => $rDate,
                    'day_name'        => Carbon::parse($rDate)->format('l'),
                    'time'            => $r->created_at ? $r->created_at->format('h:i A') : '—',
                    'member_or_unit'  => $r->member?->name ?? '—',
                    'reported_by'     => $reporterName,
                    'pass_count'      => $pCount,
                    'fail_count'      => $fCount,
                    'admin_rating'    => $r->admin_rating,
                    'overall_remarks' => $r->overall_remarks,
                    'admin_remarks'   => $r->admin_remarks,
                    'admin_photo_url' => $r->admin_photo_url,
                    'view_url'        => route('inspection-reports.show', ['type' => $reportType->key, 'report' => $r->id]),
                ];
            }
        }

        $avgRating = count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : null;

        return [
            'isSingleReport'   => true,
            'totalDays'        => $totalDays,
            'activeReportName' => $reportType->name,
            'activeReportKey'  => $reportType->key,
            'activeReportType' => $reportType,
            'totalReports'     => $totalReports,
            'totalPassItems'   => $totalPassItems,
            'totalFailItems'   => $totalFailItems,
            'avgRating'        => $avgRating,
            'reporterCounts'   => $reporterCounts,
            'memberCounts'     => $memberCounts,
            'dayWiseGroups'    => array_values($dayWiseGroups),
        ];
    }

    /**
     * Aggregation for All Reports (Section-wise & Day-wise tables).
     */
    private function buildAllReportsSummary($reportTypes, Carbon $from, Carbon $to, array $datesListDesc, int $totalDays, ?int $employeeId = null): array
    {
        // 1. Fetch standard reports in date range
        $standardReports = InspectionReport::with(['reporter', 'member', 'items', 'reportType'])
            ->whereDate('report_date', '>=', $from->format('Y-m-d'))
            ->whereDate('report_date', '<=', $to->format('Y-m-d'))
            ->when($employeeId, fn($q) => $q->where('reported_by', $employeeId))
            ->orderByDesc('report_date')
            ->orderByDesc('created_at')
            ->get();

        // 2. Fetch flat inspection reports in date range
        $flatReports = FlatInspectionReport::with(['unit', 'tenant', 'inspector', 'items'])
            ->whereDate('inspected_at', '>=', $from->format('Y-m-d'))
            ->whereDate('inspected_at', '<=', $to->format('Y-m-d'))
            ->when($employeeId, function ($q) use ($employeeId) {
                $q->where(function ($sq) use ($employeeId) {
                    $sq->where('inspected_by', $employeeId)
                       ->orWhere('inspection_person_id', $employeeId);
                });
            })
            ->orderByDesc('inspected_at')
            ->orderByDesc('id')
            ->get();

        $totalSubmissions = $standardReports->count() + $flatReports->count();
        $totalPass = 0;
        $totalFail = 0;
        $allRatings = [];

        // 3. Build unified items array
        $allReportItems = [];

        foreach ($standardReports as $sr) {
            $pCount = $sr->passCount();
            $fCount = $sr->failCount();
            $totalPass += $pCount;
            $totalFail += $fCount;
            if ($sr->admin_rating !== null && is_numeric($sr->admin_rating) && (float)$sr->admin_rating > 0) {
                $allRatings[] = (float) $sr->admin_rating;
            }

            $rDate = $sr->report_date ? Carbon::parse($sr->report_date)->format('Y-m-d') : '';
            $allReportItems[] = [
                'id'              => $sr->id,
                'report_key'      => $sr->reportType?->key ?? 'cleaning',
                'report_name'     => $sr->reportType?->name ?? 'Report',
                'is_daily'        => $sr->reportType?->is_daily ?? false,
                'date'            => $rDate,
                'day_name'        => $rDate ? Carbon::parse($rDate)->format('l') : '',
                'time'            => $sr->created_at ? $sr->created_at->format('h:i A') : '—',
                'reported_by'     => $sr->reporter?->name ?? 'Staff',
                'member_or_unit'  => $sr->member?->name ?? '—',
                'pass_count'      => $pCount,
                'fail_count'      => $fCount,
                'admin_rating'    => $sr->admin_rating,
                'overall_remarks' => $sr->overall_remarks,
                'admin_photo_url' => $sr->admin_photo_url,
                'view_url'        => route('inspection-reports.show', ['type' => $sr->reportType?->key ?? 'cleaning', 'report' => $sr->id]),
                'timestamp'       => $sr->created_at ? $sr->created_at->timestamp : 0,
            ];
        }

        foreach ($flatReports as $fr) {
            $pCount = $fr->items->where('status', 'yes')->count();
            $fCount = $fr->items->where('status', 'no')->count();
            $totalPass += $pCount;
            $totalFail += $fCount;

            $rDate = $fr->inspected_at ? Carbon::parse($fr->inspected_at)->format('Y-m-d') : '';
            $allReportItems[] = [
                'id'              => $fr->id,
                'report_key'      => 'flat_inspection',
                'report_name'     => 'Flat Inspection',
                'is_daily'        => false,
                'date'            => $rDate,
                'day_name'        => $rDate ? Carbon::parse($rDate)->format('l') : '',
                'time'            => $fr->inspected_at ? Carbon::parse($fr->inspected_at)->format('h:i A') : '—',
                'reported_by'     => $fr->inspector?->name ?? ($fr->inspectionPerson?->name ?? 'Staff'),
                'member_or_unit'  => ($fr->unit ? 'Flat ' . $fr->unit->unit_number : 'Unit') . ($fr->tenant ? ' (' . $fr->tenant->name . ')' : ''),
                'pass_count'      => $pCount,
                'fail_count'      => $fCount,
                'admin_rating'    => null,
                'overall_remarks' => $fr->remarks,
                'admin_photo_url' => null,
                'view_url'        => route('inspection-reports.show', ['type' => 'flat_inspection', 'report' => $fr->id]),
                'timestamp'       => $fr->inspected_at ? Carbon::parse($fr->inspected_at)->timestamp : 0,
            ];
        }

        $overallAvgRating = count($allRatings) > 0 ? round(array_sum($allRatings) / count($allRatings), 1) : null;

        // 4. Section Structure 1: Grouped by Day / Date (Day-wise Sections)
        $daySections = [];
        foreach ($datesListDesc as $d) {
            $matchingForDay = array_filter($allReportItems, fn($item) => $item['date'] === $d);
            // Sort by report name and time
            usort($matchingForDay, fn($a, $b) => strcmp($a['report_name'], $b['report_name']));

            $dayPass = array_reduce($matchingForDay, fn($c, $i) => $c + $i['pass_count'], 0);
            $dayFail = array_reduce($matchingForDay, fn($c, $i) => $c + $i['fail_count'], 0);

            $daySections[] = [
                'date'       => $d,
                'carbon'     => Carbon::parse($d),
                'count'      => count($matchingForDay),
                'pass_count' => $dayPass,
                'fail_count' => $dayFail,
                'reports'    => $matchingForDay,
            ];
        }

        // 5. Section Structure 2: Grouped by Report Type (Department / Module Sections)
        $reportSections = [];
        foreach ($reportTypes as $rt) {
            $matchingForType = array_filter($allReportItems, fn($item) => $item['report_key'] === $rt->key);
            usort($matchingForType, fn($a, $b) => strcmp($b['date'] . $b['time'], $a['date'] . $a['time']));

            $pCount = array_reduce($matchingForType, fn($c, $i) => $c + $i['pass_count'], 0);
            $fCount = array_reduce($matchingForType, fn($c, $i) => $c + $i['fail_count'], 0);
            $ratings = array_map('floatval', array_filter(array_column($matchingForType, 'admin_rating'), fn($v) => $v !== null && is_numeric($v) && (float)$v > 0));
            $rAvg = count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : null;

            $reportSections[] = [
                'key'        => $rt->key,
                'name'       => $rt->name,
                'is_daily'   => $rt->is_daily,
                'count'      => count($matchingForType),
                'pass_count' => $pCount,
                'fail_count' => $fCount,
                'avg_rating' => $rAvg,
                'reports'    => $matchingForType,
            ];
        }

        return [
            'isSingleReport'   => false,
            'totalDays'        => $totalDays,
            'totalSubmissions' => $totalSubmissions,
            'totalPass'        => $totalPass,
            'totalFail'        => $totalFail,
            'overallAvgRating' => $overallAvgRating,
            'daySections'      => $daySections,
            'reportSections'   => $reportSections,
        ];
    }
}
