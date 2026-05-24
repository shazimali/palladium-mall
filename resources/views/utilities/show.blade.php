@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Utility Reading — {{ $reading->unit->unit_number }}" />

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
        title="{{ $reading->type_icon }} {{ $reading->type_label }} — {{ $reading->unit->unit_number }}"
        desc="{{ $reading->month->format('F Y') }} · {{ $reading->tenant->name }}">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['Unit',              $reading->unit->unit_number],
                ['Tenant',            $reading->tenant->name],
                ['Type',              $reading->type_label],
                ['Month',             $reading->month->format('F Y')],
                ['Previous Reading',  number_format($reading->previous_reading, 2).' units'],
                ['Current Reading',   number_format($reading->current_reading, 2).' units'],
                ['Units Consumed',    number_format($reading->units_consumed, 2).' units'],
                ['Rate Per Unit',     'Rs. '.number_format($reading->rate_per_unit, 2)],
                ['Bill Amount',       'Rs. '.number_format($reading->bill_amount, 2)],
                ['Due Date',          $reading->due_date->format('d M Y')],
                ['Status',            ucfirst($reading->status)],
                ['Paid At',           $reading->paid_at ? $reading->paid_at->format('d M Y H:i') : '—'],
            ] as [$label, $value])
                <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $label }}</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-800 dark:text-white/90">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        @if($reading->notes)
            <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-400">Notes</p>
                <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $reading->notes }}</p>
            </div>
        @endif

        @if($reading->bill_proof)
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <a href="{{ $reading->bill_proof_url }}" target="_blank"
                    class="text-sm text-brand-500 hover:underline">
                    View Payment Proof
                </a>
            </div>
        @endif

        <div class="flex items-center gap-3 pt-2">
            @if(auth()->user()->hasPermission('utilities.edit') || auth()->user()->isSuperAdmin())
                <a href="{{ route('utilities.edit', $reading) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    Edit Reading
                </a>
            @endif
            <a href="{{ route('utilities.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                Back to Utilities
            </a>
        </div>
    </x-common.component-card>
@endsection