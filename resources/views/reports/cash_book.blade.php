@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Daily Cash Book" />

    {{-- Date Range Selector Panel --}}
    <div class="sticky top-[72px] z-[990] mb-6 rounded-xl border border-gray-200 bg-white/95 p-5 shadow-md backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/95"
        style="position: sticky; top: 72px; z-index: 990;" x-data="{ isScrolled: false }"
        @scroll.window="isScrolled = (window.scrollY > 60)">
        <h5 x-show="isScrolled" x-cloak style="display: none;" class="font-bold text-brand-600 dark:text-brand-400 mb-3">
            Daily Cash Book</h5>
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

            @php
                $todayStart = date('Y-m-d');
                $todayEnd = date('Y-m-d');
                $yestStart = date('Y-m-d', strtotime('-1 day'));
                $yestEnd = date('Y-m-d', strtotime('-1 day'));
                $monthStart = date('Y-m-01');
                $monthEnd = date('Y-m-t');

                $isToday = ($startDate === $todayStart && $endDate === $todayEnd);
                $isYesterday = ($startDate === $yestStart && $endDate === $yestEnd);
                $isThisMonth = ($startDate === $monthStart && $endDate === $monthEnd);

                $btnActive = "rounded-lg border border-brand-500 bg-brand-500 px-3 py-2 text-xs font-bold text-white shadow-sm dark:bg-brand-600 dark:border-brand-600 transition-colors";
                $btnInactive = "rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors";
            @endphp
            {{-- Filter Summary Stats Count --}}
            <div class="flex flex-wrap gap-2.5 items-center">
                <div class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50/80 px-3 py-1.5 dark:border-emerald-800/60 dark:bg-emerald-950/40">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Debit:</span>
                    <span class="text-sm font-black text-emerald-700 dark:text-emerald-300 font-mono">{{ number_format($totalInflows, 2) }}</span>
                </div>
                <div class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50/80 px-3 py-1.5 dark:border-rose-800/60 dark:bg-rose-950/40">
                    <span class="text-xs font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-400">Credit:</span>
                    <span class="text-sm font-black text-rose-700 dark:text-rose-300 font-mono">{{ number_format($totalOutflows, 2) }}</span>
                </div>
                <div class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50/80 px-3 py-1.5 dark:border-brand-800/60 dark:bg-brand-950/40">
                    <span class="text-xs font-semibold uppercase tracking-wider text-brand-700 dark:text-brand-400">Net Cash:</span>
                    <span class="text-sm font-black text-brand-800 dark:text-brand-300 font-mono">{{ number_format($netFlow, 2) }}</span>
                </div>
            </div>
        </form>
    </div>

    {{-- Unified Ledger Table --}}
    <div class="overflow-hidden border-2 border-gray-200 rounded-2xl dark:border-gray-800 shadow-md mb-6">
        <table class="w-full text-[18px] text-left text-gray-900 dark:text-gray-100 border-collapse">
            <thead
                class="text-[18px] font-black uppercase tracking-wider bg-brand-600 text-white dark:bg-brand-700 border-b-2 border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-3.5 py-2.5 text-white">Date</th>
                    <th class="px-3.5 py-2.5 text-white">Voucher #</th>
                    <th class="px-3.5 py-2.5 text-white">Type</th>
                    <th class="px-3.5 py-2.5 text-white">Description / Ref</th>
                    <th class="px-2 py-2 text-white w-1 whitespace-nowrap text-center">Unit</th>
                    <th class="px-3.5 py-2.5 text-right text-white">Debit</th>
                    <th class="px-3.5 py-2.5 text-right text-white">Credit</th>
                    <th class="px-3.5 py-2.5 text-right text-white">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-900 dark:text-gray-100 font-bold">
                @forelse($ledgerEntries as $entry)
                    <tr class="{{ !empty($entry['is_opening']) ? 'bg-purple-50/60 dark:bg-purple-950/20' : 'hover:bg-gray-50 dark:hover:bg-white/[0.02]' }} transition-colors">
                        <td class="px-3.5 py-2.5 text-[18px] font-mono font-bold whitespace-nowrap">
                            {{ $entry['date'] instanceof \Carbon\Carbon ? $entry['date']->format('d M Y') : \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}
</td>       
                        <td class="px-3.5 py-2.5 text-[14px] font-black">
                            @if(!empty($entry['model_type']) && !empty($entry['model_id']))
                                @if($entry['model_type'] === 'receiving_voucher')
                                    <a href="{{ route('receiving-vouchers.show', $entry['model_id']) }}"
                                        class="text-brand-600 hover:underline font-black text-[14px] dark:text-brand-400">
                                        {{ $entry['voucher_no'] }}
                                    </a>
                                @elseif($entry['model_type'] === 'general_receiving_voucher')
                                    <a href="{{ route('general-receiving-vouchers.show', $entry['model_id']) }}"
                                        class="text-brand-600 hover:underline font-black text-[14px] dark:text-brand-400">
                                        {{ $entry['voucher_no'] }}
                                    </a>
                                @elseif($entry['model_type'] === 'payment_voucher')
                                    <a href="{{ route('payment-vouchers.show', $entry['model_id']) }}"
                                        class="text-brand-600 hover:underline font-black text-[14px] dark:text-brand-400">
                                        {{ $entry['voucher_no'] }}
                                    </a>
                                @elseif($entry['model_type'] === 'expense')
                                    <a href="{{ route('expenses.show', $entry['model_id']) }}"
                                        class="text-brand-600 hover:underline font-black text-[14px] dark:text-brand-400">
                                        {{ $entry['voucher_no'] }}
                                    </a>
                                @elseif($entry['model_type'] === 'withdrawal')
                                    <a href="{{ route('withdrawals.show', $entry['model_id']) }}"
                                        class="text-brand-600 hover:underline font-black text-[14px] dark:text-brand-400">
                                        {{ $entry['voucher_no'] }}
                                    </a>
                                @elseif($entry['model_type'] === 'jv_voucher')
                                    <a href="{{ route('jv-vouchers.show', $entry['model_id']) }}"
                                        class="text-brand-600 hover:underline font-black text-[14px] dark:text-brand-400">
                                        {{ $entry['voucher_no'] }}
                                    </a>
                                @else
                                    <span class="text-gray-500 font-bold text-[14px] dark:text-gray-400">{{ $entry['voucher_no'] }}</span>
                                @endif
                            @else
                                <span class="text-gray-500 font-bold text-[14px] dark:text-gray-400">{{ $entry['voucher_no'] }}</span>
                            @endif
                        </td>
                        <td class="px-3.5 py-2.5 text-[14px]">
                            @php
                                $typeBadge = match (true) {
                                    !empty($entry['is_opening']) || str_contains($entry['type'], 'Opening') => 'bg-purple-100 text-purple-800 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-300 dark:border-purple-700',
                                    str_contains($entry['type'], 'Receipt') || str_contains($entry['type'], 'Inflow') => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800',
                                    str_contains($entry['type'], 'Expense') => 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800',
                                    str_contains($entry['type'], 'Payout') || str_contains($entry['type'], 'Outflow') => 'bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400 border border-blue-200 dark:border-blue-800',
                                    default => 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 border border-amber-200 dark:border-amber-800',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-[14px] font-black {{ $typeBadge }}">
                                {{ $entry['type'] }}
                            </span>
                        </td>
                        <td class="px-3.5 py-2.5 text-[18px] font-bold">
                            {!! $entry['details'] !!}
                        </td>
                        <td class="px-2 py-2 text-center font-bold whitespace-nowrap w-1 text-[18px]">
                            @if(!empty($entry['unit_number']))
                                <span
                                    class="unit-badge-lg px-2.5 py-0.5 text-[16px] font-black rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/30 dark:text-brand-400 border border-brand-200/60 dark:border-brand-800/40">
                                    Unit {{ $entry['unit_number'] }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td
                            class="px-3.5 py-2.5 text-right font-black text-emerald-600 dark:text-emerald-400 text-[18px]">
                            {{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '—' }}
                        </td>
                        <td
                            class="px-3.5 py-2.5 text-right font-black text-rose-600 dark:text-rose-400 text-[18px]">
                            {{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '—' }}
                        </td>
                        <td
                            class="px-3.5 py-2.5 text-right font-black text-gray-900 dark:text-white text-[18px]">
                            {{ number_format($entry['running_balance'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400 dark:text-gray-600 text-[18px] font-bold">
                            No cash transactions logged for this period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($ledgerEntries->isNotEmpty())
                <tfoot
                    class="bg-gray-200/90 dark:bg-gray-800 border-t-2 border-gray-400 dark:border-gray-600 text-gray-900 dark:text-white font-black">
                    <tr>
                        <td colspan="5"
                            class="px-3.5 py-2.5 text-[18px] uppercase tracking-wider font-black text-gray-900 dark:text-white">
                            Total Summary
                        </td>
                        <td
                            class="px-3.5 py-2.5 text-right text-emerald-600 dark:text-emerald-400 font-black text-[18px]">
                            {{ number_format($totalInflows, 2) }}
                        </td>
                        <td
                            class="px-3.5 py-2.5 text-right text-rose-600 dark:text-rose-400 font-black text-[18px]">
                            {{ number_format($totalOutflows, 2) }}
                        </td>
                        <td class="px-3.5 py-2.5 text-right font-black text-[18px] text-gray-900 dark:text-white">
                            {{ number_format($netFlow, 2) }}
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