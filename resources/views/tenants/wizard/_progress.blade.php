@php
    $steps = [
        1 => 'Tenant & Agreement',
        2 => 'Guarantor',
        4 => 'Documents',
        5 => 'Move-in',
    ];

    $tenant = $tenantId ? \App\Models\Tenant::find($tenantId) : null;
    $draftAgreement = null;
    if ($tenant) {
        $draftAgreement = $tenant->agreements()->where('status', 'active')->latest()->first()
            ?: $tenant->agreements()->where('status', 'draft')->latest()->first();
    }

    $stepFilled = [
        1 => $tenant !== null && $draftAgreement && $draftAgreement->status === 'active',
        2 => $draftAgreement && $draftAgreement->guarantors()->exists(),
        4 => $draftAgreement && $draftAgreement->documentChecklist()->exists(),
        5 => $draftAgreement &&
            $draftAgreement->moveInChecklist !== null &&
            $draftAgreement->moveInChecklist->inspection_member !== null &&
            $draftAgreement->moveInChecklist->checklist_date !== null,
    ];
@endphp

<div class="mb-8">
    <div class="flex items-center justify-between">
        {{-- Non-linear: steps 2/4/5 no longer gate each other or activation, so each
             chip just reflects whether that section has data, not sequential progress. --}}
        @foreach($steps as $n => $label)
            @php
                $isFilled = $stepFilled[$n] ?? false;
                $isActive = $n === $currentStep;
            @endphp

            <div class="flex flex-col items-center {{ !$loop->last ? 'flex-1' : '' }}">
                <div class="flex items-center w-full">
                    {{-- Left connector --}}
                    @if(!$loop->first)
                        <div
                            class="flex-1 h-0.5 transition-colors duration-300 {{ $isFilled || $isActive ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                        </div>
                    @endif

                    {{-- Circle --}}
                    <div class="relative flex-shrink-0">
                        @if($isActive)
                            @if($isFilled && $tenantId)
                                <a href="{{ route('tenants.showStep', [$tenantId, $n]) }}" title="{{ $label }}"
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-green-600 text-white shadow-md ring-4 ring-green-200 dark:ring-green-900/50 hover:bg-green-700 transition-all">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </a>
                            @else
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-full bg-green-600 text-white shadow-md ring-4 ring-green-200 dark:ring-green-900/50">
                                    <span class="text-sm font-bold">{{ $n }}</span>
                                </div>
                            @endif
                        @elseif($isFilled && $tenantId)
                            <a href="{{ route('tenants.showStep', [$tenantId, $n]) }}" title="Go to {{ $label }}"
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-500 text-white shadow-sm hover:bg-brand-600 transition-all">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </a>
                        @elseif($isFilled)
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-500 text-white shadow-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        @elseif($tenantId)
                            <a href="{{ route('tenants.showStep', [$tenantId, $n]) }}" title="Go to {{ $label }}"
                                class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-900 hover:border-brand-400 transition-colors">
                                <span class="text-sm font-bold">{{ $n }}</span>
                            </a>
                        @else
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-900">
                                <span class="text-sm font-bold">{{ $n }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Right connector --}}
                    @if(!$loop->last)
                        <div
                            class="flex-1 h-0.5 transition-colors duration-300 {{ $isFilled ? 'bg-brand-500' : 'bg-gray-200 dark:bg-gray-700' }}">
                        </div>
                    @endif
                </div>

                <span
                    class="mt-2 text-xs font-bold {{ $isActive ? 'text-green-600 dark:text-green-400' : ($isFilled ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400 dark:text-gray-500') }}">
                    {{ $label }}
                </span>
            </div>
        @endforeach
    </div>
</div>