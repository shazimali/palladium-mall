@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Worker Daily Working Sheet & Performance Report — {{ $monthName }}" />

    @if(!$report)
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-800 dark:bg-amber-900/20">
            <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">
                No report has been generated for {{ $monthName }} yet.
                @if(auth()->user()->hasPermission('performance.reports.view') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('users.show', $employee) }}" class="text-brand-500 hover:underline ml-1">Generate it from the user profile</a>.
                @endif
            </p>
        </div>
    @endif

    {{-- Actions Bar --}}
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('users.show', $employee) }}"
           class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
            ← Back to User Profile
        </a>
        <div class="flex items-center gap-3">
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('users.tasks', $employee) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-300 bg-indigo-50 dark:bg-indigo-950/40 dark:border-indigo-800 px-4 py-2 text-sm font-bold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 transition-colors shadow-2xs">
                    ⚙️ Configure Task Amounts & Templates
                </a>
            @endif
            @if($report)
                <a href="{{ route('performance.report.pdf', [$employee, $year, $month]) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-red-500 px-4 py-2 text-sm font-bold text-white hover:bg-red-600 transition-colors shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Download PDF Sheet
                </a>
            @endif
        </div>
    </div>

    {{-- Top Overview Card with Yellow Monthly Amount Banner (as in Google Sheet) --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 text-xl font-bold">
                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $employee->name }}</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $profile->designation ?? 'Employee' }} {{ $profile->department ? '· ' . $profile->department : '' }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center rounded-md bg-brand-50 px-2 py-0.5 text-xs font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                            🗓️ {{ $monthName }}
                        </span>
                        <span class="text-xs text-gray-400">Total Working Days: {{ $gridSheet['summary']['working_days'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Spreadsheet-Style Highlights: Yellow Banner for Max Amount --}}
            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-xl border-2 border-yellow-400 bg-yellow-300/90 dark:bg-yellow-500/20 px-5 py-3 text-center shadow-xs">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-yellow-900 dark:text-yellow-200">Total Monthly Amount</p>
                    <p class="text-2xl font-black text-gray-950 dark:text-yellow-100 font-mono">Rs. {{ number_format($gridSheet['summary']['total_monthly_max'], 0) }}</p>
                </div>
                <div class="rounded-xl border-2 border-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 px-5 py-3 text-center shadow-xs">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Total Earned (Payable)</p>
                    <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300 font-mono">Rs. {{ number_format($gridSheet['summary']['total_earned'], 0) }}</p>
                </div>
                <div class="rounded-xl border border-red-200 bg-red-50 dark:bg-red-950/30 px-4 py-3 text-center">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-red-600 dark:text-red-400">Deductions</p>
                    <p class="text-xl font-black text-red-600 dark:text-red-400 font-mono">- Rs. {{ number_format($gridSheet['summary']['total_deducted'], 0) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 dark:bg-gray-800 px-4 py-3 text-center">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Performance</p>
                    <p class="text-xl font-black text-brand-600 dark:text-brand-400">{{ $gridSheet['summary']['performance_percentage'] }}%</p>
                </div>
            </div>
        </div>
    </div>

    {{-- WORKER DAILY WORKING SHEET (Full Spreadsheet Matrix View) --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-3 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h3 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>📊 Worker Daily Working Sheet</span>
                    <span class="rounded-md bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300 text-[10px] font-black px-2 py-0.5">Matrix Grid</span>
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Daily task points breakdown. Satisfactory completed tasks earn full daily points; report not made or admin unsatisfactory remarks deduct the amount.
                </p>
            </div>
            {{-- Legend --}}
            <div class="flex items-center gap-2 text-[11px] flex-wrap">
                <span class="flex items-center gap-1.5 font-bold text-gray-600 dark:text-gray-300">
                    <span class="w-3.5 h-3.5 rounded bg-emerald-200 border border-emerald-400 inline-block"></span> Satisfied / Earned
                </span>
                <span class="flex items-center gap-1.5 font-bold text-gray-600 dark:text-gray-300">
                    <span class="w-3.5 h-3.5 rounded bg-red-200 border border-red-400 inline-block"></span> Admin Unsatisfied (Deducted)
                </span>
                <span class="flex items-center gap-1.5 font-bold text-gray-600 dark:text-gray-300">
                    <span class="w-3.5 h-3.5 rounded bg-gray-100 border border-gray-300 inline-block"></span> Not Done / Missed
                </span>
                <span class="flex items-center gap-1.5 font-bold text-gray-600 dark:text-gray-300">
                    <span class="w-3.5 h-3.5 rounded bg-amber-100 border border-amber-300 inline-block"></span> Friday Highlight
                </span>
            </div>
        </div>

        <div class="overflow-x-auto border border-gray-300 dark:border-gray-700 rounded-xl">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    {{-- Row 1: Day Names (Sat, Sun, Mon... with Friday Peach highlight) --}}
                    <tr class="bg-gray-100 dark:bg-gray-800 text-[10px] uppercase font-black text-gray-700 dark:text-gray-300 border-b border-gray-300 dark:border-gray-700">
                        <th class="px-2 py-2 w-8 text-center border-r border-gray-300 dark:border-gray-700" rowspan="2">SN</th>
                        <th class="px-3 py-2 min-w-[200px] border-r border-gray-300 dark:border-gray-700" rowspan="2">Days & Works</th>
                        <th class="px-3 py-2 text-center w-24 border-r border-gray-300 dark:border-gray-700" rowspan="2">Monthly Amt</th>
                        @foreach($gridSheet['days'] as $dayMeta)
                            <th class="px-1.5 py-1 text-center font-bold border-r border-gray-300 dark:border-gray-700 min-w-[36px] {{ $dayMeta['is_friday'] ? 'bg-amber-100 text-amber-900 dark:bg-amber-900/50 dark:text-amber-200' : '' }}">
                                {{ $dayMeta['day_name'] }}
                            </th>
                        @endforeach
                        <th class="px-3 py-2 text-center w-24 border-r border-gray-300 dark:border-gray-700" rowspan="2">Earned Amt</th>
                        <th class="px-3 py-2 text-center w-20" rowspan="2">Deduction</th>
                    </tr>
                    {{-- Row 2: Dates (1-Aug, 2-Aug...) --}}
                    <tr class="bg-gray-50 dark:bg-gray-800/80 text-[10px] font-bold text-gray-600 dark:text-gray-400 border-b-2 border-gray-300 dark:border-gray-700">
                        @foreach($gridSheet['days'] as $dayMeta)
                            <th class="px-1 py-1.5 text-center border-r border-gray-300 dark:border-gray-700 {{ $dayMeta['is_friday'] ? 'bg-amber-50 text-amber-900 dark:bg-amber-950/40 dark:text-amber-300 font-black' : '' }}">
                                {{ $dayMeta['label'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800 font-mono">
                    @forelse($gridSheet['rows'] as $idx => $row)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-2 py-2 text-center font-bold text-gray-400 border-r border-gray-200 dark:border-gray-800">{{ $idx + 1 }}</td>
                            <td class="px-3 py-2 font-sans font-bold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-800 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    @if($row['type'] === 'dynamic_report')
                                        <span class="text-xs">📋</span>
                                    @elseif($row['type'] === 'task')
                                        <span class="text-xs">📌</span>
                                    @else
                                        <span class="text-xs">✍️</span>
                                    @endif
                                    <span>{{ $row['name'] }}</span>
                                </div>
                                <div class="text-[10px] font-normal text-gray-400 font-sans mt-0.5">
                                    @if($row['is_daily'])
                                        <span>Daily (~{{ round($row['unit_amount']) }} / day)</span>
                                    @else
                                        <span>Count-based (~{{ round($row['unit_amount']) }} / task)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-2 text-center font-black text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                                {{ number_format($row['monthly_amount'], 0) }}
                            </td>

                            {{-- Days Cells (1 to 31) --}}
                            @foreach($gridSheet['days'] as $d => $dayMeta)
                                @php
                                    $cell = $row['days_data'][$d];
                                    $status = $cell['status'];
                                    $earned = $cell['earned'];
                                    $isFriday = $dayMeta['is_friday'];
                                @endphp
                                <td class="px-1 py-1.5 text-center text-[10px] border-r border-gray-200 dark:border-gray-800 font-bold 
                                    @if($status === 'done')
                                        bg-emerald-100 text-emerald-900 dark:bg-emerald-900/40 dark:text-emerald-200
                                    @elseif($status === 'partial')
                                        bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200
                                    @elseif($status === 'unsatisfied')
                                        bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300
                                    @elseif($status === 'undone')
                                        bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500
                                    @elseif(!$row['is_daily'] && $status === 'na')
                                        bg-gray-900 text-transparent select-none dark:bg-gray-950
                                    @else
                                        text-gray-300 dark:text-gray-600 {{ $isFriday ? 'bg-amber-50/40' : '' }}
                                    @endif"
                                    title="Day {{ $d }}: {{ ucfirst($status) }} (Earned: {{ $earned }} pts) {{ $cell['admin_rating'] ? '· Admin: ' . $cell['admin_rating'] : '' }}">
                                    @if($status === 'done' || $status === 'partial')
                                        {{ round($earned) }}
                                    @elseif($status === 'unsatisfied')
                                        <span class="line-through text-red-500" title="Admin Unsatisfactory: 0 pts">{{ round($row['unit_amount']) }}</span>
                                    @elseif($status === 'undone')
                                        <span class="text-gray-400">{{ round($row['unit_amount']) }}</span>
                                    @elseif(!$row['is_daily'] && $status === 'na')
                                        &nbsp;
                                    @else
                                        <span class="text-gray-300 dark:text-gray-600">—</span>
                                    @endif
                                </td>
                            @endforeach

                            <td class="px-3 py-2 text-center font-black text-emerald-600 dark:text-emerald-400 border-r border-gray-200 dark:border-gray-800 bg-emerald-50/30 dark:bg-emerald-950/20">
                                {{ number_format($row['total_earned'], 0) }}
                            </td>
                            <td class="px-3 py-2 text-center font-black text-red-500 dark:text-red-400 bg-red-50/30 dark:bg-red-950/20">
                                @if($row['total_deducted'] > 0)
                                    -{{ number_format($row['total_deducted'], 0) }}
                                @else
                                    0
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + $gridSheet['daysInMonth'] }}" class="px-4 py-8 text-center text-xs text-gray-400 font-sans">
                                No active task templates found for this employee.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                {{-- Totals Row at the bottom --}}
                <tfoot class="font-mono bg-gray-100 dark:bg-gray-800 text-xs font-black border-t-2 border-gray-300 dark:border-gray-700">
                    <tr>
                        <td colspan="2" class="px-3 py-2.5 font-sans font-extrabold uppercase text-gray-700 dark:text-gray-300 border-r border-gray-300 dark:border-gray-700">
                            Daily Earned Totals
                        </td>
                        <td class="px-3 py-2.5 text-center text-gray-900 dark:text-white border-r border-gray-300 dark:border-gray-700 bg-yellow-100 dark:bg-yellow-900/30">
                            {{ number_format($gridSheet['summary']['total_monthly_max'], 0) }}
                        </td>
                        @foreach($gridSheet['days'] as $d => $dayMeta)
                            <td class="px-1 py-2 text-center border-r border-gray-300 dark:border-gray-700 text-[10px] {{ $dayMeta['is_friday'] ? 'bg-amber-100/70 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200' : '' }}">
                                {{ round($gridSheet['dailyTotals'][$d]) }}
                            </td>
                        @endforeach
                        <td class="px-3 py-2.5 text-center text-emerald-700 dark:text-emerald-300 border-r border-gray-300 dark:border-gray-700 bg-emerald-100 dark:bg-emerald-900/40">
                            {{ number_format($gridSheet['summary']['total_earned'], 0) }}
                        </td>
                        <td class="px-3 py-2.5 text-center text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/40">
                            -{{ number_format($gridSheet['summary']['total_deducted'], 0) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Summary & Final Salary Breakdown Card --}}
    @if($report)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Performance Summary Card --}}
            <x-common.component-card title="Performance Evaluation & Attendance" desc="Summary of work completion and attendance this month">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3.5 dark:border-gray-800 dark:bg-gray-800/40">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Working Days</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $report->working_days }} Days</p>
                        </div>
                        <div class="rounded-xl border border-green-200 bg-green-50 p-3.5 dark:border-green-900/30 dark:bg-green-950/20">
                            <p class="text-xs text-green-700 dark:text-green-400 font-bold">Days Present</p>
                            <p class="text-xl font-black text-green-700 dark:text-green-400 mt-1">{{ $report->days_present }} Days</p>
                        </div>
                        <div class="rounded-xl border border-red-200 bg-red-50 p-3.5 dark:border-red-900/30 dark:bg-red-950/20">
                            <p class="text-xs text-red-600 dark:text-red-400 font-bold">Days Absent</p>
                            <p class="text-xl font-black text-red-600 dark:text-red-400 mt-1">{{ $report->days_absent }} Days</p>
                        </div>
                        <div class="rounded-xl border border-brand-200 bg-brand-50 p-3.5 dark:border-brand-900/30 dark:bg-brand-950/20">
                            <p class="text-xs text-brand-700 dark:text-brand-400 font-bold">Performance Grade</p>
                            <p class="text-xl font-black text-brand-700 dark:text-brand-400 mt-1">{{ $report->grade }} ({{ $report->performance_percentage }}%)</p>
                        </div>
                    </div>
                </div>
            </x-common.component-card>

            {{-- Salary Calculation Breakdown --}}
            <x-common.component-card title="Final Salary Calculation" desc="Calculation based on monthly basic allowances + performance task earnings">
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden text-xs">
                    <table class="w-full">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">Basic Salary</td>
                                <td class="px-4 py-2.5 text-right font-bold text-gray-900 dark:text-white font-mono">Rs. {{ number_format($report->basic_salary, 0) }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">Fuel Allowance</td>
                                <td class="px-4 py-2.5 text-right font-bold text-gray-900 dark:text-white font-mono">Rs. {{ number_format($report->fuel_allowance, 0) }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">Attendance Incentive</td>
                                <td class="px-4 py-2.5 text-right font-bold text-gray-900 dark:text-white font-mono">Rs. {{ number_format($report->attendance_incentive, 0) }}</td>
                            </tr>
                            <tr class="bg-gray-50 dark:bg-gray-800/50 font-bold border-t-2 border-gray-200 dark:border-gray-700">
                                <td class="px-4 py-2.5 text-gray-900 dark:text-white uppercase">Total Fixed Allowances</td>
                                <td class="px-4 py-2.5 text-right text-gray-900 dark:text-white font-mono">Rs. {{ number_format($report->total_basic, 0) }}</td>
                            </tr>
                            <tr class="bg-emerald-50/50 dark:bg-emerald-950/20 font-bold">
                                <td class="px-4 py-3 text-emerald-800 dark:text-emerald-300">
                                    🎯 Performance Tasks & Reports Earned
                                    <span class="block text-[10px] font-normal text-emerald-600 dark:text-emerald-400">Calculated from Daily Working Sheet</span>
                                </td>
                                <td class="px-4 py-3 text-right text-emerald-700 dark:text-emerald-300 font-mono text-sm">
                                    + Rs. {{ number_format($report->other_works_amount, 0) }}
                                </td>
                            </tr>
                            <tr class="bg-brand-500 text-white font-black text-sm">
                                <td class="px-4 py-3.5 uppercase tracking-wide">Final Payable Salary</td>
                                <td class="px-4 py-3.5 text-right font-mono text-base">Rs. {{ number_format($report->final_salary, 0) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-common.component-card>
        </div>
    @endif
@endsection
