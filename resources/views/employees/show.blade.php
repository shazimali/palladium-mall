@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ $employee->name }}" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Profile Header --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 text-2xl font-bold">
                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $employee->name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $employee->email }}</p>
                    <div class="mt-1 flex flex-wrap gap-2">
                        @if($profile->designation)
                            <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $profile->designation }}</span>
                        @endif
                        @if($profile->department)
                            <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $profile->department }}</span>
                        @endif
                        @if($profile->employee_code)
                            <span class="inline-flex items-center rounded-md bg-brand-50 px-2 py-0.5 text-xs font-mono text-brand-600 dark:bg-brand-900/20 dark:text-brand-400">{{ $profile->employee_code }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(auth()->user()->hasPermission('employees.manage') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('employees.edit', $employee) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                        Edit Profile
                    </a>
                @endif
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('employees.tasks', $employee) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-purple-500 px-4 py-2 text-sm font-medium text-white hover:bg-purple-600 transition-colors">
                        Manage Tasks
                    </a>
                @endif
            </div>
        </div>

        {{-- Salary Summary --}}
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-5">
            @php
                $salaryItems = [
                    ['label' => 'Basic Salary', 'value' => 'Rs. ' . number_format($profile->basic_salary, 0), 'color' => 'blue'],
                    ['label' => 'Fuel', 'value' => 'Rs. ' . number_format($profile->fuel_allowance, 0), 'color' => 'amber'],
                    ['label' => 'Attendance Incentive', 'value' => 'Rs. ' . number_format($profile->attendance_incentive, 0), 'color' => 'green'],
                    ['label' => 'Collection Incentive', 'value' => $profile->collection_incentive_pct . '%', 'color' => 'purple'],
                    ['label' => 'Joined', 'value' => $profile->joined_at ? $profile->joined_at->format('d M Y') : '—', 'color' => 'gray'],
                ];
            @endphp
            @foreach($salaryItems as $item)
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['label'] }}</p>
                    <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Current Month Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        @php
            $grade       = $currentScore['grade'];
            $gradeColors = ['Excellent' => 'green', 'Good' => 'blue', 'Average' => 'amber', 'Poor' => 'red'];
            $gc          = $gradeColors[$grade] ?? 'gray';
        @endphp
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
            <p class="text-xs text-gray-500 dark:text-gray-400">This Month Points</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($currentScore['total_earned_points'], 0) }}</p>
            <p class="text-xs text-gray-400">of {{ number_format($currentScore['total_max_points'], 0) }} max</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
            <p class="text-xs text-gray-500 dark:text-gray-400">Performance</p>
            <p class="mt-1 text-2xl font-bold text-{{ $gc }}-600 dark:text-{{ $gc }}-400">{{ $currentScore['performance_percentage'] }}%</p>
            <p class="text-xs text-gray-400">{{ now()->format('F Y') }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
            <p class="text-xs text-gray-500 dark:text-gray-400">Grade</p>
            <p class="mt-1 text-2xl font-bold text-{{ $gc }}-600 dark:text-{{ $gc }}-400">{{ $grade }}</p>
            <p class="text-xs text-gray-400">Current month</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
            <p class="text-xs text-gray-500 dark:text-gray-400">Attendance</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $currentScore['days_present'] }}<span class="text-base text-gray-400">/{{ $currentScore['working_days'] }}</span></p>
            <p class="text-xs text-gray-400">Days present</p>
        </div>
    </div>

    {{-- Generate Report + Monthly Report History --}}
    <x-common.component-card title="Monthly Reports" desc="Performance reports history with salary breakdown">

        {{-- Generate Form --}}
        @if(auth()->user()->hasPermission('performance.reports.view') || auth()->user()->isSuperAdmin())
            <div class="mb-5 flex flex-wrap items-end gap-3 p-4 rounded-xl border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                <form action="{{ route('performance.report.generate', $employee) }}" method="POST" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Month</label>
                        <select name="month" class="rounded-xl border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($m == $currentMonth)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Year</label>
                        <select name="year" class="rounded-xl border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30">
                            @foreach(range(now()->year, now()->year - 3, -1) as $y)
                                <option value="{{ $y }}" @selected($y == $currentYear)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-bold text-white hover:bg-brand-600 transition-colors shadow-sm cursor-pointer">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                        </svg>
                        Generate Report
                    </button>
                </form>
            </div>
        @endif

        {{-- Reports Table --}}
        @if($reports->isEmpty())
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-8 text-center text-sm text-gray-400 dark:border-gray-800 dark:bg-white/[0.02]">
                No monthly reports generated yet.
            </div>
        @else
            <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
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
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $report->month_name }}</td>
                                <td class="px-4 py-3">
                                    {{ number_format($report->total_earned_points, 0) }}
                                    <span class="text-gray-400">/{{ number_format($report->total_max_points, 0) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-20 h-1.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                            <div class="h-full rounded-full bg-{{ $rc }}-500" style="width: {{ $report->performance_percentage }}%"></div>
                                        </div>
                                        <span class="text-{{ $rc }}-600 dark:text-{{ $rc }}-400 font-medium">{{ $report->performance_percentage }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-{{ $rc }}-100 text-{{ $rc }}-700 dark:bg-{{ $rc }}-900/30 dark:text-{{ $rc }}-400">
                                        {{ $report->grade }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $report->days_present }}/{{ $report->working_days }} days</td>
                                <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                    Rs. {{ number_format($report->final_salary, 0) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('performance.report', [$employee, $report->year, $report->month]) }}"
                                           class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800 transition-colors">
                                            View
                                        </a>
                                        <a href="{{ route('performance.report.pdf', [$employee, $report->year, $report->month]) }}"
                                           class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800 transition-colors">
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

    </x-common.component-card>

    {{-- Task Templates Summary --}}
    <div class="mt-6">
        <x-common.component-card title="Assigned Task Templates" desc="Tasks used to calculate this employee's daily performance score">
            @if($templates->isEmpty())
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-8 text-center text-sm text-gray-400 dark:border-gray-800 dark:bg-white/[0.02]">
                    No task templates assigned.
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('employees.tasks', $employee) }}" class="text-brand-500 hover:underline ml-1">Assign tasks now</a>.
                    @endif
                </div>
            @else
                <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Task</th>
                                <th class="px-4 py-3">Monthly Points</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($templates as $i => $t)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $t->name }}</td>
                                    <td class="px-4 py-3 font-mono">{{ number_format($t->monthly_points, 0) }} pts</td>
                                    <td class="px-4 py-3">
                                        @if($t->is_active)
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 dark:bg-gray-800/50">
                                <td colspan="2" class="px-4 py-2 text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Total Max Points / Month</td>
                                <td class="px-4 py-2 font-bold text-gray-900 dark:text-white font-mono">{{ number_format($templates->where('is_active', true)->sum('monthly_points'), 0) }} pts</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </x-common.component-card>
    </div>
@endsection
