@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Expense Voucher — {{ $expense->voucher_no }}" />

    <form action="{{ route('expenses.update', $expense) }}" method="POST" enctype="multipart/form-data"
        @submit.prevent="handleSubmit($event)"
        x-data="{
            expenseHeadId: '{{ old('expense_head_id', $expense->expense_head_id) }}',
            paymentAccountId: '{{ old('payment_account_id', $expense->payment_account_id) }}',
            paymentMethod: '{{ old('payment_method', $expense->payment_method) }}',
            voucherAmount: '{{ old('amount', $expense->amount) }}',
            displayAmount: '',

            openExpenseHead: false,
            searchExpenseHead: '',
            highlightedExpenseHeadIndex: -1,

            expenseHeadOptions: [
                @foreach($expenseHeads as $h)
                { id: '{{ $h->id }}', name: '{{ addslashes($h->name) }}' },
                @endforeach
            ],

            accountOptions: [
                @foreach($paymentAccounts as $account)
                { id: '{{ $account->id }}', name: '{{ addslashes($account->name) }} ({{ ucfirst($account->type) }})' },
                @endforeach
            ],

            get filteredExpenseHeads() {
                if (!this.searchExpenseHead) return this.expenseHeadOptions;
                let s = this.searchExpenseHead.toLowerCase();
                return this.expenseHeadOptions.filter(h => h.name.toLowerCase().includes(s));
            },
            get selectedExpenseHeadName() {
                let selected = this.expenseHeadOptions.find(h => h.id == this.expenseHeadId);
                return selected ? selected.name : '';
            },

            selectExpenseHead(opt) {
                this.expenseHeadId = opt.id;
                this.openExpenseHead = false;
                this.searchExpenseHead = '';
                this.highlightedExpenseHeadIndex = -1;
            },

            init() {
                if (this.voucherAmount) {
                    this.formatAmount(String(this.voucherAmount));
                }
            },

            formatAmount(val) {
                let clean = val.replace(/[^\d.]/g, '');
                let parts = clean.split('.');
                if (parts.length > 2) {
                    parts = [parts[0], parts.slice(1).join('')];
                }
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                this.displayAmount = parts.join('.');
                this.voucherAmount = clean ? parseFloat(clean) : '';
            },

            handleSubmit(event) {
                if (!this.expenseHeadId) {
                    Swal.fire({
                        title: 'Expense Head Required',
                        text: 'Please select an Expense Category / Head.',
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

                if (!this.paymentAccountId) {
                    Swal.fire({
                        title: 'Payment Account Required',
                        text: 'Please select a Paid From Payment Account.',
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
                    title: 'Confirm Update Expense Voucher',
                    text: 'Are you sure you want to update and print this expense voucher for Rs. ' + amt.toLocaleString('en-US', {minimumFractionDigits: 2}) + '?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Update & Print',
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

        {{-- FORM CONTAINER WITH NO OUTER BG COLOR, CENTERED TITLE, ADAPTED TO THEME --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm text-gray-900 dark:text-white font-sans relative">

            {{-- FORM HEADER WITH CENTERED TITLE & RIGHT CORNER VOUCHER NUMBER --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">
                <div class="hidden sm:block w-36"></div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-brand-600 dark:text-brand-400 uppercase text-center">
                    Expense Voucher
                </h2>
                <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-2 shadow-2xs">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Voucher No:</span>
                    <span class="text-base sm:text-lg font-black font-mono text-brand-600 dark:text-brand-400">{{ $expense->voucher_no }}</span>
                </div>
            </div>

            {{-- FORM GRID CONTAINER --}}
            <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-visible mb-6 border border-gray-200 dark:border-gray-700">
                
                {{-- ROW 1: Voucher Date & Expense Category --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-50 rounded-t-2xl">
                    {{-- Field 1: Voucher Date --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide rounded-tl-2xl">Voucher Date</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <input type="text" id="voucher_date" name="date" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required autocomplete="off"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-sm font-extrabold text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all cursor-pointer">
                        </div>
                    </div>

                    {{-- Field 2: Searchable Expense Category Dropdown --}}
                    <div class="grid grid-cols-3 min-h-[52px] relative z-50">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide md:rounded-tr-2xl">Expense Head <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center md:rounded-tr-2xl">
                            <div class="w-full relative" @click.away="openExpenseHead = false; highlightedExpenseHeadIndex = -1">
                                <input type="hidden" name="expense_head_id" x-model="expenseHeadId" required>
                                <button type="button" @click="openExpenseHead = !openExpenseHead; if(openExpenseHead) { $nextTick(() => $refs.expenseHeadSearchInput.focus()) }"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-sm font-extrabold text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                    <span x-text="selectedExpenseHeadName ? selectedExpenseHeadName : 'Select Expense Category...'" class="truncate"></span>
                                    <span class="ml-2 text-xs opacity-60">▼</span>
                                </button>

                                <div x-show="openExpenseHead" x-transition x-cloak
                                    class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                    <div class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <input type="text" x-ref="expenseHeadSearchInput" x-model="searchExpenseHead" placeholder="Type expense category to search..."
                                            @keydown.arrow-down.prevent="highlightedExpenseHeadIndex = (highlightedExpenseHeadIndex + 1) % filteredExpenseHeads.length"
                                            @keydown.arrow-up.prevent="highlightedExpenseHeadIndex = (highlightedExpenseHeadIndex - 1 + filteredExpenseHeads.length) % filteredExpenseHeads.length"
                                            @keydown.enter.prevent="if(highlightedExpenseHeadIndex >= 0 && filteredExpenseHeads[highlightedExpenseHeadIndex]) selectExpenseHead(filteredExpenseHeads[highlightedExpenseHeadIndex])"
                                            @keydown.escape="openExpenseHead = false; highlightedExpenseHeadIndex = -1"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2 text-sm text-gray-900 dark:text-white font-bold focus:border-brand-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto p-1.5 space-y-1 text-sm">
                                        <template x-for="(opt, index) in filteredExpenseHeads" :key="opt.id">
                                            <button type="button" @click="selectExpenseHead(opt)"
                                                @mouseenter="highlightedExpenseHeadIndex = index"
                                                class="w-full text-left px-3.5 py-2.5 rounded-xl transition-colors flex items-center justify-between"
                                                :class="expenseHeadId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedExpenseHeadIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                                <span x-text="opt.name" class="font-black text-sm truncate"></span>
                                                <span x-show="expenseHeadId == opt.id" class="font-black text-base">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="filteredExpenseHeads.length === 0" class="px-4 py-3 text-center text-xs font-semibold text-gray-400">
                                            No matching Category found
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ROW 2: Voucher Number & Paid From Account --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-20">
                    {{-- Field 1: Voucher Number --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Voucher No.</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-brand-600 dark:text-brand-400 px-4 py-3 flex items-center font-black text-base sm:text-lg font-mono">
                            {{ $expense->voucher_no }}
                        </div>
                    </div>

                    {{-- Field 2: Paid From Account --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Paid From <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <select name="payment_account_id" x-model="paymentAccountId" required
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-sm font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                <option value="">Select Account...</option>
                                @foreach($paymentAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('payment_account_id', $expense->payment_account_id) == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} ({{ ucfirst($account->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ROW 3: Payment Amount & Payment Method Type --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-10 rounded-b-2xl">
                    {{-- Field 1: Payment Amount --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide rounded-bl-2xl md:rounded-bl-none">Payment Amount <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <input type="text" x-model="displayAmount" @input="formatAmount($event.target.value)" required placeholder="0.00"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            <input type="hidden" name="amount" x-model="voucherAmount">
                        </div>
                    </div>

                    {{-- Field 2: Payment Method Type --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Payment Method</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center md:rounded-br-2xl">
                            <select name="payment_method" x-model="paymentMethod" required
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-sm font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Online">Online</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Card">Card</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ROW 4 / BOTTOM SECTION: Approved by & Description --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">
                {{-- Left Box: Approved by Box --}}
                <div class="bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl p-4 flex flex-col justify-center border border-gray-200 dark:border-gray-700 shadow-xs">
                    <p class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                        Approved by: <span class="text-brand-600 dark:text-brand-400 font-extrabold ml-1">{{ $expense->user->name ?? auth()->user()->name }}</span>
                    </p>
                </div>

                {{-- Right Box: Remarks --}}
                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 shadow-xs">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                        Remarks:
                    </label>
                    <textarea name="notes" rows="2" placeholder="Expense voucher remarks or notes..."
                        class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl p-3.5 text-base sm:text-lg font-black text-gray-900 dark:text-white placeholder-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all">{{ old('notes', $expense->notes) }}</textarea>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('expenses.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold transition-all text-xs sm:text-sm">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-black text-xs sm:text-sm shadow-md transition-all cursor-pointer">
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
                    defaultDate: '{{ old('date', $expense->date->format('Y-m-d')) }}'
                });
            }

            @if ($errors->any())
                Swal.fire({
                    title: 'Form Validation Error',
                    text: "{{ $errors->first() }}",
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
