@php
    $steps = [
        1 => 'Tenant Info',
        2 => 'Guarantor',
        3 => 'Agreement',
        4 => 'Documents',
        5 => 'Move-in',
        6 => 'Confirm',
    ];
@endphp

<div class="mb-8">
    <div class="flex items-center justify-between">
        @foreach($steps as $n => $label)
            @php
                $isCompleted = $n < $currentStep;
                $isActive    = $n === $currentStep;
            @endphp

            <div class="flex flex-col items-center {{ $n < count($steps) ? 'flex-1' : '' }}">
                <div class="flex items-center w-full">
                    {{-- Left connector --}}
                    @if($n > 1)
                        <div class="flex-1 h-0.5 transition-colors duration-300 {{ ($isCompleted || $isActive) ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                    @endif

                    {{-- Circle --}}
                    <div class="relative flex-shrink-0">
                        @if($isCompleted && $tenantId)
                            <a href="{{ route('tenants.showStep', [$tenantId, $n]) }}"
                               title="Go back to {{ $label }}"
                               class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-500 text-white shadow-sm hover:bg-brand-600 transition-all">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </a>
                        @elseif($isCompleted)
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-500 text-white shadow-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        @elseif($isActive)
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-500 text-white shadow-md ring-4 ring-brand-200 dark:ring-brand-900/50">
                                <span class="text-sm font-bold">{{ $n }}</span>
                            </div>
                        @else
                            <div class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-900">
                                <span class="text-sm font-medium">{{ $n }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Right connector --}}
                    @if($n < count($steps))
                        <div class="flex-1 h-0.5 transition-colors duration-300 {{ $n < $currentStep ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                    @endif
                </div>

                <span class="mt-2 text-xs font-medium {{ $isActive ? 'text-brand-600 dark:text-brand-400' : ($isCompleted ? 'text-gray-600 dark:text-gray-400' : 'text-gray-400 dark:text-gray-600') }}">
                    {{ $label }}
                </span>
            </div>
        @endforeach
    </div>
</div>
