@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="General Voucher Details — {{ $voucher->voucher_no }}" />

    {{-- STICKY GENERAL RECEIVING VOUCHER HEADER --}}
    <div class="sticky mb-6 rounded-2xl border-2 border-emerald-500 bg-white dark:bg-gray-900 p-5 shadow-xl backdrop-blur-md"
        style="position: sticky; top: 72px; z-index: 990;">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-md text-3xl font-black">
                    📥
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                        General Receiving Voucher: {{ $voucher->voucher_no }}
                    </p>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900 dark:text-white mt-0.5">
                        {{ $voucher->party ? $voucher->party->name : ($voucher->landlord ? $voucher->landlord->name : ($voucher->fromPaymentAccount ? $voucher->fromPaymentAccount->name : 'General Voucher')) }}
                    </h2>
                </div>
            </div>

            <div class="text-right">
                <span class="text-xs font-extrabold uppercase tracking-wider text-gray-400 block">Total Amount Received</span>
                <span class="text-2xl sm:text-3xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                    Rs. {{ number_format($voucher->amount) }}
                </span>
            </div>
        </div>
    </div>

    <x-common.component-card title="General Receiving Voucher Details" desc="Voucher Reference #{{ $voucher->voucher_no }}">
        
        <div class="mb-6 flex justify-end gap-3 no-print">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('general-receiving-vouchers.edit'))
                <a href="{{ route('general-receiving-vouchers.edit', $voucher) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Voucher
                </a>
            @endif
            <a href="{{ route('general-receiving-vouchers.print', $voucher) }}"
                onclick="window.open(this.href,'_blank','width=800,height=800,scrollbars=yes'); return false;"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
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

        @php
            if ($voucher->received_from_type === 'account') {
                $receivedFrom = $voucher->fromPaymentAccount ? $voucher->fromPaymentAccount->name : 'Payment Account';
                $receivedFromLabel = 'Received From Account';
            } elseif ($voucher->received_from_type === 'landlord') {
                $receivedFrom = $voucher->landlord ? $voucher->landlord->name : 'N/A';
                $receivedFromLabel = 'Received From Landlord';
            } else {
                $receivedFrom = $voucher->party ? $voucher->party->name : 'N/A';
                $receivedFromLabel = 'Received From Party';
            }
        @endphp

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-6">
            @foreach([
                ['Voucher Number',      $voucher->voucher_no],
                ['Voucher Date',        $voucher->date->format('d M Y')],
                [$receivedFromLabel,    $receivedFrom],
                ['Voucher Amount',      'Rs. ' . number_format($voucher->amount, 0)],
                ['Received In Account', $voucher->paymentAccount ? $voucher->paymentAccount->name : '—'],
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
