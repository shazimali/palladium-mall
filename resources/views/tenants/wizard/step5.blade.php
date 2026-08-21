@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">

    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants and Agreements</a>
        <span>/</span>
        <span class="text-gray-800 dark:text-white/90">{{ $title }}</span>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @include('tenants.wizard._progress', ['currentStep' => $step, 'tenantId' => $tenant->id])
    @include('tenants.wizard._tenant_banner')

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white/90">Step 5 — Move-in Inspection</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Complete the move-in inspection checklist before handing over the unit.</p>
            </div>
            <div>
                <a href="{{ route('tenants.printStep', [$tenant, 5]) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print Checklist for Client
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('tenants.saveStep', [$tenant, 5]) }}"
              enctype="multipart/form-data"
              class="px-6 py-6 space-y-6">
            @csrf

            @if(!empty($prefilledFromVacant) && $flatInspectionReport)
                <div class="rounded-xl border border-blue-200 bg-blue-50/80 p-4 text-xs text-blue-900 dark:border-blue-900/50 dark:bg-blue-900/20 dark:text-blue-300 flex items-start gap-3">
                    <span class="text-base">ℹ️</span>
                    <div>
                        <strong class="font-bold">Auto Pre-Filled from Vacant Inspection:</strong>
                        Checklist items and condition have been pre-filled from the vacant flat inspection conducted on <strong>{{ $flatInspectionReport->inspected_at?->format('d M Y') ?? 'earlier' }}</strong>.
                        Saving this form will create a new <strong>Move-In Inspection</strong> linked to this agreement while preserving the previous vacant inspection in the unit's history.
                    </div>
                </div>
            @endif

            @php
            $cl = $checklist;
            $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
            $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
            $checkboxClass = 'h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600';
            $checkLabel = 'flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer';
            $sectionClass = 'rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]';
            $sectionTitle = 'mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300';
            @endphp

            {{-- Header fields --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Inspection Details</h4>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="{{ $label }}">Inspection Date <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="checklist_date" id="checklist_date"
                                   value="{{ old('checklist_date', optional($cl?->checklist_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                                   placeholder="Select inspection date"
                                   class="{{ $input }} pr-10 {{ $errors->has('checklist_date') ? 'border-red-400' : '' }}" readonly>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                        </div>
                        @error('checklist_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Inspection Team Member <span class="text-red-500">*</span></label>
                        <select name="inspection_person_id" class="{{ $input }} {{ $errors->has('inspection_person_id') ? 'border-red-400' : '' }}" required>
                            <option value="">Select Inspector</option>
                            @foreach($inspectionPersons as $person)
                                <option value="{{ $person->id }}" {{ old('inspection_person_id', $cl?->inspection_person_id ?? '') == $person->id ? 'selected' : '' }}>
                                    {{ $person->name }} ({{ $person->designation ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @error('inspection_person_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ⚡ Electricity Breaker & Initial Meter Inspection --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }} flex items-center gap-2">
                    <span>⚡ Electricity Breaker &amp; Initial Meter Inspection</span>
                </h4>
                <p class="text-xs text-gray-500 mb-4">Record initial meter reading, officer verification statement, photo proof, and upload the signed handover form to safely switch breaker <strong>ON</strong> for tenant move-in.</p>
                
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="{{ $label }}">Initial Meter Reading (kWh) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="meter_reading" value="{{ old('meter_reading', $defaultMeterReading ?? '') }}" placeholder="e.g. 14850.50" class="{{ $input }}" required>
                        @error('meter_reading') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Meter Reading Photo Proof</label>
                        <input type="file" name="meter_image" accept="image/*" class="{{ $input }}">
                        @error('meter_image') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Inspection Officer <span class="text-red-500">*</span></label>
                        <select name="inspection_person_id" class="{{ $input }}" required>
                            <option value="">Select Inspection Officer</option>
                            @foreach($inspectionPersons as $person)
                                <option value="{{ $person->id }}" {{ old('inspection_person_id', $cl?->inspection_person_id ?? '') == $person->id ? 'selected' : '' }}>
                                    {{ $person->name }} ({{ $person->role ?? $person->designation ?? 'Inspector' }})
                                </option>
                            @endforeach
                        </select>
                        @error('inspection_person_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Upload Signed Inspection PDF / Form</label>
                        <input type="file" name="signed_inspection_doc" accept="application/pdf,image/*" class="{{ $input }}">
                        <p class="mt-1 text-xs text-gray-400">Download/print handover PDF, get physical signatures, then upload here.</p>
                        @error('signed_inspection_doc') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Officer Statement / Handover Verification <span class="text-red-500">*</span></label>
                        <textarea name="officer_statement" rows="2" class="{{ $input }}" required>I inspect and confirm initial electricity meter reading and switch breaker ON for tenant move-in.</textarea>
                        @error('officer_statement') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════
                 DYNAMIC FLAT INSPECTION CHECKLIST (InspectionHead)
            ══════════════════════════════════════════════════════ --}}
            @if($inspectionHeads->isEmpty())
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800 dark:border-amber-800/40 dark:bg-amber-900/20 dark:text-amber-300">
                    ⚠️ No inspection checklist items found. Please add <strong>🏠 Flat Inspection</strong> heads via
                    <a href="{{ route('inspection-heads.index') }}" class="underline font-semibold" target="_blank">Inspection Heads</a>.
                </div>
            @else
                <div class="{{ $sectionClass }}">
                    <h4 class="{{ $sectionTitle }}">🏠 Flat Inspection Checklist</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Mark each item as Pass or Fail, add a comment if needed, and optionally upload a photo (max 2 MB).</p>

                    <div class="space-y-3">
                        @foreach($inspectionHeads as $head)
                            @php
                                $existing      = $flatInspectionReport?->items->firstWhere('inspection_head_id', $head->id);
                                $savedStatus   = $existing?->status;
                                $currentStatus = old("head_{$head->id}_status",
                                    $savedStatus === true  ? 'pass' :
                                    ($savedStatus === false ? 'fail' : ''));
                            @endphp

                            <div class="rounded-xl border {{ $currentStatus === 'pass' ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-800/40 dark:bg-emerald-900/10' : ($currentStatus === 'fail' ? 'border-rose-200 bg-rose-50/50 dark:border-rose-800/40 dark:bg-rose-900/10' : 'border-gray-100 bg-white dark:border-gray-800 dark:bg-white/[0.02]') }} p-4 transition-colors">

                            {{-- Row header: item name + pass/fail checkboxes --}}
                                <div class="flex items-start justify-between gap-4 flex-wrap">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-white/90 flex-1">
                                        <span class="text-gray-400 mr-1">{{ $loop->iteration }}.</span> {{ $head->name }}
                                    </p>
                                    <div class="flex items-center gap-5 shrink-0">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox"
                                                   id="head_{{ $head->id }}_pass"
                                                   name="head_{{ $head->id }}_status"
                                                   value="pass"
                                                   {{ $currentStatus === 'pass' ? 'checked' : '' }}
                                                   onchange="toggleInspectionStatus(this, 'head_{{ $head->id }}_fail')"
                                                   class="h-4 w-4 rounded text-emerald-500 border-gray-300 focus:ring-emerald-400">
                                            <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">✅ Pass</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox"
                                                   id="head_{{ $head->id }}_fail"
                                                   name="head_{{ $head->id }}_status"
                                                   value="fail"
                                                   {{ $currentStatus === 'fail' ? 'checked' : '' }}
                                                   onchange="toggleInspectionStatus(this, 'head_{{ $head->id }}_pass')"
                                                   class="h-4 w-4 rounded text-rose-500 border-gray-300 focus:ring-rose-400">
                                            <span class="text-sm font-semibold text-rose-700 dark:text-rose-400">❌ Fail</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- Comment --}}
                                <textarea name="head_{{ $head->id }}_comment"
                                          rows="1"
                                          placeholder="Comment (optional)..."
                                          class="mt-2 w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-1.5 text-xs text-gray-700 dark:text-gray-300 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500/30">{{ old("head_{$head->id}_comment", $existing?->remarks) }}</textarea>

                                {{-- Image upload + existing thumb --}}
                                <div class="mt-2 flex items-center gap-3">
                                    @if($existing?->image_path)
                                        <a href="{{ Storage::url($existing->image_path) }}" target="_blank" class="shrink-0">
                                            <img src="{{ Storage::url($existing->image_path) }}"
                                                 alt="Inspection photo"
                                                 class="h-14 w-14 rounded-lg object-cover border border-gray-200 dark:border-gray-700 shadow-xs hover:opacity-80 transition-opacity">
                                        </a>
                                    @endif
                                    <label class="flex-1 cursor-pointer">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $existing?->image_path ? 'Replace photo' : 'Upload photo' }} (optional, max 200 KB)</span>
                                        <input type="file"
                                               name="head_{{ $head->id }}_image"
                                               accept="image/*"
                                               class="mt-1 block w-full text-xs text-gray-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300">
                                    </label>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Damage Notes --}}
            <div>
                <label class="{{ $label }}">6. Damage / Missing Items Notes</label>
                <textarea name="damage_notes" rows="3" placeholder="Describe any damage or missing items..."
                    class="{{ $input }}">{{ old('damage_notes', $cl?->damage_notes ?? '') }}</textarea>
            </div>

            {{-- Inventory Notes --}}
            <div>
                <label class="{{ $label }}">Inventory Notes</label>
                <textarea name="inventory_notes" rows="2" placeholder="Any inventory notes..."
                    class="{{ $input }}">{{ old('inventory_notes', $cl?->inventory_notes ?? '') }}</textarea>
            </div>

            {{-- Flat Condition + Final Remarks --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Final Assessment</h4>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="{{ $label }}">Flat Condition</label>
                        <div class="flex gap-4 mt-2">
                            @foreach(['good' => 'Good', 'needs_repair' => 'Needs Repair'] as $val => $lbl)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="flat_condition" value="{{ $val }}"
                                           {{ old('flat_condition', $cl?->flat_condition ?? '') === $val ? 'checked' : '' }}
                                           class="h-4 w-4 text-brand-500 border-gray-300 focus:ring-brand-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Final Remarks</label>
                        <textarea name="final_remarks" rows="2" placeholder="Final remarks by inspector..."
                            class="{{ $input }}">{{ old('final_remarks', $cl?->final_remarks ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Nav --}}
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('tenants.showStep', [$tenant, 4]) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
                <div class="flex items-center gap-3">
                    <button type="submit" name="save_only" value="1"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Save Only
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                        Continue — Review
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@once
@push('scripts')
<script>
// Mutual-exclusion: checking Pass unchecks Fail and vice versa
function toggleInspectionStatus(changedBox, otherId) {
    if (changedBox.checked) {
        const other = document.getElementById(otherId);
        if (other) other.checked = false;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const dateEl = document.getElementById('checklist_date');
    if (dateEl && typeof flatpickr !== 'undefined') {
        flatpickr(dateEl, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            disableMobile: true,
            allowInput: false,
        });
    }
});
</script>
@endpush
@endonce
