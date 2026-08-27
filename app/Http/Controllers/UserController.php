<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\EmployeeProfile;
use App\Models\PerformanceTaskTemplate;
use App\Models\ReportType;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Services\PerformanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(protected PerformanceService $performance) {}

    public function index(Request $request): View
    {
        $query = User::with(['roles', 'employeeProfile'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('employeeProfile', function ($ep) use ($search) {
                        $ep->where('employee_code', 'like', "%{$search}%")
                            ->orWhere('designation', 'like', "%{$search}%")
                            ->orWhere('department', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        if ($request->filled('type')) {
            if ($request->type === 'employees') {
                $query->where('is_employee', true);
            } elseif ($request->type === 'users') {
                $query->where('is_employee', false);
            }
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::orderBy('display_name')->get();

        $counts = [
            'total'     => User::count(),
            'employees' => User::where('is_employee', true)->count(),
            'users'     => User::where('is_employee', false)->count(),
        ];

        return view('users.index', compact('users', 'roles', 'counts'));
    }

    public function create(): View
    {
        $roles = Role::orderBy('display_name')->get();
        return view('users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $isEmployee = $request->boolean('is_employee');

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'is_active'   => $request->boolean('is_active', true),
            'is_employee' => $isEmployee,
        ]);

        if ($request->filled('roles')) {
            $user->roles()->sync($request->roles);
        }

        if ($isEmployee) {
            EmployeeProfile::create([
                'user_id'                  => $user->id,
                'employee_code'            => $request->input('employee_code'),
                'designation'              => $request->input('designation'),
                'department'               => $request->input('department'),
                'joined_at'                => $request->input('joined_at'),
                'basic_salary'             => $request->input('basic_salary', 0) ?? 0,
                'fuel_allowance'           => $request->input('fuel_allowance', 0) ?? 0,
                'attendance_incentive'     => $request->input('attendance_incentive', 0) ?? 0,
                'collection_incentive_pct' => $request->input('collection_incentive_pct', 0) ?? 0,
                'is_active'                => $user->is_active,
                'created_by'               => auth()->id(),
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'employeeProfile']);

        $profile      = $user->employeeProfile;
        $reports      = collect();
        $templates    = collect();
        $currentScore = null;
        $currentMonth = now()->month;
        $currentYear  = now()->year;

        if ($user->isEmployee()) {
            $reports = $user->performanceMonthlyReports()
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->get();

            $templates = $user->performanceTaskTemplates()
                ->with(['reportType', 'linkedTask.category'])
                ->orderBy('sort_order')
                ->get();

            $currentScore = $this->performance->calculateMonthlyScore($user, $currentMonth, $currentYear);
        }

        return view('users.show', compact(
            'user',
            'profile',
            'reports',
            'templates',
            'currentScore',
            'currentMonth',
            'currentYear'
        ));
    }

    public function edit(User $user): View
    {
        $user->load('employeeProfile');
        $roles     = Role::orderBy('display_name')->get();
        $userRoles = $user->roles->pluck('id')->toArray();
        $profile   = $user->employeeProfile;

        return view('users.edit', compact('user', 'roles', 'userRoles', 'profile'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $isEmployee = $request->boolean('is_employee');

        $data = [
            'name'        => $request->name,
            'email'       => $request->email,
            'is_active'   => $request->boolean('is_active'),
            'is_employee' => $isEmployee,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->roles()->sync($request->roles ?? []);

        if ($isEmployee) {
            $profileData = [
                'employee_code'            => $request->input('employee_code'),
                'designation'              => $request->input('designation'),
                'department'               => $request->input('department'),
                'joined_at'                => $request->input('joined_at'),
                'basic_salary'             => $request->input('basic_salary', 0) ?? 0,
                'fuel_allowance'           => $request->input('fuel_allowance', 0) ?? 0,
                'attendance_incentive'     => $request->input('attendance_incentive', 0) ?? 0,
                'collection_incentive_pct' => $request->input('collection_incentive_pct', 0) ?? 0,
                'is_active'                => $user->is_active,
            ];

            if ($user->employeeProfile) {
                $user->employeeProfile->update($profileData);
            } else {
                $profileData['user_id']    = $user->id;
                $profileData['created_by'] = auth()->id();
                EmployeeProfile::create($profileData);
            }
        } elseif ($user->employeeProfile) {
            $user->employeeProfile->update(['is_active' => false]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);
        if ($user->employeeProfile) {
            $user->employeeProfile->update(['is_active' => $user->is_active]);
        }

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()->route('users.index')
            ->with('success', "User {$status} successfully.");
    }

    public function sendResetLink(User $user): RedirectResponse
    {
        $status = \Illuminate\Support\Facades\Password::broker()->sendResetLink(
            ['email' => $user->email]
        );

        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            return redirect()->route('users.index')
                ->with('success', 'Password reset link sent to ' . $user->name . ' successfully.');
        }

        return redirect()->route('users.index')
            ->with('error', 'Failed to send password reset link: ' . __($status));
    }

    // -----------------------------------------------------------------------
    // Employee Task Templates
    // -----------------------------------------------------------------------

    public function tasks(User $user): View
    {
        abort_unless($user->isEmployee(), 404);
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $templates = $user->performanceTaskTemplates()
            ->with(['reportType', 'linkedTask.category'])
            ->orderBy('sort_order')
            ->get();

        $reportTypes = ReportType::where('is_active', true)->orderBy('name')->get();
        $tasks = Task::with('category')->latest()->get();

        return view('users.tasks', compact('user', 'templates', 'reportTypes', 'tasks'));
    }

    public function storeTasks(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->isEmployee(), 404);
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'tasks'                  => 'required|array|min:1',
            'tasks.*.id'             => 'nullable|exists:performance_task_templates,id',
            'tasks.*.type'           => 'nullable|in:custom,dynamic_report,task',
            'tasks.*.name'           => 'required|string|max:255',
            'tasks.*.report_type_id' => 'nullable|exists:report_types,id',
            'tasks.*.task_id'        => 'nullable|exists:tasks,id',
            'tasks.*.monthly_points' => 'required|numeric|min:0',
            'tasks.*.is_daily'       => 'nullable|boolean',
            'tasks.*.target_count'   => 'nullable|integer|min:1|max:100',
            'tasks.*.sort_order'     => 'nullable|integer|min:0',
            'tasks.*.is_active'      => 'nullable|boolean',
        ]);

        foreach ($data['tasks'] as $i => $taskData) {
            $type = $taskData['type'] ?? 'custom';
            $reportTypeId = ($type === 'dynamic_report') ? ($taskData['report_type_id'] ?? null) : null;
            if ($type === 'dynamic_report' && ! $reportTypeId) {
                $matched = \App\Models\ReportType::where('name', 'LIKE', trim($taskData['name']))
                    ->orWhere('key', strtolower(str_replace(' ', '_', trim($taskData['name']))))
                    ->first();
                $reportTypeId = $matched?->id;
            }
            $taskId = ($type === 'task') ? ($taskData['task_id'] ?? null) : null;

            $attrs = [
                'user_id'        => $user->id,
                'name'           => $taskData['name'],
                'type'           => $type,
                'report_type_id' => $reportTypeId,
                'task_id'        => $taskId,
                'monthly_points' => $taskData['monthly_points'],
                'is_daily'       => isset($taskData['is_daily']) ? (bool) $taskData['is_daily'] : true,
                'target_count'   => !empty($taskData['target_count']) ? (int) $taskData['target_count'] : 1,
                'sort_order'     => $taskData['sort_order'] ?? $i,
                'is_active'      => isset($taskData['is_active']) ? (bool) $taskData['is_active'] : true,
                'created_by'     => auth()->id(),
            ];

            if (! empty($taskData['id'])) {
                PerformanceTaskTemplate::where('id', $taskData['id'])
                    ->where('user_id', $user->id)
                    ->update($attrs);
            } else {
                PerformanceTaskTemplate::create($attrs);
            }
        }

        return redirect()->route('users.tasks', $user)
            ->with('success', 'Task templates saved successfully.');
    }

    public function destroyTask(User $user, PerformanceTaskTemplate $template): RedirectResponse
    {
        abort_unless($user->isEmployee(), 404);
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        abort_unless($template->user_id === $user->id, 404);

        $template->delete();

        return back()->with('success', 'Task removed.');
    }
}
