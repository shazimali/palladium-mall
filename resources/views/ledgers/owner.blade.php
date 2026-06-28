@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Managing Owner Ledger" />

    <x-common.component-card title="Owner Capital Statement" desc="Generate chronological statement of deposits and payouts for managing owners.">
        
        <form action="{{ route('ledgers.owner') }}" method="GET" id="owner-ledger-form">
            <!-- Filters -->
            <div class="grid grid-cols-1 gap-5 md:grid-cols-4 items-end mb-6">
                <!-- Owner Dropdown -->
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Select Managing Owner <span class="text-red-500">*</span>
                    </label>
                    <select name="owner_id" onchange="this.form.submit()" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
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
                    @if($ownerId || $dateFrom || $dateTo)
                        <a href="{{ route('ledgers.owner') }}"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>

                @if($ledgerData)
                    <div class="flex items-center gap-2">
                        <!-- Excel Export -->
                        <a href="{{ route('ledgers.owner.excel', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors dark:border-emerald-900/30 dark:bg-emerald-950/10 dark:text-emerald-400">
                            🟢 Export Excel
                        </a>
                        <!-- PDF Export -->
                        <a href="{{ route('ledgers.owner.pdf', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-red-300 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100 transition-colors dark:border-red-900/30 dark:bg-red-950/10 dark:text-red-400">
                            🔴 Export PDF
                        </a>
                        <a href="{{ route('ledgers.owner.print', request()->all()) }}"
                            onclick="window.open(this.href,'_blank','width=1100,height=800,scrollbars=yes'); return false;"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            🖨️ Print
                        </a>
                    </div>
                @endif
            </div>
        </form>

        @if($ledgerData)
            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                <div class="bg-blue-50/50 dark:bg-blue-950/10 p-5 rounded-xl border border-blue-100 dark:border-blue-900/30">
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400">Total Payouts (Debits)</span>
                    <span class="block mt-2 text-2xl font-bold text-gray-800 dark:text-white">Rs. {{ number_format($ledgerData['summary']['total_debit'], 2) }}</span>
                </div>
                <div class="bg-green-50/50 dark:bg-green-950/10 p-5 rounded-xl border border-green-100 dark:border-green-900/30">
                    <span class="text-xs font-semibold uppercase tracking-wider text-green-500 dark:text-green-400">Total Deposits (Credits)</span>
                    <span class="block mt-2 text-2xl font-bold text-green-600 dark:text-green-400">Rs. {{ number_format($ledgerData['summary']['total_credit'], 2) }}</span>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/40 p-5 rounded-xl border border-gray-150 dark:border-gray-800">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Net Business Balance</span>
                    <span class="block mt-2 text-2xl font-bold text-gray-900 dark:text-white font-mono">Rs. {{ number_format($ledgerData['summary']['net_balance'], 2) }}</span>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3.5">Date</th>
                            <th class="px-5 py-3.5">Voucher #</th>
                            <th class="px-5 py-3.5">Account</th>
                            <th class="px-5 py-3.5">Reference</th>
                            <th class="px-5 py-3.5">Notes</th>
                            <th class="px-5 py-3.5 text-right">Debit (Payout)</th>
                            <th class="px-5 py-3.5 text-right">Credit (Deposit)</th>
                            <th class="px-5 py-3.5 text-right">Running Balance</th>
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
