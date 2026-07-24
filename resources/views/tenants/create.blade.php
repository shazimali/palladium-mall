@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">

    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants and Agreements</a>
        <span>/</span>
        <span class="text-gray-800 dark:text-white/90">Add New Tenant</span>
    </div>

    @include('tenants.wizard._progress', ['currentStep' => 1, 'tenantId' => null])

    {{-- Card --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white/90">Step 1 — Tenant Personal Information</h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Fill in the tenant's personal details. This creates a draft record.</p>
        </div>

        <form method="POST" action="{{ route('tenants.store') }}" enctype="multipart/form-data" class="px-6 py-6 space-y-6">
            @csrf
            @include('tenants.wizard._step1_fields', ['tenant' => null])

            <div class="flex items-center justify-between pt-4 gap-4 border-t-2 border-gray-100 dark:border-gray-800">
                <a href="{{ route('tenants.index') }}"
                   class="inline-flex items-center gap-2 rounded-2xl border-2 border-gray-300 bg-white px-6 py-3.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Cancel
                </a>
                <div class="flex items-center gap-3">
                    {{-- Save Only --}}
                    <button type="submit" name="save_only" value="1"
                        class="inline-flex items-center gap-2.5 rounded-2xl border-2 border-gray-300 bg-white px-6 py-3.5 text-base font-extrabold text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition-colors shadow-xs cursor-pointer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Only
                    </button>
                    {{-- Continue --}}
                    <button type="submit"
                        class="inline-flex items-center gap-2.5 rounded-2xl bg-brand-600 px-7 py-3.5 text-base font-extrabold text-white shadow-md hover:bg-brand-700 focus:outline-none transition-colors cursor-pointer">
                        Continue — Step 2
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection