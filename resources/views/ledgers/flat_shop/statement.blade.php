@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Navigation Breadcrumb & Back --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('ledgers.flat_shop.index', request()->all()) }}"
            class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-brand-600 dark:text-gray-400 transition-colors">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Flat / Shop Ledger Summary
        </a>

        <a href="window.print()" onclick="window.print(); return false;"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-sm transition-all">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Statement
        </a>
    </div>

    {{-- Statement Header Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 border-b border-gray-100 dark:border-gray-800 pb-6">
            <div>
                <span class="rounded-md bg-brand-50 px-3 py-1 text-xs font-black uppercase text-brand-600 dark:bg-brand-950/40 dark:text-brand-400 border border-brand-200 dark:border-brand-800">
                    Flat / Shop Statement
                </span>
                <h1 class="text-3xl font-black text-gray-900 dark:text-white mt-2 tracking-tight">Unit {{ $unit->unit_number }}</h1>
                <p class="text-sm font-semibold text-gray-500 mt-1">
                    Owner: <strong>{{ $unit->is_self ? ($unit->landlord?->name ?? 'Other Owner') : 'PM Mall' }}</strong>
                    @if($unit->is_self && $unit->otherTenant)
                        | Tenant: <strong>{{ $unit->otherTenant->name }}</strong>
                    @elseif(!$unit->is_self && $unit->activeAgreement?->tenant)
                        | Tenant: <strong>{{ $unit->activeAgreement->tenant->name }}</strong>
                    @endif
                </p>
            </div>
            <div class="text-left md:text-right space-y-1">
                <span class="text-xs font-black uppercase text-gray-400">Statement Period</span>
                <p class="text-sm font-extrabold text-gray-800 dark:text-gray-200">
                    {{ \Carbon\Carbon::parse($date_from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($date_to)->format('d M Y') }}
                </p>
            </div>
        </div>

        {{-- Statement Summary Widgets --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mt-6">
            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                <span class="text-xs font-black uppercase text-gray-400">Opening Balance</span>
                <p class="text-lg font-black text-gray-900 dark:text-white mt-0.5">Rs. {{ number_format($opening_balance) }}</p>
            </div>
            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                <span class="text-xs font-black uppercase text-gray-400">Total Billed (Debits)</span>
                <p class="text-lg font-black text-gray-900 dark:text-white mt-0.5">Rs. {{ number_format($total_debit) }}</p>
            </div>
            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                <span class="text-xs font-black uppercase text-green-600">Total Received (Credits)</span>
                <p class="text-lg font-black text-green-600 mt-0.5">Rs. {{ number_format($total_credit) }}</p>
            </div>
            <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                <span class="text-xs font-black uppercase text-brand-600">Closing Balance</span>
                <p class="text-lg font-black text-brand-600 mt-0.5">Rs. {{ number_format($closing_balance) }}</p>
            </div>
        </div>
    </div>

    {{-- Statement Transactions Table --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-200">
                    <tr>
                        <th class="px-4 py-3.5">Month</th>
                        <th class="px-4 py-3.5">Voucher / Ref #</th>
                        <th class="px-4 py-3.5">Type</th>
                        <th class="px-4 py-3.5">Description</th>
                        <th class="px-4 py-3.5 text-right">Debit (Billed)</th>
                        <th class="px-4 py-3.5 text-right">Credit (Paid)</th>
                        <th class="px-4 py-3.5 text-right">Running Balance</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr class="bg-gray-50/50 dark:bg-gray-800/30 italic">
                        <td colspan="6" class="px-4 py-3 text-xs font-extrabold text-gray-500">Opening Balance Brought Forward</td>
                        <td class="px-4 py-3 text-right font-black text-gray-900 dark:text-white">Rs. {{ number_format($opening_balance) }}</td>
                        <td></td>
                    </tr>
                    @forelse($entries as $e)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 text-xs font-bold text-gray-500">{{ $e['month'] }}</td>
                            <td class="px-4 py-3 font-mono text-xs font-bold text-gray-800 dark:text-gray-200">{{ $e['voucher_no'] }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $e['type'] }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ $e['description'] }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rs. {{ number_format($e['debit']) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-green-600">Rs. {{ number_format($e['credit']) }}</td>
                            <td class="px-4 py-3 text-right font-black {{ $e['running_balance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                Rs. {{ number_format($e['running_balance']) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-md px-2 py-0.5 text-xs font-black uppercase border {{ $e['status'] === 'paid' ? 'bg-green-50 text-green-700 border-green-200' : ($e['status'] === 'partial' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-red-50 text-red-700 border-red-200') }}">
                                    {{ $e['status'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400 font-semibold">No transactions recorded for Unit {{ $unit->unit_number }} in this statement period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
