@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants and Agreements</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">{{ $title }}</span>
        </div>
        <div>
            <a href="{{ route('tenants.printStep', [$tenant, 6]) }}" target="_blank"
               class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-brand-600 transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Full Profile & Agreements
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    @include('tenants.wizard._progress', ['currentStep' => $step, 'tenantId' => $tenant->id])
    @include('tenants.wizard._tenant_banner')

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
                <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                    <a href="{{ route('tenants.printStep', [$tenant, 1]) }}" target="_blank"
                       class="text-xs text-brand-500 hover:underline inline-flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </a>
                    <a href="{{ route('tenants.showStep', [$tenant, 1]) }}" class="text-xs text-gray-400 hover:text-brand-500 hover:underline">Edit</a>
                </div>
            </div>
        </div>

        {{-- Partners / Co-Tenants (if shared tenancy) --}}
        @if($partners->isNotEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-6">
                <div class="flex items-center justify-between mb-3 border-b border-gray-100 dark:border-gray-800 pb-2">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Partners / Co-Tenants</h3>
                    <a href="{{ route('tenants.showStep', [$tenant, 1]) }}" class="text-xs text-brand-500 hover:underline">Edit</a>
                </div>
                <div class="space-y-4">
                    @foreach($partners as $index => $partner)
                        <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/[0.01]">
                            <div class="flex items-start gap-4">
                                @if($partner->passport_photo)
                                    <img src="{{ $partner->passport_photo_url }}" class="h-12 w-12 rounded-full object-cover border border-gray-200 flex-shrink-0">
                                @else
                                    <div class="h-12 w-12 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center flex-shrink-0">
                                        <span class="text-sm font-bold text-brand-600">{{ strtoupper(substr($partner->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                                        Partner #{{ $index + 1 }}: {{ $partner->name }}
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1 text-xs text-gray-600 dark:text-gray-400">
                                        <div><span class="font-medium">CNIC:</span> {{ $partner->cnic }}</div>
                                        <div><span class="font-medium">Phone:</span> {{ $partner->phone }}</div>
                                        @if($partner->father_name) <div><span class="font-medium">Father:</span> {{ $partner->father_name }}</div> @endif
                                        @if($partner->whatsapp_number) <div><span class="font-medium">WhatsApp:</span> {{ $partner->whatsapp_number }}</div> @endif
                                        @if($partner->email) <div><span class="font-medium">Email:</span> {{ $partner->email }}</div> @endif
                                        @if($partner->occupation) <div><span class="font-medium">Occupation:</span> {{ $partner->occupation }}</div> @endif
                                        @if($partner->monthly_income) <div><span class="font-medium">Income:</span> PKR {{ number_format($partner->monthly_income) }}</div> @endif
                                        @if($partner->address) <div class="md:col-span-2"><span class="font-medium">Address:</span> {{ $partner->address }}</div> @endif
                                    </div>
                                    <div class="mt-2 flex gap-3 text-xs">
                                        @if($partner->cnic_front_image)
                                            <a href="{{ $partner->cnic_front_url }}" target="_blank" class="text-brand-500 hover:underline">Front CNIC</a>
                                        @endif
                                        @if($partner->cnic_back_image)
                                            <a href="{{ $partner->cnic_back_url }}" target="_blank" class="text-brand-500 hover:underline">Back CNIC</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Guarantor + Emergency Contacts --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Guarantors</h3>
                    <div class="flex items-center gap-3">
                        @if($guarantors->isNotEmpty())
                            <a href="{{ route('tenants.printStep', [$tenant, 2]) }}" target="_blank"
                               class="text-xs text-brand-500 hover:underline inline-flex items-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </a>
                            <span class="text-gray-300 dark:text-gray-700">|</span>
                        @endif
                        <a href="{{ route('tenants.showStep', [$tenant, 2]) }}" class="text-xs text-brand-500 hover:underline">Edit</a>
                    </div>
                </div>
                @forelse($guarantors as $index => $g)
                    <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/[0.01] mb-3 last:mb-0">
                        <div class="flex items-start gap-4">
                            @if($g->photo)
                                <img src="{{ $g->photo_url }}" class="h-12 w-12 rounded-full object-cover border border-gray-200 flex-shrink-0">
                            @else
                                <div class="h-12 w-12 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-bold text-brand-600">{{ strtoupper(substr($g->name, 0, 1)) }}</span>
                                </div>
                            @endif
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                                    Guarantor #{{ $index + 1 }}: {{ $g->name }}
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1 text-xs text-gray-600 dark:text-gray-400">
                                    <div><span class="font-medium">CNIC:</span> {{ $g->cnic }}</div>
                                    <div><span class="font-medium">Phone:</span> {{ $g->phone }}</div>
                                    <div><span class="font-medium">Relation:</span> {{ ucfirst($g->relation) }}</div>
                                    @if($g->occupation) <div><span class="font-medium">Occupation:</span> {{ $g->occupation }}</div> @endif
                                    @if($g->shop_name) <div><span class="font-medium">Shop Name:</span> {{ $g->shop_name }}</div> @endif
                                    <div class="md:col-span-2"><span class="font-medium">Address:</span> {{ $g->address }}</div>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-3 text-xs">
                                    @if($g->photo)
                                        <a href="{{ $g->photo_url }}" target="_blank" class="text-brand-500 hover:underline">Portrait Photo</a>
                                    @endif
                                    @if($g->cnic_front)
                                        <a href="{{ $g->cnic_front_url }}" target="_blank" class="text-brand-500 hover:underline">Front CNIC</a>
                                    @endif
                                    @if($g->cnic_back)
                                        <a href="{{ $g->cnic_back_url }}" target="_blank" class="text-brand-500 hover:underline">Back CNIC</a>
                                    @endif
                                    @if($g->visiting_card)
                                        <a href="{{ $g->visiting_card_url }}" target="_blank" class="text-brand-500 hover:underline">Visiting Card</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-red-400">⚠ No guarantors added</p>
                @endforelse
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
                <div class="flex items-center gap-3">
                    @if($agreement)
                        <a href="{{ route('tenants.printStep', [$tenant, 3]) }}" target="_blank"
                           class="text-xs text-brand-500 hover:underline inline-flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Print
                        </a>
                        <span class="text-gray-300 dark:text-gray-700">|</span>
                    @endif
                    <a href="{{ route('tenants.showStep', [$tenant, 3]) }}" class="text-xs text-brand-500 hover:underline">Edit</a>
                </div>
            </div>
            @if($agreement)
                <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <div class="text-gray-500">Unit</div>
                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $tenant->unit?->unit_number ?? '—' }}</div>
                    <div class="text-gray-500">Period</div>
                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $agreement->start_date ? $agreement->start_date->format('d M Y') : 'Draft' }} → {{ $agreement->end_date ? $agreement->end_date->format('d M Y') : 'Draft' }}</div>
                    <div class="text-gray-500">Monthly Rent</div>
                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $agreement->monthly_rent ? 'PKR ' . number_format($agreement->monthly_rent) : '—' }}</div>
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
                Clicking "Confirm & Save" will activate this tenant, activate the agreement, and mark the unit as rented.
            </p>

            @if(!$agreement || empty($agreement->govt_document))
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                    <strong>⚠️ Government Document Required:</strong> You cannot confirm this agreement because the Government Document (Image/PDF) has not been uploaded in Step 3. Please return to Step 3 and upload it first.
                </div>
            @endif

            <div class="flex items-center gap-3">
                <a href="{{ route('tenants.showStep', [$tenant, 5]) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
                
                @if($agreement && !empty($agreement->govt_document))
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
                @else
                    <button type="button" disabled
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-300 px-6 py-2.5 text-sm font-semibold text-gray-500 shadow-sm cursor-not-allowed dark:bg-gray-800 dark:text-gray-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Confirm & Save (Govt. Document Required)
                    </button>
                    <a href="{{ route('tenants.showStep', [$tenant, 3]) }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-brand-600 transition-colors">
                        Go to Step 3
                    </a>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
