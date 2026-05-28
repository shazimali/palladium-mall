@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Generate Invoice" />

    <x-common.component-card title="Generate Invoice"
        desc="Select a tenant and month — items are auto-pulled from existing records">
        <form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
            @csrf

            {{-- Tenant + Month + Due Date --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                    Invoice Details
                </h4>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    {{-- Tenant --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tenant <span class="text-red-500">*</span>
                        </label>
                        <select id="tenant_id" name="tenant_id"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('tenant_id') ? 'border-red-400' : '' }}">
                            <option value="">Select tenant</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                    {{ $tenant->name }} — {{ $tenant->unit->unit_number ?? '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('tenant_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Month --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Billing Month <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="month" name="month" value="{{ old('month') }}" placeholder="Select month"
                            autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('month') ? 'border-red-400' : '' }}">
                        @error('month')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Due Date --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Due Date <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="due_date" name="due_date" value="{{ old('due_date') }}"
                            placeholder="Select due date" autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('due_date') ? 'border-red-400' : '' }}">
                        @error('due_date')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Pull Items Banner/Section --}}
                <div class="mt-5 rounded-lg border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-900/20 dark:bg-brand-950/10">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 rounded-full bg-brand-100 p-1.5 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Auto-Pull Records</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Pulls existing payment and utility records for the selected tenant and month.
                                </p>
                            </div>
                        </div>
                        <button type="button" id="pullItemsBtn"
                            class="inline-flex items-center justify-center gap-2 shrink-0 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Pull Items
                        </button>
                    </div>
                </div>
            </div>

            {{-- Invoice Items --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                <div class="mb-4 flex items-center justify-between">
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                        Invoice Items
                    </h4>
                    <button type="button" id="addItemBtn"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Item
                    </button>
                </div>

                @error('items')
                    <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
                @enderror

                {{-- Header row --}}
                <div class="grid grid-cols-12 gap-3 px-1 pb-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                    <div class="col-span-5">Description</div>
                    <div class="col-span-3">Type</div>
                    <div class="col-span-3">Amount (Rs.)</div>
                    <div class="col-span-1"></div>
                </div>

                <div id="itemsContainer" class="mt-1 space-y-1">
                    {{-- Items injected here by JS --}}
                </div>

                {{-- Total --}}
                <div class="mt-4 flex justify-end">
                    <div class="w-64">
                        <div class="flex items-center justify-between rounded-lg bg-brand-500 px-4 py-3">
                            <span class="text-sm font-bold text-white">Total</span>
                            <span id="invoiceTotal" class="text-lg font-bold text-white">Rs. 0</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                <textarea name="notes" rows="2" placeholder="Optional notes to appear on the invoice..."
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Generate Invoice
                </button>
                <a href="{{ route('invoices.index') }}"
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

            flatpickr('#month', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F Y',
                allowInput: false,
                disableMobile: true,
                disable: [function (date) { return date.getDate() !== 1; }],
            });

            flatpickr('#due_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
            });

            // ── Item row template ─────────────────────────────────────────────
            function itemRowHtml(index, item = {}) {
                const types = ['rent', 'maintenance', 'electricity', 'water', 'gas', 'fine', 'other'];
                const typeOptions = types.map(t =>
                    `<option value="${t}" ${item.type === t ? 'selected' : ''}>${t.charAt(0).toUpperCase() + t.slice(1)}</option>`
                ).join('');

                return `
            <div class="item-row grid grid-cols-12 gap-3 items-center py-2 border-b border-gray-100 dark:border-gray-800/60">
                <div class="col-span-5">
                    <input type="text" name="items[${index}][description]"
                        value="${item.description || ''}"
                        placeholder="Description"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="col-span-3">
                    <select name="items[${index}][type]"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        ${typeOptions}
                    </select>
                </div>
                <div class="col-span-3">
                    <input type="number" name="items[${index}][amount]"
                        value="${item.amount || ''}"
                        min="0" step="0.01" placeholder="0.00"
                        class="item-amount w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>
                <div class="col-span-1 flex justify-center">
                    <button type="button" onclick="removeItem(this)"
                        class="inline-flex items-center rounded-lg p-2 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>`;
            }

            // ── Add item manually ─────────────────────────────────────────────
            let itemCount = 0;

            document.getElementById('addItemBtn').addEventListener('click', function () {
                const container = document.getElementById('itemsContainer');
                container.insertAdjacentHTML('beforeend', itemRowHtml(itemCount));
                itemCount++;
                bindAmountListeners();
            });

            // ── Remove item ───────────────────────────────────────────────────
            window.removeItem = function (btn) {
                btn.closest('.item-row').remove();
                rebuildIndexes();
                calculateTotal();
            };

            // ── Pull items from records ───────────────────────────────────────
            document.getElementById('pullItemsBtn').addEventListener('click', function () {
                const tenantId = document.getElementById('tenant_id').value;
                const month = document.getElementById('month')._flatpickr?.input?.value
                    || document.getElementById('month').value;

                if (!tenantId || !month) {
                    alert('Please select a tenant and billing month first.');
                    return;
                }

                this.textContent = 'Loading...';
                this.disabled = true;

                fetch(`/ajax/invoice-items?tenant_id=${tenantId}&month=${month}`)
                    .then(r => r.json())
                    .then(data => {
                        const container = document.getElementById('itemsContainer');
                        container.innerHTML = '';
                        itemCount = 0;

                        if (data.items.length === 0) {
                            container.innerHTML = `<p class="text-sm text-gray-400 py-4 text-center">No payment or utility records found for this tenant and month. Add items manually.</p>`;
                        } else {
                            data.items.forEach(item => {
                                container.insertAdjacentHTML('beforeend', itemRowHtml(itemCount, item));
                                itemCount++;
                            });
                            bindAmountListeners();
                            calculateTotal();
                        }
                    })
                    .finally(() => {
                        this.textContent = 'Pull Items from Records';
                        this.disabled = false;
                    });
            });

            // ── Calculate total ───────────────────────────────────────────────
            function calculateTotal() {
                const amounts = [...document.querySelectorAll('.item-amount')]
                    .map(i => parseFloat(i.value) || 0);
                const total = amounts.reduce((a, b) => a + b, 0);
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
        });
    </script>
@endpush