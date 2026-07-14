@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Voucher Details — {{ $receivable->voucher_no }}" />

    <x-common.component-card title="Owner Receivable Voucher Details" desc="Voucher Reference #{{ $receivable->voucher_no }}">
        
        <div class="mb-6 flex justify-end gap-3 no-print">
            <a href="{{ route('owner-receivables.print', $receivable) }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print Voucher
            </a>

            @if(auth()->user()->isSuperAdmin())
                <form action="{{ route('owner-receivables.destroy', $receivable) }}" method="POST" x-data
                    @submit.prevent="confirmAction($el, 'Are you sure you want to cancel and delete this Owner Receivable Voucher of Rs. {{ number_format($receivable->amount) }}? This action is irreversible.', 'Cancel Voucher?', 'Yes, Cancel')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors shadow-sm cursor-pointer">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Cancel Voucher
                    </button>
                </form>
            @endif

            <a href="{{ route('owner-receivables.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                Back to List
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-6">
            @foreach([
                ['Voucher Number',      $receivable->voucher_no],
                ['Voucher Date',        $receivable->date->format('d M Y')],
                ['Managing Owner',      $receivable->owner->name ?? '—'],
                ['Voucher Amount',      'Rs. ' . number_format($receivable->amount, 2)],
                ['Received In Account', $receivable->paymentAccount ? $receivable->paymentAccount->name : '—'],
                ['Payment Method',      $receivable->paymentAccount ? strtoupper($receivable->paymentAccount->type) : '—'],
                ['Reference / Remarks', $receivable->reference ?? '—'],
                ['Recorded By',         $receivable->user->name ?? '—'],
            ] as [$label, $value])
                <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $label }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        @if($receivable->notes)
            <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03] mb-6">
                <p class="text-xs text-gray-400">Notes / Remarks</p>
                <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $receivable->notes }}</p>
            </div>
        @endif

        @if($receivable->receipt)
            <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03] mb-6">
                <p class="text-xs text-gray-400 mb-2">Attachment</p>
                <a href="{{ $receivable->receipt_url }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 dark:bg-brand-950/20 dark:hover:bg-brand-950/40 px-4 py-2 text-sm font-medium transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    View Attached File
                </a>
            </div>
        @endif

    </x-common.component-card>
@endsection
