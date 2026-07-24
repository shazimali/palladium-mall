@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Flat/Shop — {{ $unit->unit_number }}" />

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
    <div x-data="{ activeTab: 'overview' }" class="space-y-6">
        {{-- Navigation Tabs --}}
        <div class="flex flex-wrap border-b-2 border-gray-200 dark:border-gray-800 gap-2">
            <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-brand-500 text-brand-600 dark:text-brand-400 font-black' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 font-bold'"
                class="whitespace-nowrap border-b-4 px-6 py-4 text-base sm:text-lg transition-all cursor-pointer">
                Overview &amp; Details
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
                            ['Status',        ucfirst($unit->status), null],
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
                                @if($unit->otherTenant->whatsapp_number)
                                    <div>
                                        <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">WhatsApp</p>
                                        <p class="mt-1 text-base sm:text-lg font-bold text-gray-900 dark:text-white">{{ $unit->otherTenant->whatsapp_number }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="py-4 text-center text-gray-400 dark:text-gray-600">
                                <span class="text-3xl">🔑</span>
                                <p class="text-sm font-semibold mt-1">This unit is currently unoccupied.</p>
                            </div>
                        @endif
                    </x-common.component-card>
                @else
                    <x-common.component-card title="Current Active Tenant" desc="Active tenancy details">
                        @if($unit->tenant)
                            <div class="space-y-4">
                                <div>
                                    <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</p>
                                    <p class="mt-1 text-base sm:text-lg font-bold text-brand-600 hover:underline">
                                        <a href="{{ route('tenants.show', $unit->tenant->id) }}">{{ $unit->tenant->name }}</a>
                                    </p>
                                </div>
                                @if($unit->tenant->phone)
                                    <div>
                                        <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Phone</p>
                                        <p class="mt-1 text-base sm:text-lg font-bold text-gray-900 dark:text-white">{{ $unit->tenant->phone }}</p>
                                    </div>
                                @endif
                                @if($unit->tenant->email)
                                    <div>
                                        <p class="text-xs sm:text-sm font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Email</p>
                                        <p class="mt-1 text-base sm:text-lg font-bold text-gray-900 dark:text-white">{{ $unit->tenant->email }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="py-4 text-center text-gray-400 dark:text-gray-600">
                                <span class="text-3xl">🔑</span>
                                <p class="text-sm font-semibold mt-1">This unit is currently vacant.</p>
                            </div>
                        @endif
                    </x-common.component-card>
                @endif
            </div>
        </div>

        {{-- ── OWNERSHIP HISTORY TAB ──────────────────────────────── --}}
        <div x-show="activeTab === 'ownership'">
            <x-common.component-card title="Ownership History" desc="Full record of every landlord who has owned this unit from day one">
                @if($ownerships->isEmpty())
                    <div class="py-12 text-center text-gray-400">
                        <span class="text-4xl">🏗️</span>
                        <p class="text-base font-bold mt-2">No ownership records found for this unit.</p>
                        <p class="text-sm font-medium mt-1 text-gray-400">Assign a landlord via the Landlord form to start tracking.</p>
                    </div>
                @else
                    <div class="relative pl-6 border-l-2 border-brand-200 dark:border-brand-800 ml-4 space-y-6 py-2">
                        @foreach($ownerships as $ownership)
                            <div class="relative">
                                {{-- Timeline dot --}}
                                <span class="absolute -left-[37px] top-1 flex h-8 w-8 items-center justify-center rounded-full {{ $ownership->is_current ? 'bg-green-500 text-white' : 'bg-white border-2 border-gray-300 dark:bg-gray-900 dark:border-gray-700' }} text-sm font-black shadow-sm">
                                    {{ $ownership->is_current ? '✓' : '↩' }}
                                </span>

                                <div class="rounded-2xl border-2 {{ $ownership->is_current ? 'border-green-300 bg-green-50/40 dark:border-green-900/40 dark:bg-green-900/10' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.02]' }} p-5 shadow-sm">
                                    {{-- Header row --}}
                                    <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
                                        <div>
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-black {{ $ownership->is_current ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                                {{ $ownership->is_current ? 'CURRENT OWNER' : 'TRANSFERRED' }}
                                            </span>
                                            <a href="{{ route('landlords.show', $ownership->landlord_id) }}"
                                               class="ml-3 text-base sm:text-lg font-black text-brand-600 hover:underline">
                                                {{ $ownership->landlord?->name ?? '—' }}
                                            </a>
                                        </div>
                                        <span class="text-sm text-gray-500 font-bold">{{ $ownership->period }}</span>
                                    </div>

                                    {{-- Nominee --}}
                                    @if($ownership->nominee_name)
                                        <div class="mb-4 rounded-xl bg-blue-50/70 p-3 dark:bg-blue-900/20 border-2 border-blue-100 dark:border-blue-900/30">
                                            <p class="text-xs font-black uppercase tracking-wider text-blue-600 dark:text-blue-400 mb-1">Nominee</p>
                                            <p class="text-base text-gray-900 dark:text-gray-100 font-bold">{{ $ownership->nominee_full_line }}</p>
                                        </div>
                                    @endif

                                    {{-- Financial + Office --}}
                                    <div class="grid grid-cols-2 gap-x-6 gap-y-3 sm:grid-cols-3 text-sm">
                                        @foreach([
                                            ['Total Amount',   $ownership->total_amount    ? 'Rs. '.number_format($ownership->total_amount)    : '—'],
                                            ['Received',       $ownership->received_amount ? 'Rs. '.number_format($ownership->received_amount) : '—'],
                                            ['Credit / Bal.',  $ownership->credit_amount   ? 'Rs. '.number_format($ownership->credit_amount)   : '—'],
                                            ['Received From',  $ownership->received_from ?? '—'],
                                            ['Approved By',    $ownership->approved_by ?? '—'],
                                            ['Received By',    $ownership->received_by ?? '—'],
                                            ['Approved Date',  $ownership->approved_date ? $ownership->approved_date->format('d M Y') : '—'],
                                        ] as [$lbl, $val])
                                            <div>
                                                <p class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $lbl }}</p>
                                                <p class="font-bold text-gray-900 dark:text-gray-100 text-sm sm:text-base mt-0.5">{{ $val }}</p>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if($ownership->notes)
                                        <div class="mt-3 rounded-xl bg-gray-50 p-3 text-sm font-semibold text-gray-700 dark:bg-white/[0.03] dark:text-gray-300">
                                            {{ $ownership->notes }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-common.component-card>
        </div>

        {{-- ── TIMELINE TAB ─────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'timeline'">
            <div class="rounded-2xl border-2 border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.02] shadow-sm overflow-hidden">

                {{-- Header --}}
                <div class="px-6 py-5 border-b-2 border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white">Activity Timeline</h3>
                        <p class="text-sm font-semibold text-gray-500 mt-0.5">Complete chronological history of this unit</p>
                    </div>
                    @if($timeline->isNotEmpty())
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-3.5 py-1.5 text-xs sm:text-sm font-black text-brand-600 dark:bg-brand-900/20 dark:text-brand-400">
                            {{ $timeline->count() }} events
                        </span>
                    @endif
                </div>

                @if($timeline->isEmpty())
                    <div class="py-20 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0z" />
                            </svg>
                        </div>
                        <p class="text-base font-bold text-gray-600 dark:text-gray-400">No events recorded yet</p>
                        <p class="text-sm font-semibold text-gray-400 mt-1">Events will appear here as activity occurs on this unit.</p>
                    </div>
                @else
                    <div class="px-6 py-6">
                        @php
                            $grouped = $timeline->groupBy(fn($e) => $e['date'] ? $e['date']->format('Y') : 'Unknown');
                        @endphp

                        @foreach($grouped as $year => $events)
                            {{-- Year separator --}}
                            <div class="flex items-center gap-3 mb-6 {{ !$loop->first ? 'mt-10' : '' }}">
                                <span class="text-sm font-black tracking-widest uppercase text-gray-500 dark:text-gray-400">{{ $year }}</span>
                                <div class="flex-1 h-0.5 bg-gray-200 dark:bg-gray-800"></div>
                                <span class="text-xs font-bold text-gray-400 dark:text-gray-500">{{ $events->count() }} {{ Str::plural('event', $events->count()) }}</span>
                            </div>

                            {{-- Events for this year --}}
                            <div class="relative">
                                {{-- Vertical connector line --}}
                                <div class="absolute left-5 top-0 bottom-0 w-0.5 bg-gradient-to-b from-gray-300 via-gray-200 to-transparent dark:from-gray-700 dark:via-gray-800"></div>

                                <div class="space-y-5">
                                    @foreach($events as $event)
                                        @php
                                            $typeStyles = match($event['type']) {
                                                'agreement'             => ['ring' => 'ring-blue-200 dark:ring-blue-800',   'bg' => 'bg-blue-500',   'dot' => 'bg-blue-100 dark:bg-blue-900/40', 'line' => 'border-blue-200 dark:border-blue-900/50',  'label_bg' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'],
                                                'agreement_terminated'  => ['ring' => 'ring-red-200 dark:ring-red-800',    'bg' => 'bg-red-500',    'dot' => 'bg-red-100 dark:bg-red-900/40',   'line' => 'border-red-200 dark:border-red-900/50',    'label_bg' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'],
                                                'payment'               => match($event['status']) {
                                                    'paid'    => ['ring' => 'ring-emerald-200 dark:ring-emerald-800', 'bg' => 'bg-emerald-500', 'dot' => 'bg-emerald-100 dark:bg-emerald-900/40', 'line' => 'border-emerald-200 dark:border-emerald-900/50', 'label_bg' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'],
                                                    'partial' => ['ring' => 'ring-amber-200 dark:ring-amber-800',   'bg' => 'bg-amber-500',   'dot' => 'bg-amber-100 dark:bg-amber-900/40',   'line' => 'border-amber-200 dark:border-amber-900/50',   'label_bg' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'],
                                                    default   => ['ring' => 'ring-gray-200 dark:ring-gray-700',     'bg' => 'bg-gray-400',    'dot' => 'bg-gray-100 dark:bg-gray-800',        'line' => 'border-gray-200 dark:border-gray-800',        'label_bg' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'],
                                                },
                                                'ownership_current'     => ['ring' => 'ring-violet-200 dark:ring-violet-800', 'bg' => 'bg-violet-500', 'dot' => 'bg-violet-100 dark:bg-violet-900/40', 'line' => 'border-violet-200 dark:border-violet-900/50', 'label_bg' => 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-300'],
                                                'ownership_transfer'    => ['ring' => 'ring-purple-200 dark:ring-purple-800', 'bg' => 'bg-purple-400', 'dot' => 'bg-purple-100 dark:bg-purple-900/40', 'line' => 'border-purple-200 dark:border-purple-900/50', 'label_bg' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'],
                                                'other_tenant_attached' => ['ring' => 'ring-orange-200 dark:ring-orange-800', 'bg' => 'bg-orange-500', 'dot' => 'bg-orange-100 dark:bg-orange-900/40', 'line' => 'border-orange-200 dark:border-orange-900/50', 'label_bg' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300'],
                                                'other_tenant_detached' => ['ring' => 'ring-slate-200 dark:ring-slate-700',  'bg' => 'bg-slate-400',  'dot' => 'bg-slate-100 dark:bg-slate-800',      'line' => 'border-slate-200 dark:border-slate-800',      'label_bg' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'],
                                                default                 => ['ring' => 'ring-gray-200 dark:ring-gray-700',     'bg' => 'bg-gray-400',    'dot' => 'bg-gray-100 dark:bg-gray-800',        'line' => 'border-gray-200 dark:border-gray-800',        'label_bg' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'],
                                            };

                                            $svgIcon = match($event['type']) {
                                                'agreement'             => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
                                                'agreement_terminated'  => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
                                                'payment'               => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
                                                'ownership_current'     => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
                                                'ownership_transfer'    => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>',
                                                'other_tenant_attached' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>',
                                                'other_tenant_detached' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="22" y1="11" x2="16" y2="11"/></svg>',
                                                default                 => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>',
                                            };

                                            $detailPairs = [];
                                            foreach(explode(' | ', $event['details']) as $part) {
                                                $pieces = explode(': ', $part, 2);
                                                $detailPairs[] = count($pieces) === 2 ? ['label' => trim($pieces[0]), 'value' => trim($pieces[1])] : ['label' => '', 'value' => trim($part)];
                                            }
                                        @endphp

                                        <div class="relative flex gap-4 group">
                                            {{-- Circle icon on the line --}}
                                            <div class="relative z-10 flex-shrink-0">
                                                <div class="h-10 w-10 rounded-full {{ $typeStyles['bg'] }} ring-4 {{ $typeStyles['ring'] }} flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-200">
                                                    {!! $svgIcon !!}
                                                </div>
                                            </div>

                                            {{-- Event card --}}
                                            <div class="flex-1 mb-2 rounded-2xl border-2 {{ $typeStyles['line'] }} bg-white dark:bg-gray-900/50 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
                                                <div class="h-1 w-full {{ $typeStyles['bg'] }} opacity-70"></div>

                                                <div class="px-5 py-4">
                                                    {{-- Title row --}}
                                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                                        <div class="flex items-center gap-2.5 flex-wrap">
                                                            <h4 class="text-base font-black text-gray-900 dark:text-white leading-tight">
                                                                {{ $event['title'] }}
                                                            </h4>
                                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-black uppercase tracking-wider {{ $typeStyles['label_bg'] }}">
                                                                {{ ucfirst(str_replace('_', ' ', $event['status'])) }}
                                                            </span>
                                                        </div>
                                                        <div class="flex-shrink-0 text-right">
                                                            <span class="text-xs sm:text-sm font-bold text-gray-600 dark:text-gray-300">
                                                                {{ $event['date'] ? $event['date']->format('d M Y') : '—' }}
                                                            </span>
                                                            @if($event['date'])
                                                                <p class="text-xs text-gray-400 mt-0.5 font-medium">
                                                                    {{ $event['date']->diffForHumans() }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Subtitle --}}
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-bold">
                                                        {{ $event['subtitle'] }}
                                                    </p>

                                                    {{-- Details as chips --}}
                                                    @if(!empty($detailPairs))
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            @foreach($detailPairs as $pair)
                                                                @if($pair['value'] && $pair['value'] !== '—' && $pair['value'] !== 'Rs. 0')
                                                                    <div class="flex items-center gap-1.5 rounded-xl {{ $typeStyles['dot'] }} px-3 py-1.5 border border-gray-200/50">
                                                                        @if($pair['label'])
                                                                            <span class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $pair['label'] }}:</span>
                                                                        @endif
                                                                        <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100">{{ $pair['value'] }}</span>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    {{-- Footer action --}}
                                                    <div class="mt-4 flex justify-end">
                                                        <a href="{{ $event['url'] }}"
                                                           class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-black text-brand-600 hover:text-brand-800 dark:hover:text-brand-300 transition-colors group/link">
                                                            View Details
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover/link:translate-x-0.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                                <polyline points="9 18 15 12 9 6"/>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ── AGREEMENTS TAB ───────────────────────────────────────────── --}}
        <div x-show="activeTab === 'agreements'">
            <x-common.component-card title="Tenancy Agreements" desc="Active and historical leases for this unit">
                @if($unit->agreements->isEmpty())
                    <div class="py-12 text-center text-gray-400">
                        <span class="text-4xl">📄</span>
                        <p class="text-base font-bold mt-2">No agreements signed for this unit yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto font-sans border-2 border-gray-200 rounded-2xl dark:border-gray-800">
                        <table class="w-full text-base text-left text-gray-700 dark:text-gray-300">
                            <thead class="text-xs sm:text-sm uppercase font-black bg-brand-500 text-white dark:bg-brand-600 dark:text-white">
                                <tr>
                                    <th class="px-5 py-4 text-white">Tenant</th>
                                    <th class="px-5 py-4 text-white">Monthly Rent</th>
                                    <th class="px-5 py-4 text-white">Security Deposit</th>
                                    <th class="px-5 py-4 text-white">Start Date</th>
                                    <th class="px-5 py-4 text-white">End Date</th>
                                    <th class="px-5 py-4 text-white">Status</th>
                                    <th class="px-5 py-4 text-right text-white">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($unit->agreements as $agreement)
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
    </div>
@endsection