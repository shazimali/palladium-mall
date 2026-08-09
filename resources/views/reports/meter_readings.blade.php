@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Meter Reading Report" />

    <x-common.component-card title="" desc="">

        {{-- STICKY HORIZONTAL INLINE FILTER FORM WITH SCROLL-COLLAPSIBLE HEADING --}}
        <form action="{{ route('reports.meter-readings') }}" method="GET" id="meter-reading-report-form"
            x-data="{
                isScrolled: false,
                init() {
                    window.addEventListener('scroll', () => {
                        this.isScrolled = window.scrollY > 80;
                    });
                }
            }"
            class="sticky top-[72px] z-[990] bg-white/95 dark:bg-gray-900/95 p-4 rounded-2xl border-2 border-brand-500 shadow-xl backdrop-blur-md mb-6 font-sans transition-all duration-300">

            <!-- Heading: Hidden by default, shows automatically when scrolling down -->
            <div x-show="isScrolled" x-collapse.duration.300ms class="mb-3 pb-2 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <h2 class="text-lg font-black uppercase tracking-wider text-gray-900 dark:text-white flex items-center gap-2">
                    <span>⚡ Meter Reading</span>
                </h2>
                <span class="text-xs font-extrabold text-gray-400 uppercase tracking-widest">Filter Controls</span>
            </div>

            <!-- Filter Controls Horizontal Bar -->
            <div class="flex flex-wrap items-center gap-3">

                <!-- 1. Searchable Flat/Shop Dropdown -->
                <div x-data="{
                        open: false,
                        search: '',
                        selectedId: '{{ $unitId ?? '' }}',
                        selectedLabel: '{{ $selectedUnit ? 'Flat/Shop: ' . addslashes($selectedUnit->unit_number) . ($selectedUnit->tenant ? ' (' . addslashes($selectedUnit->tenant->name) . ')' : '') : 'All Flats / Shops' }}',
                        units: [
                            { id: '', label: 'All Flats / Shops' },
                            @foreach($units as $u)
                                { id: '{{ $u->id }}', label: 'Flat/Shop: {{ addslashes($u->unit_number) }} {{ $u->tenant ? " (" . addslashes($u->tenant->name) . ")" : ($u->otherTenant ? " (" . addslashes($u->otherTenant->name) . ")" : "") }}' },
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
                            this.$nextTick(() => { document.getElementById('meter-reading-report-form').submit(); });
                        }
                    }" class="relative min-w-[260px] max-w-[380px] flex-1" @click.away="open = false">
                    
                    <input type="hidden" name="unit_id" :value="selectedId">
                    
                    <button type="button" @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                        class="w-full flex items-center justify-between rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm sm:text-base font-extrabold text-gray-900 text-left focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white h-[46px] cursor-pointer">
                        <span x-text="selectedLabel" class="truncate">All Flats / Shops</span>
                        <svg class="h-5 w-5 text-gray-500 transition-transform duration-200 shrink-0 ml-1.5" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Container --}}
                    <div x-show="open" x-transition x-cloak
                        class="absolute left-0 right-0 z-[99999] mt-2 w-full min-w-[300px] max-h-80 overflow-hidden rounded-2xl border-2 border-gray-200 bg-white p-2.5 shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                        
                        <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                            <div class="relative">
                                <input type="text" x-ref="searchInput" x-model="search" placeholder="Type unit or tenant name..."
                                    class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2.5 pl-9 text-sm font-semibold text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
                            </div>
                        </div>

                        <div class="max-h-60 overflow-y-auto p-1 space-y-1">
                            <template x-for="u in filteredUnits" :key="u.id">
                                <button type="button" @click="selectUnit(u)"
                                    class="w-full text-left px-3.5 py-2.5 text-sm font-extrabold rounded-xl transition-colors flex items-center justify-between cursor-pointer"
                                    :class="selectedId == u.id ? 'bg-brand-600 text-white' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800'">
                                    <span x-text="u.label" class="truncate"></span>
                                    <span x-show="selectedId == u.id" class="text-sm">✔️</span>
                                </button>
                            </template>
                            <div x-show="filteredUnits.length === 0" class="px-3 py-4 text-center text-sm font-semibold text-gray-400">
                                No matching Flat / Shop found
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Date From -->
                <div class="w-40 sm:w-44 shrink-0">
                    <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}" placeholder="Date From..." autocomplete="off"
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-3.5 py-2 text-sm sm:text-base font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white cursor-pointer h-[46px]">
                </div>

                <!-- 3. Date To -->
                <div class="w-40 sm:w-44 shrink-0">
                    <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}" placeholder="Date To..." autocomplete="off"
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-3.5 py-2 text-sm sm:text-base font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white cursor-pointer h-[46px]">
                </div>

                <!-- 4. Status Filter -->
                <div class="w-36 shrink-0">
                    <select name="status" onchange="document.getElementById('meter-reading-report-form').submit()"
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-3.5 py-2 text-sm sm:text-base font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white cursor-pointer h-[46px]">
                        <option value="">Status: All</option>
                        <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ $status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>

                <!-- 5. Action Buttons Inline: Filter, Clear, Print -->
                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm sm:text-base font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer h-[46px]">
                        Filter
                    </button>
                    @if($unitId || $dateFrom || $dateTo || $status)
                        <a href="{{ route('reports.meter-readings') }}"
                            class="inline-flex items-center justify-center rounded-xl border-2 border-gray-300 px-4 py-2.5 text-sm sm:text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors h-[46px]">
                            Clear
                        </a>
                    @endif
                    <a href="{{ route('reports.meter-readings.print', request()->all()) }}"
                        onclick="window.open(this.href,'_blank','width=1100,height=800,scrollbars=yes'); return false;"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm sm:text-base font-extrabold text-white shadow-md hover:bg-gray-800 transition-colors cursor-pointer h-[46px]">
                        🖨️ Print Report
                    </a>
                </div>

            </div>

        </form>

        {{-- TABLE MATCHING EXACT LEDGERS UI STRATEGY --}}
        <div class="overflow-hidden border-2 border-gray-200 rounded-2xl dark:border-gray-800 shadow-md"
            x-data="{ imageModalOpen: false, modalImageSrc: '', modalTitle: '' }">
            <table class="w-full text-sm sm:text-base text-left text-gray-900 dark:text-gray-100">
                <thead class="text-xs sm:text-sm font-black uppercase tracking-wider bg-brand-600 text-white dark:bg-brand-700 border-b-2 border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-white text-center">#</th>
                        <th class="px-4 py-3 text-white">Voucher Date</th>
                        <th class="px-4 py-3 text-white">Due Date</th>
                        <th class="px-4 py-3 text-white">Ref / Voucher #</th>
                        <th class="px-4 py-3 text-white">Flat/Shop</th>
                        <th class="px-4 py-3 text-white">Tenant Name</th>
                        <th class="px-4 py-3 text-white">GEPCO Ref #</th>
                        <th class="px-4 py-3 text-right text-white">Prev. kWh</th>
                        <th class="px-4 py-3 text-right text-white">Curr. kWh</th>
                        <th class="px-4 py-3 text-right text-white">Consumed</th>
                        <th class="px-4 py-3 text-right text-white">Bill Amount</th>
                        <th class="px-4 py-3 text-center text-white">Status</th>
                        <th class="px-4 py-3 text-center text-white">Photo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-900 dark:text-gray-100 font-bold">
                    @forelse($voucherData as $index => $item)
                        @php
                            $v = $item['voucher'];
                            $consumption = $item['consumption'];
                            $prevReading = $item['prev_reading'];
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 text-sm font-bold text-gray-400 text-center">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-mono font-bold whitespace-nowrap">
                                {{ $v->date ? $v->date->format('d M Y') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm font-mono font-semibold whitespace-nowrap">
                                {{ $v->due_date ? $v->due_date->format('d M Y') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm font-mono font-black">
                                <a href="{{ route('meter-reading-vouchers.show', $v) }}" class="text-brand-600 hover:underline font-mono font-black dark:text-brand-400">
                                    {{ $v->voucher_no }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm font-bold whitespace-nowrap">
                                @if($v->unit)
                                    <span class="unit-badge-lg px-2.5 py-0.5 text-xs font-black rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/30 dark:text-brand-400 border border-brand-200/60 dark:border-brand-800/40">
                                        Unit {{ $v->unit->unit_number }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-bold">
                                {{ $v->unit?->tenant?->name ?? ($v->unit?->otherTenant?->name ?? 'Vacant / Self') }}
                            </td>
                            <td class="px-4 py-3 text-sm font-mono font-bold">
                                {{ $v->meter_ref_no ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-gray-500">
                                {{ $prevReading !== null ? number_format($prevReading, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-mono font-bold">
                                {{ $v->current_reading !== null ? number_format($v->current_reading, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-mono font-black">
                                @if($consumption !== null)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        ⚡ {{ number_format($consumption, 2) }} kWh
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-black font-mono">
                                Rs. {{ number_format($v->amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($v->status === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                        PAID
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black uppercase bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                        UNPAID
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($v->meter_image_url)
                                    <button type="button"
                                        @click="modalImageSrc = '{{ $v->meter_image_url }}'; modalTitle = 'Meter Photo - Voucher #{{ $v->voucher_no }}'; imageModalOpen = true"
                                        class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 hover:underline cursor-pointer">
                                        🖼️ View
                                    </button>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 font-bold">
                                No Meter Reading records found for the selected criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($voucherData) > 0)
                    <tfoot class="bg-brand-50/80 dark:bg-brand-950/40 font-black text-gray-900 dark:text-white text-sm border-t-2 border-brand-500">
                        <tr>
                            <td colspan="9" class="px-4 py-3.5 text-right uppercase tracking-wider">Total Consumed & Billed:</td>
                            <td class="px-4 py-3.5 text-right font-mono text-amber-700 dark:text-amber-300 text-base">
                                {{ number_format($totalConsumption, 2) }} kWh
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono text-base">
                                Rs. {{ number_format($totalBilledAmount, 2) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>

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

    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#date_from', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    defaultDate: '{{ $dateFrom }}',
                    onChange: function() {
                        document.getElementById('meter-reading-report-form').submit();
                    }
                });
                flatpickr('#date_to', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    defaultDate: '{{ $dateTo }}',
                    onChange: function() {
                        document.getElementById('meter-reading-report-form').submit();
                    }
                });
            }
        });
    </script>
@endpush
