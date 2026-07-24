@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Payment Account Ledger" />

    <x-common.component-card title="Cash & Bank Account Ledger" desc="Generate chronological statement of inflows and outflows for specific cash/bank accounts.">
        
        <form action="{{ route('ledgers.payment-account') }}" method="GET" id="account-ledger-form">
            <!-- Filters -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4 items-end mb-6">
                <!-- Account Dropdown -->
                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs sm:text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Select Payment Account <span class="text-red-500">*</span>
                    </label>
                    <select name="payment_account_id" onchange="this.form.submit()" required
                        class="w-full rounded-2xl border-2 border-gray-300 bg-white px-5 py-3.5 text-lg font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Choose an Account</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ $accountId == $account->id ? 'selected' : '' }}>
                                {{ $account->name }} ({{ ucfirst($account->type) }})
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
                    @if($accountId || $dateFrom || $dateTo)
                        <a href="{{ route('ledgers.payment-account') }}"
                            class="rounded-2xl border-2 border-gray-300 px-6 py-3.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>

                @if($ledgerData)
                    <div class="flex items-center gap-3">
                        <!-- Excel Export -->
                        <a href="{{ route('ledgers.payment-account.excel', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-2xl border-2 border-emerald-300 bg-emerald-50 px-5 py-3.5 text-base font-extrabold text-emerald-700 hover:bg-emerald-100 transition-colors dark:border-emerald-900/40 dark:bg-emerald-950/20 dark:text-emerald-400">
                            🟢 Excel
                        </a>
                        <!-- PDF Export -->
                        <a href="{{ route('ledgers.payment-account.pdf', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-3.5 text-base font-extrabold text-red-700 hover:bg-red-100 transition-colors dark:border-red-900/40 dark:bg-red-950/20 dark:text-red-400">
                            🔴 PDF
                        </a>
                        <a href="{{ route('ledgers.payment-account.print', request()->all()) }}"
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
                            💳
                        </div>
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-brand-600 dark:text-brand-400">
                                Payment Account Statement
                            </p>
                            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900 dark:text-white">
                                {{ $ledgerData['account']->name }} <span class="text-base font-bold text-gray-500">({{ ucfirst($ledgerData['account']->type) }})</span>
                            </h2>
                        </div>
                    </div>
                </div>

                {{-- Summary Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="bg-green-50/70 dark:bg-green-950/20 p-5 rounded-2xl border-2 border-green-200 dark:border-green-900/40">
                        <span class="text-xs font-black uppercase tracking-wider text-green-600 dark:text-green-400">Total Inflows (Debits)</span>
                        <span class="block mt-2 text-2xl sm:text-3xl font-black font-mono text-green-600 dark:text-green-400">Rs. {{ number_format($ledgerData['summary']['total_inflow'], 2) }}</span>
                    </div>
                    <div class="bg-blue-50/70 dark:bg-blue-950/20 p-5 rounded-2xl border-2 border-blue-200 dark:border-blue-900/40">
                        <span class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">Total Outflows (Credits)</span>
                        <span class="block mt-2 text-2xl sm:text-3xl font-black font-mono text-gray-900 dark:text-white">Rs. {{ number_format($ledgerData['summary']['total_outflow'], 2) }}</span>
                    </div>
                    <div class="bg-gray-50/70 dark:bg-gray-800/40 p-5 rounded-2xl border-2 border-gray-200 dark:border-gray-700">
                        <span class="text-xs font-black uppercase tracking-wider text-gray-600 dark:text-gray-300">Account Running Balance</span>
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
                            <th class="px-5 py-4">Flat/Shop</th>
                            <th class="px-5 py-4">Voucher #</th>
                            <th class="px-5 py-4">Type</th>
                            <th class="px-5 py-4">Description / Ref</th>
                            <th class="px-5 py-4 text-right">Debit (Inflow)</th>
                            <th class="px-5 py-4 text-right">Credit (Outflow)</th>
                            <th class="px-5 py-4 text-right">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                        @forelse($ledgerData['entries'] as $entry)
                            <tr class="{{ !empty($entry['is_opening']) ? 'bg-amber-50/20 dark:bg-amber-950/5 font-medium' : '' }} hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 text-xs font-mono whitespace-nowrap">
                                    {{ $entry['date']->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-xs font-semibold whitespace-nowrap">
                                    @if(!empty($entry['unit_number']))
                                        <span class="unit-badge-lg px-2.5 py-1 text-xs font-bold rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/30 dark:text-brand-400 border border-brand-200/60 dark:border-brand-800/40">
                                            Unit {{ $entry['unit_number'] }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-xs font-mono font-semibold">
                                    @if(!empty($entry['model_type']) && !empty($entry['model_id']))
                                        @if($entry['model_type'] === 'receiving_voucher')
                                            <a href="{{ route('receiving-vouchers.show', $entry['model_id']) }}" class="text-brand-500 hover:underline font-semibold">
                                                {{ $entry['voucher_no'] }}
                                            </a>
                                        @elseif($entry['model_type'] === 'general_receiving_voucher')
                                            <a href="{{ route('general-receiving-vouchers.show', $entry['model_id']) }}" class="text-brand-500 hover:underline font-semibold">
                                                {{ $entry['voucher_no'] }}
                                            </a>
                                        @elseif($entry['model_type'] === 'payment_voucher')
                                            <a href="{{ route('payment-vouchers.show', $entry['model_id']) }}" class="text-brand-500 hover:underline font-semibold">
                                                {{ $entry['voucher_no'] }}
                                            </a>
                                        @elseif($entry['model_type'] === 'expense')
                                            <a href="{{ route('expenses.show', $entry['model_id']) }}" class="text-brand-500 hover:underline font-semibold">
                                                {{ $entry['voucher_no'] }}
                                            </a>
                                        @elseif($entry['model_type'] === 'withdrawal')
                                            <a href="{{ route('withdrawals.show', $entry['model_id']) }}" class="text-brand-500 hover:underline font-semibold">
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
                                    @php
                                        $typeBadge = match($entry['type']) {
                                            'Receipt' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400',
                                            'Payout'  => 'bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400',
                                            default   => 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $typeBadge }}">
                                        {{ $entry['type'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-xs">
                                    {{ $entry['description'] }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-emerald-600">
                                    {{ $entry['debit'] > 0 ? 'Rs. ' . number_format($entry['debit'], 2) : '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-rose-600">
                                    {{ $entry['credit'] > 0 ? 'Rs. ' . number_format($entry['credit'], 2) : '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-bold text-gray-900 dark:text-white font-mono">
                                    Rs. {{ number_format($entry['running_balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-gray-400 dark:text-gray-600">
                                    No transaction entries found for the selected payment account.
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
                                <td class="px-5 py-4 text-right text-emerald-600 font-mono font-bold text-sm">
                                    Rs. {{ number_format($sumDebit, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right text-rose-600 font-mono font-bold text-sm">
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
                Please select a Payment Account to generate the ledger statement.
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
