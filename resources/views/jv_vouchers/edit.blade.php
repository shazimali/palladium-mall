@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit JV Voucher" />

    <div class="mb-6 flex items-center justify-between no-print max-w-4xl mx-auto">
        <a href="{{ route('jv-vouchers.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-all">
            ← Back to List
        </a>
    </div>

    <form action="{{ route('jv-vouchers.update', $voucher->id) }}" method="POST" enctype="multipart/form-data"
        @submit.prevent="handleSubmit($event)" x-data="{
                status: '{{ old('status', $voucher->status) }}',
                expenseHeadId: '{{ old('expense_head_id', $voucher->expense_head_id) }}',
                voucherAmount: '{{ old('amount', $voucher->amount) }}',
                displayAmount: '{{ number_format((float) old('amount', $voucher->amount), 2) }}',

                isNotesManuallyEdited: {{ old('notes', $voucher->notes) ? 'true' : 'false' }},
                notes: @js(old('notes', $voucher->notes ?? '')),

                openCategory: false,
                searchCategory: '',
                highlightedCategoryIndex: -1,

                categoryOptions: [
                    @foreach($expenseHeads as $head)
                        { id: '{{ $head->id }}', name: '{{ addslashes($head->name) }}' },
                    @endforeach
                ],

                get filteredCategories() {
                    if (!this.searchCategory) return this.categoryOptions;
                    let s = this.searchCategory.toLowerCase();
                    return this.categoryOptions.filter(c => c.name.toLowerCase().includes(s));
                },

                get selectedCategoryName() {
                    let selected = this.categoryOptions.find(c => c.id == this.expenseHeadId);
                    return selected ? selected.name : '';
                },

                selectCategory(opt) {
                    this.expenseHeadId = opt.id;
                    this.openCategory = false;
                    this.searchCategory = '';
                    this.highlightedCategoryIndex = -1;
                    this.updateAutoRemarks();
                },

                updateAutoRemarks() {
                    if (this.isNotesManuallyEdited) return;

                    let categoryName = this.selectedCategoryName;
                    let amtNum = parseFloat(this.voucherAmount || 0);
                    let amtStr = amtNum > 0 ? 'Rs. ' + Math.round(amtNum).toLocaleString() : '';
                    let statusStr = this.status === 'paid' ? 'Paid' : 'Unpaid';

                    if (!amtStr && !categoryName) {
                        this.notes = '';
                        return;
                    }

                    let remarks = 'JV Voucher';
                    if (amtStr) remarks += ' of ' + amtStr;
                    if (categoryName) remarks += ' for ' + categoryName;
                    this.notes = remarks;
                },

                formatAmount(val) {
                    let clean = String(val).replace(/[^0-9.]/g, '');
                    let parts = clean.split('.');
                    if (parts.length > 2) clean = parts[0] + '.' + parts.slice(1).join('');
                    this.voucherAmount = clean;
                    if (!clean) {
                        this.displayAmount = '';
                        this.updateAutoRemarks();
                        return;
                    }
                    let num = parseFloat(clean);
                    if (isNaN(num)) return;
                    if (clean.endsWith('.')) {
                        this.displayAmount = num.toLocaleString('en-US') + '.';
                    } else if (parts.length === 2 && parts[1].length > 0) {
                        this.displayAmount = parseFloat(parts[0]).toLocaleString('en-US') + '.' + parts[1].substring(0, 2);
                    } else {
                        this.displayAmount = num.toLocaleString('en-US');
                    }
                    this.updateAutoRemarks();
                },

                init() {
                    this.$watch('status', () => this.updateAutoRemarks());
                    this.$watch('expenseHeadId', () => this.updateAutoRemarks());
                    this.$watch('voucherAmount', () => this.updateAutoRemarks());
                    if (!this.isNotesManuallyEdited) {
                        this.updateAutoRemarks();
                    }
                },

                handleSubmit(event) {
                    if (!this.expenseHeadId) {
                        Swal.fire({
                            title: 'Category Required',
                            text: 'Please select an Expense Category.',
                            icon: 'warning',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                            },
                            buttonsStyling: false
                        });
                        return;
                    }

                    let amt = parseFloat(this.voucherAmount || 0);
                    if (isNaN(amt) || amt <= 0) {
                        Swal.fire({
                            title: 'Invalid Amount',
                            text: 'Please enter a valid voucher amount greater than zero.',
                            icon: 'warning',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                            },
                            buttonsStyling: false
                        });
                        return;
                    }

                    if (this.status === 'paid' && !document.querySelector('[name=payment_account_id]').value) {
                        Swal.fire({
                            title: 'Payment Account Required',
                            text: 'Please select a Payment Account for settled voucher.',
                            icon: 'warning',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                            },
                            buttonsStyling: false
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Confirm Update JV Voucher',
                        text: 'Are you sure you want to update this Journal Voucher?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Update Voucher',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer mr-2',
                            cancelButton: 'inline-flex items-center justify-center rounded-xl bg-gray-200 dark:bg-gray-700 px-6 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-200 shadow-md hover:bg-gray-300 transition-colors cursor-pointer'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            event.target.submit();
                        }
                    });
                }
            }">

        @csrf
        @method('PUT')

        {{-- FORM CONTAINER MATCHING PAYMENT VOUCHERS CREATE UI --}}
        <div
            class="mx-auto max-w-4xl bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm text-gray-900 dark:text-white font-sans relative">

            {{-- FORM HEADER WITH CENTERED TITLE & RIGHT CORNER VOUCHER NUMBER --}}
            <div
                class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">
                <div class="hidden sm:block w-36"></div>
                <h2
                    class="text-2xl sm:text-3xl font-black tracking-tight text-brand-600 dark:text-brand-400 uppercase text-center">
                    Edit Journal Voucher
                </h2>
                <div
                    class="inline-flex items-center gap-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-2 shadow-2xs">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Voucher
                        No:</span>
                    <span
                        class="text-base sm:text-lg font-black font-mono text-brand-600 dark:text-brand-400">{{ $voucher->voucher_no }}</span>
                </div>
            </div>

            {{-- FULL-WIDTH FORM GRID CONTAINER --}}
            <div
                class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-visible mb-6 border border-gray-200 dark:border-gray-700">

                {{-- ROW 1: Voucher Date & Searchable Expense Category --}}
                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-40 rounded-t-2xl">
                    {{-- Field 1: Voucher Date --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div
                            class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide rounded-tl-2xl">
                            Voucher Date <span class="text-rose-300 ml-1">*</span></div>
                        <div
                            class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <input type="text" id="voucher_date" name="date"
                                value="{{ old('date', $voucher->date ? $voucher->date->format('Y-m-d') : '') }}" required
                                autocomplete="off"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all cursor-pointer">
                        </div>
                    </div>

                    {{-- Field 2: Searchable Expense Category Dropdown --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div
                            class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">
                            Category <span class="text-rose-300 ml-1">*</span></div>
                        <div
                            class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center md:rounded-tr-2xl">
                            <div class="w-full relative" @click.away="openCategory = false; highlightedCategoryIndex = -1">
                                <input type="hidden" name="expense_head_id" x-model="expenseHeadId" required>
                                <button type="button"
                                    @click="openCategory = !openCategory; if(openCategory) { $nextTick(() => $refs.categorySearchInput.focus()) }"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                    <span x-text="selectedCategoryName ? selectedCategoryName : 'Select Category...'"
                                        class="truncate"></span>
                                    <span class="ml-2 text-xs opacity-60">▼</span>
                                </button>

                                <div x-show="openCategory" x-transition x-cloak
                                    class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                    <div
                                        class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <input type="text" x-ref="categorySearchInput" x-model="searchCategory"
                                            placeholder="Type category name to search..."
                                            @keydown.arrow-down.prevent="highlightedCategoryIndex = (highlightedCategoryIndex + 1) % filteredCategories.length"
                                            @keydown.arrow-up.prevent="highlightedCategoryIndex = (highlightedCategoryIndex - 1 + filteredCategories.length) % filteredCategories.length"
                                            @keydown.enter.prevent="if(highlightedCategoryIndex >= 0 && filteredCategories[highlightedCategoryIndex]) selectCategory(filteredCategories[highlightedCategoryIndex])"
                                            @keydown.escape="openCategory = false; highlightedCategoryIndex = -1"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2 text-base font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto p-1.5 space-y-1 text-sm">
                                        <template x-for="(opt, index) in filteredCategories" :key="opt.id">
                                            <button type="button" @click="selectCategory(opt)"
                                                @mouseenter="highlightedCategoryIndex = index"
                                                class="w-full text-left px-3.5 py-2.5 rounded-xl transition-colors flex items-center justify-between"
                                                :class="expenseHeadId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedCategoryIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                                <span x-text="opt.name"
                                                    class="font-black text-base sm:text-lg truncate"></span>
                                                <span x-show="expenseHeadId == opt.id" class="font-black text-base">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="filteredCategories.length === 0"
                                            class="px-4 py-3 text-center text-xs font-semibold text-gray-400">
                                            No matching Category found
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ROW 2: Payment Amount & Voucher Status --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-30">
                    {{-- Field 3: Payment Amount --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div
                            class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">
                            Payment Amount <span class="text-rose-300 ml-1">*</span></div>
                        <div
                            class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <input type="text" x-model="displayAmount" @input="formatAmount($event.target.value)" required
                                placeholder="0.00"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-lg sm:text-xl font-black font-mono text-emerald-600 dark:text-emerald-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            <input type="hidden" name="amount" x-model="voucherAmount">
                        </div>
                    </div>

                    {{-- Field 4: Voucher Status Selector --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div
                            class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">
                            Voucher Status <span class="text-rose-300 ml-1">*</span></div>
                        <div
                            class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-xs sm:text-sm">
                                <input type="radio" name="status" value="unpaid" x-model="status"
                                    class="text-amber-600 focus:ring-amber-500">
                                <span
                                    :class="status === 'unpaid' ? 'text-amber-600 dark:text-amber-400 font-black' : 'text-gray-700 dark:text-gray-300'">Unpaid
                                    (Accrued)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer font-bold text-xs sm:text-sm">
                                <input type="radio" name="status" value="paid" x-model="status"
                                    class="text-green-600 focus:ring-green-500">
                                <span
                                    :class="status === 'paid' ? 'text-green-600 dark:text-green-400 font-black' : 'text-gray-700 dark:text-gray-300'">Paid
                                    (Settled)</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- ROW 3: SETTLEMENT ACCOUNT & METHOD (Conditional based on Status) --}}
                <div x-show="status === 'paid'" x-cloak
                    class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-20">
                    {{-- Field 5: Paid From Payment Account --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div
                            class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">
                            Paid From <span class="text-rose-300 ml-1">*</span></div>
                        <div
                            class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <select name="payment_account_id" :required="status === 'paid'"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                <option value="">Select Account...</option>
                                @foreach($paymentAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('payment_account_id', $voucher->payment_account_id) == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} (Available: Rs. {{ number_format($account->current_balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Field 6: Payment Method & Paid Date --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div
                            class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">
                            Method & Date <span class="text-rose-300 ml-1">*</span></div>
                        <div
                            class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center gap-2">
                            <select name="payment_method" :required="status === 'paid'"
                                class="w-1/2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-2 py-2 text-xs sm:text-sm font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                <option value="Cash" {{ old('payment_method', $voucher->payment_method ?? 'Cash') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Bank Transfer" {{ old('payment_method', $voucher->payment_method) == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="Cheque" {{ old('payment_method', $voucher->payment_method) == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="Online" {{ old('payment_method', $voucher->payment_method) == 'Online' ? 'selected' : '' }}>Online</option>
                            </select>
                            <input type="date" name="paid_date"
                                value="{{ old('paid_date', $voucher->paid_date ? $voucher->paid_date->format('Y-m-d') : date('Y-m-d')) }}"
                                :required="status === 'paid'"
                                class="w-1/2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-2 py-2 text-xs sm:text-sm font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- ROW 4: Reference # & Attach Receipt Document --}}
                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-10 rounded-b-2xl">
                    {{-- Field 7: Reference / Bill # --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div
                            class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide rounded-bl-2xl">
                            Reference #</div>
                        <div
                            class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <input type="text" name="reference" value="{{ old('reference', $voucher->reference) }}"
                                placeholder="Optional bill / ref #"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-sm sm:text-base font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>
                    </div>

                    {{-- Field 8: Attach Receipt --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div
                            class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">
                            Attach Receipt</div>
                        <div
                            class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex flex-col justify-center rounded-br-2xl">
                            <input type="file" name="receipt" accept="image/*,.pdf"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-1.5 text-xs font-semibold text-gray-800 dark:text-white">
                            @if($voucher->receipt)
                                <span class="mt-1 text-[11px] font-bold text-gray-500">
                                    Current file: <a href="{{ $voucher->receipt_url }}" target="_blank"
                                        class="text-brand-600 dark:text-brand-400 underline">View attachment</a>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

            {{-- ROW 5 / BOTTOM SECTION: Approved by & Description --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">
                {{-- Left Box: Approved by Box --}}
                <div
                    class="bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl p-4 flex flex-col justify-center border border-gray-200 dark:border-gray-700 shadow-xs">
                    <p class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                        Approved by: <span
                            class="text-brand-600 dark:text-brand-400 font-extrabold ml-1">{{ auth()->user()->name }}</span>
                    </p>
                </div>

                {{-- Right Box: Remarks --}}
                <div
                    class="md:col-span-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 shadow-xs">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                        Remarks:
                    </label>
                    <textarea name="notes" x-model="notes" @input="isNotesManuallyEdited = true" rows="2"
                        placeholder="Auto-generated remarks or enter notes..."
                        class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl p-3.5 text-base sm:text-lg font-black text-gray-900 dark:text-white placeholder-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all"></textarea>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('jv-vouchers.index') }}"
                    class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold transition-all text-xs sm:text-sm">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-black text-xs sm:text-sm shadow-md transition-all cursor-pointer">
                    Update Voucher
                </button>
            </div>

        </div>

    </form>
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
                    defaultDate: '{{ old('date', $voucher->date ? $voucher->date->format('Y-m-d') : '') }}'
                });
            }

            @if ($errors->any() || session('error'))
                Swal.fire({
                    title: 'Form Error',
                    text: "{{ $errors->first() ?: session('error') }}",
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