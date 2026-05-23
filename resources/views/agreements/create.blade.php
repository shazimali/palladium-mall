@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="New Agreement" />

    <x-common.component-card title="New Agreement" desc="Create a tenancy agreement with rent terms and fine rules">
        <form action="{{ route('agreements.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('agreements._form')

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Agreement
                </button>
                <a href="{{ route('agreements.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-common.component-card>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');

            const commonConfig = {
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
            };

            const startPicker = flatpickr('#start_date', {
                ...commonConfig,
                onChange: function (selectedDates) {
                    // When start date changes, update end date min date
                    if (selectedDates[0]) {
                        endPicker.set('minDate', selectedDates[0]);
                    }
                },
            });

            const endPicker = flatpickr('#end_date', {
                ...commonConfig,
                // End date must be after start date
                minDate: document.getElementById('start_date').value || null,
            });
        });
    </script>
@endpush