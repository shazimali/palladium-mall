@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Payment — {{ $payment->tenant->name }}" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <x-common.component-card
        title="{{ $payment->tenant->name }}"
        desc="{{ ucfirst($payment->type) }} — {{ $payment->month->format('F Y') }} — Unit {{ $payment->unit->unit_number }}">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['Tenant',          $payment->tenant->name],
                ['Unit',            $payment->unit->unit_number],
                ['Type',            ucfirst($payment->type)],
                ['Month',           $payment->month->format('F Y')],
                ['Amount Due',      'Rs. '.number_format($payment->amount)],
                ['Amount Paid',     'Rs. '.number_format($payment->amount_paid)],
                ['Balance',         'Rs. '.number_format($payment->balanceDue())],
                ['Payment Method',  $payment->payment_method ? ucfirst(str_replace('_',' ',$payment->payment_method)) : '—'],
                ['Reference',       $payment->reference ?? '—'],
                ['Due Date',        $payment->due_date->format('d M Y')],
                ['Paid At',         $payment->paid_at ? $payment->paid_at->format('d M Y') : '—'],
                ['Status',          ucfirst($payment->status)],
            ] as [$label, $value])
                <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $label }}</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-800 dark:text-white/90">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        @if($payment->notes)
            <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-400">Notes</p>
                <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $payment->notes }}</p>
            </div>
        @endif

        @if($payment->receipt)
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <a href="{{ $payment->receipt_url }}" target="_blank"
                    class="text-sm text-brand-500 hover:underline">View Receipt</a>
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-3 pt-2">
            @if(auth()->user()->hasPermission('payments.edit') || auth()->user()->isSuperAdmin())
                <a href="{{ route('payments.edit', $payment) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    Edit Payment
                </a>
            @endif
            <a href="{{ route('payments.print', $payment) }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print Receipt
            </a>
            <a href="{{ route('payments.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                Back to Payments
            </a>
        </div>
    </x-common.component-card>
@endsection