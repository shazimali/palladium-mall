@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Create Permission" />

    <div class="mx-auto w-full max-w-[650px]">
        <div
            class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Permission Details</h3>

            <form action="{{ route('permissions.store') }}" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <label for="display_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Display Name
                        </label>
                        <input type="text" name="display_name" id="display_name" value="{{ old('display_name') }}"
                            placeholder="e.g., View Users"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('display_name') border-red-500 @enderror"
                            required />
                        @error('display_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            System Name (Slug)
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g., users.view"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('name') border-red-500 @enderror"
                            required />
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="group" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Group / Section
                        </label>
                        <input type="text" name="group" id="group" value="{{ old('group') }}"
                            placeholder="e.g., User Management" list="group-list"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('group') border-red-500 @enderror"
                            required />
                        <datalist id="group-list">
                            @foreach($groups as $g)
                                <option value="{{ $g }}">
                            @endforeach
                        </datalist>
                        @error('group')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('permissions.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Create Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection