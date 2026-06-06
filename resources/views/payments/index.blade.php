@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Rent & Payments" />

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

    {{-- Summary cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs text-gray-400">Total Due (This Month)</p>
            <p class="mt-1 text-lg font-bold text-gray-800 dark:text-white">Rs. {{ number_format($summary['total_due']) }}
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs text-gray-400">Total Collected</p>
            <p class="mt-1 text-lg font-bold text-green-600">Rs. {{ number_format($summary['total_paid']) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs text-gray-400">Unpaid This Month</p>
            <p class="mt-1 text-lg font-bold text-red-500">{{ $summary['unpaid_count'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs text-gray-400">Overdue Records</p>
            <p class="mt-1 text-lg font-bold text-orange-500">{{ $summary['overdue_count'] }}</p>
        </div>
    </div>

    <x-common.component-card title="All Payments" desc="Track rent, maintenance and fine payments">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2">
                <span
                    class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Total: {{ $payments->total() }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                {{-- Bulk Generate button --}}
                @if(auth()->user()->hasPermission('payments.create') || auth()->user()->isSuperAdmin())
                    <button type="button" x-data @click="$dispatch('open-bulk-generate')"
                        class="inline-flex items-center gap-2 rounded-lg border border-brand-500 px-4 py-2 text-sm font-medium text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Bulk Generate
                    </button>

                    <a href="{{ route('payments.utilities.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Record Utility
                    </a>

                    <a href="{{ route('payments.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Payment
                    </a>
                @endif
            </div>
        </div>

        <div class="overflow-hidden">
            <table id="paymentsTable" class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Tenant</th>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Month</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Paid</th>
                        <th class="px-4 py-3">Due Date</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($payments as $index => $payment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 text-gray-400">{{ $payments->firstItem() + $index }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90">
                                {{ $payment->tenant->name }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                                    {{ $payment->unit->unit_number }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $payment->type_badge_class }}">
                                    {{ ucfirst($payment->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $payment->month->format('M Y') }}</td>
                            <td class="px-4 py-3 font-medium">Rs. {{ number_format($payment->amount) }}</td>
                            <td class="px-4 py-3">
                                <span class="{{ $payment->amount_paid > 0 ? 'text-green-600 font-medium' : 'text-gray-400' }}">
                                    Rs. {{ number_format($payment->amount_paid) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs {{ $payment->isOverdue() ? 'text-red-500 font-semibold' : '' }}">
                                {{ $payment->due_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $payment->status_badge_class }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">

                                    <a href="{{ route('payments.show', $payment) }}"
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

                                    <a href="{{ route('payments.print', $payment) }}" target="_blank"
                                        class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                        title="Print Receipt">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                                        </svg>
                                    </a>

                                    {{-- Record Payment --}}
                                    @if(!$payment->isPaid())
                                        <button type="button" x-data @click="$dispatch('open-record-payment', {
                                                            action: '{{ route('payments.record', $payment) }}',
                                                            amount: '{{ $payment->amount }}',
                                                            balance: '{{ $payment->balanceDue() }}'
                                                        })"
                                            class="inline-flex items-center rounded-lg p-1.5 text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                                            title="Record Payment">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </button>
                                    @endif

                                    @if(auth()->user()->hasPermission('payments.edit') || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('payments.edit', $payment) }}"
                                            class="inline-flex items-center rounded-lg p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                            title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('payments.delete') || auth()->user()->isSuperAdmin())
                                        <form action="{{ route('payments.destroy', $payment) }}" method="POST" x-data
                                            @submit.prevent="if(confirm('Delete this payment record?')) $el.submit()">
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
                                No payment records found.
                                <a href="{{ route('payments.create') }}" class="text-brand-500 hover:underline">Add one.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>

    {{-- Record Payment Modal --}}
    <div x-data="{
                show: false,
                action: '',
                balance: 0,
                init() {
                    window.addEventListener('open-record-payment', (e) => {
                        this.action  = e.detail.action;
                        this.balance = e.detail.balance;
                        this.show    = true;
                    });
                }
            }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900" @click.outside="show = false">
            <h3 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">Record Payment</h3>
            <form :action="action" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Amount Paid (Rs.) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="amount_paid" min="0" step="0.01"
                                :placeholder="'Balance: Rs. ' + parseFloat(balance).toLocaleString()"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Payment Method <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_method"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Select</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Reference / Cheque No.
                            </label>
                            <input type="text" name="reference" placeholder="Optional"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Payment Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="paid_at" value="{{ now()->toDateString() }}"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Receipt (optional)
                        </label>
                        <input type="file" name="receipt" accept="image/jpeg,image/jpg,image/png,application/pdf"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1 file:text-xs file:font-medium file:text-brand-600 dark:border-gray-700 dark:bg-gray-800">
                        <p class="mt-1 text-xs text-gray-400">JPEG, PNG or PDF. Max 3MB.</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                        <input type="text" name="notes" placeholder="Optional"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                </div>

                <div class="mt-5 flex items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                        Confirm Payment
                    </button>
                    <button type="button" @click="show = false"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk Generate Modal --}}
    <div x-data="{
                show: false,
                init() {
                    window.addEventListener('open-bulk-generate', () => { this.show = true; });
                }
            }" x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900" @click.outside="show = false">
            <h3 class="mb-1 text-base font-semibold text-gray-800 dark:text-white">Bulk Generate Payments</h3>
            <p class="mb-4 text-sm text-gray-500">Creates payment records for all active tenants with active agreements.</p>
            <form action="{{ route('payments.bulk-generate') }}" method="POST">
                @csrf
                <div class="space-y-4">

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Billing Month <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="bulk_month" name="month" placeholder="Select month" autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Due Date <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="bulk_due_date" name="due_date" placeholder="Select due date"
                            autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Generate For <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="types[]" value="rent" checked
                                    class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                Rent
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox" name="types[]" value="maintenance"
                                    class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                Maintenance
                            </label>
                        </div>
                    </div>

                </div>

                <div class="mt-5 flex items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        Generate Now
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
            new DataTable('#paymentsTable', {
                pageLength: 20,
                lengthMenu: [10, 20, 50, 100],
                order: [[4, 'desc']],
                columnDefs: [{ orderable: false, targets: [0, 9] }],
                language: {
                    search: '',
                    searchPlaceholder: 'Search payments...',
                    lengthMenu: 'Show _MENU_ per page',
                    info: 'Showing _START_ to _END_ of _TOTAL_ payments',
                },
            });

            flatpickr('#bulk_month', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F Y',
                allowInput: false,
                disableMobile: true,
                disable: [function (date) { return date.getDate() !== 1; }],
            });

            flatpickr('#bulk_due_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
            });
        });
    </script>
@endpush