@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Tenant Voucher Details — {{ $voucher->voucher_no }}" />

    @php
        $recipientName = '';
        if ($voucher->received_from_type === 'tenant') {
            if ($voucher->tenant) {
                $recipientName = $voucher->tenant->name;
            } else {
                $firstPayment = $voucher->payments->first();
                $recipientName = ($firstPayment && $firstPayment->otherTenant) ? $firstPayment->otherTenant->name : 'N/A';
            }
        } elseif ($voucher->received_from_type === 'owner') {
            $recipientName = $voucher->owner->name ?? 'N/A';
        } else {
            $recipientName = $voucher->other_name;
        }
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 no-print">
        <div class="flex items-center gap-2">
            <a href="{{ route('receiving-vouchers.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-all">
                ← Back to List
            </a>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('receiving_vouchers.edit') || auth()->user()->hasPermission('receiving-vouchers.edit'))
                <a href="{{ route('receiving-vouchers.edit', $voucher) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-700 transition-all shadow-md">
                    ✏️ Edit Voucher
                </a>
            @endif
            <a href="{{ route('receiving-vouchers.print', $voucher) }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-gray-800 transition-all shadow-md">
                🖨️ Print Voucher
            </a>
        </div>
    </div>

    {{-- REFINED VOUCHER CARD --}}
    <div
        class="mx-auto max-w-4xl bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm text-gray-900 dark:text-white font-sans">

        {{-- FORM HEADER WITH CENTERED TITLE & RIGHT CORNER VOUCHER NUMBER --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">
            <div class="hidden sm:block w-36"></div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-brand-600 dark:text-brand-400 uppercase text-center">
                Tenant Receiving Voucher
            </h2>
            <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-2 shadow-2xs">
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Voucher No:</span>
                <span class="text-base sm:text-lg font-black font-mono text-brand-600 dark:text-brand-400">{{ $voucher->voucher_no }}</span>
            </div>
        </div>

        {{-- 2-COLUMN SIDE-BY-SIDE FORM GRID LAYOUT --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6 items-start">
            
            {{-- LEFT COLUMN: Date, Flat/Shop, Tenant Name, Payment Amount, Payment Method --}}
            <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                
                {{-- Field 1: Voucher Date --}}
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Voucher Date</div>
                    <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-base sm:text-lg">
                        {{ $voucher->date->format('M. d, Y') }}
                    </div>
                </div>

                {{-- Field 2: Flat / Shop --}}
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Flat / Shop</div>
                    <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-base sm:text-lg">
                        {{ $voucher->display_unit_number !== '—' ? $voucher->display_unit_number : 'N/A' }}
                    </div>
                </div>

                {{-- Field 3: Tenant Name --}}
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Tenant Name</div>
                    <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base">
                        {{ $recipientName }}
                    </div>
                </div>

                {{-- Field 4: Payment Amount --}}
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Payment Amount</div>
                    <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-lg sm:text-xl font-mono text-emerald-600 dark:text-emerald-400">
                        Rs. {{ number_format($voucher->amount, 2) }}
                    </div>
                </div>

                {{-- Field 5: Payment Method --}}
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Payment Method</div>
                    <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-base sm:text-lg">
                        {{ $voucher->paymentAccount ? $voucher->paymentAccount->name : '—' }}
                        @if($voucher->payment_method)
                            <span class="ml-2 text-xs font-semibold text-gray-500 dark:text-gray-400">({{ ucfirst(str_replace('_', ' ', $voucher->payment_method)) }})</span>
                        @endif
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: ONLY Payments List --}}
            <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden min-h-[260px] self-stretch">
                <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">
                    <span>Payments List</span>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white p-4 flex-1 flex flex-col justify-start">
                    @if($voucher->payments->isNotEmpty())
                        <div class="space-y-2.5">
                            @foreach($voucher->payments as $payment)
                                <div class="flex items-center justify-between p-3.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-2xs">
                                    <span class="font-black text-sm sm:text-base text-gray-900 dark:text-white">
                                        {{ $payment->month ? $payment->month->format('M Y') : '—' }} - {{ $payment->type_label }}
                                    </span>
                                    <span class="text-sm sm:text-base font-black text-brand-600 dark:text-brand-400 font-mono">
                                        Rs. {{ number_format($payment->pivot->amount_allocated, 2) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-base sm:text-lg font-black text-gray-400 my-auto text-center p-6">
                            No specific payments allocated.
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- BOTTOM GRID SECTION --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">

            {{-- Left Box: Approved by Box --}}
            <div class="bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl p-4 flex flex-col justify-center border border-gray-200 dark:border-gray-700 shadow-xs">
                <p class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                    Approved by: <span class="text-brand-600 dark:text-brand-400 font-extrabold ml-1">{{ $voucher->user->name ?? 'Management' }}</span>
                </p>
            </div>

            {{-- Right Box: Remarks --}}
            <div class="md:col-span-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 shadow-xs">
                <p class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                    Remarks:
                </p>
                <p class="text-base sm:text-lg font-black text-gray-900 dark:text-white leading-relaxed">
                    {{ $voucher->notes ?? 'No specific remarks entered.' }}
                </p>
            </div>

        </div>

    </div>
@endsection