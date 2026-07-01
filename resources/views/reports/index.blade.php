@extends('layouts.app')

@section('content')
<div x-data="{ isFilterModalOpen: false }">

    {{-- ── Filter Panel ───────────────────────────────────────────────────── --}}
    @php
        $appliedFilters = [];

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
            $appliedFilters[] = "<strong>From:</strong> " . \Carbon\Carbon::parse($filters['date_from'])->format('d M Y');
        }
        if (!empty($filters['date_to'])) {
            $appliedFilters[] = "<strong>To:</strong> " . \Carbon\Carbon::parse($filters['date_to'])->format('d M Y');
        }

        // Unit
        if (!empty($filters['unit_id'])) {
            $selectedUnit = $units->firstWhere('id', $filters['unit_id']);
            if ($selectedUnit) {
                $appliedFilters[] = "<strong>Unit:</strong> " . $selectedUnit->unit_number;
            }
        }

        // Unit Status
        if (!empty($filters['unit_status'])) {
            $appliedFilters[] = "<strong>Unit Status:</strong> " . ucfirst($filters['unit_status']);
        }

        // Tenant
        if (!empty($filters['tenant_id'])) {
            $selectedTenant = $tenants->firstWhere('id', $filters['tenant_id']);
            if ($selectedTenant) {
                $appliedFilters[] = "<strong>Tenant:</strong> " . $selectedTenant->name;
            }
        }

        // Status
        if (!empty($filters['status'])) {
            $appliedFilters[] = "<strong>Status:</strong> " . ucfirst($filters['status']);
        }

        // Landlord
        if (!empty($filters['landlord_id'])) {
            $selectedLandlord = $landlords->firstWhere('id', $filters['landlord_id']);
            if ($selectedLandlord) {
                $appliedFilters[] = "<strong>Landlord:</strong> " . $selectedLandlord->name;
            }
        }

        // Owner Type
        if (!empty($filters['owner_type'])) {
            $appliedFilters[] = "<strong>Owner Type:</strong> " . ($filters['owner_type'] === 'pm_mall' ? 'PM Mall Owners' : 'Other Owners');
        }

        // Payment Method
        if (!empty($filters['payment_method'])) {
            $appliedFilters[] = "<strong>Payment Method:</strong> " . ucfirst(str_replace('_', ' ', $filters['payment_method']));
        }

        // Payment Account
        if (!empty($filters['payment_account_id'])) {
            $selectedAccount = $paymentAccounts->firstWhere('id', $filters['payment_account_id']);
            if ($selectedAccount) {
                $appliedFilters[] = "<strong>Account:</strong> " . $selectedAccount->name;
            }
        }
    @endphp

    <!-- Filters Modal Backdrop & Modal Card -->
    <div x-show="isFilterModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-0"
         x-cloak
         style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-950/50 backdrop-blur-xs transition-opacity z-40" @click="isFilterModalOpen = false"></div>
        
        <!-- Modal Body -->
        <div class="relative z-50 transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all dark:bg-gray-900 w-full max-w-4xl p-6 border border-gray-100 dark:border-gray-800">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4 mb-5">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                    Modify Report Filters
                </h3>
                <button type="button" @click="isFilterModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    ✕
                </button>
            </div>
            
            <form method="GET" action="{{ route('reports.index') }}" id="reportForm">
                <input type="hidden" name="no_sidebar" value="1">

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                {{-- Report Type --}}
                <div>
                    <label for="report_type" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Report Type
                    </label>
                    <select id="report_type" name="report_type"
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
            </form>
            <!-- Modal Footer Actions -->
            <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 dark:border-gray-800 pt-4">
                <button type="button" @click="isFilterModalOpen = false" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.05]">
                    Cancel
                </button>
                <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    {{-- ── Results ──────────────────────────────────────────────────────────── --}}
    @if($hasQuery)
        @php
            $selectedMonth = ($filters['date_from'] ?? false)
                ? \Carbon\Carbon::parse($filters['date_from'])->format('F Y')
                : \Carbon\Carbon::now()->format('F Y');
        @endphp



        {{-- Dynamic Summary Widgets --}}
        @if($summary)
            @php
                $isMatrix = ($filters['report_type'] ?? '') === 'monthly_matrix';
                $totalDue = $isMatrix ? ($summary['total_amount'] ?? 0) : ($summary['total_due'] ?? 0);
                $totalPaid = $isMatrix ? ($summary['total_received'] ?? 0) : ($summary['total_paid'] ?? 0);
                $outstanding = $isMatrix ? ($summary['total_pending'] ?? 0) : ($summary['outstanding'] ?? 0);
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
                <!-- General Summary Metrics Row -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4 mb-6">
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $isMatrix ? 'Total Units' : 'Total Records' }}</span>
                        <h4 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white leading-none">
                            {{ number_format($summary['count']) }}
                        </h4>
                        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $isMatrix ? 'Report billing month' : 'Processed entries' }}</p>
                    </div>
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total Billed Due</span>
                        <h4 class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-500 leading-none">
                            Rs. {{ number_format($totalDue) }}
                        </h4>
                        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500 font-medium">Billed amount</p>
                    </div>
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total Collected</span>
                        <h4 class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-500 leading-none">
                            Rs. {{ number_format($totalPaid) }}
                        </h4>
                        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500 font-medium">Received payments</p>
                    </div>
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Outstanding Due</span>
                        <h4 class="mt-2 text-2xl font-bold {{ $outstanding > 0 ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-500' }} leading-none">
                            Rs. {{ number_format($outstanding) }}
                        </h4>
                        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500 font-medium">Pending collections</p>
                    </div>
                </div>

                <!-- Category Wise Collections Row -->
                @if($isMatrix)
                    @php
                        $rentSum = $summary['total_rent_paid'] ?? 0;
                        $rentCount = $summary['rent_count'] ?? 0;

                        $maintSum = $summary['total_serv_paid'] ?? 0;
                        $maintCount = $summary['serv_count'] ?? 0;

                        $secSum = $summary['total_sec_paid'] ?? 0;
                        $secCount = $summary['sec_count'] ?? 0;

                        $extraSum = $summary['total_extra_paid'] ?? 0;
                        $extraCount = $summary['extra_count'] ?? 0;
                    @endphp
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 mt-8">Category Wise Collections</h3>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-4 mb-6">
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                            <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">🏠 Rent Collected</span>
                            <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white leading-none">Rs. {{ number_format($rentSum) }}</h4>
                            <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $rentCount }} payments</p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                            <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">🛠️ Serv (Maint)</span>
                            <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white leading-none">Rs. {{ number_format($maintSum) }}</h4>
                            <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $maintCount }} payments</p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                            <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">🔒 Sec. Deposit</span>
                            <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white leading-none">Rs. {{ number_format($secSum) }}</h4>
                            <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $secCount }} payments</p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                            <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">🔑 Extra</span>
                            <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white leading-none">Rs. {{ number_format($extraSum) }}</h4>
                            <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $extraCount }} payments</p>
                        </div>
                    </div>
                @else
                    @php
                        $rentSum = $entries->where('type', 'rent')->sum('amount_paid');
                        $rentCount = $entries->where('type', 'rent')->count();

                        $maintSum = $entries->where('type', 'maintenance')->sum('amount_paid');
                        $maintCount = $entries->where('type', 'maintenance')->count();

                        $utilSum = $entries->where('category', 'utility')->sum('amount_paid');
                        $utilCount = $entries->where('category', 'utility')->count();

                        $fineSum = $entries->where('type', 'fine')->sum('amount_paid');
                        $fineCount = $entries->where('type', 'fine')->count();

                        $otherSum = $entries->where('type', 'other')->sum('amount_paid');
                        $otherCount = $entries->where('type', 'other')->count();
                    @endphp
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 mt-8">Category Wise Collections</h3>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5 mb-6">
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                            <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">🏠 Rent Collected</span>
                            <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white leading-none">Rs. {{ number_format($rentSum) }}</h4>
                            <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $rentCount }} payments</p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                            <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">🛠️ Maintenance</span>
                            <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white leading-none">Rs. {{ number_format($maintSum) }}</h4>
                            <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $maintCount }} payments</p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                            <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">⚡ Utilities</span>
                            <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white leading-none">Rs. {{ number_format($utilSum) }}</h4>
                            <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $utilCount }} payments</p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                            <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">⚠️ Fines</span>
                            <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white leading-none">Rs. {{ number_format($fineSum) }}</h4>
                            <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $fineCount }} payments</p>
                        </div>
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
                            <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">🔑 Other</span>
                            <h4 class="mt-2 text-xl font-bold text-gray-800 dark:text-white leading-none">Rs. {{ number_format($otherSum) }}</h4>
                            <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">{{ $otherCount }} payments</p>
                        </div>
                    </div>
                @endif

                <!-- Payment Accounts & Methods Breakdown -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8 mb-6">
                    <!-- Payment Accounts -->
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3">Account Breakdown</h3>
                        <div class="space-y-3">
                            @php $hasAccountData = false; @endphp
                            @foreach($paymentAccounts as $account)
                                @php
                                    $accSum = $isMatrix 
                                        ? ($summary['accounts_total'][$account->name] ?? 0.0)
                                        : $entries->where('payment_account', $account->name)->sum('amount_paid');
                                    $accCount = $isMatrix
                                        ? ($accSum > 0 ? 1 : 0)
                                        : $entries->where('payment_account', $account->name)->count();
                                @endphp
                                @if($accSum > 0)
                                    @php $hasAccountData = true; @endphp
                                    <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-white p-4 shadow-2xs dark:border-gray-800 dark:bg-white/[0.03]">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-lg">🏦</span>
                                            <div>
                                                <h5 class="text-sm font-semibold text-gray-800 dark:text-white">{{ $account->name }}</h5>
                                                @if(!$isMatrix)
                                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $accCount }} transactions</p>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="text-base font-bold text-emerald-600 dark:text-emerald-500">Rs. {{ number_format($accSum) }}</span>
                                    </div>
                                @endif
                            @endforeach
                            @if(!$hasAccountData)
                                <p class="text-sm text-gray-400 py-2">No account transactions recorded</p>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3">Method Breakdown</h3>
                        <div class="space-y-3">
                            @php
                                $methodsList = [
                                    'cash' => ['label' => 'Cash', 'icon' => '💵'],
                                    'bank_transfer' => ['label' => 'Bank Transfer', 'icon' => '📱'],
                                    'cheque' => ['label' => 'Cheque', 'icon' => '✍️'],
                                    'other' => ['label' => 'Other', 'icon' => '🔑'],
                                ];
                                $hasMethodData = false;
                            @endphp
                            @foreach($methodsList as $mKey => $mCfg)
                                @php
                                    $mName = ucfirst(str_replace('_', ' ', $mKey));
                                    $mSum = $entries->where('payment_method', $mName)->sum('amount_paid');
                                    $mCount = $entries->where('payment_method', $mName)->count();
                                @endphp
                                @if($mCount > 0)
                                    @php $hasMethodData = true; @endphp
                                    <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-white p-4 shadow-2xs dark:border-gray-800 dark:bg-white/[0.03]">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-lg">{{ $mCfg['icon'] }}</span>
                                            <div>
                                                <h5 class="text-sm font-semibold text-gray-800 dark:text-white">{{ $mCfg['label'] }}</h5>
                                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $mCount }} transactions</p>
                                            </div>
                                        </div>
                                        <span class="text-base font-bold text-emerald-600 dark:text-emerald-500">Rs. {{ number_format($mSum) }}</span>
                                    </div>
                                @endif
                            @endforeach
                            @if(!$hasMethodData)
                                <p class="text-sm text-gray-400 py-2">No method transactions recorded</p>
                            @endif
                        </div>
                    </div>
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
                            <p class="text-sm text-gray-500 dark:text-gray-400 flex flex-wrap items-center gap-x-3 gap-y-1.5">
                                {!! implode('<span class="text-gray-300 dark:text-gray-700">|</span>', $appliedFilters) !!}
                            </p>
                        @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Showing all records (No filters applied)
                            </p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="isFilterModalOpen = true" class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors shadow-xs">
                            🔍 Filter
                        </button>
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
                        <div class="rounded-lg border border-gray-200 dark:border-gray-800">
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
            <p class="mt-1 mb-6 text-xs text-gray-400 dark:text-gray-600">Open the filters panel to configure and generate your report.</p>
            <div class="flex items-center justify-center gap-3">
                <button type="button" @click="isFilterModalOpen = true" class="inline-flex items-center gap-2 rounded-lg border border-brand-500 bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors shadow-xs">
                    🔍 Open Filters
                </button>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors shadow-xs">
                    ✕ Exit
                </a>
            </div>
        </div>
    @endif

</div>
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
    
    #reportTable th,
    #reportTable td {
        padding-top: 0.875rem !important;
        padding-bottom: 0.875rem !important;
    }
    
    [x-cloak] {
        display: none !important;
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

