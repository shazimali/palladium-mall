@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">

    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants</a>
        <span>/</span>
        <span class="text-gray-800 dark:text-white/90">{{ $title }}</span>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @include('tenants.wizard._progress', ['currentStep' => $step, 'tenantId' => $tenant->id])

    {{-- Review Card --}}
    <div class="space-y-4">

        {{-- Tenant Summary --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-6">
            <div class="flex items-start gap-4">
                @if($tenant->passport_photo)
                    <img src="{{ $tenant->passport_photo_url }}" class="h-16 w-16 rounded-full object-cover border border-gray-200 flex-shrink-0">
                @else
                    <div class="h-16 w-16 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center flex-shrink-0">
                        <span class="text-2xl font-bold text-brand-600">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
                    </div>
                @endif
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">{{ $tenant->name }}</h2>
                        <span class="rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">Draft</span>
                    </div>
                    <div class="mt-2 grid grid-cols-2 gap-x-6 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <div><span class="font-medium">CNIC:</span> {{ $tenant->cnic }}</div>
                        <div><span class="font-medium">Phone:</span> {{ $tenant->phone }}</div>
                        @if($tenant->father_name) <div><span class="font-medium">Father:</span> {{ $tenant->father_name }}</div> @endif
                        <div><span class="font-medium">Type:</span> {{ ucfirst($tenant->tenancy_type) }}</div>
                        @if($tenant->email) <div><span class="font-medium">Email:</span> {{ $tenant->email }}</div> @endif
                        <div><span class="font-medium">Adults:</span> {{ $tenant->adults_count }} | <span class="font-medium">Children:</span> {{ $tenant->children_count }}</div>
                    </div>
                </div>
                <a href="{{ route('tenants.showStep', [$tenant, 1]) }}" class="text-xs text-brand-500 hover:underline flex-shrink-0">Edit</a>
            </div>
        </div>

        {{-- Guarantor + Emergency Contacts --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Guarantor</h3>
                    <a href="{{ route('tenants.showStep', [$tenant, 2]) }}" class="text-xs text-brand-500 hover:underline">Edit</a>
                </div>
                @if($guarantor)
                    <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                        <div class="font-medium text-gray-900 dark:text-white/90">{{ $guarantor->name }}</div>
                        <div>{{ $guarantor->cnic }}</div>
                        <div>{{ ucfirst($guarantor->relation) }} · {{ $guarantor->phone }}</div>
                    </div>
                @else
                    <p class="text-sm text-red-400">⚠ Not yet added</p>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Emergency Contacts</h3>
                    <a href="{{ route('tenants.showStep', [$tenant, 2]) }}" class="text-xs text-brand-500 hover:underline">Edit</a>
                </div>
                @forelse($emergencyContacts as $contact)
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $contact->name }}</span>
                        · {{ ucfirst($contact->relation) }} · {{ $contact->phone }}
                    </div>
                @empty
                    <p class="text-sm text-red-400">⚠ Not yet added</p>
                @endforelse
            </div>
        </div>

        {{-- Agreement --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Agreement Terms</h3>
                <a href="{{ route('tenants.showStep', [$tenant, 3]) }}" class="text-xs text-brand-500 hover:underline">Edit</a>
            </div>
            @if($agreement)
                <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <div class="text-gray-500">Unit</div>
                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $tenant->unit?->unit_number ?? '—' }}</div>
                    <div class="text-gray-500">Period</div>
                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $agreement->start_date->format('d M Y') }} → {{ $agreement->end_date->format('d M Y') }}</div>
                    <div class="text-gray-500">Monthly Rent</div>
                    <div class="font-medium text-gray-900 dark:text-white/90">PKR {{ number_format($agreement->monthly_rent) }}</div>
                    @if($agreement->maintenance_charge)
                        <div class="text-gray-500">Maintenance</div>
                        <div class="font-medium text-gray-900 dark:text-white/90">PKR {{ number_format($agreement->maintenance_charge) }}</div>
                    @endif
                    <div class="text-gray-500">Security Deposit</div>
                    <div class="font-medium text-gray-900 dark:text-white/90">PKR {{ number_format($agreement->security_deposit) }}</div>
                    <div class="text-gray-500">Due Day</div>
                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $agreement->payment_due_day }}{{ ['st','nd','rd'][$agreement->payment_due_day - 1] ?? 'th' }} of month</div>
                    <div class="text-gray-500">Grace Period</div>
                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $agreement->grace_period_days ?? 0 }} days</div>
                    @if($agreement->fine_per_day)
                        <div class="text-gray-500">Fine/Day</div>
                        <div class="font-medium text-gray-900 dark:text-white/90">PKR {{ number_format($agreement->fine_per_day) }}</div>
                    @endif
                </div>
            @else
                <p class="text-sm text-red-400">⚠ Not yet configured</p>
            @endif
        </div>

        {{-- Checklists --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Documents</h3>
                    <a href="{{ route('tenants.showStep', [$tenant, 4]) }}" class="text-xs text-brand-500 hover:underline">Edit</a>
                </div>
                @if($docChecklist)
                    @php $checked = $docChecklist->countChecked(); $total = $docChecklist->countTotal(); @endphp
                    <div class="mb-2 flex items-center gap-2">
                        <div class="flex-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                            <div class="h-2 rounded-full bg-brand-500 transition-all" style="width: {{ $total > 0 ? round($checked / $total * 100) : 0 }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $checked }}/{{ $total }}</span>
                    </div>
                    <p class="text-xs text-gray-500">{{ $checked }} of {{ $total }} items confirmed</p>
                @else
                    <p class="text-sm text-yellow-500">Not completed yet</p>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Move-in Inspection</h3>
                    <a href="{{ route('tenants.showStep', [$tenant, 5]) }}" class="text-xs text-brand-500 hover:underline">Edit</a>
                </div>
                @if($moveInChecklist)
                    @php $checked = $moveInChecklist->countChecked(); $total = $moveInChecklist->countTotal(); @endphp
                    <div class="mb-2 flex items-center gap-2">
                        <div class="flex-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                            <div class="h-2 rounded-full bg-green-500 transition-all" style="width: {{ $total > 0 ? round($checked / $total * 100) : 0 }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $checked }}/{{ $total }}</span>
                    </div>
                    <p class="text-xs text-gray-500">{{ $checked }} of {{ $total }} items OK</p>
                    @if($moveInChecklist->flat_condition)
                        <p class="mt-1 text-xs {{ $moveInChecklist->flat_condition === 'good' ? 'text-green-500' : 'text-orange-500' }}">
                            Flat condition: {{ ucfirst(str_replace('_', ' ', $moveInChecklist->flat_condition)) }}
                        </p>
                    @endif
                @else
                    <p class="text-sm text-yellow-500">Not completed yet</p>
                @endif
            </div>
        </div>

        {{-- Confirm Action --}}
        <div class="rounded-2xl border border-brand-200 bg-brand-50 dark:border-brand-900/50 dark:bg-brand-900/10 p-6">
            <h3 class="text-base font-semibold text-brand-700 dark:text-brand-400 mb-1">Ready to Confirm?</h3>
            <p class="text-sm text-brand-600 dark:text-brand-500 mb-4">
                Clicking "Confirm & Save" will activate this tenant, activate the agreement, and mark the unit as occupied.
            </p>
            <div class="flex items-center gap-3">
                <a href="{{ route('tenants.showStep', [$tenant, 5]) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
                <form method="POST" action="{{ route('tenants.confirm', $tenant) }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Confirm & Save
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
