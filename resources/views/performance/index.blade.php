@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Performance Reports" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Month / Year Filter --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="GET" action="{{ route('performance.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Month</label>
                <select name="month"
                    class="rounded-xl border border-gray-300 px-3.5 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" @selected($m == $month)>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Year</label>
                <select name="year"
                    class="rounded-xl border border-gray-300 px-3.5 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30">
                    @foreach(range(now()->year, now()->year - 3, -1) as $y)
                        <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="rounded-xl bg-brand-500 px-5 py-2 text-sm font-bold text-white hover:bg-brand-600 transition-colors cursor-pointer">
                Filter
            </button>
            @if(request()->hasAny(['month', 'year']))
                <a href="{{ route('performance.index') }}"
                   class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                    Reset
                </a>
            @endif

            <span class="ml-auto text-xs text-gray-400 dark:text-gray-500 self-end pb-0.5">
                Showing: {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}
            </span>
        </form>
    </div>

    {{-- Employees Table --}}
    <x-common.component-card
        title="Employee Performance Overview"
        desc="Monthly summary for all active employees">

        @if($employees->isEmpty())
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-10 text-center text-sm text-gray-400 dark:border-gray-800 dark:bg-white/[0.02]">
                No active employees found.
                @if(auth()->user()->hasPermission('users.create') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('users.create') }}" class="text-brand-500 hover:underline ml-1">Create an employee user</a>.
                @endif
            </div>
        @else
            <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Employee</th>
                            <th class="px-4 py-3">Designation</th>
                            <th class="px-4 py-3">Performance</th>
                            <th class="px-4 py-3">Grade</th>
                            <th class="px-4 py-3">Attendance</th>
                            <th class="px-4 py-3">Final Salary</th>
                            <th class="px-4 py-3">Report</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($employees as $employee)
                            @php
                                $profile = $employee->employeeProfile;
                                $report  = $employee->performanceMonthlyReports->first();
                                $gc      = $report
                                    ? (['Excellent'=>'green','Good'=>'blue','Average'=>'amber','Poor'=>'red'][$report->grade] ?? 'gray')
                                    : 'gray';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                {{-- Employee --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 text-sm font-bold shrink-0">
                                            {{ strtoupper(substr($employee->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white">{{ $employee->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $employee->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Designation --}}
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                    {{ $profile->designation ?? '—' }}
                                </td>

                                {{-- Performance --}}
                                <td class="px-4 py-3">
                                    @if($report)
                                        <div class="flex items-center gap-2">
                                            <div class="w-20 h-1.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                                <div class="h-full rounded-full bg-{{ $gc }}-500"
                                                     style="width: {{ $report->performance_percentage }}%"></div>
                                            </div>
                                            <span class="text-{{ $gc }}-600 dark:text-{{ $gc }}-400 font-semibold text-xs">
                                                {{ $report->performance_percentage }}%
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">Not generated</span>
                                    @endif
                                </td>

                                {{-- Grade --}}
                                <td class="px-4 py-3">
                                    @if($report)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            bg-{{ $gc }}-100 text-{{ $gc }}-700 dark:bg-{{ $gc }}-900/30 dark:text-{{ $gc }}-400">
                                            {{ $report->grade }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Attendance --}}
                                <td class="px-4 py-3">
                                    @if($report)
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $report->days_present }}</span>
                                        <span class="text-gray-400">/{{ $report->working_days }} days</span>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Final Salary --}}
                                <td class="px-4 py-3">
                                    @if($report)
                                        <span class="font-bold text-gray-900 dark:text-white">
                                            Rs. {{ number_format($report->final_salary, 0) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600 text-xs">—</span>
                                    @endif
                                </td>

                                {{-- Report Status --}}
                                <td class="px-4 py-3">
                                    @if($report)
                                        <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Generated
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs text-amber-500 dark:text-amber-400">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                            </svg>
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Generate / Regenerate --}}
                                        <form action="{{ route('performance.report.generate', $employee) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="month" value="{{ $month }}">
                                            <input type="hidden" name="year" value="{{ $year }}">
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold border transition-colors
                                                    {{ $report
                                                        ? 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100 dark:bg-white/5 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-white/10'
                                                        : 'bg-brand-50 text-brand-700 border-brand-200 hover:bg-brand-100 dark:bg-brand-900/30 dark:text-brand-300 dark:border-brand-800' }}">
                                                {{ $report ? 'Refresh' : 'Generate' }}
                                            </button>
                                        </form>

                                        @if($report)
                                            <a href="{{ route('performance.report', [$employee, $year, $month]) }}"
                                               class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800 transition-colors">
                                                View
                                            </a>
                                            <a href="{{ route('performance.report.pdf', [$employee, $year, $month]) }}"
                                               class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800 transition-colors">
                                                PDF
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </x-common.component-card>
@endsection
