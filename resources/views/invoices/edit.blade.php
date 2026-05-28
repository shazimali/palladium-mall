@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Invoice — {{ $invoice->invoice_number }}" />

    <x-common.component-card title="Edit Invoice — {{ $invoice->invoice_number }}"
        desc="Only draft invoices can be edited. PDF will be regenerated on save.">
        <form action="{{ route('invoices.update', $invoice) }}" method="POST" id="editInvoiceForm">
            @csrf
            @method('PUT')

            <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                    Invoice Details
                </h4>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Tenant</label>
                        <div
                            class="rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            {{ $invoice->tenant->name }}
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Month</label>
                        <div
                            class="rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            {{ $invoice->month->format('F Y') }}
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Due Date <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="due_date" name="due_date"
                            value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                <div class="mb-4 flex items-center justify-between">
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                        Invoice Items
                    </h4>
                    <button type="button" id="addItemBtn"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600 transition-colors">
                        + Add Item
                    </button>
                </div>

                {{-- Header row --}}
                <div class="grid grid-cols-12 gap-3 px-1 pb-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                    <div class="col-span-5">Description</div>
                    <div class="col-span-3">Type</div>
                    <div class="col-span-3">Amount (Rs.)</div>
                    <div class="col-span-1"></div>
                </div>

                <div id="itemsContainer" class="mt-1 space-y-1">
                    @foreach($invoice->items as $i => $item)
                        <div
                            class="item-row grid grid-cols-12 gap-3 items-center py-2 border-b border-gray-100 dark:border-gray-800/60">
                            <div class="col-span-5">
                                <input type="text" name="items[{{ $i }}][description]" value="{{ $item->description }}"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                            <div class="col-span-3">
                                <select name="items[{{ $i }}][type]"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    @foreach(['rent', 'maintenance', 'electricity', 'water', 'gas', 'fine', 'other'] as $type)
                                        <option value="{{ $type }}" {{ $item->type === $type ? 'selected' : '' }}>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-3">
                                <input type="number" name="items[{{ $i }}][amount]" value="{{ $item->amount }}" min="0"
                                    step="0.01"
                                    class="item-amount w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                            <div class="col-span-1 flex justify-center">
                                <button type="button" onclick="removeItem(this)"
                                    class="inline-flex items-center rounded-lg p-2 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex justify-end">
                    <div class="w-64">
                        <div class="flex items-center justify-between rounded-lg bg-brand-500 px-4 py-3">
                            <span class="text-sm font-bold text-white">Total</span>
                            <span id="invoiceTotal" class="text-lg font-bold text-white">
                                Rs. {{ number_format($invoice->total, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea name="notes" rows="2"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('notes', $invoice->notes) }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Save & Regenerate PDF
                </button>
                <a href="{{ route('invoices.show', $invoice) }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            flatpickr('#due_date', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                allowInput: true,
                disableMobile: true,
            });

            let itemCount = {{ $invoice->items->count() }};

            const types = ['rent', 'maintenance', 'electricity', 'water', 'gas', 'fine', 'other'];

            function itemRowHtml(index) {
                const opts = types.map(t => `<option value="${t}">${t.charAt(0).toUpperCase() + t.slice(1)}</option>`).join('');
                return `
            <div class="item-row grid grid-cols-12 gap-3 items-center py-2 border-b border-gray-100 dark:border-gray-800/60">
                <div class="col-span-5">
                    <input type="text" name="items[${index}][description]" placeholder="Description"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="col-span-3">
                    <select name="items[${index}][type]"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        ${opts}
                    </select>
                </div>
                <div class="col-span-3">
                    <input type="number" name="items[${index}][amount]" min="0" step="0.01"
                        placeholder="0.00"
                        class="item-amount w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="col-span-1 flex justify-center">
                    <button type="button" onclick="removeItem(this)"
                        class="inline-flex items-center rounded-lg p-2 text-red-400 hover:bg-red-50 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>`;
            }

            document.getElementById('addItemBtn').addEventListener('click', function () {
                document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', itemRowHtml(itemCount));
                itemCount++;
                bindAmountListeners();
            });

            window.removeItem = function (btn) {
                btn.closest('.item-row').remove();
                rebuildIndexes();
                calculateTotal();
            };

            function calculateTotal() {
                const total = [...document.querySelectorAll('.item-amount')]
                    .reduce((sum, i) => sum + (parseFloat(i.value) || 0), 0);
                document.getElementById('invoiceTotal').textContent =
                    'Rs. ' + total.toLocaleString('en-PK', { minimumFractionDigits: 2 });
            }

            function bindAmountListeners() {
                document.querySelectorAll('.item-amount').forEach(input => {
                    input.removeEventListener('input', calculateTotal);
                    input.addEventListener('input', calculateTotal);
                });
            }

            function rebuildIndexes() {
                document.querySelectorAll('.item-row').forEach((row, i) => {
                    row.querySelectorAll('input, select').forEach(el => {
                        el.name = el.name.replace(/\[\d+\]/, `[${i}]`);
                    });
                });
                itemCount = document.querySelectorAll('.item-row').length;
            }

            // Init listeners on existing items
            bindAmountListeners();
        });
    </script>
@endpush