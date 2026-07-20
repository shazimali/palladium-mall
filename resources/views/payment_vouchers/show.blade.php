@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Voucher Details — {{ $voucher->voucher_no }}" />

    <x-common.component-card title="Payment Voucher Details" desc="Voucher Reference #{{ $voucher->voucher_no }}">
        
        <div class="mb-6 flex justify-end gap-3 no-print">
            <a href="{{ route('payment-vouchers.print', $voucher) }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print Voucher
            </a>

            @if(auth()->user()->hasPermission('payment_vouchers.delete') || auth()->user()->isSuperAdmin())
                <form action="{{ route('payment-vouchers.destroy', $voucher) }}" method="POST" x-data
                    @submit.prevent="confirmAction($el, 'Are you sure you want to cancel and delete this Payment Voucher of Rs. {{ number_format($voucher->amount) }}? This action is irreversible.', 'Cancel Voucher?', 'Yes, Cancel')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors shadow-sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Cancel Voucher
                    </button>
                </form>
            @endif

            <a href="{{ route('payment-vouchers.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                Back to List
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-6">
            @php
                if ($voucher->paid_to_type === 'owner') {
                    $payeeName = $voucher->owner->name ?? 'Partner';
                    $payeeType = 'Managing Owner / Partner';
                } elseif ($voucher->paid_to_type === 'tenant') {
                    $payeeName = ($voucher->tenant ? $voucher->tenant->name : $voucher->other_name) . ($voucher->unit ? ' (Unit ' . $voucher->unit->unit_number . ')' : '');
                    $payeeType = 'Tenant (Refund Security Deposit)';
                } elseif ($voucher->paid_to_type === 'landlord') {
                    $payeeName = $voucher->landlord ? $voucher->landlord->name : $voucher->other_name;
                    $payeeType = 'Landlord (Payout)';
                } else {
                    $payeeName = $voucher->party ? $voucher->party->name : $voucher->other_name;
                    $payeeType = 'Party (Suppliers/Contractors)';
                }
            @endphp
            
            @foreach([
                ['Voucher Number',      $voucher->voucher_no],
                ['Voucher Date',        $voucher->date->format('d M Y')],
                ['Paid To (Payee)',     $payeeName],
                ['Payee Type',          $payeeType],
                ['Voucher Amount',      'Rs. ' . number_format($voucher->amount, 2)],
                ['Advance Payout?',     $voucher->is_advance ? 'Yes (Advance)' : 'No (Standard Payout)'],
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
