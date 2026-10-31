@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Flat / Shop History" />

    <x-common.component-card title="" desc="">

        <form action="{{ route('units.history') }}" method="GET"
            class="flex flex-wrap items-end justify-between gap-3 mb-6">
            <div class="flex flex-wrap items-end gap-3">
                <div class="w-72">
                    <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Flat / Shop
                    </label>
                    <x-common.searchable-select
                        name="unit_id"
                        placeholder="Choose a Flat / Shop"
                        :selected="$filters['unit_id']"
                        :clearable="false"
                        :auto-submit="true"
                        :bold-options="true"
                        :options="collect([['value' => 'all', 'label' => 'ALL — All Flats / Shops']])->concat($units->map(fn($u) => ['value' => $u->id, 'label' => $u->unit_number . ' — ' . ($u->tenant->name ?? ($u->otherTenant->name ?? 'Vacant'))]))"
                    />
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Date Range
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="date_from" name="date_from" value="{{ $filters['date_from'] }}"
                            placeholder="From Date" autocomplete="off"
                            class="w-36 rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-base font-bold text-gray-900 placeholder:text-gray-400 placeholder:font-semibold focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        <span class="text-gray-400">-</span>
                        <input type="text" id="date_to" name="date_to" value="{{ $filters['date_to'] }}"
                            placeholder="To Date" autocomplete="off"
                            class="w-36 rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-base font-bold text-gray-900 placeholder:text-gray-400 placeholder:font-semibold focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>
                </div>

                <button type="submit"
                    class="inline-flex items-center gap-2.5 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors">
                    View
                </button>
                @if($filters['date_from'] || $filters['date_to'])
                    <a href="{{ route('units.history', array_filter(['unit_id' => $filters['unit_id']])) }}"
                        class="rounded-xl border-2 border-gray-300 px-5 py-2.5 text-sm font-extrabold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Clear Dates
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('tenants.index') }}"
                    class="rounded-xl border-2 border-gray-300 px-5 py-2.5 text-sm font-extrabold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                    Back
                </a>
                @if($rows->isNotEmpty())
                    <a href="{{ route('units.history.print', array_filter($filters)) }}" target="_blank"
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

        @if(!$filters['unit_id'])
            <div class="rounded-xl border-2 border-dashed border-gray-300 px-4 py-12 text-center text-sm font-bold text-gray-400 dark:border-gray-700 dark:text-gray-500">
                Select a flat / shop (or ALL) and an optional date range to view history.
            </div>
        @else
            <div class="mb-4 text-center">
                <h2 class="text-xl font-black uppercase tracking-wide text-gray-900 dark:text-white">
                    @if($unit)
                        {{ ucfirst($unit->type) }} No {{ $unit->unit_number }}
                    @else
                        All Flats / Shops
                    @endif
                </h2>
                <p class="text-sm font-bold text-gray-700 dark:text-gray-300 mt-1">
                    @if($unit)
                        {{ $unit->floor->name ?? '' }}{{ $unit->block ? ' · ' . $unit->block->name : '' }}{{ $unit->landlord ? ' · Owner: ' . $unit->landlord->name : '' }}
                    @endif
                    @if($filters['date_from'] || $filters['date_to'])
                        {{ $unit ? ' · ' : '' }}Period:
                        {{ $filters['date_from'] ? \Carbon\Carbon::parse($filters['date_from'])->format('d M Y') : 'Beginning' }}
                        –
                        {{ $filters['date_to'] ? \Carbon\Carbon::parse($filters['date_to'])->format('d M Y') : 'Today' }}
                    @endif
                </p>
            </div>

            <div class="overflow-x-auto rounded-xl border-2 border-gray-200 dark:border-gray-800">
                <table class="w-full text-xs sm:text-[13.5px] text-left text-gray-900 dark:text-gray-100">
                    <thead class="text-xs font-black uppercase tracking-wider bg-brand-600 text-white dark:bg-brand-700">
                        <tr>
                            <th class="px-3.5 py-2.5 text-white">#</th>
                            <th class="px-3.5 py-2.5 text-white">Flat / Shop</th>
                            <th class="px-3.5 py-2.5 text-white">Tenant Name</th>
                            <th class="px-3.5 py-2.5 text-white">Contact No</th>
                            @unless($isAll)
                            <th class="px-3.5 py-2.5 text-white text-right">Rent</th>
                            <th class="px-3.5 py-2.5 text-white text-right">Advance</th>
                            @endunless
                            <th class="px-3.5 py-2.5 text-white">Agreement Start</th>
                            <th class="px-3.5 py-2.5 text-white">Agreement End</th>
                            <th class="px-3.5 py-2.5 text-white">Vacated On</th>
                            <th class="px-3.5 py-2.5 text-white">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($rows as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-3.5 py-2 font-bold">{{ $loop->iteration }}</td>
                                <td class="px-3.5 py-2 font-black whitespace-nowrap">{{ $row['unit']->unit_number }}</td>
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
                                @unless($isAll)
                                <td class="px-3.5 py-2 text-right font-mono">{{ $row['rent'] ? number_format($row['rent']) : '—' }}</td>
                                <td class="px-3.5 py-2 text-right font-mono">{{ $row['advance'] ? number_format($row['advance']) : '—' }}</td>
                                @endunless
                                <td class="px-3.5 py-2 whitespace-nowrap">{{ $row['start_date']?->format('d M Y') ?? '—' }}</td>
                                <td class="px-3.5 py-2 whitespace-nowrap">{{ $row['end_date']?->format('d M Y') ?? '—' }}</td>
                                <td class="px-3.5 py-2 whitespace-nowrap">{{ $row['vacated_at']?->format('d M Y') ?? '—' }}</td>
                                <td class="px-3.5 py-2">
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ match($row['status']) {
                                        'Active' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'Vacant' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        default  => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                    } }}">
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAll ? 8 : 10 }}" class="px-4 py-8 text-center text-sm font-bold text-gray-400 dark:text-gray-600">
                                    No history found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                ['#date_from', '#date_to'].forEach(function (selector) {
                    flatpickr(selector, {
                        dateFormat: 'Y-m-d',
                        altInput: true,
                        altFormat: 'd M Y',
                        allowInput: true,
                        disableMobile: true,
                    });
                });
            }
        });
    </script>
@endpush
