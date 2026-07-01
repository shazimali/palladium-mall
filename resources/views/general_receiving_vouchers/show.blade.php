@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="General Voucher Details — {{ $voucher->voucher_no }}" />

    <x-common.component-card title="General Receiving Voucher Details" desc="Voucher Reference #{{ $voucher->voucher_no }}">
        
        <div class="mb-6 flex justify-end gap-3 no-print">
            <a href="{{ route('general-receiving-vouchers.print', $voucher) }}"
                onclick="window.open(this.href,'_blank','width=800,height=800,scrollbars=yes'); return false;"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print Voucher
            </a>
            <a href="{{ route('general-receiving-vouchers.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                Back to List
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-6">
            @foreach([
                ['Voucher Number',      $voucher->voucher_no],
                ['Voucher Date',        $voucher->date->format('d M Y')],
                ['Received From Party', $voucher->party ? $voucher->party->name : 'N/A'],
                ['Voucher Amount',      'Rs. ' . number_format($voucher->amount, 0)],
                ['Payment Account',     $voucher->paymentAccount ? $voucher->paymentAccount->name : '—'],
                ['Payment Method',      $voucher->payment_method ? ucfirst(str_replace('_',' ',$voucher->payment_method)) : '—'],
                ['Reference / Cheque',  $voucher->reference ?? '—'],
                ['Recorded By',         $voucher->user->name ?? '—'],
            ] as [$label, $value])
                <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $label }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        @if($voucher->notes)
            <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03] mb-6">
                <p class="text-xs text-gray-400">Notes / Remarks</p>
                <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $voucher->notes }}</p>
            </div>
        @endif

    </x-common.component-card>
@endsection
