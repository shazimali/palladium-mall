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

    <x-common.component-card title="Party Statement of Account"
        desc="Generate chronological statement of dues, receipts, and payments for any registered Party Head.">

        <form action="{{ route('ledgers.party') }}" method="GET" id="party-ledger-form" x-data="{
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

            <!-- Selector Dropdown -->
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3 items-end mb-6">
                <div class="md:col-span-2 relative" @click.away="open = false; highlightedIndex = -1">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Select Party Head <span class="text-red-500">*</span>
                    </label>

                    {{-- Trigger Button --}}
                    <button type="button" @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                        class="w-full flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 text-left focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <span x-text="selectedText"
                            :class="partyId ? 'font-bold text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'"></span>
                        <svg class="h-4 w-4 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Hidden input --}}
                    <input type="hidden" name="party_id" :value="partyId">

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
                                    class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-1.5 pl-8 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-850 dark:bg-gray-900/50 dark:text-white/90">
                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[10px]">🔍</span>
                            </div>
                        </div>

                        {{-- Options --}}
                        <div class="max-h-60 overflow-y-auto p-1">
                            <button type="button" @click="clearSelection()"
                                class="w-full text-left px-3 py-2 text-xs text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-md">
                                Clear Selection
                            </button>

                            <template x-for="(opt, index) in filteredOptions" :key="opt.id">
                                <button type="button" @click="selectOption(opt)" @mouseenter="highlightedIndex = index"
                                    class="w-full text-left px-3 py-2 text-xs rounded-md transition-colors flex items-center justify-between"
                                    :class="partyId == opt.id ? 'bg-brand-500 text-white font-semibold' : (highlightedIndex === index ? 'bg-brand-50 text-brand-900 dark:bg-brand-950/20 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5')">
                                    <span class="flex items-center justify-between w-full">
                                        <span x-text="opt.text" class="font-bold"></span>
                                        <span x-text="opt.phone" class="text-[10px] opacity-75"></span>
                                    </span>
                                </button>
                            </template>

                            <div x-show="filteredOptions.length === 0"
                                class="px-3 py-4 text-center text-xs text-gray-450 dark:text-gray-500">
                                No matching Party Head found
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @if($selectedParty)
            <!-- Actions Toolbar -->
            <div
                class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-150 pb-5 mb-6 dark:border-gray-850">
                <div class="flex items-center gap-2">
                    <!-- <button type="button" @click="$dispatch('open-due-modal')"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Due Record
                    </button> -->
                    <a href="{{ route('ledgers.party.print', ['party_id' => $selectedParty->id]) }}"
                        onclick="window.open(this.href,'_blank','width=800,height=800,scrollbars=yes'); return false;"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        🖨️ Print Ledger
                    </a>
                </div>
            </div>

            <!-- Financial Summary Widgets -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 mb-8">

                {{-- Receivable Summary --}}
                <div class="rounded-xl border border-gray-150 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.01]">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                        <span class="text-lg">📥</span> Mall Receivables from Party
                    </h3>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-lg bg-white p-3 shadow-theme-xs dark:bg-gray-900">
                            <p class="text-[10px] uppercase font-bold text-gray-400">Total Dues</p>
                            <p class="mt-1 text-sm font-bold font-mono text-gray-800 dark:text-white">Rs.
                                {{ number_format($summary['total_due_receivable'], 0) }}</p>
                        </div>
                        <div class="rounded-lg bg-white p-3 shadow-theme-xs dark:bg-gray-900">
                            <p class="text-[10px] uppercase font-bold text-gray-400">Total Recv</p>
                            <p class="mt-1 text-sm font-bold font-mono text-green-600">Rs.
                                {{ number_format($summary['total_received'], 0) }}</p>
                        </div>
                        <div class="rounded-lg bg-white p-3 shadow-theme-xs dark:bg-gray-900">
                            <p class="text-[10px] uppercase font-bold text-gray-400">Net Due</p>
                            <p
                                class="mt-1 text-sm font-bold font-mono {{ $summary['net_receivable'] > 0 ? 'text-red-500' : 'text-gray-600 dark:text-gray-300' }}">
                                Rs. {{ number_format($summary['net_receivable'], 0) }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Payable Summary --}}
                <div class="rounded-xl border border-gray-150 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.01]">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                        <span class="text-lg">📤</span> Mall Payables to Party
                    </h3>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-lg bg-white p-3 shadow-theme-xs dark:bg-gray-900">
                            <p class="text-[10px] uppercase font-bold text-gray-400">Total Dues</p>
                            <p class="mt-1 text-sm font-bold font-mono text-gray-800 dark:text-white">Rs.
                                {{ number_format($summary['total_due_payable'], 0) }}</p>
                        </div>
                        <div class="rounded-lg bg-white p-3 shadow-theme-xs dark:bg-gray-900">
                            <p class="text-[10px] uppercase font-bold text-gray-400">Total Paid</p>
                            <p class="mt-1 text-sm font-bold font-mono text-brand-600">Rs.
                                {{ number_format($summary['total_paid'], 0) }}</p>
                        </div>
                        <div class="rounded-lg bg-white p-3 shadow-theme-xs dark:bg-gray-900">
                            <p class="text-[10px] uppercase font-bold text-gray-400">Net Due</p>
                            <p
                                class="mt-1 text-sm font-bold font-mono {{ $summary['net_payable'] > 0 ? 'text-red-500' : 'text-gray-600 dark:text-gray-300' }}">
                                Rs. {{ number_format($summary['net_payable'], 0) }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Ledger Entries Table -->
            <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Ref/Voucher #</th>
                            <th class="px-4 py-3">Transaction Type</th>
                            <th class="px-4 py-3">Details / Description</th>
                            <th class="px-4 py-3 text-right">Debit (Dr)</th>
                            <th class="px-4 py-3 text-right">Credit (Cr)</th>
                            <th class="px-4 py-3 text-right">Balance</th>
                            <!-- <th class="px-4 py-3 text-right no-print">Actions</th> -->
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($ledgerEntries as $entry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3 text-xs whitespace-nowrap">
                                    {{ $entry['date']->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 font-mono font-semibold text-gray-800 dark:text-white/90">
                                    @if($entry['type'] === 'Receipt (General)')
                                        <a href="{{ route('general-receiving-vouchers.show', $entry['id']) }}"
                                            class="text-brand-500 hover:underline">
                                            {{ $entry['ref'] }}
                                        </a>
                                    @elseif($entry['type'] === 'Payment' || $entry['type'] === 'Payment (Advance)')
                                        <a href="{{ route('payment-vouchers.show', $entry['id']) }}"
                                            class="text-brand-500 hover:underline">
                                            {{ $entry['ref'] }}
                                        </a>
                                    @else
                                        {{ $entry['ref'] }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-semibold 
                                                    {{ strpos($entry['type'], 'Due') !== false ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400' : '' }}
                                                    {{ strpos($entry['type'], 'Receipt') !== false ? 'bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400' : '' }}
                                                    {{ strpos($entry['type'], 'Payment') !== false ? 'bg-red-50 text-red-700 dark:bg-red-950/20 dark:text-red-400' : '' }}
                                                ">
                                        {{ $entry['type'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    {{ $entry['description'] }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold font-mono text-gray-900 dark:text-white">
                                    {{ $entry['debit'] > 0 ? 'Rs. ' . number_format($entry['debit'], 0) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold font-mono text-gray-900 dark:text-white">
                                    {{ $entry['credit'] > 0 ? 'Rs. ' . number_format($entry['credit'], 0) : '—' }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-semibold font-mono {{ ($entry['balance'] ?? 0) > 0 ? 'text-red-500' : (($entry['balance'] ?? 0) < 0 ? 'text-green-600' : 'text-gray-500') }}">
                                    @php $bal = $entry['balance'] ?? 0; @endphp
                                    Rs. {{ number_format(abs($bal), 0) }}
                                    <span class="text-[10px] font-bold ml-0.5">{{ $bal > 0 ? 'Dr' : ($bal < 0 ? 'Cr' : '') }}</span>
                                </td>
                                <!-- <td class="px-4 py-3 text-right no-print">
                                                @if($entry['is_due'])
                                                    <form action="{{ route('ledgers.party.dues.destroy', $entry['id']) }}" method="POST"
                                                        onsubmit="return confirm('Are you sure you want to delete this due record?');"
                                                        class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-semibold">
                                                            Delete Due
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-xs text-gray-400 italic">Voucher Locked</span>
                                                @endif
                                            </td> -->
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-gray-600">
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
                            class="bg-gray-100/80 dark:bg-gray-800/80 border-t-2 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white font-bold">
                            <tr>
                                <td colspan="4"
                                    class="px-4 py-4 text-xs uppercase tracking-wider font-extrabold text-gray-700 dark:text-gray-300">
                                    Total Summary
                                </td>
                                <td class="px-4 py-4 text-right font-mono font-extrabold text-sm text-gray-900 dark:text-white">
                                    Rs. {{ number_format($sumDebit, 0) }}
                                </td>
                                <td class="px-4 py-4 text-right font-mono font-extrabold text-sm text-gray-900 dark:text-white">
                                    Rs. {{ number_format($sumCredit, 0) }}
                                </td>
                                @php $netBalance = $sumDebit - $sumCredit; @endphp
                                <td
                                    class="px-4 py-4 text-right font-mono font-extrabold text-sm {{ $netBalance > 0 ? 'text-red-500' : ($netBalance < 0 ? 'text-green-600' : 'text-gray-500') }}">
                                    Rs. {{ number_format(abs($netBalance), 0) }}
                                    <span class="text-xs ml-0.5">{{ $netBalance > 0 ? 'Dr' : ($netBalance < 0 ? 'Cr' : '') }}</span>
                                </td>
                                <td class="no-print"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @else
            <div class="px-4 py-12 text-center text-gray-400 dark:text-gray-600">
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