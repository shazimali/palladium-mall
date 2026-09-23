@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="All Ledgers" />

    <x-common.component-card title="" desc="">

        <form action="{{ route('ledgers.all') }}" method="GET" id="all-ledgers-form"
            class="sticky top-[72px] z-[990] bg-white/95 dark:bg-gray-900/95 p-4 rounded-2xl border-2 border-brand-500 shadow-xl backdrop-blur-md mb-6">

            <div class="flex flex-wrap items-end gap-3">

                {{-- Ledger Type Selector --}}
                <div class="w-64 flex-shrink-0">
                    <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Ledger Type
                    </label>
                    <x-common.searchable-select
                        name="ledger_type"
                        placeholder="Select Ledger Type"
                        :selected="$ledgerType"
                        :clearable="false"
                        :auto-submit="true"
                        :options="collect(\App\Support\LedgerTypeRegistry::types())
                            ->filter(fn($meta) => !($meta['permission'] ?? null) || $canViewLandlord)
                            ->map(fn($meta, $key) => ['value' => $key, 'label' => $meta['label']])
                            ->values()"
                    />
                </div>

                {{-- Per-type filter fields --}}
                @if($ledgerType === 'tenant')
                    <div class="w-64 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Flat / Shop</label>
                        <x-common.searchable-select
                            name="unit_id"
                            placeholder="Choose a Flat / Shop"
                            :selected="request('unit_id')"
                            :options="$units->map(fn($u) => ['value' => $u->id, 'label' => $u->unit_number . ' — ' . ($u->tenant->name ?? ($u->otherTenant->name ?? 'Vacant'))])"
                        />
                    </div>
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Date From</label>
                        <input type="text" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Date To</label>
                        <input type="text" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div class="flex-shrink-0 pb-0.5">
                        <label class="inline-flex items-center gap-1.5 rounded-lg border-2 border-gray-300 dark:border-gray-700 px-3 py-2.5 text-[11px] font-bold text-gray-700 dark:text-gray-300 cursor-pointer whitespace-nowrap">
                            <input type="checkbox" name="include_security_deposit" value="1" {{ request()->boolean('include_security_deposit') ? 'checked' : '' }}>
                            Show Security Deposit
                        </label>
                    </div>

                @elseif($ledgerType === 'owner')
                    <div class="w-56 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Owner</label>
                        <x-common.searchable-select
                            name="owner_id"
                            placeholder="Choose an Owner"
                            :selected="request('owner_id')"
                            :options="$owners->map(fn($o) => ['value' => $o->id, 'label' => $o->name])"
                        />
                    </div>
                    <div class="w-28 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Year</label>
                        <x-common.searchable-select
                            name="year"
                            placeholder="Year"
                            :selected="(string) ($year ?? now()->year)"
                            :clearable="false"
                            :options="collect(range(now()->year + 1, now()->year - 5))->map(fn($y) => ['value' => $y, 'label' => (string) $y])"
                        />
                    </div>
                    <div class="w-[420px] flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Months</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(range(1, 12) as $m)
                                <label class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-700 px-2.5 py-1 text-[11px] font-bold text-gray-700 dark:text-gray-300 cursor-pointer">
                                    <input type="checkbox" name="months[]" value="{{ $m }}" {{ in_array($m, $months ?? []) ? 'checked' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m, 1)->format('M') }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                @elseif($ledgerType === 'payment_account')
                    <div class="w-64 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Cash / Bank Account</label>
                        <x-common.searchable-select
                            name="payment_account_id"
                            placeholder="Choose an Account"
                            :selected="request('payment_account_id')"
                            :options="$accounts->map(fn($a) => ['value' => $a->id, 'label' => $a->name])"
                        />
                    </div>
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Date From</label>
                        <input type="text" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Date To</label>
                        <input type="text" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                @elseif($ledgerType === 'expense')
                    <div class="w-64 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Expense Category</label>
                        <x-common.searchable-select
                            name="expense_head_id"
                            placeholder="Choose a Category"
                            :selected="request('expense_head_id')"
                            :options="collect([['value' => 'all', 'label' => 'All Expenses']])->concat($expenseHeads->map(fn($h) => ['value' => $h->id, 'label' => $h->name]))"
                        />
                    </div>
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Date From</label>
                        <input type="text" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Date To</label>
                        <input type="text" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                @elseif($ledgerType === 'landlord')
                    <div class="w-64 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Landlord</label>
                        <x-common.searchable-select
                            name="landlord_id"
                            placeholder="Choose a Landlord"
                            :selected="request('landlord_id')"
                            :options="$landlords->map(fn($l) => ['value' => $l->id, 'label' => $l->name])"
                        />
                    </div>
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Date From</label>
                        <input type="text" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Date To</label>
                        <input type="text" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>

                @elseif($ledgerType === 'security')
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Date From</label>
                        <input type="text" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Date To</label>
                        <input type="text" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div class="w-44 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Transaction</label>
                        <x-common.searchable-select
                            name="transaction_type"
                            placeholder="All Transactions"
                            :selected="request('transaction_type', 'all')"
                            :options="[
                                ['value' => 'all', 'label' => 'All Transactions'],
                                ['value' => 'received', 'label' => 'Received'],
                                ['value' => 'deducted', 'label' => 'Deducted'],
                                ['value' => 'refunded', 'label' => 'Refunded'],
                            ]"
                        />
                    </div>
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Unit #</label>
                        <x-common.searchable-select
                            name="unit_id"
                            placeholder="All Units"
                            :selected="request('unit_id')"
                            :options="$units->map(fn($u) => ['value' => $u->id, 'label' => $u->unit_number])"
                        />
                    </div>

                @elseif($ledgerType === 'flat_shop')
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Date From</label>
                        <input type="text" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Date To</label>
                        <input type="text" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Owner Type</label>
                        <x-common.searchable-select
                            name="owner_type"
                            placeholder="All Owners"
                            :selected="request('owner_type')"
                            :options="[
                                ['value' => 'pm_mall', 'label' => 'PM Mall'],
                                ['value' => 'other_owned', 'label' => 'Other Owned'],
                            ]"
                        />
                    </div>
                    <div class="w-40 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Occupancy</label>
                        <x-common.searchable-select
                            name="occupancy_status"
                            placeholder="All Occupancy"
                            :selected="request('occupancy_status')"
                            :options="[
                                ['value' => 'pm_rented', 'label' => 'PM Mall Rented'],
                                ['value' => 'other_occupied', 'label' => 'Other Occupied'],
                                ['value' => 'other_unoccupied', 'label' => 'Other Unoccupied'],
                                ['value' => 'vacant', 'label' => 'PM Mall Vacant'],
                            ]"
                        />
                    </div>
                    <div class="w-40 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Billing Type</label>
                        <x-common.searchable-select
                            name="billing_type"
                            placeholder="All Billings"
                            :selected="request('billing_type', 'all')"
                            :options="[
                                ['value' => 'all', 'label' => 'All Billings'],
                                ['value' => 'rent', 'label' => 'Rent'],
                                ['value' => 'maintenance', 'label' => 'Maintenance'],
                                ['value' => 'extra_payment', 'label' => 'Extra Amount'],
                                ['value' => 'security_deposit', 'label' => 'Security Deposit'],
                            ]"
                        />
                    </div>
                    <div class="w-36 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Payment Status</label>
                        <x-common.searchable-select
                            name="payment_status"
                            placeholder="All Statuses"
                            :selected="request('payment_status', 'all')"
                            :options="[
                                ['value' => 'all', 'label' => 'All Statuses'],
                                ['value' => 'paid', 'label' => 'Paid'],
                                ['value' => 'unpaid', 'label' => 'Unpaid'],
                            ]"
                        />
                    </div>
                    <div class="w-32 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Unit #</label>
                        <x-common.searchable-select
                            name="unit_id"
                            placeholder="All Units"
                            :selected="request('unit_id')"
                            :options="$units->map(fn($u) => ['value' => $u->id, 'label' => $u->unit_number])"
                        />
                    </div>

                @elseif($ledgerType === 'party')
                    <div class="w-64 flex-shrink-0">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">Party</label>
                        <x-common.searchable-select
                            name="party_id"
                            placeholder="Choose a Party"
                            :selected="request('party_id')"
                            :options="$parties->map(fn($p) => ['value' => $p->id, 'label' => $p->name])"
                        />
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer">
                        Filter
                    </button>
                    <a href="{{ route('ledgers.all', ['ledger_type' => $ledgerType]) }}"
                        class="rounded-xl border-2 border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                        Clear
                    </a>
                    @if($hasSelection)
                        @if($printRoute)
                            <a href="{{ $printRoute }}" target="_blank"
                                class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-extrabold text-white shadow-md hover:bg-gray-800 transition-colors cursor-pointer">
                                🖨️ Print
                            </a>
                        @endif
                        @if($pdfRoute)
                            <a href="{{ $pdfRoute }}"
                                class="inline-flex items-center gap-2 rounded-xl border-2 border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                                PDF
                            </a>
                        @endif
                        @if($manageRoute)
                            <a href="{{ $manageRoute }}"
                                class="inline-flex items-center gap-2 rounded-xl border-2 border-brand-300 px-4 py-2.5 text-sm font-bold text-brand-700 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-300 dark:hover:bg-white/5 transition-colors">
                                + Add / Manage Dues
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </form>

        @if($hasSelection)

            {{-- Results Table --}}
            <div class="overflow-hidden border-2 border-gray-200 rounded-2xl dark:border-gray-800 shadow-md">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-[13.5px] text-left text-gray-900 dark:text-gray-100">
                        <thead class="text-xs font-black uppercase tracking-wider bg-brand-600 text-white dark:bg-brand-700 border-b-2 border-gray-200 dark:border-gray-700">
                            <tr>
                                @foreach($columns as $col)
                                    <th class="px-3.5 py-2.5 text-white {{ $col['class'] ?? '' }}">{{ $col['label'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-900 dark:text-gray-100 font-semibold text-xs sm:text-[13px]">
                            @forelse($rows as $row)
                                <tr
                                    @if(!empty($row['link'])) data-href="{{ $row['link'] }}" @endif
                                    class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors {{ !empty($row['link']) ? 'cursor-pointer' : '' }}"
                                >
                                    @foreach($columns as $col)
                                        @php
                                            $key = $col['key'];
                                            $val = $row[$key] ?? null;
                                            $type = $col['type'] ?? null;
                                        @endphp
                                        <td class="px-3.5 py-2 {{ $col['class'] ?? '' }}">
                                            @if($type === 'date')
                                                <span class="font-mono font-bold whitespace-nowrap">{{ $val ? ($val instanceof \Carbon\Carbon ? $val->format('d M Y') : \Carbon\Carbon::parse($val)->format('d M Y')) : '—' }}</span>
                                            @elseif($type === 'debit')
                                                <span class="font-mono font-black text-rose-600 dark:text-rose-400">{{ (float) $val > 0 ? 'Rs. ' . number_format($val, 2) : '—' }}</span>
                                            @elseif($type === 'credit')
                                                <span class="font-mono font-black text-emerald-600 dark:text-emerald-400">{{ (float) $val > 0 ? 'Rs. ' . number_format($val, 2) : '—' }}</span>
                                            @elseif($type === 'balance')
                                                <span class="font-mono font-black">{{ 'Rs. ' . number_format((float) $val, 2) }}</span>
                                            @elseif($type === 'amount')
                                                <span class="font-mono font-black">{{ (float) $val > 0 ? 'Rs. ' . number_format($val, 2) : '—' }}</span>
                                            @elseif($type === 'badge')
                                                <span class="rounded-md bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-black uppercase text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">{{ $val ?? '—' }}</span>
                                            @elseif($type === 'status')
                                                @php
                                                    $statusClass = match(strtolower((string) $val)) {
                                                        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                                                        'unpaid' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
                                                        'partial' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                                                        default => 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700',
                                                    };
                                                @endphp
                                                <span class="rounded-md px-2 py-0.5 text-xs font-black uppercase border {{ $statusClass }}">{{ $val ?? '—' }}</span>
                                            @else
                                                {{ $val ?? '—' }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($columns) }}" class="px-4 py-8 text-center text-gray-400 dark:text-gray-600 text-xs sm:text-sm font-bold">
                                        No entries found for the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($allRows ?? $rows) > 0)
                            <tfoot>
                                <tr class="bg-gray-100 dark:bg-gray-800 border-t-2 border-b-2 border-gray-300 dark:border-gray-700 font-black text-sm sm:text-base">
                                    @foreach($columns as $index => $col)
                                        @php
                                            $key = $col['key'];
                                            $type = $col['type'] ?? null;
                                            $totalsSet = $allRows ?? $rows;
                                            $sum = in_array($type, ['debit', 'credit', 'amount'])
                                                ? collect($totalsSet)->sum(fn($r) => (float) ($r[$key] ?? 0))
                                                : null;
                                            $lastVal = collect($totalsSet)->last()[$key] ?? null;
                                        @endphp
                                        <td class="px-3.5 py-3.5 {{ $col['class'] ?? '' }}">
                                            @if($index === 0)
                                                <span class="font-black uppercase tracking-wider text-gray-900 dark:text-white">Total</span>
                                            @elseif($type === 'debit')
                                                <span class="font-mono font-black text-rose-600 dark:text-rose-400">Rs. {{ number_format($sum, 2) }}</span>
                                            @elseif($type === 'credit')
                                                <span class="font-mono font-black text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($sum, 2) }}</span>
                                            @elseif($type === 'amount')
                                                <span class="font-mono font-black">Rs. {{ number_format($sum, 2) }}</span>
                                            @elseif($type === 'balance')
                                                <span class="font-mono font-black">Rs. {{ number_format((float) ($footerBalanceOverride ?? $lastVal), 2) }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            @if($paginator && $paginator->hasPages())
                <div class="mt-4 flex justify-end">
                    {{ $paginator->links() }}
                </div>
            @endif

        @else
            <div class="p-8 text-center text-gray-400 dark:text-gray-600 bg-gray-50 dark:bg-white/[0.01] border border-dashed border-gray-200 dark:border-gray-800 rounded-xl text-lg font-bold">
                Select the filters above to generate this ledger's statement.
            </div>
        @endif

    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                document.querySelectorAll('#date_from, #date_to').forEach(function (el) {
                    flatpickr(el, {
                        dateFormat: 'Y-m-d',
                        altInput: true,
                        altFormat: 'd M Y',
                        allowInput: true,
                        disableMobile: true,
                    });
                });
            }

            document.querySelectorAll('tbody tr[data-href]').forEach(function (row) {
                row.addEventListener('click', function (e) {
                    if (e.target.closest('a, button, input, select, label')) {
                        return;
                    }
                    if (e.ctrlKey || e.metaKey) {
                        window.open(row.dataset.href, '_blank');
                    } else {
                        window.location.href = row.dataset.href;
                    }
                });
            });
        });
    </script>
@endpush
