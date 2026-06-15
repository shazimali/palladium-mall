@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-6">
        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('inspection-persons.index') }}" class="hover:text-brand-500">Inspection Persons</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">Add Inspector</span>
        </div>

        <x-common.component-card title="Add New Inspector" desc="Create a new profile for an inspection team member">
            <form action="{{ route('inspection-persons.store') }}" method="POST" class="space-y-6">
                @csrf

                @php
                    $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
                    $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
                @endphp

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    {{-- Name --}}
                    <div>
                        <label class="{{ $label }}">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Full Name" 
                               class="{{ $input }} {{ $errors->has('name') ? 'border-red-400' : '' }}" required>
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Designation --}}
                    <div>
                        <label class="{{ $label }}">Designation</label>
                        <input type="text" name="designation" value="{{ old('designation') }}" placeholder="e.g. Inspector, Manager" 
                               class="{{ $input }} {{ $errors->has('designation') ? 'border-red-400' : '' }}">
                        @error('designation') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="{{ $label }}">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Phone Number" 
                               class="{{ $input }} {{ $errors->has('phone') ? 'border-red-400' : '' }}">
                        @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="{{ $label }}">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Address" 
                               class="{{ $input }} {{ $errors->has('email') ? 'border-red-400' : '' }}">
                        @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Active Status --}}
                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                                   class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Active status (available for selection in checklist)</span>
                        </label>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('inspection-persons.index') }}"
                       class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                        Save Inspector
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
