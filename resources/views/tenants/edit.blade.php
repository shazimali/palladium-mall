@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Tenant — {{ $tenant->name }}" />

    <x-common.component-card title="Edit Tenant — {{ $tenant->name }}"
        desc="Update tenant details, unit assignment or CNIC images">
        <form action="{{ route('tenants.update', $tenant) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('tenants._form')

            <div class="flex items-center gap-4 pt-6 mt-8 border-t-2 border-gray-100 dark:border-gray-800">
                <button type="submit"
                    class="inline-flex items-center gap-3 rounded-2xl bg-brand-600 px-7 py-3.5 text-base font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Tenant
                </button>
                <a href="{{ route('tenants.index') }}"
                    class="inline-flex items-center rounded-2xl border-2 border-gray-300 px-6 py-3.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                    Cancel
                </a>
                <a href="{{ route('tenants.show', $tenant) }}"
                    class="ml-auto text-base font-bold text-gray-500 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
                    View Profile →
                </a>
            </div>
        </form>
    </x-common.component-card>
@endsection