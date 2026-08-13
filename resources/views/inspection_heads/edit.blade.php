@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Inspection Head" />

    <div class="mx-auto max-w-xl">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-5 text-lg font-extrabold text-gray-800 dark:text-white/90">Edit: {{ $head->name }}</h3>

            <form action="{{ route('inspection-heads.update', $head) }}" method="POST" class="space-y-5">
                @csrf @method('PUT')
                @include('inspection_heads._form', ['head' => $head])
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <a href="{{ route('inspection-heads.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">Cancel</a>
                    <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2 text-sm font-bold text-white hover:bg-brand-600">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection
