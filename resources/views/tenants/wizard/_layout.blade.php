@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">

    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants and Agreements</a>
        <span>/</span>
        <span class="text-gray-800 dark:text-white/90">{{ $title }}</span>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Progress Bar ─────────────────────────────────────────────────── --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @php
                $steps = [
                    1 => 'Tenant Info',
                    2 => 'Guarantor',
                    3 => 'Agreement',
                    4 => 'Documents',
                    5 => 'Move-in',
                    6 => 'Confirm',
                ];
                $currentStep = $step ?? 1;
                $tenantId = $tenant->id ?? null;
            @endphp

            @foreach($steps as $n => $label)
                @php
                    $isCompleted = $n < $currentStep;
                    $isActive    = $n === $currentStep;
                @endphp

                <div class="flex flex-col items-center {{ $n < count($steps) ? 'flex-1' : '' }}">
                    {{-- Step dot + connector --}}
                    <div class="flex items-center w-full">
                        {{-- Left line --}}
                        @if($n > 1)
                            <div class="flex-1 h-0.5 {{ $isCompleted || $isActive ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }} transition-colors duration-300"></div>
                        @endif

                        {{-- Circle --}}
                        <div class="relative flex-shrink-0">
                            @if($isCompleted && $tenantId)
                                <a href="{{ route('tenants.showStep', [$tenantId, $n]) }}"
                                   class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-500 text-white shadow-sm hover:bg-brand-600 transition-all">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </a>
                            @elseif($isActive)
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-500 text-white shadow-md ring-4 ring-brand-200 dark:ring-brand-900">
                                    <span class="text-sm font-bold">{{ $n }}</span>
                                </div>
                            @else
                                <div class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500">
                                    <span class="text-sm font-medium">{{ $n }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Right line --}}
                        @if($n < count($steps))
                            <div class="flex-1 h-0.5 {{ $n < $currentStep ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }} transition-colors duration-300"></div>
                        @endif
                    </div>

                    {{-- Label --}}
                    <span class="mt-2 text-xs font-medium {{ $isActive ? 'text-brand-600 dark:text-brand-400' : ($isCompleted ? 'text-gray-600 dark:text-gray-400' : 'text-gray-400 dark:text-gray-600') }}">
                        {{ $label }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Card ─────────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        {{-- Card Header --}}
        <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white/90">
                Step {{ $currentStep }} —
                @php echo array_values($steps)[$currentStep - 1]; @endphp
            </h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                @switch($currentStep)
                    @case(1) Fill in the tenant's personal details. @break
                    @case(2) Add guarantor information and emergency contacts. @break
                    @case(3) Assign a unit and set the agreement terms. @break
                    @case(4) Tick off the required documents checklist. @break
                    @case(5) Complete the move-in inspection. @break
                    @case(6) Review everything before confirming. @break
                @endswitch
            </p>
        </div>

        {{-- Step Content --}}
        <div class="px-6 py-6">
            @yield('step_content')
        </div>
    </div>
</div>
@endsection
