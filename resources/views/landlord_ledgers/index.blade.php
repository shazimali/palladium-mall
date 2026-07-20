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
            <div class="grid grid-cols-1 gap-5 md:grid-cols-4 items-end mb-6">
                
                <!-- Landlord Selector Dropdown -->
                <div class="md:col-span-2 relative" @click.away="open = false; highlightedIndex = -1">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Select Landlord <span class="text-red-500">*</span>
                    </label>
                    
                    {{-- Trigger Button --}}
                    <button type="button" @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                        class="w-full flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 text-left focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <template x-if="landlordId">
                            <span class="flex items-center gap-1.5">
                                <span x-text="selectedLandlordName" class="font-bold text-gray-900 dark:text-white"></span>
                                <span x-text="selectedPhone" class="text-gray-500 dark:text-gray-400 font-normal"></span>
                            </span>
                        </template>
                        <template x-if="!landlordId">
                            <span class="text-gray-400 dark:text-gray-500">Choose a Landlord</span>
                        </template>
                        <svg class="h-4 w-4 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Hidden input --}}
                    <input type="hidden" name="landlord_id" :value="landlordId">

                    {{-- Dropdown Container --}}
                    <div x-show="open" x-transition x-cloak
                        class="absolute left-0 right-0 z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-950">
                        
                        {{-- Search field --}}
                        <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                            <div class="relative">
                                <input type="text" x-ref="searchInput" x-model="search" placeholder="Type to search..."
                                    @keydown.arrow-down.prevent="moveHighlight(1)"
                                    @keydown.arrow-up.prevent="moveHighlight(-1)"
                                    @keydown.enter.prevent="selectHighlighted()"
                                    @keydown.escape.prevent="open = false; highlightedIndex = -1"
                                    class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-1.5 pl-8 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-855 dark:bg-gray-900/50 dark:text-white/90">
                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400">
                                    🔍
                                </span>
                                <button type="button" x-show="search" @click="search = ''; highlightedIndex = -1" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-white text-xs">
                                    Clear
                                </button>
                            </div>
                        </div>

                        {{-- Options --}}
                        <div class="max-h-60 overflow-y-auto p-1">
                            <button type="button" @click="clearSelection()"
                                class="w-full text-left px-3 py-2 text-xs text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-md">
                                Clear Selection
                            </button>
                            
                            <template x-for="(opt, index) in filteredOptions" :key="opt.id">
                                <button type="button" @click="selectOption(opt)"
                                    @mouseenter="highlightedIndex = index"
                                    class="w-full text-left px-3 py-2 text-xs rounded-md transition-colors flex items-center justify-between"
                                    :class="landlordId == opt.id ? 'bg-brand-500 text-white font-semibold' : (highlightedIndex === index ? 'bg-brand-50 text-brand-900 dark:bg-brand-950/20 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5')">
                                    <template x-if="true">
                                        <span class="flex items-center gap-1.5 flex-1 min-w-0">
                                            <span x-text="opt.landlordName" class="font-bold"></span>
                                            <span x-text="opt.phone" class="font-normal opacity-75 truncate"></span>
                                        </span>
                                    </template>
                                    <span x-show="landlordId == opt.id" class="text-[10px]">✔️</span>
                                </button>
                            </template>

                            <div x-show="filteredOptions.length === 0" class="px-3 py-4 text-center text-xs text-gray-450 dark:text-gray-500">
                                No matching Landlord found
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date From -->
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Date From
                    </label>
                    <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}" placeholder="YYYY-MM-DD" autocomplete="off"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <!-- Date To -->
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Date To
                    </label>
                    <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}" placeholder="YYYY-MM-DD" autocomplete="off"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 dark:border-gray-800 pb-5 mb-6">
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        Filter Ledger
                    </button>
                    @if($landlordId || $dateFrom || $dateTo)
                        <a href="{{ route('landlord_ledgers.index') }}"
                            class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>

                @if($ledgerData)
                    <div class="flex items-center gap-2">
                        <!-- Excel Export -->
                        <a href="{{ route('landlord_ledgers.excel', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors dark:border-emerald-900/30 dark:bg-emerald-950/10 dark:text-emerald-400">
                            🟢 Export Excel
                        </a>
                        <!-- PDF Export -->
                        <a href="{{ route('landlord_ledgers.pdf', request()->all()) }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-red-300 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100 transition-colors dark:border-red-900/30 dark:bg-red-950/10 dark:text-red-400">
                            🔴 Export PDF
                        </a>
                        <!-- Print Statement -->
                        <a href="{{ route('landlord_ledgers.print', request()->all()) }}"
                            onclick="window.open(this.href,'_blank','width=1100,height=800,scrollbars=yes'); return false;"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            🖨️ Print
                        </a>
                    </div>
                @endif
            </div>

        </form>

        @if($ledgerData)
            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                <div class="bg-blue-50/50 dark:bg-blue-950/10 p-5 rounded-xl border border-blue-100 dark:border-blue-900/30">
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-500 dark:text-blue-400">Total Unit Value Owed</span>
                    <span class="block mt-2 text-2xl font-bold text-gray-800 dark:text-white">Rs. {{ number_format($ledgerData['openingBalance'], 2) }}</span>
                </div>
                <div class="bg-green-50/50 dark:bg-green-950/10 p-5 rounded-xl border border-green-100 dark:border-green-900/30">
                    <span class="text-xs font-semibold uppercase tracking-wider text-green-500 dark:text-green-400">Total Payments Received</span>
                    <span class="block mt-2 text-2xl font-bold text-green-600 dark:text-green-400">Rs. {{ number_format($ledgerData['totalPaid'], 2) }}</span>
                </div>
                <div class="p-5 rounded-xl border {{ $ledgerData['pendingBalance'] > 0 ? 'bg-orange-50/50 dark:bg-orange-950/10 border-orange-100 dark:border-orange-900/30 text-orange-600' : 'bg-gray-50 dark:bg-gray-800/40 border-gray-100 dark:border-gray-800' }}">
                    <span class="text-xs font-semibold uppercase tracking-wider {{ $ledgerData['pendingBalance'] > 0 ? 'text-orange-500' : 'text-gray-400' }}">Outstanding Balance</span>
                    <span class="block mt-2 text-2xl font-bold {{ $ledgerData['pendingBalance'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-800 dark:text-white' }}">Rs. {{ number_format($ledgerData['pendingBalance'], 2) }}</span>
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="px-5 py-3.5 font-bold">Date</th>
                            <th class="px-5 py-3.5 font-bold">Description</th>
                            <th class="px-5 py-3.5 font-bold">Ref / Voucher #</th>
                            <th class="px-5 py-3.5 font-bold text-right">Debit (Owed)</th>
                            <th class="px-5 py-3.5 font-bold text-right">Credit (Paid)</th>
                            <th class="px-5 py-3.5 font-bold text-right">Running Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                        @forelse($ledgerData['entries'] as $entry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5 text-xs font-mono">
                                    {{ $entry['date']->format('d M Y') }}
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
                                <td colspan="6" class="px-5 py-12 text-center text-gray-400 dark:text-gray-600">
                                    No transaction entries found for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
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
