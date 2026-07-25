@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Flat/Shop — {{ $unit->unit_number }}" />

    {{-- Vacant Breaker Alert Banner --}}
    @if($unit->hasVacantBreakerWarning())
        <div class="mb-6 rounded-2xl border-2 border-red-300 bg-red-50 p-5 text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300 shadow-md">
            <div class="flex items-start gap-4">
                <span class="text-3xl">⚠️</span>
                <div class="flex-1">
                    <h3 class="text-lg font-black text-red-900 dark:text-red-200">Breaker Alert: Electricity Breaker is ON on Vacant Unit!</h3>
                    <p class="mt-1 text-sm font-semibold text-red-700 dark:text-red-300">
                        This unit is currently vacant, but its electricity breaker status is set to <strong>ON</strong>.
                        To prevent power theft, unauthorized usage, and meter corruption, please turn off the physical breaker and record the inspection.
                    </p>
                    <div class="mt-3">
                        <button type="button" onclick="openBreakerModal('off')" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-xs font-black text-white hover:bg-red-700 transition-colors shadow-sm cursor-pointer">
                            ⚡ Record Breaker OFF Inspection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- KPI Indicator Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
        {{-- Total Earnings --}}
        <div class="rounded-2xl border-2 border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Earnings</p>
                    <p class="mt-2 text-3xl font-black text-green-600 dark:text-green-400">Rs. {{ number_format($total_earnings, 2) }}</p>
                </div>
                <div class="rounded-2xl bg-green-50 p-4 text-green-600 dark:bg-green-900/20 dark:text-green-400 text-2xl">
                    💵
                </div>
            </div>
            <p class="mt-2 text-sm font-semibold text-gray-500 dark:text-gray-400">Total revenue collected from rent &amp; utilities</p>
        </div>

        {{-- Outstanding Balance --}}
        <div class="rounded-2xl border-2 border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Outstanding</p>
                    <p class="mt-2 text-3xl font-black {{ $total_outstanding > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                        Rs. {{ number_format($total_outstanding, 2) }}
                    </p>
                </div>
                <div class="rounded-2xl {{ $total_outstanding > 0 ? 'bg-red-50 text-red-500 dark:bg-red-900/20 dark:text-red-400' : 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400' }} p-4 text-2xl">
                    ⏳
                </div>
            </div>
            <p class="mt-2 text-sm font-semibold text-gray-500 dark:text-gray-400">Remaining unpaid balance dues</p>
        </div>

        {{-- Total Agreements --}}
        <div class="rounded-2xl border-2 border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Agreements</p>
                    <p class="mt-2 text-3xl font-black text-blue-600 dark:text-blue-400">{{ $agreements_count }}</p>
                </div>
                <div class="rounded-2xl bg-blue-50 p-4 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 text-2xl">
                    📄
                </div>
            </div>
            <p class="mt-2 text-sm font-semibold text-gray-500 dark:text-gray-400">Total contracts signed till date</p>
        </div>
    </div>

    {{-- Main Tabbed Panel --}}
    <div x-data="{ activeTab: 'overview', showBreakerModal: false, breakerStatus: '{{ $unit->breaker_status }}' }"
        x-init="window.openBreakerModal = (status) => { if (status) breakerStatus = status; showBreakerModal = true; }"
        class="space-y-6">

        {{-- Navigation Tabs --}}
        <div class="flex flex-wrap border-b-2 border-gray-200 dark:border-gray-800 gap-2">
            <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-black' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 font-bold'"
                class="whitespace-nowrap border-b-4 px-6 py-4 text-base sm:text-lg transition-all cursor-pointer">
                Overview &amp; Details
            </button>
            <button @click="activeTab = 'breaker_inspections'" :class="activeTab === 'breaker_inspections' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-black' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 font-bold'"
                class="whitespace-nowrap border-b-4 px-6 py-4 text-base sm:text-lg transition-all cursor-pointer">
                ⚡ Breaker Inspections
                <span class="ml-1.5 inline-flex items-center rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-black text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $unit->breakerInspections->count() }}</span>
            </button>
            <button @click="activeTab = 'ownership'" :class="activeTab === 'ownership' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-black' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 font-bold'"
                class="whitespace-nowrap border-b-4 px-6 py-4 text-base sm:text-lg transition-all cursor-pointer">
                🏢 Ownership History
                <span class="ml-1.5 inline-flex items-center rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-black text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $ownerships->count() }}</span>
            </button>
            <button @click="activeTab = 'timeline'" :class="activeTab === 'timeline' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-black' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 font-bold'"
                class="whitespace-nowrap border-b-4 px-6 py-4 text-base sm:text-lg transition-all cursor-pointer">
                Timeline History
            </button>
            @if($unit->is_self)
                <button @click="activeTab = 'other_tenant_history'" :class="activeTab === 'other_tenant_history' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-black' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 font-bold'"
                    class="whitespace-nowrap border-b-4 px-6 py-4 text-base sm:text-lg transition-all cursor-pointer">
                    🔗 Occupancy History (Other Tenants)
                    <span class="ml-1.5 inline-flex items-center rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-black text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $unit->otherTenantHistory->count() }}</span>
                </button>
            @else
                <button @click="activeTab = 'agreements'" :class="activeTab === 'agreements' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-black' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 font-bold'"
                    class="whitespace-nowrap border-b-4 px-6 py-4 text-base sm:text-lg transition-all cursor-pointer">
                    Agreements ({{ $agreements_count }})
                </button>
            @endif
            <button @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-black' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 font-bold'"
                class="whitespace-nowrap border-b-4 px-6 py-4 text-base sm:text-lg transition-all cursor-pointer">
                Payments History
            </button>
        </div>

        {{-- Tab Content Blocks --}}

        {{-- ── OVERVIEW TAB ─────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'overview'" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Unit Details --}}
            <div class="lg:col-span-2">
                <x-common.component-card title="Unit Specifications" desc="Technical and physical specifications of the unit">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach([
                            ['Flat/Shop Number',   $unit->unit_number, null],
                            ['File No.',      $unit->file_no ?? '—', null],
                            ['Type',          ucfirst($unit->type), null],
                            ['Floor',         $unit->floor->name ?? '—', null],
                            ['Block',         $unit->block->name ?? '—', null],
                            ['Area / Zone',   $unit->area->name  ?? '—', null],
                            ['Area (sq.ft.)', $unit->area_sqft ? $unit->area_sqft.' sq.ft.' : '—', null],
                            ['Status',        ($unit->is_self && $unit->otherTenant) ? 'Rented' : ucfirst($unit->status), null],
                            ['Creation Date', $unit->date ? $unit->date->format('d M Y') : '—', null],
                            ['Elec. Meter',   $meters['electricity']->meter_ref_no ?? '—', null],
                            ['Water Meter',   $meters['water']->meter_ref_no ?? '—', null],
                            ['Gas Meter',     $meters['gas']->meter_ref_no ?? '—', null],
                        ] as [$label, $value, $url])
                            <div class="rounded-2xl border-2 border-gray-100 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                                <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $label }}</p>
                                <p class="mt-1 {{ $label === 'Flat/Shop Number' ? 'unit-badge-lg text-lg font-black' : 'text-base sm:text-lg font-bold text-gray-900 dark:text-white' }}">{{ $value }}</p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Breaker Status Card --}}
                    <div class="mt-6 rounded-2xl border-2 border-gray-200 bg-white p-5 shadow-xs dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <div>
                                <p class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Electricity Breaker Status</p>
                                <div class="mt-2 flex items-center gap-3">
                                    @if($unit->isBreakerOn())
                                        <span class="inline-flex items-center gap-2 rounded-xl bg-green-100 px-3.5 py-1.5 text-sm font-black text-green-800 dark:bg-green-950/60 dark:text-green-300 border border-green-300 dark:border-green-800">
                                            <span class="h-2.5 w-2.5 rounded-full bg-green-500 animate-pulse"></span>
                                            ⚡ BREAKER ON
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-xl bg-red-100 px-3.5 py-1.5 text-sm font-black text-red-800 dark:bg-red-950/60 dark:text-red-300 border border-red-300 dark:border-red-800">
                                            <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                                            ⚡ BREAKER OFF
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <button type="button" @click="openBreakerModal()" class="inline-flex items-center gap-2 rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-xs font-extrabold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors cursor-pointer shadow-xs">
                                ⚡ Update Inspection / Reading
                            </button>
                        </div>
                        
                        @php
                            $latestInsp = $unit->breakerInspections->first();
                        @endphp
                        <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-800">
                            @if($latestInsp)
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs text-gray-600 dark:text-gray-400">
                                    <div><span class="font-bold text-gray-800 dark:text-gray-200">Last Reading:</span> {{ number_format($latestInsp->meter_reading, 2) }} kWh</div>
                                    <div><span class="font-bold text-gray-800 dark:text-gray-200">Officer:</span> {{ $latestInsp->inspection_officer_name }}</div>
                                    <div><span class="font-bold text-gray-800 dark:text-gray-200">Date:</span> {{ $latestInsp->inspected_at ? $latestInsp->inspected_at->format('d M Y h:i A') : $latestInsp->created_at->format('d M Y h:i A') }}</div>
                                </div>
                            @else
                                <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">⚠️ No baseline inspection photo/reading recorded yet. Click Update Inspection to record baseline.</p>
                            @endif
                        </div>
                    </div>

                    @if($unit->notes)
                        <div class="mt-4 rounded-2xl border-2 border-gray-100 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                            <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Notes</p>
                            <p class="mt-1 text-base font-bold text-gray-800 dark:text-gray-200">{{ $unit->notes }}</p>
                        </div>
                    @endif

                    <div class="flex items-center gap-4 pt-5 border-t-2 border-gray-100 dark:border-gray-800 mt-6">
                        @if(auth()->user()->hasPermission('units.edit') || auth()->user()->isSuperAdmin())
                            <a href="{{ route('units.edit', $unit) }}"
                                class="inline-flex items-center gap-2.5 rounded-2xl bg-brand-600 px-7 py-3.5 text-base font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors">
                                Edit Unit
                            </a>
                        @endif
                        <a href="{{ route('units.index') }}"
                            class="inline-flex items-center rounded-2xl border-2 border-gray-300 px-7 py-3.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-colors">
                            Back to Units
                        </a>
                    </div>
                </x-common.component-card>
            </div>

            {{-- Landlord & Current Tenant --}}
            <div class="space-y-6">
                {{-- Landlord (Owner) --}}
                <x-common.component-card title="Owner Details" desc="Current owner of the unit">
                    @if($unit->landlord)
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</p>
                                <p class="mt-1 text-base sm:text-lg font-bold text-brand-600 hover:underline">
                                    <a href="{{ route('landlords.show', $unit->landlord_id) }}">{{ $unit->landlord->name }}</a>
                                </p>
                            </div>
                            @if($unit->landlord->phone)
                                <div>
                                    <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Phone</p>
                                    <p class="mt-1 text-base sm:text-lg font-bold text-gray-900 dark:text-white">{{ $unit->landlord->phone }}</p>
                                </div>
                            @endif
                            @if($unit->landlord->email)
                                <div>
                                    <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="mt-1 text-base sm:text-lg font-bold text-gray-900 dark:text-white">{{ $unit->landlord->email }}</p>
                                </div>
                            @endif
                            @if($unit->landlord->cnic)
                                <div>
                                    <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">CNIC</p>
                                    <p class="mt-1 text-base sm:text-lg font-bold text-gray-900 dark:text-white">{{ $unit->landlord->cnic }}</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-base font-semibold text-gray-400 dark:text-gray-600">No owner assigned to this unit.</p>
                    @endif
                </x-common.component-card>

                {{-- Tenant or Other Tenant depending on ownership --}}
                @if($unit->is_self)
                    <x-common.component-card title="Current Occupant (Other Tenant)" desc="Other tenant occupancy details">
                        @if($unit->otherTenant)
                            <div class="space-y-4">
                                <div>
                                    <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</p>
                                    <p class="mt-1 text-base sm:text-lg font-bold text-brand-600 hover:underline">
                                        <a href="{{ route('other-tenants.show', $unit->otherTenant->id) }}">{{ $unit->otherTenant->name }}</a>
                                    </p>
                                </div>
                                @if($unit->otherTenant->phone)
                                    <div>
                                        <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Phone</p>
                                        <p class="mt-1 text-base sm:text-lg font-bold text-gray-900 dark:text-white">{{ $unit->otherTenant->phone }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-base font-semibold text-gray-400 dark:text-gray-600">No other tenant attached to this unit.</p>
                        @endif
                    </x-common.component-card>
                @endif
            </div>
        </div>

        {{-- ── BREAKER INSPECTIONS TAB ───────────────────────────────────────── --}}
        <div x-show="activeTab === 'breaker_inspections'">
            <x-common.component-card title="Electricity Breaker Inspections &amp; Readings" desc="Audit trail of all meter readings, officer statements, and photo proof for breaker ON/OFF actions">
                <div class="mb-4 flex justify-end">
                    <button type="button" @click="openBreakerModal()" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-xs font-black text-white hover:bg-brand-700 transition-colors shadow-sm cursor-pointer">
                        ⚡ Add New Breaker Inspection
                    </button>
                </div>

                @if($unit->breakerInspections->isEmpty())
                    <div class="py-12 text-center text-gray-400">
                        <span class="text-4xl">⚡</span>
                        <p class="text-base font-bold mt-2">No breaker inspection records found for this unit.</p>
                        <p class="text-xs text-gray-400 mt-1">Record the baseline meter photo &amp; reading using the button above.</p>
                    </div>
                @else
                    <div class="overflow-x-auto font-sans border-2 border-gray-200 rounded-2xl dark:border-gray-800">
                        <table class="w-full text-base text-left text-gray-700 dark:text-gray-300">
                            <thead class="text-xs sm:text-sm uppercase font-black bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="px-5 py-4">Inspected Date</th>
                                    <th class="px-5 py-4">Breaker Status</th>
                                    <th class="px-5 py-4">Meter Reading</th>
                                    <th class="px-5 py-4">Meter Picture</th>
                                    <th class="px-5 py-4">Inspection Officer</th>
                                    <th class="px-5 py-4">Officer Statement</th>
                                    <th class="px-5 py-4">Signed PDF Document</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($unit->breakerInspections as $inspection)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-5 py-4 font-bold text-gray-900 dark:text-white whitespace-nowrap text-sm">
                                            {{ $inspection->inspected_at ? $inspection->inspected_at->format('d M Y h:i A') : $inspection->created_at->format('d M Y h:i A') }}
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            @if($inspection->breaker_status === 'on')
                                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-black bg-green-100 text-green-800 dark:bg-green-950/60 dark:text-green-300">
                                                    ⚡ ON
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-black bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300">
                                                    ⚡ OFF
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 font-black text-gray-900 dark:text-white text-base">
                                            {{ number_format($inspection->meter_reading, 2) }} <span class="text-xs font-normal text-gray-500">kWh</span>
                                        </td>
                                        <td class="px-5 py-4">
                                            @if($inspection->meter_image)
                                                <a href="{{ $inspection->meter_image_url }}" target="_blank" class="inline-block">
                                                    <img src="{{ $inspection->meter_image_url }}" alt="Meter Photo" class="h-12 w-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shadow-xs hover:scale-105 transition-transform">
                                                </a>
                                            @else
                                                <span class="text-xs text-gray-400">No Photo</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 font-extrabold text-gray-900 dark:text-white text-sm">
                                            {{ $inspection->inspection_officer_name }}
                                        </td>
                                        <td class="px-5 py-4 text-xs font-medium text-gray-600 dark:text-gray-300 max-w-xs">
                                            "{{ $inspection->officer_statement }}"
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            @if($inspection->signed_inspection_doc)
                                                <a href="{{ $inspection->signed_inspection_doc_url }}" target="_blank"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-brand-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-brand-400">
                                                    📄 View Signed PDF
                                                </a>
                                            @else
                                                <span class="text-xs text-gray-400">No Signed Doc</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-common.component-card>
        </div>

        {{-- ── OWNERSHIP TAB ─────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'ownership'">
            <x-common.component-card title="Ownership History" desc="History of landlords for this unit">
                @if($ownerships->isEmpty())
                    <div class="py-12 text-center text-gray-400">
                        <span class="text-4xl">🏢</span>
                        <p class="text-base font-bold mt-2">No ownership history records found for this unit.</p>
                    </div>
                @else
                    <div class="overflow-x-auto font-sans border-2 border-gray-200 rounded-2xl dark:border-gray-800">
                        <table class="w-full text-base text-left text-gray-700 dark:text-gray-300">
                            <thead class="text-xs sm:text-sm uppercase font-black bg-brand-500 text-white dark:bg-brand-600 dark:text-white">
                                <tr>
                                    <th class="px-5 py-4 text-white">Owner Name</th>
                                    <th class="px-5 py-4 text-white">CNIC</th>
                                    <th class="px-5 py-4 text-white">Phone</th>
                                    <th class="px-5 py-4 text-white">Transfer Date</th>
                                    <th class="px-5 py-4 text-white">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($ownerships as $ownership)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-5 py-4 font-black text-gray-900 dark:text-white">
                                            {{ $ownership->landlord->name ?? '—' }}
                                        </td>
                                        <td class="px-5 py-4 font-bold">{{ $ownership->landlord->cnic ?? '—' }}</td>
                                        <td class="px-5 py-4 font-bold">{{ $ownership->landlord->phone ?? '—' }}</td>
                                        <td class="px-5 py-4 font-bold">{{ $ownership->created_at->format('d M Y') }}</td>
                                        <td class="px-5 py-4">
                                            @if($ownership->is_current)
                                                <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-black bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Current Owner</span>
                                            @else
                                                <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-black bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">Previous Owner</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-common.component-card>
        </div>

        {{-- ── TIMELINE TAB ─────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'timeline'">
            <x-common.component-card title="Timeline History" desc="Chronological timeline of events for this unit">
                @if($timeline->isEmpty())
                    <div class="py-12 text-center text-gray-400">
                        <span class="text-4xl">🕒</span>
                        <p class="text-base font-bold mt-2">No timeline records found for this unit.</p>
                    </div>
                @else
                    <div class="relative border-l-2 border-brand-500 ml-4 space-y-6">
                        @foreach($timeline as $event)
                            <div class="relative pl-6">
                                <span class="absolute -left-2.5 top-1.5 h-5 w-5 rounded-full bg-brand-500 border-4 border-white dark:border-gray-900"></span>
                                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-black uppercase text-brand-600 dark:text-brand-400">{{ $event['type'] }}</span>
                                        <span class="text-xs text-gray-400 font-bold">{{ $event['date'] ? \Carbon\Carbon::parse($event['date'])->format('d M Y') : '—' }}</span>
                                    </div>
                                    <h4 class="text-base font-black text-gray-900 dark:text-white mt-1">{{ $event['title'] }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $event['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-common.component-card>
        </div>

        @if($unit->is_self)
            {{-- ── OTHER TENANT HISTORY TAB ──────────────────────────────────── --}}
            <div x-show="activeTab === 'other_tenant_history'">
                <x-common.component-card title="Occupancy History (Other Tenants)" desc="Detailed history of attached and detached tenants for this unit">
                    @if($unit->otherTenantHistory->isEmpty())
                        <div class="py-12 text-center text-gray-400">
                            <span class="text-4xl">🔗</span>
                            <p class="text-base font-bold mt-2">No occupant history records found for this unit.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto font-sans border-2 border-gray-200 rounded-2xl dark:border-gray-800">
                            <table class="w-full text-base text-left text-gray-700 dark:text-gray-300">
                                <thead class="text-xs sm:text-sm uppercase font-black bg-brand-500 text-white dark:bg-brand-600 dark:text-white">
                                    <tr>
                                        <th class="px-5 py-4 text-white">Tenant Name</th>
                                        <th class="px-5 py-4 text-white">Phone</th>
                                        <th class="px-5 py-4 text-white">Attached At</th>
                                        <th class="px-5 py-4 text-white">Detached At</th>
                                        <th class="px-5 py-4 text-white">Duration</th>
                                        <th class="px-5 py-4 text-right text-white">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($unit->otherTenantHistory as $history)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                            <td class="px-5 py-4 font-extrabold text-gray-900 dark:text-white">
                                                {{ $history->otherTenant->name ?? '—' }}
                                            </td>
                                            <td class="px-5 py-4 font-bold">
                                                {{ $history->otherTenant->phone ?? '—' }}
                                            </td>
                                            <td class="px-5 py-4 text-green-600 dark:text-green-400 font-extrabold">
                                                {{ $history->attached_at ? $history->attached_at->format('d M Y') : '—' }}
                                            </td>
                                            <td class="px-5 py-4 font-bold">
                                                @if($history->detached_at)
                                                    <span class="text-red-500 dark:text-red-400 font-extrabold">
                                                        {{ $history->detached_at->format('d M Y') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-black bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                                        Current Occupant
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-sm font-bold text-gray-500">
                                                @php
                                                    $end = $history->detached_at ?? now();
                                                    $diff = $history->attached_at ? $history->attached_at->diffInDays($end) : 0;
                                                @endphp
                                                {{ $diff }} day{{ $diff === 1 ? '' : 's' }}
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                @if($history->other_tenant_id)
                                                    <a href="{{ route('other-tenants.show', $history->other_tenant_id) }}" class="text-brand-600 hover:underline font-black text-sm">
                                                        View Profile
                                                    </a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-common.component-card>
            </div>
        @else
            {{-- ── AGREEMENTS TAB ─────────────────────────────────────────────── --}}
            <div x-show="activeTab === 'agreements'">
                <x-common.component-card title="Agreements History" desc="All tenancy contracts signed for this unit">
                    @if($agreements->isEmpty())
                        <div class="py-12 text-center text-gray-400">
                            <span class="text-4xl">📄</span>
                            <p class="text-base font-bold mt-2">No agreements signed yet.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto font-sans border-2 border-gray-200 rounded-2xl dark:border-gray-800">
                            <table class="w-full text-base text-left text-gray-700 dark:text-gray-300">
                                <thead class="text-xs sm:text-sm uppercase font-black bg-brand-500 text-white dark:bg-brand-600 dark:text-white">
                                    <tr>
                                        <th class="px-5 py-4 text-white">Tenant</th>
                                        <th class="px-5 py-4 text-white">Rent</th>
                                        <th class="px-5 py-4 text-white">Security Deposit</th>
                                        <th class="px-5 py-4 text-white">Start Date</th>
                                        <th class="px-5 py-4 text-white">End Date</th>
                                        <th class="px-5 py-4 text-white">Status</th>
                                        <th class="px-5 py-4 text-right text-white">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($agreements as $agreement)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                            <td class="px-5 py-4 font-extrabold text-gray-900 dark:text-white">
                                                {{ $agreement->tenant->name ?? '—' }}
                                            </td>
                                            <td class="px-5 py-4 font-black text-gray-900 dark:text-white">
                                                {{ $agreement->monthly_rent ? 'Rs. ' . number_format($agreement->monthly_rent) : '—' }}
                                            </td>
                                            <td class="px-5 py-4 font-bold">
                                                {{ $agreement->security_deposit ? 'Rs. ' . number_format($agreement->security_deposit) : '—' }}
                                            </td>
                                            <td class="px-5 py-4 font-bold">{{ $agreement->start_date ? $agreement->start_date->format('d M Y') : 'Draft' }}</td>
                                            <td class="px-5 py-4 font-bold">{{ $agreement->end_date ? $agreement->end_date->format('d M Y') : 'Draft' }}</td>
                                            <td class="px-5 py-4">
                                                <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-black {{ $agreement->status_badge_class }}">
                                                    {{ ucfirst($agreement->status) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                <a href="{{ route('agreements.show', $agreement->id) }}" class="text-brand-600 hover:underline font-black text-sm">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-common.component-card>
            </div>
        @endif

        {{-- ── PAYMENTS TAB ────────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'payments'">
            <x-common.component-card title="Payments History" desc="History of payments and billings for this unit">
                @if($unit->payments->isEmpty())
                    <div class="py-12 text-center text-gray-400">
                        <span class="text-4xl">💰</span>
                        <p class="text-base font-bold mt-2">No billing records found for this unit.</p>
                    </div>
                @else
                    <div class="overflow-x-auto font-sans border-2 border-gray-200 rounded-2xl dark:border-gray-800">
                        <table class="w-full text-base text-left text-gray-700 dark:text-gray-300">
                            <thead class="text-xs sm:text-sm uppercase font-black bg-brand-500 text-white dark:bg-brand-600 dark:text-white">
                                <tr>
                                    <th class="px-5 py-4 text-white">Month</th>
                                    <th class="px-5 py-4 text-white">Due Date</th>
                                    <th class="px-5 py-4 text-white">Tenant</th>
                                    <th class="px-5 py-4 text-white">Type</th>
                                    <th class="px-5 py-4 text-white">Amount Due</th>
                                    <th class="px-5 py-4 text-white">Amount Paid</th>
                                    <th class="px-5 py-4 text-white">Status</th>
                                    <th class="px-5 py-4 text-white">Paid At</th>
                                    <th class="px-5 py-4 text-right text-white">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($unit->payments as $payment)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-5 py-4 font-black text-gray-900 dark:text-white">
                                            {{ $payment->month->format('M Y') }}
                                        </td>
                                        <td class="px-5 py-4 font-bold text-sm">{{ $payment->due_date->format('d M Y') }}</td>
                                        <td class="px-5 py-4 font-extrabold text-gray-900 dark:text-white">{{ $payment->tenant->name ?? '—' }}</td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-black {{ $payment->type_badge_class }}">
                                                {{ ucfirst($payment->type) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 font-black text-gray-900 dark:text-white">Rs. {{ number_format($payment->amount) }}</td>
                                        <td class="px-5 py-4 text-green-600 dark:text-green-400 font-black">Rs. {{ number_format($payment->amount_paid) }}</td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-black {{ $payment->status_badge_class }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 font-bold text-sm text-gray-500">
                                            {{ $payment->paid_at ? $payment->paid_at->format('d M Y') : '—' }}
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <a href="{{ route('payments.show', $payment->id) }}" class="text-brand-600 hover:underline font-black text-sm">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-common.component-card>
        </div>

        {{-- ── BREAKER INSPECTION MODAL ────────────────────────────────────── --}}
        <div x-show="showBreakerModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);">
            <div @click.outside="showBreakerModal = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800 mb-4">
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white">⚡ Electricity Breaker Inspection</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Unit {{ $unit->unit_number }} &mdash; Meter Reading &amp; Officer Verification</p>
                    </div>
                    <button type="button" @click="showBreakerModal = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10">
                        ✕
                    </button>
                </div>

                <form action="{{ route('units.breaker-inspections.store', $unit) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    {{-- Breaker Status --}}
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-600 dark:text-gray-400 mb-1.5">Breaker Status *</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center justify-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                :class="breakerStatus === 'on' ? 'border-green-500 bg-green-50 text-green-900 dark:bg-green-950/40 dark:text-green-300 font-bold' : 'border-gray-200 text-gray-600 dark:border-gray-700'">
                                <input type="radio" name="breaker_status" value="on" x-model="breakerStatus" class="sr-only">
                                <span>⚡ BREAKER ON</span>
                            </label>
                            <label class="flex items-center justify-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all"
                                :class="breakerStatus === 'off' ? 'border-red-500 bg-red-50 text-red-900 dark:bg-red-950/40 dark:text-red-300 font-bold' : 'border-gray-200 text-gray-600 dark:border-gray-700'">
                                <input type="radio" name="breaker_status" value="off" x-model="breakerStatus" class="sr-only">
                                <span>⚡ BREAKER OFF</span>
                            </label>
                        </div>
                    </div>

                    {{-- Meter Reading --}}
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-600 dark:text-gray-400 mb-1">Current Meter Reading (kWh) *</label>
                        <input type="number" step="0.01" name="meter_reading" required placeholder="e.g. 12450.50" value="{{ $latestInsp?->meter_reading ?? '' }}"
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white font-bold focus:ring-2 focus:ring-brand-500/30">
                    </div>

                    {{-- Meter Image Upload --}}
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-600 dark:text-gray-400 mb-1">Meter Reading Picture (Photo Proof)</label>
                        <input type="file" name="meter_image" accept="image/*"
                            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-950 dark:file:text-brand-300">
                    </div>

                    {{-- Inspection Officer --}}
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-600 dark:text-gray-400 mb-1">Inspection Officer *</label>
                        <select name="inspection_person_id" required
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white font-semibold">
                            <option value="">Select Inspection Officer</option>
                            @foreach($inspectionPersons as $person)
                                <option value="{{ $person->id }}">{{ $person->name }} ({{ $person->role ?? 'Inspector' }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Upload Signed Inspection PDF --}}
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-600 dark:text-gray-400 mb-1">Upload Signed Inspection PDF / Form (Optional)</label>
                        <input type="file" name="signed_inspection_doc" accept="application/pdf,image/*"
                            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-950 dark:file:text-brand-300">
                    </div>

                    {{-- Officer Statement --}}
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-600 dark:text-gray-400 mb-1">Officer Statement / Verification Notes *</label>
                        <textarea name="officer_statement" rows="3" required placeholder="e.g., I inspect and confirm that electricity meter reading is recorded and breaker status is verified."
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">I inspect and confirm electricity meter reading and breaker status.</textarea>
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="showBreakerModal = false" class="rounded-xl border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="rounded-xl bg-brand-600 px-5 py-2 text-xs font-black text-white hover:bg-brand-700 transition-colors shadow-sm">
                            Save Inspection Record
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection