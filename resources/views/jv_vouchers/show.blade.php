@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="JV Voucher Details — {{ $voucher->voucher_no }}" />

    <div x-data="{ 
        payModalOpen: {{ $errors->has('payment_account_id') ? 'true' : 'false' }},
        payAccountId: '',
        openPayAccount: false,
        searchPayAccount: '',
        highlightedPayAccountIndex: -1,

        payAccountOptions: [
            @foreach($paymentAccounts as $account)
            { id: '{{ $account->id }}', name: '{{ addslashes($account->name) }} (Available: Rs. {{ number_format($account->current_balance, 2) }})' },
            @endforeach
        ],

        get filteredPayAccounts() {
            if (!this.searchPayAccount) return this.payAccountOptions;
            let s = this.searchPayAccount.toLowerCase();
            return this.payAccountOptions.filter(a => a.name.toLowerCase().includes(s));
        },

        get selectedPayAccountName() {
            let selected = this.payAccountOptions.find(a => a.id == this.payAccountId);
            return selected ? selected.name : '';
        },

        selectPayAccount(opt) {
            this.payAccountId = opt.id;
            this.openPayAccount = false;
            this.searchPayAccount = '';
            this.highlightedPayAccountIndex = -1;
        }
    }">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 no-print max-w-4xl mx-auto">
            <div class="flex items-center gap-2">
                <a href="{{ route('jv-vouchers.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-all">
                    ← Back to List
                </a>
            </div>
            <div class="flex items-center gap-2">
                @if($voucher->status === 'unpaid' && (auth()->user()->hasPermission('jv_vouchers.pay') || auth()->user()->isSuperAdmin()))
                    <a href="{{ route('jv-vouchers.settle', $voucher->id) }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-green-700 transition-all shadow-md">
                        ✅ Mark as Paid
                    </a>
                @endif

                @if(auth()->user()->hasPermission('jv_vouchers.edit') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('jv-vouchers.edit', $voucher->id) }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700 transition-all shadow-md">
                        ✏️ Edit Voucher
                    </a>
                @endif

                <a href="{{ route('jv-vouchers.print', $voucher->id) }}" target="_blank"
                    class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-gray-800 transition-all shadow-md">
                    🖨️ Print Voucher
                </a>
            </div>
        </div>

        {{-- REFINED VOUCHER CARD MATCHING RECEIVING VOUCHER --}}
        <div class="mx-auto max-w-4xl bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm text-gray-900 dark:text-white font-sans">

            {{-- FORM HEADER WITH CENTERED TITLE & RIGHT CORNER VOUCHER NUMBER --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">
                <div class="hidden sm:block w-36"></div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-brand-600 dark:text-brand-400 uppercase text-center">
                    Journal Voucher (JV)
                </h2>
                <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-2 shadow-2xs">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Voucher No:</span>
                    <span class="text-base sm:text-lg font-black font-mono text-brand-600 dark:text-brand-400">{{ $voucher->voucher_no }}</span>
                </div>
            </div>

            {{-- 2-COLUMN SIDE-BY-SIDE FORM GRID LAYOUT --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6 items-start">
                
                {{-- LEFT COLUMN: Voucher Date, Expense Head, Amount, Status --}}
                <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    
                    {{-- Field 1: Voucher Date --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Voucher Date</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-base sm:text-lg">
                            {{ $voucher->date ? $voucher->date->format('M. d, Y') : '—' }}
                        </div>
                    </div>

                    {{-- Field 2: Expense Category --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Category</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-base sm:text-lg">
                            {{ $voucher->expenseHead->name ?? 'Uncategorized' }}
                        </div>
                    </div>

                    {{-- Field 3: Payment Amount --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Amount</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-lg sm:text-xl font-mono text-emerald-600 dark:text-emerald-400">
                            Rs. {{ number_format($voucher->amount, 2) }}
                        </div>
                    </div>

                    {{-- Field 4: Voucher Status --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Status</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base">
                            @if($voucher->status === 'paid')
                                <span class="text-green-600 dark:text-green-400 font-bold uppercase">● Paid (Settled)</span>
                            @else
                                <span class="text-amber-600 dark:text-amber-400 font-bold uppercase">● Unpaid (Accrued Expense)</span>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN: Settlement Account, Paid Date, Reference, Recorded By --}}
                <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    
                    {{-- Field 5: Settlement Account --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Payment Acc.</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base">
                            {{ $voucher->status === 'paid' ? ($voucher->paymentAccount->name ?? '—') : 'Not Settled Yet' }}
                        </div>
                    </div>

                    {{-- Field 6: Settlement Date / Method --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Paid Date</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base">
                            {{ $voucher->status === 'paid' && $voucher->paid_date ? $voucher->paid_date->format('M. d, Y') . ' (' . ($voucher->payment_method ?? 'Cash') . ')' : '—' }}
                        </div>
                    </div>

                    {{-- Field 7: Reference / Bill # --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Reference #</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-semibold text-sm">
                            {{ $voucher->reference ?? '—' }}
                        </div>
                    </div>

                    {{-- Field 8: User --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Prepared By</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-semibold text-sm">
                            {{ $voucher->user->name ?? 'System' }}
                        </div>
                    </div>

                </div>

            </div>

            {{-- REMARKS / NOTES SECTION --}}
            <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 mb-6">
                <span class="block text-xs uppercase font-bold text-gray-500 dark:text-gray-400 mb-1">Description / Remarks:</span>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 whitespace-pre-line">
                    {{ $voucher->notes ?? 'No specific remarks entered.' }}
                </p>
            </div>

            @if($voucher->receipt)
                <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 mb-6 flex items-center justify-between">
                    <div>
                        <span class="block text-xs uppercase font-bold text-gray-500 dark:text-gray-400">Attached Receipt Document</span>
                        <span class="text-xs text-gray-400">Uploaded file attachment</span>
                    </div>
                    <a href="{{ $voucher->receipt_url }}" target="_blank"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white hover:bg-brand-600 shadow-sm transition-all">
                        📄 View Document &rarr;
                    </a>
                </div>
            @endif

            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-800 text-xs text-gray-400">
                <span>Created at: {{ $voucher->created_at ? $voucher->created_at->format('d M Y h:i A') : '—' }}</span>
                <a href="{{ route('jv-vouchers.index') }}" class="font-bold text-brand-600 dark:text-brand-400 hover:underline">&larr; Back to JV Vouchers</a>
            </div>
        </div>

        {{-- Mark as Paid Modal (Big Font & Searchable Payment Account) --}}
        @if($voucher->status === 'unpaid')
            <div x-show="payModalOpen" x-cloak
                class="fixed inset-0 z-99999 flex items-center justify-center bg-black/50 p-4">
                <div @click.away="payModalOpen = false"
                    class="w-full max-w-lg rounded-3xl bg-white p-6 sm:p-8 shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-900 dark:text-white">
                    
                    <div class="flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-800">
                        <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                            Settle JV Voucher: <span class="text-brand-600 dark:text-brand-400 font-mono">{{ $voucher->voucher_no }}</span>
                        </h3>
                        <button @click="payModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl font-bold cursor-pointer">
                            &times;
                        </button>
                    </div>

                    <form action="{{ route('jv-vouchers.pay', $voucher->id) }}" method="POST" class="mt-5 space-y-5">
                        @csrf

                        @if($errors->has('payment_account_id'))
                            <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                                <svg class="h-4 w-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                <span>{{ $errors->first('payment_account_id') }}</span>
                            </div>
                        @endif
                        
                        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 flex items-center justify-between">
                            <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Amount to Settle</span>
                            <span class="font-black text-xl sm:text-2xl text-emerald-600 dark:text-emerald-400 font-mono">Rs. {{ number_format($voucher->amount, 2) }}</span>
                        </div>

                        <!-- Searchable Payment Account Dropdown -->
                        <div>
                            <label class="mb-2 block text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Payment Account (Bank/Cash) <span class="text-rose-500">*</span>
                            </label>
                            <div class="w-full relative" @click.away="openPayAccount = false; highlightedPayAccountIndex = -1">
                                <input type="hidden" name="payment_account_id" x-model="payAccountId" required>
                                <button type="button" id="jv-pay-account-btn-show"
                                    @click="openPayAccount = !openPayAccount; if(openPayAccount) { $nextTick(() => $refs.payAccountSearchInput.focus()) }"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400"
                                    :class="!payAccountId && document.getElementById('jv-pay-account-error-show')?.style.display !== 'none' ? 'border-red-500' : ''">
                                    <span x-text="selectedPayAccountName ? selectedPayAccountName : 'Select Payment Account...'" class="truncate"></span>
                                    <span class="ml-2 text-xs opacity-60">▼</span>
                                </button>

                                <div x-show="openPayAccount" x-transition x-cloak
                                    class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                    <div class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <input type="text" x-ref="payAccountSearchInput" x-model="searchPayAccount" placeholder="Type account name to search..."
                                            @keydown.arrow-down.prevent="highlightedPayAccountIndex = (highlightedPayAccountIndex + 1) % filteredPayAccounts.length"
                                            @keydown.arrow-up.prevent="highlightedPayAccountIndex = (highlightedPayAccountIndex - 1 + filteredPayAccounts.length) % filteredPayAccounts.length"
                                            @keydown.enter.prevent="if(highlightedPayAccountIndex >= 0 && filteredPayAccounts[highlightedPayAccountIndex]) selectPayAccount(filteredPayAccounts[highlightedPayAccountIndex])"
                                            @keydown.escape="openPayAccount = false; highlightedPayAccountIndex = -1"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2 text-base font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-[260px] overflow-y-auto p-1.5 space-y-1 text-sm">
                                        <template x-for="(opt, index) in filteredPayAccounts" :key="opt.id">
                                            <button type="button" @click="selectPayAccount(opt)"
                                                @mouseenter="highlightedPayAccountIndex = index"
                                                class="w-full text-left px-3.5 py-2.5 rounded-xl transition-colors flex items-center justify-between"
                                                :class="payAccountId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedPayAccountIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                                <span x-text="opt.name" class="font-black text-base sm:text-lg truncate"></span>
                                                <span x-show="payAccountId == opt.id" class="font-black text-base">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="filteredPayAccounts.length === 0" class="px-4 py-3 text-center text-xs font-semibold text-gray-400">
                                            No matching Account found
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p id="jv-pay-account-error-show" style="display:none;" class="mt-1.5 text-xs font-semibold text-red-600 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                Payment Account is required. Please select a Bank or Cash account.
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Payment Method <span class="text-rose-500">*</span>
                            </label>
                            <select name="payment_method" required
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Online">Online</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Payment Date <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="paid_date" value="{{ date('Y-m-d') }}" required
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                                Reference / Cheque #
                            </label>
                            <input type="text" name="reference" value="{{ $voucher->reference }}" placeholder="Optional cheque/ref #"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-base sm:text-lg font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                            <button type="button" @click="payModalOpen = false"
                                class="rounded-xl border border-gray-300 dark:border-gray-700 px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800 transition-all">
                                Cancel
                            </button>
                            <button type="button"
                                onclick="confirmJvSettlementShow(this)"
                                class="rounded-xl bg-green-600 px-6 py-3 text-sm sm:text-base font-black text-white hover:bg-green-700 shadow-md transition-all cursor-pointer">
                                Confirm Settlement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<style>.swal2-container { z-index: 9999999 !important; }</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
@if($errors->has('payment_account_id'))
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        title: '❌ Insufficient Balance',
        html: `<div style="text-align:left;font-size:15px;">
            <p style="margin-bottom:10px;color:#374151;">{{ addslashes($errors->first('payment_account_id')) }}</p>
            <p style="margin-top:10px;color:#b45309;font-weight:700;">⚠️ Please select a different payment account with sufficient balance, or top up the current account before settling.</p>
        </div>`,
        icon: 'error',
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'OK, Go Back',
        allowOutsideClick: false,
    });
});
@endif
function confirmJvSettlementShow(btn) {
    const form = btn.closest('form');
    const payAccountInput = form.querySelector('input[name="payment_account_id"]');
    const errorEl = document.getElementById('jv-pay-account-error-show');
    const dropdownBtn = document.getElementById('jv-pay-account-btn-show');

    if (!payAccountInput || !payAccountInput.value) {
        if (errorEl) errorEl.style.display = 'flex';
        if (dropdownBtn) dropdownBtn.classList.add('border-red-500');
        dropdownBtn?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    if (errorEl) errorEl.style.display = 'none';
    if (dropdownBtn) dropdownBtn.classList.remove('border-red-500');

    Swal.fire({
        title: 'Confirm Settlement?',
        html: `<div style="text-align:left;font-size:15px;">
            <p style="margin-bottom:10px;">You are about to mark <strong>{{ $voucher->voucher_no }}</strong> as <strong>Paid/Settled</strong>.</p>
            <table style="width:100%;border-collapse:collapse;">
                <tr><td style="padding:6px 0;color:#6b7280;font-weight:600;">Voucher No:</td><td style="padding:6px 0;font-weight:900;color:#6d28d9;font-family:monospace;">{{ $voucher->voucher_no }}</td></tr>
                <tr><td style="padding:6px 0;color:#6b7280;font-weight:600;">Amount:</td><td style="padding:6px 0;font-weight:900;color:#059669;font-family:monospace;">Rs. {{ number_format($voucher->amount, 2) }}</td></tr>
            </table>
            <p style="margin-top:12px;color:#b45309;font-weight:700;">⚠️ This action cannot be undone.</p>
        </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '✅ Yes, Confirm Settlement',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        focusCancel: true,
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>
@endpush
