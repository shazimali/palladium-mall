@extends('layouts.app')

@section('content')
    @php
        $isReceived = \App\Models\Voucher::isReceivedType($type);
        $isCash = \App\Models\Voucher::isCashType($type);
        $typeLabel = \App\Models\Voucher::TYPES[$type];
        $initialLines = old('lines', $lines ?: []);
        $action = $voucher ? route('vouchers.update', $voucher) : route('vouchers.store');
        $dateValue = old('date', $voucher?->date?->toDateString() ?? now()->toDateString());
        // Received: cash/bank is debited, the other account credited. Paid: the reverse.
        $cashSide = $isReceived ? 'Debit' : 'Credit';
        $entrySide = $isReceived ? 'Credit' : 'Debit';

        $labelCell = 'bg-brand-600 dark:bg-brand-900 text-white px-3 py-1.5 flex items-center font-bold text-xs tracking-wide';
        $valueCell = 'col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 flex items-center';
        $inputCls = 'w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 text-sm font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all';
    @endphp

    <x-common.page-breadcrumb :pageTitle="$voucher ? 'Edit ' . $typeLabel . ' Voucher' : 'New ' . $typeLabel . ' Voucher'" />

    @if(session('error'))
        <div class="mb-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
            {{ session('error') }}
        </div>
    @endif

    {{-- Type switcher (create only; the type is fixed once a voucher exists) --}}
    @unless($voucher)
        <div class="mb-2 flex flex-wrap justify-center gap-1.5">
            @foreach(\App\Models\Voucher::TYPES as $key => $label)
                <a href="{{ route('vouchers.create', ['type' => $key]) }}"
                    class="rounded-lg px-3 py-1.5 text-xs font-bold shadow-xs transition-all {{ $key === $type ? 'bg-brand-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    @endunless

    <form action="{{ $action }}" method="POST" @submit.prevent="handleSubmit($event)"
        x-data="voucherForm({
            lines: @js(array_values($initialLines)),
            entryTypes: @js($entryTypes),
            options: @js($options),
            isReceived: @js($isReceived),
            paymentAccountId: @js((string) old('payment_account_id', $voucher?->payment_account_id ?? $defaultAccountId ?? '')),
            narration: @js(old('narration', $voucher?->narration ?? '')),
            errors: @js($errors->getMessages()),
            typeLabel: @js($typeLabel),
            isEdit: @js((bool) $voucher),
            duesUrl: @js(route('ajax.tenant-pending-payments')),
            payablesUrl: @js(route('vouchers.tenant-payables')),
            voucherId: @js($voucher?->id),
        })">
        @csrf
        @if($voucher)
            @method('PUT')
            {{-- Reference is no longer entered on this form; keep any existing value --}}
            <input type="hidden" name="reference" value="{{ $voucher->reference }}">
        @else
            <input type="hidden" name="type" value="{{ $type }}">
        @endif

        {{-- Added entries are posted from these hidden fields; the table below is display only --}}
        <template x-for="(line, i) in lines" :key="line._key">
            <div>
                <input type="hidden" :name="`lines[${i}][entry_type]`" :value="line.entry_type">
                <input type="hidden" :name="`lines[${i}][${fieldFor(line.entry_type)}]`" :value="line[fieldFor(line.entry_type)]">
                {{-- Extra ids not covered by the main field (e.g. the unit of a tenant security refund) --}}
                <template x-if="line.unit_id && fieldFor(line.entry_type) !== 'unit_id'">
                    <input type="hidden" :name="`lines[${i}][unit_id]`" :value="line.unit_id">
                </template>
                <template x-if="line.tenant_id && fieldFor(line.entry_type) !== 'tenant_id'">
                    <input type="hidden" :name="`lines[${i}][tenant_id]`" :value="line.tenant_id">
                </template>
                <template x-for="pid in (line.payment_ids || [])" :key="pid">
                    <input type="hidden" :name="`lines[${i}][payment_ids][]`" :value="pid">
                </template>
                <input type="hidden" :name="`lines[${i}][amount]`" :value="line.amount">
                <input type="hidden" :name="`lines[${i}][notes]`" :value="line.notes ?? ''">
            </div>
        </template>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm text-gray-900 dark:text-white font-sans relative">

            {{-- HEADER: centered title & voucher number badge --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 mb-3 pb-2 border-b border-gray-200 dark:border-gray-800">
                <div class="hidden sm:block w-36"></div>
                <h2 class="text-lg sm:text-xl font-black tracking-tight text-brand-600 dark:text-brand-400 uppercase text-center">
                    {{ $typeLabel }} Voucher
                </h2>
                <div class="inline-flex items-center gap-2 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3 py-1 shadow-2xs">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Voucher No:</span>
                    <span class="text-sm font-black font-mono text-brand-600 dark:text-brand-400">{{ $nextVoucherNo }}</span>
                </div>
            </div>

            {{-- FORM GRID (same layout as old vouchers, compact) --}}
            <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-xl overflow-visible mb-3 border border-gray-200 dark:border-gray-700">
                {{-- Row 1: Voucher Date & Manual Voucher No --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-t-xl">
                    <div class="grid grid-cols-3 min-h-[40px]">
                        <div class="{{ $labelCell }} rounded-tl-xl">Voucher Date <span class="text-rose-300 ml-1">*</span></div>
                        <div class="{{ $valueCell }}">
                            <input type="text" id="voucher_date" name="date" value="{{ $dateValue }}" required autocomplete="off" class="{{ $inputCls }} cursor-pointer">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 min-h-[40px]">
                        <div class="{{ $labelCell }}">Manual Voucher No</div>
                        <div class="{{ $valueCell }} md:rounded-tr-xl">
                            <input type="text" name="manual_voucher_no" value="{{ old('manual_voucher_no', $voucher?->manual_voucher_no) }}" maxlength="255" placeholder="e.g. 1024" class="{{ $inputCls }}">
                        </div>
                    </div>
                </div>

                {{-- Row 2: Cash/Bank Account & other account type --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-40">
                    <div class="grid grid-cols-3 min-h-[40px]">
                        <div class="{{ $labelCell }}">
                            {{ $cashSide }} Account ({{ $isCash ? 'Cash' : 'Bank' }}) <span class="text-rose-300 ml-1">*</span>
                        </div>
                        <div class="{{ $valueCell }}">
                            <select name="payment_account_id" x-model="paymentAccountId" required class="{{ $inputCls }}">
                                <option value="">Select {{ $isCash ? 'Cash' : 'Bank' }} Account...</option>
                                @foreach($headerAccounts as $account)
                                    <option value="{{ $account['value'] }}">{{ $account['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 min-h-[40px]">
                        <div class="{{ $labelCell }}">{{ $entrySide }} Account Type <span class="text-rose-300 ml-1">*</span></div>
                        <div class="{{ $valueCell }}">
                            <select x-model="draft.entry_type" @change="onTypeChange()" class="{{ $inputCls }}">
                                <template x-for="(label, key) in entryTypes" :key="key">
                                    <option :value="key" x-text="label" :selected="key === draft.entry_type"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Row 3: Searchable selection & Amount --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-50">
                    <div class="grid grid-cols-3 min-h-[40px]">
                        <div class="{{ $labelCell }}">
                            {{ $entrySide }} Account<span class="text-rose-300 ml-1">*</span>
                        </div>
                        <div class="{{ $valueCell }} flex-col !items-stretch gap-1">
                            <div class="w-full relative" @click.away="draft._open = false; draft._hi = -1">
                                <button type="button" @click="toggle($el)"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg px-2.5 py-1.5 text-sm font-bold text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                    <span class="truncate" x-text="selectedLabel(draft) || ('Select ' + entryTypes[draft.entry_type] + '...')"></span>
                                    <span class="ml-2 text-xs opacity-60">▼</span>
                                </button>

                                <div x-show="draft._open" x-transition x-cloak
                                    class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                    <div class="p-2 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <input type="text" x-model="draft._search" data-picker-search placeholder="Type to search..."
                                            @keydown.arrow-down.prevent="move(1)"
                                            @keydown.arrow-up.prevent="move(-1)"
                                            @keydown.enter.prevent="let f = filtered(); if (draft._hi >= 0 && f[draft._hi]) pick(f[draft._hi])"
                                            @keydown.escape="draft._open = false; draft._hi = -1"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-gray-900 dark:text-white font-bold focus:border-brand-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-[240px] overflow-y-auto p-1 space-y-1 text-sm">
                                        <template x-for="(opt, index) in filtered()" :key="opt.value">
                                            <button type="button" @click="pick(opt)" @mouseenter="draft._hi = index"
                                                class="w-full text-left px-3 py-2 rounded-lg transition-colors flex items-center justify-between"
                                                :class="String(draft[fieldFor(draft.entry_type)]) === String(opt.value) ? 'bg-brand-600 text-white font-black' : (draft._hi === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                                <span x-text="opt.label" class="font-bold text-sm truncate"></span>
                                                <span x-show="String(draft[fieldFor(draft.entry_type)]) === String(opt.value)" class="font-black text-sm">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="filtered().length === 0" class="px-3 py-2 text-center text-xs font-semibold text-gray-400">
                                            No matching record found
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="grid grid-cols-3 min-h-[40px]">
                        <div class="{{ $labelCell }}">Amount <span class="text-rose-300 ml-1">*</span></div>
                        <div class="{{ $valueCell }}">
                            <input type="text" inputmode="numeric" placeholder="0" x-model="draft._display" @input="formatAmount($event.target.value)"
                                @keydown.enter.prevent="addEntry()" class="{{ $inputCls }} font-mono">
                        </div>
                    </div>
                </div>

                {{-- Security deposit payable to the selected tenant (paid vouchers → Tenant Security Refund) --}}
                <div class="grid grid-cols-3 md:grid-cols-6 gap-[2px] bg-gray-200 dark:bg-gray-700" x-show="showPayables()" x-cloak>
                    <div class="{{ $labelCell }}">Security Deposit Payable</div>
                    <div class="col-span-2 md:col-span-5 bg-gray-50 dark:bg-gray-800 px-2 py-1.5">
                        <p x-show="draft.payablesLoading" class="text-xs font-semibold text-gray-500 py-1">Loading payables...</p>
                        <p x-show="!draft.payablesLoading && draft.payables.length === 0" class="text-xs font-bold text-rose-600 py-1">No security deposit is payable to this tenant.</p>
                        <div x-show="!draft.payablesLoading && draft.payables.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-1.5 max-h-[132px] overflow-y-auto pr-1">
                            <template x-for="u in draft.payables" :key="u.unit_id">
                                <label class="flex items-center justify-between gap-2 px-2 py-1 rounded-lg border cursor-pointer select-none bg-white dark:bg-gray-900 text-xs"
                                    :class="String(draft.unit_id) === String(u.unit_id) ? 'border-brand-500 ring-1 ring-brand-500/30' : 'border-gray-200 dark:border-gray-700'">
                                    <span class="flex items-center gap-2 min-w-0">
                                        <input type="radio" :value="String(u.unit_id)" :checked="String(draft.unit_id) === String(u.unit_id)" @change="pickPayable(u)"
                                            class="h-4 w-4 border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                                        <span class="font-bold text-gray-900 dark:text-white truncate" x-text="'Unit ' + u.unit_number"></span>
                                    </span>
                                    <span class="font-black font-mono text-brand-600 dark:text-brand-400 shrink-0" x-text="'Rs. ' + Math.round(u.pending).toLocaleString('en-US')"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Outstanding dues of the selected tenant unit (received vouchers only) --}}
                <div class="grid grid-cols-3 md:grid-cols-6 gap-[2px] bg-gray-200 dark:bg-gray-700" x-show="showDues()" x-cloak>
                    <div class="{{ $labelCell }} flex-col !items-start justify-center gap-1">
                        <span>Outstanding Dues</span>
                        <button type="button" x-show="draft.dues.length > 1" @click="toggleAllDues()"
                            class="text-[10px] font-bold underline text-white/90 hover:text-white cursor-pointer"
                            x-text="draft.payment_ids.length === draft.dues.length ? 'Unselect all' : 'Select all'"></button>
                    </div>
                    <div class="col-span-2 md:col-span-5 bg-gray-50 dark:bg-gray-800 px-2 py-1.5">
                        <p x-show="draft.duesLoading" class="text-xs font-semibold text-gray-500 py-1">Loading outstanding dues...</p>
                        <p x-show="!draft.duesLoading && draft.dues.length === 0" class="text-xs font-bold text-emerald-600 dark:text-emerald-400 py-1">✓ No outstanding dues for this Flat / Shop.</p>
                        <div x-show="!draft.duesLoading && draft.dues.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-1.5 max-h-[132px] overflow-y-auto pr-1">
                            <template x-for="p in draft.dues" :key="p.id">
                                <label class="flex items-center justify-between gap-2 px-2 py-1 rounded-lg border cursor-pointer select-none bg-white dark:bg-gray-900 text-xs"
                                    :class="draft.payment_ids.includes(String(p.id)) ? 'border-brand-500 ring-1 ring-brand-500/30' : 'border-gray-200 dark:border-gray-700'">
                                    <span class="flex items-center gap-2 min-w-0">
                                        <input type="checkbox" :value="String(p.id)" x-model="draft.payment_ids" @change="applyDuesAmount()"
                                            class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                                        <span class="font-bold text-gray-900 dark:text-white truncate" x-text="p.month + ' - ' + p.type"></span>
                                    </span>
                                    <span class="font-black font-mono text-brand-600 dark:text-brand-400 shrink-0" x-text="'Rs. ' + Math.round(p.balance).toLocaleString('en-US')"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Row 4: Entry remarks & Add entry --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-b-xl">
                    <div class="grid grid-cols-3 min-h-[40px]">
                        <div class="{{ $labelCell }} md:rounded-bl-xl">Entry Remarks</div>
                        <div class="{{ $valueCell }}">
                            <input type="text" x-model="draft.notes" maxlength="1000" placeholder="Optional" @keydown.enter.prevent="addEntry()" class="{{ $inputCls }}">
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 px-2 py-1.5 flex items-center justify-between gap-2 rounded-b-xl md:rounded-bl-none">
                        <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400">For one entry just save. Use Add Entry for more.</span>
                        <button type="button" @click="addEntry()"
                            class="shrink-0 inline-flex items-center gap-1 rounded-lg border border-brand-500 px-3 py-1.5 text-xs font-bold text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-white/5 transition-all cursor-pointer">
                            ➕ Add Entry
                        </button>
                    </div>
                </div>
            </div>

            {{-- ENTRIES TABLE --}}
            <div class="mb-3 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700" x-show="lines.length" x-cloak>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800 text-xs uppercase text-gray-600 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-1.5 text-left w-10">#</th>
                            <th class="px-3 py-1.5 text-left">Type</th>
                            <th class="px-3 py-1.5 text-left">{{ $entrySide }} Account</th>
                            <th class="px-3 py-1.5 text-left">Remarks</th>
                            <th class="px-3 py-1.5 text-right">Amount</th>
                            <th class="px-3 py-1.5 w-28"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(line, i) in lines" :key="line._key">
                            <tr class="border-t border-gray-100 dark:border-gray-800 text-gray-800 dark:text-white/90">
                                <td class="px-3 py-1.5 font-bold" x-text="i + 1"></td>
                                <td class="px-3 py-1.5" x-text="entryTypes[line.entry_type]"></td>
                                <td class="px-3 py-1.5 font-bold">
                                    <span x-text="selectedLabel(line)"></span>
                                    <p class="text-[11px] font-semibold text-gray-500" x-show="line.refund_label" x-text="line.refund_label"></p>
                                    <p class="text-[11px] font-semibold text-gray-500" x-show="(line.payment_ids || []).length"
                                        x-text="(line.payment_labels || []).length ? line.payment_labels.join(', ') : line.payment_ids.length + ' due(s) selected'"></p>
                                    <p class="text-xs font-bold text-rose-600" x-show="rowError(i)" x-text="rowError(i)"></p>
                                </td>
                                <td class="px-3 py-1.5 text-gray-500" x-text="line.notes || ''"></td>
                                <td class="px-3 py-1.5 text-right font-mono font-bold" x-text="Number(line.amount).toLocaleString('en-US')"></td>
                                <td class="px-3 py-1.5 text-right whitespace-nowrap">
                                    <button type="button" @click="editEntry(i)" class="text-xs font-bold text-amber-600 hover:underline cursor-pointer">Edit</button>
                                    <button type="button" @click="removeEntry(i)" class="ml-2 text-xs font-bold text-rose-600 hover:underline cursor-pointer">Remove</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                            <td colspan="4" class="px-3 py-1.5 text-right text-xs font-bold uppercase text-gray-600 dark:text-gray-400">Total</td>
                            <td class="px-3 py-1.5 text-right font-mono font-black text-emerald-700 dark:text-emerald-400" x-text="'Rs. ' + total().toLocaleString('en-US')"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- BOTTOM SECTION: Approved by & Remarks (same layout as old vouchers, compact) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-stretch">
                <div class="bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white rounded-xl px-3 py-2 flex flex-col justify-center border border-gray-200 dark:border-gray-700 shadow-xs">
                    <p class="text-xs font-bold text-gray-700 dark:text-gray-300">
                        Approved by: <span class="text-brand-600 dark:text-brand-400 font-extrabold ml-1">{{ $voucher?->user?->name ?? auth()->user()->name }}</span>
                    </p>
                </div>
                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 shadow-xs">
                    <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Remarks:</label>
                    <textarea name="narration" x-model="narration" @input="narrationEdited = true" rows="1" maxlength="1000"
                        placeholder="Voucher remarks or notes..."
                        class="{{ $inputCls }} resize-none placeholder-gray-400"></textarea>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-center justify-end gap-2 mt-3 pt-3 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('vouchers.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold transition-all text-sm">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-white font-bold text-sm shadow-md transition-all cursor-pointer">
                    {{ $voucher ? 'Update Voucher' : 'Save Voucher' }}
                </button>
            </div>
        </div>
    </form>

    <script>
        function voucherForm({ lines, entryTypes, options, isReceived, paymentAccountId, narration, errors, typeLabel, isEdit, duesUrl, payablesUrl, voucherId }) {
            const FIELD = {
                party: 'party_id', landlord: 'landlord_id', owner: 'owner_id', account: 'account_id',
                expense: 'expense_head_id', tenant: 'unit_id',
            };
            const OPTIONS = {
                party: 'party', landlord: 'landlord', owner: 'owner', account: 'account',
                expense: 'expense', tenant: 'unit',
            };
            const swalBtn = 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer';
            const warn = (title, text) => Swal.fire({ title, text, icon: 'warning', confirmButtonText: 'OK', customClass: { confirmButton: swalBtn }, buttonsStyling: false });
            const firstType = Object.keys(entryTypes)[0];
            let seq = 0;
            const newDraft = (type = firstType) => ({ entry_type: type, tenant_id: '', unit_id: '', amount: '', _display: '', notes: '', _open: false, _search: '', _hi: -1,
                payment_ids: [], dues: [], duesLoading: false, rv_id: null, payables: [], payablesLoading: false, refund_label: '' });

            return {
                lines: lines.map(l => ({ ...l, _key: ++seq })),
                draft: newDraft(),
                entryTypes, options, isReceived, paymentAccountId, narration, errors,
                narrationEdited: !!narration,

                // Tenant entries: received vouchers pick a unit (its dues); paid vouchers pick a tenant (security refund)
                fieldFor(type) { return type === 'tenant' && !this.isReceived ? 'tenant_id' : (FIELD[type] ?? 'party_id'); },
                optionsFor(type) { return this.options[type === 'tenant' && !this.isReceived ? 'tenant' : OPTIONS[type]] ?? []; },
                filtered() {
                    const opts = this.optionsFor(this.draft.entry_type);
                    if (!this.draft._search) return opts;
                    const s = this.draft._search.toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
                    return opts.filter(o => o.label.toLowerCase().replace(/[^a-z0-9]+/g, ' ').includes(s));
                },
                selectedLabel(line) {
                    const id = line[this.fieldFor(line.entry_type)];
                    const opt = this.optionsFor(line.entry_type).find(o => String(o.value) === String(id ?? ''));
                    return opt ? opt.label : '';
                },
                toggle(el) {
                    this.draft._open = !this.draft._open;
                    if (this.draft._open) this.$nextTick(() => el.parentElement.querySelector('[data-picker-search]')?.focus());
                },
                move(step) {
                    const n = this.filtered().length;
                    if (n) this.draft._hi = (this.draft._hi + step + n) % n;
                },
                pick(opt) {
                    this.draft[this.fieldFor(this.draft.entry_type)] = opt.value;
                    this.draft._open = false; this.draft._search = ''; this.draft._hi = -1;
                    this.draft.payment_ids = [];
                    this.draft.rv_id = null;
                    if (this.isPaidTenant()) {
                        this.draft.unit_id = '';
                        this.draft.refund_label = '';
                        this.loadPayables();
                    }
                    this.loadDues();
                    this.updateAutoRemarks();
                },

                // ── Tenant outstanding dues (received vouchers) ──
                showDues() { return this.isReceived && this.draft.entry_type === 'tenant' && !!this.draft.unit_id; },
                async loadDues() {
                    if (!this.showDues()) { this.draft.dues = []; return; }
                    const draft = this.draft;
                    draft.duesLoading = true;
                    try {
                        const params = new URLSearchParams({ unit_id: draft.unit_id });
                        // When re-editing a saved entry, count its own allocations as still outstanding
                        if (draft.rv_id) params.set('exclude_voucher_id', draft.rv_id);
                        const res = await fetch(duesUrl + '?' + params, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        draft.dues = data.payments || [];
                        const ids = draft.dues.map(p => String(p.id));
                        draft.payment_ids = draft.payment_ids.map(String).filter(id => ids.includes(id));
                    } catch (e) {
                        draft.dues = [];
                    } finally {
                        draft.duesLoading = false;
                    }
                },
                selectedDuesTotal() {
                    return this.draft.dues
                        .filter(p => this.draft.payment_ids.includes(String(p.id)))
                        .reduce((s, p) => s + Math.round(p.balance), 0);
                },
                applyDuesAmount() {
                    const total = this.selectedDuesTotal();
                    this.formatAmount(total > 0 ? String(total) : '');
                },
                toggleAllDues() {
                    this.draft.payment_ids = this.draft.payment_ids.length === this.draft.dues.length
                        ? [] : this.draft.dues.map(p => String(p.id));
                    this.applyDuesAmount();
                },
                // ── Tenant security deposit payable (paid vouchers) ──
                isPaidTenant() { return !this.isReceived && this.draft.entry_type === 'tenant'; },
                showPayables() { return this.isPaidTenant() && !!this.draft.tenant_id; },
                async loadPayables() {
                    if (!this.showPayables()) { this.draft.payables = []; return; }
                    const draft = this.draft;
                    draft.payablesLoading = true;
                    try {
                        const params = new URLSearchParams({ tenant_id: draft.tenant_id });
                        // While editing, this voucher's own refunds still count as payable
                        if (voucherId) params.set('exclude_voucher_id', voucherId);
                        const res = await fetch(payablesUrl + '?' + params, { headers: { 'Accept': 'application/json' } });
                        const data = await res.json();
                        draft.payables = data.units || [];
                        if (draft.payables.length === 0) {
                            // Nothing payable: don't leave a previous amount behind
                            draft.unit_id = '';
                            draft.refund_label = '';
                            this.formatAmount('');
                        } else if (!draft.unit_id && draft.payables.length === 1) {
                            // A single payable unit is selected automatically
                            this.pickPayable(draft.payables[0]);
                        }
                    } catch (e) {
                        draft.payables = [];
                    } finally {
                        draft.payablesLoading = false;
                    }
                },
                pickPayable(u) {
                    const keepAmount = parseInt(this.draft.amount, 10) > 0 && String(this.draft.unit_id) === String(u.unit_id);
                    this.draft.unit_id = u.unit_id;
                    this.draft.refund_label = 'Unit ' + u.unit_number + ' security deposit refund';
                    if (!keepAmount) this.formatAmount(String(Math.round(u.pending)));
                },
                onTypeChange() {
                    this.draft = { ...newDraft(this.draft.entry_type), amount: this.draft.amount, _display: this.draft._display, notes: this.draft.notes };
                    this.updateAutoRemarks();
                },
                formatAmount(value) {
                    const digits = String(value).replace(/[^0-9]/g, '');
                    this.draft.amount = digits;
                    this.draft._display = digits ? parseInt(digits, 10).toLocaleString('en-US') : '';
                    this.updateAutoRemarks();
                },

                draftIsEmpty() { return !this.draft[this.fieldFor(this.draft.entry_type)] && !(parseInt(this.draft.amount, 10) > 0); },
                // Returns an error message, or '' when the draft entry is complete
                draftProblem() {
                    if (!this.draft[this.fieldFor(this.draft.entry_type)]) return 'Please select ' + this.entryTypes[this.draft.entry_type] + '.';
                    if (this.isPaidTenant() && !this.draft.unit_id) return 'Please select the unit whose security deposit is being refunded.';
                    if (!(parseInt(this.draft.amount, 10) > 0)) return 'Please enter an amount greater than zero.';
                    return '';
                },
                addEntry(silent = false) {
                    const problem = this.draftProblem();
                    if (problem) { if (!silent) warn('Entry Incomplete', problem); return false; }
                    const field = this.fieldFor(this.draft.entry_type);
                    this.lines.push({
                        _key: ++seq,
                        entry_type: this.draft.entry_type,
                        [field]: this.draft[field],
                        unit_id: this.isPaidTenant() ? this.draft.unit_id : (field === 'unit_id' ? this.draft.unit_id : ''),
                        refund_label: this.isPaidTenant() ? this.draft.refund_label : '',
                        amount: String(parseInt(this.draft.amount, 10)),
                        notes: this.draft.notes,
                        payment_ids: this.showDues() ? [...this.draft.payment_ids] : [],
                        payment_labels: this.showDues()
                            ? this.draft.dues.filter(p => this.draft.payment_ids.includes(String(p.id))).map(p => p.month + ' ' + p.type)
                            : [],
                        rv_id: this.draft.rv_id,
                    });
                    this.errors = {};
                    this.draft = newDraft(this.draft.entry_type);
                    this.updateAutoRemarks();
                    return true;
                },
                // A single-entry voucher (edit, or re-shown after a validation error) opens with
                // its entry in the form fields, exactly as it was filled in; several entries stay in the table.
                init() {
                    if (this.lines.length === 1) this.loadIntoDraft(0);
                },
                loadIntoDraft(i) {
                    const line = this.lines.splice(i, 1)[0];
                    const amount = String(parseInt(line.amount, 10) || '');
                    this.draft = { ...newDraft(line.entry_type), ...line, amount, _display: amount ? parseInt(amount, 10).toLocaleString('en-US') : '',
                        tenant_id: line.tenant_id ?? '', notes: line.notes ?? '',
                        unit_id: line.unit_id ?? '', refund_label: line.refund_label ?? '',
                        payment_ids: (line.payment_ids || []).map(String), dues: [], payables: [] };
                    this.loadDues();
                    this.loadPayables();
                },
                editEntry(i) {
                    if (!this.draftIsEmpty() && !confirm('Discard the entry currently in the form?')) return;
                    this.loadIntoDraft(i);
                    this.errors = {};
                    this.updateAutoRemarks();
                },
                removeEntry(i) { this.lines.splice(i, 1); this.errors = {}; this.updateAutoRemarks(); },
                total() { return this.lines.reduce((s, l) => s + (parseInt(l.amount, 10) || 0), 0); },
                rowError(i) {
                    return Object.entries(this.errors).filter(([k]) => k.startsWith(`lines.${i}.`)).map(([, v]) => v[0]).join(' ');
                },

                updateAutoRemarks() {
                    if (this.narrationEdited) return;
                    const all = [...this.lines, ...(this.draftIsEmpty() ? [] : [this.draft])];
                    const names = all.map(l => this.selectedLabel(l)).filter(Boolean);
                    const total = all.reduce((s, l) => s + (parseInt(l.amount, 10) || 0), 0);
                    if (!names.length && !total) { this.narration = ''; return; }
                    let text = this.isReceived ? 'Payment received' : 'Payment made';
                    if (total) text += ' of Rs. ' + total.toLocaleString('en-US');
                    if (names.length) text += (this.isReceived ? ' from ' : ' to ') + names.join(', ');
                    this.narration = text;
                },

                handleSubmit(event) {
                    if (!this.paymentAccountId) {
                        return warn('Account Required', 'Please select the ' + typeLabel.split(' ')[0].toLowerCase() + ' account.');
                    }
                    // A filled-in entry that wasn't added yet is added automatically (the usual single-entry case)
                    if (!this.draftIsEmpty() && !this.addEntry(true)) {
                        return warn('Entry Incomplete', this.draftProblem());
                    }
                    if (!this.lines.length) {
                        return warn('Entry Required', 'Please select ' + this.entryTypes[this.draft.entry_type] + ' and enter an amount.');
                    }

                    Swal.fire({
                        title: 'Confirm ' + typeLabel + ' Voucher',
                        text: 'Are you sure you want to ' + (isEdit ? 'update' : 'save') + ' this voucher'
                            + (this.lines.length > 1 ? ' with ' + this.lines.length + ' entries' : '')
                            + ' for Rs. ' + this.total().toLocaleString('en-US') + '?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: isEdit ? 'Yes, Update & Print' : 'Yes, Save & Print',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            confirmButton: swalBtn + ' mr-2',
                            cancelButton: 'inline-flex items-center justify-center rounded-xl bg-gray-200 dark:bg-gray-700 px-6 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-200 shadow-md hover:bg-gray-300 transition-colors cursor-pointer',
                        },
                        buttonsStyling: false,
                    }).then(result => {
                        // Wait for Alpine to render the hidden inputs of an auto-added entry
                        if (result.isConfirmed) this.$nextTick(() => event.target.submit());
                    });
                },
            };
        }
    </script>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#voucher_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    defaultDate: @js($dateValue),
                });
            }

            @if ($errors->any())
                Swal.fire({
                    title: 'Form Validation Error',
                    text: @js($errors->first()),
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                    },
                    buttonsStyling: false
                });
            @endif
        });
    </script>
@endpush
