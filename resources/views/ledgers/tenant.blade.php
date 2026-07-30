@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Tenant / Unit Ledger" />

    <x-common.component-card title="" desc="">
        
        <form action="{{ route('ledgers.tenant') }}" method="GET" id="ledger-filter-form"
            class="sticky top-[72px] z-[990] bg-white/95 dark:bg-gray-900/95 p-4 rounded-2xl border-2 border-brand-500 shadow-xl backdrop-blur-md mb-6"
            x-data="{
                unitId: '{{ $unitId ?? '' }}',
                search: '',
                open: false,
                highlightedIndex: -1,
                options: [
                    @foreach($units as $unit)
                    {
                        id: '{{ $unit->id }}',
                        unit: 'Flat/Shop: {{ addslashes($unit->unit_number) }}',
                        tenant: '(Tenant: {{ addslashes($unit->tenant->name ?? ($unit->otherTenant->name ?? "Vacant")) }})',
                        text: 'Flat/Shop: {{ addslashes($unit->unit_number) }} (Tenant: {{ addslashes($unit->tenant->name ?? ($unit->otherTenant->name ?? "Vacant")) }})',
                        searchLabel: '{{ strtolower($unit->unit_number . " " . ($unit->tenant->name ?? ($unit->otherTenant->name ?? "vacant"))) }}'
                    },
                    @endforeach
                ],
                get filteredOptions() {
                    if (!this.search) return this.options;
                    let s = this.search.toLowerCase();
                    return this.options.filter(opt => opt.searchLabel.includes(s));
                },
                get selectedUnit() {
                    let selected = this.options.find(opt => opt.id == this.unitId);
                    return selected ? selected.unit : '';
                },
                get selectedTenant() {
                    let selected = this.options.find(opt => opt.id == this.unitId);
                    return selected ? selected.tenant : '';
                },
                get selectedText() {
                    let selected = this.options.find(opt => opt.id == this.unitId);
                    return selected ? selected.text : 'Choose a Flat / Shop';
                },
                selectOption(opt) {
                    this.unitId = opt.id;
                    this.open = false;
                    this.search = '';
                    this.highlightedIndex = -1;
                    this.$nextTick(() => {
                        document.getElementById('ledger-filter-form').submit();
                    });
                },
                moveHighlight(dir) {
                    let list = this.filteredOptions;
                    if (list.length === 0) return;
                    this.highlightedIndex = (this.highlightedIndex + dir + list.length) % list.length;
                },
                selectHighlighted() {
                    let list = this.filteredOptions;
                    if (this.highlightedIndex >= 0 && this.highlightedIndex < list.length) {
                        this.selectOption(list[this.highlightedIndex]);
                    }
                },
                clearSelection() {
                    this.unitId = '';
                    this.open = false;
                    this.search = '';
                    this.highlightedIndex = -1;
                }
            }">

            <!-- Sticky Inline Filter Controls -->
            <div class="flex flex-wrap items-end gap-3.5">
                
                <!-- Unit Selector Dropdown -->
                <div class="flex-1 min-w-[260px] relative" :class="open ? 'relative z-[99999]' : 'relative'" @click.away="open = false; highlightedIndex = -1">
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Select Flat / Shop <span class="text-red-500">*</span>
                    </label>
                    
                    {{-- Trigger Button --}}
                    <button type="button" @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                        class="w-full flex items-center justify-between rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-base font-bold text-gray-900 text-left focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <template x-if="unitId">
                            <span class="flex items-center gap-2 truncate">
                                <span x-text="selectedUnit" class="font-extrabold text-brand-600 dark:text-brand-400"></span>
                                <span x-text="selectedTenant" class="text-gray-600 dark:text-gray-300 font-semibold truncate"></span>
                            </span>
                        </template>
                        <template x-if="!unitId">
                            <span class="text-gray-400 dark:text-gray-500">Choose a Flat / Shop</span>
                        </template>
                        <svg class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Hidden input --}}
                    <input type="hidden" name="unit_id" :value="unitId">

                    {{-- Dropdown Container --}}
                    <div x-show="open" x-transition x-cloak
                        class="absolute left-0 right-0 z-[99999] mt-2 w-full rounded-2xl border-2 border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                        
                        {{-- Search field --}}
                        <div class="p-3 border-b border-gray-100 dark:border-gray-800">
                            <div class="relative">
                                <input type="text" x-ref="searchInput" x-model="search" placeholder="Type unit or tenant name..."
                                    @keydown.arrow-down.prevent="moveHighlight(1)"
                                    @keydown.arrow-up.prevent="moveHighlight(-1)"
                                    @keydown.enter.prevent="selectHighlighted()"
                                    @keydown.escape.prevent="open = false; highlightedIndex = -1"
                                    class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 pl-10 text-base font-semibold text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">
                                    🔍
                                </span>
                                <button type="button" x-show="search" @click="search = ''; highlightedIndex = -1" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400 hover:text-gray-600 dark:hover:text-white">
                                    Clear
                                </button>
                            </div>
                        </div>

                        {{-- Options --}}
                        <div class="max-h-64 overflow-y-auto p-2">
                            <button type="button" @click="clearSelection()"
                                class="w-full text-left px-4 py-2 text-sm font-semibold text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl">
                                Clear Selection
                            </button>
                            
                            <template x-for="(opt, index) in filteredOptions" :key="opt.id">
                                <button type="button" @click="selectOption(opt)"
                                    @mouseenter="highlightedIndex = index"
                                    class="w-full text-left px-4 py-3 text-base rounded-xl transition-colors flex items-center justify-between"
                                    :class="unitId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedIndex === index ? 'bg-brand-50 text-brand-900 dark:bg-brand-950/40 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5')">
                                    <span class="flex items-center gap-2 flex-1 min-w-0">
                                        <span x-text="opt.unit" class="font-bold"></span>
                                        <span x-text="opt.tenant" class="font-medium opacity-80 truncate"></span>
                                    </span>
                                    <span x-show="unitId == opt.id" class="text-sm">✔️</span>
                                </button>
                            </template>

                            <div x-show="filteredOptions.length === 0" class="px-4 py-6 text-center text-sm font-semibold text-gray-500">
                                No matching Flat / Shop found
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date From -->
                <div class="w-full sm:w-44">
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Date From
                    </label>
                    <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}" placeholder="YYYY-MM-DD" autocomplete="off"
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-base font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <!-- Date To -->
                <div class="w-full sm:w-44">
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Date To
                    </label>
                    <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}" placeholder="YYYY-MM-DD" autocomplete="off"
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-base font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <!-- Action Buttons: Filter, Clear, Print -->
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-base font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer">
                        Filter
                    </button>
                    @if($unitId || $dateFrom || $dateTo)
                        <a href="{{ route('ledgers.tenant') }}"
                            class="rounded-xl border-2 border-gray-300 px-4 py-2.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            Clear
                        </a>
                    @endif
                    @if($ledgerData)
                        <a href="{{ route('ledgers.tenant.print', request()->all()) }}"
                            onclick="window.open(this.href,'_blank','width=1100,height=800,scrollbars=yes'); return false;"
                            class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-base font-extrabold text-white shadow-md hover:bg-gray-800 transition-colors cursor-pointer">
                            🖨️ Print
                        </a>
                    @endif
                </div>

            </div>

        </form>

        @if($ledgerData)

            {{-- Table --}}
            <div class="overflow-hidden border-2 border-gray-200 rounded-2xl dark:border-gray-800 shadow-md">
                <table class="w-full text-base sm:text-lg text-left text-gray-900 dark:text-gray-100">
                    <thead class="text-sm sm:text-base font-black uppercase tracking-wider bg-brand-600 text-white dark:bg-brand-700 border-b-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-5 py-4 text-white">Date</th>
                            <th class="px-5 py-4 text-white">Flat/Shop</th>
                            <th class="px-5 py-4 text-white">Description</th>
                            <th class="px-5 py-4 text-white">Ref / Voucher #</th>
                            <th class="px-5 py-4 text-right text-white">Debit (Charged)</th>
                            <th class="px-5 py-4 text-right text-white">Credit (Paid)</th>
                            <th class="px-5 py-4 text-right text-white">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-900 dark:text-gray-100 font-bold">
                        @forelse($ledgerData['entries'] as $entry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4 text-base sm:text-lg font-mono font-bold whitespace-nowrap">
                                    {{ $entry['date']->format('d M Y') }}
                                </td>
                                <td class="px-5 py-4 text-base sm:text-lg font-bold whitespace-nowrap">
                                    @if(!empty($entry['unit_number']))
                                        <span class="unit-badge-lg px-3 py-1 text-sm font-black rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/30 dark:text-brand-400 border border-brand-200/60 dark:border-brand-800/40">
                                            Unit {{ $entry['unit_number'] }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-base sm:text-lg font-bold">
                                    <div>{{ $entry['description'] }}</div>
                                </td>
                                <td class="px-5 py-4 text-base sm:text-lg font-mono font-black">
                                    @if($entry['type'] === 'voucher' && !empty($entry['id']))
                                        <a href="{{ route('receiving-vouchers.show', $entry['id']) }}" class="text-brand-600 hover:underline font-mono font-black dark:text-brand-400">
                                            {{ $entry['reference'] }}
                                        </a>
                                    @elseif($entry['type'] === 'bill' && !empty($entry['id']))
                                        <a href="{{ route('payments.show', $entry['id']) }}" class="text-brand-600 hover:underline font-mono font-black dark:text-brand-400">
                                            {{ $entry['reference'] }}
                                        </a>
                                    @elseif($entry['type'] === 'voucher_payout' && !empty($entry['id']))
                                        <a href="{{ route('payment-vouchers.show', $entry['id']) }}" class="text-brand-600 hover:underline font-mono font-black dark:text-brand-400">
                                            {{ $entry['reference'] }}
                                        </a>
                                    @else
                                        <span class="font-mono text-gray-500 font-bold dark:text-gray-400">{{ $entry['reference'] }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right font-black text-rose-600 dark:text-rose-400 text-base sm:text-lg font-mono">
                                    {{ $entry['debit'] > 0 ? 'Rs. ' . number_format($entry['debit'], 2) : '—' }}
                                </td>
                                <td class="px-5 py-4 text-right font-black text-emerald-600 dark:text-emerald-400 text-base sm:text-lg font-mono">
                                    {{ $entry['credit'] > 0 ? 'Rs. ' . number_format($entry['credit'], 2) : '—' }}
                                </td>
                                <td class="px-5 py-4 text-right font-black text-gray-900 dark:text-white font-mono text-lg sm:text-xl">
                                    Rs. {{ number_format($entry['running_balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-400 dark:text-gray-600 text-lg font-bold">
                                    No transaction entries found for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($ledgerData['entries']) > 0)
                        @php
                            $sumDebit = $ledgerData['entries']->sum('debit');
                            $sumCredit = $ledgerData['entries']->sum('credit');
                            $finalBalance = $ledgerData['entries']->last()['running_balance'] ?? 0;
                        @endphp
                        <tfoot class="bg-gray-200/90 dark:bg-gray-800 border-t-4 border-gray-400 dark:border-gray-600 text-gray-900 dark:text-white font-black">
                            <tr>
                                <td colspan="4" class="px-5 py-4 text-lg sm:text-xl uppercase tracking-wider font-black text-gray-900 dark:text-white">
                                    Total Summary
                                </td>
                                <td class="px-5 py-4 text-right text-rose-600 dark:text-rose-400 font-mono font-black text-xl sm:text-2xl">
                                    Rs. {{ number_format($sumDebit, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right text-emerald-600 dark:text-emerald-400 font-mono font-black text-xl sm:text-2xl">
                                    Rs. {{ number_format($sumCredit, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right font-mono font-black text-xl sm:text-2xl text-gray-900 dark:text-white">
                                    Rs. {{ number_format($finalBalance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @else
            <div class="p-8 text-center text-gray-400 dark:text-gray-600 bg-gray-50 dark:bg-white/[0.01] border border-dashed border-gray-200 dark:border-gray-800 rounded-xl text-lg font-bold">
                Please select a Flat / Shop to generate the ledger statement.
            </div>
        @endif

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
                });

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
@endpush
