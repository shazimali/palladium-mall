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
                                ['Flat/Shop Number', $unit->unit_number, null],
                                ['File No.', $unit->file_no ?? '—', null],
                                ['Type', ucfirst($unit->type), null],
                                ['Floor', $unit->floor->name ?? '—', null],
                                ['Block', $unit->block->name ?? '—', null],
                                ['Area / Zone', $unit->area->name ?? '—', null],
                                ['Area (sq.ft.)', $unit->area_sqft ? $unit->area_sqft . ' sq.ft.' : '—', null],
                                ['Status', ($unit->is_self && $unit->otherTenant) ? 'Rented' : ucfirst($unit->status), null],
                                ['Creation Date', $unit->date ? $unit->date->format('d M Y') : '—', null],
                                ['Elec. Meter', $meters['electricity']->meter_ref_no ?? '—', null],
                                ['Water Meter', $meters['water']->meter_ref_no ?? '—', null],
                                ['Gas Meter', $meters['gas']->meter_ref_no ?? '—', null],
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
                            </div>
                        @else
                            <p class="text-base font-semibold text-gray-400 dark:text-gray-600">No other tenant attached to this unit.</p>
                        @endif
                    </x-common.component-card>
                @endif
            </div>
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
                                    @if(!empty($event['subtitle']))
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-0.5">{{ $event['subtitle'] }}</p>
                                    @endif
                                    @if(!empty($event['details']) || !empty($event['desc']))
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $event['details'] ?? $event['desc'] }}</p>
                                    @endif
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



    </div>
@endsection