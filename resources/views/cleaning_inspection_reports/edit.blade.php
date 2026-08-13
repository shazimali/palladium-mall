@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Cleaning Report" />

    <div class="mx-auto w-full">
        <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">

            <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-5">
                <h2 class="text-xl font-extrabold text-gray-800 dark:text-white/90">🧹 Edit Cleaning Inspection Report</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $report->report_date->format('l, d M Y') }}</p>
            </div>

            <form action="{{ route('cleaning-inspections.update', $report) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-7">
                @csrf @method('PUT')

                {{-- Overall Remarks --}}
                <div class="max-w-lg">
                    <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">Overall Remarks</label>
                    <input type="text" name="overall_remarks" value="{{ old('overall_remarks', $report->overall_remarks) }}"
                           placeholder="Any general remarks for today..."
                           class="h-11 w-full rounded-lg border border-gray-300 px-3 text-base dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                {{-- Cleaning Checklist --}}
                <div>
                    <h4 class="text-base font-extrabold text-gray-800 dark:text-white/90 mb-4 flex items-center gap-2">
                        📋 Cleaning Checklist
                        <span class="text-sm font-normal text-gray-400">({{ $heads->count() }} items)</span>
                    </h4>

                    <div class="w-full overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-base">
                            <thead class="bg-gray-50 dark:bg-gray-900/40">
                                <tr>
                                    <th class="px-5 py-4 text-left text-sm font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-8">#</th>
                                    <th class="px-5 py-4 text-left text-sm font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Area / Item</th>
                                    <th class="px-5 py-4 text-left text-sm font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="min-width:260px">Status</th>
                                    <th class="px-5 py-4 text-left text-sm font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Remarks</th>
                                    <th class="px-5 py-4 text-left text-sm font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="min-width:180px">Image <span class="text-xs font-normal text-gray-400">(max 200KB)</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($heads as $idx => $head)
                                    @php
                                        $existing  = $existingItems->get($head->id);
                                        $oldStatus = old("items.{$head->id}.status",
                                            $existing ? ($existing->status === true ? 'yes' : ($existing->status === false ? 'no' : 'na')) : 'na'
                                        );
                                    @endphp
                                    <tr class="hover:bg-gray-50/60 dark:hover:bg-white/[0.01] transition-colors">

                                        <td class="px-5 py-4 text-sm text-gray-400 font-medium align-middle">{{ $idx + 1 }}</td>

                                        <td class="px-5 py-4 font-bold text-gray-800 dark:text-white/90 text-base align-middle">{{ $head->name }}</td>

                                        {{-- Status toggle buttons --}}
                                        <td class="px-5 py-4 align-middle">
                                            <div class="flex gap-2 flex-wrap">

                                                {{-- YES --}}
                                                <label class="status-toggle cursor-pointer select-none">
                                                    <input type="radio" name="items[{{ $head->id }}][status]" value="yes"
                                                           @checked($oldStatus === 'yes')
                                                           class="sr-only status-radio" />
                                                    <span class="inline-flex items-center gap-2 rounded-lg border-2 px-4 py-2 text-sm font-bold transition-all
                                                        {{ $oldStatus === 'yes'
                                                            ? 'border-green-500 bg-green-500 text-white shadow-sm'
                                                            : 'border-gray-200 bg-white text-gray-500 hover:border-green-400 hover:text-green-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400' }}">
                                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                                                        YES
                                                    </span>
                                                </label>

                                                {{-- NO --}}
                                                <label class="status-toggle cursor-pointer select-none">
                                                    <input type="radio" name="items[{{ $head->id }}][status]" value="no"
                                                           @checked($oldStatus === 'no')
                                                           class="sr-only status-radio" />
                                                    <span class="inline-flex items-center gap-2 rounded-lg border-2 px-4 py-2 text-sm font-bold transition-all
                                                        {{ $oldStatus === 'no'
                                                            ? 'border-red-500 bg-red-500 text-white shadow-sm'
                                                            : 'border-gray-200 bg-white text-gray-500 hover:border-red-400 hover:text-red-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400' }}">
                                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                                        NO
                                                    </span>
                                                </label>

                                                {{-- N/A --}}
                                                <label class="status-toggle cursor-pointer select-none">
                                                    <input type="radio" name="items[{{ $head->id }}][status]" value="na"
                                                           @checked($oldStatus === 'na')
                                                           class="sr-only status-radio" />
                                                    <span class="inline-flex items-center gap-2 rounded-lg border-2 px-4 py-2 text-sm font-bold transition-all
                                                        {{ $oldStatus === 'na'
                                                            ? 'border-gray-500 bg-gray-500 text-white shadow-sm'
                                                            : 'border-gray-200 bg-white text-gray-500 hover:border-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400' }}">
                                                        <span class="text-base leading-none">—</span>
                                                        N/A
                                                    </span>
                                                </label>

                                            </div>
                                        </td>

                                        {{-- Remarks --}}
                                        <td class="px-5 py-4 align-middle">
                                            <input type="text" name="items[{{ $head->id }}][remarks]"
                                                   value="{{ old("items.{$head->id}.remarks", $existing?->remarks) }}"
                                                   placeholder="Remarks..."
                                                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/80" />
                                        </td>

                                        {{-- Image --}}
                                        <td class="px-5 py-4 align-middle">
                                            @if($existing?->image_path)
                                                <a href="{{ Storage::url($existing->image_path) }}" target="_blank" class="block mb-2">
                                                    <img src="{{ Storage::url($existing->image_path) }}"
                                                         class="h-14 w-14 rounded-xl object-cover border-2 border-gray-200 hover:scale-105 transition-transform shadow-sm" />
                                                </a>
                                            @endif

                                            <div class="img-preview-wrap mb-1.5 hidden">
                                                <img class="img-preview h-14 w-14 rounded-xl object-cover border-2 border-green-400 shadow-sm" src="" alt="preview" />
                                            </div>
                                            <p class="img-size-error hidden text-sm font-semibold text-red-600 mb-1">⚠️ Max 200KB</p>

                                            <input type="file"
                                                   name="items[{{ $head->id }}][image]"
                                                   accept="image/*"
                                                   class="insp-img-input text-sm text-gray-600 dark:text-gray-400
                                                          file:rounded-lg file:border-0 file:bg-green-50 file:px-3 file:py-1.5
                                                          file:text-sm file:font-semibold file:text-green-700 hover:file:bg-green-100" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('cleaning-inspections.index') }}"
                       class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-lg bg-green-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-green-700 shadow-sm">
                        💾 Update Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        const MAX_KB = 200;

        // ── Status toggle visual update ──────────────────────────────────
        document.querySelectorAll('.status-radio').forEach(function (radio) {
            radio.addEventListener('change', function () {
                const row = radio.closest('tr');
                if (!row) return;

                row.querySelectorAll('.status-toggle').forEach(function (lbl) {
                    const span = lbl.querySelector('span');
                    const input = lbl.querySelector('input');
                    const val = input.value;
                    const sel = input.checked;

                    const colorMap = { yes: 'green', no: 'red', na: 'gray' };
                    const c = colorMap[val] || 'gray';

                    if (sel) {
                        span.className = `inline-flex items-center gap-2 rounded-lg border-2 px-4 py-2 text-sm font-bold transition-all border-${c}-500 bg-${c}-500 text-white shadow-sm`;
                    } else {
                        span.className = 'inline-flex items-center gap-2 rounded-lg border-2 px-4 py-2 text-sm font-bold transition-all border-gray-200 bg-white text-gray-500 hover:border-gray-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400';
                    }
                });
            });
        });

        // ── Image preview & 200KB validation ────────────────────────────
        document.querySelectorAll('.insp-img-input').forEach(function (input) {
            input.addEventListener('change', function () {
                const td   = input.closest('td');
                const wrap = td.querySelector('.img-preview-wrap');
                const img  = td.querySelector('.img-preview');
                const err  = td.querySelector('.img-size-error');
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
