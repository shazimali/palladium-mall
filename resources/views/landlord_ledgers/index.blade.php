@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Landlord Ledger" />

    <x-common.component-card title="Landlord Statement of Account" desc="Generate chronological statement of charges and payments for any landlord.">
        
        <form action="{{ route('landlord_ledgers.index') }}" method="GET" id="ledger-filter-form"
            x-data="{
                landlordId: '{{ $landlordId ?? '' }}',
                search: '',
                open: false,
                highlightedIndex: -1,
                options: [
                    @foreach($landlords as $landlord)
                    {
                        id: '{{ $landlord->id }}',
                        landlordName: '{{ addslashes($landlord->name) }}',
                        phone: '{{ $landlord->phone ? "(Phone: " . addslashes($landlord->phone) . ")" : "" }}',
                        text: '{{ addslashes($landlord->name) }} {{ $landlord->phone ? "(Phone: " . addslashes($landlord->phone) . ")" : "" }}',
                        searchLabel: '{{ strtolower($landlord->name . " " . ($landlord->phone ?? "")) }}'
                    },
                    @endforeach
                ],
                get filteredOptions() {
                    if (!this.search) return this.options;
                    let s = this.search.toLowerCase();
                    return this.options.filter(opt => opt.searchLabel.includes(s));
                },
                get selectedLandlordName() {
                    let selected = this.options.find(opt => opt.id == this.landlordId);
                    return selected ? selected.landlordName : '';
                },
                get selectedPhone() {
                    let selected = this.options.find(opt => opt.id == this.landlordId);
                    return selected ? selected.phone : '';
                },
                get selectedText() {
                    let selected = this.options.find(opt => opt.id == this.landlordId);
                    return selected ? selected.text : 'Choose a Landlord';
                },
                selectOption(opt) {
                    this.landlordId = opt.id;
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
                    this.landlordId = '';
                    this.open = false;
                    this.search = '';
                    this.highlightedIndex = -1;
                }
            }">

            <!-- Filters -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4 items-end mb-6">
                
                <!-- Landlord Selector Dropdown -->
                <div class="md:col-span-2 relative" :class="open ? 'relative z-[99999]' : 'relative'" @click.away="open = false; highlightedIndex = -1">
                    <label class="mb-2 block text-xs sm:text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Select Landlord <span class="text-red-500">*</span>
                    </label>
                    
                    {{-- Trigger Button --}}
                    <button type="button" @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                        class="w-full flex items-center justify-between rounded-2xl border-2 border-gray-300 bg-white px-5 py-3.5 text-lg font-bold text-gray-900 text-left focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <template x-if="landlordId">
                            <span class="flex items-center gap-2 truncate">
                                <span x-text="selectedLandlordName" class="font-extrabold text-brand-600 dark:text-brand-400"></span>
                                <span x-text="selectedPhone" class="text-gray-600 dark:text-gray-300 font-semibold truncate font-mono"></span>
                            </span>
                        </template>
                        <template x-if="!landlordId">
                            <span class="text-gray-400 dark:text-gray-500">Choose a Landlord</span>
                        </template>
                        <svg class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Hidden input --}}
                    <input type="hidden" name="landlord_id" :value="landlordId">

                    {{-- Dropdown Container --}}
                    <div x-show="open" x-transition x-cloak
                        class="absolute left-0 right-0 z-[99999] mt-2 w-full rounded-2xl border-2 border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                        
                        {{-- Search field --}}
                        <div class="p-3 border-b border-gray-100 dark:border-gray-800">
                            <div class="relative">
                                <input type="text" x-ref="searchInput" x-model="search" placeholder="Type landlord name or phone..."
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
                                    :class="landlordId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedIndex === index ? 'bg-brand-50 text-brand-900 dark:bg-brand-950/40 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5')">
                                    <span class="flex items-center gap-2 flex-1 min-w-0">
                                        <span x-text="opt.landlordName" class="font-bold"></span>
                                        <span x-text="opt.phone" class="font-medium opacity-80 truncate font-mono"></span>
                                    </span>
                                    <span x-show="landlordId == opt.id" class="text-sm">✔️</span>
                                </button>
                            </template>

                            <div x-show="filteredOptions.length === 0" class="px-4 py-6 text-center text-sm font-semibold text-gray-500">
                                No matching Landlord found
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date From -->
                <div>
                    <label class="mb-2 block text-xs sm:text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Date From
                    </label>
                    <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}" placeholder="YYYY-MM-DD" autocomplete="off"
                        class="w-full rounded-2xl border-2 border-gray-300 bg-white px-5 py-3.5 text-lg font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <!-- Date To -->
                <div>
                    <label class="mb-2 block text-xs sm:text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Date To
                    </label>
                    <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}" placeholder="YYYY-MM-DD" autocomplete="off"
                        class="w-full rounded-2xl border-2 border-gray-300 bg-white px-5 py-3.5 text-lg font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-6 mb-6">
                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-3 rounded-2xl bg-brand-600 px-6 py-3.5 text-base font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer">
                        Filter Ledger
                    </button>
                    @if($landlordId || $dateFrom || $dateTo)
                        <a href="{{ route('landlord_ledgers.index') }}"
                            class="rounded-2xl border-2 border-gray-300 px-6 py-3.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>

                @if($ledgerData)
                    <div class="flex items-center gap-3">
                        <!-- Excel Export -->
                        <a href="{{ route('landlord_ledgers.excel', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-2xl border-2 border-emerald-300 bg-emerald-50 px-5 py-3.5 text-base font-extrabold text-emerald-700 hover:bg-emerald-100 transition-colors dark:border-emerald-900/40 dark:bg-emerald-950/20 dark:text-emerald-400">
                            🟢 Excel
                        </a>
                        <!-- PDF Export -->
                        <a href="{{ route('landlord_ledgers.pdf', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-2xl border-2 border-red-300 bg-red-50 px-5 py-3.5 text-base font-extrabold text-red-700 hover:bg-red-100 transition-colors dark:border-red-900/40 dark:bg-red-950/20 dark:text-red-400">
                            🔴 PDF
                        </a>
                        <!-- Print Statement -->
                        <a href="{{ route('landlord_ledgers.print', request()->all()) }}"
                            onclick="window.open(this.href,'_blank','width=1100,height=800,scrollbars=yes'); return false;"
                            class="inline-flex items-center gap-2 rounded-2xl border-2 border-gray-300 px-5 py-3.5 text-base font-extrabold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            🖨️ Print
                        </a>
                    </div>
                @endif
            </div>

        </form>

        @if($ledgerData)
            {{-- STICKY BIG HEADING & SUMMARY BANNER --}}
            <div class="sticky mb-6 rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 p-6 shadow-xl backdrop-blur-md"
                style="position: sticky; top: 72px; z-index: 990;">
                
                <div class="mb-4 flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-md text-3xl font-black">
                            🏠
                        </div>
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-brand-600 dark:text-brand-400">
                                Landlord Statement of Account
                            </p>
                            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900 dark:text-white"
                                x-text="selectedLandlordName ? selectedLandlordName : 'Statement'"></h2>
                        </div>
                    </div>
                </div>

                {{-- Summary Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="bg-blue-50/70 dark:bg-blue-950/20 p-5 rounded-2xl border-2 border-blue-200 dark:border-blue-900/40">
                        <span class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400">Total Unit Value Owed</span>
                        <span class="block mt-2 text-2xl sm:text-3xl font-black font-mono text-gray-900 dark:text-white">Rs. {{ number_format($ledgerData['openingBalance'], 2) }}</span>
                    </div>
                    <div class="bg-green-50/70 dark:bg-green-950/20 p-5 rounded-2xl border-2 border-green-200 dark:border-green-900/40">
                        <span class="text-xs font-black uppercase tracking-wider text-green-600 dark:text-green-400">Total Payments Received</span>
                        <span class="block mt-2 text-2xl sm:text-3xl font-black font-mono text-green-600 dark:text-green-400">Rs. {{ number_format($ledgerData['totalPaid'], 2) }}</span>
                    </div>
                    <div class="p-5 rounded-2xl border-2 {{ $ledgerData['pendingBalance'] > 0 ? 'bg-amber-50/70 dark:bg-amber-950/20 border-amber-300 dark:border-amber-900/40 text-amber-700' : 'bg-gray-50 dark:bg-gray-800/40 border-gray-200 dark:border-gray-800' }}">
                        <span class="text-xs font-black uppercase tracking-wider {{ $ledgerData['pendingBalance'] > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-gray-500' }}">Outstanding Balance</span>
                        <span class="block mt-2 text-2xl sm:text-3xl font-black font-mono {{ $ledgerData['pendingBalance'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }}">Rs. {{ number_format($ledgerData['pendingBalance'], 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="overflow-x-auto rounded-2xl border-2 border-gray-200 dark:border-gray-800 shadow-md">
                <table class="w-full text-base sm:text-lg text-left text-gray-800 dark:text-gray-200">
                    <thead class="text-xs font-black uppercase tracking-wider bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-b-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-5 py-4">Date</th>
                            <th class="px-5 py-4">Flat/Shop</th>
                            <th class="px-5 py-4">Description</th>
                            <th class="px-5 py-4">Ref / Voucher #</th>
                            <th class="px-5 py-4 text-right">Debit (Owed)</th>
                            <th class="px-5 py-4 text-right">Credit (Paid)</th>
                            <th class="px-5 py-4 text-right">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                        @forelse($ledgerData['entries'] as $entry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 text-xs font-mono whitespace-nowrap">
                                    {{ $entry['date']->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-xs font-semibold whitespace-nowrap">
                                    @if(!empty($entry['unit_number']) && $entry['unit_number'] !== '—')
                                        <span class="unit-badge-lg px-2.5 py-1 text-xs font-bold rounded-lg bg-brand-50 text-brand-700 dark:bg-brand-950/30 dark:text-brand-400 border border-brand-200/60 dark:border-brand-800/40">
                                            {{ \Illuminate\Support\Str::startsWith(strtolower($entry['unit_number']), ['unit', 'shop', 'flat', 'off']) ? $entry['unit_number'] : 'Unit ' . $entry['unit_number'] }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-medium">{{ $entry['description'] }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-xs">
                                    @if(!empty($entry['model']))
                                        @if($entry['model'] instanceof \App\Models\ReceivingVoucher)
                                            <a href="{{ route('receiving-vouchers.show', $entry['model']->id) }}" class="text-brand-500 hover:underline font-mono font-semibold">
                                                {{ $entry['voucher_no'] }}
                                            </a>
                                        @elseif($entry['model'] instanceof \App\Models\GeneralReceivingVoucher)
                                            <a href="{{ route('general-receiving-vouchers.show', $entry['model']->id) }}" class="text-brand-500 hover:underline font-mono font-semibold">
                                                {{ $entry['voucher_no'] }}
                                            </a>
                                        @elseif($entry['model'] instanceof \App\Models\PaymentVoucher)
                                            <a href="{{ route('payment-vouchers.show', $entry['model']->id) }}" class="text-brand-500 hover:underline font-mono font-semibold">
                                                {{ $entry['voucher_no'] }}
                                            </a>
                                        @else
                                            <span class="font-mono text-gray-400 dark:text-gray-500">{{ $entry['voucher_no'] ?? '—' }}</span>
                                        @endif
                                    @else
                                        <span class="font-mono text-gray-400 dark:text-gray-500">{{ $entry['voucher_no'] ?? '—' }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-rose-600">
                                    {{ $entry['debit'] > 0 ? 'Rs. ' . number_format($entry['debit'], 2) : '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-emerald-600">
                                    {{ $entry['credit'] > 0 ? 'Rs. ' . number_format($entry['credit'], 2) : '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-bold text-gray-900 dark:text-white font-mono">
                                    Rs. {{ number_format($entry['running_balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-400 dark:text-gray-600">
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
                        <tfoot class="bg-gray-100/80 dark:bg-gray-800/80 border-t-2 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white font-bold">
                            <tr>
                                <td colspan="4" class="px-5 py-4 text-xs uppercase tracking-wider font-extrabold text-gray-700 dark:text-gray-300">
                                    Total Summary
                                </td>
                                <td class="px-5 py-4 text-right text-rose-600 font-mono font-bold text-sm">
                                    Rs. {{ number_format($sumDebit, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right text-emerald-600 font-mono font-bold text-sm">
                                    Rs. {{ number_format($sumCredit, 2) }}
                                </td>
                                <td class="px-5 py-4 text-right font-mono font-extrabold text-sm text-gray-900 dark:text-white">
                                    Rs. {{ number_format($finalBalance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @else
            <div class="p-8 text-center text-gray-400 dark:text-gray-600 bg-gray-50 dark:bg-white/[0.01] border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                Please select a Landlord to generate the ledger statement.
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
