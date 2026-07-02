@extends('layouts.app')

@section('containerClass', 'w-full max-w-full')

@section('content')
<div>

    {{-- ── Filter Panel ───────────────────────────────────────────────────── --}}
    @php
        $appliedFilters = [];

        $badge = function($label, $value, $color) {
            $classes = match($color) {
                'blue' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/30 dark:text-blue-300 dark:border-blue-900/50',
                'purple' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/30 dark:text-purple-300 dark:border-purple-900/50',
                'pink' => 'bg-pink-50 text-pink-700 border-pink-200 dark:bg-pink-950/30 dark:text-pink-300 dark:border-pink-900/50',
                'teal' => 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/30 dark:text-teal-300 dark:border-teal-900/50',
                'amber' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/30 dark:text-amber-300 dark:border-amber-900/50',
                'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-300 dark:border-emerald-900/50',
                'indigo' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/30 dark:text-indigo-300 dark:border-indigo-900/50',
                default => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-950/30 dark:text-gray-300 dark:border-gray-900/50',
            };
            return '<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold border ' . $classes . '">' .
                '<span class="opacity-75 mr-1">' . e($label) . ':</span>' . e($value) .
                '</span>';
        };

        // Report Type
        $reportType = $filters['report_type'] ?? '';
        $reportTypeLabel = match($reportType) {
            'rent' => 'Rent Collected',
            'maintinance' => 'Maintenance',
            'utilities' => 'Utilities Paid',
            'fines' => 'Fines',
            'other_owned' => 'Other Owned',
            'occupide' => 'Occupied (Ext)',
            'non_occupide' => 'Vacant (Ext)',
            'monthly_matrix' => 'Monthly Matrix',
            'potential_revenue' => 'Fully Rented Forecast',
            default => 'All Data',
        };

        // Dates
        if (!empty($filters['date_from'])) {
            $appliedFilters[] = $badge('From', \Carbon\Carbon::parse($filters['date_from'])->format('d M Y'), 'blue');
        }
        if (!empty($filters['date_to'])) {
            $appliedFilters[] = $badge('To', \Carbon\Carbon::parse($filters['date_to'])->format('d M Y'), 'blue');
        }

        // Unit
        if (!empty($filters['unit_id'])) {
            $selectedUnit = $units->firstWhere('id', $filters['unit_id']);
            if ($selectedUnit) {
                $appliedFilters[] = $badge('Unit', $selectedUnit->unit_number, 'purple');
            }
        }

        // Unit Status
        if (!empty($filters['unit_status'])) {
            $appliedFilters[] = $badge('Unit Status', ucfirst($filters['unit_status']), 'purple');
        }

        // Tenant
        if (!empty($filters['tenant_id'])) {
            $selectedTenant = $tenants->firstWhere('id', $filters['tenant_id']);
            if ($selectedTenant) {
                $appliedFilters[] = $badge('Tenant', $selectedTenant->name, 'pink');
            }
        }

        // Status
        if (!empty($filters['status'])) {
            $appliedFilters[] = $badge('Status', ucfirst($filters['status']), 'amber');
        }

        // Landlord
        if (!empty($filters['landlord_id'])) {
            $selectedLandlord = $landlords->firstWhere('id', $filters['landlord_id']);
            if ($selectedLandlord) {
                $appliedFilters[] = $badge('Landlord', $selectedLandlord->name, 'teal');
            }
        }

        // Owner Type
        if (!empty($filters['owner_type'])) {
            $appliedFilters[] = $badge('Owner Type', $filters['owner_type'] === 'pm_mall' ? 'PM Mall Owners' : 'Other Owners', 'emerald');
        }

        // Payment Method
        if (!empty($filters['payment_method'])) {
            $appliedFilters[] = $badge('Payment Method', ucfirst(str_replace('_', ' ', $filters['payment_method'])), 'indigo');
        }

        // Payment Account
        if (!empty($filters['payment_account_id'])) {
            $selectedAccount = $paymentAccounts->firstWhere('id', $filters['payment_account_id']);
            if ($selectedAccount) {
                $appliedFilters[] = $badge('Account', $selectedAccount->name, 'indigo');
            }
        }
    @endphp

    <!-- Filters Panel -->
    <div class="mb-6 rounded-2xl bg-white p-6 shadow-sm border border-gray-100 dark:bg-gray-900 dark:border-gray-800">
        <form method="GET" action="{{ route('reports.index') }}" id="reportForm">
            <input type="hidden" name="no_sidebar" value="1">

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 items-end">

                {{-- Report Type --}}
                <div>
                    <label for="report_type" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Report Type
                    </label>
                    <select id="report_type" name="report_type" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="" {{ ($filters['report_type'] ?? '') === '' ? 'selected' : '' }}>All Data</option>
                        <option value="rent" {{ ($filters['report_type'] ?? '') === 'rent' ? 'selected' : '' }}>Rent Collected</option>
                        <option value="maintenance" {{ ($filters['report_type'] ?? '') === 'maintenance' || ($filters['report_type'] ?? '') === 'maintinance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="utilities" {{ ($filters['report_type'] ?? '') === 'utilities' ? 'selected' : '' }}>Utilities Paid</option>
                        <option value="fines" {{ ($filters['report_type'] ?? '') === 'fines' ? 'selected' : '' }}>Fines</option>
                        <option value="other_owned" {{ ($filters['report_type'] ?? '') === 'other_owned' ? 'selected' : '' }}>Other Owned</option>
                        <option value="occupied" {{ ($filters['report_type'] ?? '') === 'occupied' || ($filters['report_type'] ?? '') === 'occupide' ? 'selected' : '' }}>Occupied (Ext)</option>
                        <option value="non_occupied" {{ ($filters['report_type'] ?? '') === 'non_occupied' || ($filters['report_type'] ?? '') === 'non_occupide' ? 'selected' : '' }}>Vacant (Ext)</option>
                        <option value="monthly_matrix" {{ ($filters['report_type'] ?? '') === 'monthly_matrix' ? 'selected' : '' }}>Monthly Matrix</option>
                        <option value="potential_revenue" {{ ($filters['report_type'] ?? '') === 'potential_revenue' ? 'selected' : '' }}>Fully Rented Forecast</option>
                    </select>
                </div>

                {{-- Date From --}}
                <div class="{{ ($filters['report_type'] ?? '') === 'potential_revenue' ? 'hidden' : '' }}">
                    <label for="date_from" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ ($filters['report_type'] ?? '') === 'monthly_matrix' ? 'Report Month' : 'Date From' }}
                    </label>
                    <input type="text" id="date_from" name="date_from"
                        value="{{ $filters['date_from'] ?? '' }}"
                        placeholder="{{ ($filters['report_type'] ?? '') === 'monthly_matrix' ? 'Select Month' : 'Start date' }}"
                        autocomplete="off"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
                </div>

                {{-- Date To --}}
                <div class="{{ in_array(($filters['report_type'] ?? ''), ['monthly_matrix', 'potential_revenue']) ? 'hidden' : '' }}">
                    <label for="date_to" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Date To
                    </label>
                    <input type="text" id="date_to" name="date_to"
                        value="{{ $filters['date_to'] ?? '' }}"
                        placeholder="End date"
                        autocomplete="off"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
                </div>

                {{-- Unit --}}
                <div>
                    <label for="unit_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Flat / Shop
                    </label>
                    <select id="unit_id" name="unit_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Flats/Shops</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ ($filters['unit_id'] ?? '') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->unit_number }} ({{ ucfirst($unit->type) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Unit Status --}}
                <div>
                    <label for="unit_status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Flat/Shop Status
                    </label>
                    <select id="unit_status" name="unit_status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Unit Statuses</option>
                        <option value="rented" {{ ($filters['unit_status'] ?? '') === 'rented' ? 'selected' : '' }}>Rented</option>
                        <option value="vacant" {{ ($filters['unit_status'] ?? '') === 'vacant' ? 'selected' : '' }}>Vacant</option>
                    </select>
                </div>

                {{-- Tenant --}}
                <div>
                    <label for="tenant_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tenant
                    </label>
                    <select id="tenant_id" name="tenant_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Tenants</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}" {{ ($filters['tenant_id'] ?? '') == $tenant->id ? 'selected' : '' }}>
                                {{ $tenant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status
                    </label>
                    <select id="status" name="status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Statuses</option>
                        <option value="paid"    {{ ($filters['status'] ?? '') === 'paid'    ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid"  {{ ($filters['status'] ?? '') === 'unpaid'  ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ ($filters['status'] ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>

                {{-- Landlord --}}
                <div>
                    <label for="landlord_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Landlord
                    </label>
                    <select id="landlord_id" name="landlord_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Landlords</option>
                        @foreach($landlords as $landlord)
                            <option value="{{ $landlord->id }}" {{ ($filters['landlord_id'] ?? '') == $landlord->id ? 'selected' : '' }}>
                                {{ $landlord->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Owner Type --}}
                <div>
                    <label for="owner_type" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Owner Type
                    </label>
                    <select id="owner_type" name="owner_type"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Owners</option>
                        <option value="pm_mall" {{ ($filters['owner_type'] ?? '') === 'pm_mall' ? 'selected' : '' }}>PM Mall Owners</option>
                        <option value="other" {{ ($filters['owner_type'] ?? '') === 'other' ? 'selected' : '' }}>Other Owners</option>
                    </select>
                </div>

                {{-- Payment Method --}}
                <div>
                    <label for="payment_method" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Payment Method
                    </label>
                    <select id="payment_method" name="payment_method"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Payment Methods</option>
                        <option value="cash" {{ ($filters['payment_method'] ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="bank_transfer" {{ ($filters['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cheque" {{ ($filters['payment_method'] ?? '') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="other" {{ ($filters['payment_method'] ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                {{-- Payment Account --}}
                <div>
                    <label for="payment_account_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Payment Account
                    </label>
                    <select id="payment_account_id" name="payment_account_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Accounts</option>
                        @foreach($paymentAccounts as $account)
                            <option value="{{ $account->id }}" {{ ($filters['payment_account_id'] ?? '') == $account->id ? 'selected' : '' }}>
                                {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Generate Report
                </button>

                <a href="{{ route('reports.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                    Reset
                </a>

                {{-- Export buttons — only show when results exist --}}
                @if($hasQuery && $entries->isNotEmpty())
                    <div class="ml-auto flex gap-2">
                        <a href="{{ route('reports.excel', $filters) }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-green-500 px-4 py-2.5 text-sm font-medium text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Export Excel
                        </a>
                        <a href="{{ route('reports.pdf', $filters) }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-red-400 px-4 py-2.5 text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Export PDF
                        </a>
                        <a href="{{ route('reports.print', $filters) }}"
                            onclick="window.open(this.href,'_blank','width=1100,height=800,scrollbars=yes'); return false;"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                            🖨️ Print
                        </a>
                    </div>
                @endif

            </div>
        </div>

    {{-- ── Results ──────────────────────────────────────────────────────────── --}}
    @if($hasQuery)
        @php
            $selectedMonth = ($filters['date_from'] ?? false)
                ? \Carbon\Carbon::parse($filters['date_from'])->format('F Y')
                : \Carbon\Carbon::now()->format('F Y');
        @endphp



        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                Report Summary
            </h2>
        </div>

        {{-- Dynamic Summary Widgets --}}
        @if($summary)
            @php
                $isMatrix = ($filters['report_type'] ?? '') === 'monthly_matrix';

                // Calculate widget data
                $wGrandDue = 0; $wGrandPaid = 0; $wGrandUnpaid = 0;
                $wRentDue = 0; $wRentPaid = 0; $wRentUnpaid = 0;
                $wServDue = 0; $wServPaid = 0; $wServUnpaid = 0;
                $wSecDue = 0;  $wSecPaid = 0;  $wSecUnpaid = 0;

                if ($isMatrix) {
                    $wGrandDue = $summary['total_amount'] ?? 0;
                    $wGrandPaid = $summary['total_received'] ?? 0;
                    $wGrandUnpaid = $summary['total_pending'] ?? 0;

                    $wRentDue = $summary['total_rent'] ?? 0;
                    $wRentPaid = $summary['total_rent_paid'] ?? 0;
                    $wRentUnpaid = max(0, $wRentDue - $wRentPaid);

                    $wServDue = ($summary['total_serv'] ?? 0) + ($summary['total_extra'] ?? 0);
                    $wServPaid = ($summary['total_serv_paid'] ?? 0) + ($summary['total_extra_paid'] ?? 0);
                    $wServUnpaid = max(0, $wServDue - $wServPaid);

                    $wSecDue = $summary['total_security_deposit'] ?? 0;
                    $wSecPaid = $summary['total_sec_paid'] ?? 0;
                    $wSecUnpaid = max(0, $wSecDue - $wSecPaid);
                } elseif (isset($entries) && $entries instanceof \Illuminate\Support\Collection) {
                    $wGrandDue = $summary['total_due'] ?? 0;
                    $wGrandPaid = $summary['total_paid'] ?? 0;
                    $wGrandUnpaid = $summary['outstanding'] ?? 0;

                    $wRentDue = $entries->where('type', 'rent')->sum('amount_due');
                    $wRentPaid = $entries->where('type', 'rent')->sum('amount_paid');
                    $wRentUnpaid = max(0, $wRentDue - $wRentPaid);

                    $servTypes = ['maintenance', 'utility', 'fine', 'other'];
                    $wServDue = $entries->whereIn('type', $servTypes)->sum('amount_due');
                    $wServPaid = $entries->whereIn('type', $servTypes)->sum('amount_paid');
                    $wServUnpaid = max(0, $wServDue - $wServPaid);

                    $wSecDue = $entries->where('type', 'security_deposit')->sum('amount_due');
                    $wSecPaid = $entries->where('type', 'security_deposit')->sum('amount_paid');
                    $wSecUnpaid = max(0, $wSecDue - $wSecPaid);
                }

                $historyWidgets = [
                    'grand_total' => [
                        'label' => 'Grand Total Summary',
                        'gradient' => 'linear-gradient(135deg, #465fff 0%, #2a31d8 100%)',
                        'icon' => '📊',
                        'due' => $wGrandDue, 'paid' => $wGrandPaid, 'unpaid' => $wGrandUnpaid,
                    ],
                    'rent' => [
                        'label' => 'Rent Summary',
                        'gradient' => 'linear-gradient(135deg, #f04438 0%, #912018 100%)',
                        'icon' => '🔑',
                        'due' => $wRentDue, 'paid' => $wRentPaid, 'unpaid' => $wRentUnpaid,
                    ],
                    'services' => [
                        'label' => 'Services Summary',
                        'gradient' => 'linear-gradient(135deg, #7a5af8 0%, #2a31d8 100%)',
                        'icon' => '🛠️',
                        'due' => $wServDue, 'paid' => $wServPaid, 'unpaid' => $wServUnpaid,
                    ],
                    'security_deposit' => [
                        'label' => 'Security Deposit',
                        'gradient' => 'linear-gradient(135deg, #a855f7 0%, #701a75 100%)',
                        'icon' => '🛡️',
                        'due' => $wSecDue, 'paid' => $wSecPaid, 'unpaid' => $wSecUnpaid,
                    ],
                ];
            @endphp

            @if(($filters['report_type'] ?? '') === 'potential_revenue')
                <!-- Potential Revenue Grid -->
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 mb-6">
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total Flats/Shops</p>
                        <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white leading-tight">{{ number_format($summary['count']) }}</h4>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Potential Rent</p>
                        <h4 class="mt-2 text-2xl font-bold text-blue-600">Rs. {{ number_format($summary['total_rent']) }}</h4>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Potential Maint.</p>
                        <h4 class="mt-2 text-2xl font-bold text-purple-600">Rs. {{ number_format($summary['total_maintenance']) }}</h4>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Combined Potential</p>
                        <h4 class="mt-2 text-2xl font-bold text-emerald-600">Rs. {{ number_format($summary['total_combined']) }}</h4>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Rented (Agreement)</p>
                        <h4 class="mt-2 text-2xl font-bold text-brand-600">{{ number_format($summary['rented_count']) }}</h4>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Vacant/Other (Default)</p>
                        <h4 class="mt-2 text-2xl font-bold text-orange-500">{{ number_format($summary['vacant_count']) }}</h4>
                    </div>
                </div>
            @else
                <!-- Dynamic Billing History Style Widgets -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    @foreach($historyWidgets as $wKey => $cfg)
                        <div class="group relative overflow-hidden rounded-2xl p-4 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                            style="background: {{ $cfg['gradient'] }}; min-height: 140px;">
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                            <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                            <div class="relative flex justify-between items-center mb-2">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-white/75">{{ $cfg['label'] }}</p>
                                <span class="text-sm">{{ $cfg['icon'] }}</span>
                            </div>
                            <div class="relative mt-2 space-y-1">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-[10px] uppercase text-white/70">Expected Total</span>
                                    <span class="font-bold text-white text-sm sm:text-base">
                                        Rs. {{ number_format($cfg['due']) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-[10px] uppercase text-white/70">Received</span>
                                    <span class="font-bold text-emerald-300 text-sm sm:text-base">
                                        Rs. {{ number_format($cfg['paid']) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                                    <span class="text-[10px] uppercase text-white/70">Pending</span>
                                    <span class="font-bold text-rose-300 text-sm sm:text-base">
                                        Rs. {{ number_format($cfg['unpaid']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        {{-- Data Table --}}
        <div class="mt-4">
            <x-common.component-card
                title="{{ ($filters['report_type'] ?? '') === 'monthly_matrix' ? 'Monthly Matrix - ' . $selectedMonth : (($filters['report_type'] ?? '') === 'potential_revenue' ? 'Fully Rented Potential Revenue Forecast' : 'Report Results') }}"
                desc="{{ ($filters['report_type'] ?? '') === 'monthly_matrix' ? 'Grid matrix for flat status and collections' : (($filters['report_type'] ?? '') === 'potential_revenue' ? 'Potential monthly revenue snapshot for all flats and shops' : $summary['count'] . ' ' . Str::plural('record', $summary['count']) . ' found') }}">

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-4 mb-4">
                    <div>
                        @if(count($appliedFilters) > 0)
                            <div class="flex flex-wrap items-center gap-2">
                                @foreach($appliedFilters as $filterBadge)
                                    {!! $filterBadge !!}
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Showing all records (No filters applied)
                            </p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors shadow-xs">
                            ✕ Exit
                        </a>
                    </div>
                </div>

                @if($entries->isEmpty())
                    <div class="py-12 text-center text-gray-400">
                        <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm font-medium">No records found for the selected filters.</p>
                        <p class="mt-1 text-xs text-gray-300 dark:text-gray-600">Try adjusting the date range or removing filters.</p>
                    </div>
                @else


                    @if(($filters['report_type'] ?? '') === 'monthly_matrix')
                        @include('reports.partials.matrix_table')
                    @elseif(($filters['report_type'] ?? '') === 'potential_revenue')
                        @include('reports.partials.potential_revenue_table')
                    @else
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                            <table id="reportTable" class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-white">
                                    <tr>
                                        <th class="px-4 py-3">#</th>
                                        <th class="px-4 py-3">Month</th>
                                        <th class="px-4 py-3">Flat/Shop</th>
                                        <th class="px-4 py-3">Tenant</th>
                                        <th class="px-4 py-3">Landlord</th>
                                        <th class="px-4 py-3">Type</th>
                                        <th class="px-4 py-3">Payment Method</th>
                                        <th class="px-4 py-3">Payment Account</th>
                                        <th class="px-4 py-3">Security Deposit</th>
                                        <th class="px-4 py-3">Amount Due</th>
                                        <th class="px-4 py-3">Amount Paid</th>
                                        <th class="px-4 py-3">Balance</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Paid At</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($entries as $i => $entry)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>

                                            <td data-order="{{ $entry['month'] instanceof \Carbon\Carbon ? $entry['month']->toDateString() : '' }}" class="px-4 py-3 text-xs font-medium text-gray-700 dark:text-gray-300">
                                                {{ $entry['month'] instanceof \Carbon\Carbon ? $entry['month']->format('M Y') : '—' }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                                    {{ $entry['unit'] }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                                {{ $entry['tenant'] }}
                                            </td>

                                            <td class="px-4 py-3 text-gray-700 dark:text-white/80">
                                                {{ $entry['landlord'] }}
                                            </td>

                                            <td class="px-4 py-3">
                                                @php
                                                    $typeClass = match($entry['type']) {
                                                        'rent'        => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                        'fine'        => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                                                        'electricity' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                        'water'       => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
                                                        'gas'         => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                                        'maintenance' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                                        default       => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $typeClass }}">
                                                    {{ ucfirst($entry['type']) }}
                                                </span>
                                            </td>

                                            <td class="px-4 py-3 text-gray-700 dark:text-white/80">
                                                {{ $entry['payment_method'] }}
                                            </td>

                                            <td class="px-4 py-3 text-gray-700 dark:text-white/80">
                                                {{ $entry['payment_account'] }}
                                            </td>

                                            <td data-order="{{ $entry['security_deposit'] }}" class="px-4 py-3 font-medium text-indigo-600 dark:text-indigo-400 text-xs">
                                                {{ $entry['security_deposit'] > 0 ? ('Rs. ' . number_format($entry['security_deposit'])) : '—' }}
                                            </td>

                                            <td data-order="{{ $entry['amount_due'] }}" class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                                Rs. {{ number_format($entry['amount_due']) }}
                                            </td>

                                            <td data-order="{{ $entry['amount_paid'] }}" class="px-4 py-3 font-medium text-green-600">
                                                Rs. {{ number_format($entry['amount_paid']) }}
                                            </td>

                                            <td data-order="{{ $entry['balance'] }}" class="px-4 py-3 font-semibold {{ $entry['balance'] > 0 ? 'text-red-500' : 'text-green-600' }}">
                                                Rs. {{ number_format($entry['balance']) }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="flex flex-col gap-1 items-start">
                                                    @php
                                                        $statusClass = match($entry['status']) {
                                                            'paid'    => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                            'partial' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                            default   => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $statusClass }}">
                                                        {{ ucfirst($entry['status']) }}
                                                    </span>
                                                    @if(!empty($entry['is_self']))
                                                        <span class="inline-flex items-center gap-0.5 rounded-md bg-violet-100 px-1.5 py-0.5 text-[10px] font-semibold text-violet-700 dark:bg-violet-900/30 dark:text-violet-400">
                                                            Other-Owned
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>

                                            <td data-order="{{ $entry['paid_at'] instanceof \Carbon\Carbon ? $entry['paid_at']->toDateString() : '0000-00-00' }}" class="px-4 py-3 text-xs text-gray-500">
                                                {{ $entry['paid_at'] instanceof \Carbon\Carbon ? $entry['paid_at']->format('d M Y') : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50 dark:bg-gray-800 font-semibold text-sm">
                                        <td colspan="9" class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                            Totals ({{ $summary['count'] }} records)
                                        </td>
                                        <td class="px-4 py-3 text-gray-800 dark:text-white">
                                            Rs. {{ number_format($summary['total_due']) }}
                                        </td>
                                        <td class="px-4 py-3 text-green-600">
                                            Rs. {{ number_format($summary['total_paid']) }}
                                        </td>
                                        <td class="px-4 py-3 {{ $summary['outstanding'] > 0 ? 'text-red-500' : 'text-green-600' }}">
                                            Rs. {{ number_format($summary['outstanding']) }}
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                @endif

            </x-common.component-card>
        </div>

    @else
        {{-- Empty state --}}
        <div class="mt-6 rounded-2xl border border-dashed border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-white/[0.03]">
            <svg class="mx-auto mb-4 h-14 w-14 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">No report generated yet</p>
            <p class="mt-1 mb-6 text-xs text-gray-400 dark:text-gray-600">Configure filters above and click Generate Report.</p>
            <div class="flex items-center justify-center gap-3">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors shadow-xs">
                    ✕ Exit
                </a>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<style>
    #reportTable th,
    #reportTable td {
        padding-top: 0.875rem !important;
        padding-bottom: 0.875rem !important;
    }
    [x-cloak] {
        display: none !important;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('reportTable');
    if (table) {
        const headers = table.querySelectorAll('thead th');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        if (rows.length > 0) {
            headers.forEach((header, index) => {
                // Skip the serial number column
                if (index === 0) return;

                header.style.cursor = 'pointer';
                header.classList.add('select-none', 'hover:bg-gray-100', 'dark:hover:bg-gray-800/50');
                
                let asc = true;

                header.addEventListener('click', () => {
                    // Remove indicators from all headers
                    headers.forEach((h) => {
                        const arrow = h.querySelector('.sort-arrow');
                        if (arrow) arrow.remove();
                    });

                    // Append sorting direction indicator to active header
                    const arrow = document.createElement('span');
                    arrow.className = 'sort-arrow ml-1 text-gray-400 font-bold';
                    arrow.textContent = asc ? ' ▲' : ' ▼';
                    header.appendChild(arrow);

                    // Sort row elements
                    const sortedRows = rows.sort((a, b) => {
                        const cellA = a.cells[index]?.innerText || a.cells[index]?.textContent || '';
                        const cellB = b.cells[index]?.innerText || b.cells[index]?.textContent || '';

                        const cleanNum = (str) => {
                            let s = str.replace(/[^\d.-]/g, '');
                            return s ? parseFloat(s) : NaN;
                        };

                        const valA = cleanNum(cellA);
                        const valB = cleanNum(cellB);

                        if (!isNaN(valA) && !isNaN(valB)) {
                            return asc ? valA - valB : valB - valA;
                        }

                        return asc 
                            ? cellA.localeCompare(cellB, undefined, { numeric: true, sensitivity: 'base' })
                            : cellB.localeCompare(cellA, undefined, { numeric: true, sensitivity: 'base' });
                    });

                    // Re-append sorted rows to body
                    tbody.innerHTML = '';
                    sortedRows.forEach(row => tbody.appendChild(row));

                    // Re-index the SR column
                    sortedRows.forEach((row, i) => {
                        const srCell = row.cells[0];
                        if (srCell) {
                            srCell.textContent = i + 1;
                        }
                    });

                    asc = !asc;
                });
            });
        }
    }

    // Date pickers
    if (typeof flatpickr !== 'undefined') {
        @if(($filters['report_type'] ?? '') === 'monthly_matrix')
            flatpickr('#date_from', {
                dateFormat: 'Y-m-01',
                altInput: true,
                altFormat: 'F Y',
                disableMobile: true,
                plugins: [
                    new monthSelectPlugin({
                        shorthand: false,
                        dateFormat: 'Y-m-01',
                        altFormat: 'F Y',
                        theme: 'light',
                    })
                ],
            });
        @else
            flatpickr('#date_from', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                allowInput: true,
                disableMobile: true,
            });
        @endif

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

<script>
    // Collapse the sidebar automatically on the Reports page
    document.addEventListener('alpine:init', function () {
        Alpine.nextTick(function () {
            if (window.innerWidth >= 1280 && Alpine.store('sidebar')) {
                Alpine.store('sidebar').isExpanded = false;
            }
        });
    });

    // Fallback: collapse after DOMContentLoaded in case alpine:init already fired
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Alpine !== 'undefined' && window.innerWidth >= 1280) {
            const sidebar = Alpine.store('sidebar');
            if (sidebar) sidebar.isExpanded = false;
        }
    });
</script>
@endpush

