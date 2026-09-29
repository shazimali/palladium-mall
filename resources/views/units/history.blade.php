@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Flat / Shop History" />

    <x-common.component-card title="" desc="">

        <form action="{{ route('units.history') }}" method="GET"
            class="flex flex-wrap items-end justify-between gap-3 mb-6">
            <div class="w-72">
                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                    Flat / Shop
                </label>
                <x-common.searchable-select
                    name="unit_id"
                    placeholder="Choose a Flat / Shop"
                    :selected="request('unit_id')"
                    :clearable="false"
                    :auto-submit="true"
                    :bold-options="true"
                    :options="$units->map(fn($u) => ['value' => $u->id, 'label' => $u->unit_number . ' — ' . ($u->tenant->name ?? ($u->otherTenant->name ?? 'Vacant'))])"
                />
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('units.index') }}"
                    class="rounded-xl border-2 border-gray-300 px-5 py-2.5 text-sm font-extrabold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                    Back
                </a>
                @if($unit)
                    <a href="{{ route('units.history.print', ['unit_id' => $unit->id]) }}" target="_blank"
                        class="inline-flex items-center gap-2.5 rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-sm font-extrabold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/5 transition-colors shadow-xs">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                        </svg>
                        Print
                    </a>
                @endif
            </div>
        </form>

        @if(!$unit)
            <div class="rounded-xl border-2 border-dashed border-gray-300 px-4 py-12 text-center text-sm font-bold text-gray-400 dark:border-gray-700 dark:text-gray-500">
                Select a flat / shop to view its history.
            </div>
        @else
            <div class="mb-4 text-center">
                <h2 class="text-xl font-black uppercase tracking-wide text-gray-900 dark:text-white">
                    {{ ucfirst($unit->type) }} No {{ $unit->unit_number }}
                </h2>
                <p class="text-sm font-bold text-gray-700 dark:text-gray-300 mt-1">
                    {{ $unit->floor->name ?? '' }}{{ $unit->block ? ' · ' . $unit->block->name : '' }}{{ $unit->landlord ? ' · Owner: ' . $unit->landlord->name : '' }}
                </p>
            </div>

            <div class="overflow-x-auto rounded-xl border-2 border-gray-200 dark:border-gray-800">
                <table class="w-full text-xs sm:text-[13.5px] text-left text-gray-900 dark:text-gray-100">
                    <thead class="text-xs font-black uppercase tracking-wider bg-brand-600 text-white dark:bg-brand-700">
                        <tr>
                            <th class="px-3.5 py-2.5 text-white">#</th>
                            <th class="px-3.5 py-2.5 text-white">Name</th>
                            <th class="px-3.5 py-2.5 text-white">Contact No</th>
                            <th class="px-3.5 py-2.5 text-white text-right">Rent</th>
                            <th class="px-3.5 py-2.5 text-white text-right">Advance</th>
                            <th class="px-3.5 py-2.5 text-white">Agreement Start</th>
                            <th class="px-3.5 py-2.5 text-white">Agreement End</th>
                            <th class="px-3.5 py-2.5 text-white">Vacated On</th>
                            <th class="px-3.5 py-2.5 text-white">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($history as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-3.5 py-2 font-bold">{{ $loop->iteration }}</td>
                                <td class="px-3.5 py-2 font-bold">
                                    @if($row['url'])
                                        <a href="{{ $row['url'] }}" class="hover:text-brand-600 hover:underline">{{ $row['name'] }}</a>
                                    @else
                                        {{ $row['name'] }}
                                    @endif
                                    @if($row['kind'] !== 'Tenant')
                                        <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $row['kind'] }}</span>
                                    @endif
                                </td>
                                <td class="px-3.5 py-2 font-mono">{{ $row['phone'] ?: '—' }}</td>
                                <td class="px-3.5 py-2 text-right font-mono">{{ $row['rent'] ? number_format($row['rent']) : '—' }}</td>
                                <td class="px-3.5 py-2 text-right font-mono">{{ $row['advance'] ? number_format($row['advance']) : '—' }}</td>
                                <td class="px-3.5 py-2 whitespace-nowrap">{{ $row['start_date']?->format('d M Y') ?? '—' }}</td>
                                <td class="px-3.5 py-2 whitespace-nowrap">{{ $row['end_date']?->format('d M Y') ?? '—' }}</td>
                                <td class="px-3.5 py-2 whitespace-nowrap">{{ $row['vacated_at']?->format('d M Y') ?? '—' }}</td>
                                <td class="px-3.5 py-2">
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $row['status'] === 'Active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-sm font-bold text-gray-400 dark:text-gray-600">
                                    No history found for this flat / shop.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </x-common.component-card>
@endsection
