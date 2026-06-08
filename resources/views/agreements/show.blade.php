@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Agreement — {{ $agreement->tenant?->name ?? 'Deleted Tenant' }}" />

    <x-common.component-card
        title="{{ $agreement->tenant?->name ?? 'Deleted Tenant' }}"
        desc="Unit {{ $agreement->unit?->unit_number ?? 'Deleted Unit' }} · {{ $agreement->start_date->format('d M Y') }} to {{ $agreement->end_date->format('d M Y') }}">

        {{-- Status banner --}}
        @if($agreement->isExpiringSoon())
            <div class="flex items-center gap-3 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-700 dark:border-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                This agreement expires in {{ $agreement->daysRemaining() }} days.
            </div>
        @endif

        {{-- Details grid --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['Tenant',           $agreement->tenant?->name ?? 'Deleted Tenant'],
                ['Unit',             $agreement->unit?->unit_number ?? 'Deleted Unit'],
                ['Start Date',       $agreement->start_date->format('d M Y')],
                ['End Date',         $agreement->end_date->format('d M Y')],
                ['Duration',         $agreement->durationInMonths().' months'],
                ['Days Remaining',   $agreement->isActive() ? $agreement->daysRemaining().' days' : '—'],
                ['Monthly Rent',     'Rs. '.number_format($agreement->monthly_rent)],
                ['Security Deposit', $agreement->security_deposit ? 'Rs. '.number_format($agreement->security_deposit) : '—'],
                ['Grace Period',     $agreement->grace_period_days.' days'],
                ['Fine Per Day',     'Rs. '.number_format($agreement->fine_per_day)],
                ['Status',           ucfirst($agreement->status)],
            ] as [$label, $value])
                <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $label }}</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-800 dark:text-white/90">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        {{-- Terms --}}
        @if($agreement->terms)
            <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-400 dark:text-gray-500">Agreement Terms</p>
                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $agreement->terms }}</p>
            </div>
        @endif

        {{-- Document --}}
        @if($agreement->document)
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <a href="{{ $agreement->document_url }}" target="_blank"
                    class="text-sm text-brand-500 hover:underline">
                    View Signed Document
                </a>
            </div>
        @endif

        <div class="flex items-center gap-3 pt-2">
            @if(auth()->user()->hasPermission('agreements.edit') || auth()->user()->isSuperAdmin())
                <a href="{{ route('agreements.edit', $agreement) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    Edit Agreement
                </a>
            @endif
            <a href="{{ route('agreements.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                Back to Agreements
            </a>
        </div>
    </x-common.component-card>
@endsection