@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Daily Cash Book Report" />

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
                    <p class="text-xl font-extrabold uppercase tracking-wider text-brand-600 dark:text-brand-400">
                        Daily Cash Book
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
    <div class="overflow-hidden border-2 border-gray-200 rounded-2xl dark:border-gray-800 shadow-md mb-6">
        <table class="w-full text-base sm:text-lg text-left text-gray-900 dark:text-gray-100">
            <thead class="text-xs sm:text-sm font-black uppercase tracking-wider bg-brand-600 text-white dark:bg-brand-700 border-b-2 border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-4 py-3 text-white">Date</th>
                    <th class="px-4 py-3 text-white">Flat/Shop</th>
                    <th class="px-4 py-3 text-white">Voucher #</th>
                    <th class="px-4 py-3 text-white">Type</th>
                    <th class="px-4 py-3 text-white">Description / Ref</th>
                    <th class="px-4 py-3 text-right text-white">Debit (Inflow)</th>
                    <th class="px-4 py-3 text-right text-white">Credit (Outflow)</th>
                    <th class="px-4 py-3 text-right text-white">Running Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-900 dark:text-gray-100 font-bold">
                @forelse($ledgerEntries as $entry)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                        <td class="px-4 py-3 text-sm sm:text-base font-mono font-bold whitespace-nowrap">
                            {{ $entry['date'] instanceof \Carbon\Carbon ? $entry['date']->format('d M Y') : \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm sm:text-base font-bold whitespace-nowrap">
                            @if(!empty($entry['unit_number']))
                                <span class="unit-badge-lg px-2.5 py-0.5 text-xs font-black rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/30 dark:text-brand-400 border border-brand-200/60 dark:border-brand-800/40">
                                    Unit {{ $entry['unit_number'] }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm sm:text-base font-mono font-black">
                            @if(!empty($entry['model_type']) && !empty($entry['model_id']))
                                @if($entry['model_type'] === 'receiving_voucher')
                                    <a href="{{ route('receiving-vouchers.show', $entry['model_id']) }}" class="text-brand-600 hover:underline font-mono font-black dark:text-brand-400">
                                        {{ $entry['voucher_no'] }}
                                    </a>
                                @elseif($entry['model_type'] === 'general_receiving_voucher')
                                    <a href="{{ route('general-receiving-vouchers.show', $entry['model_id']) }}" class="text-brand-600 hover:underline font-mono font-black dark:text-brand-400">
                                        {{ $entry['voucher_no'] }}
                                    </a>
                                @elseif($entry['model_type'] === 'payment_voucher')
                                    <a href="{{ route('payment-vouchers.show', $entry['model_id']) }}" class="text-brand-600 hover:underline font-mono font-black dark:text-brand-400">
                                        {{ $entry['voucher_no'] }}
                                    </a>
                                @elseif($entry['model_type'] === 'expense')
                                    <a href="{{ route('expenses.show', $entry['model_id']) }}" class="text-brand-600 hover:underline font-mono font-black dark:text-brand-400">
                                        {{ $entry['voucher_no'] }}
                                    </a>
                                @elseif($entry['model_type'] === 'withdrawal')
                                    <a href="{{ route('withdrawals.show', $entry['model_id']) }}" class="text-brand-600 hover:underline font-mono font-black dark:text-brand-400">
                                        {{ $entry['voucher_no'] }}
                                    </a>
                                @elseif($entry['model_type'] === 'jv_voucher')
                                    <a href="{{ route('jv-vouchers.show', $entry['model_id']) }}" class="text-brand-600 hover:underline font-mono font-black dark:text-brand-400">
                                        {{ $entry['voucher_no'] }}
                                    </a>
                                @else
                                    <span class="font-mono text-gray-500 font-bold dark:text-gray-400">{{ $entry['voucher_no'] }}</span>
                                @endif
                            @else
                                <span class="font-mono text-gray-500 font-bold dark:text-gray-400">{{ $entry['voucher_no'] }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm sm:text-base">
                            @php
                                $typeBadge = match(true) {
                                    str_contains($entry['type'], 'Receipt') || str_contains($entry['type'], 'Inflow') => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800',
                                    str_contains($entry['type'], 'Payout') || str_contains($entry['type'], 'Outflow')  => 'bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400 border border-blue-200 dark:border-blue-800',
                                    default => 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 border border-amber-200 dark:border-amber-800',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-lg px-2 py-0.5 text-xs font-black {{ $typeBadge }}">
                                {{ $entry['type'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm sm:text-base font-bold">
                            {!! $entry['details'] !!}
                        </td>
                        <td class="px-4 py-3 text-right font-black text-emerald-600 dark:text-emerald-400 text-sm sm:text-base font-mono">
                            {{ $entry['debit'] > 0 ? 'Rs. ' . number_format($entry['debit'], 2) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-black text-rose-600 dark:text-rose-400 text-sm sm:text-base font-mono">
                            {{ $entry['credit'] > 0 ? 'Rs. ' . number_format($entry['credit'], 2) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-black text-gray-900 dark:text-white font-mono text-base sm:text-lg">
                            Rs. {{ number_format($entry['running_balance'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400 dark:text-gray-600 text-base font-bold">
                            No cash transactions logged for this period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($ledgerEntries->isNotEmpty())
                <tfoot class="bg-gray-200/90 dark:bg-gray-800 border-t-4 border-gray-400 dark:border-gray-600 text-gray-900 dark:text-white font-black">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-base sm:text-lg uppercase tracking-wider font-black text-gray-900 dark:text-white">
                            Total Summary
                        </td>
                        <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400 font-mono font-black text-base sm:text-lg">
                            Rs. {{ number_format($totalInflows, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right text-rose-600 dark:text-rose-400 font-mono font-black text-base sm:text-lg">
                            Rs. {{ number_format($totalOutflows, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-black text-lg sm:text-xl text-gray-900 dark:text-white">
                            Rs. {{ number_format($netFlow, 2) }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
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