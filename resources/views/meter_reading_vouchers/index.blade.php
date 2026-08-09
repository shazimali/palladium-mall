@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Meter Reading Vouchers" />

    {{-- Flash Messages --}}
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
    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <div
        x-data="{ 
            showTable: {{ (request()->anyFilled(['search', 'unit_id', 'status', 'date_from', 'date_to', 'start_date', 'end_date']) || request()->has('show_search')) ? 'true' : 'false' }},
            imageModalOpen: false,
            modalImageSrc: '',
            modalTitle: ''
        }">
        <x-common.component-card title="" desc="">

            {{-- Top Action Bar matching /receiving-vouchers strategy --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center mb-4">
                <div class="flex flex-wrap items-center gap-3">
                    {{-- 1. New Meter Reading Voucher Button --}}
                    @if(auth()->user()->hasPermission('meter_vouchers.create') || auth()->user()->isSuperAdmin())
                        <a href="{{ route('meter-reading-vouchers.create') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-600 transition-all shadow-md">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            New Meter Reading Voucher
                        </a>
                    @endif

                    {{-- 2. Toggle Search Panel Button --}}
                    <button type="button" @click="showTable = !showTable"
                        :class="showTable ? 'bg-brand-600 text-white' : 'bg-brand-500 text-white hover:bg-brand-600'"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition-all shadow-md cursor-pointer">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span x-text="showTable ? 'Hide Search' : 'Search'"></span>
                    </button>
                </div>
            </div>

            {{-- Filter & Table Panel (Hidden by Default) --}}
            <div x-show="showTable" x-cloak class="mt-4">

                <!-- Inline Filters Bar & Action Buttons -->
                <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
                    <form x-ref="filterForm" action="{{ route('meter-reading-vouchers.index') }}" method="GET"
                        class="flex flex-wrap items-center gap-3">
                        <input type="hidden" name="show_search" value="1">

                        <!-- Search Input -->
                        <div class="relative min-w-[200px] flex-1">
                            <span class="absolute -translate-y-1/2 pointer-events-none left-3.5 top-1/2">
                                <svg class="fill-gray-500 dark:fill-gray-400" width="16" height="16" viewBox="0 0 20 20" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                                </svg>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search voucher #, Flat/Shop, ref #, tenant..."
                                class="h-10 w-full rounded-xl border border-gray-300 bg-white py-2 pl-10 pr-3 text-xs font-semibold text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        </div>

                        <!-- Searchable Flat/Shop Dropdown -->
                        <div x-data="{
                                open: false,
                                search: '',
                                selectedId: '{{ request('unit_id') }}',
                                selectedLabel: '{{ request('unit_id') ? ($units->firstWhere('id', request('unit_id'))?->unit_number ?? 'All Flats/Shops') : 'All Flats/Shops' }}',
                                highlightedIndex: -1,
                                units: [
                                    { id: '', label: 'All Flats/Shops' },
                                    @foreach($units as $unit)
                                        { id: '{{ $unit->id }}', label: '{{ addslashes($unit->unit_number) }}' },
                                    @endforeach
                                ],
                                get filteredUnits() {
                                    if (!this.search) return this.units;
                                    let s = this.search.toLowerCase();
                                    return this.units.filter(u => u.label.toLowerCase().includes(s));
                                },
                                selectUnit(u) {
                                    this.selectedId = u.id;
                                    this.selectedLabel = u.label;
                                    this.open = false;
                                    this.search = '';
                                    this.$nextTick(() => { $refs.filterForm.submit(); });
                                }
                            }" class="relative min-w-[150px]">
                            <input type="hidden" name="unit_id" :value="selectedId">
                            <button type="button" @click="open = !open"
                                class="flex h-10 w-full items-center justify-between rounded-xl border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <span x-text="selectedLabel">All Flats/Shops</span>
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-cloak
                                class="absolute left-0 right-0 z-50 mt-1 max-h-60 overflow-y-auto rounded-xl border border-gray-200 bg-white p-2 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                                <input type="text" x-model="search" placeholder="Search Flat/Shop..."
                                    class="mb-2 h-8 w-full rounded-lg border border-gray-300 px-2 text-xs font-semibold dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                <template x-for="u in filteredUnits" :key="u.id">
                                    <div @click="selectUnit(u)"
                                        class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-bold hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20 text-gray-700 dark:text-gray-200"
                                        x-text="u.label"></div>
                                </template>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="min-w-[130px]">
                            <select name="status" onchange="this.form.submit()"
                                class="h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="">All Statuses</option>
                                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            </select>
                        </div>

                        <!-- Date From (Flatpickr Enabled) -->
                        <div class="min-w-[140px]">
                            <input type="text" id="filter_date_from" name="date_from" value="{{ request('date_from', request('start_date')) }}" placeholder="Date From..." autocomplete="off"
                                class="h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white cursor-pointer" />
                        </div>

                        <!-- Date To (Flatpickr Enabled) -->
                        <div class="min-w-[140px]">
                            <input type="text" id="filter_date_to" name="date_to" value="{{ request('date_to', request('end_date')) }}" placeholder="Date To..." autocomplete="off"
                                class="h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-xs font-semibold text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white cursor-pointer" />
                        </div>

                        <!-- Action Buttons Inline: Search/Filter, Print List, Clear -->
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-brand-600 transition-all cursor-pointer h-10">
                                🔍 Filter
                            </button>

                            <a href="{{ route('meter-reading-vouchers.print-list', request()->query()) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition-all cursor-pointer h-10">
                                🖨️ Print List
                            </a>

                            @if(request()->anyFilled(['search', 'unit_id', 'status', 'date_from', 'date_to', 'start_date', 'end_date']))
                                <a href="{{ route('meter-reading-vouchers.index', ['show_search' => 1]) }}"
                                    class="inline-flex items-center gap-1 rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 transition-all h-10">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Summary Cards --}}
                <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-blue-200 bg-blue-50/60 p-4 dark:border-blue-900/40 dark:bg-blue-950/20">
                        <p class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Total Billed Amount</p>
                        <p class="mt-1 text-2xl font-black font-mono text-blue-900 dark:text-blue-200">Rs. {{ number_format($totalBilledAmount, 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Total Paid Bills</p>
                        <p class="mt-1 text-2xl font-black font-mono text-emerald-900 dark:text-emerald-200">Rs. {{ number_format($totalPaidAmount, 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-rose-200 bg-rose-50/60 p-4 dark:border-rose-900/40 dark:bg-rose-950/20">
                        <p class="text-xs font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Total Unpaid Bills</p>
                        <p class="mt-1 text-2xl font-black font-mono text-rose-900 dark:text-rose-200">Rs. {{ number_format($totalUnpaidAmount, 2) }}</p>
                    </div>
                </div>

                {{-- Table listing matching meter reading vouchers --}}
                <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-800">
                    <table class="w-full text-left text-xs text-gray-700 dark:text-gray-300">
                        <thead class="bg-gray-50 text-[11px] font-extrabold uppercase text-gray-600 dark:bg-gray-800/60 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="px-4 py-3">Voucher #</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Due Date</th>
                                <th class="px-4 py-3">Flat / Shop</th>
                                <th class="px-4 py-3">Tenant Name</th>
                                <th class="px-4 py-3">GEPCO Ref #</th>
                                <th class="px-4 py-3 text-right">Reading (kWh)</th>
                                <th class="px-4 py-3 text-right">Bill Amount</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Photo</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800 font-medium">
                            @forelse($vouchers as $v)
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-4 py-3 font-black font-mono text-brand-600 dark:text-brand-400">
                                        <a href="{{ route('meter-reading-vouchers.show', $v) }}" class="hover:underline">
                                            {{ $v->voucher_no }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-semibold">
                                        {{ $v->date ? $v->date->format('d M Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-semibold">
                                        {{ $v->due_date ? $v->due_date->format('d M Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-black">
                                        @if($v->unit)
                                            <span class="unit-badge-lg px-2.5 py-0.5 text-xs font-black">
                                                {{ $v->unit->unit_number }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-extrabold text-gray-900 dark:text-white">
                                        {{ $v->unit?->tenant?->name ?? ($v->unit?->otherTenant?->name ?? 'Vacant / Self') }}
                                    </td>
                                    <td class="px-4 py-3 font-mono font-bold text-gray-800 dark:text-gray-200">
                                        {{ $v->meter_ref_no ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono font-bold">
                                        {{ $v->current_reading ? number_format($v->current_reading, 2) : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-black font-mono text-gray-900 dark:text-white text-sm">
                                        Rs. {{ number_format($v->amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($v->status === 'paid')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                PAID
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                                UNPAID
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($v->meter_image_url)
                                            <button type="button"
                                                @click="modalImageSrc = '{{ $v->meter_image_url }}'; modalTitle = 'Meter Photo - Voucher #{{ $v->voucher_no }}'; imageModalOpen = true"
                                                class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 hover:underline cursor-pointer">
                                                🖼️ Photo
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('meter-reading-vouchers.show', $v) }}"
                                                title="View Voucher"
                                                class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-800 dark:hover:text-white transition-colors">
                                                👁️
                                            </a>
                                            <a href="{{ route('meter-reading-vouchers.print', $v) }}" target="_blank"
                                                title="Print Voucher"
                                                class="rounded-lg p-1 text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/30 transition-colors">
                                                🖨️
                                            </a>
                                            @if(auth()->user()->hasPermission('meter_vouchers.edit') || auth()->user()->isSuperAdmin())
                                                <a href="{{ route('meter-reading-vouchers.edit', $v) }}"
                                                    title="Edit Voucher"
                                                    class="rounded-lg p-1 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                                    ✏️
                                                </a>
                                            @endif
                                            @if(auth()->user()->hasPermission('meter_vouchers.delete') || auth()->user()->isSuperAdmin())
                                                <form action="{{ route('meter-reading-vouchers.destroy', $v) }}" method="POST"
                                                    onsubmit="return confirmAction(event, 'Are you sure you want to delete Voucher #{{ $v->voucher_no }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Delete Voucher"
                                                        class="rounded-lg p-1 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition-colors cursor-pointer">
                                                        🗑️
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 font-bold">
                                        No Meter Reading Vouchers found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links --}}
                <div class="mt-4">
                    {{ $vouchers->links() }}
                </div>

            </div>

        </x-common.component-card>

        {{-- Photo Preview Modal --}}
        <div x-show="imageModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs"
            @click.self="imageModalOpen = false" @keydown.escape.window="imageModalOpen = false">
            <div class="relative max-w-3xl w-full bg-white dark:bg-gray-900 rounded-3xl p-6 shadow-2xl border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white" x-text="modalTitle"></h3>
                    <button type="button" @click="imageModalOpen = false"
                        class="rounded-xl p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                        ✕
                    </button>
                </div>
                <div class="flex items-center justify-center">
                    <img :src="modalImageSrc" alt="Meter Photo" class="max-h-[70vh] max-w-full rounded-2xl object-contain shadow-md border border-gray-200 dark:border-gray-800" />
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#filter_date_from', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    onChange: function(selectedDates, dateStr) {
                        const form = document.querySelector('form[x-ref="filterForm"]');
                        if (form) form.submit();
                    }
                });
                flatpickr('#filter_date_to', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    onChange: function(selectedDates, dateStr) {
                        const form = document.querySelector('form[x-ref="filterForm"]');
                        if (form) form.submit();
                    }
                });
            }
        });
    </script>
@endpush
