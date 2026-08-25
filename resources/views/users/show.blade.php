@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="User Profile: {{ $user->name }}" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Main Profile Card --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl {{ $user->is_employee ? 'bg-indigo-600 text-white shadow-md' : 'bg-brand-500 text-white' }} text-2xl font-black">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                        @if($user->is_employee)
                            <span class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-black text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                👔 Employee
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-md bg-gray-100 px-2 py-0.5 text-xs font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                👤 System User
                            </span>
                        @endif

                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-bold {{ $user->is_active ? 'bg-green-50 text-green-700 border border-green-200 dark:bg-green-950/40 dark:text-green-400' : 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-950/40 dark:text-red-400' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <p class="text-xs font-mono text-gray-500 dark:text-gray-400 mt-1">{{ $user->email }}</p>

                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @forelse($user->roles as $role)
                            <span class="text-[10px] uppercase font-bold rounded-full px-2.5 py-0.5 {{ $role->name === 'super-admin' ? 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/15 dark:text-red-500' : 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/15 dark:text-blue-400' }}">
                                {{ $role->display_name }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-400 italic">No roles assigned</span>
                        @endforelse

                        @if($user->is_employee && $profile)
                            @if($profile->employee_code)
                                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-mono font-bold text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400 border border-indigo-100">
                                    Code: {{ $profile->employee_code }}
                                </span>
                            @endif
                            @if($profile->designation)
                                <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $profile->designation }}
                                </span>
                            @endif
                            @if($profile->department)
                                <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $profile->department }}
                                </span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @can('users.edit')
                    <a href="{{ route('users.edit', $user) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 px-4 py-2 text-xs font-extrabold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                        ✏️ Edit User
                    </a>
                @endcan

                @if($user->is_employee && auth()->user()->isSuperAdmin())
                    <a href="{{ route('users.tasks', $user) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-extrabold text-white hover:bg-indigo-700 transition-colors shadow-xs">
                        📋 Manage Task Templates
                    </a>
                @endif

                <a href="{{ route('users.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400 transition-colors">
                    ← Back to Users
                </a>
            </div>
        </div>

        {{-- If Employee: Salary Breakdown Card --}}
        @if($user->is_employee && $profile)
            <div class="mt-6 border-t border-gray-100 dark:border-gray-800 pt-5">
                <h4 class="text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider mb-3">
                    💼 Employment & Compensation Details
                </h4>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                    @php
                        $salaryItems = [
                            ['label' => 'Basic Salary', 'value' => 'Rs. ' . number_format($profile->basic_salary, 0)],
                            ['label' => 'Fuel Allowance', 'value' => 'Rs. ' . number_format($profile->fuel_allowance, 0)],
                            ['label' => 'Attendance Incentive', 'value' => 'Rs. ' . number_format($profile->attendance_incentive, 0)],
                            ['label' => 'Collection Incentive', 'value' => $profile->collection_incentive_pct . '%'],
                            ['label' => 'Joining Date', 'value' => $profile->joined_at ? $profile->joined_at->format('d M Y') : '—'],
                        ];
                    @endphp
                    @foreach($salaryItems as $item)
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-gray-800 dark:bg-white/[0.02]">
                            <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400">{{ $item['label'] }}</p>
                            <p class="mt-1 text-sm font-black text-gray-900 dark:text-white font-mono">{{ $item['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($user->is_employee && $currentScore)
        {{-- Current Month Live Stats --}}
        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            @php
                $grade       = $currentScore['grade'];
                $gradeColors = ['Excellent' => 'green', 'Good' => 'blue', 'Average' => 'amber', 'Poor' => 'red'];
                $gc          = $gradeColors[$grade] ?? 'gray';
            @endphp
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                <p class="text-xs text-gray-500 dark:text-gray-400">Current Month Points</p>
                <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white font-mono">{{ number_format($currentScore['total_earned_points'], 0) }}</p>
                <p class="text-xs text-gray-400">of {{ number_format($currentScore['total_max_points'], 0) }} max</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                <p class="text-xs text-gray-500 dark:text-gray-400">Performance</p>
                <p class="mt-1 text-2xl font-black text-{{ $gc }}-600 dark:text-{{ $gc }}-400 font-mono">{{ $currentScore['performance_percentage'] }}%</p>
                <p class="text-xs text-gray-400">{{ now()->format('F Y') }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                <p class="text-xs text-gray-500 dark:text-gray-400">Performance Grade</p>
                <p class="mt-1 text-2xl font-black text-{{ $gc }}-600 dark:text-{{ $gc }}-400">{{ $grade }}</p>
                <p class="text-xs text-gray-400">Current month live</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                <p class="text-xs text-gray-500 dark:text-gray-400">Attendance</p>
                <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white font-mono">{{ $currentScore['days_present'] }}<span class="text-base text-gray-400">/{{ $currentScore['working_days'] }}</span></p>
                <p class="text-xs text-gray-400">Days present</p>
            </div>
        </div>

        {{-- Monthly Reports & History --}}
        <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 mb-4 border-b border-gray-100 dark:border-gray-800 gap-3">
                <div>
                    <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Monthly Performance Reports</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Archived performance scores and calculated salaries.</p>
                </div>

                {{-- Generate Report Form --}}
                @if(auth()->user()->hasPermission('performance.reports.view') || auth()->user()->isSuperAdmin())
                    <form action="{{ route('performance.report.generate', $user) }}" method="POST" class="flex flex-wrap items-center gap-2">
                        @csrf
                        <select name="month" class="rounded-xl border border-gray-300 px-3 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-brand-500/20">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($m == $currentMonth)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endforeach
                        </select>
                        <select name="year" class="rounded-xl border border-gray-300 px-3 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-brand-500/20">
                            @foreach(range(now()->year, now()->year - 3, -1) as $y)
                                <option value="{{ $y }}" @selected($y == $currentYear)>{{ $y }}</option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-brand-700 shadow-xs cursor-pointer">
                            ⚡ Generate Report
                        </button>
                    </form>
                @endif
            </div>

            @if($reports->isEmpty())
                <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-8 text-center text-xs text-gray-400 dark:border-gray-800 dark:bg-white/[0.02]">
                    No monthly reports generated yet for this employee.
                </div>
            @else
                <div class="overflow-x-auto border border-gray-200 rounded-xl dark:border-gray-800">
                    <table class="w-full text-xs text-left text-gray-600 dark:text-gray-400">
                        <thead class="uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400 text-[11px]">
                            <tr>
                                <th class="px-4 py-3">Month</th>
                                <th class="px-4 py-3">Points</th>
                                <th class="px-4 py-3">Performance</th>
                                <th class="px-4 py-3">Grade</th>
                                <th class="px-4 py-3">Attendance</th>
                                <th class="px-4 py-3">Final Salary</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($reports as $report)
                                @php $rc = ['Excellent'=>'green','Good'=>'blue','Average'=>'amber','Poor'=>'red'][$report->grade] ?? 'gray'; @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">{{ $report->month_name }}</td>
                                    <td class="px-4 py-3 font-mono">
                                        {{ number_format($report->total_earned_points, 0) }}
                                        <span class="text-gray-400">/{{ number_format($report->total_max_points, 0) }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 h-1.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                                <div class="h-full rounded-full bg-{{ $rc }}-500" style="width: {{ $report->performance_percentage }}%"></div>
                                            </div>
                                            <span class="text-{{ $rc }}-600 dark:text-{{ $rc }}-400 font-bold font-mono">{{ $report->performance_percentage }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold bg-{{ $rc }}-100 text-{{ $rc }}-700 dark:bg-{{ $rc }}-900/30 dark:text-{{ $rc }}-400">
                                            {{ $report->grade }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $report->days_present }}/{{ $report->working_days }} days</td>
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white font-mono">
                                        Rs. {{ number_format($report->final_salary, 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('performance.report', [$user, $report->year, $report->month]) }}"
                                                class="rounded-lg px-2.5 py-1 text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800 transition-colors">
                                                View
                                            </a>
                                            <a href="{{ route('performance.report.pdf', [$user, $report->year, $report->month]) }}"
                                                class="rounded-lg px-2.5 py-1 text-xs font-bold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800 transition-colors">
                                                PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Task Templates Summary --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-4 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>🎯 Assigned Daily Task Templates & Performance</span>
                        <span class="rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 text-[11px] font-black px-2.5 py-0.5">
                            {{ $templates->count() }} Tasks
                        </span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Configured dynamic reports, linked tasks, and custom templates for employee daily performance scoring.</p>
                </div>
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('users.tasks', $user) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-brand-500 px-4 py-2 text-xs font-black text-white hover:bg-brand-600 transition-colors shadow-xs">
                        ✏️ Configure Templates & Amounts
                    </a>
                @endif
            </div>

            @if($templates->isEmpty())
                <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-8 text-center text-xs text-gray-400 dark:border-gray-800 dark:bg-white/[0.02]">
                    No task templates assigned yet.
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('users.tasks', $user) }}" class="text-brand-600 dark:text-brand-400 font-bold hover:underline ml-1">Configure tasks and performance amounts now</a>.
                    @endif
                </div>
            @else
                <div class="overflow-x-auto border border-gray-200 rounded-xl dark:border-gray-800">
                    <table class="w-full text-xs text-left text-gray-600 dark:text-gray-400">
                        <thead class="uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400 text-[11px]">
                            <tr>
                                <th class="px-4 py-3 w-10">#</th>
                                <th class="px-4 py-3 w-40">Type</th>
                                <th class="px-4 py-3">Task Name / Source</th>
                                <th class="px-4 py-3 w-36">Monthly Points</th>
                                <th class="px-4 py-3 w-32">Daily Points</th>
                                <th class="px-4 py-3 w-24">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($templates as $i => $t)
                                @php
                                    $dailyPts = round($t->monthly_points / cal_days_in_month(CAL_GREGORIAN, now()->month, now()->year), 1);
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-4 py-3 text-gray-400 font-mono">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3">
                                        @if($t->type === 'dynamic_report')
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 font-black text-[10px] px-2 py-0.5 border border-emerald-200 dark:border-emerald-800">
                                                📋 Dynamic Report
                                            </span>
                                        @elseif($t->type === 'task')
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-300 font-black text-[10px] px-2 py-0.5 border border-indigo-200 dark:border-indigo-800">
                                                📌 Assigned Task
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-black text-[10px] px-2 py-0.5 border border-gray-200 dark:border-gray-700">
                                                ✍️ Custom Task
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                        {{ $t->name }}
                                        @if($t->type === 'dynamic_report' && $t->reportType)
                                            <span class="text-xs text-gray-400 font-normal block mt-0.5">Linked: {{ $t->reportType->name }}</span>
                                        @elseif($t->type === 'task' && $t->linkedTask)
                                            <span class="text-xs text-gray-400 font-normal block mt-0.5">Task #{{ $t->linkedTask->id }}: {{ $t->linkedTask->title }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-mono font-bold text-brand-600 dark:text-brand-400">{{ number_format($t->monthly_points, 0) }} pts</td>
                                    <td class="px-4 py-3 font-mono text-gray-500 dark:text-gray-400">~{{ $dailyPts }} pts/day</td>
                                    <td class="px-4 py-3">
                                        @if($t->is_active)
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-bold text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-500 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 dark:bg-gray-800/50 font-bold border-t border-gray-200 dark:border-gray-700">
                                <td colspan="3" class="px-4 py-3 text-xs font-extrabold text-gray-700 dark:text-gray-300 uppercase">Total Monthly Max Points</td>
                                <td class="px-4 py-3 font-black text-gray-900 dark:text-white font-mono text-sm">{{ number_format($templates->where('is_active', true)->sum('monthly_points'), 0) }} pts</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    @endif
@endsection
