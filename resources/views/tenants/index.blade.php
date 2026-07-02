@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Tenants and Agreements" />

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

    <x-common.component-card title="All Tenants and Agreements" desc="Manage tenant profiles and unit assignments">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tenants.index', ['search' => request('search'), 'landlord_id' => request('landlord_id'), 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}"
                    class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium transition-colors {{ !request('status') ? 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800/40 dark:text-gray-400 dark:hover:bg-gray-800' }}">
                    Total: {{ $counts['total'] }}
                </a>
                <a href="{{ route('tenants.index', ['status' => 'active', 'search' => request('search'), 'landlord_id' => request('landlord_id'), 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}"
                    class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium transition-colors {{ request('status') === 'active' ? 'bg-green-600 text-white dark:bg-green-700' : 'bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-950/20 dark:text-green-400 dark:hover:bg-green-950/40' }}">
                    Active: {{ $counts['active'] }}
                </a>
                <a href="{{ route('tenants.index', ['status' => 'inactive', 'search' => request('search'), 'landlord_id' => request('landlord_id'), 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}"
                    class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium transition-colors {{ request('status') === 'inactive' ? 'bg-red-600 text-white dark:bg-red-700' : 'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-950/20 dark:text-red-400 dark:hover:bg-red-950/40' }}">
                    Inactive: {{ $counts['inactive'] }}
                </a>
                <a href="{{ route('tenants.index', ['status' => 'draft', 'search' => request('search'), 'landlord_id' => request('landlord_id'), 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}"
                    class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-medium transition-colors {{ request('status') === 'draft' ? 'bg-yellow-500 text-white dark:bg-yellow-600' : 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100 dark:bg-yellow-950/20 dark:text-yellow-400 dark:hover:bg-yellow-950/40' }}">
                    Drafts: {{ $counts['draft'] }}
                </a>
            </div>

            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'status', 'landlord_id', 'date_from', 'date_to']))
                    <a href="{{ route('tenants.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                        Clear
                    </a>
                @endif
                @if(auth()->user()->hasPermission('tenants.create') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('tenants.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Tenant & Agreement 
                    </a>
                @endif
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('tenants.index') }}" method="GET"
                class="flex flex-col gap-4 sm:flex-row sm:items-center">
                
                <!-- Search Input -->
                <div class="relative flex-1 max-w-md">
                    <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search tenants..."
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <!-- Status Filter -->
                <div class="relative">
                    <select name="status" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                <!-- Landlord Filter -->
                <div class="relative">
                    <select name="landlord_id" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Landlords</option>
                        @foreach($landlords as $landlord)
                            <option value="{{ $landlord->id }}" {{ request('landlord_id') == $landlord->id ? 'selected' : '' }}>
                                {{ $landlord->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Picker Fields -->
                <div class="flex items-center gap-2">
                    <input type="text" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="Date From" autocomplete="off"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-36 rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    <span class="text-xs text-gray-400">to</span>
                    <input type="text" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="Date To" autocomplete="off"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-36 rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>
                
                <button type="submit" class="hidden">Submit</button>
            </form>
        </div>

        <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Flat/Shop</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Agreement</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($tenants as $index => $tenant)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-4 py-3 text-gray-400">{{ $tenants->firstItem() + $index }}</td>
                                    <td class="px-4 py-3">
                                        @if($tenant->unit)
                                        <span class="font-bold text-gray-900 dark:text-white text-sm">
                                            {{ $tenant->unit->unit_number }}
                                        </span>
                                        @else
                                        <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-800 dark:text-white/90">{{ $tenant->name }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ $tenant->phone }}</td>
                                    <td class="px-4 py-3 text-xs">
                                        @php
                                            $showAgreements = $tenant->agreements->whereIn('status', ['active', 'expired', 'terminated'])->sortByDesc('id');
                                        @endphp
                                        @if($showAgreements->isNotEmpty())
                                            <div class="flex flex-col gap-2">
                                                @foreach($showAgreements as $agreement)
                                                    @if($agreement->start_date && $agreement->end_date)
                                                        <div class="flex flex-col gap-0.5 rounded-md border border-gray-100 bg-gray-50/50 p-1.5 dark:border-gray-800 dark:bg-white/[0.02]">
                                                            <span class="font-medium text-gray-800 dark:text-white/90 whitespace-nowrap">
                                                                {{ $agreement->start_date->format('d M Y') }} - {{ $agreement->end_date->format('d M Y') }}
                                                            </span>
                                                            <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[9px] font-medium w-fit {{ $agreement->status_badge_class }}">
                                                                {{ ucfirst($agreement->status) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($tenant->status === 'draft')
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                                            Draft
                                        </span>
                                        @elseif($tenant->status === 'active')
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                            Active
                                        </span>
                                        @else
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                                            Inactive
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('tenants.show', $tenant) }}"
                                                class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                                title="View">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>

                                            @if(auth()->user()->hasPermission('tenants.edit') || auth()->user()->isSuperAdmin())
                                                @if($tenant->isDraft())
                                                    <a href="{{ route('tenants.showStep', [$tenant, $tenant->wizardStep()]) }}"
                                                        class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-yellow-600 bg-yellow-50 hover:bg-yellow-100 dark:bg-yellow-900/20 dark:text-yellow-400 transition-colors"
                                                        title="Resume wizard">
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 00010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        Resume
                                                    </a>
                                                @else
                                                    <a href="{{ route('tenants.showStep', [$tenant, 1]) }}"
                                                        class="inline-flex items-center rounded-lg p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                                        title="Edit">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </a>
                                                @endif
                                            @endif

                                            @if($tenant->status === 'active')
                                                <a href="{{ route('tenants.moveOut.create', $tenant) }}"
                                                    class="inline-flex items-center gap-1 rounded-lg bg-orange-500 px-2 py-1 text-xs font-semibold text-white hover:bg-orange-600 transition-colors"
                                                    title="Move Out">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                    </svg>
                                                    Move Out
                                                </a>
                                            @endif

                                            @if(auth()->user()->hasPermission('tenants.delete') || auth()->user()->isSuperAdmin())
                                                <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" x-data
                                                    @submit.prevent="if(confirm('Remove {{ $tenant->name }}? Their unit will be marked vacant.')) $el.submit()">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center rounded-lg p-1.5 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                        title="Delete">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor"
                                    stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                No tenants found.
                                <a href="{{ route('tenants.create') }}" class="text-brand-500 hover:underline">Add your first
                                    tenant.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @if($tenants->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                {{ $tenants->links() }}
            </div>
        @endif
    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#date_from', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    onChange: function(selectedDates, dateStr, instance) {
                        if (dateStr) {
                            instance.element.form.submit();
                        }
                    }
                });

                flatpickr('#date_to', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    onChange: function(selectedDates, dateStr, instance) {
                        if (dateStr) {
                            instance.element.form.submit();
                        }
                    }
                });
            }
        });
    </script>
@endpush