@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-6">

    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants and Agreements</a>
        <span>/</span>
        <span class="text-gray-800 dark:text-white/90">{{ $tenant->name }}</span>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Hero ─────────────────────────────────────────────────────────── --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
            {{-- Avatar --}}
            <div class="flex-shrink-0">
                @if($tenant->passport_photo)
                    <img src="{{ $tenant->passport_photo_url }}" class="h-20 w-20 rounded-full object-cover border-2 border-brand-200 shadow-sm">
                @else
                    <div class="h-20 w-20 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center shadow-sm">
                        <span class="text-3xl font-bold text-white">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3 mb-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">{{ $tenant->name }}</h1>
                    @if($tenant->status === 'draft')
                        <span class="rounded-full bg-yellow-100 px-3 py-0.5 text-xs font-semibold text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">Draft</span>
                    @elseif($tenant->status === 'active')
                        <span class="rounded-full bg-green-100 px-3 py-0.5 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
                    @else
                        <span class="rounded-full bg-red-100 px-3 py-0.5 text-xs font-semibold text-red-600 dark:bg-red-900/30 dark:text-red-400">Inactive</span>
                    @endif
                    @if($tenant->unit)
                        <span class="rounded-full bg-blue-100 px-3 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            Unit {{ $tenant->unit->unit_number }}
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                    <span>{{ $tenant->cnic }}</span>
                    <span>{{ $tenant->phone }}</span>
                    @if($tenant->email) <span>{{ $tenant->email }}</span> @endif
                    @if($tenant->occupation) <span>{{ $tenant->occupation }}</span> @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                <a href="{{ route('tenants.showStep', [$tenant, 1]) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                @if($tenant->status === 'active')
                    <a href="{{ route('tenants.moveOut.create', $tenant) }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-medium text-white hover:bg-orange-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Move Out
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Info Grid ────────────────────────────────────────────────────── --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

        {{-- Personal --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Personal</h3>
                <a href="{{ route('tenants.printStep', [$tenant, 1]) }}" target="_blank"
                   class="text-xs text-brand-500 hover:underline inline-flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </a>
            </div>
            <dl class="space-y-2 text-sm">
                @if($tenant->father_name) <div class="flex justify-between"><dt class="text-gray-500">Father</dt><dd class="font-medium text-gray-800 dark:text-gray-200">{{ $tenant->father_name }}</dd></div> @endif
                @if($tenant->date_of_birth) <div class="flex justify-between"><dt class="text-gray-500">DOB</dt><dd class="font-medium text-gray-800 dark:text-gray-200">{{ $tenant->date_of_birth->format('d M Y') }}</dd></div> @endif
                @if($tenant->gender) <div class="flex justify-between"><dt class="text-gray-500">Gender</dt><dd class="font-medium text-gray-800 dark:text-gray-200">{{ ucfirst($tenant->gender) }}</dd></div> @endif
                @if($tenant->marital_status) <div class="flex justify-between"><dt class="text-gray-500">Marital</dt><dd class="font-medium text-gray-800 dark:text-gray-200">{{ ucfirst($tenant->marital_status) }}</dd></div> @endif
                <div class="flex justify-between"><dt class="text-gray-500">Adults</dt><dd class="font-medium text-gray-800 dark:text-gray-200">{{ $tenant->adults_count }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Children</dt><dd class="font-medium text-gray-800 dark:text-gray-200">{{ $tenant->children_count }}</dd></div>
            </dl>
        </div>

        {{-- Guarantors --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Guarantors</h3>
                @if($tenant->guarantors->isNotEmpty())
                    <a href="{{ route('tenants.printStep', [$tenant, 2]) }}" target="_blank"
                       class="text-xs text-brand-500 hover:underline inline-flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </a>
                @endif
            </div>
            @forelse($tenant->guarantors as $index => $g)
                <div class="mb-4 last:mb-0 pb-4 last:pb-0 border-b border-gray-100 last:border-0 dark:border-gray-800">
                    <div class="flex gap-3 items-start">
                        @if($g->photo)
                            <img src="{{ $g->photo_url }}" class="h-9 w-9 rounded-full object-cover border border-gray-200 flex-shrink-0">
                        @else
                            <div class="h-9 w-9 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-brand-600">{{ strtoupper(substr($g->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        <div class="flex-1">
                            <div class="font-medium text-gray-800 dark:text-gray-200">#{{ $index + 1 }}: {{ $g->name }}</div>
                            <div class="text-gray-500 text-xs mt-1 space-y-0.5">
                                <div><span class="font-semibold text-gray-700 dark:text-gray-300">CNIC:</span> {{ $g->cnic }}</div>
                                <div><span class="font-semibold text-gray-700 dark:text-gray-300">Phone:</span> {{ $g->phone }}</div>
                                <div><span class="font-semibold text-gray-700 dark:text-gray-300">Relation:</span> {{ ucfirst($g->relation) }}</div>
                                @if($g->occupation) <div><span class="font-semibold text-gray-700 dark:text-gray-300">Occupation:</span> {{ $g->occupation }}</div> @endif
                                @if($g->shop_name) <div><span class="font-semibold text-gray-700 dark:text-gray-300">Shop Name:</span> {{ $g->shop_name }}</div> @endif
                                <div><span class="font-semibold text-gray-700 dark:text-gray-300">Address:</span> {{ $g->address }}</div>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs">
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
                <p class="text-sm text-gray-400">Not added yet</p>
            @endforelse
        </div>

        {{-- Emergency Contacts --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Emergency Contacts</h3>
            @forelse($tenant->emergencyContacts as $contact)
                <div class="mb-2 text-sm">
                    <div class="font-medium text-gray-800 dark:text-gray-200">{{ $contact->name }}</div>
                    <div class="text-gray-500 text-xs">{{ ucfirst($contact->relation) }} · {{ $contact->phone }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-400">Not added yet</p>
            @endforelse
        </div>

        {{-- Partners / Co-Tenants --}}
        @if($tenant->partners->isNotEmpty())
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
            <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Partners / Co-Tenants</h3>
            @foreach($tenant->partners as $index => $partner)
                <div class="mb-4 last:mb-0 pb-4 last:pb-0 border-b border-gray-100 last:border-0 dark:border-gray-800">
                    <div class="flex gap-3 items-start">
                        @if($partner->passport_photo)
                            <img src="{{ $partner->passport_photo_url }}" class="h-9 w-9 rounded-full object-cover border border-gray-200 flex-shrink-0">
                        @else
                            <div class="h-9 w-9 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-brand-600">{{ strtoupper(substr($partner->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        <div class="flex-1">
                            <div class="font-medium text-gray-800 dark:text-gray-200">#{{ $index + 1 }}: {{ $partner->name }}</div>
                            <div class="text-gray-500 text-xs mt-1 space-y-0.5">
                                <div><span class="font-semibold text-gray-700 dark:text-gray-300">CNIC:</span> {{ $partner->cnic }}</div>
                                <div><span class="font-semibold text-gray-700 dark:text-gray-300">Phone:</span> {{ $partner->phone }}</div>
                                @if($partner->father_name) <div><span class="font-semibold text-gray-700 dark:text-gray-300">Father:</span> {{ $partner->father_name }}</div> @endif
                                @if($partner->whatsapp_number) <div><span class="font-semibold text-gray-700 dark:text-gray-300">WhatsApp:</span> {{ $partner->whatsapp_number }}</div> @endif
                                @if($partner->email) <div><span class="font-semibold text-gray-700 dark:text-gray-300">Email:</span> {{ $partner->email }}</div> @endif
                                @if($partner->occupation) <div><span class="font-semibold text-gray-700 dark:text-gray-300">Occupation:</span> {{ $partner->occupation }}</div> @endif
                                @if($partner->monthly_income) <div><span class="font-semibold text-gray-700 dark:text-gray-300">Income:</span> PKR {{ number_format($partner->monthly_income) }}</div> @endif
                                @if($partner->address) <div><span class="font-semibold text-gray-700 dark:text-gray-300">Address:</span> {{ $partner->address }}</div> @endif
                            </div>
                            <div class="mt-2 flex gap-2 text-xs">
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
        @endif

    </div>

    {{-- ── Active Agreement ─────────────────────────────────────────────── --}}
    @if($tenant->activeAgreement)
    @php $ag = $tenant->activeAgreement; @endphp
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Active Agreement</h3>
            <div class="flex items-center gap-3">
                <a href="{{ route('tenants.printStep', [$tenant, 3]) }}" target="_blank"
                   class="text-xs text-brand-500 hover:underline inline-flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </a>
                <span class="text-gray-300 dark:text-gray-700">|</span>
                <a href="{{ route('tenants.showStep', [$tenant, 3]) }}" class="text-xs text-brand-500 hover:underline">Edit Terms</a>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm sm:grid-cols-4">
            <div><div class="text-gray-400 text-xs">Start Date</div><div class="font-medium text-gray-800 dark:text-gray-200">{{ $ag->start_date->format('d M Y') }}</div></div>
            <div><div class="text-gray-400 text-xs">End Date</div><div class="font-medium text-gray-800 dark:text-gray-200">{{ $ag->end_date->format('d M Y') }}</div></div>
            <div><div class="text-gray-400 text-xs">Monthly Rent</div><div class="font-medium text-gray-800 dark:text-gray-200">PKR {{ number_format($ag->monthly_rent) }}</div></div>
            @if($ag->maintenance_charge)
            <div><div class="text-gray-400 text-xs">Maintenance</div><div class="font-medium text-gray-800 dark:text-gray-200">PKR {{ number_format($ag->maintenance_charge) }}</div></div>
            @endif
            <div><div class="text-gray-400 text-xs">Security Deposit</div><div class="font-medium text-gray-800 dark:text-gray-200">PKR {{ number_format($ag->security_deposit) }}</div></div>
            <div><div class="text-gray-400 text-xs">Due Day</div><div class="font-medium text-gray-800 dark:text-gray-200">{{ $ag->payment_due_day }}th of month</div></div>
            <div><div class="text-gray-400 text-xs">Grace Period</div><div class="font-medium text-gray-800 dark:text-gray-200">{{ $ag->grace_period_days ?? 0 }} days</div></div>
            @if($ag->fine_per_day)
            <div><div class="text-gray-400 text-xs">Fine/Day</div><div class="font-medium text-gray-800 dark:text-gray-200">PKR {{ number_format($ag->fine_per_day) }}</div></div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Documents + Move-In Checklist Progress ───────────────────────── --}}
    @php
        $moc = $tenant->moveInChecklists->where('type', 'move_out')->first();
        $gridCols = $moc ? 'sm:grid-cols-2 lg:grid-cols-3' : 'sm:grid-cols-2';
    @endphp
    <div class="mb-6 grid grid-cols-1 gap-4 {{ $gridCols }}">

        {{-- Doc Checklist --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Documents</h3>
                <a href="{{ route('tenants.showStep', [$tenant, 4]) }}" class="text-xs text-brand-500 hover:underline">Update</a>
            </div>
            @if($tenant->documentChecklist)
                @php $checked = $tenant->documentChecklist->countChecked(); $total = $tenant->documentChecklist->countTotal(); @endphp
                <div class="mb-2 flex items-center gap-3">
                    <div class="flex-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-2 rounded-full bg-brand-500 transition-all" style="width: {{ $total > 0 ? round($checked / $total * 100) : 0 }}%"></div>
                    </div>
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $checked }}/{{ $total }}</span>
                </div>
                <p class="text-xs text-gray-400">{{ $checked }} of {{ $total }} document items confirmed</p>
            @else
                <p class="text-sm text-yellow-500">Not yet completed</p>
            @endif
        </div>

        {{-- Move-in --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Move-in Inspection</h3>
                <a href="{{ route('tenants.showStep', [$tenant, 5]) }}" class="text-xs text-brand-500 hover:underline">Update</a>
            </div>
            @php $mic = $tenant->moveInChecklists->where('type','move_in')->first(); @endphp
            @if($mic)
                @php $checked = $mic->countChecked(); $total = $mic->countTotal(); @endphp
                <div class="mb-2 flex items-center gap-3">
                    <div class="flex-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-2 rounded-full bg-green-500 transition-all" style="width: {{ $total > 0 ? round($checked / $total * 100) : 0 }}%"></div>
                    </div>
                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $checked }}/{{ $total }}</span>
                </div>
                @if($mic->flat_condition)
                    <p class="text-xs {{ $mic->flat_condition === 'good' ? 'text-green-500' : 'text-orange-500' }}">
                        Condition: {{ ucfirst(str_replace('_', ' ', $mic->flat_condition)) }}
                    </p>
                @endif
            @else
                <p class="text-sm text-yellow-500">Not yet completed</p>
            @endif
        </div>

        {{-- Move-out --}}
        @if($moc)
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Move-out Inspection</h3>
                <a href="{{ route('tenants.printMoveOut', $tenant) }}" target="_blank"
                   class="text-xs text-brand-500 hover:underline inline-flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </a>
            </div>
            @php $checked = $moc->countChecked(); $total = $moc->countTotal(); @endphp
            <div class="mb-2 flex items-center gap-3">
                <div class="flex-1 h-2 rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="h-2 rounded-full bg-orange-500 transition-all" style="width: {{ $total > 0 ? round($checked / $total * 100) : 0 }}%"></div>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $checked }}/{{ $total }}</span>
            </div>
            @if($moc->flat_condition)
                <p class="text-xs {{ $moc->flat_condition === 'good' ? 'text-green-500' : 'text-orange-500' }}">
                    Condition: {{ ucfirst(str_replace('_', ' ', $moc->flat_condition)) }}
                </p>
            @endif
            @if($moc->deposit_deduction > 0)
                <p class="text-xs text-red-500 mt-1">Deduction: PKR {{ number_format($moc->deposit_deduction) }}</p>
            @endif
        </div>
        @endif

    </div>

    {{-- ── All Agreements ────────────────────────────────────────────────── --}}
    @if($tenant->agreements->count() > 0)
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 p-5">
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Agreement History</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-gray-400">
                    <tr>
                        <th class="py-2 pr-4">Period</th>
                        <th class="py-2 pr-4">Rent/mo</th>
                        <th class="py-2 pr-4">Deposit</th>
                        <th class="py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($tenant->agreements as $ag)
                    <tr>
                        <td class="py-2 pr-4 text-gray-800 dark:text-gray-200">{{ $ag->start_date->format('d M Y') }} → {{ $ag->end_date->format('d M Y') }}</td>
                        <td class="py-2 pr-4">PKR {{ number_format($ag->monthly_rent) }}</td>
                        <td class="py-2 pr-4">PKR {{ number_format($ag->security_deposit) }}</td>
                        <td class="py-2">
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                {{ match($ag->status) {
                                    'active' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'draft'  => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'expired','terminated' => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                                    default  => 'bg-gray-100 text-gray-500',
                                } }}">{{ ucfirst($ag->status) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection