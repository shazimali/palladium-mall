@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Settle JV Voucher — {{ $voucher->voucher_no }}" />

    {{-- Top navigation bar --}}
    <div class="mb-6 flex items-center justify-between no-print max-w-4xl mx-auto">
        <a href="{{ route('jv-vouchers.show', $voucher->id) }}"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-all">
            ← Back to Voucher
        </a>
    </div>

    <form id="settle-form" action="{{ route('jv-vouchers.pay', $voucher->id) }}" method="POST"
        @submit.prevent="handleSubmit($event)"
        x-data="{
            payAccountId: '{{ old('payment_account_id', '') }}',
            openPayAccount: false,
            searchPayAccount: '',
            highlightedIndex: -1,
            voucherAmount: {{ (float) $voucher->amount }},

            payAccountOptions: [
                @foreach($paymentAccounts as $account)
                { id: '{{ $account->id }}', name: '{{ addslashes($account->name) }}', balance: '{{ number_format($account->current_balance, 2) }}', rawBalance: {{ (float) $account->current_balance }} },
                @endforeach
            ],

            get filteredAccounts() {
                if (!this.searchPayAccount) return this.payAccountOptions;
                let s = this.searchPayAccount.toLowerCase();
                return this.payAccountOptions.filter(a => a.name.toLowerCase().includes(s));
            },

            get selectedAccount() {
                return this.payAccountOptions.find(a => a.id == this.payAccountId);
            },

            get selectedAccountName() {
                let sel = this.selectedAccount;
                return sel ? sel.name + ' (Available: Rs. ' + sel.balance + ')' : '';
            },

            selectAccount(opt) {
                this.payAccountId = opt.id;
                this.openPayAccount = false;
                this.searchPayAccount = '';
                this.highlightedIndex = -1;
                let errEl = document.getElementById('settle-account-error');
                if (errEl) errEl.style.display = 'none';
                let btn = document.getElementById('settle-account-btn');
                if (btn) btn.classList.remove('border-red-500');
            },

            handleSubmit(event) {
                let errEl = document.getElementById('settle-account-error');
                let errText = document.getElementById('settle-account-error-text');
                let btn = document.getElementById('settle-account-btn');

                // 1. Check if Payment Account selected
                if (!this.payAccountId) {
                    if (errText) errText.textContent = 'Payment Account is required. Please select a Bank or Cash account.';
                    if (errEl) errEl.style.display = 'flex';
                    if (btn) btn.classList.add('border-red-500');
                    btn?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                // 2. Check if selected account has sufficient balance
                let sel = this.selectedAccount;
                if (sel && sel.rawBalance < this.voucherAmount) {
                    if (errText) {
                        errText.textContent = 'Selected Payment Account (' + sel.name + ') has insufficient balance. Available: Rs. ' + sel.balance + ', Required: Rs. ' + this.voucherAmount.toLocaleString('en-US', {minimumFractionDigits: 2}) + '.';
                    }
                    if (errEl) errEl.style.display = 'flex';
                    if (btn) btn.classList.add('border-red-500');
                    btn?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                // Clear inline error
                if (errEl) errEl.style.display = 'none';
                if (btn) btn.classList.remove('border-red-500');

                // 3. Show SweetAlert confirm dialog
                let accountLabel = sel ? sel.name : 'Selected Account';
                Swal.fire({
                    title: 'Confirm Settlement?',
                    html: `<div style='text-align:left;font-size:15px;'>
                        <p style='margin-bottom:12px;'>You are about to mark this Journal Voucher as <strong>Paid / Settled</strong>.</p>
                        <table style='width:100%;border-collapse:collapse;'>
                            <tr><td style='padding:6px 0;color:#6b7280;font-weight:600;'>Voucher No:</td><td style='padding:6px 0;font-weight:900;color:#6d28d9;font-family:monospace;'>{{ $voucher->voucher_no }}</td></tr>
                            <tr><td style='padding:6px 0;color:#6b7280;font-weight:600;'>Amount:</td><td style='padding:6px 0;font-weight:900;color:#059669;font-family:monospace;'>Rs. {{ number_format($voucher->amount, 2) }}</td></tr>
                            <tr><td style='padding:6px 0;color:#6b7280;font-weight:600;'>Paid Via:</td><td style='padding:6px 0;font-weight:700;color:#111827;'>${accountLabel}</td></tr>
                        </table>
                        <p style='margin-top:14px;color:#b45309;font-weight:700;'>⚠️ This action cannot be undone.</p>
                    </div>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Confirm Settlement',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'inline-flex items-center justify-center rounded-xl bg-green-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-green-700 transition-colors cursor-pointer mr-2',
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

        {{-- FORM CONTAINER MATCHING JV VOUCHERS CREATE UI --}}
        <div class="mx-auto max-w-4xl bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm text-gray-900 dark:text-white font-sans relative">

            {{-- FORM HEADER WITH CENTERED TITLE & RIGHT CORNER VOUCHER NUMBER --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">
                <div class="hidden sm:block w-36"></div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-brand-600 dark:text-brand-400 uppercase text-center">
                    Settle Journal Voucher
                </h2>
                <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-2 shadow-2xs">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Voucher No:</span>
                    <span class="text-base sm:text-lg font-black font-mono text-brand-600 dark:text-brand-400">{{ $voucher->voucher_no }}</span>
                </div>
            </div>

            {{-- FULL-WIDTH FORM GRID CONTAINER --}}
            <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-visible mb-6 border border-gray-200 dark:border-gray-700">
                
                {{-- ROW 1: Voucher Date & Expense Category --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-40 rounded-t-2xl">
                    {{-- Field 1: Voucher Date --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide rounded-tl-2xl">Voucher Date</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-base sm:text-lg">
                            {{ $voucher->date ? $voucher->date->format('d M Y') : '—' }}
                        </div>
                    </div>

                    {{-- Field 2: Expense Category --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">Category</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-base sm:text-lg md:rounded-tr-2xl">
                            {{ $voucher->expenseHead->name ?? 'Uncategorized' }}
                        </div>
                    </div>
                </div>

                {{-- ROW 2: Payment Amount & Voucher Status --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-30">
                    {{-- Field 3: Payment Amount --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">Amount to Settle</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-xl sm:text-2xl font-mono text-emerald-600 dark:text-emerald-400">
                            Rs. {{ number_format($voucher->amount, 2) }}
                        </div>
                    </div>

                    {{-- Field 4: Voucher Status Indicator --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">Settlement Status</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 dark:bg-green-900/40 px-3 py-1 text-xs sm:text-sm font-black text-green-800 dark:text-green-300">
                                ● Settling as Paid
                            </span>
                        </div>
                    </div>
                </div>

                {{-- ROW 3: SETTLEMENT ACCOUNT & PAYMENT METHOD (Separated) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-20">
                    {{-- Field 5: Paid From Payment Account (Searchable Dropdown) --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">Paid From <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex flex-col justify-center">
                            <div class="w-full relative" @click.away="openPayAccount = false; highlightedIndex = -1">
                                <input type="hidden" name="payment_account_id" x-model="payAccountId" required>
                                <button type="button" id="settle-account-btn"
                                    @click="openPayAccount = !openPayAccount; if(openPayAccount) { $nextTick(() => $refs.payAccountSearchInput.focus()) }"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                    <span x-text="selectedAccountName ? selectedAccountName : 'Select Payment Account...'" class="truncate"></span>
                                    <span class="ml-2 text-xs opacity-60">▼</span>
                                </button>

                                <div x-show="openPayAccount" x-transition x-cloak
                                    class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                    <div class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <input type="text" x-ref="payAccountSearchInput" x-model="searchPayAccount" placeholder="Type account name to search..."
                                            @keydown.arrow-down.prevent="highlightedIndex = (highlightedIndex + 1) % filteredAccounts.length"
                                            @keydown.arrow-up.prevent="highlightedIndex = (highlightedIndex - 1 + filteredAccounts.length) % filteredAccounts.length"
                                            @keydown.enter.prevent="if(highlightedIndex >= 0 && filteredAccounts[highlightedIndex]) selectAccount(filteredAccounts[highlightedIndex])"
                                            @keydown.escape="openPayAccount = false; highlightedIndex = -1"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2 text-base font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto p-1.5 space-y-1 text-sm">
                                        <template x-for="(opt, index) in filteredAccounts" :key="opt.id">
                                            <button type="button" @click="selectAccount(opt)"
                                                @mouseenter="highlightedIndex = index"
                                                class="w-full text-left px-3.5 py-2.5 rounded-xl transition-colors flex items-center justify-between"
                                                :class="payAccountId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                                <div>
                                                    <span x-text="opt.name" class="font-black text-base sm:text-lg block truncate"></span>
                                                    <span x-text="'Available: Rs. ' + opt.balance" class="text-xs opacity-75 font-mono"></span>
                                                </div>
                                                <span x-show="payAccountId == opt.id" class="font-black text-base ml-2">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="filteredAccounts.length === 0" class="px-4 py-3 text-center text-xs font-semibold text-gray-400">
                                            No matching Account found
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p id="settle-account-error" style="display:none;" class="mt-1.5 text-xs font-semibold text-red-600 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                <span id="settle-account-error-text">Payment Account is required. Please select a Bank or Cash account.</span>
                            </p>
                        </div>
                    </div>

                    {{-- Field 6: Payment Method (Separate) --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">Payment Method <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <select name="payment_method" required
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Bank Transfer" {{ old('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="Cheque" {{ old('payment_method') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="Online" {{ old('payment_method') == 'Online' ? 'selected' : '' }}>Online</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ROW 4: PAID DATE (DATE PICKER) & REFERENCE # --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-10 rounded-b-2xl">
                    {{-- Field 7: Paid Date (with Flatpickr Date Picker) --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide rounded-bl-2xl">Paid Date <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <input type="text" id="paid_date" name="paid_date" value="{{ old('paid_date', now()->toDateString()) }}" required autocomplete="off"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all cursor-pointer">
                        </div>
                    </div>

                    {{-- Field 8: Reference / Cheque # --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">Reference #</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center rounded-br-2xl">
                            <input type="text" name="reference" value="{{ old('reference', $voucher->reference) }}" placeholder="Optional cheque / ref #"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-sm sm:text-base font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>
                    </div>
                </div>

            </div>

            {{-- ROW 5 / BOTTOM SECTION: Settled by & Voucher Remarks (Matching Create UI) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch mb-6">
                {{-- Left Box: Settled by Box --}}
                <div class="bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl p-4 flex flex-col justify-center border border-gray-200 dark:border-gray-700 shadow-xs">
                    <p class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                        Settled by: <span class="text-brand-600 dark:text-brand-400 font-extrabold ml-1">{{ auth()->user()->name }}</span>
                    </p>
                </div>

                {{-- Right Box: Voucher Remarks --}}
                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 shadow-xs">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                        Voucher Remarks / Notes:
                    </label>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 whitespace-pre-line">
                        {{ $voucher->notes ?? 'No remarks provided on creation.' }}
                    </p>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('jv-vouchers.show', $voucher->id) }}"
                    class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold transition-all text-xs sm:text-sm">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-7 py-3 rounded-xl bg-green-600 hover:bg-green-700 text-white font-black text-xs sm:text-sm shadow-md transition-all cursor-pointer">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Confirm Settlement
                </button>
            </div>

        </div>

    </form>
@endsection

@push('scripts')
<style>.swal2-container { z-index: 9999999 !important; }</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#paid_date', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                allowInput: true,
                disableMobile: true,
                defaultDate: '{{ old('paid_date', now()->toDateString()) }}'
            });
        }

        @if ($errors->any() || session('error'))
            Swal.fire({
                title: 'Settlement Error',
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
