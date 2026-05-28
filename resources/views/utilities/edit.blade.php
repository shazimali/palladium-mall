@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Utility Reading" />

    <x-common.component-card title="Edit Utility Reading" desc="Update meter reading or bill details">
        <form action="{{ route('utilities.update', $reading) }}" method="POST">
            @csrf
            @method('PUT')
            @include('utilities._form')

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Reading
                </button>
                <a href="{{ route('utilities.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                    Cancel
                </a>
                <a href="{{ route('utilities.show', $reading) }}"
                    class="ml-auto text-sm text-gray-400 hover:text-gray-600 transition-colors">
                    View Reading →
                </a>
            </div>
        </form>
    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            flatpickr('#month', {
                dateFormat: 'Y-m-01',
                altInput: true,
                altFormat: 'F Y',
                disableMobile: true,
                plugins: [
                    new monthSelectPlugin({
                        shorthand: false,
                        dateFormat: 'Y-m-01',
                        altFormat: 'F Y',
                        theme: 'light',
                    })
                ],
            });

            flatpickr('#due_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
            });

            // Auto-fill tenant when unit changes
            document.getElementById('unit_id').addEventListener('change', function () {
                const unitId = this.value;
                if (!unitId) {
                    document.getElementById('tenant_display').textContent = 'Auto-filled when unit is selected';
                    document.getElementById('tenant_id').value = '';
                    return;
                }

                fetch(`/ajax/tenant-by-unit?unit_id=${unitId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.tenant) {
                            document.getElementById('tenant_display').textContent = data.tenant.name;
                            document.getElementById('tenant_id').value = data.tenant.id;
                        } else {
                            document.getElementById('tenant_display').textContent = 'No active tenant for this unit';
                            document.getElementById('tenant_id').value = '';
                        }
                    });

                fetchPreviousReading();
            });

            document.getElementById('type').addEventListener('change', fetchPreviousReading);

            function fetchPreviousReading() {
                const unitId = document.getElementById('unit_id').value;
                const type = document.getElementById('type').value;

                if (!unitId || !type) return;

                fetch(`/ajax/previous-reading?unit_id=${unitId}&type=${type}`)
                    .then(r => r.json())
                    .then(data => {
                        document.getElementById('previous_reading').value = data.previous_reading;
                        recalculate();
                    });
            }

            ['current_reading', 'previous_reading', 'rate_per_unit'].forEach(id => {
                document.getElementById(id).addEventListener('input', recalculate);
            });

            function recalculate() {
                const prev = parseFloat(document.getElementById('previous_reading').value) || 0;
                const curr = parseFloat(document.getElementById('current_reading').value) || 0;
                const rate = parseFloat(document.getElementById('rate_per_unit').value) || 0;

                const consumed = Math.max(0, curr - prev);
                const calculated = consumed * rate;

                document.getElementById('units_consumed_display').textContent = consumed.toFixed(2);
                document.getElementById('calculated_amount_display').textContent = 'Rs. ' + calculated.toLocaleString('en-PK', { minimumFractionDigits: 2 });
                document.getElementById('bill_amount').value = calculated.toFixed(2);
            }
        });
    </script>
@endpush