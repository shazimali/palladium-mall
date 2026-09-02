@extends('layouts.app')

@push('styles')
<style>
    .flatpickr-calendar {
        z-index: 999999 !important;
    }
</style>
@endpush

@section('containerClass', 'max-w-7xl mx-auto px-4 py-6')

@section('content')
<div x-data="{ 
    selectedAction: '{{ $action }}',
    showPreview: {{ $stagingItems !== null ? 'true' : 'false' }},
    hideStaging() {
        this.showPreview = false;
    }
}" 
x-init="$watch('selectedAction', () => hideStaging())"
class="space-y-6">

    {{-- Breadcrumb & Top Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
                <a href="{{ route('payments.index') }}" class="hover:text-brand-500 transition-colors">Billings &amp; Payments</a>
                <span>/</span>
                <span class="text-gray-800 dark:text-white/90">Bulk Operations Tool</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">⚡ Bulk Payments Operations &amp; Staging Management</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400">Configure bulk criteria, preview all affected payment records in staging view, and safely update the database.</p>
        </div>
        <div>
            <a href="{{ route('payments.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors shadow-xs">
                ← Back to Billings List
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-800 dark:bg-green-950/40 dark:text-green-300 flex items-center gap-3">
            <span class="text-xl">✅</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300 flex items-center gap-3">
            <span class="text-xl">⚠️</span>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- STEP 1: CRITERIA SELECTION FORM --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="text-base font-black uppercase tracking-wider text-gray-900 dark:text-white mb-4 pb-3 border-b border-gray-100 dark:border-gray-800 flex items-center gap-2">
            <span>1️⃣ Select Action &amp; Target Criteria</span>
        </h3>

        <form action="{{ route('payments.bulk-preview') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Action Selector Radio Cards --}}
            <div>
                <label class="block text-xs font-black uppercase text-gray-600 dark:text-gray-400 mb-2">Select Bulk Action *</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Bulk Generate --}}
                    <label class="relative flex cursor-pointer rounded-2xl border-2 p-4 transition-all"
                        :class="selectedAction === 'bulk_generate' ? 'border-brand-500 bg-brand-50/50 dark:bg-brand-950/20 dark:border-brand-500' : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800/40'">
                        <input type="radio" name="action" value="bulk_generate" x-model="selectedAction" class="sr-only">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">✨</span>
                            <div>
                                <span class="block text-sm font-black text-gray-900 dark:text-white">Bulk Generate</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Generate rent &amp; maintenance billings for all active tenants in batch.</span>
                            </div>
                        </div>
                    </label>

                    {{-- Bulk Edit --}}
                    <label class="relative flex cursor-pointer rounded-2xl border-2 p-4 transition-all"
                        :class="selectedAction === 'bulk_edit' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/20 dark:border-amber-500' : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800/40'">
                        <input type="radio" name="action" value="bulk_edit" x-model="selectedAction" class="sr-only">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">✏️</span>
                            <div>
                                <span class="block text-sm font-black text-gray-900 dark:text-white">Bulk Edit</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Batch edit billing month, due date, or amounts for matching unpaid records.</span>
                            </div>
                        </div>
                    </label>

                    {{-- Bulk Delete --}}
                    <label class="relative flex cursor-pointer rounded-2xl border-2 p-4 transition-all"
                        :class="selectedAction === 'bulk_delete' ? 'border-red-500 bg-red-50/50 dark:bg-red-950/20 dark:border-red-500' : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-800/40'">
                        <input type="radio" name="action" value="bulk_delete" x-model="selectedAction" class="sr-only">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">🗑️</span>
                            <div>
                                <span class="block text-sm font-black text-gray-900 dark:text-white">Bulk Delete</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">Purge matching unpaid billing records for a specific month.</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Target Filters Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 p-5 rounded-2xl bg-gray-50 dark:bg-white/[0.02] border border-gray-100 dark:border-gray-800">
                
                {{-- Billing Month --}}
                <div>
                    <label class="block text-xs font-black uppercase text-gray-700 dark:text-gray-300 mb-1.5">Target Month *</label>
                    <input type="text" id="target_month_input" name="month" value="{{ $month }}" placeholder="Select Month" required @change="hideStaging()"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm font-bold text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer focus:ring-2 focus:ring-brand-500/30">
                    @error('month') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Due Date --}}
                <div>
                    <label class="block text-xs font-black uppercase text-gray-700 dark:text-gray-300 mb-1.5">Target Due Date</label>
                    <input type="text" id="target_due_date_input" name="due_date" value="{{ $dueDate }}" placeholder="Select Due Date" @change="hideStaging()"
                        class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm font-bold text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer focus:ring-2 focus:ring-brand-500/30">
                    @error('due_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Payment Types Checkboxes --}}
                <div>
                    <label class="block text-xs font-black uppercase text-gray-700 dark:text-gray-300 mb-1.5">Payment Types *</label>
                    <div class="flex flex-wrap gap-2.5 pt-1">
                        @php
                            $availableTypes = [
                                'rent'        => 'Rent',
                                'maintenance' => 'Maintenance',
                            ];
                            $selectedTypes = (array) $types;
                        @endphp
                        @foreach($availableTypes as $tKey => $tLabel)
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-50">
                                <input type="checkbox" name="types[]" value="{{ $tKey }}" {{ in_array($tKey, $selectedTypes) ? 'checked' : '' }} @change="hideStaging()" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span>{{ $tLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('types') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

            </div>

            {{-- Optional Fields for Bulk Edit --}}
            <div x-show="selectedAction === 'bulk_edit'" x-cloak class="p-5 rounded-2xl border border-amber-200 bg-amber-50/40 dark:border-amber-900/40 dark:bg-amber-950/10 space-y-4">
                <h4 class="text-xs font-black uppercase tracking-wider text-amber-800 dark:text-amber-400">Proposed New Values for Bulk Edit (Optional)</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">New Month</label>
                        <input type="text" id="new_month_input" name="new_month" value="{{ $newMonth }}" placeholder="New Month" @change="hideStaging()"
                            class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">New Due Date</label>
                        <input type="text" id="new_due_date_input" name="new_due_date" value="{{ $newDueDate }}" placeholder="New Due Date" @change="hideStaging()"
                            class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Override Amount (PKR)</label>
                        <input type="number" step="0.01" name="new_amount" value="{{ $newAmount }}" placeholder="Leave blank to keep existing" @input="hideStaging()"
                            class="w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>
            </div>

            {{-- Submit Preview Button --}}
            <div class="flex justify-end pt-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-3 text-sm font-black text-white hover:bg-brand-700 transition-colors shadow-md cursor-pointer">
                    <span>🔍 Preview Staging Impact</span>
                </button>
            </div>
        </form>
    </div>

    {{-- STEP 2: STAGING PREVIEW TABLE (BEFORE DATABASE UPDATE) --}}
    @if($stagingItems !== null)
        <div x-show="showPreview" x-data="{ selectAll: true }" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900 space-y-6">
            
            {{-- Staging Banner --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between p-4 rounded-xl border bg-gray-50 dark:bg-white/[0.02] border-gray-200 dark:border-gray-800 gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-black text-gray-900 dark:text-white">2️⃣ Live Staging Preview</h3>
                        <span class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-xs font-black uppercase
                            {{ $action === 'bulk_generate' ? 'bg-brand-100 text-brand-800 dark:bg-brand-950 dark:text-brand-300' : ($action === 'bulk_edit' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300') }}">
                            {{ str_replace('_', ' ', $action) }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Review the matched payment records below. Uncheck any item you do not wish to execute before clicking <strong>Update Database</strong>.</p>
                </div>
                <div class="flex items-center gap-4 text-right">
                    <div>
                        <span class="block text-xs uppercase font-bold text-gray-500">Eligible Records</span>
                        <span class="text-lg font-black text-gray-900 dark:text-white">{{ number_format($summaryInfo['total_count']) }}</span>
                    </div>
                    <div class="border-l border-gray-300 dark:border-gray-700 pl-4">
                        <span class="block text-xs uppercase font-bold text-gray-500">Total Financial Impact</span>
                        <span class="text-lg font-black text-green-600 dark:text-green-400">PKR {{ number_format($summaryInfo['total_amount']) }}</span>
                    </div>
                </div>
            </div>

            @if(empty($stagingItems))
                <div class="py-12 text-center text-gray-400">
                    <span class="text-4xl">🔍</span>
                    <p class="text-base font-bold mt-2">No matching payment records found for the selected criteria.</p>
                </div>
            @else
                <form id="bulk-commit-form" action="{{ route('payments.bulk-commit') }}" method="POST" onsubmit="event.preventDefault(); confirmBulkCommit(this);">
                    @csrf
                    <input type="hidden" name="action" value="{{ $action }}">
                    <input type="hidden" name="payload" value="{{ json_encode($stagingItems) }}">

                    <div class="overflow-x-auto font-sans border-2 border-gray-200 rounded-2xl dark:border-gray-800 mb-6">
                        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-300">
                            <thead class="text-xs uppercase font-black bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-center">
                                        <input type="checkbox" x-model="selectAll" @change="$el.closest('table').querySelectorAll('.item-cb').forEach(cb => cb.checked = selectAll)" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                    </th>
                                    <th class="px-4 py-3">Tenant / Occupant</th>
                                    <th class="px-4 py-3">Unit</th>
                                    <th class="px-4 py-3">Month</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3 text-right">Current Amount</th>
                                    <th class="px-4 py-3 text-right">Proposed Amount</th>
                                    <th class="px-4 py-3">Due Date</th>
                                    <th class="px-4 py-3">Staging Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($stagingItems as $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors {{ !$item['eligible'] ? 'opacity-50 bg-gray-50/50' : '' }}">
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" name="selected_keys[]" value="{{ $item['key'] }}"
                                                {{ $item['eligible'] ? 'checked' : 'disabled' }}
                                                class="item-cb h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                        </td>
                                        <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                            {{ $item['tenant_name'] }}
                                        </td>
                                        <td class="px-4 py-3 font-black text-gray-800 dark:text-gray-200">
                                            {{ $item['unit_number'] }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            {{ Carbon\Carbon::parse($item['month'])->format('M Y') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                                {{ ucfirst($item['type']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold">
                                            PKR {{ number_format($item['current_amount']) }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-black text-green-600 dark:text-green-400">
                                            PKR {{ number_format($item['proposed_amount']) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap font-medium text-xs">
                                            {{ !empty($item['new_due_date']) ? Carbon\Carbon::parse($item['new_due_date'])->format('d M Y') : (Carbon\Carbon::parse($item['due_date'])->format('d M Y')) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($item['eligible'])
                                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-black bg-green-100 text-green-800 dark:bg-green-950/60 dark:text-green-300">
                                                    ✓ {{ $item['status'] }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-black bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                                    🚫 {{ $item['status'] }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Final Commit Footer --}}
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-gray-900 text-white dark:bg-brand-950 shadow-xl">
                        <div>
                            <span class="block text-xs uppercase tracking-wider text-gray-400 font-bold">Ready to Finalize?</span>
                            <span class="text-sm font-semibold text-gray-200">Review selected items above. Clicking the button will execute changes to the database.</span>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-7 py-3.5 text-base font-black text-white hover:bg-green-700 transition-colors shadow-lg cursor-pointer">
                            <span>🚀 Update Database</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>
    @endif

</div>
@endsection

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#target_month_input', {
            dateFormat: 'Y-m-01',
            altInput: true,
            altFormat: 'F Y',
            allowInput: false,
            disableMobile: true,
            plugins: [
                new monthSelectPlugin({
                    shorthand: false,
                    dateFormat: 'Y-m-01',
                    altFormat: 'F Y',
                    theme: 'light',
                })
            ]
        });

        flatpickr('#target_due_date_input', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            disableMobile: true,
        });

        flatpickr('#new_month_input', {
            dateFormat: 'Y-m-01',
            altInput: true,
            altFormat: 'F Y',
            allowInput: false,
            disableMobile: true,
            plugins: [
                new monthSelectPlugin({
                    shorthand: false,
                    dateFormat: 'Y-m-01',
                    altFormat: 'F Y',
                    theme: 'light',
                })
            ]
        });

        flatpickr('#new_due_date_input', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',
            disableMobile: true,
        });
    }
});

function confirmBulkCommit(form) {
    Swal.fire({
        title: 'Commit Operations?',
        text: 'Are you sure you want to commit these selected operations to the database?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, Commit Operations'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>
@endpush
@endonce
