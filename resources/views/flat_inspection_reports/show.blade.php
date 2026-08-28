@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Flat Inspection Report Details" />

    <div class="mx-auto w-full max-w-4xl space-y-4">
        <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="text-lg font-extrabold text-gray-800 dark:text-white/90">
                        {{ $report->type_label }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $report->stage_badge_class }}">
                        {{ $report->type_label }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Unit / Flat: <strong>{{ $report->effective_unit?->unit_number ?? '—' }}</strong>
                    @if($report->tenant)
                        &nbsp;|&nbsp; Tenant: <strong>{{ $report->tenant->name }}</strong>
                    @endif
                    @if($report->agreement_id)
                        &nbsp;|&nbsp; Agreement #<strong>{{ $report->agreement_id }}</strong>
                    @endif
                    &nbsp;|&nbsp; Date: <strong>{{ $report->inspected_at?->format('d M Y') ?? '—' }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('inspection-reports.index', 'flat_inspection') }}"
                   class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-2 text-xs font-bold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                    ← History
                </a>
                <a href="{{ route('inspection-reports.print', ['type' => 'flat_inspection', 'report' => $report->id]) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3 py-2 text-xs font-bold text-white hover:bg-gray-800">
                    🖨️ Print Report
                </a>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-center dark:border-green-800/30 dark:bg-green-900/20">
                <div class="text-2xl font-extrabold text-green-700 dark:text-green-300">{{ $report->passCount() }}</div>
                <div class="text-xs font-semibold text-green-600 dark:text-green-400 mt-1">✅ Pass</div>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-center dark:border-red-800/30 dark:bg-red-900/20">
                <div class="text-2xl font-extrabold text-red-700 dark:text-red-300">{{ $report->failCount() }}</div>
                <div class="text-xs font-semibold text-red-600 dark:text-red-400 mt-1">❌ Fail</div>
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center dark:border-gray-800 dark:bg-gray-900/30">
                <div class="text-2xl font-extrabold text-gray-700 dark:text-gray-300">{{ $report->totalCount() }}</div>
                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1">Total Heads</div>
            </div>
        </div>

        {{-- Meta Info --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div><span class="block text-xs text-gray-400">Inspector / Officer</span><span class="font-semibold text-gray-800 dark:text-white/90">{{ $report->inspectionPerson?->name ?? ($report->inspection_member ?: '—') }}</span></div>
                <div><span class="block text-xs text-gray-400">Date</span><span class="font-semibold text-gray-800 dark:text-white/90">{{ $report->inspected_at?->format('d M Y') ?? '—' }}</span></div>
                <div><span class="block text-xs text-gray-400">Flat Condition</span><span class="font-semibold text-gray-800 dark:text-white/90">{{ ucfirst($report->flat_condition ?? '—') }}</span></div>
                <div><span class="block text-xs text-gray-400">Logged By</span><span class="font-semibold text-gray-800 dark:text-white/90">{{ $report->inspector?->name ?? 'Admin' }}</span></div>
            </div>
            @if($report->remarks)
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400 border-t border-gray-100 pt-3 dark:border-gray-800"><strong>Overall Remarks:</strong> {{ $report->remarks }}</p>
            @endif
        </div>

        {{-- Items Table --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <table class="min-w-full text-sm divide-y divide-gray-100 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Head</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Remarks</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Image</th>
                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-amber-800 dark:text-amber-300 bg-amber-50/60 dark:bg-amber-950/30" style="min-width:200px">Admin Evaluation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($report->items as $i => $item)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/80">{{ $item->head?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($item->status === true)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 text-xs font-bold text-green-700 dark:bg-green-900/30 dark:text-green-300">✅ Pass</span>
                                @elseif($item->status === false)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-600 dark:bg-red-900/30 dark:text-red-400">❌ Fail</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-500 dark:bg-gray-800 dark:text-gray-400">— N/A</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if($item->systemRemark)
                                    <div class="font-bold text-gray-900 dark:text-white mb-0.5">
                                        🏷️ {{ $item->systemRemark->remark }}
                                    </div>
                                @endif
                                <div class="text-gray-600 dark:text-gray-400">
                                    {{ $item->remarks ?: '—' }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($item->image_path)
                                    <a href="{{ Storage::url($item->image_path) }}" target="_blank">
                                        <img src="{{ Storage::url($item->image_path) }}" class="h-10 w-10 rounded-lg object-cover border border-gray-200 hover:scale-105 transition-transform" />
                                    </a>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 bg-amber-50/20 dark:bg-amber-950/10 border-l border-amber-100 dark:border-amber-900/20">
                                <div class="space-y-1">
                                    @if($item->admin_rating === 'good')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-black text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                            ✨ Satisfactory
                                        </span>
                                    @elseif($item->admin_rating === 'bad')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-black text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                            ⚠️ Unsatisfactory
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs font-medium">Unrated</span>
                                    @endif

                                    @if($item->admin_remarks)
                                        <div class="text-xs text-gray-700 dark:text-gray-300 font-semibold">{{ $item->admin_remarks }}</div>
                                    @endif

                                    @if($item->admin_photo)
                                        <div class="pt-1 flex items-center gap-1.5">
                                            <a href="{{ $item->admin_photo_url }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-600 dark:text-brand-400 hover:underline">
                                                <span>📷 View Admin Photo</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
