@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Agreement Center" />

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

    {{-- Expiring soon alert --}}
    @if($expiringCount > 0)
        <div
            class="mb-4 flex items-center gap-3 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-700 dark:border-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
            {{ $expiringCount }} {{ Str::plural('agreement', $expiringCount) }} expiring within 30 days.
        </div>
    @endif

    <x-common.component-card title="All Agreements" desc="Manage tenancy agreements, terms and signed documents">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2">
                <span
                    class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Total: {{ $agreements->total() }}
                </span>
            </div>

            @if(auth()->user()->hasPermission('agreements.create') || auth()->user()->isSuperAdmin())
                <a href="{{ route('agreements.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    New Agreement
                </a>
            @endif
        </div>

        <div class="overflow-hidden">
            <table id="agreementsTable" class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Tenant</th>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3">Start Date</th>
                        <th class="px-4 py-3">End Date</th>
                        <th class="px-4 py-3">Monthly Rent</th>
                        <th class="px-4 py-3">Days Left</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($agreements as $index => $agreement)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 text-gray-400">{{ $agreements->firstItem() + $index }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90">
                                {{ $agreement->tenant->name }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                                    {{ $agreement->unit->unit_number }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $agreement->start_date ? $agreement->start_date->format('d M Y') : 'Draft' }}</td>
                            <td class="px-4 py-3 text-xs">{{ $agreement->end_date ? $agreement->end_date->format('d M Y') : 'Draft' }}</td>
                            <td class="px-4 py-3 font-medium">{{ $agreement->monthly_rent ? 'Rs. ' . number_format($agreement->monthly_rent) : '—' }}</td>
                            <td class="px-4 py-3">
                                @if($agreement->isActive())
                                    @php $days = $agreement->daysRemaining(); @endphp
                                    <span
                                        class="text-xs font-medium {{ $days <= 30 ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400' }}">
                                        {{ $days }} days
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $agreement->status_badge_class }}">
                                    {{ ucfirst($agreement->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('agreements.show', $agreement) }}"
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

                                    @if(auth()->user()->hasPermission('agreements.edit') || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('agreements.edit', $agreement) }}"
                                            class="inline-flex items-center rounded-lg p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                            title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif

                                    @if($agreement->status === 'active')
                                        <a href="{{ route('tenants.moveOut.create', $agreement->tenant) }}"
                                            class="inline-flex items-center gap-1 rounded-lg bg-orange-500 px-2 py-1 text-xs font-semibold text-white hover:bg-orange-600 transition-colors"
                                            title="Move Out">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            Move Out
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('agreements.delete') || auth()->user()->isSuperAdmin())
                                        <form action="{{ route('agreements.destroy', $agreement) }}" method="POST" x-data
                                            @submit.prevent="if(confirm('Delete this agreement for {{ $agreement->tenant->name }}?')) $el.submit()">
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
                            <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                                <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor"
                                    stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                No agreements found.
                                <a href="{{ route('agreements.create') }}" class="text-brand-500 hover:underline">Create the
                                    first one.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.min.css">
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new DataTable('#agreementsTable', {
                pageLength: 20,
                lengthMenu: [10, 20, 50, 100],
                order: [[3, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [0, 8] },
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search agreements...',
                    lengthMenu: 'Show _MENU_ per page',
                    info: 'Showing _START_ to _END_ of _TOTAL_ agreements',
                    emptyTable: 'No agreements found',
                },
            });
        });
    </script>
@endpush