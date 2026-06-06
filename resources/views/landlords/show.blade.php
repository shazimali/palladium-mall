@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Landlord Detail — {{ $landlord->name }}" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left: Landlord info profile card --}}
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-col items-center text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-xl font-bold text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                        {{ strtoupper(substr($landlord->name, 0, 2)) }}
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-gray-950 dark:text-white">{{ $landlord->name }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Unit Owner / Landlord</p>
                </div>

                <div class="mt-6 space-y-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">Phone</span>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $landlord->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">Email</span>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $landlord->email ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">CNIC</span>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $landlord->cnic ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">Mailing Address</span>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90 leading-relaxed">{{ $landlord->address ?? '—' }}</p>
                    </div>
                </div>

                @if($landlord->notes)
                    <div class="mt-6 border-t border-gray-100 pt-6 dark:border-gray-800">
                        <span class="text-xs text-gray-400 dark:text-gray-500">Notes / Remarks</span>
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 leading-relaxed">{{ $landlord->notes }}</p>
                    </div>
                @endif

                <div class="mt-6 flex items-center gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                    @if(auth()->user()->hasPermission('landlords.edit') || auth()->user()->isSuperAdmin())
                        <a href="{{ route('landlords.edit', $landlord) }}"
                            class="flex-1 text-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                            Edit Profile
                        </a>
                    @endif
                    <a href="{{ route('landlords.index') }}"
                        class="flex-1 text-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-colors">
                        Back to List
                    </a>
                </div>
            </div>
        </div>

        {{-- Right: Associated units owned by this landlord --}}
        <div class="lg:col-span-2">
            <x-common.component-card title="Owned Properties" desc="Flats and shops owned by {{ $landlord->name }}">
                <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Flat No.</th>
                                <th class="px-4 py-3">Floor</th>
                                <th class="px-4 py-3">Block</th>
                                <th class="px-4 py-3">Area / Zone</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($landlord->units as $unit)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
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
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('units.show', $unit) }}"
                                            class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white transition-colors"
                                            title="View Unit Details">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-gray-400">
                                        No properties assigned to this landlord.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-common.component-card>
        </div>
    </div>
@endsection
