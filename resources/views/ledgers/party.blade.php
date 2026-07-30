@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Party Ledger" />

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

    <x-common.component-card title="" desc="">

        <form action="{{ route('ledgers.party') }}" method="GET" id="party-ledger-form"
            class="sticky top-[72px] z-[990] bg-white/95 dark:bg-gray-900/95 p-4 rounded-2xl border-2 border-brand-500 shadow-xl backdrop-blur-md mb-6"
            x-data="{
                partyId: '{{ $selectedParty->id ?? '' }}',
                search: '',
                open: false,
                highlightedIndex: -1,
                options: [
                    @foreach($parties as $party)
                        {
                            id: '{{ $party->id }}',
                            text: '{{ addslashes($party->name) }}',
                            phone: '{{ addslashes($party->phone ?? '—') }}',
                            searchLabel: '{{ strtolower($party->name . " " . ($party->phone ?? "")) }}'
                        },
                    @endforeach
                ],
                get filteredOptions() {
                    if (!this.search) return this.options;
                    let s = this.search.toLowerCase();
                    return this.options.filter(opt => opt.searchLabel.includes(s));
                },
                get selectedText() {
                    let selected = this.options.find(opt => opt.id == this.partyId);
                    return selected ? selected.text : 'Choose a Party Head';
                },
                selectOption(opt) {
                    this.partyId = opt.id;
                    this.open = false;
                    this.search = '';
                    this.highlightedIndex = -1;
                    this.$nextTick(() => {
                        document.getElementById('party-ledger-form').submit();
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
                    this.partyId = '';
                    this.open = false;
                    this.search = '';
                    this.highlightedIndex = -1;
                }
            }">

            <!-- Sticky Inline Filter Controls -->
            <div class="flex flex-wrap items-end gap-3.5">
                
                <!-- Party Searchable Dropdown -->
                <div class="flex-1 min-w-[260px] relative" :class="open ? 'relative z-[99999]' : 'relative'" @click.away="open = false; highlightedIndex = -1">
                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Select Party Head <span class="text-red-500">*</span>
                    </label>

                    {{-- Trigger Button --}}
                    <button type="button" @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                        class="w-full flex items-center justify-between rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-base font-bold text-gray-900 text-left focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <span x-text="selectedText"
                            :class="partyId ? 'font-extrabold text-brand-600 dark:text-brand-400' : 'text-gray-400 dark:text-gray-500'"></span>
                        <svg class="h-5 w-5 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Hidden input --}}
                    <input type="hidden" name="party_id" :value="partyId">

                    {{-- Dropdown Container --}}
                    <div x-show="open" x-transition x-cloak
                        class="absolute left-0 right-0 z-[99999] mt-2 w-full rounded-2xl border-2 border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">

                        {{-- Search field --}}
                        <div class="p-3 border-b border-gray-100 dark:border-gray-800">
                            <div class="relative">
                                <input type="text" x-ref="searchInput" x-model="search" placeholder="Type party name or phone..."
                                    @keydown.arrow-down.prevent="moveHighlight(1)"
                                    @keydown.arrow-up.prevent="moveHighlight(-1)"
                                    @keydown.enter.prevent="selectHighlighted()"
                                    @keydown.escape.prevent="open = false; highlightedIndex = -1"
                                    class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-2.5 pl-10 text-base font-semibold text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">🔍</span>
                            </div>
                        </div>

                        {{-- Options --}}
                        <div class="max-h-64 overflow-y-auto p-2">
                            <button type="button" @click="clearSelection()"
                                class="w-full text-left px-4 py-2 text-sm font-semibold text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl">
                                Clear Selection
                            </button>

                            <template x-for="(opt, index) in filteredOptions" :key="opt.id">
                                <button type="button" @click="selectOption(opt)" @mouseenter="highlightedIndex = index"
                                    class="w-full text-left px-4 py-3 text-base rounded-xl transition-colors flex items-center justify-between"
                                    :class="partyId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedIndex === index ? 'bg-brand-50 text-brand-900 dark:bg-brand-950/40 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5')">
                                    <span class="flex items-center justify-between w-full">
                                        <span x-text="opt.text" class="font-bold"></span>
                                        <span x-text="opt.phone" class="text-xs opacity-80 font-mono"></span>
                                    </span>
                                </button>
                            </template>

                            <div x-show="filteredOptions.length === 0"
                                class="px-4 py-6 text-center text-sm font-semibold text-gray-500">
                                No matching Party Head found
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons: Filter, Clear, Print -->
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-base font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer">
                        Filter
                    </button>
                    @if($selectedParty)
                        <a href="{{ route('ledgers.party') }}"
                            class="rounded-xl border-2 border-gray-300 px-4 py-2.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            Clear
                        </a>
                        <a href="{{ route('ledgers.party.print', ['party_id' => $selectedParty->id]) }}"
                            onclick="window.open(this.href,'_blank','width=1100,height=800,scrollbars=yes'); return false;"
                            class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-base font-extrabold text-white shadow-md hover:bg-gray-800 transition-colors cursor-pointer">
                            🖨️ Print
                        </a>
                    @endif
                </div>

            </div>

        </form>

        @if($selectedParty)

            <!-- Ledger Entries Table -->
            <div class="overflow-hidden border-2 border-gray-200 rounded-2xl dark:border-gray-800 shadow-md">
                <table class="w-full text-base sm:text-lg text-left text-gray-900 dark:text-gray-100">
                    <thead class="text-sm sm:text-base font-black uppercase tracking-wider bg-brand-600 text-white dark:bg-brand-700 border-b-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-5 py-4 text-white">Date</th>
                            <th class="px-5 py-4 text-white">Ref / Voucher #</th>
                            <th class="px-5 py-4 text-white">Transaction Type</th>
                            <th class="px-5 py-4 text-white">Details / Description</th>
                            <th class="px-5 py-4 text-right text-white">Debit (Dr)</th>
                            <th class="px-5 py-4 text-right text-white">Credit (Cr)</th>
                            <th class="px-5 py-4 text-right text-white">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-900 dark:text-gray-100 font-bold">
                        @forelse($ledgerEntries as $entry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-4 text-base sm:text-lg font-mono font-bold whitespace-nowrap">
                                    {{ ($entry['date'] instanceof \Carbon\Carbon ? $entry['date'] : \Carbon\Carbon::parse($entry['date']))->format('d M Y') }}
                                </td>
                                <td class="px-5 py-4 text-base sm:text-lg font-mono font-black">
                                    @if($entry['type'] === 'Receipt (General)')
                                        <a href="{{ route('general-receiving-vouchers.show', $entry['id']) }}"
                                            class="text-brand-600 hover:underline font-mono font-black dark:text-brand-400">
                                            {{ $entry['ref'] }}
                                        </a>
                                    @elseif($entry['type'] === 'Payment' || $entry['type'] === 'Payment (Advance)')
                                        <a href="{{ route('payment-vouchers.show', $entry['id']) }}"
                                            class="text-brand-600 hover:underline font-mono font-black dark:text-brand-400">
                                            {{ $entry['ref'] }}
                                        </a>
                                    @else
                                        <span class="font-mono text-gray-500 font-bold dark:text-gray-400">{{ $entry['ref'] }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-base sm:text-lg">
                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs sm:text-sm font-black 
                                                    {{ strpos($entry['type'], 'Due') !== false ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 border border-amber-200 dark:border-amber-800' : '' }}
                                                    {{ strpos($entry['type'], 'Receipt') !== false ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' : '' }}
                                                    {{ strpos($entry['type'], 'Payment') !== false ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/20 dark:text-rose-400 border border-rose-200 dark:border-rose-800' : '' }}
                                                ">
                                        {{ $entry['type'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-base sm:text-lg font-bold">
                                    {{ $entry['description'] }}
                                </td>
                                <td class="px-5 py-4 text-right font-black text-emerald-600 dark:text-emerald-400 text-base sm:text-lg font-mono">
                                    {{ $entry['debit'] > 0 ? 'Rs. ' . number_format($entry['debit'], 0) : '—' }}
                                </td>
                                <td class="px-5 py-4 text-right font-black text-rose-600 dark:text-rose-400 text-base sm:text-lg font-mono">
                                    {{ $entry['credit'] > 0 ? 'Rs. ' . number_format($entry['credit'], 0) : '—' }}
                                </td>
                                <td
                                    class="px-5 py-4 text-right font-black font-mono text-lg sm:text-xl {{ ($entry['balance'] ?? 0) > 0 ? 'text-emerald-600 dark:text-emerald-400' : (($entry['balance'] ?? 0) < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white') }}">
                                    @php $bal = $entry['balance'] ?? 0; @endphp
                                    Rs. {{ number_format(abs($bal), 0) }}
                                    <span class="text-xs font-black ml-0.5">{{ $bal > 0 ? 'Dr' : ($bal < 0 ? 'Cr' : '') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-400 dark:text-gray-600 text-lg font-bold">
                                    No ledger entries found for this party.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($ledgerEntries) > 0)
                        @php
                            $sumDebit = $ledgerEntries->sum('debit');
                            $sumCredit = $ledgerEntries->sum('credit');
                        @endphp
                        <tfoot
                            class="bg-gray-200/90 dark:bg-gray-800 border-t-4 border-gray-400 dark:border-gray-600 text-gray-900 dark:text-white font-black">
                            <tr>
                                <td colspan="4"
                                    class="px-5 py-4 text-lg sm:text-xl uppercase tracking-wider font-black text-gray-900 dark:text-white">
                                    Total Summary
                                </td>
                                <td class="px-5 py-4 text-right font-mono font-black text-xl sm:text-2xl text-emerald-600 dark:text-emerald-400">
                                    Rs. {{ number_format($sumDebit, 0) }}
                                </td>
                                <td class="px-5 py-4 text-right font-mono font-black text-xl sm:text-2xl text-rose-600 dark:text-rose-400">
                                    Rs. {{ number_format($sumCredit, 0) }}
                                </td>
                                @php $netBalance = $sumDebit - $sumCredit; @endphp
                                <td
                                    class="px-5 py-4 text-right font-mono font-black text-xl sm:text-2xl {{ $netBalance > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($netBalance < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white') }}">
                                    Rs. {{ number_format(abs($netBalance), 0) }}
                                    <span class="text-sm font-black ml-0.5">{{ $netBalance > 0 ? 'Dr' : ($netBalance < 0 ? 'Cr' : '') }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @else
            <div class="p-8 text-center text-gray-400 dark:text-gray-600 bg-gray-50 dark:bg-white/[0.01] border border-dashed border-gray-200 dark:border-gray-800 rounded-xl text-lg font-bold">
                Please select a Party Head to view their Statement of Account.
            </div>
        @endif

    </x-common.component-card>

    {{-- Due Creation Modal --}}
    @if($selectedParty)
        <x-ui.modal x-data="{ open: false }" @open-due-modal.window="open = true" :isOpen="false" class="max-w-[500px] p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Record Outstanding Party Due</h3>

            <form action="{{ route('ledgers.party.dues.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="party_id" value="{{ $selectedParty->id }}">

                {{-- Due Type --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Due Record Type</label>
                    <select name="type" required
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="payable">Due Payable (Mall owes to Party/Contractor)</option>
                        <option value="receivable">Due Receivable (Party owes to Mall)</option>
                    </select>
                </div>

                {{-- Date --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                    <input type="text" id="due_date" name="date" value="{{ date('Y-m-d') }}" required placeholder="YYYY-MM-DD"
                        autocomplete="off"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                {{-- Amount --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Amount (PKR)</label>
                    <input type="number" name="amount" required min="1" step="1" placeholder="e.g. 25000"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                {{-- Reference --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Reference / Bill # <span
                            class="text-xs text-gray-400">(Optional)</span></label>
                    <input type="text" name="reference" placeholder="e.g. Invoice #2193"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                {{-- Notes --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Remarks /
                        Description</label>
                    <textarea name="notes" rows="3" placeholder="Enter details about this due entry..."
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-850">
                    <button type="button" @click="open = false"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05]">
                        Cancel
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        Save Due Record
                    </button>
                </div>
            </form>
        </x-ui.modal>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#due_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true
                });
            }
        });
    </script>
@endpush