@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Expense Head Ledger" />

    <x-common.component-card title="Operating Expense Category Ledger" desc="Generate chronological statement of operational expenditures logged under specific expense heads.">
        
        <form action="{{ route('ledgers.expense') }}" method="GET" id="expense-ledger-form">
            <!-- Filters -->
            <div class="grid grid-cols-1 gap-5 md:grid-cols-4 items-end mb-6">
                <!-- Category Dropdown -->
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Select Expense Category <span class="text-red-500">*</span>
                    </label>
                    <select name="expense_head_id" onchange="this.form.submit()" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Choose a Category</option>
                        @foreach($heads as $head)
                            <option value="{{ $head->id }}" {{ $expenseHeadId == $head->id ? 'selected' : '' }}>
                                {{ $head->name }} (Code: {{ $head->code ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Date From
                    </label>
                    <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}" placeholder="YYYY-MM-DD" autocomplete="off"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <!-- Date To -->
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Date To
                    </label>
                    <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}" placeholder="YYYY-MM-DD" autocomplete="off"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-5 mb-6">
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        Filter Ledger
                    </button>
                    @if($expenseHeadId || $dateFrom || $dateTo)
                        <a href="{{ route('ledgers.expense') }}"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>

                @if($ledgerData)
                    <div class="flex items-center gap-2">
                        <!-- Excel Export -->
                        <a href="{{ route('ledgers.expense.excel', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors dark:border-emerald-900/30 dark:bg-emerald-950/10 dark:text-emerald-400">
                            🟢 Export Excel
                        </a>
                        <!-- PDF Export -->
                        <a href="{{ route('ledgers.expense.pdf', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-red-300 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100 transition-colors dark:border-red-900/30 dark:bg-red-950/10 dark:text-red-400">
                            🔴 Export PDF
                        </a>
                        <button type="button" onclick="window.print()"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            🖨️ Print
                        </button>
                    </div>
                @endif
            </div>
        </form>

        @if($ledgerData)
            {{-- Summary Card --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                <div class="bg-amber-50/50 dark:bg-amber-950/10 p-5 rounded-xl border border-amber-100 dark:border-amber-900/30">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-500 dark:text-amber-400 font-bold">Total Spent Under Head</span>
                    <span class="block mt-2 text-2xl font-bold text-gray-800 dark:text-white">Rs. {{ number_format($ledgerData['summary']['total_amount'], 2) }}</span>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3.5">Date</th>
                            <th class="px-5 py-3.5">Voucher #</th>
                            <th class="px-5 py-3.5">Spent On / Notes</th>
                            <th class="px-5 py-3.5">Payment Account</th>
                            <th class="px-5 py-3.5">Reference</th>
                            <th class="px-5 py-3.5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                        @forelse($ledgerData['entries'] as $entry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 text-xs font-mono">
                                    {{ $entry['date']->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-xs font-mono font-semibold">
                                    {{ $entry['voucher_no'] }}
                                </td>
                                <td class="px-5 py-3.5 text-xs">
                                    {{ $entry['notes'] }}
                                </td>
                                <td class="px-5 py-3.5 text-xs font-semibold">
                                    {{ $entry['payment_account'] }}
                                </td>
                                <td class="px-5 py-3.5 text-xs font-mono">
                                    {{ $entry['reference'] }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-bold text-rose-600">
                                    Rs. {{ number_format($entry['amount'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-gray-400 dark:text-gray-600">
                                    No expenditures recorded under this expense category.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center text-gray-400 dark:text-gray-600 bg-gray-50 dark:bg-white/[0.01] border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                Please select an Expense Category to generate the ledger statement.
            </div>
        @endif

    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#date_from', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                });

                flatpickr('#date_to', {
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
