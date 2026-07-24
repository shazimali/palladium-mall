{{-- Tenants Table Partial (Rendered for AJAX and initial load) --}}
<div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
            <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">Flat / Shop No.</th>
                <th class="px-4 py-3">Tenant Name</th>
                <th class="px-4 py-3">Phone</th>
                <th class="px-4 py-3">Agreements / Duration</th>
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
                            <span class="unit-badge-lg">
                                {!! isset($highlight) ? $highlight($tenant->unit->unit_number) : e($tenant->unit->unit_number) !!}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-bold text-gray-900 dark:text-white/90 text-base">
                            {!! isset($highlight) ? $highlight($tenant->name) : e($tenant->name) !!}
                        </div>
                    </td>
                    <td class="px-4 py-3 font-medium">
                        {!! isset($highlight) ? $highlight($tenant->phone) : e($tenant->phone) !!}
                    </td>
                    <td class="px-4 py-3 text-xs">
                        @php
                            $showAgreements = $tenant->agreements->whereIn('status', ['active', 'expired', 'terminated'])->sortByDesc('id');
                        @endphp
                        @if($showAgreements->isNotEmpty())
                            <div class="flex flex-col gap-2">
                                @foreach($showAgreements as $agreement)
                                    @if($agreement->start_date && $agreement->end_date)
                                        <div class="flex flex-col gap-0.5 rounded-xl border border-gray-200 bg-gray-50/70 p-2 dark:border-gray-800 dark:bg-white/[0.02]">
                                            <span class="font-bold text-gray-900 dark:text-white/90 whitespace-nowrap">
                                                {{ $agreement->start_date->format('d M Y') }} - {{ $agreement->end_date->format('d M Y') }}
                                            </span>
                                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-0.5 flex flex-wrap gap-x-2 items-center">
                                                <span class="font-extrabold text-gray-800 dark:text-gray-200">Rent:</span>
                                                <span class="font-mono font-bold">Rs. {{ number_format($agreement->monthly_rent) }}</span>
                                                @if($agreement->maintenance_charge)
                                                    <span class="text-gray-300 dark:text-gray-700">|</span>
                                                    <span class="font-extrabold text-gray-800 dark:text-gray-200">Maint:</span>
                                                    <span class="font-mono font-bold">Rs. {{ number_format($agreement->maintenance_charge) }}</span>
                                                @endif
                                                @if($agreement->security_deposit)
                                                    <span class="text-gray-300 dark:text-gray-700">|</span>
                                                    <span class="font-extrabold text-gray-800 dark:text-gray-200">Dep:</span>
                                                    <span class="font-mono font-bold">Rs. {{ number_format($agreement->security_deposit) }}</span>
                                                @endif
                                            </div>
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
                            <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-extrabold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300">
                                Draft
                            </span>
                        @elseif($tenant->status === 'active')
                            <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-extrabold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-extrabold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('tenants.show', $tenant) }}"
                                class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                title="View">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>

                            @if(auth()->user()->hasPermission('tenants.edit') || auth()->user()->isSuperAdmin())
                                @if($tenant->isDraft())
                                    <a href="{{ route('tenants.showStep', [$tenant, $tenant->wizardStep()]) }}"
                                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-bold text-yellow-700 bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 transition-colors"
                                        title="Resume wizard">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
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
                                    class="inline-flex items-center gap-1 rounded-lg bg-orange-500 px-2.5 py-1 text-xs font-bold text-white hover:bg-orange-600 transition-colors"
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        No tenants found matching your search criteria.
                        <a href="{{ route('tenants.create') }}" class="text-brand-500 hover:underline font-bold">Add your first tenant.</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($tenants->hasPages())
    <div class="border-t border-gray-100 p-4 dark:border-gray-800">
        {{ $tenants->links() }}
    </div>
@endif
