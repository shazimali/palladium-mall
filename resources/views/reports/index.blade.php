@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Reports" />

    {{-- ── Report Type Tabs ──────────────────────────────────────────────── --}}
    <div class="mb-6 flex flex-wrap gap-2">
        @php
            $activeType = $filters['report_type'] ?? '';
            $tabs = [
                ''           => ['label' => 'All Data',        'icon' => '📊'],
                'rent'       => ['label' => 'Rent Collected',  'icon' => '🏠'],
                'utilities'  => ['label' => 'Utilities Paid',  'icon' => '⚡'],
                'fines'      => ['label' => 'Fines',           'icon' => '⚠️'],
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
                        Date From
                    </label>
                    <input type="text" id="date_from" name="date_from"
                        value="{{ $filters['date_from'] ?? '' }}"
                        placeholder="Start date"
                        autocomplete="off"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
                </div>

                {{-- Date To --}}
                <div>
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
                        <option value="">All Units</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ ($filters['unit_id'] ?? '') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->unit_number }} ({{ ucfirst($unit->type) }})
                            </option>
                        @endforeach
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
                    </div>
                @endif
            </div>
        </form>
    </x-common.component-card>

    {{-- ── Results ──────────────────────────────────────────────────────────── --}}
    @if($hasQuery)

        {{-- Summary Cards --}}
        @if($summary)
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">

                <div class="col-span-1 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">Records Found</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white">{{ number_format($summary['count']) }}</p>
                </div>

                <div class="col-span-1 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">Total Due</p>
                    <p class="mt-1 text-lg font-bold text-orange-500">Rs. {{ number_format($summary['total_due']) }}</p>
                </div>

                <div class="col-span-1 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">Total Collected</p>
                    <p class="mt-1 text-lg font-bold text-green-600">Rs. {{ number_format($summary['total_paid']) }}</p>
                </div>

                <div class="col-span-1 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">Outstanding</p>
                    <p class="mt-1 text-lg font-bold {{ $summary['outstanding'] > 0 ? 'text-red-500' : 'text-green-600' }}">
                        Rs. {{ number_format($summary['outstanding']) }}
                    </p>
                </div>

                <div class="col-span-1 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">🏠 Rent Collected</p>
                    <p class="mt-1 text-lg font-bold text-blue-600">Rs. {{ number_format($summary['rent_collected']) }}</p>
                </div>

                <div class="col-span-1 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">⚡ Utilities Paid</p>
                    <p class="mt-1 text-lg font-bold text-purple-600">Rs. {{ number_format($summary['utilities_paid']) }}</p>
                </div>

            </div>
        @endif

        {{-- Data Table --}}
        <div class="mt-4">
            <x-common.component-card
                title="Report Results"
                desc="{{ $summary['count'] }} {{ Str::plural('record', $summary['count']) }} found">

                @if($entries->isEmpty())
                    <div class="py-12 text-center text-gray-400">
                        <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm font-medium">No records found for the selected filters.</p>
                        <p class="mt-1 text-xs text-gray-300 dark:text-gray-600">Try adjusting the date range or removing filters.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table id="reportTable" class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                            <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">Month</th>
                                    <th class="px-4 py-3">Unit</th>
                                    <th class="px-4 py-3">Tenant</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Description</th>
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

                                        <td class="px-4 py-3 text-xs font-medium text-gray-700 dark:text-gray-300">
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
                                            {{ $entry['description'] }}
                                        </td>

                                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                            Rs. {{ number_format($entry['amount_due']) }}
                                        </td>

                                        <td class="px-4 py-3 font-medium text-green-600">
                                            Rs. {{ number_format($entry['amount_paid']) }}
                                        </td>

                                        <td class="px-4 py-3 font-semibold {{ $entry['balance'] > 0 ? 'text-red-500' : 'text-green-600' }}">
                                            Rs. {{ number_format($entry['balance']) }}
                                        </td>

                                        <td class="px-4 py-3">
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
                                        </td>

                                        <td class="px-4 py-3 text-xs text-gray-500">
                                            {{ $entry['paid_at'] instanceof \Carbon\Carbon ? $entry['paid_at']->format('d M Y') : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 dark:bg-gray-800 font-semibold text-sm">
                                    <td colspan="6" class="px-4 py-3 text-gray-700 dark:text-gray-300">
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
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Date pickers
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

    // DataTable
    const table = document.getElementById('reportTable');
    if (table) {
        new DataTable('#reportTable', {
            pageLength: 25,
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
@endpush
