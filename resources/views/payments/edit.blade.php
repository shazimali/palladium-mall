@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Payment" />

    <x-common.component-card title="Edit Payment" desc="Update payment record details">
        <form action="{{ route('payments.update', $payment) }}" method="POST">
            @csrf
            @method('PUT')
            @include('payments._form')

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Record
                </button>
                <a href="{{ route('payments.index') }}"
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
            flatpickr('#month', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F Y',
                allowInput: false,
                disableMobile: true,
                disable: [function (date) { return date.getDate() !== 1; }],
            });

            flatpickr('#due_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
            });
        });
    </script>
@endpush