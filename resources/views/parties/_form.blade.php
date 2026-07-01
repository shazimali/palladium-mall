<div class="space-y-6">
    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
            Party Head Profile Details
        </h4>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            {{-- Name --}}
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $party->name ?? '') }}" required
                    placeholder="e.g. Muhammad Ali"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('name') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Phone Number
                </label>
                <input type="text" name="phone" value="{{ old('phone', $party->phone ?? '') }}"
                    placeholder="e.g. +923001234567"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('phone') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
                @error('phone')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- WhatsApp Number --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    WhatsApp Number
                </label>
                <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $party->whatsapp_number ?? '') }}"
                    placeholder="e.g. +923001234567"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('whatsapp_number') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
                @error('whatsapp_number')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>
