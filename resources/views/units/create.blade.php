@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Add New Flat/Shop" />

    <x-common.component-card title="Add New Flat/Shop" desc="Register a new flat or shop in the system">
        <form action="{{ route('units.store') }}" method="POST">
            @csrf
            @include('units._form')

            <div class="flex items-center gap-4 pt-6 mt-8 border-t-2 border-gray-100 dark:border-gray-800">
                <button type="submit"
                    class="inline-flex items-center gap-3 rounded-2xl bg-brand-600 px-7 py-3.5 text-base font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Flat/Shop
                </button>
                <a href="{{ route('units.index') }}"
                    class="inline-flex items-center rounded-2xl border-2 border-gray-300 px-6 py-3.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-common.component-card>
@endsection