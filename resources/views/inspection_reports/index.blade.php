@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ $reportType->name }} Reports" />

    <div class="mx-auto w-full space-y-4">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-extrabold text-gray-800 dark:text-white/90">{{ $reportType->name }} Reports</h2>
                    @if($reportType->is_daily)
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                            📅 Daily ({{ $reportType->time_window_display }})
                        </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $reportType->description ?: 'Inspection checklist records for ' . $reportType->name . '.' }}
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                @if($reportType->is_daily && !$isWithinWindow)
                    <div class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                        <span>⏰ Active Window:</span> {{ $reportType->time_window_display }}
                    </div>
                @endif
            @can('inspection_reports.create')
                <a href="{{ route('inspection-reports.create', $reportType->key) }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-600 transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ $reportType->is_daily ? "Create Today's Report" : "New " . $reportType->name }}
                </a>
            @endcan
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('inspection-reports.index', $reportType->key) }}"
          class="flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">From Date</label>
            <div class="relative">
                <input type="text" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="From Date"
                       class="h-9 w-40 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            </div>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">To Date</label>
            <div class="relative">
                <input type="text" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="To Date"
                       class="h-9 w-40 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            </div>
        </div>
        <button type="submit" class="h-9 rounded-lg bg-brand-500 px-4 text-sm font-bold text-white hover:bg-brand-600 shadow-sm transition-colors">Filter</button>
        @if(request()->hasAny(['date_from', 'date_to']))
            <a href="{{ route('inspection-reports.index', $reportType->key) }}" class="h-9 flex items-center rounded-lg border border-gray-300 px-4 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Report Date</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Reported By</th>
                        <th class="px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pass / Clean</th>
                        <th class="px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Issues</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Overall Remarks</th>
                        <th class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($reports as $report)
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 font-bold text-gray-800 dark:text-white/90">
                                {{ $report->report_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $report->reporter?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-full bg-green-50 text-green-700 font-extrabold text-xs dark:bg-green-900/30 dark:text-green-300">
                                    {{ $report->passCount() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-[28px] h-7 px-2 rounded-full {{ $report->failCount() > 0 ? 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-800' }} font-extrabold text-xs">
                                    {{ $report->failCount() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                {{ $report->overall_remarks ?: '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('inspection-reports.show', ['type' => $reportType->key, 'report' => $report->id]) }}"
                                       class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 transition-colors">
                                        👁 View
                                    </a>
                                    <a href="{{ route('inspection-reports.print', ['type' => $reportType->key, 'report' => $report->id]) }}" target="_blank"
                                       class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 transition-colors">
                                        🖨️ Print
                                    </a>
                                    @can('inspection_reports.edit')
                                        <a href="{{ route('inspection-reports.edit', ['type' => $reportType->key, 'report' => $report->id]) }}"
                                           class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-100 dark:border-blue-800/30 dark:bg-blue-900/20 dark:text-blue-400 transition-colors">
                                            ✏️ Edit
                                        </a>
                                    @endcan
                                    @can('inspection_reports.delete')
                                        <form action="{{ route('inspection-reports.destroy', ['type' => $reportType->key, 'report' => $report->id]) }}" method="POST"
                                              onsubmit="return confirm('Delete this inspection report?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 dark:border-red-800/30 dark:bg-red-900/20 dark:text-red-400 transition-colors">
                                                🗑
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                    No {{ $reportType->name }} reports found.
                                    <a href="{{ route('inspection-reports.create', $reportType->key) }}" class="text-brand-500 font-semibold hover:underline ml-1">Create one now</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reports->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#date_from', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true
                });
                flatpickr('#date_to', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true
                });
            }
        });
    </script>
    @endpush
@endsection
