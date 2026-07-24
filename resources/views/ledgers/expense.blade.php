@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Expense Head Ledger" />

    <x-common.component-card title="Operating Expense Category Ledger" desc="Generate chronological statement of operational expenditures logged under specific expense heads.">
        
        <form action="{{ route('ledgers.expense') }}" method="GET" id="expense-ledger-form">
            <!-- Filters -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4 items-end mb-6">
                <!-- Category Dropdown -->
                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs sm:text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Select Expense Category <span class="text-red-500">*</span>
                    </label>
                    <select name="expense_head_id" onchange="this.form.submit()" required
                        class="w-full rounded-2xl border-2 border-gray-300 bg-white px-5 py-3.5 text-lg font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
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
                    <label class="mb-2 block text-xs sm:text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Date From
                    </label>
                    <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}" placeholder="YYYY-MM-DD" autocomplete="off"
                        class="w-full rounded-2xl border-2 border-gray-300 bg-white px-5 py-3.5 text-lg font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <!-- Date To -->
                <div>
                    <label class="mb-2 block text-xs sm:text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Date To
                    </label>
                    <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}" placeholder="YYYY-MM-DD" autocomplete="off"
                        class="w-full rounded-2xl border-2 border-gray-300 bg-white px-5 py-3.5 text-lg font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-6 mb-6">
                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-3 rounded-2xl bg-brand-600 px-6 py-3.5 text-base font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer">
                        Filter Ledger
                    </button>
                    @if($expenseHeadId || $dateFrom || $dateTo)
                        <a href="{{ route('ledgers.expense') }}"
                            class="rounded-2xl border-2 border-gray-300 px-6 py-3.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>

                @if($ledgerData)
                    <div class="flex items-center gap-3">
                        <!-- Excel Export -->
                        <a href="{{ route('ledgers.expense.excel', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-2xl border-2 border-emerald-300 bg-emerald-50 px-5 py-3.5 text-base font-extrabold text-emerald-700 hover:bg-emerald-100 transition-colors dark:border-emerald-900/40 dark:bg-emerald-950/20 dark:text-emerald-400">
                            🟢 Excel
                        </a>
                        <!-- PDF Export -->
                        <a href="{{ route('ledgers.expense.pdf', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-3.5 text-base font-extrabold text-red-700 hover:bg-red-100 transition-colors dark:border-red-900/40 dark:bg-red-950/20 dark:text-red-400">
                            🔴 PDF
                        </a>
                        <a href="{{ route('ledgers.expense.print', request()->all()) }}"
                            onclick="window.open(this.href,'_blank','width=1100,height=800,scrollbars=yes'); return false;"
                            class="inline-flex items-center gap-2 rounded-2xl border-2 border-gray-300 px-5 py-3.5 text-base font-extrabold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            🖨️ Print
                        </a>
                    </div>
                @endif
            </div>
        </form>

        @if($ledgerData)
            {{-- STICKY BIG HEADING & SUMMARY BANNER --}}
            <div class="sticky mb-6 rounded-2xl border-2 border-amber-500 bg-white dark:bg-gray-900 p-6 shadow-xl backdrop-blur-md"
                style="position: sticky; top: 72px; z-index: 990;">
                
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-600 text-white shadow-md text-3xl font-black">
                            🧾
                        </div>
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                Expense Category Ledger
                            </p>
                            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900 dark:text-white">
                                {{ $ledgerData['head']->name ?? ($ledgerData['expense_head']->name ?? 'Category') }}
                            </h2>
                        </div>
                    </div>

                    <div class="text-right bg-amber-50/80 dark:bg-amber-950/30 p-4 rounded-2xl border border-amber-200 dark:border-amber-800/40 min-w-[240px]">
                        <span class="text-xs font-black uppercase tracking-wider text-amber-700 dark:text-amber-400 block">Total Spent Under Category</span>
                        <span class="text-2xl sm:text-3xl font-black font-mono text-amber-700 dark:text-amber-400">
                            Rs. {{ number_format($ledgerData['summary']['total_amount'], 2) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-hidden border-2 border-gray-200 rounded-2xl dark:border-gray-800 shadow-md">
                <table class="w-full text-base sm:text-lg text-left text-gray-800 dark:text-gray-200">
                    <thead class="text-xs font-black uppercase tracking-wider bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-b-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-5 py-4">Date</th>
                            <th class="px-5 py-4">Voucher #</th>
                            <th class="px-5 py-4">Spent On / Notes</th>
                            <th class="px-5 py-4">Payment Account</th>
                            <th class="px-5 py-4">Reference</th>
                            <th class="px-5 py-4 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                        @forelse($ledgerData['entries'] as $entry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 text-xs font-mono">
                                    {{ $entry['date']->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-xs font-mono font-semibold">
                                    @if(!empty($entry['id']))
                                        <a href="{{ route('expenses.show', $entry['id']) }}" class="text-brand-500 hover:underline">
                                            {{ $entry['voucher_no'] }}
                                        </a>
                                    @else
                                        {{ $entry['voucher_no'] }}
                                    @endif
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
                    @if(count($ledgerData['entries']) > 0)
                        @php
                            $sumAmount = $ledgerData['entries']->sum('amount');
                        @endphp
                        <tfoot class="bg-gray-100/80 dark:bg-gray-800/80 border-t-2 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white font-bold">
                            <tr>
                                <td colspan="5" class="px-5 py-4 text-xs uppercase tracking-wider font-extrabold text-gray-700 dark:text-gray-300">
                                    Total Summary
                                </td>
                                <td class="px-5 py-4 text-right text-rose-600 font-mono font-extrabold text-sm">
                                    Rs. {{ number_format($sumAmount, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
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
