@extends('layouts.app')

@section('content')
    @php
        $typeLabel = $type === 'move_in' ? 'Move In' : 'Move Out';
        $unit = $agreement->unit;
    @endphp

    <x-common.page-breadcrumb pageTitle="Flat Inspection — {{ $typeLabel }}" />

    <div class="mx-auto w-full max-w-4xl">
        <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 px-6 py-4">
                <div>
                    <h2 class="text-lg font-extrabold text-gray-800 dark:text-white/90">
                        {{ $typeLabel === 'Move In' ? '🏠' : '🚪' }} {{ $typeLabel }} Inspection
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Unit / Flat: <strong class="text-gray-700 dark:text-white/80">{{ $unit?->unit_number ?? '—' }}</strong>
                        &nbsp;|&nbsp; Tenant: <strong class="text-gray-700 dark:text-white/80">{{ $agreement->tenant?->name ?? '—' }}</strong>
                    </p>
                </div>
                @if($report)
                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700 dark:bg-green-900/30 dark:text-green-300">Existing Report — Editing</span>
                @endif
            </div>

            <form action="{{ route('flat-inspections.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                <input type="hidden" name="agreement_id" value="{{ $agreement->id }}">
                <input type="hidden" name="type" value="{{ $type }}">

                {{-- Meta Fields --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-400">Inspection Date</label>
                        <input type="date" name="inspected_at" value="{{ old('inspected_at', $report?->inspected_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                               class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-400">Inspector Name</label>
                        <input type="text" name="inspection_member" value="{{ old('inspection_member', $report?->inspection_member) }}"
                               placeholder="Inspector name..."
                               class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-400">Flat Condition</label>
                        <select name="flat_condition" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">— Select —</option>
                            <option value="good" @selected(old('flat_condition', $report?->flat_condition) === 'good')>Good</option>
                            <option value="average" @selected(old('flat_condition', $report?->flat_condition) === 'average')>Average</option>
                            <option value="poor" @selected(old('flat_condition', $report?->flat_condition) === 'poor')>Poor</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-400">Overall Remarks</label>
                    <textarea name="remarks" rows="2" placeholder="Overall remarks..."
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('remarks', $report?->remarks) }}</textarea>
                </div>

                {{-- Inspection Items Table --}}
                <div>
                    <h4 class="text-sm font-extrabold text-gray-800 dark:text-white/90 mb-3 flex items-center gap-2">
                        📋 Inspection Items
                        <span class="text-xs font-normal text-gray-400">({{ $heads->count() }} heads)</span>
                    </h4>

                    @if($heads->isEmpty())
                        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-700 dark:border-yellow-800/30 dark:bg-yellow-500/10 dark:text-yellow-400">
                            ⚠️ No active flat inspection heads found.
                            <a href="{{ route('inspection-heads.create') }}" class="font-bold underline">Add inspection heads</a> first.
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900/40">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Head</th>
                                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-44">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Remarks</th>
                                        <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-44">Image <span class="font-normal text-gray-400">(max 200KB)</span></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($heads as $head)
                                        @php
                                            $existing = $existingItems->get($head->id);
                                            $oldStatus = old("items.{$head->id}.status",
                                                $existing ? ($existing->status === true ? 'pass' : ($existing->status === false ? 'fail' : 'na')) : 'na'
                                            );
                                        @endphp
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/80">{{ $head->name }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex gap-2">
                                                    @foreach(['pass' => ['✅', 'text-green-600', 'border-green-300 bg-green-50'], 'fail' => ['❌', 'text-red-600', 'border-red-300 bg-red-50'], 'na' => ['—', 'text-gray-500', 'border-gray-300 bg-gray-50']] as $val => [$icon, $textClass, $bgClass])
                                                        <label class="flex items-center gap-1 cursor-pointer text-xs font-semibold {{ $textClass }}">
                                                            <input type="radio" name="items[{{ $head->id }}][status]" value="{{ $val }}"
                                                                   @checked($oldStatus === $val)
                                                                   class="accent-brand-500" />
                                                            {{ $icon }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="text" name="items[{{ $head->id }}][remarks]"
                                                       value="{{ old("items.{$head->id}.remarks", $existing?->remarks) }}"
                                                       placeholder="Remarks..."
                                                       class="h-8 w-full rounded-lg border border-gray-300 px-2 text-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/80" />
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($existing?->image_path)
                                                    <div class="mb-1.5 flex items-center gap-2">
                                                        <a href="{{ Storage::url($existing->image_path) }}" target="_blank">
                                                            <img src="{{ Storage::url($existing->image_path) }}" class="h-10 w-10 rounded-lg object-cover border border-gray-200" />
                                                        </a>
                                                        <span class="text-xs text-gray-400">Current</span>
                                                    </div>
                                                @endif
                                                <input type="file" name="items[{{ $head->id }}][image]" accept="image/*"
                                                       class="text-xs text-gray-600 dark:text-gray-400 file:rounded-lg file:border-0 file:bg-brand-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-brand-700 hover:file:bg-brand-100" />
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
                                        {{ old('admin_rating', $report?->admin_rating ?? '') === 'good' ? 'checked' : '' }}
                                        class="w-4 h-4 text-emerald-600 border-gray-300 dark:border-gray-600 focus:ring-emerald-500">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 px-2.5 py-1 text-xs font-black text-emerald-800 dark:text-emerald-300">
                                        ✨ Satisfactory
                                    </span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition-colors shadow-2xs">
                                    <input type="radio" name="admin_rating" value="bad"
                                        {{ old('admin_rating', $report?->admin_rating ?? '') === 'bad' ? 'checked' : '' }}
                                        class="w-4 h-4 text-rose-600 border-gray-300 dark:border-gray-600 focus:ring-rose-500">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-rose-100 dark:bg-rose-900/40 px-2.5 py-1 text-xs font-black text-rose-800 dark:text-rose-300">
                                        ⚠️ Unsatisfactory
                                    </span>
                                </label>
                                <label class="inline-flex items-center gap-2 cursor-pointer p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors shadow-2xs">
                                    <input type="radio" name="admin_rating" value=""
                                        {{ !in_array(old('admin_rating', $report?->admin_rating ?? ''), ['good', 'bad']) ? 'checked' : '' }}
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
                                class="w-full text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">{{ old('admin_remarks', $report?->admin_remarks) }}</textarea>
                        </div>

                        {{-- Admin Photo Upload (Max 200 KB) --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                📷 Attach Admin Feedback Photo (Max 200 KB)
                            </label>

                            @if($report?->admin_photo)
                                <div class="flex items-center gap-3 p-3 rounded-xl border border-brand-200 bg-white dark:bg-gray-800 mb-2">
                                    <img src="{{ $report->admin_photo_url }}" alt="Admin photo" class="h-14 w-14 object-cover rounded-lg border border-gray-200">
                                    <div class="text-xs">
                                        <p class="font-bold text-gray-800 dark:text-gray-200">Current Photo Attached</p>
                                        <label class="inline-flex items-center gap-1.5 mt-1 text-xs text-red-600 font-semibold cursor-pointer">
                                            <input type="checkbox" name="remove_admin_photo" value="1" class="rounded text-red-600 focus:ring-red-500">
                                            <span>Delete this photo</span>
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <input type="file" name="admin_photo" accept="image/*" class="w-full text-xs text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-100 file:text-brand-700 hover:file:bg-brand-200 dark:file:bg-brand-900/30 dark:file:text-brand-300">
                            <span class="text-[10px] text-gray-400 block mt-1">JPEG, PNG, WEBP up to 200 KB</span>
                            @error('admin_photo')
                                <span class="text-xs text-red-500 font-bold block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <a href="{{ route('agreements.show', $agreement) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">Cancel</a>
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-600">
                        Save {{ $typeLabel }} Inspection
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
