{{-- Landlords Table Partial (Rendered for AJAX and initial load) --}}
<div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
            <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Phone</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">CNIC</th>
                <th class="px-4 py-3">Assigned Flats & Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($landlords as $index => $landlord)
                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                    <td class="px-4 py-3 text-gray-400">{{ $landlords->firstItem() + $index }}</td>
                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white/90 text-base">
                        {!! isset($highlight) ? $highlight($landlord->name) : e($landlord->name) !!}
                    </td>
                    <td class="px-4 py-3 font-medium">
                        {!! isset($highlight) ? $highlight($landlord->phone ?? '—') : e($landlord->phone ?? '—') !!}
                    </td>
                    <td class="px-4 py-3">
                        {!! isset($highlight) ? $highlight($landlord->email ?? '—') : e($landlord->email ?? '—') !!}
                    </td>
                    <td class="px-4 py-3 font-mono font-medium">
                        {!! isset($highlight) ? $highlight($landlord->cnic ?? '—') : e($landlord->cnic ?? '—') !!}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col gap-1.5">
                            <span class="inline-flex items-center rounded-lg bg-blue-100 px-3 py-1 text-xs font-black text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 w-fit">
                                {{ $landlord->units_count }} {{ Str::plural('Property', $landlord->units_count) }}
                            </span>
                            @if($landlord->units->isNotEmpty())
                                <div class="mt-1 flex flex-wrap gap-1.5 max-w-sm">
                                    @foreach($landlord->units as $unit)
                                        @php
                                            $badgeColor = match($unit->status) {
                                                'rented' => 'bg-green-100 text-green-800 border-green-300 dark:bg-green-950/40 dark:text-green-300 dark:border-green-800',
                                                'vacant' => 'bg-yellow-100 text-yellow-800 border-yellow-300 dark:bg-yellow-950/40 dark:text-yellow-300 dark:border-yellow-800',
                                                'self' => 'bg-gray-100 text-gray-800 border-gray-300 dark:bg-gray-800/60 dark:text-gray-300 dark:border-gray-700',
                                                default => 'bg-gray-100 text-gray-800 border-gray-300 dark:bg-gray-800/60 dark:text-gray-300 dark:border-gray-700',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border px-2 py-0.5 text-xs font-bold {{ $badgeColor }}">
                                            {!! isset($highlight) ? $highlight($unit->unit_number) : e($unit->unit_number) !!} ({{ ucfirst($unit->status) }})
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            {{-- View --}}
                            <a href="{{ route('landlords.show', $landlord) }}"
                                class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white transition-colors"
                                title="View">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>

                            {{-- Edit --}}
                            @if(auth()->user()->hasPermission('landlords.edit') || auth()->user()->isSuperAdmin())
                                <a href="{{ route('landlords.edit', $landlord) }}"
                                    class="inline-flex items-center rounded-lg p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-blue-900/20 transition-colors"
                                    title="Edit">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            @endif

                            {{-- Delete --}}
                            @if(auth()->user()->hasPermission('landlords.delete') || auth()->user()->isSuperAdmin())
                                <form action="{{ route('landlords.destroy', $landlord) }}" method="POST" x-data
                                    @submit.prevent="if(confirm('Remove landlord {{ $landlord->name }}? Any linked units will have their landlord set to unassigned.')) $el.submit()">
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
                    <td colspan="7" class="px-4 py-12 text-center text-gray-400 font-medium">
                        <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        No landlords found matching your search criteria.
                        <a href="{{ route('landlords.create') }}" class="text-brand-500 hover:underline font-bold">Add one.</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($landlords->hasPages())
    <div class="border-t border-gray-100 p-4 dark:border-gray-800">
        {{ $landlords->links() }}
    </div>
@endif
