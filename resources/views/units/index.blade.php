@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Flat / Shop Master" />

    {{-- Flash Messages --}}
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

    <x-common.component-card title="All Flats / Shops" desc="Manage flats and shops — add, edit or update status">

        {{-- Top bar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            {{-- Stats strip --}}
            <div class="flex flex-wrap gap-2">
                @php
                    $total = $units->total();
                    $vacant = $units->getCollection()->where('status', 'vacant')->count();
                    $occupied = $units->getCollection()->where('status', 'occupied')->count();
                @endphp
                <span
                    class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Total: {{ $total }}
                </span>
                <span
                    class="inline-flex items-center rounded-lg bg-green-100 px-3 py-1 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                    Occupied: {{ $occupied }}
                </span>
                <span
                    class="inline-flex items-center rounded-lg bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                    Vacant: {{ $vacant }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'status', 'type', 'floor_id', 'block_id', 'area_id']))
                    <a href="{{ route('units.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                        Clear
                    </a>
                @endif
                @if(auth()->user()->hasPermission('units.create') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('units.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Flat/Shop
                    </a>
                @endif
            </div>
        </div>

        <!-- Filters & Search -->
        <div
            class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('units.index') }}" method="GET" class="flex flex-col gap-4 sm:flex-row sm:items-center">

                <!-- Search Input -->
                <div class="relative flex-1 max-w-md">
                    <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20"
                            fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by unit no..."
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <!-- Status Filter -->
                <div class="relative">
                    <select name="status" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Statuses</option>
                        <option value="vacant" {{ request('status') === 'vacant' ? 'selected' : '' }}>Vacant</option>
                        <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>Occupied</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div class="relative">
                    <select name="type" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Types</option>
                        <option value="shop" {{ request('type') === 'shop' ? 'selected' : '' }}>Shop</option>
                        <option value="flat" {{ request('type') === 'flat' ? 'selected' : '' }}>Flat</option>
                    </select>
                </div>

                <!-- Floor Filter -->
                <div class="relative">
                    <select name="floor_id" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Floors</option>
                        @foreach($floors ?? [] as $floor)
                            <option value="{{ $floor->id }}" {{ request('floor_id') == $floor->id ? 'selected' : '' }}>
                                {{ $floor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Block Filter -->
                <div class="relative">
                    <select name="block_id" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Blocks</option>
                        @foreach($blocks ?? [] as $block)
                            <option value="{{ $block->id }}" {{ request('block_id') == $block->id ? 'selected' : '' }}>
                                {{ $block->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Area Filter -->
                <div class="relative">
                    <select name="area_id" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Areas</option>
                        @foreach($areas ?? [] as $area)
                            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="hidden">Submit</button>
            </form>
        </div>

        {{-- DataTable --}}
        <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Flat No.</th>
                        <th class="px-4 py-3">Floor</th>
                        <th class="px-4 py-3">Block</th>
                        <th class="px-4 py-3">Area / Zone</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($units as $index => $unit)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-4 py-3 text-gray-400">{{ $units->firstItem() + $index }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90">
                                        {{ $unit->unit_number }}
                                    </td>
                                    <td class="px-4 py-3">{{ $unit->floor->name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $unit->block->name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $unit->area->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium
                                                                                                                                                                    {{ $unit->type === 'flat'
                        ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                        : 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' }}">
                                            {{ ucfirst($unit->type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium
                                                                                                                                                                    {{ $unit->status === 'occupied'
                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                        : ($unit->status === 'vacant'
                            ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'
                            : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400') }}">
                                            {{ ucfirst($unit->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- View --}}
                                            <a href="{{ route('units.show', $unit) }}"
                                                class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white transition-colors"
                                                title="View">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>

                                            {{-- Edit --}}
                                            @if(auth()->user()->hasPermission('units.edit') || auth()->user()->isSuperAdmin())
                                                <a href="{{ route('units.edit', $unit) }}"
                                                    class="inline-flex items-center rounded-lg p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-blue-900/20 transition-colors"
                                                    title="Edit">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                            @endif

                                            {{-- Delete --}}
                                            @if(auth()->user()->hasPermission('units.delete') || auth()->user()->isSuperAdmin())
                                                <form action="{{ route('units.destroy', $unit) }}" method="POST" x-data
                                                    @submit.prevent="if(confirm('Remove unit {{ $unit->unit_number }}? This can be restored later.')) $el.submit()">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center rounded-lg p-1.5 text-red-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 transition-colors"
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
                            <td colspan="10" class="px-4 py-12 text-center text-gray-400 dark:text-gray-600">
                                <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor"
                                    stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                No units found. <a href="{{ route('units.create') }}" class="text-brand-500 hover:underline">Add
                                    your first Flat/Shop.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($units->hasPages())
                <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                    {{ $units->links() }}
                </div>
            @endif
    </x-common.component-card>
@endsection