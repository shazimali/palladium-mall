@extends('layouts.app')

@section('title', 'Landlord Ledger - ' . $landlord->name)

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Ledger: {{ $landlord->name }}</h1>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a href="{{ route('landlord_ledgers.index') }}"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                Back to Ledgers
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Opening Balance -->
        <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between animate-fade-in"
            style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); min-height: 125px;">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-10 bg-white"></div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-white/75">Total Unit Value Owed</p>
                <p class="mt-2 text-2xl md:text-3xl font-extrabold text-white">
                    Rs. {{ number_format($openingBalance) }}
                </p>
            </div>
            <p class="text-[10px] text-white/75 mt-2">Based on active unit ownerships credit amount</p>
        </div>

        <!-- Total Paid -->
        <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between animate-fade-in"
            style="background: linear-gradient(135deg, #10b981 0%, #047857 100%); min-height: 125px;">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-10 bg-white"></div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-white/75">Total Payments Received</p>
                <p class="mt-2 text-2xl md:text-3xl font-extrabold text-white">
                    Rs. {{ number_format($totalPaid) }}
                </p>
            </div>
            <p class="text-[10px] text-white/75 mt-2">Sum of all receipt vouchers recorded for this landlord</p>
        </div>

        <!-- Pending Balance -->
        @php
            $pendingBalance = $openingBalance - $totalPaid;
        @endphp
        <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between animate-fade-in"
            style="background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%); min-height: 125px;">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-10 bg-white"></div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-white/75">Current Pending Balance</p>
                <p class="mt-2 text-2xl md:text-3xl font-extrabold text-white">
                    Rs. {{ number_format($pendingBalance) }}
                </p>
            </div>
            <p class="text-[10px] text-white/75 mt-2">Remaining outstanding balance due from this landlord</p>
        </div>
    </div>

    {{-- Date Filters Card --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <form action="{{ route('landlord_ledgers.show', $landlord) }}" method="GET" class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <div class="flex flex-col gap-1.5 flex-1 max-w-xs">
                <label class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Date From</label>
                <input type="text" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="Select Date" autocomplete="off"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            </div>

            <div class="flex flex-col gap-1.5 flex-1 max-w-xs">
                <label class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Date To</label>
                <input type="text" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="Select Date" autocomplete="off"
                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            </div>

            <div class="flex gap-2 self-end mt-1.5">
                <button type="submit" class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['date_from', 'date_to']))
                    <a href="{{ route('landlord_ledgers.show', $landlord) }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl mb-8">
        <div class="overflow-x-auto">
            <table class="table-auto w-full dark:text-gray-300">
                <thead class="text-xs uppercase text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700/50 rounded-sm">
                    <tr>
                        <th class="p-4 whitespace-nowrap"><div class="font-semibold text-left">Date</div></th>
                        <th class="p-4 whitespace-nowrap"><div class="font-semibold text-left">Description</div></th>
                        <th class="p-4 whitespace-nowrap"><div class="font-semibold text-right">Payable (Debit)</div></th>
                        <th class="p-4 whitespace-nowrap"><div class="font-semibold text-right">Payment (Credit)</div></th>
                        <th class="p-4 whitespace-nowrap"><div class="font-semibold text-right">Balance</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100 dark:divide-gray-700/50">
                    <!-- Opening Balance Row -->
                    <tr class="bg-gray-50/50 dark:bg-gray-800/50">
                        <td class="p-4 whitespace-nowrap">-</td>
                        <td class="p-4 whitespace-nowrap font-medium">Opening Balance</td>
                        <td class="p-4 whitespace-nowrap text-right">-</td>
                        <td class="p-4 whitespace-nowrap text-right">-</td>
                        <td class="p-4 whitespace-nowrap text-right font-bold text-gray-900 dark:text-gray-100">
                            Rs. {{ number_format($openingBalance) }}
                        </td>
                    </tr>
                    
                    @php $runningBalance = $openingBalance; @endphp
                    @forelse($transactions as $t)
                        @php
                            // Depending on the business logic, you might add Debits (Payables) to the balance or not.
                            // If Payables are new charges, we add them. If they are installments of the Opening Balance, we don't.
                            // For a true ledger, if Payables add to the balance:
                            // $runningBalance += $t['debit'];
                            // $runningBalance -= $t['credit'];
                            
                            // Let's assume Payables don't increase the total unit debt, but just schedule it.
                            // So only payments reduce the balance.
                            $runningBalance -= $t['credit'];
                        @endphp
                        <tr>
                            <td class="p-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($t['date'])->format('M d, Y') }}</td>
                            <td class="p-4 whitespace-nowrap">{{ $t['description'] }}</td>
                            <td class="p-4 whitespace-nowrap text-right text-amber-600">
                                {{ $t['debit'] > 0 ? 'Rs. ' . number_format($t['debit']) : '-' }}
                            </td>
                            <td class="p-4 whitespace-nowrap text-right text-emerald-600">
                                {{ $t['credit'] > 0 ? 'Rs. ' . number_format($t['credit']) : '-' }}
                            </td>
                            <td class="p-4 whitespace-nowrap text-right font-medium text-gray-900 dark:text-gray-100">
                                Rs. {{ number_format($runningBalance) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-gray-500">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
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
                    disableMobile: true
                });
                flatpickr('#date_to', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true
                });
            }
        });
    </script>
@endpush
