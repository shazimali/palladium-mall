@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- Sticky Header & Inline Filter Panel --}}
    <div class="sticky top-[70px] z-30 space-y-3 rounded-2xl border border-gray-200 bg-white/95 p-4 shadow-md backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">

        {{-- Title Heading & Print/Export Buttons --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3">
            <div>
                <h1 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Security Ledgers</h1>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Security deposit received, deducted and refunded per flat/shop.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('ledgers.security.print', request()->all()) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition-all">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print (PDF)
                </a>
                <a href="{{ route('ledgers.security.export', request()->all()) }}"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-brand-700 transition-all">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export Excel
                </a>
            </div>
        </div>

        {{-- Filters Form --}}
        <form method="GET" action="{{ route('ledgers.security.index') }}" class="flex flex-wrap items-center gap-3">

            {{-- From Date --}}
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-wider">From Date</label>
                <div class="relative">
                    <input type="text" id="date_from" name="date_from" value="{{ $date_from }}" placeholder="YYYY-MM-DD"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-bold px-3.5 py-2.5 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 shadow-sm transition-all">
                </div>
            </div>

            {{-- To Date --}}
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-wider">To Date</label>
                <div class="relative">
                    <input type="text" id="date_to" name="date_to" value="{{ $date_to }}" placeholder="YYYY-MM-DD"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-bold px-3.5 py-2.5 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 shadow-sm transition-all">
                </div>
            </div>

            {{-- Transaction Type --}}
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-wider">Transaction</label>
                <select name="transaction_type" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-bold px-3.5 py-2.5 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 shadow-sm transition-all">
                    <option value="all" {{ request('transaction_type') === 'all' || !request('transaction_type') ? 'selected' : '' }}>All Transactions</option>
                    <option value="received" {{ request('transaction_type') === 'received' ? 'selected' : '' }}>Received</option>
                    <option value="deducted" {{ request('transaction_type') === 'deducted' ? 'selected' : '' }}>Deducted</option>
                    <option value="refunded" {{ request('transaction_type') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            {{-- Flat / Shop Select --}}
            <div class="flex-1 min-w-[130px]">
                <label class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-wider">Unit #</label>
                <select name="unit_id" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-bold px-3.5 py-2.5 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 shadow-sm transition-all">
                    <option value="">All Units</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ (string)request('unit_id') === (string)$u->id ? 'selected' : '' }}>
                            {{ $u->unit_number }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Action Buttons Inline --}}
            <div class="flex items-center gap-2 pt-5">
                <a href="{{ route('ledgers.security.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-all">
                    Reset
                </a>
                <button type="submit" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-brand-700 transition-all">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Summary KPI Cards --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm dark:border-emerald-900/40 dark:bg-white/[0.03]">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Deposit Received</p>
            <h4 class="mt-2 text-2xl font-black text-emerald-600">Rs. {{ number_format($summary['total_received'] ?? 0) }}</h4>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm dark:border-amber-900/40 dark:bg-white/[0.03]">
            <p class="text-xs font-bold uppercase tracking-wider text-amber-600">Deducted / Damage</p>
            <h4 class="mt-2 text-2xl font-black text-amber-600">Rs. {{ number_format($summary['total_deducted'] ?? 0) }}</h4>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm dark:border-rose-900/40 dark:bg-white/[0.03]">
            <p class="text-xs font-bold uppercase tracking-wider text-rose-600">Deposit Refunded</p>
            <h4 class="mt-2 text-2xl font-black text-rose-600">Rs. {{ number_format($summary['total_refunded'] ?? 0) }}</h4>
        </div>
        <div class="rounded-2xl border border-purple-100 bg-white p-5 shadow-sm dark:border-purple-900/40 dark:bg-white/[0.03]">
            <p class="text-xs font-bold uppercase tracking-wider text-purple-600">Currently Held</p>
            <h4 class="mt-2 text-2xl font-black text-purple-600">Rs. {{ number_format($summary['total_balance'] ?? 0) }}</h4>
        </div>
    </div>

    {{-- Main Table Container --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                <thead class="text-xs uppercase font-extrabold bg-gray-50 text-gray-500 border-b border-gray-200 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-200">
                    <tr>
                        <th class="px-4 py-4 tracking-wider">SR #</th>
                        <th class="px-4 py-4 tracking-wider">DATE</th>
                        <th class="px-4 py-4 tracking-wider">FLAT/SHOP</th>
                        <th class="px-4 py-4 tracking-wider">TENANT</th>
                        <th class="px-4 py-4 tracking-wider">TRANSACTION</th>
                        <th class="px-4 py-4 tracking-wider">REFERENCE</th>
                        <th class="px-4 py-4 text-right tracking-wider">DEBIT</th>
                        <th class="px-4 py-4 text-right tracking-wider">CREDIT</th>
                        <th class="px-4 py-4 text-right tracking-wider">BALANCE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                    @forelse($rows as $r)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3.5 text-xs text-gray-400 font-bold">{{ $r['sr'] }}</td>
                            <td class="px-4 py-3.5 text-xs font-semibold text-gray-500">{{ $r['date'] }}</td>
                            <td class="px-4 py-3.5 font-black text-gray-900 dark:text-white">
                                {{ $r['unit_number'] }}
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-gray-800 dark:text-gray-200">{{ $r['tenant_name'] }}</td>
                            <td class="px-4 py-3.5">
                                @php
                                    $badgeStyle = match($r['type']) {
                                        'Received' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                        'Deducted' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                        'Refunded' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
                                        default => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                                    };
                                @endphp
                                <span class="rounded-md px-2 py-0.5 text-xs font-black uppercase border {{ $badgeStyle }}">
                                    {{ $r['type'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-xs font-mono font-bold">
                                @if($r['reference_type'] === 'receiving_voucher' && !empty($r['reference_id']))
                                    <a href="{{ route('receiving-vouchers.show', $r['reference_id']) }}" class="text-brand-600 hover:underline dark:text-brand-400">
                                        {{ $r['reference'] }}
                                    </a>
                                @elseif($r['reference_type'] === 'payment_voucher' && !empty($r['reference_id']))
                                    <a href="{{ route('payment-vouchers.show', $r['reference_id']) }}" class="text-brand-600 hover:underline dark:text-brand-400">
                                        {{ $r['reference'] }}
                                    </a>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400">{{ $r['reference'] }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold {{ $r['debit'] > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                                {{ $r['debit'] > 0 ? 'Rs. ' . number_format($r['debit']) : '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold {{ $r['credit'] > 0 ? 'text-emerald-600' : 'text-gray-400' }}">
                                {{ $r['credit'] > 0 ? 'Rs. ' . number_format($r['credit']) : '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-black text-gray-900 dark:text-white">
                                Rs. {{ number_format($r['balance']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-400 font-semibold">No security ledger records match the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                <tfoot>
                    <tr class="bg-gray-100 dark:bg-gray-800 font-black text-sm text-gray-900 dark:text-white uppercase border-t-2 border-b-2 border-gray-300 dark:border-gray-700 tracking-wider">
                        <td colspan="6" class="px-4 py-4.5 text-sm font-black">Total ({{ $summary['total_records'] }} Records)</td>
                        <td class="px-4 py-4.5 text-right text-sm font-black text-rose-600">Rs. {{ number_format($summary['total_deducted'] + $summary['total_refunded']) }}</td>
                        <td class="px-4 py-4.5 text-right text-sm font-black text-emerald-600">Rs. {{ number_format($summary['total_received']) }}</td>
                        <td class="px-4 py-4.5 text-right text-sm font-black text-purple-600">Rs. {{ number_format($summary['total_balance']) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Pagination Links --}}
    @if($rows instanceof \Illuminate\Pagination\LengthAwarePaginator && $rows->hasPages())
        <div class="mt-4 flex justify-end">
            {{ $rows->links() }}
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#date_from', {
                dateFormat: 'Y-m-d',
                allowInput: true,
            });
            flatpickr('#date_to', {
                dateFormat: 'Y-m-d',
                allowInput: true,
            });
        }
    });
</script>
@endsection
