@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-6">
        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('expense-heads.index') }}" class="hover:text-brand-500">Expense Heads</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">Edit Expense Head</span>
        </div>

        <x-common.component-card title="Edit Expense Head" desc="Update details for the expense category">
            <form action="{{ route('expense-heads.update', $expenseHead) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @php
                    $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
                    $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
                @endphp

                <div class="space-y-4">
                    {{-- Name --}}
                    <div>
                        <label class="{{ $label }}">Category / Head Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $expenseHead->name) }}" placeholder="e.g. Office Stationery, Janitorial Supplies" 
                               class="{{ $input }} {{ $errors->has('name') ? 'border-red-400' : '' }}" required>
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="{{ $label }}">Description</label>
                        <textarea name="description" placeholder="Brief details about what is recorded under this category..." rows="4"
                                  class="{{ $input }} {{ $errors->has('description') ? 'border-red-400' : '' }}">{{ old('description', $expenseHead->description) }}</textarea>
                        @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('expense-heads.index') }}"
                       class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                        Update Expense Head
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
