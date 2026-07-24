@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Cash Book Report" />

    {{-- STICKY CASH BOOK LEDGER HEADER --}}
    <div class="sticky mb-6 rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 p-5 shadow-xl backdrop-blur-md"
        style="position: sticky; top: 72px; z-index: 990;">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <div
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-500 text-white shadow-md text-3xl font-black">
                    💵
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-brand-600 dark:text-brand-400">
                        Cash Book Ledger
                    </p>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900 dark:text-white mt-0.5">
                        {{ date('d M Y', strtotime($startDate)) }} — {{ date('d M Y', strtotime($endDate)) }}
                    </h2>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                <div class="text-right">
                    <span
                        class="text-xs font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block">Cash
                        Receipts</span>
                    <span class="text-xl sm:text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                        Rs. {{ number_format($totalInflows) }}
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-red-600 dark:text-red-400 block">Cash
                        Payments</span>
                    <span class="text-xl sm:text-2xl font-black font-mono text-red-600 dark:text-red-400">
                        Rs. {{ number_format($totalOutflows) }}
                    </span>
                </div>
                <div class="text-right">
                    <span
                        class="text-xs font-extrabold uppercase tracking-wider text-brand-600 dark:text-brand-400 block">Net
                        Cash</span>
                    <span
                        class="text-xl sm:text-2xl font-black font-mono {{ ($totalInflows - $totalOutflows) >= 0 ? 'text-brand-600 dark:text-brand-400' : 'text-red-600 dark:text-red-400' }}">
                        Rs. {{ number_format($totalInflows - $totalOutflows) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Date Range Selector Panel --}}
    <div
        class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <form action="{{ route('reports.cash-book') }}" method="GET"
            class="flex flex-col gap-4 sm:flex-row sm:items-end justify-between">
            <div class="flex flex-col sm:flex-row gap-4 flex-1">
                @php
                    $dateInput = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 w-full sm:w-48';
                    $dateLabel = 'mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400';
                @endphp
                <div>
                    <label class="{{ $dateLabel }}">Start Date</label>
                    <input type="text" id="start_date" name="start_date" value="{{ $startDate }}" placeholder="YYYY-MM-DD"
                        autocomplete="off" class="{{ $dateInput }}" />
                </div>
                <div>
                    <label class="{{ $dateLabel }}">End Date</label>
                    <input type="text" id="end_date" name="end_date" value="{{ $endDate }}" placeholder="YYYY-MM-DD"
                        autocomplete="off" class="{{ $dateInput }}" />
                </div>
                <div class="flex gap-2 items-end w-full sm:w-auto">
                    <button type="submit"
                        class="h-10 rounded-lg bg-brand-500 px-5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors w-full sm:w-auto">
                        Apply Filter
                    </button>
                    @if($ledgerEntries->isNotEmpty())
                        <a href="{{ route('reports.cash-book.print', request()->all()) }}"
                            onclick="window.open(this.href,'_blank','width=1100,height=800,scrollbars=yes'); return false;"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors w-full sm:w-auto">
                            🖨️ Print
                        </a>
                    @endif
                </div>

            </div>

            <div class="flex gap-2">
                <a href="{{ route('reports.cash-book', ['start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d')]) }}"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    Today
                </a>
                <a href="{{ route('reports.cash-book', ['start_date' => date('Y-m-d', strtotime('-1 day')), 'end_date' => date('Y-m-d', strtotime('-1 day'))]) }}"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    Yesterday
                </a>
                <a href="{{ route('reports.cash-book', ['start_date' => date('Y-m-01'), 'end_date' => date('Y-m-t')]) }}"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    This Month
                </a>
            </div>
        </form>
    </div>

    {{-- Unified Ledger Table --}}
    <div
        class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] mb-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white/90">
                Ledger Statement / Cash Transaction History
            </h3>
            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                {{ $ledgerEntries->count() }} Cash Transactions Found
            </span>
        </div>

        <div class="overflow-x-auto border border-gray-100 rounded-lg dark:border-gray-800">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Voucher #</th>
                        <th class="px-4 py-3">Details / Reference</th>
                        <th class="px-4 py-3 text-right text-red-600 dark:text-red-400">Debit (Outflow)</th>
                        <th class="px-4 py-3 text-right text-green-600 dark:text-green-400">Credit (Inflow)</th>
                        <th class="px-4 py-3 text-right font-semibold">Running Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                    @forelse($ledgerEntries as $entry)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-4 py-3 text-xs font-mono">
                                {{ $entry['date'] instanceof \Carbon\Carbon ? $entry['date']->format('d M Y') : \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-xs font-mono font-semibold">
                                @if(!empty($entry['model_type']) && !empty($entry['model_id']))
                                    @if($entry['model_type'] === 'receiving_voucher')
                                        <a href="{{ route('receiving-vouchers.show', $entry['model_id']) }}"
                                            class="text-brand-500 hover:underline">
                                            {{ $entry['voucher_no'] }}
                                        </a>
                                    @elseif($entry['model_type'] === 'general_receiving_voucher')
                                        <a href="{{ route('general-receiving-vouchers.show', $entry['model_id']) }}"
                                            class="text-brand-500 hover:underline">
                                            {{ $entry['voucher_no'] }}
                                        </a>
                                    @elseif($entry['model_type'] === 'payment_voucher')
                                        <a href="{{ route('payment-vouchers.show', $entry['model_id']) }}"
                                            class="text-brand-500 hover:underline">
                                            {{ $entry['voucher_no'] }}
                                        </a>
                                    @elseif($entry['model_type'] === 'expense')
                                        <a href="{{ route('expenses.show', $entry['model_id']) }}"
                                            class="text-brand-500 hover:underline">
                                            {{ $entry['voucher_no'] }}
                                        </a>
                                    @endif
                                @else
                                    {{ $entry['voucher_no'] }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs font-medium">
                                {!! $entry['details'] !!}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-red-600 dark:text-red-400">
                                {{ $entry['debit'] > 0 ? 'Rs. ' . number_format($entry['debit'], 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-green-600 dark:text-green-400">
                                {{ $entry['credit'] > 0 ? 'Rs. ' . number_format($entry['credit'], 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white font-mono">
                                Rs. {{ number_format($entry['running_balance'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-gray-600">
                                No ledger cash transactions logged for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($ledgerEntries->isNotEmpty())
                    <tfoot class="bg-gray-50 dark:bg-gray-800 font-bold">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-gray-700 dark:text-gray-300 text-right">Totals:</td>
                            <td class="px-4 py-3 text-right text-red-600 dark:text-red-400">
                                Rs. {{ number_format($totalOutflows, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">
                                Rs. {{ number_format($totalInflows, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-blue-600 dark:text-blue-400 font-mono">
                                Rs. {{ number_format($netFlow, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#start_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                });

                flatpickr('#end_date', {
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