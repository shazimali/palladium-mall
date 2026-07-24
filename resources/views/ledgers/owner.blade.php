@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Managing Owner Ledger" />

    <x-common.component-card title="Owner Capital Statement" desc="Generate chronological statement of deposits and payouts for managing owners.">
        
        <form action="{{ route('ledgers.owner') }}" method="GET" id="owner-ledger-form">
            <!-- Filters -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4 items-end mb-6">
                <!-- Owner Dropdown -->
                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs sm:text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Select Managing Owner <span class="text-red-500">*</span>
                    </label>
                    <select name="owner_id" onchange="this.form.submit()" required
                        class="w-full rounded-2xl border-2 border-gray-300 bg-white px-5 py-3.5 text-lg font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Choose an Owner</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" {{ $ownerId == $owner->id ? 'selected' : '' }}>
                                {{ $owner->name }} ({{ $owner->email ?? '—' }})
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
                    @if($ownerId || $dateFrom || $dateTo)
                        <a href="{{ route('ledgers.owner') }}"
                            class="rounded-2xl border-2 border-gray-300 px-6 py-3.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>

                @if($ledgerData)
                    <div class="flex items-center gap-3">
                        <!-- Excel Export -->
                        <a href="{{ route('ledgers.owner.excel', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-2xl border-2 border-emerald-300 bg-emerald-50 px-5 py-3.5 text-base font-extrabold text-emerald-700 hover:bg-emerald-100 transition-colors dark:border-emerald-900/40 dark:bg-emerald-950/20 dark:text-emerald-400">
                            🟢 Excel
                        </a>
                        <!-- PDF Export -->
                        <a href="{{ route('ledgers.owner.pdf', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-3.5 text-base font-extrabold text-red-700 hover:bg-red-100 transition-colors dark:border-red-900/40 dark:bg-red-950/20 dark:text-red-400">
                            🔴 PDF
                        </a>
                        <a href="{{ route('ledgers.owner.print', request()->all()) }}"
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
            <div class="sticky mb-6 rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 p-6 shadow-xl backdrop-blur-md"
                style="position: sticky; top: 72px; z-index: 990;">
                
                <div class="mb-4 flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-md text-3xl font-black">
                            👑
                        </div>
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-brand-600 dark:text-brand-400">
                                Managing Owner Ledger
                            </p>
                            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900 dark:text-white">
                                {{ $ledgerData['owner']->name }}
                            </h2>
                        </div>
                    </div>
                </div>

                {{-- Summary Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="bg-blue-50/70 dark:bg-blue-950/20 p-5 rounded-2xl border-2 border-blue-200 dark:border-blue-900/40">
                        <span class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">Total Payouts (Debits)</span>
                        <span class="block mt-2 text-2xl sm:text-3xl font-black font-mono text-gray-900 dark:text-white">Rs. {{ number_format($ledgerData['summary']['total_debit'], 2) }}</span>
                    </div>
                    <div class="bg-green-50/70 dark:bg-green-950/20 p-5 rounded-2xl border-2 border-green-200 dark:border-green-900/40">
                        <span class="text-xs font-black uppercase tracking-wider text-green-600 dark:text-green-400">Total Deposits (Credits)</span>
                        <span class="block mt-2 text-2xl sm:text-3xl font-black font-mono text-green-600 dark:text-green-400">Rs. {{ number_format($ledgerData['summary']['total_credit'], 2) }}</span>
                    </div>
                    <div class="bg-gray-50/70 dark:bg-gray-800/40 p-5 rounded-2xl border-2 border-gray-200 dark:border-gray-700">
                        <span class="text-xs font-black uppercase tracking-wider text-gray-600 dark:text-gray-300">Net Business Balance</span>
                        <span class="block mt-2 text-2xl sm:text-3xl font-black font-mono text-gray-900 dark:text-white">Rs. {{ number_format($ledgerData['summary']['net_balance'], 2) }}</span>
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
                            <th class="px-5 py-4">Account</th>
                            <th class="px-5 py-4">Reference</th>
                            <th class="px-5 py-4">Notes</th>
                            <th class="px-5 py-4 text-right">Debit (Payout)</th>
                            <th class="px-5 py-4 text-right">Credit (Deposit)</th>
                            <th class="px-5 py-4 text-right">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                        @forelse($ledgerData['entries'] as $entry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 text-xs font-mono">
                                    {{ $entry['date']->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-xs font-mono font-semibold">
                                    @if(!empty($entry['type']) && !empty($entry['id']))
                                        @if($entry['type'] === 'payment_voucher')
                                            <a href="{{ route('payment-vouchers.show', $entry['id']) }}" class="text-brand-500 hover:underline font-semibold">
                                                {{ $entry['voucher_no'] }}
                                            </a>
                                        @elseif($entry['type'] === 'receiving_voucher')
                                            <a href="{{ route('receiving-vouchers.show', $entry['id']) }}" class="text-brand-500 hover:underline font-semibold">
                                                {{ $entry['voucher_no'] }}
                                            </a>
                                        @elseif($entry['type'] === 'withdrawal')
                                            <a href="{{ route('withdrawals.show', $entry['id']) }}" class="text-brand-500 hover:underline font-semibold">
                                                {{ $entry['voucher_no'] }}
                                            </a>
                                        @else
                                            {{ $entry['voucher_no'] }}
                                        @endif
                                    @else
                                        {{ $entry['voucher_no'] }}
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-xs">
                                    {{ $entry['account'] }}
                                </td>
                                <td class="px-5 py-3.5 text-xs">
                                    {{ $entry['reference'] }}
                                </td>
                                <td class="px-5 py-3.5 text-xs">
                                    {{ $entry['notes'] }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-rose-600">
                                    {{ $entry['debit'] > 0 ? 'Rs. ' . number_format($entry['debit'], 2) : '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-emerald-600">
                                    {{ $entry['credit'] > 0 ? 'Rs. ' . number_format($entry['credit'], 2) : '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-bold text-gray-900 dark:text-white font-mono">
                                    Rs. {{ number_format($entry['running_balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-gray-400 dark:text-gray-600">
                                    No transaction entries found for the selected owner.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($ledgerData['entries']) > 0)
                        @php
                            $sumDebit = $ledgerData['entries']->sum('debit');
                            $sumCredit = $ledgerData['entries']->sum('credit');
                            $finalBalance = $ledgerData['entries']->last()['running_balance'] ?? 0;
                        @endphp
                        <tfoot class="bg-gray-100/80 dark:bg-gray-800/80 border-t-2 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white font-bold">
                            <tr>
                                <td colspan="5" class="px-5 py-4 text-xs uppercase tracking-wider font-extrabold text-gray-700 dark:text-gray-300">
                                    Total Summary
                                </td>
                                <td class="px-5 py-4 text-right text-rose-600 font-mono font-bold text-sm">
                                    Rs. {{ number_format($sumDebit, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right text-emerald-600 font-mono font-bold text-sm">
                                    Rs. {{ number_format($sumCredit, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right font-mono font-extrabold text-sm text-gray-900 dark:text-white">
                                    Rs. {{ number_format($finalBalance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @else
            <div class="p-8 text-center text-gray-400 dark:text-gray-600 bg-gray-50 dark:bg-white/[0.01] border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                Please select a Managing Owner to generate the ledger statement.
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
