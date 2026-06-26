@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-6">
        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('stock-entries.index') }}" class="hover:text-brand-500">Stock Inflows</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">Record Inflow</span>
        </div>

        @if(session('error'))
            <div class="mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-green-900/20 dark:text-red-400">
                {{ session('error') }}
            </div>
        @endif

        <x-common.component-card title="Record Stock Inflow" desc="Add materials to stock inventory. Selecting a Payment Account automatically creates an Expense Voucher.">
            <form action="{{ route('stock-entries.store') }}" method="POST" class="space-y-6"
                x-data="{
                    rows: [
                        { inventory_item_id: '', quantity: 1, unit_price: 0, unit_of_measure: '' }
                    ],
                    items: {{ $items->map(fn($i) => ['id' => $i->id, 'name' => $i->name, 'code' => $i->code, 'unit_of_measure' => $i->unit_of_measure])->toJson() }},
                    addRow() {
                        this.rows.push({ inventory_item_id: '', quantity: 1, unit_price: 0, unit_of_measure: '' });
                    },
                    removeRow(index) {
                        if (this.rows.length > 1) {
                            this.rows.splice(index, 1);
                        } else {
                            this.rows[0] = { inventory_item_id: '', quantity: 1, unit_price: 0, unit_of_measure: '' };
                        }
                    },
                    updateUom(row, itemId) {
                        let matched = this.items.find(i => i.id == itemId);
                        row.unit_of_measure = matched ? matched.unit_of_measure : '';
                    },
                    get grandTotal() {
                        return this.rows.reduce((sum, r) => sum + (parseFloat(r.quantity || 0) * parseFloat(r.unit_price || 0)), 0);
                    }
                }">
                @csrf

                @php
                    $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
                    $label = 'mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400';
                @endphp

                <!-- Parent Fields -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- Date --}}
                    <div>
                        <label class="{{ $label }}">Date <span class="text-red-500">*</span></label>
                        <input type="text" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" placeholder="YYYY-MM-DD" required autocomplete="off"
                               class="{{ $input }}">
                        @error('date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Entry Type --}}
                    <div>
                        <label class="{{ $label }}">Entry Type <span class="text-red-500">*</span></label>
                        <select name="type" required class="{{ $input }}">
                            <option value="IN" {{ old('type') === 'IN' ? 'selected' : '' }}>🟢 Stock Inflow (Purchase/Addition)</option>
                            <option value="ADJUST" {{ old('type') === 'ADJUST' ? 'selected' : '' }}>⚙️ Stock Adjustment / Reconciliation</option>
                        </select>
                        @error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Payment Account --}}
                    <div>
                        <label class="{{ $label }}">Payment Account (Optional)</label>
                        <select name="payment_account_id" class="{{ $input }}">
                            <option value="">No Cash/Bank Outflow (Stock Adjustment)</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('payment_account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->name }} ({{ ucfirst($acc->type) }})
                                </option>
                            @endforeach
                        </select>
                        @error('payment_account_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        <p class="mt-1 text-[11px] text-gray-400 font-semibold leading-relaxed">
                            💡 Selecting a Payment Account automatically creates a corresponding Expense Voucher.
                        </p>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="{{ $label }}">Notes / Particulars</label>
                    <textarea name="notes" placeholder="Specify vendor name, bill reference, or adjustment rationale..." rows="2"
                              class="{{ $input }}">{{ old('notes') }}</textarea>
                    @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Line Items Table -->
                <div class="border-t border-gray-150 pt-5 dark:border-gray-800">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white/95 mb-4 uppercase tracking-wider">Item Details</h3>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 w-1/3">Item <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-3 w-1/8">Unit</th>
                                    <th class="px-4 py-3 w-1/6">Qty <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-3 w-1/6">Unit Price <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-3 w-1/6 text-right">Subtotal</th>
                                    <th class="px-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <template x-for="(row, index) in rows" :key="index">
                                    <tr class="align-middle">
                                        {{-- Item Dropdown --}}
                                        <td class="px-2 py-3">
                                            <select :name="'items['+index+'][inventory_item_id]'" x-model="row.inventory_item_id" required
                                                    @change="updateUom(row, row.inventory_item_id)"
                                                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                                <option value="">Select SKU Item</option>
                                                <template x-for="item in items" :key="item.id">
                                                    <option :value="item.id" x-text="item.name + ' (' + item.code + ')'" :selected="row.inventory_item_id == item.id"></option>
                                                </template>
                                            </select>
                                        </td>

                                        {{-- UOM --}}
                                        <td class="px-4 py-3 text-xs font-mono text-gray-500" x-text="row.unit_of_measure || '—'"></td>

                                        {{-- Qty --}}
                                        <td class="px-2 py-3">
                                            <input type="number" :name="'items['+index+'][quantity]'" x-model.number="row.quantity" required min="0.01" step="any"
                                                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-xs text-right text-gray-850 dark:border-gray-700 dark:bg-gray-900 dark:text-white font-mono">
                                        </td>

                                        {{-- Unit Price --}}
                                        <td class="px-2 py-3">
                                            <input type="number" :name="'items['+index+'][unit_price]'" x-model.number="row.unit_price" required min="0" step="any"
                                                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-xs text-right text-gray-850 dark:border-gray-700 dark:bg-gray-900 dark:text-white font-mono">
                                        </td>

                                        {{-- Subtotal --}}
                                        <td class="px-4 py-3 text-right font-mono text-xs font-bold text-gray-800 dark:text-white"
                                            x-text="'Rs. ' + (parseFloat(row.quantity || 0) * parseFloat(row.unit_price || 0)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
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

                    {{-- Add Item & Grand Total --}}
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-4 border-t border-gray-150 pt-4 dark:border-gray-850">
                        <button type="button" @click="addRow()"
                                class="inline-flex items-center gap-2 rounded-lg border border-brand-500 px-4 py-2 text-xs font-semibold text-brand-500 hover:bg-brand-50 transition-colors dark:hover:bg-brand-950/10">
                            ➕ Add Row
                        </button>

                        <div class="bg-gray-50 dark:bg-white/[0.02] px-6 py-3 rounded-lg border border-gray-150 dark:border-gray-805 text-right">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Purchase Cost:</span>
                            <span class="block mt-1 text-xl font-mono font-extrabold text-brand-600 dark:text-brand-400"
                                  x-text="'Rs. ' + grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('stock-entries.index') }}"
                       class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                        Save Stock Inflow
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
