@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Update Utility Billing & Notes — {{ $unit->unit_number }}" />

    @if($canEditMetersOnly ?? false)
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs sm:text-sm text-amber-800 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-300 flex items-center gap-3 shadow-xs">
            <span class="text-2xl">⚡</span>
            <div>
                <span class="font-bold text-sm block">Utility Meters Management Mode</span>
                <span>Viewing utility meter connection references for Flat/Shop <strong>{{ $unit->unit_number }}</strong>. General unit specifications and financial records are hidden.</span>
            </div>
        </div>

        <x-common.component-card title="Utility Meters — {{ $unit->unit_number }}"
            desc="Manage electricity, water, and gas meter connection details">
            @include('meters._panel', [
                'unit'            => $unit,
                'existingMeters'  => $existingMeters,
                'canEditMeters'   => $canEditMeters ?? true,
                'canDeleteMeters' => $canDeleteMeters ?? true,
            ])

            <div class="flex items-center gap-4 pt-6 mt-8 border-t-2 border-gray-100 dark:border-gray-800">
                <a href="{{ route('units.show', $unit) }}"
                    class="inline-flex items-center rounded-2xl border-2 border-gray-300 px-6 py-3.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                    Back to Flat/Shop
                </a>
            </div>
        </x-common.component-card>
    @else
        <x-common.component-card title="Update Utility Billing & Notes — {{ $unit->unit_number }}"
            desc="Update utility meter details and billing notes for this unit">
            <form action="{{ route('units.update', $unit) }}" method="POST">
                @csrf
                @method('PUT')
                @include('units._form')

                <div class="flex items-center gap-4 pt-6 mt-8 border-t-2 border-gray-100 dark:border-gray-800">
                    <button type="submit"
                        class="inline-flex items-center gap-3 rounded-2xl bg-brand-600 px-7 py-3.5 text-base font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Flat/Shop
                    </button>
                    <a href="{{ route('units.show', $unit) }}"
                        class="inline-flex items-center rounded-2xl border-2 border-gray-300 px-6 py-3.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                        Cancel
                    </a>
                    <a href="{{ route('units.show', $unit) }}"
                        class="ml-auto inline-flex items-center gap-2 text-base font-bold text-gray-500 hover:text-brand-600 dark:hover:text-brand-400 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        View Flat/Shop
                    </a>
                </div>
            </form>
        </x-common.component-card>
    @endif
@endsection