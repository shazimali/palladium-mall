@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Tenant — {{ $tenant->name }}" />

    <x-common.component-card
        title="{{ $tenant->name }}"
        desc="Unit {{ $tenant->unit->unit_number }} · {{ ucfirst($tenant->status) }}">

        {{-- Details grid --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['Full Name',   $tenant->name],
                ['CNIC',        $tenant->cnic],
                ['Phone',       $tenant->phone],
                ['Email',       $tenant->email       ?? '—'],
                ['Occupation',  $tenant->occupation  ?? '—'],
                ['Dependents',  $tenant->dependents  ?? '—'],
                ['Address',     $tenant->address     ?? '—'],
                ['Unit',        $tenant->unit->unit_number],
                ['Status',      ucfirst($tenant->status)],
            ] as [$label, $value])
                <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $label }}</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-800 dark:text-white/90">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        {{-- CNIC Images --}}
        @if($tenant->cnic_front_image || $tenant->cnic_back_image)
            <div>
                <h4 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">CNIC Images</h4>
                <div class="flex flex-wrap gap-4">
                    @if($tenant->cnic_front_image)
                        <div class="flex flex-col items-center gap-2">
                            <p class="text-xs text-gray-400">Front</p>
                            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                <img src="{{ $tenant->cnic_front_url }}" alt="CNIC Front" class="h-32 w-auto object-cover opacity-90 hover:opacity-100 transition-opacity">
                            </div>
                            <a href="{{ $tenant->cnic_front_url }}" target="_blank"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm text-brand-500 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/[0.05] transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                View Full
                            </a>
                        </div>
                    @endif
                    @if($tenant->cnic_back_image)
                        <div class="flex flex-col items-center gap-2">
                            <p class="text-xs text-gray-400">Back</p>
                            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                <img src="{{ $tenant->cnic_back_url }}" alt="CNIC Back" class="h-32 w-auto object-cover opacity-90 hover:opacity-100 transition-opacity">
                            </div>
                            <a href="{{ $tenant->cnic_back_url }}" target="_blank"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm text-brand-500 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/[0.05] transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                View Full
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Notes --}}
        @if($tenant->notes)
            <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-400 dark:text-gray-500">Notes</p>
                <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $tenant->notes }}</p>
            </div>
        @endif

        <div class="flex items-center gap-3 pt-2">
            @if(auth()->user()->hasPermission('tenants.edit') || auth()->user()->isSuperAdmin())
                <a href="{{ route('tenants.edit', $tenant) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    Edit Tenant
                </a>
            @endif
            <a href="{{ route('tenants.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                Back to Tenants
            </a>
        </div>
    </x-common.component-card>
@endsection