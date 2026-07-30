@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Managing Owner Ledger" />

    <x-common.component-card title="" desc="">

        <form action="{{ route('ledgers.owner') }}" method="GET" id="owner-ledger-form"
            class="sticky top-[72px] z-[990] bg-white/95 dark:bg-gray-900/95 p-4 rounded-2xl border-2 border-brand-500 shadow-xl backdrop-blur-md mb-6">
            <div class="flex flex-wrap items-end gap-3.5">
                <!-- Owner Dropdown -->
                <div class="flex-1 min-w-[240px]">
                    <label
                        class="mb-1.5 block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Select Managing Owner <span class="text-red-500">*</span>
                    </label>
                    <select name="owner_id" onchange="this.form.submit()" required
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-base font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Choose an Owner</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" {{ $ownerId == $owner->id ? 'selected' : '' }}>
                                {{ $owner->name }} ({{ $owner->email ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From -->
                <div class="w-full sm:w-44">
                    <label
                        class="mb-1.5 block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Date From
                    </label>
                    <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}" placeholder="YYYY-MM-DD"
                        autocomplete="off"
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-base font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <!-- Date To -->
                <div class="w-full sm:w-44">
                    <label
                        class="mb-1.5 block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Date To
                    </label>
                    <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}" placeholder="YYYY-MM-DD"
                        autocomplete="off"
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-base font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <!-- Action Buttons: Filter, Clear, Print -->
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-base font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer">
                        Filter
                    </button>
                    @if($ownerId || $dateFrom || $dateTo)
                        <a href="{{ route('ledgers.owner') }}"
                            class="rounded-xl border-2 border-gray-300 px-4 py-2.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            Clear
                        </a>
                    @endif
                    @if($ledgerData)
                        <a href="{{ route('ledgers.owner.print', request()->all()) }}"
                            onclick="window.open(this.href,'_blank','width=1100,height=800,scrollbars=yes'); return false;"
                            class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-base font-extrabold text-white shadow-md hover:bg-gray-800 transition-colors cursor-pointer">
                            🖨️ Print
                        </a>
                    @endif
                </div>
            </div>
        </form>

        @if($ledgerData)

            {{-- Table --}}
            <div class="overflow-hidden border-2 border-gray-200 rounded-2xl dark:border-gray-800 shadow-md">
                <table class="w-full text-base sm:text-lg text-left text-gray-800 dark:text-gray-200">
                    <thead
                        class="text-xs font-black uppercase tracking-wider bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-b-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-5 py-4">Date</th>
                            <th class="px-5 py-4">Voucher #</th>
                            <th class="px-5 py-4">Account</th>
                            <th class="px-5 py-4">Reference</th>
                            <th class="px-5 py-4">Notes</th>
                            <th class="px-5 py-4 text-right">Withdrawal</th>
                            <th class="px-5 py-4 text-right">Deposit</th>
                            <th class="px-5 py-4 text-right">Balance</th>
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
                                            <a href="{{ route('payment-vouchers.show', $entry['id']) }}"
                                                class="text-brand-500 hover:underline font-semibold">
                                                {{ $entry['voucher_no'] }}
                                            </a>
                                        @elseif($entry['type'] === 'receiving_voucher')
                                            <a href="{{ route('receiving-vouchers.show', $entry['id']) }}"
                                                class="text-brand-500 hover:underline font-semibold">
                                                {{ $entry['voucher_no'] }}
                                            </a>
                                        @elseif($entry['type'] === 'withdrawal')
                                            <a href="{{ route('withdrawals.show', $entry['id']) }}"
                                                class="text-brand-500 hover:underline font-semibold">
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
                        <tfoot
                            class="bg-gray-200/90 dark:bg-gray-800 border-t-4 border-gray-400 dark:border-gray-600 text-gray-900 dark:text-white font-black">
                            <tr>
                                <td colspan="5"
                                    class="px-5 py-4 text-base sm:text-lg uppercase tracking-wider font-black text-gray-900 dark:text-white">
                                    Total Summary
                                </td>
                                <td class="px-5 py-4 text-right text-rose-600 font-mono font-black text-lg sm:text-xl">
                                    Rs. {{ number_format($sumDebit, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right text-emerald-600 font-mono font-black text-lg sm:text-xl">
                                    Rs. {{ number_format($sumCredit, 2) }}
                                </td>
                                <td
                                    class="px-5 py-4 text-right font-mono font-black text-lg sm:text-xl text-gray-900 dark:text-white">
                                    Rs. {{ number_format($finalBalance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @else
            <div
                class="p-8 text-center text-gray-400 dark:text-gray-600 bg-gray-50 dark:bg-white/[0.01] border border-dashed border-gray-200 dark:border-gray-800 rounded-xl font-bold">
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