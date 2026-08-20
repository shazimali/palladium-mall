@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit {{ $reportType->name }} Settings" />

    <div class="mx-auto w-full max-w-4xl">
        <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] p-6 space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 dark:border-gray-800">
                <div>
                    <h2 class="text-lg font-extrabold text-gray-800 dark:text-white/90">Edit Report Type: {{ $reportType->name }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Update report identity, daily time-window schedule, and settings.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('report-types.remarks', $reportType) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-3 py-1.5 text-xs font-bold text-brand-600 hover:bg-brand-100 dark:border-brand-900/40 dark:bg-brand-950/40 dark:text-brand-400 transition-colors">
                        💬 Manage Remarks ({{ $reportType->remarks->count() }})
                    </a>
                    <a href="{{ route('report-types.index') }}"
                       class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                        ← Back to List
                    </a>
                </div>
            </div>

            <form action="{{ route('report-types.update', $reportType) }}" method="POST">
                @method('PUT')
                @include('report_types._form')
            </form>
        </div>
    </div>
@endsection
