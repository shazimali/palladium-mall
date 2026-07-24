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
        <div x-show="activeTab === 'timeline'" class="max-w-3xl">
            <x-common.component-card title="Historical Performance Timeline" desc="Chronological performance history of the unit from day one">
                @if($timeline->isEmpty())
                    <div class="py-12 text-center text-gray-400">
                        <span class="text-3xl">🗓️</span>
                        <p class="text-sm font-medium mt-2">No historical events recorded for this unit.</p>
                    </div>
                @else
                    <div class="relative pl-6 border-l-2 border-gray-200 dark:border-gray-800 ml-4 space-y-8 py-2">
                        @foreach($timeline as $event)
                            <div class="relative">
                                {{-- Icon badge positioned over the vertical line --}}
                                <span class="absolute -left-[37px] top-1 flex h-7 w-7 items-center justify-center rounded-full bg-white text-xs border border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                                    {{ $event['icon'] }}
                                </span>

                                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-white/[0.02]">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <h4 class="text-sm font-semibold text-gray-800 dark:text-white">
                                            {{ $event['title'] }}
                                        </h4>
                                        <span class="text-xs text-gray-400 font-medium">
                                            {{ $event['date'] ? $event['date']->format('d M Y') : '—' }}
                                        </span>
                                    </div>
                                    <p class="text-xs font-medium text-gray-500 mt-1 dark:text-gray-400">
                                        {{ $event['subtitle'] }}
                                    </p>
                                    <p class="text-xs text-gray-600 mt-2 bg-gray-50/50 p-2.5 rounded-lg border border-gray-100/50 dark:bg-black/20 dark:border-gray-800/20 dark:text-gray-300">
                                        {{ $event['details'] }}
                                    </p>
                                    <div class="mt-3 flex items-center justify-between">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-medium {{ $event['status_badge'] }}">
                                            {{ ucfirst($event['status']) }}
                                        </span>
                                        <a href="{{ $event['url'] }}" class="text-[11px] font-semibold text-brand-500 hover:underline">
                                            View Details &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-common.component-card>
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