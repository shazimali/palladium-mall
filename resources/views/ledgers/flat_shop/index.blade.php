@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- Sticky Header & Inline Filter Panel --}}
    <div class="sticky top-[70px] z-30 space-y-3 rounded-2xl border border-gray-200 bg-white/95 p-4 shadow-md backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
        
        {{-- Title Heading & Print/Export Buttons --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-3">
            <div>
                <h1 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Flat / Shop Ledger</h1>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Detailed billing and payment ledger statement for all flats and shops.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('ledgers.flat_shop.print', request()->all()) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition-all">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print (PDF)
                </a>
                <a href="{{ route('ledgers.flat_shop.export', request()->all()) }}"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-brand-700 transition-all">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export Excel
                </a>
            </div>
        </div>

        {{-- Filters Form --}}
        <form method="GET" action="{{ route('ledgers.flat_shop.index') }}" class="flex flex-wrap items-center gap-3">
            
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

            {{-- Owner Type --}}
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-wider">Owner Type</label>
                <select name="owner_type" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-bold px-3.5 py-2.5 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 shadow-sm transition-all">
                    <option value="">All Owners</option>
                    <option value="pm_mall" {{ request('owner_type') === 'pm_mall' ? 'selected' : '' }}>PM Mall</option>
                    <option value="other_owned" {{ request('owner_type') === 'other_owned' ? 'selected' : '' }}>Other Owned</option>
                </select>
            </div>

            {{-- Occupancy --}}
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-wider">Occupancy</label>
                <select name="occupancy_status" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-bold px-3.5 py-2.5 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 shadow-sm transition-all">
                    <option value="">All Occupancy</option>
                    <option value="pm_rented" {{ request('occupancy_status') === 'pm_rented' ? 'selected' : '' }}>PM Mall Rented</option>
                    <option value="other_occupied" {{ request('occupancy_status') === 'other_occupied' ? 'selected' : '' }}>Other Occupied</option>
                    <option value="other_unoccupied" {{ request('occupancy_status') === 'other_unoccupied' ? 'selected' : '' }}>Other Unoccupied</option>
                    <option value="vacant" {{ request('occupancy_status') === 'vacant' ? 'selected' : '' }}>PM Mall Vacant</option>
                </select>
            </div>

            {{-- Billing Type --}}
            <div class="flex-1 min-w-[150px]">
                <label class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-wider">Billing Type</label>
                <select name="billing_type" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-bold px-3.5 py-2.5 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 shadow-sm transition-all">
                    <option value="all" {{ request('billing_type') === 'all' || !request('billing_type') ? 'selected' : '' }}>All Billings</option>
                    <option value="rent" {{ request('billing_type') === 'rent' ? 'selected' : '' }}>Rent</option>
                    <option value="maintenance" {{ request('billing_type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="extra_payment" {{ request('billing_type') === 'extra_payment' ? 'selected' : '' }}>Extra Amount</option>
                    <option value="security_deposit" {{ request('billing_type') === 'security_deposit' ? 'selected' : '' }}>Security Deposit</option>
                </select>
            </div>

            {{-- Payment Status --}}
            <div class="flex-1 min-w-[140px]">
                <label class="block text-xs font-black uppercase text-gray-500 mb-1 tracking-wider">Payment Status</label>
                <select name="payment_status" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm font-bold px-3.5 py-2.5 outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 shadow-sm transition-all">
                    <option value="all" {{ request('payment_status') === 'all' || !request('payment_status') ? 'selected' : '' }}>All Statuses</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>Partial</option>
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
                <a href="{{ route('ledgers.flat_shop.index') }}" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-all">
                    Reset
                </a>
                <button type="submit" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-brand-700 transition-all">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Summary KPI Cards --}}
    @if(!empty($is_security_deposit))
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm dark:border-blue-900/40 dark:bg-white/[0.03]">
                <p class="text-xs font-bold uppercase tracking-wider text-blue-600">Required Deposit</p>
                <h4 class="mt-2 text-2xl font-black text-blue-700 dark:text-blue-400">Rs. {{ number_format($summary['total_required'] ?? 0) }}</h4>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm dark:border-emerald-900/40 dark:bg-white/[0.03]">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Collected Deposit</p>
                <h4 class="mt-2 text-2xl font-black text-emerald-600">Rs. {{ number_format($summary['total_collected'] ?? 0) }}</h4>
            </div>
            <div class="rounded-2xl border border-rose-100 bg-white p-5 shadow-sm dark:border-rose-900/40 dark:bg-white/[0.03]">
                <p class="text-xs font-bold uppercase tracking-wider text-rose-600">Pending Deposit</p>
                <h4 class="mt-2 text-2xl font-black text-rose-600">Rs. {{ number_format($summary['total_pending'] ?? 0) }}</h4>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm dark:border-amber-900/40 dark:bg-white/[0.03]">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-600">Deductions / Damage</p>
                <h4 class="mt-2 text-2xl font-black text-amber-600">Rs. {{ number_format($summary['total_deductions'] ?? 0) }}</h4>
            </div>
            <div class="rounded-2xl border border-purple-100 bg-white p-5 shadow-sm dark:border-purple-900/40 dark:bg-white/[0.03]">
                <p class="text-xs font-bold uppercase tracking-wider text-purple-600">Net Refundable</p>
                <h4 class="mt-2 text-2xl font-black text-purple-600">Rs. {{ number_format($summary['total_net_refundable'] ?? 0) }}</h4>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Records</p>
                <h4 class="mt-2 text-2xl font-black text-gray-800 dark:text-white leading-tight">{{ number_format($summary['total_records']) }}</h4>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-500">Total Prev. Unpaid</p>
                <h4 class="mt-2 text-2xl font-black text-amber-600 dark:text-amber-400 leading-tight">Rs. {{ number_format($summary['total_prev_unpaid']) }}</h4>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-500">Total Amount Due</p>
                <h4 class="mt-2 text-2xl font-black text-indigo-600 dark:text-indigo-400 leading-tight">Rs. {{ number_format($summary['total_amount_due']) }}</h4>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-500">Total Amount Paid</p>
                <h4 class="mt-2 text-2xl font-black text-emerald-600 leading-tight">Rs. {{ number_format($summary['total_amount_paid']) }}</h4>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-500">Net Balance</p>
                <h4 class="mt-2 text-2xl font-black text-brand-600 leading-tight">Rs. {{ number_format($summary['total_balance']) }}</h4>
            </div>
        </div>
    @endif

    {{-- Main Table Container --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            @if(!empty($is_security_deposit))
                {{-- Security Deposit Matrix Table View --}}
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300 border-collapse">
                    <thead class="text-xs uppercase font-extrabold tracking-wider">
                        <tr class="bg-gray-50 border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                            <th class="px-4 py-4 text-center">SR</th>
                            <th class="px-4 py-4">FLAT / SHOP</th>
                            <th class="px-4 py-4">OWNER</th>
                            <th class="px-4 py-4">TENANT</th>
                            <th class="px-4 py-4 text-center">STATUS</th>
                            <th class="px-4 py-4 text-right bg-blue-50/80 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300 border-l border-blue-100 dark:border-blue-900/50">REQUIRED DEPOSIT</th>
                            <th class="px-4 py-4 text-right bg-emerald-50/80 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 border-l border-emerald-100 dark:border-emerald-900/50">COLLECTED DEPOSIT</th>
                            <th class="px-4 py-4 text-right bg-rose-50/80 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300 border-l border-rose-100 dark:border-rose-900/50">PENDING DEPOSIT</th>
                            <th class="px-4 py-4 text-right bg-amber-50/80 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border-l border-amber-100 dark:border-amber-900/50">DEDUCTIONS / DAMAGE</th>
                            <th class="px-4 py-4 text-right bg-purple-50/80 text-purple-800 dark:bg-purple-950/40 dark:text-purple-300 border-l border-purple-100 dark:border-purple-900/50">NET REFUNDABLE</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                        @forelse($rows as $r)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3.5 text-center text-xs text-gray-400 font-bold">{{ $r['sr'] }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center justify-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-black text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800">
                                        {{ $r['unit_number'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 font-bold text-gray-900 dark:text-white">{{ $r['owner'] }}</td>
                                <td class="px-4 py-3.5 font-black uppercase text-gray-800 dark:text-gray-200">{{ $r['tenant_name'] }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    @php
                                        $st = strtoupper($r['status'] ?? '');
                                        $stBadge = match(true) {
                                            $st === 'RENTED' || $st === 'OCCUPIED' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                            $st === 'VACANT' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                            default => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                                        };
                                    @endphp
                                    <span class="rounded-md px-2 py-0.5 text-xs font-black uppercase border {{ $stBadge }}">
                                        {{ $st }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right font-black text-indigo-700 dark:text-indigo-300 bg-blue-50/30 dark:bg-blue-950/10">
                                    {{ $r['required_deposit'] > 0 ? 'Rs. ' . number_format($r['required_deposit']) : '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-black text-emerald-700 dark:text-emerald-300 bg-emerald-50/30 dark:bg-emerald-950/10">
                                    {{ $r['collected_deposit'] > 0 ? 'Rs. ' . number_format($r['collected_deposit']) : '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-black {{ $r['pending_deposit'] > 0 ? 'text-rose-600' : 'text-gray-400' }} bg-rose-50/30 dark:bg-rose-950/10">
                                    {{ $r['pending_deposit'] > 0 ? 'Rs. ' . number_format($r['pending_deposit']) : '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-black {{ $r['deduction_deposit'] > 0 ? 'text-amber-600' : 'text-gray-400' }} bg-amber-50/30 dark:bg-amber-950/10">
                                    {{ $r['deduction_deposit'] > 0 ? 'Rs. ' . number_format($r['deduction_deposit']) : '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-black text-purple-700 dark:text-purple-300 bg-purple-50/30 dark:bg-purple-950/10">
                                    {{ $r['net_refundable'] > 0 ? 'Rs. ' . number_format($r['net_refundable']) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-12 text-center text-gray-400 font-semibold">No security deposit records match the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($rows->isNotEmpty())
                    <tfoot>
                        <tr class="bg-gray-100 dark:bg-gray-800 font-black text-sm text-gray-900 dark:text-white uppercase border-t-2 border-b-2 border-gray-300 dark:border-gray-700 tracking-wider">
                            <td colspan="5" class="px-4 py-4.5 text-sm font-black">Total ({{ $summary['total_records'] }} Units)</td>
                            <td class="px-4 py-4.5 text-right text-sm font-black text-indigo-700 dark:text-indigo-300">Rs. {{ number_format($summary['total_required'] ?? 0) }}</td>
                            <td class="px-4 py-4.5 text-right text-sm font-black text-emerald-600">Rs. {{ number_format($summary['total_collected'] ?? 0) }}</td>
                            <td class="px-4 py-4.5 text-right text-sm font-black text-rose-600">Rs. {{ number_format($summary['total_pending'] ?? 0) }}</td>
                            <td class="px-4 py-4.5 text-right text-sm font-black text-amber-600">Rs. {{ number_format($summary['total_deductions'] ?? 0) }}</td>
                            <td class="px-4 py-4.5 text-right text-sm font-black text-purple-600">Rs. {{ number_format($summary['total_net_refundable'] ?? 0) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            @else
                {{-- Standard Billing Ledger Table View --}}
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300">
                    <thead class="text-xs uppercase font-extrabold bg-gray-50 text-gray-500 border-b border-gray-200 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-200">
                        <tr>
                            <th class="px-4 py-4 tracking-wider">SR #</th>
                            <th class="px-4 py-4 tracking-wider">FLAT/SHOP</th>
                            <th class="px-4 py-4 tracking-wider">TENANT</th>
                            <th class="px-4 py-4 tracking-wider">BILLING TYPE</th>
                            <th class="px-4 py-4 text-right tracking-wider">PREV. UNPAID</th>
                            <th class="px-4 py-4 text-right tracking-wider">AMOUNT DUE</th>
                            <th class="px-4 py-4 text-right tracking-wider">AMOUNT PAID</th>
                            <th class="px-4 py-4 tracking-wider">PAYMENT METHOD</th>
                            <th class="px-4 py-4 tracking-wider">PAYMENT ACCOUNT</th>
                            <th class="px-4 py-4 tracking-wider">PAID AT</th>
                            <th class="px-4 py-4 text-right tracking-wider">BALANCE</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-medium">
                        @forelse($rows as $r)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3.5 text-xs text-gray-400 font-bold">{{ $r['sr'] }}</td>
                                <td class="px-4 py-3.5 font-black text-gray-900 dark:text-white">
                                    {{ $r['unit_number'] }}
                                </td>
                                <td class="px-4 py-3.5 font-semibold text-gray-800 dark:text-gray-200">{{ $r['tenant_name'] }}</td>
                                <td class="px-4 py-3.5">
                                    @php
                                        $typeCode = strtolower($r['type_label'] ?? '');
                                        $badgeStyle = match(true) {
                                            str_contains($typeCode, 'rent') => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
                                            str_contains($typeCode, 'maint') => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                            str_contains($typeCode, 'security') || str_contains($typeCode, 'deposit') => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
                                            str_contains($typeCode, 'extra') || str_contains($typeCode, 'fine') || str_contains($typeCode, 'other') => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                            default => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                                        };
                                    @endphp
                                    <span class="rounded-md px-2 py-0.5 text-xs font-black uppercase border {{ $badgeStyle }}">
                                        {{ $r['type_label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold {{ $r['prev_unpaid'] > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                    Rs. {{ number_format($r['prev_unpaid']) }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold text-gray-900 dark:text-white">
                                    Rs. {{ number_format($r['amount_due']) }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold text-green-600">
                                    Rs. {{ number_format($r['amount_paid']) }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $r['payment_method'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-xs font-semibold text-gray-600 dark:text-gray-400">
                                    {{ $r['payment_account'] }}
                                </td>
                                <td class="px-4 py-3.5 text-xs font-semibold text-gray-500">
                                    {{ $r['paid_at'] }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-black {{ $r['balance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    Rs. {{ number_format($r['balance']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-12 text-center text-gray-400 font-semibold">No flat/shop ledger records match the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($rows->isNotEmpty())
                    <tfoot>
                        <tr class="bg-gray-100 dark:bg-gray-800 font-black text-sm text-gray-900 dark:text-white uppercase border-t-2 border-b-2 border-gray-300 dark:border-gray-700 tracking-wider">
                            <td colspan="4" class="px-4 py-4.5 text-sm font-black">Total ({{ $summary['total_records'] }} Records)</td>
                            <td class="px-4 py-4.5 text-right text-sm font-black text-amber-600">Rs. {{ number_format($summary['total_prev_unpaid']) }}</td>
                            <td class="px-4 py-4.5 text-right text-sm font-black text-gray-900 dark:text-white">Rs. {{ number_format($summary['total_amount_due']) }}</td>
                            <td class="px-4 py-4.5 text-right text-sm font-black text-green-600">Rs. {{ number_format($summary['total_amount_paid']) }}</td>
                            <td colspan="3"></td>
                            <td class="px-4 py-4.5 text-right text-sm font-black text-brand-600">Rs. {{ number_format($summary['total_balance']) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            @endif
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
