@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="ORP Voucher Details — {{ $voucher->voucher_no }}" />

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400 max-w-4xl mx-auto">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="max-w-4xl mx-auto">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 no-print">
            <div class="flex items-center gap-2">
                <a href="{{ route('other-owned-rent-purchase-vouchers.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-all">
                    ← Back to List
                </a>
            </div>

            <div class="flex items-center gap-3">
                @if(auth()->user()->hasPermission('other_owned_rent_purchase_vouchers.edit') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('other-owned-rent-purchase-vouchers.edit', $voucher->id) }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-700 transition-all shadow-xs">
                        ✏️ Edit Voucher
                    </a>
                @endif
                <a href="{{ route('other-owned-rent-purchase-vouchers.print', $voucher->id) }}" target="_blank"
                    class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-gray-800 transition-all shadow-md">
                    🖨️ Print Voucher
                </a>
            </div>
        </div>

        {{-- Voucher Card matching JV Vouchers --}}
        <div class="mx-auto max-w-4xl bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm text-gray-900 dark:text-white font-sans">
            
            {{-- Header matching JV Vouchers --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">
                <div class="hidden sm:block w-36"></div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-brand-600 dark:text-brand-400 uppercase text-center">
                    Rent Purchase Voucher (ORP)
                </h2>
                <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-2 shadow-2xs">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Voucher No:</span>
                    <span class="text-base sm:text-lg font-black font-mono text-brand-600 dark:text-brand-400">{{ $voucher->voucher_no }}</span>
                </div>
            </div>

            {{-- 2-Column Side-By-Side Form Grid Layout matching JV Vouchers --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6 items-start">
                
                {{-- LEFT COLUMN --}}
                <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Voucher Date</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-base sm:text-lg">
                            {{ $voucher->date ? $voucher->date->format('M. d, Y') : '—' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Billing Month</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-base sm:text-lg">
                            {{ $voucher->month ? $voucher->month->format('F Y') : '—' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Landlord</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-base sm:text-lg">
                            {{ $voucher->landlord->name ?? '—' }}
                        </div>
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Self Unit</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-base sm:text-lg">
                            {{ $voucher->unit?->unit_number ?? '—' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Other Tenant</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-black text-base sm:text-lg">
                            {{ $voucher->otherTenant?->name ?? '—' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Purchase Amount</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 px-4 py-3 flex items-center font-black text-lg sm:text-xl font-mono">
                            Rs. {{ number_format($voucher->amount, 2) }}
                        </div>
                    </div>
                </div>

            </div>

            {{-- Footer Section --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">
                <div class="bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl p-4 flex flex-col justify-center border border-gray-200 dark:border-gray-700 shadow-xs">
                    <p class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                        Prepared by: <span class="text-brand-600 dark:text-brand-400 font-extrabold ml-1">{{ $voucher->user->name ?? 'Management' }}</span>
                    </p>
                </div>

                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4">
                    <p class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                        Remarks:
                    </p>
                    <p class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 leading-relaxed">
                        {{ $voucher->notes ?? 'No specific remarks entered.' }}
                    </p>
                </div>
            </div>

        </div>
    </div>
@endsection
