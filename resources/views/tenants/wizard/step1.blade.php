@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">

    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants and Agreements</a>
        <span>/</span>
        @if(isset($tenant) && $tenant->id)
            <a href="{{ route('tenants.show', $tenant) }}" class="hover:text-brand-500">{{ $tenant->name }}</a>
            <span>/</span>
        @endif
        <span class="text-gray-800 dark:text-white/90">{{ $title }}</span>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if(isset($tenant) && $tenant->id)
        @include('tenants.wizard._progress', ['currentStep' => $step, 'tenantId' => $tenant->id])
    @endif

    {{-- Tenant photo banner (for edit mode) --}}
    @if(isset($tenant) && $tenant->id)
        @include('tenants.wizard._tenant_banner')
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800 flex justify-between items-center">
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white/90">Tenant Application Form</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Fill in the tenant's personal details and assign a flat.</p>
            </div>
            @if(isset($tenant) && $tenant->id)
                <a href="{{ route('tenants.printStep', [$tenant, 1]) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </a>
            @endif
        </div>

        @if(isset($tenant) && $tenant->id)
            <form method="POST" action="{{ route('tenants.update', $tenant) }}" enctype="multipart/form-data" class="px-6 py-6 space-y-6">
                @csrf
                @method('PUT')
        @else
            <form method="POST" action="{{ route('tenants.store') }}" enctype="multipart/form-data" class="px-6 py-6 space-y-6">
                @csrf
        @endif

            @include('tenants.wizard._step1_fields', ['tenant' => $tenant ?? null, 'units' => $units])

            <div class="flex items-center justify-between pt-2 gap-3">
                @if(isset($tenant) && $tenant->id)
                    <a href="{{ route('tenants.show', $tenant) }}"
                       class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        Cancel
                    </a>
                @else
                    <span></span>
                @endif

                <div class="flex items-center gap-3">
                    {{-- Save Only --}}
                    <button type="submit" name="save_only" value="1"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Only
                    </button>
                    {{-- Save & Continue --}}
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                        Save & Continue — Step 2
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
