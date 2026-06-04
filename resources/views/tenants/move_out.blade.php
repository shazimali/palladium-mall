@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">

    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants and Agreements</a>
        <span>/</span>
        <a href="{{ route('tenants.show', $tenant) }}" class="hover:text-brand-500">{{ $tenant->name }}</a>
        <span>/</span>
        <span class="text-gray-800 dark:text-white/90">Move Out</span>
    </div>

    {{-- Warning banner --}}
    <div class="mb-6 rounded-xl border border-orange-200 bg-orange-50 px-5 py-4 dark:border-orange-800 dark:bg-orange-900/10">
        <div class="flex items-start gap-3">
            <svg class="h-5 w-5 text-orange-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="font-medium text-orange-700 dark:text-orange-400">Move-Out Process</p>
                <p class="mt-0.5 text-sm text-orange-600 dark:text-orange-500">
                    Completing this form will terminate the agreement, mark the unit as vacant, and change tenant status to Inactive.
                </p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white/90">Move-Out Inspection — {{ $tenant->name }}</h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Complete the move-out checklist before releasing the security deposit.</p>
        </div>

        <form method="POST" action="{{ route('tenants.moveOut.store', $tenant) }}" class="px-6 py-6 space-y-6">
            @csrf

            @php
            $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
            $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
            $checkboxClass = 'h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600';
            $checkLabel = 'flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer';
            $sectionClass = 'rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]';
            $sectionTitle = 'mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300';
            @endphp

            {{-- Header --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Inspection Details</h4>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="{{ $label }}">Move-Out Date <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="checklist_date" id="checklist_date"
                                   value="{{ old('checklist_date', now()->format('Y-m-d')) }}"
                                   placeholder="Select move-out date"
                                   class="{{ $input }} pr-10 {{ $errors->has('checklist_date') ? 'border-red-400' : '' }}" readonly>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                        </div>
                        @error('checklist_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Inspection Team Member <span class="text-red-500">*</span></label>
                        <input type="text" name="inspection_member" value="{{ old('inspection_member') }}"
                               placeholder="Name of the inspector" class="{{ $input }} {{ $errors->has('inspection_member') ? 'border-red-400' : '' }}">
                        @error('inspection_member') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            @php
            $sections = [
                '1. General Cleanliness' => [
                    'rooms_cleaned'     => 'All rooms cleaned',
                    'kitchen_cleaned'   => 'Kitchen cleaned',
                    'bathrooms_cleaned' => 'Bathrooms cleaned',
                    'no_garbage'        => 'No garbage left inside',
                ],
                '2. Walls, Paint & Fixtures' => [
                    'no_wall_damage'    => 'No wall damage',
                    'paint_condition_ok'=> 'Paint condition acceptable',
                    'light_fixtures_ok' => 'Light fixtures working',
                    'electric_wiring_ok'=> 'Wiring in good condition',
                    'no_breaker_issues' => 'No breaker issues',
                ],
                '3. Furniture & Appliances' => [
                    'furniture_ok'          => 'Furniture in good condition',
                    'ac_working'            => 'ACs returned / working',
                    'kitchen_appliances_ok' => 'Kitchen appliances returned',
                    'stove_clean'           => 'Stove clean',
                    'keys_returned'         => 'All keys returned',
                ],
                '4. Doors & Windows' => [
                    'doors_locks_ok'   => 'Doors and locks working',
                    'windows_ok'       => 'Windows intact',
                    'balcony_doors_ok' => 'Balcony doors secured',
                ],
                '5. Utilities & Dues' => [
                    'water_supply_ok'          => 'Water supply checked',
                    'electricity_supply_ok'    => 'Electricity checked',
                    'gas_supply_ok'            => 'Gas checked',
                    'no_pending_utility_bills' => 'No pending utility bills',
                    'no_pending_maintenance'   => 'No pending maintenance dues',
                    'no_pending_rent'          => 'No pending rent',
                ],
                '6. Inventory & Final Handover' => [
                    'fixtures_available'     => 'All fixtures available',
                    'no_missing_items'       => 'No missing items',
                    'access_cards_returned'  => 'Access cards returned',
                    'no_pending_requests'    => 'No open service requests',
                    'move_out_form_signed'   => 'Move-out form signed by tenant',
                ],
            ];
            @endphp

            @foreach($sections as $title => $items)
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">{{ $title }}</h4>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach($items as $field => $itemLabel)
                        <label class="{{ $checkLabel }}">
                            <input type="checkbox" name="{{ $field }}" value="1" class="{{ $checkboxClass }}" {{ old($field) ? 'checked' : '' }}>
                            {{ $itemLabel }}
                        </label>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- Damage notes --}}
            <div>
                <label class="{{ $label }}">Damage / Missing Items Notes</label>
                <textarea name="damage_notes" rows="3" placeholder="Describe any damage or missing items..."
                    class="{{ $input }}">{{ old('damage_notes') }}</textarea>
            </div>

            {{-- Inventory notes --}}
            <div>
                <label class="{{ $label }}">Inventory Notes</label>
                <textarea name="inventory_notes" rows="2" placeholder="Inventory notes..."
                    class="{{ $input }}">{{ old('inventory_notes') }}</textarea>
            </div>

            {{-- Final Assessment --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Final Assessment</h4>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="{{ $label }}">Flat Condition</label>
                        <div class="flex gap-4 mt-2">
                            @foreach(['good' => 'Good', 'needs_repair' => 'Needs Repair'] as $val => $lbl)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="flat_condition" value="{{ $val }}"
                                           {{ old('flat_condition') === $val ? 'checked' : '' }}
                                           class="h-4 w-4 text-brand-500 border-gray-300 focus:ring-brand-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="{{ $label }}">Deposit Deduction (PKR)</label>
                        <input type="number" name="deposit_deduction" value="{{ old('deposit_deduction', 0) }}"
                               min="0" step="0.01" class="{{ $input }}">
                        <p class="mt-1 text-xs text-gray-400">Amount to deduct from security deposit for damages</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Final Remarks</label>
                        <textarea name="final_remarks" rows="2" placeholder="Final remarks by inspector..."
                            class="{{ $input }}">{{ old('final_remarks') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Nav --}}
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('tenants.show', $tenant) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Complete Move-Out
                </button>
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
