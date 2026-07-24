{{-- Units Table Partial (Rendered for AJAX and initial load) --}}
<div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
            <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">Flat No.</th>
                <th class="px-4 py-3">Floor</th>
                <th class="px-4 py-3">Block</th>
                <th class="px-4 py-3">Area / Zone</th>
                <th class="px-4 py-3">Landlord</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Other-Owned</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($units as $index => $unit)
                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-4 py-3 text-gray-400">{{ $units->firstItem() + $index }}</td>
                    <td class="px-4 py-3">
                        <span class="unit-badge-lg">{!! isset($highlight) ? $highlight($unit->unit_number) : e($unit->unit_number) !!}</span>
                    </td>
                    <td class="px-4 py-3">{!! isset($highlight) ? $highlight($unit->floor->name ?? '—') : e($unit->floor->name ?? '—') !!}</td>
                    <td class="px-4 py-3">{!! isset($highlight) ? $highlight($unit->block->name ?? '—') : e($unit->block->name ?? '—') !!}</td>
                    <td class="px-4 py-3">{!! isset($highlight) ? $highlight($unit->area->name ?? '—') : e($unit->area->name ?? '—') !!}</td>
                    <td class="px-4 py-3">
                        @if($unit->landlord)
                            <a href="{{ route('landlords.show', $unit->landlord_id) }}"
                                class="text-brand-500 hover:underline font-medium">
                                {!! isset($highlight) ? $highlight($unit->landlord->name) : e($unit->landlord->name) !!}
                            </a>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold
                            {{ $unit->type === 'flat'
                                ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                : 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' }}">
                            {{ ucfirst($unit->type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold
                            {{ $unit->status === 'rented'
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                : ($unit->status === 'vacant'
                                    ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'
                                    : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400') }}">
                            {{ ucfirst($unit->status) }}
                        </span>
                    </td>

                    {{-- External Owner (is_self) --}}
                    <td class="px-4 py-3">
                        @if($unit->is_self)
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex w-fit items-center gap-1 rounded-full bg-violet-100 px-2 py-0.5 text-[11px] font-semibold text-violet-700 dark:bg-violet-900/30 dark:text-violet-300">
                                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Other-Owned
                                </span>
                            </div>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            {{-- View --}}
                            <a href="{{ route('units.show', $unit) }}"
                                class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white transition-colors"
                                title="View">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>

                            {{-- Print --}}
                            <a href="{{ route('units.print-one', $unit) }}" target="_blank"
                                class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white transition-colors"
                                title="Print Specification">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                                </svg>
                            </a>

                            {{-- Edit --}}
                            @if(auth()->user()->hasPermission('units.edit') || auth()->user()->isSuperAdmin())
                                <a href="{{ route('units.edit', $unit) }}"
                                    class="inline-flex items-center rounded-lg p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-blue-900/20 transition-colors"
                                    title="Edit">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
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
                    <td colspan="10" class="px-4 py-12 text-center text-gray-400 dark:text-gray-600">
                        <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        No units found matching your search criteria. <a href="{{ route('units.create') }}" class="text-brand-500 hover:underline font-medium">Add a Flat/Shop.</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($units->hasPages())
    <div class="border-t border-gray-100 p-4 dark:border-gray-800">
        {{ $units->links() }}
    </div>
@endif
