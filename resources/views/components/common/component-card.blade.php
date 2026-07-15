@props([
    'title' => null,
    'desc' => '',
])

<div {{ $attributes->merge(['class' => 'w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]']) }}>
    @if($title)
    <!-- Card Header -->
    <div class="px-6 py-5">
        <h3 class="text-xl font-bold text-gray-800 dark:text-white/90">
            {{ $title }}
        </h3>
        @if($desc)
            <p class="mt-1.5 text-base font-medium text-gray-500 dark:text-gray-400">
                {{ $desc }}
            </p>
        @endif
    </div>
    @endif

    <!-- Card Body -->
    <div class="{{ $title ? 'border-t border-gray-100 dark:border-gray-800' : '' }} p-4 sm:p-6">
        <div class="space-y-6">
            {{ $slot }}
        </div>
    </div>
</div>