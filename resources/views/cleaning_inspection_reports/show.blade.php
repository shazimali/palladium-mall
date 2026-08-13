@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Cleaning Report — {{ $report->report_date->format('d M Y') }}" />

    <div class="mx-auto w-full space-y-5">

        {{-- Top Action Bar --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-extrabold text-gray-800 dark:text-white/90">
                    🧹 Cleaning Inspection Report
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $report->report_date->format('l, d M Y') }}
                </p>
            </div>
            <div class="flex gap-2 flex-wrap">
                @can('cleaning_inspections.edit')
                    <a href="{{ route('cleaning-inspections.edit', $report) }}"
                       class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                        ✏️ Edit
                    </a>
                @endcan
                <a href="{{ route('cleaning-inspections.print', $report) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100 dark:border-blue-800/30 dark:bg-blue-500/10 dark:text-blue-400">
                    🖨️ Print
                </a>
                <a href="{{ route('cleaning-inspections.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                    ← Back
                </a>
            </div>
        </div>

        {{-- Summary Cards --}}
        @php
            $yesCount  = $report->items->where('status', true)->count();
            $noCount   = $report->items->where('status', false)->count();
            $naCount   = $report->items->whereNull('status')->count();
            $total     = $report->items->count();
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-center dark:border-green-800/30 dark:bg-green-500/10">
                <div class="text-3xl font-extrabold text-green-700 dark:text-green-400">{{ $yesCount }}</div>
                <div class="text-xs font-bold text-green-600 dark:text-green-500 mt-1">✅ YES</div>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-center dark:border-red-800/30 dark:bg-red-500/10">
                <div class="text-3xl font-extrabold text-red-700 dark:text-red-400">{{ $noCount }}</div>
                <div class="text-xs font-bold text-red-600 dark:text-red-500 mt-1">❌ NO</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center dark:border-gray-700 dark:bg-gray-800/40">
                <div class="text-3xl font-extrabold text-gray-500 dark:text-gray-400">{{ $naCount }}</div>
                <div class="text-xs font-bold text-gray-500 mt-1">— N/A</div>
            </div>
            <div class="rounded-xl border border-brand-200 bg-brand-50 p-4 text-center dark:border-brand-800/30 dark:bg-brand-500/10">
                <div class="text-3xl font-extrabold text-brand-600 dark:text-brand-400">{{ $total }}</div>
                <div class="text-xs font-bold text-brand-600 dark:text-brand-400 mt-1">Total Items</div>
            </div>
        </div>

        {{-- Meta Info --}}
        <div class="rounded-xl border border-gray-200 bg-white px-6 py-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-wrap gap-6 text-sm">
                <div>
                    <span class="text-xs font-semibold uppercase text-gray-400">Reported By</span>
                    <p class="font-semibold text-gray-800 dark:text-white/90 mt-0.5">{{ $report->reporter?->name ?? '—' }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase text-gray-400">Report Date</span>
                    <p class="font-semibold text-gray-800 dark:text-white/90 mt-0.5">{{ $report->report_date->format('d M Y') }}</p>
                </div>
                @if($report->overall_remarks)
                    <div class="flex-1">
                        <span class="text-xs font-semibold uppercase text-gray-400">Overall Remarks</span>
                        <p class="font-semibold text-gray-800 dark:text-white/90 mt-0.5">{{ $report->overall_remarks }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Items Table --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-4">
                <h3 class="text-base font-extrabold text-gray-800 dark:text-white/90">📋 Checklist Items</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-8">#</th>
                            <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Area / Item</th>
                            <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Remarks</th>
                            <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Image</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($report->items as $idx => $item)
                            <tr class="{{ $item->status === false ? 'bg-red-50/40 dark:bg-red-500/5' : '' }} hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                <td class="px-5 py-4 text-sm text-gray-400 font-medium">{{ $idx + 1 }}</td>
                                <td class="px-5 py-4 font-bold text-gray-800 dark:text-white/90">{{ $item->head?->name ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    @if($item->status === true)
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border-2 border-green-500 bg-green-500 px-3 py-1 text-sm font-bold text-white">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                                            YES
                                        </span>
                                    @elseif($item->status === false)
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border-2 border-red-500 bg-red-500 px-3 py-1 text-sm font-bold text-white">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                            NO
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border-2 border-gray-300 bg-gray-100 px-3 py-1 text-sm font-bold text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                            — N/A
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $item->remarks ?: '—' }}
                                </td>
                                <td class="px-5 py-4">
                                    @if($item->image_path)
                                        <a href="{{ Storage::url($item->image_path) }}" target="_blank">
                                            <img src="{{ Storage::url($item->image_path) }}"
                                                 class="h-14 w-14 rounded-xl object-cover border-2 border-gray-200 hover:scale-110 transition-transform shadow-sm" />
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">No items recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
