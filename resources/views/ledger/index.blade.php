@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Ledger" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Filter Panel ────────────────────────────────────────────────── --}}
    <x-common.component-card title="Ledger Filters" desc="Select scope and filters to load the ledger">
        <form method="GET" action="{{ route('ledger.index') }}" id="ledgerForm"
            x-data="{
                scope: '{{ $filters['scope'] ?? 'tenant' }}',
                hasResults: {{ $hasQuery ? 'true' : 'false' }},
            }">

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

                {{-- Scope toggle --}}
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        View By <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <label class="flex cursor-pointer items-center gap-2">
                            <input type="radio" name="scope" value="tenant"
                                x-model="scope"
                                class="text-brand-500 focus:ring-brand-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tenant</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 ml-4">
                            <input type="radio" name="scope" value="unit"
                                x-model="scope"
                                class="text-brand-500 focus:ring-brand-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Unit</span>
                        </label>
                    </div>
                </div>

                {{-- Tenant dropdown --}}
                <div x-show="scope === 'tenant'" x-cloak>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Select Tenant <span class="text-red-500">*</span>
                    </label>
                    <select name="tenant_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Select a tenant</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}"
                                {{ ($filters['tenant_id'] ?? '') == $tenant->id ? 'selected' : '' }}>
                                {{ $tenant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Unit dropdown --}}
                <div x-show="scope === 'unit'" x-cloak>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Select Unit <span class="text-red-500">*</span>
                    </label>
                    <select name="unit_id"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Select a unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}"
                                {{ ($filters['unit_id'] ?? '') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->unit_number }} ({{ ucfirst($unit->type) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- From Date --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        From Date
                    </label>
                    <input type="text" id="from" name="from"
                        value="{{ $filters['from'] ?? '' }}"
                        placeholder="Start date"
                        autocomplete="off"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
                </div>

                {{-- To Date --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        To Date
                    </label>
                    <input type="text" id="to" name="to"
                        value="{{ $filters['to'] ?? '' }}"
                        placeholder="End date"
                        autocomplete="off"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
                </div>

                {{-- Category --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Category
                    </label>
                    <select name="category"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Categories</option>
                        <option value="payment" {{ ($filters['category'] ?? '') === 'payment' ? 'selected' : '' }}>Payments Only</option>
                        <option value="utility" {{ ($filters['category'] ?? '') === 'utility' ? 'selected' : '' }}>Utilities Only</option>
                    </select>
                </div>

                {{-- Type --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Type
                    </label>
                    <select name="type"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Types</option>
                        <optgroup label="Payments">
                            <option value="rent"        {{ ($filters['type'] ?? '') === 'rent'        ? 'selected' : '' }}>Rent</option>
                            <option value="maintenance"  {{ ($filters['type'] ?? '') === 'maintenance'  ? 'selected' : '' }}>Maintenance</option>
                            <option value="fine"        {{ ($filters['type'] ?? '') === 'fine'        ? 'selected' : '' }}>Fine</option>
                            <option value="other"       {{ ($filters['type'] ?? '') === 'other'       ? 'selected' : '' }}>Other</option>
                        </optgroup>
                        <optgroup label="Utilities">
                            <option value="electricity" {{ ($filters['type'] ?? '') === 'electricity' ? 'selected' : '' }}>Electricity</option>
                            <option value="water"       {{ ($filters['type'] ?? '') === 'water'       ? 'selected' : '' }}>Water</option>
                            <option value="gas"         {{ ($filters['type'] ?? '') === 'gas'         ? 'selected' : '' }}>Gas</option>
                        </optgroup>
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status
                    </label>
                    <select name="status"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Statuses</option>
                        <option value="paid"    {{ ($filters['status'] ?? '') === 'paid'    ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid"  {{ ($filters['status'] ?? '') === 'unpaid'  ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ ($filters['status'] ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>

            </div>

            {{-- Action buttons --}}
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Load Ledger
                </button>

                <a href="{{ route('ledger.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                    Reset
                </a>

                {{-- Export buttons — only show when results loaded --}}
                @if($hasQuery && $entries->isNotEmpty())
                    @if(auth()->user()->hasPermission('ledger.export') || auth()->user()->isSuperAdmin())
                        <div class="ml-auto flex gap-2">
                            <a href="{{ route('ledger.excel', $filters) }}"
                                class="inline-flex items-center gap-2 rounded-lg border border-green-500 px-4 py-2.5 text-sm font-medium text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Export Excel
                            </a>
                            <a href="{{ route('ledger.pdf', $filters) }}"
                                class="inline-flex items-center gap-2 rounded-lg border border-red-400 px-4 py-2.5 text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Export PDF
                            </a>
                        </div>
                    @endif
                @endif
            </div>

        </form>
    </x-common.component-card>

    {{-- ── Results ──────────────────────────────────────────────────────── --}}
    @if($hasQuery)

        {{-- Summary cards --}}
        @if($summary)
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">Subject</p>
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $subject }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">Total Due</p>
                    <p class="mt-1 text-lg font-bold text-red-500">Rs. {{ number_format($summary['total_due']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">Total Paid</p>
                    <p class="mt-1 text-lg font-bold text-green-600">Rs. {{ number_format($summary['total_paid']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400">Outstanding</p>
                    <p class="mt-1 text-lg font-bold {{ $summary['outstanding'] > 0 ? 'text-red-500' : 'text-green-600' }}">
                        Rs. {{ number_format($summary['outstanding']) }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Ledger table --}}
        <div class="mt-4">
            <x-common.component-card
                title="Ledger — {{ $subject }}"
                desc="{{ $summary['count'] }} {{ Str::plural('entry', $summary['count']) }} found">

                @if($entries->isEmpty())
                    <div class="py-12 text-center text-gray-400">
                        <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        No ledger entries found for the selected filters.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table id="ledgerTable" class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                            <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Month</th>
                                    @if(($filters['scope'] ?? '') === 'unit')
                                        <th class="px-4 py-3">Tenant</th>
                                    @endif
                                    <th class="px-4 py-3">Description</th>
                                    <th class="px-4 py-3">Category</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Amount Due</th>
                                    <th class="px-4 py-3">Amount Paid</th>
                                    <th class="px-4 py-3">Balance</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($entries as $i => $entry)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3 text-xs">
                                            {{ $entry['date'] instanceof \Carbon\Carbon ? $entry['date']->format('d M Y') : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-xs">
                                            {{ $entry['month'] instanceof \Carbon\Carbon ? $entry['month']->format('M Y') : '—' }}
                                        </td>
                                        @if(($filters['scope'] ?? '') === 'unit')
                                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                                {{ $entry['tenant'] }}
                                            </td>
                                        @endif
                                        <td class="px-4 py-3 text-gray-800 dark:text-white/90">
                                            {{ $entry['description'] }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium
                                                {{ $entry['category'] === 'payment'
                                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                                    : 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' }}">
                                                {{ ucfirst($entry['category']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                                {{ ucfirst($entry['type']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                            Rs. {{ number_format($entry['amount_due']) }}
                                        </td>
                                        <td class="px-4 py-3 text-green-600 font-medium">
                                            Rs. {{ number_format($entry['amount_paid']) }}
                                        </td>
                                        <td class="px-4 py-3 font-semibold {{ $entry['balance'] > 0 ? 'text-red-500' : 'text-green-600' }}">
                                            Rs. {{ number_format($entry['balance']) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium
                                                {{ $entry['status'] === 'paid'
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                    : ($entry['status'] === 'partial'
                                                        ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'
                                                        : 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400') }}">
                                                {{ ucfirst($entry['status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 dark:bg-gray-800 font-semibold text-sm">
                                    <td colspan="{{ ($filters['scope'] ?? '') === 'unit' ? 7 : 6 }}"
                                        class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                        Totals ({{ $summary['count'] }} entries)
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
                                    <td></td>
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
            <svg class="mx-auto mb-4 h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Select a tenant or unit above and click Load Ledger</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-600">You can also apply date, type and status filters to narrow the results</p>
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
    flatpickr('#from', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd M Y',
        allowInput: true,
        disableMobile: true,
    });

    flatpickr('#to', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd M Y',
        allowInput: true,
        disableMobile: true,
    });

    // DataTable — only init if table exists
    const table = document.getElementById('ledgerTable');
    if (table) {
        new DataTable('#ledgerTable', {
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [0] }],
            language: {
                search: '',
                searchPlaceholder: 'Search entries...',
                lengthMenu: 'Show _MENU_ per page',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                emptyTable: 'No ledger entries found',
            },
        });
    }
});
</script>
@endpush