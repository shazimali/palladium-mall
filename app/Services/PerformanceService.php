<?php

namespace App\Services;

use App\Models\EmployeeAttendance;
use App\Models\FlatInspectionReport;
use App\Models\InspectionReport;
use App\Models\PerformanceDailyEntry;
use App\Models\PerformanceMonthlyReport;
use App\Models\PerformanceTaskTemplate;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class PerformanceService
{
    // -----------------------------------------------------------------------
    // Grade Calculation
    // -----------------------------------------------------------------------

    /**
     * Return grade string based on performance percentage.
     */
    public function getGrade(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'Excellent',
            $percentage >= 75 => 'Good',
            $percentage >= 60 => 'Average',
            default           => 'Poor',
        };
    }

    // -----------------------------------------------------------------------
    // Daily Point Value
    // -----------------------------------------------------------------------

    /**
     * Point value for a single unit/day of a task in the given month/year.
     */
    public function getDailyPoints(PerformanceTaskTemplate $template, int $month, int $year): float
    {
        return $template->unitPoints($month, $year);
    }

    // -----------------------------------------------------------------------
    // Monthly Score Calculation
    // -----------------------------------------------------------------------

    /**
     * Calculate monthly performance totals for an employee.
     *
     * @return array{
     *     total_max_points: float,
     *     total_earned_points: float,
     *     performance_percentage: float,
     *     grade: string,
     *     working_days: int,
     *     days_present: int,
     *     days_absent: int,
     * }
     */
    public function calculateMonthlyScore(User $user, int $month, int $year): array
    {
        $sheet = $this->getMonthlyGridSheet($user, $month, $year);
        $summary = $sheet['summary'];

        return [
            'total_max_points'       => $summary['total_monthly_max'],
            'total_earned_points'    => $summary['total_earned'],
            'performance_percentage' => $summary['performance_percentage'],
            'grade'                  => $summary['grade'],
            'working_days'           => $summary['working_days'],
            'days_present'           => $summary['days_present'],
            'days_absent'            => $summary['days_absent'],
        ];
    }

    // -----------------------------------------------------------------------
    // Salary Calculation
    // -----------------------------------------------------------------------

    /**
     * Calculate salary components from employee profile + performance data.
     *
     * @return array{
     *     basic_salary: float,
     *     fuel_allowance: float,
     *     attendance_incentive: float,
     *     collection_incentive_pct: float,
     *     collection_incentive_amt: float,
     *     other_works_amount: float,
     *     total_basic: float,
     *     final_salary: float,
     * }
     */
    public function calculateSalary(User $user, array $scoreData): array
    {
        $profile = $user->employeeProfile;

        if (! $profile) {
            return array_fill_keys([
                'basic_salary', 'fuel_allowance', 'attendance_incentive',
                'collection_incentive_pct', 'collection_incentive_amt',
                'other_works_amount', 'total_basic', 'final_salary',
            ], 0.0);
        }

        $basicSalary           = (float) $profile->basic_salary;
        $fuelAllowance         = (float) $profile->fuel_allowance;
        $attendanceIncentive   = (float) $profile->attendance_incentive;
        $collectionPct         = (float) $profile->collection_incentive_pct;

        // Attendance incentive: paid in full only if employee was present all working days
        $attendanceIncentivePaid = ($scoreData['days_present'] >= $scoreData['working_days'])
            ? $attendanceIncentive
            : 0.0;

        // Collection incentive: % of basic scaled by performance
        $collectionIncentiveAmt = round(
            $basicSalary * ($collectionPct / 100) * ($scoreData['performance_percentage'] / 100),
            2
        );

        $otherWorksAmount = round($scoreData['total_earned_points'], 2);

        $totalBasic  = round($basicSalary + $fuelAllowance + $attendanceIncentivePaid + $collectionIncentiveAmt, 2);
        $finalSalary = round($totalBasic + $otherWorksAmount, 2);

        return [
            'basic_salary'             => $basicSalary,
            'fuel_allowance'           => $fuelAllowance,
            'attendance_incentive'     => $attendanceIncentivePaid,
            'collection_incentive_pct' => $collectionPct,
            'collection_incentive_amt' => $collectionIncentiveAmt,
            'other_works_amount'       => $otherWorksAmount,
            'total_basic'              => $totalBasic,
            'final_salary'             => $finalSalary,
        ];
    }

    // -----------------------------------------------------------------------
    // Generate Monthly Report
    // -----------------------------------------------------------------------

    /**
     * Create or refresh the monthly performance report for an employee.
     */
    public function generateMonthlyReport(User $user, int $month, int $year, int $generatedBy): PerformanceMonthlyReport
    {
        $score  = $this->calculateMonthlyScore($user, $month, $year);
        $salary = $this->calculateSalary($user, $score);

        $data = array_merge($score, $salary, [
            'user_id'      => $user->id,
            'month'        => $month,
            'year'         => $year,
            'generated_at' => Carbon::now(),
            'generated_by' => $generatedBy,
        ]);

        return PerformanceMonthlyReport::updateOrCreate(
            ['user_id' => $user->id, 'month' => $month, 'year' => $year],
            $data
        );
    }

    // -----------------------------------------------------------------------
    // Save Daily Entry
    // -----------------------------------------------------------------------

    /**
     * Upsert a single daily task entry for an employee.
     * Points are auto-calculated based on template unitPoints.
     */
    public function saveDailyEntry(
        User $user,
        PerformanceTaskTemplate $template,
        string $date,
        bool $isDone,
        ?string $note = null
    ): PerformanceDailyEntry {
        $carbon = Carbon::parse($date);
        $pointsEarned = $isDone
            ? $template->unitPoints((int) $carbon->month, (int) $carbon->year)
            : 0.0;

        return PerformanceDailyEntry::updateOrCreate(
            [
                'user_id'     => $user->id,
                'template_id' => $template->id,
                'date'        => $date,
            ],
            [
                'is_done'       => $isDone,
                'points_earned' => round($pointsEarned, 2),
                'note'          => $note,
            ]
        );
    }

    // -----------------------------------------------------------------------
    // Monthly Grid Sheet (Worker Daily Working Sheet Structure)
    // -----------------------------------------------------------------------

    /**
     * Generates day-by-day performance matrix matching the worker daily working sheet.
     */
    public function getMonthlyGridSheet(User $user, int $month, int $year): array
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $todayStr    = now()->toDateString();

        $templates = PerformanceTaskTemplate::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['reportType', 'linkedTask'])
            ->orderBy('sort_order')
            ->get();

        // Preload daily entries for this month
        $entries = PerformanceDailyEntry::where('user_id', $user->id)
            ->forMonth($month, $year)
            ->get()
            ->groupBy('template_id');

        // Preload attendance for this month
        $attendances = EmployeeAttendance::where('user_id', $user->id)
            ->forMonth($month, $year)
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->date)->day);

        // Preload inspection reports by this user for the month
        $reports = InspectionReport::where('reported_by', $user->id)
            ->whereYear('report_date', $year)
            ->whereMonth('report_date', $month)
            ->get()
            ->groupBy('report_type_id');

        // Flat inspections by this user
        $flatReports = FlatInspectionReport::where('inspected_by', $user->id)
            ->whereYear('inspected_at', $year)
            ->whereMonth('inspected_at', $month)
            ->get();

        // Generate day columns metadata
        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $carbon = Carbon::createFromDate($year, $month, $d);
            $days[$d] = [
                'day'              => $d,
                'date'             => $carbon->toDateString(),
                'day_name'         => $carbon->format('D'), // Sat, Sun, Mon...
                'label'            => $carbon->format('j-M'), // 1-Aug...
                'is_friday'        => $carbon->isFriday(),
                'is_sunday'        => $carbon->isSunday(),
                'is_past_or_today' => $carbon->toDateString() <= $todayStr,
            ];
        }

        $rows = [];
        $totalMonthlyMax = 0;
        $totalMonthlyEarned = 0;
        $dailyTotals = array_fill_keys(range(1, $daysInMonth), 0.0);

        foreach ($templates as $template) {
            $totalMonthlyMax += (float) $template->monthly_points;
            $unitAmount = $template->unitPoints($month, $year);
            $templateEntries = $entries->get($template->id, collect())->keyBy(fn($e) => Carbon::parse($e->date)->day);

            $daysData = [];
            $templateEarned = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dayMeta       = $days[$d];
                $dateStr       = $dayMeta['date'];
                $isPastOrToday = $dayMeta['is_past_or_today'];

                $entry  = $templateEntries->get($d);
                $status = 'undone'; // 'done', 'undone', 'unsatisfied', 'na'
                $earned = 0.0;
                $rating = null;
                $note   = null;

                if ($entry) {
                    if ($entry->is_done) {
                        $status = 'done';
                        $earned = (float) $entry->points_earned;
                    } else {
                        $status = 'undone';
                        $earned = 0.0;
                    }
                    $note = $entry->note;
                }

                // Check dynamic reports automatic sync & admin rating
                if ($template->type === 'dynamic_report') {
                    if ($template->report_type_id == 1) { // Flat Inspection
                        $dayFlat = $flatReports->filter(fn($r) => Carbon::parse($r->inspected_at)->day === $d)->first();
                        if ($dayFlat) {
                            $rating = $dayFlat->admin_rating;
                            if ($rating === 'bad') {
                                $status = 'unsatisfied';
                                $earned = 0.0;
                            } else {
                                $status = 'done';
                                $earned = $unitAmount;
                            }
                        }
                    } elseif ($template->report_type_id) {
                        $typeReports = $reports->get($template->report_type_id, collect());
                        $dayReport = $typeReports->filter(fn($r) => Carbon::parse($r->report_date)->day === $d)->first();
                        if ($dayReport) {
                            $rating = $dayReport->admin_rating;
                            if ($rating === 'bad') {
                                $status = 'unsatisfied';
                                $earned = 0.0;
                            } else {
                                $status = 'done';
                                $earned = $unitAmount;
                            }
                        }
                    }
                }

                // Check attendance (if template is punctuality / attendance)
                if (stripos($template->name, 'attendance') !== false || stripos($template->name, 'punctuality') !== false) {
                    $att = $attendances->get($d);
                    if ($att) {
                        if ($att->status === 'present') {
                            $status = 'done';
                            $earned = $unitAmount;
                        } elseif ($att->status === 'half_day') {
                            $status = 'done';
                            $earned = round($unitAmount / 2, 2);
                        } else {
                            $status = 'undone';
                            $earned = 0.0;
                        }
                    }
                }

                // Non-daily task handling: if not daily, earned occurs when completed
                if (! $template->is_daily && ! $entry && ! $rating) {
                    if (! $isPastOrToday) {
                        $status = 'na';
                    }
                }

                if (! $isPastOrToday && $status === 'undone') {
                    $status = 'na';
                }

                $templateEarned   += $earned;
                $dailyTotals[$d] += $earned;

                $daysData[$d] = [
                    'status'       => $status,
                    'earned'       => round($earned, 2),
                    'admin_rating' => $rating,
                    'unit_amount'  => $unitAmount,
                    'note'         => $note,
                ];
            }

            $totalMonthlyEarned += $templateEarned;
            $achievementPct = $template->monthly_points > 0
                ? min(100, round(($templateEarned / (float) $template->monthly_points) * 100, 1))
                : 0.0;

            $rows[] = [
                'template'        => $template,
                'type'            => $template->type,
                'name'            => $template->name,
                'monthly_amount'  => (float) $template->monthly_points,
                'unit_amount'     => $unitAmount,
                'is_daily'        => (bool) $template->is_daily,
                'target_count'    => (int) ($template->target_count ?? 1),
                'days_data'       => $daysData,
                'total_earned'    => round($templateEarned, 2),
                'total_deducted'  => round(max(0, (float) $template->monthly_points - $templateEarned), 2),
                'achievement_pct' => $achievementPct,
            ];
        }

        $performancePct = $totalMonthlyMax > 0
            ? min(100, round(($totalMonthlyEarned / $totalMonthlyMax) * 100, 2))
            : 0.0;

        $daysPresent = $attendances->where('status', 'present')->count();
        $daysAbsent  = $attendances->where('status', 'absent')->count();

        $summary = [
            'total_monthly_max'      => round($totalMonthlyMax, 2),
            'total_earned'           => round($totalMonthlyEarned, 2),
            'total_deducted'         => round(max(0, $totalMonthlyMax - $totalMonthlyEarned), 2),
            'performance_percentage' => $performancePct,
            'grade'                  => $this->getGrade($performancePct),
            'days_present'           => $daysPresent,
            'days_absent'            => $daysAbsent,
            'working_days'           => $daysInMonth,
            'final_salary'           => round($totalMonthlyEarned, 2),
        ];

        return [
            'daysInMonth' => $daysInMonth,
            'days'        => $days,
            'rows'        => $rows,
            'dailyTotals' => $dailyTotals,
            'summary'     => $summary,
        ];
    }

    // -----------------------------------------------------------------------
    // Task Summary for a Month (per-template breakdown)
    // -----------------------------------------------------------------------

    /**
     * Returns per-task breakdown for use in report view.
     *
     * @return array<array{template: PerformanceTaskTemplate, days_done: int, points_earned: float}>
     */
    public function getMonthlyTaskBreakdown(User $user, int $month, int $year): array
    {
        $sheet = $this->getMonthlyGridSheet($user, $month, $year);

        $result = [];
        foreach ($sheet['rows'] as $row) {
            $daysDone = collect($row['days_data'])->where('status', 'done')->count();
            $result[] = [
                'template'      => $row['template'],
                'days_done'     => $daysDone,
                'points_earned' => $row['total_earned'],
            ];
        }

        return $result;
    }
}
