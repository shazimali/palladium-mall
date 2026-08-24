<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAttendance;
use App\Models\EmployeeProfile;
use App\Models\PerformanceDailyEntry;
use App\Models\PerformanceMonthlyReport;
use App\Models\PerformanceTaskTemplate;
use App\Models\User;
use App\Services\PerformanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct(protected PerformanceService $performance) {}

    // -----------------------------------------------------------------------
    // Employee List
    // -----------------------------------------------------------------------

    public function index(Request $request): View
    {
        $search = $request->get('search');

        $employees = User::with('employeeProfile')
            ->whereHas('employeeProfile')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('employees.index', compact('employees', 'search'));
    }

    // -----------------------------------------------------------------------
    // Register Employee
    // -----------------------------------------------------------------------

    public function create(): View
    {
        // Only show users who are not already employees
        $users = User::whereDoesntHave('employeeProfile')
            ->orderBy('name')
            ->get();

        return view('employees.form', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id'                  => 'required|exists:users,id|unique:employee_profiles,user_id',
            'employee_code'            => 'nullable|string|max:50',
            'designation'              => 'nullable|string|max:100',
            'department'               => 'nullable|string|max:100',
            'joined_at'                => 'nullable|date',
            'basic_salary'             => 'required|numeric|min:0',
            'fuel_allowance'           => 'nullable|numeric|min:0',
            'attendance_incentive'     => 'nullable|numeric|min:0',
            'collection_incentive_pct' => 'nullable|numeric|min:0|max:100',
            'is_active'                => 'nullable|boolean',
        ]);

        $data['created_by'] = auth()->id();
        $data['is_active']  = $request->boolean('is_active', true);

        EmployeeProfile::create($data);

        return redirect()->route('employees.index')
            ->with('success', 'Employee registered successfully.');
    }

    // -----------------------------------------------------------------------
    // Employee Detail
    // -----------------------------------------------------------------------

    public function show(User $employee): View
    {
        abort_unless($employee->isEmployee(), 404);

        $profile  = $employee->employeeProfile;
        $reports  = $employee->performanceMonthlyReports()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        $templates = $employee->performanceTaskTemplates()
            ->orderBy('sort_order')
            ->get();

        $currentMonth = now()->month;
        $currentYear  = now()->year;

        // Current month quick stats (live, not from report)
        $currentScore = $this->performance->calculateMonthlyScore($employee, $currentMonth, $currentYear);

        return view('employees.show', compact(
            'employee', 'profile', 'reports', 'templates', 'currentScore', 'currentMonth', 'currentYear'
        ));
    }

    // -----------------------------------------------------------------------
    // Edit Employee Profile
    // -----------------------------------------------------------------------

    public function edit(User $employee): View
    {
        abort_unless($employee->isEmployee(), 404);
        $profile = $employee->employeeProfile;

        return view('employees.form', compact('employee', 'profile'));
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        abort_unless($employee->isEmployee(), 404);

        $data = $request->validate([
            'employee_code'            => 'nullable|string|max:50',
            'designation'              => 'nullable|string|max:100',
            'department'               => 'nullable|string|max:100',
            'joined_at'                => 'nullable|date',
            'basic_salary'             => 'required|numeric|min:0',
            'fuel_allowance'           => 'nullable|numeric|min:0',
            'attendance_incentive'     => 'nullable|numeric|min:0',
            'collection_incentive_pct' => 'nullable|numeric|min:0|max:100',
            'is_active'                => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $employee->employeeProfile->update($data);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee profile updated successfully.');
    }

    // -----------------------------------------------------------------------
    // Task Templates (Super Admin only)
    // -----------------------------------------------------------------------

    public function tasks(User $employee): View
    {
        abort_unless($employee->isEmployee(), 404);
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $templates = $employee->performanceTaskTemplates()
            ->orderBy('sort_order')
            ->get();

        return view('employees.tasks', compact('employee', 'templates'));
    }

    public function storeTasks(Request $request, User $employee): RedirectResponse
    {
        abort_unless($employee->isEmployee(), 404);
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'tasks'                  => 'required|array|min:1',
            'tasks.*.id'             => 'nullable|exists:performance_task_templates,id',
            'tasks.*.name'           => 'required|string|max:255',
            'tasks.*.monthly_points' => 'required|numeric|min:0',
            'tasks.*.sort_order'     => 'nullable|integer|min:0',
            'tasks.*.is_active'      => 'nullable|boolean',
        ]);

        foreach ($data['tasks'] as $i => $taskData) {
            $attrs = [
                'user_id'        => $employee->id,
                'name'           => $taskData['name'],
                'monthly_points' => $taskData['monthly_points'],
                'sort_order'     => $taskData['sort_order'] ?? $i,
                'is_active'      => isset($taskData['is_active']) ? (bool) $taskData['is_active'] : true,
                'created_by'     => auth()->id(),
            ];

            if (! empty($taskData['id'])) {
                PerformanceTaskTemplate::where('id', $taskData['id'])
                    ->where('user_id', $employee->id)
                    ->update($attrs);
            } else {
                PerformanceTaskTemplate::create($attrs);
            }
        }

        return redirect()->route('employees.tasks', $employee)
            ->with('success', 'Task templates saved successfully.');
    }

    public function destroyTask(User $employee, PerformanceTaskTemplate $template): RedirectResponse
    {
        abort_unless($employee->isEmployee(), 404);
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        abort_unless($template->user_id === $employee->id, 404);

        $template->delete();

        return back()->with('success', 'Task removed.');
    }
}
