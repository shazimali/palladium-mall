@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Utilities" />

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

    <x-common.component-card title="Utility Readings" desc="Track electricity, water and gas bills per unit">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <span
                class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Total: {{ $readings->total() }}
            </span>

            @if(auth()->user()->hasPermission('utilities.create') || auth()->user()->isSuperAdmin())
                <a href="{{ route('utilities.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Reading
                </a>
            @endif
        </div>

        <div class="overflow-hidden">
            <table id="utilitiesTable" class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3">Tenant</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Month</th>
                        <th class="px-4 py-3">Units</th>
                        <th class="px-4 py-3">Bill (Rs.)</th>
                        <th class="px-4 py-3">Due Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($readings as $index => $reading)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 text-gray-400">{{ $readings->firstItem() + $index }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                                    {{ $reading->unit->unit_number }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                {{ $reading->tenant->name }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $reading->type_icon }} {{ $reading->type_label }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                {{ $reading->month->format('M Y') }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                {{ number_format($reading->units_consumed, 2) }}
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {{ number_format($reading->bill_amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-xs {{ $reading->isOverdue() ? 'text-red-500 font-semibold' : '' }}">
                                {{ $reading->due_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $reading->status_badge_class }}">
                                    {{ ucfirst($reading->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">

                                    {{-- View --}}
                                    <a href="{{ route('utilities.show', $reading) }}"
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

                                    {{-- Mark Paid --}}
                                    @if(!$reading->isPaid())
                                        <button type="button" x-data
                                            @click="$dispatch('open-mark-paid', { id: {{ $reading->id }}, action: '{{ route('utilities.mark-paid', $reading) }}' })"
                                            class="inline-flex items-center rounded-lg p-1.5 text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                                            title="Mark Paid">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    @endif

                                    {{-- Edit --}}
                                    @if(auth()->user()->hasPermission('utilities.edit') || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('utilities.edit', $reading) }}"
                                            class="inline-flex items-center rounded-lg p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                            title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif

                                    {{-- Delete --}}
                                    @if(auth()->user()->hasPermission('utilities.delete') || auth()->user()->isSuperAdmin())
                                        <form action="{{ route('utilities.destroy', $reading) }}" method="POST" x-data
                                            @submit.prevent="if(confirm('Delete this reading?')) $el.submit()">
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
                            <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                                No utility readings found.
                                <a href="{{ route('utilities.create') }}" class="text-brand-500 hover:underline">Add the first
                                    one.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>

    {{-- Mark Paid Modal --}}
    <div x-data="{
                show: false,
                action: '',
                init() {
                    window.addEventListener('open-mark-paid', (e) => {
                        this.action = e.detail.action;
                        this.show = true;
                    });
                }
            }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900" @click.outside="show = false">
            <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Mark Bill as Paid</h3>
            <form :action="action" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Upload Payment Proof (optional)
                    </label>
                    <input type="file" name="bill_proof" accept="image/jpeg,image/jpg,image/png"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1 file:text-xs file:font-medium file:text-brand-600 dark:border-gray-700 dark:bg-gray-900">
                    <p class="mt-1 text-xs text-gray-400">JPEG or PNG. Max 2MB.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                        Confirm Paid
                    </button>
                    <button type="button" @click="show = false"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.min.css">
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new DataTable('#utilitiesTable', {
                pageLength: 20,
                lengthMenu: [10, 20, 50, 100],
                order: [[4, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [0, 9] },
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search utilities...',
                    lengthMenu: 'Show _MENU_ per page',
                    info: 'Showing _START_ to _END_ of _TOTAL_ readings',
                },
            });
        });
    </script>
@endpush