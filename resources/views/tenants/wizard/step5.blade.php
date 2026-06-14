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

        <form method="POST" action="{{ route('tenants.saveStep', [$tenant, 5]) }}" class="px-6 py-6 space-y-6">
            @csrf

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
                        <input type="text" name="inspection_member" value="{{ old('inspection_member', $cl?->inspection_member ?? '') }}"
                               placeholder="Name of the inspector" class="{{ $input }} {{ $errors->has('inspection_member') ? 'border-red-400' : '' }}">
                        @error('inspection_member') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            @php
            $sections = [
                '1. General Cleanliness' => [
                    'rooms_cleaned'     => 'All rooms cleaned (floors, walls, ceilings)',
                    'kitchen_cleaned'   => 'Kitchen cleaned (sink, counters, cabinets)',
                    'bathrooms_cleaned' => 'Bathrooms cleaned (toilet, shower, tiles)',
                    'no_garbage'        => 'No garbage left inside unit',
                ],
                '2. Walls, Paint & Fixtures' => [
                    'no_wall_damage'    => 'No damage to walls (holes, cracks, stains)',
                    'paint_condition_ok'=> 'Paint condition acceptable',
                    'light_fixtures_ok' => 'Light fixtures, switches, sockets working',
                    'electric_wiring_ok'=> 'Electric cables and wiring in good condition',
                    'no_breaker_issues' => 'No issues with electricity breakers',
                ],
                '3. Furniture, Appliances & Kitchen' => [
                    'furniture_ok'           => 'Furniture present and in good condition (if provided)',
                    'ac_working'             => 'Air-conditioners working',
                    'kitchen_appliances_ok'  => 'Kitchen appliances working (stove, hob, oven, fridge)',
                    'stove_clean'            => 'Stove / Hob clean and in working condition',
                    'keys_returned'          => 'Keys for all doors, cupboards, mailbox handed over',
                ],
                '4. Doors, Windows & Locks' => [
                    'doors_locks_ok'   => 'All doors and locks working properly',
                    'windows_ok'       => 'Windows not broken, open/close properly',
                    'balcony_doors_ok' => 'Balcony doors / windows secured properly',
                ],
                '5. Utilities & Dues' => [
                    'water_supply_ok'          => 'Water supply working',
                    'electricity_supply_ok'    => 'Electricity supply working',
                    'gas_supply_ok'            => 'Gas supply checked',
                    'no_pending_utility_bills' => 'No pending electricity, water or gas bills',
                    'no_pending_maintenance'   => 'No pending maintenance dues',
                    'no_pending_rent'          => 'No pending rent payments',
                ],
                '7. Stock & Inventory' => [
                    'fixtures_available' => 'All original flat fittings and fixtures available',
                    'no_missing_items'   => 'No missing inventory items',
                ],
                '8. Final' => [
                    'access_cards_returned'  => 'All access cards, parking stickers handed over',
                    'no_pending_requests'    => 'No pending service requests or complaints',
                    'move_out_form_signed'   => 'Tenant signed move-in form',
                ],
            ];
            @endphp

            @foreach($sections as $title => $items)
                <div class="{{ $sectionClass }}">
                    <h4 class="{{ $sectionTitle }}">{{ $title }}</h4>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @foreach($items as $field => $itemLabel)
                            <label class="{{ $checkLabel }}">
                                <input type="checkbox" name="{{ $field }}" value="1" class="{{ $checkboxClass }}"
                                       {{ old($field, $cl->{$field} ?? false) ? 'checked' : '' }}>
                                {{ $itemLabel }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

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
