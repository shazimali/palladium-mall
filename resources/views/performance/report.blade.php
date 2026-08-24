@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Performance Report — {{ $monthName }}" />

    @if(!$report)
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-800 dark:bg-amber-900/20">
            <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">
                No report has been generated for {{ $monthName }} yet.
                @if(auth()->user()->hasPermission('performance.reports.view') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('employees.show', $employee) }}" class="text-brand-500 hover:underline ml-1">Generate it from the employee page</a>.
                @endif
            </p>
        </div>
    @endif

    {{-- Actions Bar --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('employees.show', $employee) }}"
           class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
            ← Back to Employee
        </a>
        @if($report)
            <a href="{{ route('performance.report.pdf', [$employee, $year, $month]) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-4 py-2 text-sm font-bold text-white hover:bg-red-600 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Download PDF
            </a>
        @endif
    </div>

    {{-- Employee + Report Header --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 text-xl font-bold">
                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $employee->name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $profile->designation ?? '' }} {{ $profile->department ? '· ' . $profile->department : '' }}</p>
                    <p class="text-sm font-medium text-brand-600 dark:text-brand-400 mt-0.5">{{ $monthName }}</p>
                </div>
            </div>
            @if($report)
                @php $gc = ['Excellent'=>'green','Good'=>'blue','Average'=>'amber','Poor'=>'red'][$report->grade] ?? 'gray'; @endphp
                <div class="text-right">
                    <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-bold bg-{{ $gc }}-100 text-{{ $gc }}-700 dark:bg-{{ $gc }}-900/30 dark:text-{{ $gc }}-400">
                        {{ $report->grade }}
                    </span>
                    <p class="text-2xl font-bold text-{{ $gc }}-600 dark:text-{{ $gc }}-400 mt-1">{{ $report->performance_percentage }}%</p>
                    <p class="text-xs text-gray-400">Performance Score</p>
                </div>
            @endif
        </div>
    </div>

    @if($report)
        {{-- Stats Cards --}}
        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            @php $gc = ['Excellent'=>'green','Good'=>'blue','Average'=>'amber','Poor'=>'red'][$report->grade] ?? 'gray'; @endphp
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                <p class="text-xs text-gray-500 dark:text-gray-400">Points Earned</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($report->total_earned_points, 0) }}</p>
                <p class="text-xs text-gray-400">of {{ number_format($report->total_max_points, 0) }} max</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                <p class="text-xs text-gray-500 dark:text-gray-400">Days Present</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $report->days_present }}</p>
                <p class="text-xs text-gray-400">of {{ $report->working_days }} working days</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                <p class="text-xs text-gray-500 dark:text-gray-400">Days Absent</p>
                <p class="text-2xl font-bold text-red-500 dark:text-red-400">{{ $report->days_absent }}</p>
                <p class="text-xs text-gray-400">This month</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                <p class="text-xs text-gray-500 dark:text-gray-400">Final Salary</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">Rs. {{ number_format($report->final_salary, 0) }}</p>
                <p class="text-xs text-gray-400">Take Home</p>
            </div>
        </div>
    @endif

    {{-- Task Breakdown --}}
    <div class="mb-6">
        <x-common.component-card title="Task Performance Breakdown" desc="Daily task completion and points earned this month">
            <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Task</th>
                            <th class="px-4 py-3">Monthly Points</th>
                            <th class="px-4 py-3">Days Done</th>
                            <th class="px-4 py-3">Points Earned</th>
                            <th class="px-4 py-3">Achievement</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($taskBreakdown as $i => $row)
                            @php
                                $pct = $row['template']->monthly_points > 0
                                    ? round(($row['points_earned'] / $row['template']->monthly_points) * 100, 1)
                                    : 0;
                                $bc = $pct >= 90 ? 'green' : ($pct >= 75 ? 'blue' : ($pct >= 60 ? 'amber' : 'red'));
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $row['template']->name }}</td>
                                <td class="px-4 py-3 font-mono">{{ number_format($row['template']->monthly_points, 0) }}</td>
                                <td class="px-4 py-3">{{ $row['days_done'] }} days</td>
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ number_format($row['points_earned'], 1) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 h-1.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                            <div class="h-full rounded-full bg-{{ $bc }}-500" style="width: {{ min(100, $pct) }}%"></div>
                                        </div>
                                        <span class="text-xs text-{{ $bc }}-600 dark:text-{{ $bc }}-400 font-medium">{{ $pct }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 font-bold">
                            <td colspan="2" class="px-4 py-3 text-xs uppercase text-gray-500">Total</td>
                            <td class="px-4 py-3 font-mono text-gray-900 dark:text-white">{{ number_format(collect($taskBreakdown)->sum(fn($r) => $r['template']->monthly_points), 0) }}</td>
                            <td class="px-4 py-3">—</td>
                            <td class="px-4 py-3 font-mono text-gray-900 dark:text-white">{{ number_format(collect($taskBreakdown)->sum('points_earned'), 1) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-common.component-card>
    </div>

    @if($report)
        {{-- Salary Calculation --}}
        <div class="mb-6">
            <x-common.component-card title="Salary Calculation" desc="Monthly salary breakdown for {{ $monthName }}">
                <div class="max-w-sm mx-auto">
                    @php
                        $salaryRows = [
                            ['label' => 'Basic Salary', 'value' => $report->basic_salary, 'color' => 'gray'],
                            ['label' => 'Fuel Allowance', 'value' => $report->fuel_allowance, 'color' => 'gray'],
                            ['label' => 'Attendance Incentive', 'value' => $report->attendance_incentive, 'color' => 'gray'],
                            ['label' => "Collection Incentive ({$report->collection_incentive_pct}%)", 'value' => $report->collection_incentive_amt, 'color' => 'gray'],
                        ];
                    @endphp
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <table class="w-full text-sm">
                            <tbody>
                                @foreach($salaryRows as $row)
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $row['label'] }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rs. {{ number_format($row['value'], 0) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="border-b-2 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-white/[0.02]">
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">Total Basic</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">Rs. {{ number_format($report->total_basic, 0) }}</td>
                                </tr>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">Other Works (Task Points)</td>
                                    <td class="px-4 py-3 text-right font-medium text-brand-600 dark:text-brand-400">Rs. {{ number_format($report->other_works_amount, 0) }}</td>
                                </tr>
                                <tr class="bg-brand-50 dark:bg-brand-900/20">
                                    <td class="px-4 py-4 font-bold text-lg text-gray-900 dark:text-white">Final Salary</td>
                                    <td class="px-4 py-4 text-right font-bold text-xl text-brand-600 dark:text-brand-400">Rs. {{ number_format($report->final_salary, 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-center text-xs text-gray-400 mt-3">
                        Generated on {{ $report->generated_at?->format('d M Y, h:i A') ?? '—' }}
                        @if($report->generatedByUser)
                            by {{ $report->generatedByUser->name }}
                        @endif
                    </p>
                </div>
            </x-common.component-card>
        </div>
    @endif
@endsection
