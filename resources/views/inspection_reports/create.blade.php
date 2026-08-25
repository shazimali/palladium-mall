@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="New {{ $reportType->name }} Report" />

    <div class="mx-auto w-full">
        <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">

            @if($reportType->is_daily && !$isWithinWindow)
                <div class="p-4 bg-amber-50 border-b border-amber-200 text-amber-900 dark:bg-amber-950/30 dark:border-amber-900/40 dark:text-amber-300 rounded-t-xl flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⏰</span>
                        <div>
                            <strong class="font-bold">Active Time Window Notice:</strong>
                            Daily reports for <strong>{{ $reportType->name }}</strong> are scheduled between <strong>{{ $reportType->time_window_display }}</strong> everyday.
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('inspection-reports.store', $reportType->key) }}"
                  method="POST" enctype="multipart/form-data" class="p-6 space-y-7">
                @csrf

                {{-- Date, Member & Remarks --}}
                <div class="grid grid-cols-1 md:grid-cols-{{ (isset($hasMembers) && $hasMembers) ? '3' : '2' }} gap-5">
                    @if($reportType->is_daily)
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Report Date <span class="text-red-500">*</span>
                                <span class="text-[10px] font-normal text-amber-600 dark:text-amber-400 ml-1">(Today's Daily)</span>
                            </label>
                            <div class="relative">
                                <input type="text" value="{{ \Carbon\Carbon::parse($today)->format('d M Y') }}" readonly
                                       class="h-11 w-full rounded-lg border border-gray-300 pl-10 pr-3 text-sm bg-gray-100 text-gray-700 cursor-not-allowed font-bold dark:border-gray-700 dark:bg-gray-800 dark:text-white/80 select-none" />
                                <input type="hidden" name="report_date" value="{{ $today }}" />
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    @else
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Report Date <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="date" name="report_date" value="{{ old('report_date', $today) }}" required
                                       class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('report_date') border-red-500 @enderror" />
                            </div>
                            @error('report_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if(isset($hasMembers) && $hasMembers)
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Select Member / Officer <span class="text-red-500">*</span>
                            </label>
                            <select name="report_type_member_id" required
                                    class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm font-semibold dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('report_type_member_id') border-red-500 @enderror">
                                <option value="">— Select Active Member * —</option>
                                @foreach($activeMembers as $mem)
                                    @php
                                        $alreadySubmitted = $reportType->is_daily && isset($todayMemberReportIds[$mem->id]);
                                    @endphp
                                    <option value="{{ $mem->id }}"
                                        @selected(old('report_type_member_id') == $mem->id)
                                        {{ $alreadySubmitted ? 'disabled class=text-gray-400' : '' }}>
                                        👤 {{ $mem->member_name }} {{ $alreadySubmitted ? '⚠️ (Already logged for today)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('report_type_member_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Overall Remarks <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="overall_remarks" required
                               value="{{ old('overall_remarks') }}"
                               placeholder="Overall inspection summary / remarks (mandatory)..."
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
                        <span class="text-xs text-red-500 font-medium">* Status, System Remark, and Additional Remarks are all mandatory</span>
                    </div>

                    @if($heads->isEmpty())
                        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-base text-yellow-700 dark:border-yellow-800/30 dark:bg-yellow-500/10 dark:text-yellow-400">
                            ⚠️ No active inspection heads assigned to <strong>{{ $reportType->name }}</strong>.
                            <a href="{{ route('inspection-heads.create') }}" class="font-bold underline ml-1">Add inspection heads</a> first.
                        </div>
                    @else
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
                                                System Remark <span class="text-red-500">*</span> & Additional Remarks <span class="text-red-500">*</span>
                                            @else
                                                Remarks <span class="text-red-500">*</span>
                                            @endif
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="min-width:180px">
                                            Photo <span class="text-xs font-normal text-gray-400">(optional, max 200KB)</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($heads as $idx => $head)
                                        @php
                                            $oldStatus = old("items.{$head->id}.status", 'yes');
                                            $oldRemarkId = old("items.{$head->id}.report_type_remark_id");
                                            $oldRemarks = old("items.{$head->id}.remarks");
                                        @endphp
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                            {{-- Order / Index --}}
                                            <td class="px-4 py-3.5 text-gray-400 font-mono text-xs font-semibold">
                                                {{ $idx + 1 }}
                                            </td>

                                            {{-- Head Name --}}
                                            <td class="px-4 py-3.5">
                                                <div class="font-bold text-gray-800 dark:text-white/90 text-sm">{{ $head->name }}</div>
                                            </td>

                                            {{-- Status Toggle Radio Group (Mandatory) --}}
                                            <td class="px-4 py-3.5">
                                                <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1 dark:border-gray-700 dark:bg-gray-900 gap-1">
                                                    {{-- YES / PASS --}}
                                                    <label class="status-toggle cursor-pointer">
                                                        <input type="radio" name="items[{{ $head->id }}][status]" value="yes"
                                                               class="sr-only status-radio" required
                                                               @checked($oldStatus === 'yes') />
                                                        <span class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-xs font-bold transition-all
                                                            {{ $oldStatus === 'yes' ? 'bg-green-600 text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400' }}">
                                                            ✓ Pass / Clean
                                                        </span>
                                                    </label>

                                                    {{-- NO / FAIL --}}
                                                    <label class="status-toggle cursor-pointer">
                                                        <input type="radio" name="items[{{ $head->id }}][status]" value="no"
                                                               class="sr-only status-radio" required
                                                               @checked($oldStatus === 'no') />
                                                        <span class="inline-flex items-center gap-1 rounded-md px-3 py-1.5 text-xs font-bold transition-all
                                                            {{ $oldStatus === 'no' ? 'bg-red-600 text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400' }}">
                                                            ✗ Fail / Issue
                                                        </span>
                                                    </label>

                                                    {{-- NA --}}
                                                    <label class="status-toggle cursor-pointer">
                                                        <input type="radio" name="items[{{ $head->id }}][status]" value="na"
                                                               class="sr-only status-radio" required
                                                               @checked($oldStatus === 'na') />
                                                        <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-bold transition-all
                                                            {{ $oldStatus === 'na' ? 'bg-gray-600 text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400' }}">
                                                            N/A
                                                        </span>
                                                    </label>
                                                </div>
                                            </td>

                                            {{-- System Remarks Dropdown (if configured) & Mandatory Additional Remarks --}}
                                            <td class="px-4 py-3.5 space-y-2">
                                                @if($systemRemarks->isNotEmpty())
                                                    <div>
                                                        <select name="items[{{ $head->id }}][report_type_remark_id]" required
                                                                class="h-9 w-full rounded-lg border border-gray-300 px-2.5 text-xs font-medium text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-brand-500 focus:outline-none">
                                                            <option value="">— Select System Remark * —</option>
                                                            @foreach($systemRemarks as $rem)
                                                                <option value="{{ $rem->id }}" @selected($oldRemarkId == $rem->id)>
                                                                    {{ $rem->remark }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif
                                                <input type="text" name="items[{{ $head->id }}][remarks]" required
                                                       value="{{ $oldRemarks }}"
                                                       placeholder="{{ $systemRemarks->isNotEmpty() ? 'Additional remarks / details (mandatory) *...' : 'Enter inspection remarks (mandatory) *...' }}"
                                                       class="h-9 w-full rounded-lg border border-gray-200 px-2.5 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/80 focus:border-brand-500 focus:outline-none" />
                                            </td>

                                            {{-- Image Upload (Optional, max 200KB) --}}
                                            <td class="px-4 py-3.5">
                                                <div class="space-y-1.5">
                                                    <label class="inline-flex items-center gap-1.5 cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                                                        <span>📷 Upload</span>
                                                        <input type="file" name="items[{{ $head->id }}][image]"
                                                               accept="image/*" class="sr-only insp-img-input" />
                                                    </label>
                                                    
                                                    <div class="img-preview-wrap hidden mt-1 relative w-16 h-16 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                                        <img class="img-preview w-full h-full object-cover" src="" alt="preview" />
                                                    </div>
                                                    <p class="img-size-error text-xs text-red-500 font-bold hidden">Exceeds 200 KB!</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Super Admin Feedback Section --}}
                @if(auth()->user()->isSuperAdmin())
                    <div class="rounded-2xl border-2 border-brand-200 bg-brand-50/40 p-5 dark:border-brand-900/40 dark:bg-brand-950/20 space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-wider text-brand-900 dark:text-brand-300 flex items-center gap-2">
                                👑 Super Admin Remarks & Feedback
                            </h4>
                            <span class="text-[11px] font-bold text-brand-600 bg-brand-100 dark:bg-brand-900/40 dark:text-brand-300 px-2 py-0.5 rounded-full">
                                Super Admin Only
                            </span>
                        </div>

                        {{-- Rating Selection --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                                Admin Evaluation / Rating
                            </label>
                            <div class="flex items-center gap-3 flex-wrap">
                                <label class="inline-flex items-center gap-2 cursor-pointer p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 transition-colors shadow-2xs">
                                    <input type="radio" name="admin_rating" value="good"
                                        {{ old('admin_rating') === 'good' ? 'checked' : '' }}
                                        class="w-4 h-4 text-emerald-600 border-gray-300 dark:border-gray-600 focus:ring-emerald-500">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 px-2.5 py-1 text-xs font-black text-emerald-800 dark:text-emerald-300">
                                        ✨ Satisfactory
                                    </span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors shadow-2xs">
                                    <input type="radio" name="admin_rating" value="bad"
                                        {{ old('admin_rating') === 'bad' ? 'checked' : '' }}
                                        class="w-4 h-4 text-rose-600 border-gray-300 dark:border-gray-600 focus:ring-rose-500">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-rose-100 dark:bg-rose-900/40 px-2.5 py-1 text-xs font-black text-rose-800 dark:text-rose-300">
                                        ⚠️ Unsatisfactory
                                    </span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors shadow-2xs">
                                    <input type="radio" name="admin_rating" value=""
                                        {{ !in_array(old('admin_rating'), ['good', 'bad']) ? 'checked' : '' }}
                                        class="w-4 h-4 text-gray-400 border-gray-300 dark:border-gray-600 focus:ring-gray-400">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-bold">None</span>
                                </label>
                            </div>
                        </div>

                        {{-- Admin Remarks Textarea --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                Admin Remarks (Optional)
                            </label>
                            <textarea name="admin_remarks" rows="2" placeholder="Enter admin feedback / instructions..."
                                class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">{{ old('admin_remarks') }}</textarea>
                        </div>

                        {{-- Admin Photo Upload (Max 200 KB) --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                📷 Attach Admin Feedback Photo (Max 200 KB)
                            </label>
                            <input type="file" name="admin_photo" accept="image/*" class="w-full text-xs text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-100 file:text-brand-700 hover:file:bg-brand-200 dark:file:bg-brand-900/30 dark:file:text-brand-300">
                            <span class="text-[10px] text-gray-400 block mt-1">JPEG, PNG, WEBP up to 200 KB</span>
                            @error('admin_photo')
                                <span class="text-xs text-red-500 font-bold block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('inspection-reports.index', $reportType->key) }}"
                       class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                        Cancel
                    </a>
                    <button type="submit" @if($reportType->is_daily && !$isWithinWindow && !auth()->user()->isSuperAdmin()) disabled @endif
                            class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-600 shadow-sm transition-colors @if($reportType->is_daily && !$isWithinWindow && !auth()->user()->isSuperAdmin()) opacity-50 cursor-not-allowed @endif">
                        💾 Save {{ $reportType->name }} Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        const MAX_KB = 200;

        // ── Status toggle visual highlight ────────────────────────────────
        document.querySelectorAll('.status-radio').forEach(function (radio) {
            radio.addEventListener('change', function () {
                const container = radio.closest('td');
                if (!container) return;

                container.querySelectorAll('.status-toggle').forEach(function (lbl) {
                    const span = lbl.querySelector('span');
                    const input = lbl.querySelector('input');
                    const val = input.value;
                    const sel = input.checked;

                    // Reset classes
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

        // ── Image preview & 200KB validation ────────────────────────────
        document.querySelectorAll('.insp-img-input').forEach(function (input) {
            input.addEventListener('change', function () {
                const td = input.closest('td');
                const wrap = td.querySelector('.img-preview-wrap');
                const img = td.querySelector('.img-preview');
                const err = td.querySelector('.img-size-error');
                const file = input.files[0];

                wrap.classList.add('hidden');
                err.classList.add('hidden');
                img.src = '';

                if (!file) return;

                if (file.size > MAX_KB * 1024) {
                    err.classList.remove('hidden');
                    input.value = '';
                    return;
                }

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
