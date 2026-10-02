@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Vouchers" />

    @if(session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @php
        $canCreate = auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('vouchers.create');
        $canEdit = auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('vouchers.edit');
        $canDelete = auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('vouchers.delete');
        $filterKeys = ['search', 'type', 'cash_bank', 'source', 'payment_account_id', 'start_date', 'end_date', 'entry', 'entity_id', 'amount_min', 'amount_max', 'user_id'];
        $fieldCls = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
        $labelCls = 'mb-1 block text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400';
        $badge = [
            'cash_received' => 'bg-emerald-100 text-emerald-700',
            'bank_received' => 'bg-blue-100 text-blue-700',
            'cash_paid' => 'bg-orange-100 text-orange-700',
            'bank_paid' => 'bg-red-100 text-red-700',
        ];
        $sourceBadge = [
            'legacy' => 'bg-gray-100 text-gray-600',
            'stock_entry' => 'bg-purple-100 text-purple-700',
            'move_out' => 'bg-amber-100 text-amber-700',
        ];
    @endphp

    <x-common.component-card title="" desc="">
        @if($canCreate)
            <div class="flex flex-wrap items-center justify-center gap-3">
                @foreach(\App\Models\Voucher::TYPES as $key => $label)
                    <a href="{{ route('vouchers.create', ['type' => $key]) }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-600 transition-all shadow-md">
                        + {{ $label }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Filters: options adapt to the chosen voucher type --}}
        <div class="my-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
            x-data="voucherFilters({
                type: @js((string) request('type', '')),
                cashBank: @js((string) request('cash_bank', '')),
                accountId: @js((string) request('payment_account_id', '')),
                entry: @js((string) request('entry', '')),
                entityId: @js((string) request('entity_id', '')),
                accounts: @js($paymentAccounts->map(fn($a) => ['value' => (string) $a->id, 'label' => $a->name, 'cash' => \App\Models\Voucher::isCashAccount($a)])->values()),
                options: @js($filterOptions),
                receivedEntries: @js(\App\Models\Voucher::RECEIVED_ENTRY_TYPES),
                paidEntries: @js(\App\Models\Voucher::PAID_ENTRY_TYPES),
            })">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white">🔍 Search & Filter Vouchers</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('vouchers.print-list', request()->query()) }}" target="_blank"
                        class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-600">🖨️ Print List</a>
                    @if(request()->anyFilled($filterKeys))
                        <a href="{{ route('vouchers.index') }}"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300">Clear Filters</a>
                    @endif
                </div>
            </div>

            <form action="{{ route('vouchers.index') }}" method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
                {{-- Row 1: what kind of voucher, where the money moved, when --}}
                <div class="sm:col-span-2">
                    <label class="{{ $labelCls }}">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Voucher no, manual no, name, remarks" class="{{ $fieldCls }}">
                </div>
                <div>
                    <label class="{{ $labelCls }}">Voucher Type</label>
                    <select name="type" x-model="type" @change="onTypeChange()" class="{{ $fieldCls }}">
                        <option value="">All Vouchers</option>
                        <option value="received">All Received</option>
                        <option value="paid">All Paid</option>
                        @foreach(\App\Models\Voucher::TYPES as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="!specificType()">
                    <label class="{{ $labelCls }}">Cash / Bank</label>
                    <select name="cash_bank" x-model="cashBank" @change="onCashBankChange()" :disabled="specificType()" class="{{ $fieldCls }}">
                        <option value="">Cash & Bank</option>
                        <option value="cash">Cash only</option>
                        <option value="bank">Bank only</option>
                    </select>
                </div>
                <div>
                    <label class="{{ $labelCls }}" x-text="accountLabel()"></label>
                    <select name="payment_account_id" x-model="accountId" class="{{ $fieldCls }}">
                        <option value="">All Accounts</option>
                        <template x-for="a in visibleAccounts()" :key="a.value">
                            <option :value="a.value" x-text="a.label" :selected="a.value === accountId"></option>
                        </template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2" :class="specificType() ? 'lg:col-span-2' : ''">
                    <div>
                        <label class="{{ $labelCls }}">Date From</label>
                        <input type="text" id="start_date" name="start_date" value="{{ request('start_date') }}" placeholder="From" autocomplete="off" class="{{ $fieldCls }} cursor-pointer">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Date To</label>
                        <input type="text" id="end_date" name="end_date" value="{{ request('end_date') }}" placeholder="To" autocomplete="off" class="{{ $fieldCls }} cursor-pointer">
                    </div>
                </div>

                {{-- Row 2: who / what the entries were for, amount, who prepared --}}
                <div>
                    <label class="{{ $labelCls }}" x-text="entryLabel()"></label>
                    <select name="entry" x-model="entry" @change="entityId = ''" class="{{ $fieldCls }}">
                        <option value="">All</option>
                        <template x-for="(label, key) in entries()" :key="key">
                            <option :value="key" x-text="label" :selected="key === entry"></option>
                        </template>
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="{{ $labelCls }}" x-text="entry ? entityLabel() : 'Party / Unit / Head'"></label>
                    <input type="hidden" name="entity_id" :value="entityId" :disabled="!entry || !entityId">
                    <div class="relative" @click.away="open = false">
                        <button type="button" @click="if (entry) { open = !open; $nextTick(() => $refs.entitySearch?.focus()) }"
                            class="{{ $fieldCls }} flex items-center justify-between text-left" :class="entry ? 'cursor-pointer' : 'opacity-50 cursor-not-allowed'">
                            <span class="truncate" x-text="entityText()"></span>
                            <span class="ml-2 text-xs opacity-60">▼</span>
                        </button>
                        <div x-show="open" x-transition x-cloak
                            class="absolute left-0 top-full z-50 mt-1 w-72 rounded-xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden">
                            <div class="p-2 border-b border-gray-200 dark:border-gray-800">
                                <input type="text" x-ref="entitySearch" x-model="search" placeholder="Type to search..."
                                    @keydown.escape="open = false" @keydown.enter.prevent="let f = filteredEntities(); if (f.length) pickEntity(f[0])"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-1.5 text-sm dark:text-white">
                            </div>
                            <div class="max-h-60 overflow-y-auto p-1">
                                <button type="button" @click="pickEntity(null)" class="w-full text-left px-3 py-1.5 rounded-lg text-sm font-semibold text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5">All</button>
                                <template x-for="opt in filteredEntities()" :key="opt.value">
                                    <button type="button" @click="pickEntity(opt)"
                                        class="w-full text-left px-3 py-1.5 rounded-lg text-sm font-semibold truncate"
                                        :class="String(opt.value) === entityId ? 'bg-brand-600 text-white' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5'"
                                        x-text="opt.label"></button>
                                </template>
                                <p x-show="filteredEntities().length === 0" class="px-3 py-2 text-xs text-gray-400">No match</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="{{ $labelCls }}">Amount From</label>
                        <input type="number" min="0" step="1" name="amount_min" value="{{ request('amount_min') }}" placeholder="0" class="{{ $fieldCls }}">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Amount To</label>
                        <input type="number" min="0" step="1" name="amount_max" value="{{ request('amount_max') }}" placeholder="Any" class="{{ $fieldCls }}">
                    </div>
                </div>
                <div>
                    <label class="{{ $labelCls }}">Prepared By</label>
                    <select name="user_id" class="{{ $fieldCls }}">
                        <option value="">Anyone</option>
                        @foreach($preparers as $preparer)
                            <option value="{{ $preparer->id }}" @selected((string) request('user_id') === (string) $preparer->id)>{{ $preparer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $labelCls }}">Source</label>
                    <select name="source" class="{{ $fieldCls }}">
                        <option value="">All Sources</option>
                        @foreach(\App\Models\Voucher::SOURCES as $key => $label)
                            <option value="{{ $key }}" @selected(request('source') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="h-10 w-full rounded-lg bg-brand-500 px-6 text-sm font-bold text-white hover:bg-brand-600 cursor-pointer">Apply Filters</button>
                </div>
            </form>
        </div>

        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-900/10">
                <p class="text-xs font-bold uppercase text-emerald-700">Total Received</p>
                <p class="text-xl font-black font-mono text-emerald-800 dark:text-emerald-400">Rs. {{ number_format($totals['received']) }}</p>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-900/10">
                <p class="text-xs font-bold uppercase text-red-700">Total Paid</p>
                <p class="text-xl font-black font-mono text-red-800 dark:text-red-400">Rs. {{ number_format($totals['paid']) }}</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/[0.03]">
                    <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                        <th class="px-4 py-3">Voucher No</th>
                        <th class="px-4 py-3">Manual No</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Account</th>
                        <th class="px-4 py-3">Narration</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $v)
                        <tr class="border-t border-gray-100 dark:border-gray-800 text-gray-800 dark:text-white/90">
                            <td class="px-4 py-3 font-mono font-bold">
                                <a href="{{ route('vouchers.print', $v) }}" class="text-brand-600 hover:underline">{{ $v->voucher_no }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $v->manual_voucher_no ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $v->date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $badge[$v->type] ?? '' }}">{{ $v->type_label }}</span>
                                @if($v->source !== 'form')
                                    <span class="ml-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $sourceBadge[$v->source] ?? '' }}">{{ $v->source_label }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $v->paymentAccount?->name ?? '—' }}</td>
                            <td class="px-4 py-3 max-w-xs truncate">{{ $v->narration ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold">{{ number_format($v->total_amount) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('vouchers.print', $v) }}" class="text-xs font-bold text-blue-600 hover:underline">Print</a>
                                @if($canEdit && !$v->isLocked())
                                    <a href="{{ route('vouchers.edit', $v) }}" class="ml-3 text-xs font-bold text-amber-600 hover:underline">Edit</a>
                                @endif
                                @if($canDelete && !$v->isLocked())
                                    <form action="{{ route('vouchers.destroy', $v) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete voucher {{ $v->voucher_no }}? All its entries will be reversed.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-3 text-xs font-bold text-red-600 hover:underline cursor-pointer">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-500">No vouchers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $vouchers->links() }}</div>
    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        function voucherFilters({ type, cashBank, accountId, entry, entityId, accounts, options, receivedEntries, paidEntries }) {
            // Entity dropdown per entry type; tenant entries are filtered by unit on both sides
            const ENTITY_OPTIONS = { tenant: 'unit', party: 'party', landlord: 'landlord', owner: 'owner', account: 'account', expense: 'expense' };
            const ENTITY_LABELS = { tenant: 'Unit', party: 'Party', landlord: 'Landlord', owner: 'Owner', account: 'Cash / Bank Account', expense: 'Expense Head' };
            return {
                type, cashBank, accountId, entry, entityId, accounts, options,
                open: false, search: '',

                side() {
                    if (this.type === 'received' || this.type.endsWith('_received')) return 'received';
                    if (this.type === 'paid' || this.type.endsWith('_paid')) return 'paid';
                    return '';
                },
                specificType() { return this.type.startsWith('cash_') || this.type.startsWith('bank_'); },
                // cash / bank as fixed by the type, or chosen in the Cash / Bank filter
                money() {
                    if (this.type.startsWith('cash_')) return 'cash';
                    if (this.type.startsWith('bank_')) return 'bank';
                    return this.cashBank;
                },
                visibleAccounts() {
                    const m = this.money();
                    return m ? this.accounts.filter(a => a.cash === (m === 'cash')) : this.accounts;
                },
                accountLabel() {
                    const side = this.side();
                    if (side === 'received') return 'Debit Account (Cash / Bank)';
                    if (side === 'paid') return 'Credit Account (Cash / Bank)';
                    return 'Cash / Bank Account';
                },
                entries() {
                    const side = this.side();
                    if (side === 'received') return receivedEntries;
                    if (side === 'paid') return paidEntries;
                    return { ...receivedEntries, ...paidEntries, tenant: 'Tenant / Unit', account: 'Cash / Bank Account' };
                },
                entryLabel() {
                    const side = this.side();
                    if (side === 'received') return 'Credit Account Type';
                    if (side === 'paid') return 'Debit Account Type';
                    return 'Account Type';
                },
                entityLabel() { return ENTITY_LABELS[this.entry] ?? 'Account'; },
                entityList() { return this.options[ENTITY_OPTIONS[this.entry]] ?? []; },
                filteredEntities() {
                    const list = this.entityList();
                    if (!this.search) return list;
                    const s = this.search.toLowerCase();
                    return list.filter(o => o.label.toLowerCase().includes(s));
                },
                entityText() {
                    if (!this.entry) return 'Choose an account type first';
                    const opt = this.entityList().find(o => String(o.value) === this.entityId);
                    return opt ? opt.label : 'All ' + this.entityLabel() + 's';
                },
                pickEntity(opt) { this.entityId = opt ? String(opt.value) : ''; this.open = false; this.search = ''; },

                // Drop selections that no longer fit the chosen type
                onTypeChange() {
                    if (this.specificType()) this.cashBank = '';
                    this.onCashBankChange();
                    if (this.entry && !(this.entry in this.entries())) { this.entry = ''; this.entityId = ''; }
                },
                onCashBankChange() {
                    if (this.accountId && !this.visibleAccounts().some(a => a.value === this.accountId)) this.accountId = '';
                },
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr === 'undefined') return;

            const options = { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd M Y', allowInput: true, disableMobile: true };
            const from = flatpickr('#start_date', {
                ...options,
                onChange: ([date]) => to.set('minDate', date || null),
            });
            const to = flatpickr('#end_date', {
                ...options,
                minDate: @js(request('start_date')) || null,
                onChange: ([date]) => from.set('maxDate', date || null),
            });
            if (@js(request('end_date'))) from.set('maxDate', @js(request('end_date')));
        });
    </script>
@endpush
