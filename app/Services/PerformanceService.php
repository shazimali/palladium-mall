<?php

namespace App\Services;

use App\Models\EmployeeAttendance;
use App\Models\PerformanceDailyEntry;
use App\Models\PerformanceMonthlyReport;
use App\Models\PerformanceTaskTemplate;
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
     * Daily point value for a task in the given month/year.
     */
    public function getDailyPoints(PerformanceTaskTemplate $template, int $month, int $year): float
    {
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        return (float) $template->monthly_points / $days;
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
        $workingDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Active task templates for this employee
        $templates = PerformanceTaskTemplate::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $totalMaxPoints = $templates->sum('monthly_points');

        // Sum all earned points from daily entries for this month
        $totalEarned = PerformanceDailyEntry::where('user_id', $user->id)
            ->forMonth($month, $year)
            ->where('is_done', true)
            ->sum('points_earned');

        $percentage = $totalMaxPoints > 0
            ? min(100, round(($totalEarned / $totalMaxPoints) * 100, 2))
            : 0.0;

        // Attendance stats
        $daysPresent = EmployeeAttendance::where('user_id', $user->id)
            ->forMonth($month, $year)
            ->present()
            ->count();

        $daysAbsent = EmployeeAttendance::where('user_id', $user->id)
            ->forMonth($month, $year)
            ->absent()
            ->count();

        return [
            'total_max_points'       => round($totalMaxPoints, 2),
            'total_earned_points'    => round($totalEarned, 2),
            'performance_percentage' => $percentage,
            'grade'                  => $this->getGrade($percentage),
            'working_days'           => $workingDays,
            'days_present'           => $daysPresent,
            'days_absent'            => $daysAbsent,
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
     * Points are auto-calculated based on template monthly_points ÷ days_in_month.
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
            ? $this->getDailyPoints($template, (int) $carbon->month, (int) $carbon->year)
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
    // Task Summary for a Month (per-template breakdown)
    // -----------------------------------------------------------------------

    /**
     * Returns per-task breakdown for use in report view.
     *
     * @return array<array{template: PerformanceTaskTemplate, days_done: int, points_earned: float}>
     */
    public function getMonthlyTaskBreakdown(User $user, int $month, int $year): array
    {
        $templates = PerformanceTaskTemplate::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $entries = PerformanceDailyEntry::where('user_id', $user->id)
            ->forMonth($month, $year)
            ->get()
            ->groupBy('template_id');

        $result = [];
        foreach ($templates as $template) {
            $templateEntries = $entries->get($template->id, collect());
            $daysDone        = $templateEntries->where('is_done', true)->count();
            $pointsEarned    = $templateEntries->sum('points_earned');

            $result[] = [
                'template'      => $template,
                'days_done'     => $daysDone,
                'points_earned' => round($pointsEarned, 2),
            ];
        }

        return $result;
    }
}
