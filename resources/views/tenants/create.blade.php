@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">

    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants</a>
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

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-colors">
                    Continue — Step 2
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection