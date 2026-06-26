@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Owner — {{ $owner->name }}" />

    <x-common.component-card title="Edit Owner — {{ $owner->name }}" desc="Update managing owner profile details">
        <form action="{{ route('owners.update', $owner) }}" method="POST">
            @csrf
            @method('PUT')
            @include('owners._form')

            <div class="flex items-center gap-3 pt-6">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Owner
                </button>
                <a href="{{ route('owners.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-common.component-card>
@endsection
