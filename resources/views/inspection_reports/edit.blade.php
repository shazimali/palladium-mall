@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit {{ $reportType->name }} Report" />

    <div class="mx-auto w-full">
        <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">

            @if($reportType->is_daily && !$isWithinWindow)
                <div class="p-4 bg-amber-50 border-b border-amber-200 text-amber-900 dark:bg-amber-950/30 dark:border-amber-900/40 dark:text-amber-300 rounded-t-xl flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⏰</span>
                        <div>
                            <strong class="font-bold">Active Time Window Notice:</strong>
                            Daily reports for <strong>{{ $reportType->name }}</strong> normally have an active window between <strong>{{ $reportType->time_window_display }}</strong>. As Super Admin, you are authorized to edit at any time.
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('inspection-reports.update', ['type' => $reportType->key, 'report' => $report->id]) }}"
                  method="POST" enctype="multipart/form-data" class="p-6 space-y-7">
                @csrf
                @method('PUT')

                {{-- Date, Member & Overall Remarks --}}
                <div class="grid grid-cols-1 md:grid-cols-{{ (isset($hasMembers) && $hasMembers) ? '3' : '2' }} gap-5">
                    @if($reportType->is_daily)
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Report Date</label>
                            <div class="h-11 flex items-center px-3 rounded-lg border border-gray-200 bg-gray-50 text-sm font-bold text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-white/80">
                                {{ $report->report_date->format('d M Y') }}
                            </div>
                        </div>
                    @else
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Report Date <span class="text-red-500">*</span></label>
                            <input type="date" name="report_date" value="{{ old('report_date', $report->report_date->toDateString()) }}" required
                                   class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('report_date') border-red-500 @enderror" />
                            @error('report_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if(isset($hasMembers) && $hasMembers)
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Member / Officer <span class="text-red-500">*</span>
                            </label>
                            <select name="report_type_member_id" required
                                    class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm font-semibold dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('report_type_member_id') border-red-500 @enderror">
                                <option value="">— Select Active Member * —</option>
                                @foreach($activeMembers as $mem)
                                    <option value="{{ $mem->id }}" @selected(old('report_type_member_id', $report->report_type_member_id) == $mem->id)>
                                        👤 {{ $mem->member_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('report_type_member_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Overall Remarks <span class="text-gray-400 font-normal normal-case">(optional)</span>
                        </label>
                        <input type="text" name="overall_remarks"
                               value="{{ old('overall_remarks', $report->overall_remarks) }}"
                               placeholder="Overall inspection summary / remarks..."
                               class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('overall_remarks') border-red-500 @enderror" />
                        @error('overall_remarks') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Checklist Table --}}
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-base font-extrabold text-gray-800 dark:text-white/90 flex items-center gap-2">
                            📋 Inspection Checklist
                            <span class="text-sm font-normal text-gray-400">({{ $heads->count() }} items)</span>
                        </h4>
                        <span class="text-xs text-gray-500 font-medium">* Status is mandatory (Remarks are optional)</span>
                    </div>

                    <div class="w-full overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900/40">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-12">#</th>
                                    <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Area / Item</th>
                                    <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="min-width:220px">
                                        Status <span class="text-red-500">*</span>
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="min-width:260px">
                                        @if($systemRemarks->isNotEmpty())
                                            System Remark & Additional Remarks <span class="text-xs font-normal text-gray-400">(optional)</span>
                                        @else
                                            Remarks <span class="text-xs font-normal text-gray-400">(optional)</span>
                                        @endif
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="min-width:160px">
                                        Photo <span class="text-xs font-normal text-gray-400">(optional)</span>
                                    </th>
                                    @if(auth()->user()->isSuperAdmin())
                                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-amber-800 dark:text-amber-300 bg-amber-50/60 dark:bg-amber-950/30" style="min-width:260px">
                                            👑 Admin Evaluation
                                        </th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($heads as $idx => $head)
                                    @php
                                        $existing = $report->items->where('inspection_head_id', $head->id)->first();
                                        $curStatus = old("items.{$head->id}.status", $existing?->status ?? 'yes');
                                        $curRemarkId = old("items.{$head->id}.report_type_remark_id", $existing?->report_type_remark_id);
                                        $curRemarks = old("items.{$head->id}.remarks", $existing?->remarks);
                                        $curAdminRating = old("items.{$head->id}.admin_rating", $existing?->admin_rating ?? '');
                                        $curAdminRemarks = old("items.{$head->id}.admin_remarks", $existing?->admin_remarks ?? '');
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                        <td class="px-4 py-3.5 text-gray-400 font-mono text-xs font-semibold">
                                            {{ $idx + 1 }}
                                        </td>

                                        <td class="px-4 py-3.5">
                                            <div class="font-bold text-gray-800 dark:text-white/90 text-sm">{{ $head->name }}</div>
                                        </td>

                                        {{-- Status Toggle Radio Group --}}
                                        <td class="px-4 py-3.5">
                                            <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1 dark:border-gray-700 dark:bg-gray-900 gap-1">
                                                <label class="status-toggle cursor-pointer">
                                                    <input type="radio" name="items[{{ $head->id }}][status]" value="yes"
                                                           class="sr-only status-radio" required
                                                           @checked($curStatus === 'yes') />
                                                    <span class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-xs font-bold transition-all
                                                        {{ $curStatus === 'yes' ? 'bg-green-600 text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400' }}">
                                                        ✓ Pass / Clean
                                                    </span>
                                                </label>

                                                <label class="status-toggle cursor-pointer">
                                                    <input type="radio" name="items[{{ $head->id }}][status]" value="no"
                                                           class="sr-only status-radio" required
                                                           @checked($curStatus === 'no') />
                                                    <span class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-xs font-bold transition-all
                                                        {{ $curStatus === 'no' ? 'bg-red-600 text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400' }}">
                                                        ✗ Fail / Issue
                                                    </span>
                                                </label>

                                                <label class="status-toggle cursor-pointer">
                                                    <input type="radio" name="items[{{ $head->id }}][status]" value="na"
                                                           class="sr-only status-radio" required
                                                           @checked($curStatus === 'na') />
                                                    <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-bold transition-all
                                                        {{ $curStatus === 'na' ? 'bg-gray-600 text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400' }}">
                                                        N/A
                                                    </span>
                                                </label>
                                            </div>
                                        </td>

                                        {{-- System Remarks Dropdown (if configured) & Additional Remarks --}}
                                        <td class="px-4 py-3.5 space-y-2">
                                            @if($systemRemarks->isNotEmpty())
                                                <div>
                                                    <select name="items[{{ $head->id }}][report_type_remark_id]"
                                                            class="h-9 w-full rounded-lg border border-gray-300 px-2.5 text-xs font-medium text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-brand-500 focus:outline-none">
                                                        <option value="">— Select System Remark (optional) —</option>
                                                        @foreach($systemRemarks as $rem)
                                                            <option value="{{ $rem->id }}" @selected($curRemarkId == $rem->id)>
                                                                {{ $rem->remark }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            <input type="text" name="items[{{ $head->id }}][remarks]"
                                                   value="{{ $curRemarks }}"
                                                   placeholder="{{ $systemRemarks->isNotEmpty() ? 'Additional remarks / details (optional)...' : 'Enter inspection remarks (optional)...' }}"
                                                   class="h-9 w-full rounded-lg border border-gray-200 px-2.5 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/80 focus:border-brand-500 focus:outline-none" />
                                        </td>

                                        {{-- Photo Upload --}}
                                        <td class="px-4 py-3.5">
                                            <div class="space-y-1.5">
                                                <label class="inline-flex items-center gap-1.5 cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                                                    <span>📷 {{ $existing?->image_path ? 'Change Photo' : 'Upload Photo' }}</span>
                                                    <input type="file" name="items[{{ $head->id }}][image]"
                                                           accept="image/*" class="sr-only insp-img-input" />
                                                </label>
                                                
                                                <div class="img-preview-wrap {{ $existing?->image_path ? '' : 'hidden' }} mt-1 relative w-16 h-16 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                                    <img class="img-preview w-full h-full object-cover"
                                                         src="{{ $existing?->image_url ?? '' }}" alt="preview" />
                                                </div>
                                                <p class="img-size-error text-xs text-red-500 font-bold hidden">Exceeds 200 KB!</p>
                                            </div>
                                        </td>

                                        {{-- Super Admin Per-Row Evaluation --}}
                                        @if(auth()->user()->isSuperAdmin())
                                            <td class="px-4 py-3.5 bg-amber-50/30 dark:bg-amber-950/10 border-l border-amber-100 dark:border-amber-900/30">
                                                <div class="space-y-2">
                                                    {{-- Admin Rating Radio Group --}}
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <label class="inline-flex items-center gap-1 cursor-pointer px-2 py-1 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-emerald-50 text-[11px] font-bold shadow-2xs">
                                                            <input type="radio" name="items[{{ $head->id }}][admin_rating]" value="good" class="w-3.5 h-3.5 text-emerald-600 focus:ring-emerald-500"
                                                                @checked($curAdminRating === 'good')>
                                                            <span class="text-emerald-700 dark:text-emerald-300">✨ Sat.</span>
                                                        </label>
                                                        <label class="inline-flex items-center gap-1 cursor-pointer px-2 py-1 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-rose-50 text-[11px] font-bold shadow-2xs">
                                                            <input type="radio" name="items[{{ $head->id }}][admin_rating]" value="bad" class="w-3.5 h-3.5 text-rose-600 focus:ring-rose-500"
                                                                @checked($curAdminRating === 'bad')>
                                                            <span class="text-rose-700 dark:text-rose-300">⚠️ Unsat.</span>
                                                        </label>
                                                        <label class="inline-flex items-center gap-1 cursor-pointer px-1.5 py-1 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 text-[11px] font-medium text-gray-500 shadow-2xs">
                                                            <input type="radio" name="items[{{ $head->id }}][admin_rating]" value="" class="w-3.5 h-3.5 text-gray-400 focus:ring-gray-400"
                                                                @checked(empty($curAdminRating))>
                                                            <span>None</span>
                                                        </label>
                                                    </div>

                                                    {{-- Admin Remarks --}}
                                                    <input type="text" name="items[{{ $head->id }}][admin_remarks]" value="{{ $curAdminRemarks }}"
                                                           placeholder="Admin feedback remarks..."
                                                           class="h-8 w-full rounded-lg border border-amber-200 bg-white px-2.5 text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-brand-500 focus:outline-none" />

                                                    {{-- Admin Photo --}}
                                                    <div class="flex items-center gap-2">
                                                        <label class="inline-flex items-center gap-1 cursor-pointer rounded border border-gray-300 bg-white px-2 py-1 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                            <span>📷 Admin Photo</span>
                                                            <input type="file" name="items[{{ $head->id }}][admin_photo]" accept="image/*" class="sr-only insp-img-input" />
                                                        </label>
                                                        @if(!empty($existing?->admin_photo))
                                                            <a href="{{ $existing->admin_photo_url }}" target="_blank" class="text-[11px] text-brand-600 underline font-bold">View</a>
                                                            <label class="inline-flex items-center gap-1 text-[10px] text-red-500 cursor-pointer">
                                                                <input type="checkbox" name="items[{{ $head->id }}][remove_admin_photo]" value="1" class="w-3 h-3 text-red-600 rounded">
                                                                <span>Remove</span>
                                                            </label>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('inspection-reports.index', $reportType->key) }}"
                       class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-600 shadow-sm transition-colors">
                        💾 Update {{ $reportType->name }} Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        const MAX_KB = 200;

        document.querySelectorAll('.status-radio').forEach(function (radio) {
            radio.addEventListener('change', function () {
                const container = radio.closest('td');
                if (!container) return;

                container.querySelectorAll('.status-toggle').forEach(function (lbl) {
                    const span = lbl.querySelector('span');
                    const input = lbl.querySelector('input');
                    const val = input.value;
                    const sel = input.checked;

                    span.className = 'inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-xs font-bold transition-all ';
                    if (sel) {
                        if (val === 'yes') {
                            span.className += 'bg-green-600 text-white shadow-xs';
                        } else if (val === 'no') {
                            span.className += 'bg-red-600 text-white shadow-xs';
                        } else {
                            span.className += 'bg-gray-600 text-white shadow-xs';
                        }
                    } else {
                        span.className += 'text-gray-600 hover:text-gray-900 dark:text-gray-400';
                    }
                });
            });
        });

        document.querySelectorAll('.insp-img-input').forEach(function (input) {
            input.addEventListener('change', function () {
                const td = input.closest('td');
                const wrap = td.querySelector('.img-preview-wrap');
                const img = td.querySelector('.img-preview');
                const err = td.querySelector('.img-size-error');
                const file = input.files[0];

                if (!file) return;

                if (file.size > MAX_KB * 1024) {
                    err.classList.remove('hidden');
                    input.value = '';
                    return;
                }

                err.classList.add('hidden');
                const reader = new FileReader();
                reader.onload = function (e) {
                    img.src = e.target.result;
                    wrap.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            });
        });
    })();
    </script>
    @endpush
@endsection
