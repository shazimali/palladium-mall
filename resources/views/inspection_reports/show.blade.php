@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ $reportType->name }} Report Detail" />

    <div class="mx-auto w-full space-y-6">
        {{-- Header & Quick Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-extrabold text-gray-800 dark:text-white/90">{{ $reportType->name }} Report</h2>
                    <span class="rounded-full bg-brand-50 px-3 py-0.5 text-xs font-bold text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                        {{ $report->report_date->format('d M Y') }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Conducted by <strong class="text-gray-700 dark:text-gray-300">{{ $report->reporter?->name ?? 'System' }}</strong> on {{ $report->created_at->format('d M Y, h:i A') }}
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="{{ route('inspection-reports.print', ['type' => $reportType->key, 'report' => $report->id]) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors shadow-xs">
                    🖨️ Print Report
                </a>
                <a href="{{ route('inspection-reports.edit', ['type' => $reportType->key, 'report' => $report->id]) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-bold text-white hover:bg-brand-600 transition-colors shadow-sm">
                    ✏️ Edit Report
                </a>
                <a href="{{ route('inspection-reports.index', $reportType->key) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                    ← Back to List
                </a>
            </div>
        </div>

        {{-- Metrics Breakdown Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Items</div>
                <div class="mt-1 text-2xl font-black text-gray-900 dark:text-white">{{ $report->items->count() }}</div>
            </div>
            <div class="rounded-xl border border-green-200 bg-green-50/50 p-4 dark:border-green-900/30 dark:bg-green-950/20">
                <div class="text-xs font-semibold text-green-700 dark:text-green-400 uppercase">✓ Passed / Clean</div>
                <div class="mt-1 text-2xl font-black text-green-700 dark:text-green-300">{{ $report->passCount() }}</div>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50/50 p-4 dark:border-red-900/30 dark:bg-red-950/20">
                <div class="text-xs font-semibold text-red-700 dark:text-red-400 uppercase">✗ Issues / Fail</div>
                <div class="mt-1 text-2xl font-black text-red-700 dark:text-red-300">{{ $report->failCount() }}</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">N/A Items</div>
                <div class="mt-1 text-2xl font-black text-gray-600 dark:text-gray-400">{{ $report->naCount() }}</div>
            </div>
        </div>

        @if($report->overall_remarks)
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="text-xs font-extrabold uppercase text-gray-500 dark:text-gray-400 mb-1">Overall Inspection Remarks</h4>
                <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $report->overall_remarks }}</p>
            </div>
        @endif

        {{-- Checklist Items Table --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-extrabold text-gray-800 dark:text-white text-base">📋 Detailed Checklist Results</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-12">#</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Area / Item</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $reportType->activeRemarks()->exists() ? 'System Remark & Remarks' : 'Remarks' }}</th>
                            <th class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Photo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($report->items->sortBy(fn($i) => $i->head?->sort_order ?? 999) as $idx => $item)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                <td class="px-4 py-3 text-gray-400 font-mono text-xs font-bold">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-4 py-3 font-extrabold text-sm text-gray-900 dark:text-white">
                                    {{ $item->head?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($item->status === 'yes')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 text-xs font-bold text-green-700 dark:bg-green-900/30 dark:text-green-300">
                                            ✓ Pass / Clean
                                        </span>
                                    @elseif($item->status === 'no')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-600 dark:bg-red-900/30 dark:text-red-400">
                                            ✗ Fail / Issue
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            N/A
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($item->systemRemark)
                                        <span class="inline-block rounded-md bg-brand-50 px-2.5 py-0.5 text-xs font-bold text-brand-700 dark:bg-brand-950/40 dark:text-brand-300">
                                            {{ $item->systemRemark->remark }}
                                        </span>
                                    @endif
                                    @if($item->remarks)
                                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-1 font-medium">{{ $item->remarks }}</div>
                                    @endif
                                    @if(!$item->systemRemark && !$item->remarks)
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($item->image_path)
                                        <a href="{{ $item->image_url }}" target="_blank" class="inline-block relative group">
                                            <img src="{{ $item->image_url }}" alt="item image"
                                                 class="h-12 w-12 rounded-lg object-cover border border-gray-200 shadow-xs hover:opacity-90 transition-opacity dark:border-gray-700" />
                                        </a>
                                    @else
                                        <span class="text-gray-400 text-xs">No Photo</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
