@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-6">
        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('items.index') }}" class="hover:text-brand-500">Items Stock</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">Edit SKU Item</span>
        </div>

        <x-common.component-card title="Edit SKU Item: {{ $item->code }}" desc="Modify registered inventory item parameters.">
            <form action="{{ route('items.update', $item) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @php
                    $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
                    $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
                @endphp

                <div class="space-y-4">
                    {{-- Name --}}
                    <div>
                        <label class="{{ $label }}">Item Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $item->name) }}" placeholder="e.g. LED Bulb 12W" 
                               class="{{ $input }} {{ $errors->has('name') ? 'border-red-400' : '' }}" required>
                        @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="{{ $label }}">Category</label>
                        <input type="text" name="category" value="{{ old('category', $item->category) }}" placeholder="e.g. Electrical" 
                               class="{{ $input }} {{ $errors->has('category') ? 'border-red-400' : '' }}">
                        @error('category') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Unit of Measure --}}
                        <div>
                            <label class="{{ $label }}">Unit of Measure <span class="text-red-500">*</span></label>
                            <input type="text" name="unit_of_measure" value="{{ old('unit_of_measure', $item->unit_of_measure) }}" placeholder="e.g. Pcs" 
                                   class="{{ $input }} {{ $errors->has('unit_of_measure') ? 'border-red-400' : '' }}" required>
                            @error('unit_of_measure') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Minimum Stock Level --}}
                        <div>
                            <label class="{{ $label }}">Minimum Stock Alert Level <span class="text-red-500">*</span></label>
                            <input type="number" name="min_stock_level" value="{{ old('min_stock_level', $item->min_stock_level) }}" placeholder="e.g. 5" min="0" step="any"
                                   class="{{ $input }} {{ $errors->has('min_stock_level') ? 'border-red-400' : '' }}" required>
                            @error('min_stock_level') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="{{ $label }}">Description</label>
                        <textarea name="description" placeholder="Specify brand, technical parameters or usage details..." rows="4"
                                  class="{{ $input }} {{ $errors->has('description') ? 'border-red-400' : '' }}">{{ old('description', $item->description) }}</textarea>
                        @error('description') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('items.index') }}"
                       class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                        Update SKU
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
