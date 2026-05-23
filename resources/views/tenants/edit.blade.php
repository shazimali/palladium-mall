@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Tenant — {{ $tenant->name }}" />

    <x-common.component-card title="Edit Tenant — {{ $tenant->name }}"
        desc="Update tenant details, unit assignment or CNIC images">
        <form action="{{ route('tenants.update', $tenant) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('tenants._form')

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Tenant
                </button>
                <a href="{{ route('tenants.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                    Cancel
                </a>
                <a href="{{ route('tenants.show', $tenant) }}"
                    class="ml-auto text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    View Profile →
                </a>
            </div>
        </form>
    </x-common.component-card>
@endsection