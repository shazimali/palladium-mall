@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Add Payment Record" />

    <x-common.component-card title="Add Payment Record" desc="Create a rent or maintenance payment record for a tenant">
        <form action="{{ route('payments.store') }}" method="POST">
            @csrf
            @include('payments._form')

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Record
                </button>
                <a href="{{ route('payments.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            flatpickr('#month', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F Y',
                allowInput: false,
                disableMobile: true,
                disable: [function (date) { return date.getDate() !== 1; }],
            });

            flatpickr('#due_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
            });

            // Auto-fill unit + agreement when tenant changes
            document.getElementById('tenant_id').addEventListener('change', function () {
                const tenantId = this.value;
                if (!tenantId) {
                    document.getElementById('unit_display').textContent = 'Auto-filled when tenant is selected';
                    document.getElementById('unit_id').value = '';
                    document.getElementById('agreement_id').value = '';
                    document.getElementById('amount').value = '';
                    return;
                }

                fetch(`/ajax/agreement-by-tenant?tenant_id=${tenantId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.agreement) {
                            document.getElementById('unit_display').textContent = data.agreement.unit_number;
                            document.getElementById('unit_id').value = data.agreement.unit_id;
                            document.getElementById('agreement_id').value = data.agreement.id;
                            fillAmount(data.agreement);
                        } else {
                            document.getElementById('unit_display').textContent = 'No active agreement found';
                            document.getElementById('unit_id').value = '';
                            document.getElementById('agreement_id').value = '';
                        }
                    });
            });

            // Auto-fill amount when type changes
            document.getElementById('type').addEventListener('change', function () {
                const tenantId = document.getElementById('tenant_id').value;
                if (!tenantId) return;

                fetch(`/ajax/agreement-by-tenant?tenant_id=${tenantId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.agreement) fillAmount(data.agreement);
                    });
            });

            function fillAmount(agreement) {
                const type = document.getElementById('type').value;
                const amountInput = document.getElementById('amount');

                if (type === 'rent') {
                    amountInput.value = agreement.monthly_rent;
                } else if (type === 'maintenance') {
                    amountInput.value = agreement.maintenance_charge;
                } else {
                    amountInput.value = '';
                }
            }
        });
    </script>
@endpush