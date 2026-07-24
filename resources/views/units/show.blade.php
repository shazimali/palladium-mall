@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Flat/Shop — {{ $unit->unit_number }}" />

    {{-- KPI Indicator Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
        {{-- Total Earnings --}}
        <div class="rounded-xl border border-gray-150 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total Earnings</p>
                    <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">Rs. {{ number_format($total_earnings, 2) }}</p>
                </div>
                <div class="rounded-lg bg-green-50 p-3 text-green-600 dark:bg-green-900/20 dark:text-green-400">
                    💵
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">Total revenue collected from rent & utilities</p>
        </div>

        {{-- Outstanding Balance --}}
        <div class="rounded-xl border border-gray-150 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total Outstanding</p>
                    <p class="mt-2 text-2xl font-bold {{ $total_outstanding > 0 ? 'text-red-500 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                        Rs. {{ number_format($total_outstanding, 2) }}
                    </p>
                </div>
                <div class="rounded-lg {{ $total_outstanding > 0 ? 'bg-red-50 text-red-500 dark:bg-red-900/20 dark:text-red-400' : 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400' }}">
                    <div class="p-3">⏳</div>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">Remaining unpaid balance dues</p>
        </div>

        {{-- Total Agreements --}}
        <div class="rounded-xl border border-gray-150 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total Agreements</p>
                    <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $agreements_count }}</p>
                </div>
                <div class="rounded-lg bg-blue-50 p-3 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                    📄
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">Total contracts signed till date</p>
        </div>
    </div>

    {{-- Main Tabbed Panel --}}
    <div x-data="{ activeTab: 'overview' }" class="space-y-6">
        {{-- Navigation Tabs --}}
        <div class="flex border-b border-gray-200 dark:border-gray-850">
            <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                class="whitespace-nowrap border-b-2 px-6 py-3.5 text-sm font-medium transition-all">
                Overview & Details
            </button>
            <button @click="activeTab = 'ownership'" :class="activeTab === 'ownership' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                class="whitespace-nowrap border-b-2 px-6 py-3.5 text-sm font-medium transition-all">
                🏢 Ownership History
                <span class="ml-1 inline-flex items-center rounded-full bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $ownerships->count() }}</span>
            </button>
            <button @click="activeTab = 'timeline'" :class="activeTab === 'timeline' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                class="whitespace-nowrap border-b-2 px-6 py-3.5 text-sm font-medium transition-all">
                Timeline History
            </button>
            @if($unit->is_self)
                <button @click="activeTab = 'other_tenant_history'" :class="activeTab === 'other_tenant_history' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 px-6 py-3.5 text-sm font-medium transition-all">
                    🔗 Occupancy History (Other Tenants)
                    <span class="ml-1 inline-flex items-center rounded-full bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $unit->otherTenantHistory->count() }}</span>
                </button>
            @else
                <button @click="activeTab = 'agreements'" :class="activeTab === 'agreements' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="whitespace-nowrap border-b-2 px-6 py-3.5 text-sm font-medium transition-all">
                    Agreements ({{ $agreements_count }})
                </button>
            @endif
            <button @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'border-brand-500 text-brand-600 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                class="whitespace-nowrap border-b-2 px-6 py-3.5 text-sm font-medium transition-all">
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
                            <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.02]">
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $label }}</p>
                                <p class="mt-0.5 {{ $label === 'Unit Number' ? 'unit-badge-lg' : 'text-sm font-medium text-gray-800 dark:text-white/90' }}">{{ $value }}</p>
                            </div>
                        @endforeach
                    </div>

                    @if($unit->notes)
                        <div class="mt-4 rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.02]">
                            <p class="text-xs text-gray-400 dark:text-gray-500">Notes</p>
                            <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $unit->notes }}</p>
                        </div>
                    @endif

                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-800 mt-5">
                        @if(auth()->user()->hasPermission('units.edit') || auth()->user()->isSuperAdmin())
                            <a href="{{ route('units.edit', $unit) }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                                Edit Unit
                            </a>
                        @endif
                        <a href="{{ route('units.index') }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
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
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">Name</p>
                                <p class="text-sm font-medium text-brand-500 hover:underline">
                                    <a href="{{ route('landlords.show', $unit->landlord_id) }}">{{ $unit->landlord->name }}</a>
                                </p>
                            </div>
                            @if($unit->landlord->phone)
                                <div>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">Phone</p>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $unit->landlord->phone }}</p>
                                </div>
                            @endif
                            @if($unit->landlord->email)
                                <div>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">Email</p>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $unit->landlord->email }}</p>
                                </div>
                            @endif
                            @if($unit->landlord->cnic)
                                <div>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">CNIC</p>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $unit->landlord->cnic }}</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-400 dark:text-gray-600">No owner assigned to this unit.</p>
                    @endif
                </x-common.component-card>

                {{-- Tenant or Other Tenant depending on ownership --}}
                @if($unit->is_self)
                    <x-common.component-card title="Current Occupant (Other Tenant)" desc="Other tenant occupancy details">
                        @if($unit->otherTenant)
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">Name</p>
                                    <p class="text-sm font-medium text-brand-500 hover:underline">
                                        <a href="{{ route('other-tenants.show', $unit->otherTenant->id) }}">{{ $unit->otherTenant->name }}</a>
                                    </p>
                                </div>
                                @if($unit->otherTenant->phone)
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">Phone</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $unit->otherTenant->phone }}</p>
                                    </div>
                                @endif
                                @if($unit->otherTenant->whatsapp_number)
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">WhatsApp</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $unit->otherTenant->whatsapp_number }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="py-2 text-center text-gray-400 dark:text-gray-600">
                                <span class="text-2xl">🔑</span>
                                <p class="text-xs mt-1">This unit is currently unoccupied.</p>
                            </div>
                        @endif
                    </x-common.component-card>
                @else
                    <x-common.component-card title="Current Active Tenant" desc="Active tenancy details">
                        @if($unit->tenant)
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">Name</p>
                                    <p class="text-sm font-medium text-brand-500 hover:underline">
                                        <a href="{{ route('tenants.show', $unit->tenant->id) }}">{{ $unit->tenant->name }}</a>
                                    </p>
                                </div>
                                @if($unit->tenant->phone)
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">Phone</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $unit->tenant->phone }}</p>
                                    </div>
                                @endif
                                @if($unit->tenant->email)
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">Email</p>
                                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $unit->tenant->email }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="py-2 text-center text-gray-400 dark:text-gray-600">
                                <span class="text-2xl">🔑</span>
                                <p class="text-xs mt-1">This unit is currently vacant.</p>
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
                        <span class="text-3xl">🏗️</span>
                        <p class="text-sm font-medium mt-2">No ownership records found for this unit.</p>
                        <p class="text-xs mt-1 text-gray-400">Assign a landlord via the Landlord form to start tracking.</p>
                    </div>
                @else
                    <div class="relative pl-6 border-l-2 border-brand-200 dark:border-brand-800 ml-4 space-y-6 py-2">
                        @foreach($ownerships as $ownership)
                            <div class="relative">
                                {{-- Timeline dot --}}
                                <span class="absolute -left-[37px] top-1 flex h-7 w-7 items-center justify-center rounded-full {{ $ownership->is_current ? 'bg-green-500 text-white' : 'bg-white border-2 border-gray-300 dark:bg-gray-900 dark:border-gray-700' }} text-xs shadow-sm">
                                    {{ $ownership->is_current ? '✓' : '↩' }}
                                </span>

                                <div class="rounded-xl border {{ $ownership->is_current ? 'border-green-200 bg-green-50/30 dark:border-green-900/40 dark:bg-green-900/10' : 'border-gray-100 bg-white dark:border-gray-800 dark:bg-white/[0.02]' }} p-4 shadow-sm">
                                    {{-- Header row --}}
                                    <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
                                        <div>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $ownership->is_current ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                                                {{ $ownership->is_current ? 'CURRENT OWNER' : 'TRANSFERRED' }}
                                            </span>
                                            <a href="{{ route('landlords.show', $ownership->landlord_id) }}"
                                               class="ml-2 text-sm font-bold text-brand-500 hover:underline">
                                                {{ $ownership->landlord?->name ?? '—' }}
                                            </a>
                                        </div>
                                        <span class="text-xs text-gray-400 font-medium">{{ $ownership->period }}</span>
                                    </div>

                                    {{-- Nominee --}}
                                    @if($ownership->nominee_name)
                                        <div class="mb-3 rounded-lg bg-blue-50/50 px-3 py-2 dark:bg-blue-900/10">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-400 mb-1">Nominee</p>
                                            <p class="text-sm text-gray-700 dark:text-gray-200 font-medium">{{ $ownership->nominee_full_line }}</p>
                                        </div>
                                    @endif

                                    {{-- Financial + Office --}}
                                    <div class="grid grid-cols-2 gap-x-6 gap-y-2 sm:grid-cols-3 text-xs">
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
                                                <p class="text-gray-400 dark:text-gray-500">{{ $lbl }}</p>
                                                <p class="font-medium text-gray-700 dark:text-gray-200">{{ $val }}</p>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if($ownership->notes)
                                        <div class="mt-2 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600 dark:bg-white/[0.03] dark:text-gray-300">
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
            <div class="rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-white/[0.02] shadow-sm overflow-hidden">

                {{-- Header --}}
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Activity Timeline</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Complete chronological history of this unit</p>
                    </div>
                    @if($timeline->isNotEmpty())
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-600 dark:bg-brand-900/20 dark:text-brand-400">
                            {{ $timeline->count() }} events
                        </span>
                    @endif
                </div>

                @if($timeline->isEmpty())
                    <div class="py-20 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">No events recorded yet</p>
                        <p class="text-xs text-gray-400 mt-1">Events will appear here as activity occurs on this unit.</p>
                    </div>
                @else
                    <div class="px-6 py-6">
                        @php
                            // Group timeline events by year for visual section headers
                            $grouped = $timeline->groupBy(fn($e) => $e['date'] ? $e['date']->format('Y') : 'Unknown');
                        @endphp

                        @foreach($grouped as $year => $events)
                            {{-- Year separator --}}
                            <div class="flex items-center gap-3 mb-6 {{ !$loop->first ? 'mt-10' : '' }}">
                                <span class="text-xs font-bold tracking-widest uppercase text-gray-400 dark:text-gray-500">{{ $year }}</span>
                                <div class="flex-1 h-px bg-gray-100 dark:bg-gray-800"></div>
                                <span class="text-xs text-gray-400 dark:text-gray-600">{{ $events->count() }} {{ Str::plural('event', $events->count()) }}</span>
                            </div>

                            {{-- Events for this year --}}
                            <div class="relative">
                                {{-- Vertical connector line --}}
                                <div class="absolute left-5 top-0 bottom-0 w-px bg-gradient-to-b from-gray-200 via-gray-150 to-transparent dark:from-gray-700 dark:via-gray-800"></div>

                                <div class="space-y-4">
                                    @foreach($events as $event)
                                        @php
                                            // Determine icon bg + ring color by event type
                                            $typeStyles = match($event['type']) {
                                                'agreement'             => ['ring' => 'ring-blue-200 dark:ring-blue-800',   'bg' => 'bg-blue-500',   'dot' => 'bg-blue-100 dark:bg-blue-900/40', 'line' => 'border-blue-200 dark:border-blue-900/50',  'label_bg' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
                                                'agreement_terminated'  => ['ring' => 'ring-red-200 dark:ring-red-800',    'bg' => 'bg-red-500',    'dot' => 'bg-red-100 dark:bg-red-900/40',   'line' => 'border-red-200 dark:border-red-900/50',    'label_bg' => 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400'],
                                                'payment'               => match($event['status']) {
                                                    'paid'    => ['ring' => 'ring-emerald-200 dark:ring-emerald-800', 'bg' => 'bg-emerald-500', 'dot' => 'bg-emerald-100 dark:bg-emerald-900/40', 'line' => 'border-emerald-200 dark:border-emerald-900/50', 'label_bg' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
                                                    'partial' => ['ring' => 'ring-amber-200 dark:ring-amber-800',   'bg' => 'bg-amber-500',   'dot' => 'bg-amber-100 dark:bg-amber-900/40',   'line' => 'border-amber-200 dark:border-amber-900/50',   'label_bg' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
                                                    default   => ['ring' => 'ring-gray-200 dark:ring-gray-700',     'bg' => 'bg-gray-400',    'dot' => 'bg-gray-100 dark:bg-gray-800',        'line' => 'border-gray-200 dark:border-gray-800',        'label_bg' => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'],
                                                },
                                                'ownership_current'     => ['ring' => 'ring-violet-200 dark:ring-violet-800', 'bg' => 'bg-violet-500', 'dot' => 'bg-violet-100 dark:bg-violet-900/40', 'line' => 'border-violet-200 dark:border-violet-900/50', 'label_bg' => 'bg-violet-50 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400'],
                                                'ownership_transfer'    => ['ring' => 'ring-purple-200 dark:ring-purple-800', 'bg' => 'bg-purple-400', 'dot' => 'bg-purple-100 dark:bg-purple-900/40', 'line' => 'border-purple-200 dark:border-purple-900/50', 'label_bg' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'],
                                                'other_tenant_attached' => ['ring' => 'ring-orange-200 dark:ring-orange-800', 'bg' => 'bg-orange-500', 'dot' => 'bg-orange-100 dark:bg-orange-900/40', 'line' => 'border-orange-200 dark:border-orange-900/50', 'label_bg' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'],
                                                'other_tenant_detached' => ['ring' => 'ring-slate-200 dark:ring-slate-700',  'bg' => 'bg-slate-400',  'dot' => 'bg-slate-100 dark:bg-slate-800',      'line' => 'border-slate-200 dark:border-slate-800',      'label_bg' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'],
                                                default                 => ['ring' => 'ring-gray-200 dark:ring-gray-700',     'bg' => 'bg-gray-400',    'dot' => 'bg-gray-100 dark:bg-gray-800',        'line' => 'border-gray-200 dark:border-gray-800',        'label_bg' => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'],
                                            };

                                            // SVG icons per type
                                            $svgIcon = match($event['type']) {
                                                'agreement'             => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
                                                'agreement_terminated'  => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
                                                'payment'               => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
                                                'ownership_current'     => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
                                                'ownership_transfer'    => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>',
                                                'other_tenant_attached' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>',
                                                'other_tenant_detached' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="22" y1="11" x2="16" y2="11"/></svg>',
                                                default                 => '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>',
                                            };

                                            // Parse detail string into key-value chips
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
                                            <div class="flex-1 mb-2 rounded-2xl border {{ $typeStyles['line'] }} bg-white dark:bg-gray-900/50 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">

                                                {{-- Card top accent bar --}}
                                                <div class="h-0.5 w-full {{ $typeStyles['bg'] }} opacity-60"></div>

                                                <div class="px-4 py-3.5">
                                                    {{-- Title row --}}
                                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">
                                                                {{ $event['title'] }}
                                                            </h4>
                                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $typeStyles['label_bg'] }}">
                                                                {{ ucfirst(str_replace('_', ' ', $event['status'])) }}
                                                            </span>
                                                        </div>
                                                        <div class="flex-shrink-0 text-right">
                                                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                                {{ $event['date'] ? $event['date']->format('d M Y') : '—' }}
                                                            </span>
                                                            @if($event['date'])
                                                                <p class="text-[10px] text-gray-400 mt-0.5">
                                                                    {{ $event['date']->diffForHumans() }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Subtitle --}}
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">
                                                        {{ $event['subtitle'] }}
                                                    </p>

                                                    {{-- Details as chips --}}
                                                    @if(!empty($detailPairs))
                                                        <div class="mt-3 flex flex-wrap gap-2">
                                                            @foreach($detailPairs as $pair)
                                                                @if($pair['value'] && $pair['value'] !== '—' && $pair['value'] !== 'Rs. 0')
                                                                    <div class="flex items-center gap-1 rounded-lg {{ $typeStyles['dot'] }} px-2.5 py-1.5">
                                                                        @if($pair['label'])
                                                                            <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ $pair['label'] }}:</span>
                                                                        @endif
                                                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $pair['value'] }}</span>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    {{-- Footer action --}}
                                                    <div class="mt-3 flex justify-end">
                                                        <a href="{{ $event['url'] }}"
                                                           class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-brand-500 hover:text-brand-700 dark:hover:text-brand-300 transition-colors group/link">
                                                            View Details
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 group-hover/link:translate-x-0.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
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
                        <span class="text-3xl">📄</span>
                        <p class="text-sm font-medium mt-2">No agreements signed for this unit yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto font-sans">
                        <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                            <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-white">
                                <tr>
                                    <th class="px-4 py-3">Tenant</th>
                                    <th class="px-4 py-3">Monthly Rent</th>
                                    <th class="px-4 py-3">Security Deposit</th>
                                    <th class="px-4 py-3">Start Date</th>
                                    <th class="px-4 py-3">End Date</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($unit->agreements as $agreement)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">
                                            {{ $agreement->tenant->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90">
                                            {{ $agreement->monthly_rent ? 'Rs. ' . number_format($agreement->monthly_rent) : '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $agreement->security_deposit ? 'Rs. ' . number_format($agreement->security_deposit) : '—' }}
                                        </td>
                                        <td class="px-4 py-3">{{ $agreement->start_date ? $agreement->start_date->format('d M Y') : 'Draft' }}</td>
                                        <td class="px-4 py-3">{{ $agreement->end_date ? $agreement->end_date->format('d M Y') : 'Draft' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $agreement->status_badge_class }}">
                                                {{ ucfirst($agreement->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('agreements.show', $agreement->id) }}" class="text-brand-500 hover:underline font-semibold text-xs">
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
                            <span class="text-3xl">🔗</span>
                            <p class="text-sm font-medium mt-2">No occupant history records found for this unit.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto font-sans border border-gray-200 rounded-xl dark:border-gray-800">
                            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-white">
                                    <tr>
                                        <th class="px-4 py-3">Tenant Name</th>
                                        <th class="px-4 py-3">Phone</th>
                                        <th class="px-4 py-3">Attached At</th>
                                        <th class="px-4 py-3">Detached At</th>
                                        <th class="px-4 py-3">Duration</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($unit->otherTenantHistory as $history)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white">
                                                {{ $history->otherTenant->name ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                {{ $history->otherTenant->phone ?? '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-green-600 dark:text-green-400 font-medium">
                                                {{ $history->attached_at ? $history->attached_at->format('d M Y') : '—' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                @if($history->detached_at)
                                                    <span class="text-red-500 dark:text-red-400">
                                                        {{ $history->detached_at->format('d M Y') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                                        Current Occupant
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-xs text-gray-400">
                                                @php
                                                    $end = $history->detached_at ?? now();
                                                    $diff = $history->attached_at ? $history->attached_at->diffInDays($end) : 0;
                                                @endphp
                                                {{ $diff }} day{{ $diff === 1 ? '' : 's' }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                @if($history->other_tenant_id)
                                                    <a href="{{ route('other-tenants.show', $history->other_tenant_id) }}" class="text-brand-500 hover:underline font-semibold text-xs">
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
                        <span class="text-3xl">💰</span>
                        <p class="text-sm font-medium mt-2">No billing records found for this unit.</p>
                    </div>
                @else
                    <div class="overflow-x-auto font-sans">
                        <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                            <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-white">
                                <tr>
                                    <th class="px-4 py-3">Month</th>
                                    <th class="px-4 py-3">Due Date</th>
                                    <th class="px-4 py-3">Tenant</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Amount Due</th>
                                    <th class="px-4 py-3">Amount Paid</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Paid At</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($unit->payments as $payment)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white">
                                            {{ $payment->month->format('M Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-xs">{{ $payment->due_date->format('d M Y') }}</td>
                                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">{{ $payment->tenant->name ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $payment->type_badge_class }}">
                                                {{ ucfirst($payment->type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">Rs. {{ number_format($payment->amount) }}</td>
                                        <td class="px-4 py-3 text-green-600 font-medium">Rs. {{ number_format($payment->amount_paid) }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $payment->status_badge_class }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500">
                                            {{ $payment->paid_at ? $payment->paid_at->format('d M Y') : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('payments.show', $payment->id) }}" class="text-brand-500 hover:underline font-semibold text-xs">
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