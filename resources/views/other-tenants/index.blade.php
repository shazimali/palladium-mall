@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="" />

    @php
        $search = request('search');
        $highlight = function($text) use ($search) {
            if (empty($text)) return '';
            if (empty($search)) {
                return e($text);
            }
            $escapedSearch = preg_quote($search, '/');
            return preg_replace('/(' . $escapedSearch . ')/i', '<mark class="bg-amber-100 text-amber-900 rounded px-0.5 dark:bg-amber-950/70 dark:text-amber-300 font-medium">$1</mark>', e($text));
        };
    @endphp

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Summary Widget Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6 md:gap-5">
        {{-- Total Other Tenants --}}
        <a href="{{ route('other-tenants.index', ['search' => request('search'), 'filter_month' => request('filter_month')]) }}"
           class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl block {{ !request('status') ? 'ring-4 ring-offset-2 ring-brand-500 dark:ring-offset-gray-900' : '' }}"
           style="background: linear-gradient(135deg, #465fff 0%, #2a31d8 100%);">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
            <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>
            <div class="relative">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-widest text-white/80">Total Tenants</p>
                <h4 class="mt-1 text-3xl font-extrabold">{{ number_format($counts['total']) }}</h4>
                <span class="mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-white/20">
                    All Records
                </span>
            </div>
        </a>

        {{-- Attached --}}
        <a href="{{ route('other-tenants.index', ['status' => 'attached', 'search' => request('search'), 'filter_month' => request('filter_month')]) }}"
           class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl block {{ request('status') === 'attached' ? 'ring-4 ring-offset-2 ring-green-500 dark:ring-offset-gray-900' : '' }}"
           style="background: linear-gradient(135deg, #12b76a 0%, #027a48 100%);">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
            <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>
            <div class="relative">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-widest text-white/80">Attached Tenants</p>
                <h4 class="mt-1 text-3xl font-extrabold">{{ number_format($counts['attached']) }}</h4>
                <span class="mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-white/20">
                    Active Flat/Shop
                </span>
            </div>
        </a>

        {{-- Detached --}}
        <a href="{{ route('other-tenants.index', ['status' => 'detached', 'search' => request('search'), 'filter_month' => request('filter_month')]) }}"
           class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl block {{ request('status') === 'detached' ? 'ring-4 ring-offset-2 ring-amber-500 dark:ring-offset-gray-900' : '' }}"
           style="background: linear-gradient(135deg, #f79009 0%, #b54708 100%);">
            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
            <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>
            <div class="relative">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-white/20">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-widest text-white/80">Detached Tenants</p>
                <h4 class="mt-1 text-3xl font-extrabold">{{ number_format($counts['detached']) }}</h4>
                <span class="mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-white/20">
                    Unassigned/Inactive
                </span>
            </div>
        </a>
    </div>

    <x-common.component-card title="Other Tenants" desc="Manage Other Tenants ">

        {{-- ── Top bar: Actions ── --}}
        <div class="flex items-center justify-end gap-2">
            @if(request()->anyFilled(['search', 'status', 'filter_month']))
                <a href="{{ route('other-tenants.index') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                    Clear
                </a>
            @endif
            @if(auth()->user()->hasPermission('other_tenants.create') || auth()->user()->isSuperAdmin())
                <a href="{{ route('other-tenants.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Other Tenant
                </a>
            @endif
        </div>

        {{-- ── Search bar ── --}}
        <div class="mb-6 mt-4 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('other-tenants.index') }}" method="GET"
                class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="relative flex-1 max-w-md">
                    <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search by name, CNIC, phone..."
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>
                <select name="status" onchange="this.form.submit()"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All Occupancy</option>
                    <option value="attached" {{ request('status') === 'attached' ? 'selected' : '' }}>Attached</option>
                    <option value="detached" {{ request('status') === 'detached' ? 'selected' : '' }}>Detached</option>
                </select>

                <!-- Month/Year Filter -->
                <div class="relative">
                    <input type="text" id="filter_month" name="filter_month" value="{{ request('filter_month') }}" placeholder="Select Month/Year" autocomplete="off"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-48 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <button type="submit" class="hidden">Submit</button>
            </form>
        </div>

        {{-- ── Table ── --}}
        <div x-data="attachModal()" class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Flat/Shop</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Occupancy</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($otherTenants as $index => $ot)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors {{ request('search') ? 'bg-amber-500/[0.03] dark:bg-amber-500/[0.02] border-l-2 border-l-amber-500/70' : '' }}">
                            <td class="px-4 py-3 text-gray-400">{{ $otherTenants->firstItem() + $index }}</td>

                            {{-- Attached Unit --}}
                            <td class="px-4 py-3">
                                @if($ot->unit)
                                    @php
                                        $activeHist = $ot->unitHistory->where('unit_id', $ot->unit_id)->whereNull('detached_at')->first();
                                    @endphp
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-bold text-gray-900 dark:text-white text-sm">
                                            {!! $highlight($ot->unit->unit_number) !!}
                                        </span>
                                        @if($activeHist && $activeHist->attached_at)
                                            <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-0.5">
                                                Since {{ $activeHist->attached_at->format('d M Y') }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Name --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if($ot->photo)
                                        <img src="{{ $ot->photo_url }}" alt="{{ $ot->name }}" class="h-8 w-8 rounded-full object-cover border border-gray-200 dark:border-gray-700 shadow-xs shrink-0">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-brand-50 dark:bg-brand-950/20 text-brand-500 dark:text-brand-400 flex items-center justify-center font-bold text-xs shrink-0 uppercase">
                                            {{ substr($ot->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-semibold text-gray-800 dark:text-white/90">{!! $highlight($ot->name) !!}</div>
                                        <div class="flex flex-col gap-0.5 mt-0.5">
                                            @if($ot->cnic)
                                                <span class="text-xs text-gray-400">CNIC: {!! $highlight($ot->cnic) !!}</span>
                                            @endif
                                            @if($ot->address)
                                                <div class="text-xs text-gray-400 truncate max-w-[180px]">{!! $highlight($ot->address) !!}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Phone --}}
                            <td class="px-4 py-3">
                                @if($ot->phone)
                                    <div class="text-sm text-gray-800 dark:text-white/90">{!! $highlight($ot->phone) !!}</div>
                                @endif
                                @if($ot->whatsapp_number)
                                    <div class="text-xs text-gray-400 mt-0.5">WA: {!! $highlight($ot->whatsapp_number) !!}</div>
                                @endif
                                @if(!$ot->phone && !$ot->whatsapp_number)
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Occupancy --}}
                            <td class="px-4 py-3">
                                @if($ot->unit_id)
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400">Attached</span>
                                @else
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800/40 dark:text-gray-400">Detached</span>
                                @endif
                            </td>


                            {{-- Actions --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">

                                    {{-- View --}}
                                    <a href="{{ route('other-tenants.show', $ot) }}"
                                        class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                        title="View">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    {{-- Attach button --}}
                                    @if(auth()->user()->hasPermission('other_tenants.attach') || auth()->user()->isSuperAdmin())
                                        @if($ot->unit_id)
                                            {{-- Detach --}}
                                            <form action="{{ route('other-tenants.detach', $ot) }}" method="POST"
                                                onsubmit="return confirm('Detach {{ addslashes($ot->name) }} from Unit {{ $ot->unit->unit_number ?? '' }}?')">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center rounded-lg px-2 py-1 text-xs font-medium text-orange-600 bg-orange-50 hover:bg-orange-100 dark:bg-orange-900/20 dark:text-orange-400 dark:hover:bg-orange-900/40 transition-colors"
                                                    title="Detach from unit">
                                                    Detach
                                                </button>
                                            </form>
                                        @else
                                            {{-- Attach --}}
                                            <button type="button"
                                                @click="openAttach({{ $ot->id }}, '{{ addslashes($ot->name) }}')"
                                                class="inline-flex items-center rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/20 dark:text-indigo-400 dark:hover:bg-indigo-900/40 transition-colors"
                                                title="Attach to unit">
                                                Attach
                                            </button>
                                        @endif
                                    @endif

                                    {{-- Edit --}}
                                    @if(auth()->user()->hasPermission('other_tenants.edit') || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('other-tenants.edit', $ot) }}"
                                            class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                            title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif

                                    {{-- Delete --}}
                                    @if(auth()->user()->hasPermission('other_tenants.delete') || auth()->user()->isSuperAdmin())
                                        <form action="{{ route('other-tenants.destroy', $ot) }}" method="POST"
                                            onsubmit="return confirm('Delete {{ addslashes($ot->name) }}? This cannot be undone.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center rounded-lg p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                title="Delete">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-gray-400 text-sm">
                                No other tenants found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($otherTenants->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-800">
                    {{ $otherTenants->links() }}
                </div>
            @endif

            {{-- ── Attach Modal ── --}}
            @if(auth()->user()->hasPermission('other_tenants.attach') || auth()->user()->isSuperAdmin())
            <div x-show="showModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                style="background: rgba(0,0,0,0.5);">
                <div @click.outside="showModal = false"
                    class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900">

                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Attach to Unit</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Attaching: <span class="font-medium text-gray-800 dark:text-white" x-text="tenantName"></span>
                            </p>
                        </div>
                        <button @click="showModal = false"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Unit search --}}
                    <input type="text" x-model="unitSearch" placeholder="Filter units..."
                        class="mb-4 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30">

                    <div class="max-h-80 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-sm">
                            <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left">Unit</th>
                                    <th class="px-3 py-2 text-left">Floor / Block</th>
                                    <th class="px-3 py-2 text-left">Current Tenant</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($selfUnits as $unit)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]"
                                        x-show="!unitSearch || '{{ strtolower($unit->unit_number) }}'.includes(unitSearch.toLowerCase()) || '{{ strtolower($unit->floor?->name ?? '') }}'.includes(unitSearch.toLowerCase()) || '{{ strtolower($unit->block?->name ?? '') }}'.includes(unitSearch.toLowerCase())">
                                        <td class="px-3 py-2 font-bold text-gray-900 dark:text-white">{{ $unit->unit_number }}</td>
                                        <td class="px-3 py-2 text-gray-500 dark:text-gray-400 text-xs">
                                            {{ $unit->floor?->name }} &mdash; {{ $unit->block?->name }}
                                        </td>
                                        <td class="px-3 py-2 text-xs">
                                            @if($unit->otherTenant)
                                                <span class="font-semibold text-red-600 dark:text-red-400">{{ $unit->otherTenant->name }}</span>
                                                <span class="block text-[10px] text-gray-400 font-normal mt-0.5">(Already attached)</span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            @if($unit->otherTenant)
                                                <button type="button" disabled
                                                    class="inline-flex items-center gap-1 rounded-lg px-3 py-1 text-xs font-medium bg-gray-200 text-gray-400 cursor-not-allowed dark:bg-gray-800 dark:text-gray-600">
                                                    <span>🔒</span> Locked
                                                </button>
                                            @else
                                                <form :action="`/other-tenants/${tenantId}/attach`" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                                                    <button type="submit"
                                                        class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium bg-brand-500 text-white hover:bg-brand-600 transition-colors">
                                                        Select
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            @endif

        </div>{{-- end x-data --}}

    </x-common.component-card>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr('#filter_month', {
                dateFormat: 'Y-m-01',
                altInput: true,
                altFormat: 'F Y',
                allowInput: false,
                disableMobile: true,
                plugins: [
                    new monthSelectPlugin({
                        shorthand: false,
                        dateFormat: 'Y-m-01',
                        altFormat: 'F Y',
                        theme: 'light',
                    })
                ],
                onChange: function(selectedDates, dateStr, instance) {
                    instance.element.form.submit();
                }
            });
        });

        function attachModal() {
            return {
                showModal: false,
                tenantId: null,
                tenantName: '',
                unitSearch: '',
                openAttach(id, name) {
                    this.tenantId   = id;
                    this.tenantName = name;
                    this.unitSearch = '';
                    this.showModal  = true;
                },
            };
        }
    </script>
    @endpush
@endsection
