@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Rent & Payments" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Summary cards --}}
    @php
        $monthLabel = request('month') ? Carbon\Carbon::parse(request('month'))->format('F Y') : 'This Month';
    @endphp
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs text-gray-400">Total Due ({{ $monthLabel }})</p>
            <p class="mt-1 text-lg font-bold text-gray-800 dark:text-white">Rs. {{ number_format($summary['total_due']) }}
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs text-gray-400">Total Collected ({{ $monthLabel }})</p>
            <p class="mt-1 text-lg font-bold text-green-600">Rs. {{ number_format($summary['total_paid']) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs text-gray-400">Unpaid ({{ $monthLabel }})</p>
            <p class="mt-1 text-lg font-bold text-red-500">{{ $summary['unpaid_count'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs text-gray-400">Overdue ({{ $monthLabel }})</p>
            <p class="mt-1 text-lg font-bold text-orange-500">{{ $summary['overdue_count'] }}</p>
        </div>
    </div>

    <x-common.component-card title="All Payments" desc="Track rent, maintenance and fine payments">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2">
                <span
                    class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Total: {{ $payments->total() }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'status', 'type', 'month']))
                    <a href="{{ route('payments.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Clear
                    </a>
                @endif
                {{-- Bulk Generate button --}}
                @if(auth()->user()->hasPermission('payments.create') || auth()->user()->isSuperAdmin())
                    <button type="button" x-data @click="$dispatch('open-bulk-generate')"
                        class="inline-flex items-center gap-2 rounded-lg border border-brand-500 px-4 py-2 text-sm font-medium text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Bulk Generate
                    </button>

                    <a href="{{ route('payments.utilities.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Record Utility
                    </a>

                    <a href="{{ route('payments.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Payment
                    </a>
                @endif
            </div>
        </div>

        <!-- Filters & Search -->
        <div
            class="my-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('payments.index') }}" method="GET" class="flex flex-col gap-4 sm:flex-row sm:items-center">

                <!-- Search Input -->
                <div class="relative flex-1 max-w-md">
                    <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20"
                            fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tenant, unit, ref..."
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <!-- Status Filter -->
                <div class="relative">
                    <select name="status" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Statuses</option>
                        <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div class="relative">
                    <select name="type" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Types</option>
                        <option value="rent" {{ request('type') === 'rent' ? 'selected' : '' }}>Rent</option>
                        <option value="maintenance" {{ request('type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="fine" {{ request('type') === 'fine' ? 'selected' : '' }}>Fine</option>
                        <option value="electricity" {{ request('type') === 'electricity' ? 'selected' : '' }}>Electricity</option>
                        <option value="water" {{ request('type') === 'water' ? 'selected' : '' }}>Water</option>
                        <option value="gas" {{ request('type') === 'gas' ? 'selected' : '' }}>Gas</option>
                        <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Month & Year Filter Datepicker -->
                <div class="relative max-w-[180px]">
                    <input type="text" id="filter_month" name="month" value="{{ request('month') }}" placeholder="Select Month/Year" autocomplete="off"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <button type="submit" class="hidden">Submit</button>
            </form>
        </div>

        <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Tenant</th>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Month</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Paid</th>
                        <th class="px-4 py-3">Due Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($payments as $index => $payment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 text-gray-400">{{ $payments->firstItem() + $index }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90">
                                @if($payment->tenant)
                                    {{ $payment->tenant->name }}
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-violet-600 dark:text-violet-400">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        Self-Owned
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                                    {{ $payment->unit->unit_number }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $payment->type_badge_class }}">
                                    {{ ucfirst($payment->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $payment->month->format('M Y') }}</td>
                            <td class="px-4 py-3 font-medium">Rs. {{ number_format($payment->amount) }}</td>
                            <td class="px-4 py-3">
                                <span class="{{ $payment->amount_paid > 0 ? 'text-green-600 font-medium' : 'text-gray-400' }}">
                                    Rs. {{ number_format($payment->amount_paid) }}
                                </span>
                                @if($payment->paymentAccount)
                                    <div class="text-[10px] text-gray-500 mt-1 dark:text-gray-400 font-medium whitespace-nowrap">
                                        🏦 {{ $payment->paymentAccount->name }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs {{ $payment->isOverdue() ? 'text-red-500 font-semibold' : '' }}">
                                {{ $payment->due_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                @if(auth()->user()->hasPermission('payments.record') || auth()->user()->isSuperAdmin())
                                    <form action="{{ route('payments.toggle-status', $payment) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                title="Click to toggle status (Paid / Unpaid)"
                                                class="group relative inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold shadow-xs transition-all hover:scale-105 active:scale-95 border border-transparent {{ $payment->status === 'paid' ? 'bg-green-50 text-green-700 hover:bg-red-50 hover:text-red-700 hover:border-red-200 dark:hover:bg-red-950/20 dark:hover:text-red-400' : 'bg-red-50 text-red-700 hover:bg-green-50 hover:text-green-700 hover:border-green-200 dark:hover:bg-green-950/20 dark:hover:text-green-400' }}">
                                            <!-- Dot indicator -->
                                            <span class="h-1.5 w-1.5 rounded-full transition-colors {{ $payment->status === 'paid' ? 'bg-green-600 group-hover:bg-red-600' : 'bg-red-600 group-hover:bg-green-600' }}"></span>
                                            
                                            <!-- Status Text with hover state toggling -->
                                            <span class="group-hover:hidden">{{ ucfirst($payment->status) }}</span>
                                            <span class="hidden group-hover:inline">Mark as {{ $payment->status === 'paid' ? 'Unpaid' : 'Paid' }}</span>
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $payment->status_badge_class }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $payment->status === 'paid' ? 'bg-green-600' : ($payment->status === 'partial' ? 'bg-orange-500' : 'bg-red-600') }}"></span>
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col items-end gap-1.5">
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('payments.show', $payment) }}"
                                            class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                            title="View">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        <a href="{{ route('payments.print', $payment) }}" target="_blank"
                                            class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                            title="{{ $payment->type === 'rent' ? 'Print Rent Bill' : (in_array($payment->type, ['maintenance', 'electricity', 'water', 'gas']) ? 'Print Maintenance Bill' : 'Print Receipt') }}">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                                            </svg>
                                        </a>

                                        @if(auth()->user()->hasPermission('payments.whatsapp') || auth()->user()->isSuperAdmin())
                                            @if($payment->tenant)
                                            @php
                                                $phone = $payment->tenant->whatsapp_number ?: $payment->tenant->phone;
                                                $phoneClean = preg_replace('/\D/', '', $phone);
                                                if (strpos($phoneClean, '0') === 0 && strlen($phoneClean) === 11) {
                                                    $phoneClean = '92' . substr($phoneClean, 1);
                                                }

                                                $typeStr = ucfirst($payment->type);
                                                $monthStr = $payment->month ? $payment->month->format('M Y') : '';
                                                $amountStr = number_format($payment->amount);
                                                $paidStr = number_format($payment->amount_paid);
                                                $dueDateStr = $payment->due_date ? $payment->due_date->format('d M Y') : '';
                                                $statusStr = ucfirst($payment->status);
                                                $paymentUrl = $payment->public_url;

                                                $message = "Dear {$payment->tenant->name},\n\nThis is a notification for your {$typeStr} payment towards Unit {$payment->unit->unit_number} for {$monthStr}.\n\nBill Details:\n- Type: {$typeStr}\n- Month: {$monthStr}\n- Total Amount: Rs. {$amountStr}\n- Amount Paid: Rs. {$paidStr}\n- Due Date: {$dueDateStr}\n- Status: {$statusStr}\n\nYou can view/print your bill copy here: {$paymentUrl}\n\nRegards,\nPalladium Mall Management";
                                                $whatsappUrl = "https://api.whatsapp.com/send?phone=" . urlencode($phoneClean) . "&text=" . urlencode($message);
                                            @endphp
                                            <a href="{{ $whatsappUrl }}" target="_blank"
                                                class="inline-flex items-center rounded-lg p-1.5 text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                                                title="Share Bill on WhatsApp">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12.012 2c-5.506 0-9.988 4.482-9.988 9.988 0 1.76.46 3.413 1.258 4.868L2 22l5.29-1.387c1.405.766 3 1.205 4.722 1.205 5.506 0 9.988-4.482 9.988-9.988C22 6.482 17.518 2 12.012 2zm6.262 14.373c-.258.73-1.468 1.413-2.025 1.48-.48.06-1.106.1-3.23-.787-2.716-1.137-4.46-3.906-4.594-4.088-.135-.183-.996-1.328-.996-2.534s.623-1.802.846-2.052c.222-.25.48-.312.642-.312.163 0 .326.01.467.01.147.01.343-.06.538.41.196.48.674 1.638.73 1.75.056.113.093.243.017.393-.075.15-.112.24-.225.37-.113.13-.238.29-.338.39-.11.1-.225.21-.096.43.128.22.57 1.004 1.22 1.58.84.75 1.55.98 1.77 1.1.22.12.35.1.48-.05.13-.15.56-.65.71-.87.15-.22.3-.18.5-.1.21.08 1.32.62 1.55.73.23.11.38.16.44.27.06.1.06.59-.19 1.32z"/>
                                                </svg>
                                            </a>
                                            @endif
                                        @endif

                                        {{-- Record Payment --}}
                                        @if(!$payment->isPaid())
                                            <button type="button" x-data @click="$dispatch('open-record-payment', {
                                                                action: '{{ route('payments.record', $payment) }}',
                                                                amount: '{{ $payment->amount }}',
                                                                balance: '{{ $payment->balanceDue() }}'
                                                            })"
                                                class="inline-flex items-center rounded-lg p-1.5 text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                                                title="Record Payment">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>

                                    @if(auth()->user()->hasPermission('payments.edit') || auth()->user()->hasPermission('payments.delete') || auth()->user()->isSuperAdmin())
                                        <div class="flex items-center gap-1.5">
                                            @if(auth()->user()->hasPermission('payments.edit') || auth()->user()->isSuperAdmin())
                                                <a href="{{ route('payments.edit', $payment) }}"
                                                    class="inline-flex items-center rounded-lg p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                                    title="Edit">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                            @endif

                                            @if(auth()->user()->hasPermission('payments.delete') || auth()->user()->isSuperAdmin())
                                                <form action="{{ route('payments.destroy', $payment) }}" method="POST" x-data
                                                    @submit.prevent="if(confirm('Delete this payment record?')) $el.submit()">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center rounded-lg p-1.5 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                        title="Delete">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                                No payment records found.
                                <a href="{{ route('payments.create') }}" class="text-brand-500 hover:underline">Add one.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="mt-4 border-t border-gray-100 p-4 dark:border-gray-800">
                {{ $payments->links() }}
            </div>
        @endif
    </x-common.component-card>

    {{-- Record Payment Modal --}}
    <div x-data="{
                show: false,
                action: '',
                balance: 0,
                init() {
                    window.addEventListener('open-record-payment', (e) => {
                        this.action  = e.detail.action;
                        this.balance = e.detail.balance;
                        this.show    = true;
                    });
                }
            }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900" @click.outside="if (document.body.contains($event.target) && !$event.target.closest('.flatpickr-calendar')) show = false">
            <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Record Payment</h3>
            <form :action="action" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Amount Paid (Rs.) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="amount_paid" min="0" step="0.01" required
                                :placeholder="'Balance: Rs. ' + parseFloat(balance).toLocaleString()"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Payment Account <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_account_id" required
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select Account</option>
                                @foreach($paymentAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Reference / Cheque No.
                        </label>
                        <input type="text" name="reference" placeholder="Optional"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Payment Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="paid_at" value="{{ now()->toDateString() }}" required
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Receipt (optional)
                            </label>
                            <input type="file" name="receipt" accept="image/jpeg,image/jpg,image/png,application/pdf"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1 file:text-xs file:font-medium file:text-brand-600 dark:border-gray-700 dark:bg-gray-800">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                        <input type="text" name="notes" placeholder="Optional"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                </div>

                <div class="mt-5 flex items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                        Confirm Payment
                    </button>
                    <button type="button" @click="show = false"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Generate Modal --}}
    <div x-data="{
                show: false,
                init() {
                    window.addEventListener('open-bulk-generate', () => { this.show = true; });
                }
            }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900" @click.outside="if (document.body.contains($event.target) && !$event.target.closest('.flatpickr-calendar')) show = false">
            <h3 class="mb-1 text-base font-semibold text-gray-800 dark:text-white">Bulk Generate Payments</h3>
            <p class="mb-4 text-sm text-gray-500">Creates payment records for all active tenants with active agreements.</p>
            <form action="{{ route('payments.bulk-generate') }}" method="POST">
                @csrf
                <div class="space-y-4">

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Billing Month <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="bulk_month" name="month" placeholder="Select month" autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Due Date <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="bulk_due_date" name="due_date" placeholder="Select due date"
                            autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Generate For <span class="text-red-500">*</span>
                        </label>
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="types[]" value="rent" checked
                                        class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                    Rent
                                </label>
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="types[]" value="maintenance"
                                        class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                    Maintenance
                                </label>
                            </div>

                            {{-- External owner units checkbox --}}
                            <div class="rounded-lg border border-violet-200 bg-violet-50 px-3 py-2.5 dark:border-violet-800/40 dark:bg-violet-900/10">
                                <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="checkbox" name="include_self_units" value="1"
                                        class="mt-0.5 rounded border-gray-300 text-violet-500 focus:ring-violet-500">
                                    <div>
                                        <span class="font-medium">Include Self-Owned Units</span>
                                        <p class="mt-0.5 text-[11px] text-gray-400">Also generates maintenance payments for all self-owned units. Duplicates are automatically skipped.</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-5 flex items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        Generate Now
                    </button>
                    <button type="button" @click="show = false"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr('#filter_month', {
                dateFormat: 'Y-m-01',
                altInput: true,
                altFormat: 'F Y',
                allowInput: false,
                disableMobile: true,
                plugins: [
                    new monthSelectPlugin({
                        shorthand: false,
                        dateFormat: 'Y-m-01',
                        altFormat: 'F Y',
                        theme: 'light',
                    })
                ],
                onChange: function(selectedDates, dateStr, instance) {
                    instance.element.form.submit();
                }
            });

            flatpickr('#bulk_month', {
                dateFormat: 'Y-m-01',
                altInput: true,
                altFormat: 'F Y',
                allowInput: false,
                disableMobile: true,
                static: true,
                plugins: [
                    new monthSelectPlugin({
                        shorthand: false,
                        dateFormat: 'Y-m-01',
                        altFormat: 'F Y',
                        theme: 'light',
                    })
                ],
            });

            flatpickr('#bulk_due_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
                static: true,
            });
        });
    </script>
@endpush