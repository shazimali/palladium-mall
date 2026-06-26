@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-6">
        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('gate-passes.index') }}" class="hover:text-brand-500">Gate Passes</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">Create Gate Pass</span>
        </div>

        @if(session('error'))
            <div class="mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-green-900/20 dark:text-red-400">
                {{ session('error') }}
            </div>
        @endif

        <x-common.component-card title="Issue Gate Pass" desc="Record outflows and dispatches of maintenance stock. Outflow quantities will be verified against current stock.">
            <form action="{{ route('gate-passes.store') }}" method="POST" class="space-y-6"
                x-data="{
                    rows: [
                        { inventory_item_id: '', quantity: 1, current_stock: 0, unit_of_measure: '', notes: '' }
                    ],
                    items: {{ $items->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'code' => $i->code, 'current_quantity' => (float)$i->current_quantity, 'unit_of_measure' => $i->unit_of_measure])->toJson() }},
                    addRow() {
                        this.rows.push({ inventory_item_id: '', quantity: 1, current_stock: 0, unit_of_measure: '', notes: '' });
                    },
                    removeRow(index) {
                        if (this.rows.length > 1) {
                            this.rows.splice(index, 1);
                        } else {
                            this.rows[0] = { inventory_item_id: '', quantity: 1, current_stock: 0, unit_of_measure: '', notes: '' };
                        }
                    },
                    updateItemDetails(row, itemId) {
                        let matched = this.items.find(i => i.id == itemId);
                        row.unit_of_measure = matched ? matched.unit_of_measure : '';
                        row.current_stock = matched ? matched.current_quantity : 0;
                    }
                }">
                @csrf

                @php
                    $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
                    $label = 'mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400';
                @endphp

                <!-- Parent Fields -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    {{-- Date --}}
                    <div>
                        <label class="{{ $label }}">Permit Date <span class="text-red-500">*</span></label>
                        <input type="text" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" placeholder="YYYY-MM-DD" required autocomplete="off"
                               class="{{ $input }}">
                        @error('date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Issued To --}}
                    <div class="md:col-span-2">
                        <label class="{{ $label }}">Issued To (Technician / worker) <span class="text-red-500">*</span></label>
                        <input type="text" name="issued_to" value="{{ old('issued_to') }}" placeholder="e.g. Asif (Electrician), Plumbing Team" required
                               class="{{ $input }}">
                        @error('issued_to') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Flat/Shop Ref --}}
                    <div>
                        <label class="{{ $label }}">Flat / Shop Reference (Optional)</label>
                        <select name="unit_id" class="{{ $input }}">
                            <option value="">Common Area / Mall General</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    Flat/Shop: {{ $unit->unit_number }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Purpose --}}
                <div>
                    <label class="{{ $label }}">Purpose <span class="text-red-500">*</span></label>
                    <input type="text" name="purpose" value="{{ old('purpose') }}" placeholder="e.g. Replacing corridor lighting, installing PPR pipes in shop 12" required
                           class="{{ $input }}">
                    @error('purpose') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label class="{{ $label }}">Additional Notes</label>
                    <textarea name="notes" placeholder="Specify any additional checkout authorizations or remarks..." rows="2"
                              class="{{ $input }}">{{ old('notes') }}</textarea>
                    @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Line Items Table -->
                <div class="border-t border-gray-150 pt-5 dark:border-gray-800">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white/95 mb-4 uppercase tracking-wider">Item Dispatches</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 w-1/3">Item <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-3 w-1/8">Unit</th>
                                    <th class="px-4 py-3 w-1/8 text-right">Available stock</th>
                                    <th class="px-4 py-3 w-1/8">Checkout Qty <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-3">Remarks / Usage</th>
                                    <th class="px-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <template x-for="(row, index) in rows" :key="index">
                                    <tr class="align-middle">
                                        {{-- Item Dropdown --}}
                                        <td class="px-2 py-3">
                                            <select :name="'items['+index+'][inventory_item_id]'" x-model="row.inventory_item_id" required
                                                    @change="updateItemDetails(row, row.inventory_item_id)"
                                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                                <option value="">Select SKU Item</option>
                                                <template x-for="item in items" :key="item.id">
                                                    <option :value="item.id" x-text="item.name + ' (' + item.code + ')'" :selected="row.inventory_item_id == item.id"></option>
                                                </template>
                                            </select>
                                        </td>

                                        {{-- UOM --}}
                                        <td class="px-4 py-3 text-xs font-mono text-gray-500" x-text="row.unit_of_measure || '—'"></td>

                                        {{-- Current Stock --}}
                                        <td class="px-4 py-3 text-right font-mono text-xs font-semibold"
                                            :class="row.current_stock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                            x-text="row.current_stock.toFixed(2)">
                                        </td>

                                        {{-- Qty --}}
                                        <td class="px-2 py-3">
                                            <input type="number" :name="'items['+index+'][quantity]'" x-model.number="row.quantity" required min="0.01" step="any"
                                                   :max="row.current_stock"
                                                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-xs text-right text-gray-850 dark:border-gray-700 dark:bg-gray-900 dark:text-white font-mono"
                                                   :class="parseFloat(row.quantity || 0) > parseFloat(row.current_stock) ? 'border-red-400 text-red-600 dark:text-red-400' : ''">
                                        </td>

                                        {{-- Item Notes --}}
                                        <td class="px-2 py-3">
                                            <input type="text" :name="'items['+index+'][notes]'" x-model="row.notes" placeholder="e.g. replacement bulb for corridor 1"
                                                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-xs text-gray-850 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        </td>

                                        {{-- Remove --}}
                                        <td class="px-2 py-3 text-center">
                                            <button type="button" @click="removeRow(index)"
                                                    class="inline-flex items-center rounded-lg p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors">
                                                ❌
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- Add Item --}}
                    <div class="mt-4 border-t border-gray-150 pt-4 dark:border-gray-850">
                        <button type="button" @click="addRow()"
                                class="inline-flex items-center gap-2 rounded-lg border border-brand-500 px-4 py-2 text-xs font-semibold text-brand-500 hover:bg-brand-50 transition-colors dark:hover:bg-brand-950/10">
                            ➕ Add Row
                        </button>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('gate-passes.index') }}"
                       class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                        Save Gate Pass
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                });
            }
        });
    </script>
@endpush
