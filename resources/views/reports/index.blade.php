@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Reports" />

    {{-- ── Report Type Tabs ──────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-wrap gap-2">
        @php
            $activeType = $filters['report_type'] ?? '';
            $tabs = [
                ''               => ['label' => 'All Data',        'icon' => '📊'],
                'rent'           => ['label' => 'Rent Collected',  'icon' => '🏠'],
                'maintinance'    => ['label' => 'Maintenance',     'icon' => '🛠️'],
                'utilities'      => ['label' => 'Utilities Paid',  'icon' => '⚡'],
                'fines'          => ['label' => 'Fines',           'icon' => '⚠️'],
                'other_owned'    => ['label' => 'Other Owned',     'icon' => '🔑'],
                'occupide'       => ['label' => 'Occupied (Ext)',  'icon' => '👥'],
                'non_occupide'   => ['label' => 'Vacant (Ext)',    'icon' => '🚪'],
                'monthly_matrix' => ['label' => 'Monthly Matrix',  'icon' => '📅'],
            ];
        @endphp
        @foreach($tabs as $typeKey => $tab)
            @php
                $tabParams = array_merge($filters, ['report_type' => $typeKey]);
                $isActive  = $activeType === $typeKey;
            @endphp
            <a href="{{ route('reports.index', $tabParams) }}"
               class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all
                      {{ $isActive
                          ? 'bg-brand-500 text-white shadow-sm'
                          : 'border border-gray-200 bg-white text-gray-600 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300' }}">
                <span>{{ $tab['icon'] }}</span>
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    {{-- ── Filter Panel ───────────────────────────────────────────────────── --}}
    <x-common.component-card title="Report Filters" desc="Apply filters to generate your report">
        <form method="GET" action="{{ route('reports.index') }}" id="reportForm">
            <input type="hidden" name="report_type" value="{{ $filters['report_type'] ?? '' }}">

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                {{-- Date From --}}
                <div>
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
                <div class="{{ ($filters['report_type'] ?? '') === 'monthly_matrix' ? 'hidden' : '' }}">
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
        </form>
    </x-common.component-card>

    {{-- ── Results ──────────────────────────────────────────────────────────── --}}
    @if($hasQuery)
        @php
            $selectedMonth = ($filters['date_from'] ?? false)
                ? \Carbon\Carbon::parse($filters['date_from'])->format('F Y')
                : \Carbon\Carbon::now()->format('F Y');
        @endphp

        @if(($filters['report_type'] ?? '') === 'monthly_matrix')
            <div class="mb-4 rounded-xl border border-brand-100 bg-brand-50 p-4 dark:border-brand-900/20 dark:bg-brand-950/10">
                <div class="flex items-center gap-3">
                    <span class="text-xl">📅</span>
                    <div>
                        <h4 class="text-sm font-semibold text-brand-900 dark:text-brand-200">Monthly Matrix Report</h4>
                        <p class="text-xs text-brand-600 dark:text-brand-400">Showing flat status and collections breakdown for the billing month of <strong class="font-bold text-brand-700 dark:text-brand-300">{{ $selectedMonth }}</strong></p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Summary Cards --}}
        @if($summary)
            @php
                $isMatrix = ($filters['report_type'] ?? '') === 'monthly_matrix';
                $totalDue = $isMatrix ? ($summary['total_amount'] ?? 0) : ($summary['total_due'] ?? 0);
                $totalPaid = $isMatrix ? ($summary['total_received'] ?? 0) : ($summary['total_paid'] ?? 0);
                $outstanding = $isMatrix ? ($summary['total_pending'] ?? 0) : ($summary['outstanding'] ?? 0);

                if ($isMatrix) {
                    $card5Label = '🏠 Rent Due';
                    $card5Value = $summary['total_rent'] ?? 0;
                    $card5Class = 'text-blue-600';
                    $card6Label = '🛠️ Services Due';
                    $card6Value = $summary['total_serv'] ?? 0;
                    $card6Class = 'text-purple-600';
                } else {
                    $reportType = $filters['report_type'] ?? '';
                    if ($reportType === 'rent') {
                        $card5Label = '🏠 Rent Collected';
                        $card5Value = $summary['rent_collected'] ?? 0;
                        $card5Class = 'text-blue-600';
                        $card6Label = '🏠 Rent Outstanding';
                        $card6Value = $outstanding;
                        $card6Class = $outstanding > 0 ? 'text-red-500' : 'text-green-600';
                    } elseif ($reportType === 'maintinance' || $reportType === 'maintenance') {
                        $card5Label = '🛠️ Maintenance Collected';
                        $card5Value = $summary['maintenance_collected'] ?? 0;
                        $card5Class = 'text-purple-600';
                        $card6Label = '🛠️ Maintenance Outstanding';
                        $card6Value = $outstanding;
                        $card6Class = $outstanding > 0 ? 'text-red-500' : 'text-green-600';
                    } elseif ($reportType === 'utilities') {
                        $card5Label = '⚡ Utilities Paid';
                        $card5Value = $summary['utilities_paid'] ?? 0;
                        $card5Class = 'text-yellow-600';
                        $card6Label = '⚡ Utilities Outstanding';
                        $card6Value = $outstanding;
                        $card6Class = $outstanding > 0 ? 'text-red-500' : 'text-green-600';
                    } elseif ($reportType === 'fines') {
                        $card5Label = '⚠️ Fines Collected';
                        $card5Value = $summary['fines_collected'] ?? 0;
                        $card5Class = 'text-red-600';
                        $card6Label = '⚠️ Fines Outstanding';
                        $card6Value = $outstanding;
                        $card6Class = $outstanding > 0 ? 'text-red-500' : 'text-green-600';
                    } elseif ($reportType === 'other_owned' || $reportType === 'occupied' || $reportType === 'occupide' || $reportType === 'non_occupied' || $reportType === 'non_occupide') {
                        $card5Label = match ($reportType) {
                            'occupied', 'occupide' => '👥 Occupied (Ext) Collected',
                            'non_occupied', 'non_occupide' => '🚪 Vacant (Ext) Collected',
                            default => '🔑 Other Owned Collected',
                        };
                        $card5Value = $summary['maintenance_collected'] ?? 0;
                        $card5Class = 'text-purple-600';
                        $card6Label = match ($reportType) {
                            'occupied', 'occupide' => '👥 Occupied (Ext) Outstanding',
                            'non_occupied', 'non_occupide' => '🚪 Vacant (Ext) Outstanding',
                            default => '🔑 Other Owned Outstanding',
                        };
                        $card6Value = $outstanding;
                        $card6Class = $outstanding > 0 ? 'text-red-500' : 'text-green-600';
                    } else {
                        $card5Label = '🏠 Rent Collected';
                        $card5Value = $summary['rent_collected'] ?? 0;
                        $card5Class = 'text-blue-600';
                        $card6Label = '🛠️ Maintenance Collected';
                        $card6Value = $summary['maintenance_collected'] ?? 0;
                        $card6Class = 'text-purple-600';
                    }
                }
            @endphp
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">

                <div class="col-span-1 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">{{ $isMatrix ? 'Report Month' : 'Records Found' }}</p>
                    <p class="mt-1 text-base font-bold text-gray-800 dark:text-white leading-tight">{{ $isMatrix ? $selectedMonth : number_format($summary['count']) }}</p>
                </div>

                <div class="col-span-1 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">Total Due</p>
                    <p class="mt-1 text-lg font-bold text-orange-500">Rs. {{ number_format($totalDue) }}</p>
                </div>

                <div class="col-span-1 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">{{ $isMatrix ? 'Total Received' : 'Total Collected' }}</p>
                    <p class="mt-1 text-lg font-bold text-green-600">Rs. {{ number_format($totalPaid) }}</p>
                </div>

                <div class="col-span-1 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">{{ $isMatrix ? 'Total Pending' : 'Outstanding' }}</p>
                    <p class="mt-1 text-lg font-bold {{ $outstanding > 0 ? 'text-red-500' : 'text-green-600' }}">
                        Rs. {{ number_format($outstanding) }}
                    </p>
                </div>

                <div class="col-span-1 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">{{ $card5Label }}</p>
                    <p class="mt-1 text-lg font-bold {{ $card5Class }}">Rs. {{ number_format($card5Value) }}</p>
                </div>

                <div class="col-span-1 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">{{ $card6Label }}</p>
                    <p class="mt-1 text-lg font-bold {{ $card6Class }}">Rs. {{ number_format($card6Value) }}</p>
                </div>

            </div>
        @endif

        {{-- Data Table --}}
        <div class="mt-4">
            <x-common.component-card
                title="{{ ($filters['report_type'] ?? '') === 'monthly_matrix' ? 'Monthly Matrix - ' . $selectedMonth : 'Report Results' }}"
                desc="{{ ($filters['report_type'] ?? '') === 'monthly_matrix' ? 'Grid matrix for flat status and collections' : $summary['count'] . ' ' . Str::plural('record', $summary['count']) . ' found' }}">

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
                    @else
                        <div class="max-h-[calc(100vh-320px)] overflow-auto rounded-lg border border-gray-200 dark:border-gray-800">
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
                                        <td colspan="8" class="px-4 py-3 text-gray-700 dark:text-gray-300">
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
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-600">Select a report type using the tabs above, apply filters and click <strong>Generate Report</strong></p>
        </div>
    @endif

@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.min.css">
<style>
    /* DataTables Tailwind Custom Theme Integration */
    .dt-container {
        padding: 1.25rem 0 !important;
        background-color: transparent;
    }
    .dt-layout-row {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    @media (min-width: 640px) {
        .dt-layout-row {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }
    }
    .dt-search {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .dt-search label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #667085;
    }
    .dark .dt-search label {
        color: #98a2b3;
    }
    .dt-search input {
        border-radius: 0.5rem !important;
        border: 1px solid #d0d5dd !important;
        background-color: #ffffff !important;
        padding: 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
        color: #1d2939 !important;
        outline: none !important;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .dark .dt-search input {
        border-color: #344054 !important;
        background-color: #0c111d !important;
        color: rgba(255, 255, 255, 0.9) !important;
    }
    .dt-search input:focus {
        border-color: #465fff !important;
        box-shadow: 0 0 0 1px #465fff !important;
    }
    .dt-length select {
        border-radius: 0.5rem !important;
        border: 1px solid #d0d5dd !important;
        background-color: #ffffff !important;
        padding: 0.5rem 2rem 0.5rem 0.75rem !important;
        font-size: 0.875rem !important;
        color: #1d2939 !important;
        outline: none !important;
        margin-right: 0.5rem !important;
    }
    .dark .dt-length select {
        border-color: #344054 !important;
        background-color: #0c111d !important;
        color: rgba(255, 255, 255, 0.9) !important;
    }
    .dt-length select:focus {
        border-color: #465fff !important;
        box-shadow: 0 0 0 1px #465fff !important;
    }
    .dt-length label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #667085;
    }
    .dark .dt-length label {
        color: #98a2b3;
    }
    .dt-info {
        font-size: 0.875rem !important;
        color: #667085 !important;
    }
    .dark .dt-info {
        color: #98a2b3 !important;
    }
    .dt-paging {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        margin-top: 0.5rem;
    }
    @media (min-width: 640px) {
        .dt-paging {
            margin-top: 0;
        }
    }
    .dt-paging-button {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border-radius: 0.5rem !important;
        border: 1px solid #e4e7ec !important;
        background-color: #ffffff !important;
        padding: 0.4rem 0.75rem !important;
        font-size: 0.875rem !important;
        font-weight: 500 !important;
        color: #344054 !important;
        transition: background-color 0.2s, color 0.2s, border-color 0.2s;
        cursor: pointer;
    }
    .dark .dt-paging-button {
        border-color: #1d2939 !important;
        background-color: #0c111d !important;
        color: #d0d5dd !important;
    }
    .dt-paging-button:hover:not(.disabled) {
        background-color: #f9fafb !important;
        color: #111827 !important;
    }
    .dark .dt-paging-button:hover:not(.disabled) {
        background-color: rgba(255, 255, 255, 0.05) !important;
        color: #ffffff !important;
    }
    .dt-paging-button.current {
        background-color: #465fff !important;
        border-color: #465fff !important;
        color: #ffffff !important;
    }
    .dark .dt-paging-button.current {
        background-color: #465fff !important;
        border-color: #465fff !important;
        color: #ffffff !important;
    }
    .dt-paging-button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    /* Sorting active headers */
    table.dataTable thead th.dt-orderable-asc:hover,
    table.dataTable thead th.dt-orderable-desc:hover {
        background-color: #f2f4f7;
    }
    .dark table.dataTable thead th.dt-orderable-asc:hover,
    .dark table.dataTable thead th.dt-orderable-desc:hover {
        background-color: #1d2939;
    }
    .dark th,
    .dark table thead th,
    .dark #reportTable thead th,
    .dark table.dataTable thead th {
        color: #ffffff !important;
    }
    /* Sticky table header headings */
    #reportTable thead th {
        position: sticky !important;
        top: 0 !important; /* Aligns to the top of the scroll container */
        z-index: 20 !important;
        background-color: #f9fafb !important; /* bg-gray-50 */
        box-shadow: inset 0 -1px 0 #e5e7eb, 0 1px 2px rgba(0,0,0,0.05) !important;
    }
    .dark #reportTable thead th {
        background-color: #1f2937 !important; /* dark:bg-gray-800 */
        box-shadow: inset 0 -1px 0 #374151, 0 1px 2px rgba(0,0,0,0.2) !important;
    }
</style>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

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

    // DataTable
    const table = document.getElementById('reportTable');
    if (table) {
        new DataTable('#reportTable', {
            pageLength: 100,
            lengthMenu: [10, 25, 50, 100, 200],
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [0] }],
            language: {
                search: '',
                searchPlaceholder: 'Search records…',
                lengthMenu: 'Show _MENU_ per page',
                info: 'Showing _START_ to _END_ of _TOTAL_ records',
                emptyTable: 'No records found',
            },
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

