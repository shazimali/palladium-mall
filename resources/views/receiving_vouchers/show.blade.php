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

        {{-- CENTERED TITLE --}}
        <div class="text-center mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-brand-600 dark:text-brand-400 uppercase">
                Tenant Receiving Voucher
            </h2>
        </div>

        {{-- TOP 2x2 GRID --}}
        <div
            class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-hidden mb-5 border border-gray-200 dark:border-gray-700">

            {{-- Row 1, Col 1: Date --}}
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">
                    Voucher Date</div>
                <div
                    class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base">
                    {{ $voucher->date->format('M. d, Y') }}
                </div>
            </div>

            {{-- Row 1, Col 2: Tenant Name --}}
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">
                    Tenant Name</div>
                <div
                    class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base">
                    {{ $recipientName }}
                </div>
            </div>

            {{-- Row 2, Col 1: Voucher No --}}
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">
                    Voucher No.</div>
                <div
                    class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base font-mono">
                    {{ $voucher->voucher_no }}
                </div>
            </div>

            {{-- Row 2, Col 2: Flat / Shop --}}
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">
                    Flat / Shop</div>
                <div
                    class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base">
                    {{ $voucher->payments->first()?->unit?->unit_number ? $voucher->payments->first()->unit->unit_number : 'N/A' }}
                </div>
            </div>

        </div>

        {{-- MIDDLE STACKED GRID --}}
        <div
            class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-hidden mb-5 border border-gray-200 dark:border-gray-700">

            {{-- Row 1: Payments List --}}
            <div class="grid grid-cols-1 md:grid-cols-3 min-h-[48px]">
                <div
                    class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide md:col-span-1">
                    Payments List</div>
                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3">
                    @if($voucher->payments->isNotEmpty())
                        <div class="space-y-1">
                            @foreach($voucher->payments as $payment)
                                <div class="text-xs font-bold text-gray-800 dark:text-gray-200">
                                    {{ $payment->month ? $payment->month->format('M Y') : '—' }} ({{ $payment->type_label }}) - Rs.
                                    {{ number_format($payment->pivot->amount_allocated, 2) }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-xs italic text-gray-500">No specific payments allocated.</span>
                    @endif
                </div>
            </div>

            {{-- Row 2: Payment Amount --}}
            <div class="grid grid-cols-1 md:grid-cols-3 min-h-[48px]">
                <div
                    class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide md:col-span-1">
                    Payment Amount</div>
                <div
                    class="md:col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-lg sm:text-xl font-mono text-emerald-600 dark:text-emerald-400">
                    Rs. {{ number_format($voucher->amount, 2) }}
                </div>
            </div>

            {{-- Row 3: Payment Method --}}
            <div class="grid grid-cols-1 md:grid-cols-3 min-h-[48px]">
                <div
                    class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide md:col-span-1">
                    Payment Method</div>
                <div
                    class="md:col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-extrabold text-sm">
                    {{ $voucher->paymentAccount ? $voucher->paymentAccount->name : '—' }}
                    @if($voucher->payment_method)
                        <span
                            class="ml-2 text-xs text-gray-500">({{ ucfirst(str_replace('_', ' ', $voucher->payment_method)) }})</span>
                    @endif
                </div>
            </div>

        </div>

        {{-- BOTTOM GRID SECTION --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">

            {{-- Left Box: Approved by Box --}}
            <div
                class="bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl p-4 flex flex-col justify-center border border-gray-200 dark:border-gray-700 shadow-xs">
                <p class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                    Approved by: <span
                        class="text-brand-600 dark:text-brand-400 font-extrabold ml-1">{{ $voucher->user->name ?? 'Management' }}</span>
                </p>
            </div>

            {{-- Right Box: Description of Goods/Services / Remarks --}}
            <div
                class="md:col-span-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 shadow-xs">
                <p class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">
                    Description of Goods/Services / Remarks:
                </p>
                <p class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 leading-relaxed">
                    {{ $voucher->notes ?? 'No specific remarks entered.' }}
                </p>
            </div>

        </div>

    </div>
@endsection