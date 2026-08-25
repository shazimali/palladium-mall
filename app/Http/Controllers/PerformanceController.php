<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAttendance;
use App\Models\PerformanceDailyEntry;
use App\Models\PerformanceMonthlyReport;
use App\Models\PerformanceTaskTemplate;
use App\Models\User;
use App\Services\PerformanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    public function __construct(protected PerformanceService $performance) {}

    // -----------------------------------------------------------------------
    // Dashboard — all employees overview
    // -----------------------------------------------------------------------

    public function index(Request $request): View
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $employees = User::with(['employeeProfile', 'performanceMonthlyReports' => function ($q) use ($month, $year) {
            $q->where('month', $month)->where('year', $year);
        }])
            ->where('is_employee', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('performance.index', compact('employees', 'month', 'year'));
    }

    // -----------------------------------------------------------------------
    // Daily Entry — employee logs attendance + tasks
    // -----------------------------------------------------------------------

    public function daily(Request $request): View
    {
        $user = auth()->user();
        $date = $request->get('date', today()->toDateString());

        $templates = PerformanceTaskTemplate::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Existing entries for that date
        $existingEntries = PerformanceDailyEntry::where('user_id', $user->id)
            ->where('date', $date)
            ->get()
            ->keyBy('template_id');

        // Existing attendance record
        $attendance = EmployeeAttendance::where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        $month       = Carbon::parse($date)->month;
        $year        = Carbon::parse($date)->year;
        $workingDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Running total for this month
        $monthScore = $this->performance->calculateMonthlyScore($user, $month, $year);

        return view('performance.daily', compact(
            'user', 'date', 'templates', 'existingEntries',
            'attendance', 'monthScore', 'workingDays'
        ));
    }

    public function saveDaily(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $data = $request->validate([
            'date'                => 'required|date|before_or_equal:today',
            'attendance_status'   => 'required|in:present,absent,leave,half_day',
            'check_in_at'         => 'nullable|date_format:H:i',
            'check_out_at'        => 'nullable|date_format:H:i|after:check_in_at',
            'attendance_note'     => 'nullable|string|max:500',
            'tasks'               => 'nullable|array',
            'tasks.*.template_id' => 'required|exists:performance_task_templates,id',
            'tasks.*.is_done'     => 'nullable|boolean',
            'tasks.*.note'        => 'nullable|string|max:500',
        ]);

        // Save attendance
        EmployeeAttendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $data['date']],
            [
                'status'       => $data['attendance_status'],
                'check_in_at'  => $data['check_in_at'] ?? null,
                'check_out_at' => $data['check_out_at'] ?? null,
                'note'         => $data['attendance_note'] ?? null,
            ]
        );

        // Save task entries
        if (! empty($data['tasks'])) {
            foreach ($data['tasks'] as $taskData) {
                $template = PerformanceTaskTemplate::where('id', $taskData['template_id'])
                    ->where('user_id', $user->id)
                    ->firstOrFail();

                $this->performance->saveDailyEntry(
                    user: $user,
                    template: $template,
                    date: $data['date'],
                    isDone: (bool) ($taskData['is_done'] ?? false),
                    note: $taskData['note'] ?? null,
                );
            }
        }

        return redirect()->route('performance.daily', ['date' => $data['date']])
            ->with('success', 'Daily entry saved successfully.');
    }

    // -----------------------------------------------------------------------
    // Generate Monthly Report
    // -----------------------------------------------------------------------

    public function generateReport(Request $request, User $employee): RedirectResponse
    {
        abort_unless($employee->isEmployee(), 404);

        $data = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020|max:2100',
        ]);

        $report = $this->performance->generateMonthlyReport(
            user: $employee,
            month: (int) $data['month'],
            year: (int) $data['year'],
            generatedBy: auth()->id(),
        );

        return redirect()->route('performance.report', [
            'employee' => $employee->id,
            'year'     => $data['year'],
            'month'    => $data['month'],
        ])->with('success', 'Monthly report generated successfully.');
    }

    // -----------------------------------------------------------------------
    // View Monthly Report
    // -----------------------------------------------------------------------

    public function report(User $employee, int $year, int $month): View
    {
        abort_unless($employee->isEmployee(), 404);

        // Employees can only view their own report
        if (! auth()->user()->isSuperAdmin() && ! auth()->user()->hasPermission('performance.reports.view')) {
            abort_unless($employee->id === auth()->id(), 403);
        }

        $report = PerformanceMonthlyReport::where('user_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $profile       = $employee->employeeProfile;
        $gridSheet     = $this->performance->getMonthlyGridSheet($employee, $month, $year);
        $taskBreakdown = $this->performance->getMonthlyTaskBreakdown($employee, $month, $year);
        $monthName     = Carbon::createFromDate($year, $month, 1)->format('F Y');

        // Daily attendance detail
        $attendances = EmployeeAttendance::where('user_id', $employee->id)
            ->forMonth($month, $year)
            ->orderBy('date')
            ->get();

        return view('performance.report', compact(
            'employee', 'profile', 'report', 'gridSheet', 'taskBreakdown',
            'monthName', 'month', 'year', 'attendances'
        ));
    }

    // -----------------------------------------------------------------------
    // PDF Export
    // -----------------------------------------------------------------------

    public function reportPdf(User $employee, int $year, int $month)
    {
        abort_unless($employee->isEmployee(), 404);

        $report = PerformanceMonthlyReport::where('user_id', $employee->id)
            ->where('month', $month)
            ->where('year', $year)
            ->firstOrFail();

        $profile       = $employee->employeeProfile;
        $gridSheet     = $this->performance->getMonthlyGridSheet($employee, $month, $year);
        $taskBreakdown = $this->performance->getMonthlyTaskBreakdown($employee, $month, $year);
        $monthName     = Carbon::createFromDate($year, $month, 1)->format('F Y');
        $attendances   = EmployeeAttendance::where('user_id', $employee->id)
            ->forMonth($month, $year)
            ->orderBy('date')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('performance.report-pdf', compact(
            'employee', 'profile', 'report', 'gridSheet', 'taskBreakdown',
            'monthName', 'month', 'year', 'attendances'
        ))->setPaper('a4', 'landscape');

        $filename = "performance-{$employee->name}-{$monthName}.pdf";

        return $pdf->download(str_replace(' ', '-', $filename));
    }
}
