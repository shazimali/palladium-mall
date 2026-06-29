@extends('layouts.app')

@section('content')
    @php
        $isSelf = is_null($payment->tenant_id);
        $pageTitle = $isSelf ? 'Edit Other-Owned Unit Payment' : 'Edit Tenant Payment';
        $descText = $isSelf 
            ? 'Update maintenance payment details for an other-owned unit' 
            : 'Update payment record details linked to a tenant\'s agreement';
    @endphp

    <x-common.page-breadcrumb pageTitle="{{ $pageTitle }}" />

    @if($isSelf)
        {{-- ── SELF-UNIT MAINTENANCE EDIT FORM ─────────────────────────── --}}
        <div x-data="{
            selfUnitId: '{{ old('unit_id', $payment->unit_id) }}',
            selfAmount: '{{ old('amount', $payment->amount) }}',
            selfUnits: {{ $selfUnits->map(fn($u) => [
                'id'      => $u->id,
                'label'   => $u->unit_number . ($u->floor ? ' — ' . $u->floor->name : '') . ($u->block ? ' / ' . $u->block->name : ''),
                'charge'  => $u->default_maintenance_charge ?? 0,
            ])->values()->toJson() }},
            selectSelfUnit(id) {
                this.selfUnitId = id;
                const u = this.selfUnits.find(x => x.id == id);
                if (u && u.charge) this.selfAmount = u.charge;
            }
        }">
            <x-common.component-card title="Other-Owned Unit — Maintenance Payment" desc="{{ $descText }}">
                <form action="{{ route('payments.update', $payment) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Unit Selection --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                            Unit Selection
                        </h4>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            {{-- Unit dropdown --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Select Unit <span class="text-red-500">*</span>
                                </label>
                                <select name="unit_id" id="self_unit_id"
                                    @change="selectSelfUnit($event.target.value)"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('unit_id') ? 'border-red-400' : '' }}">
                                    <option value="">Select unit</option>
                                    @foreach($selfUnits as $su)
                                        <option value="{{ $su->id }}"
                                            data-charge="{{ $su->default_maintenance_charge ?? 0 }}"
                                            {{ old('unit_id', $payment->unit_id) == $su->id ? 'selected' : '' }}>
                                            {{ $su->unit_number }}
                                            {{ $su->floor ? '— ' . $su->floor->name : '' }}
                                            {{ $su->block ? '/ ' . $su->block->name : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Fixed charge info --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Fixed Monthly Charge
                                </label>
                                <div x-show="selfUnitId && selfAmount"
                                    class="flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2.5 dark:border-green-800/40 dark:bg-green-900/20">
                                    <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-sm font-semibold text-green-700 dark:text-green-300">
                                        Rs. <span x-text="Number(selfAmount).toLocaleString('en-PK')"></span>
                                        <span class="text-xs font-normal text-green-500">/ month</span>
                                    </p>
                                </div>
                                <div x-show="!selfUnitId || !selfAmount"
                                    class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-400 dark:border-gray-700 dark:bg-gray-800">
                                    Select a unit to see the charge
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Details --}}
                    <div class="mt-5 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                            Maintenance Payment Details
                        </h4>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                            {{-- Type — locked --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                                <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Maintenance
                                </div>
                                <input type="hidden" name="type" value="maintenance">
                            </div>

                            {{-- Month --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Month <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="self_month" name="month"
                                    value="{{ old('month', $payment->month ? $payment->month->format('Y-m-d') : '') }}"
                                    placeholder="Select month" autocomplete="off"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('month') ? 'border-red-400' : '' }}">
                                @error('month')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Amount --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Amount (Rs.) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="self_amount" name="amount"
                                    x-model="selfAmount"
                                    min="0" step="0.01"
                                    placeholder="Auto-filled from unit charge"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('amount') ? 'border-red-400' : '' }}">
                                <p class="mt-1 text-xs text-gray-400">Auto-filled from the unit's fixed charge. Override if needed.</p>
                                @error('amount')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Due Date --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Due Date <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="self_due_date" name="due_date"
                                    value="{{ old('due_date', $payment->due_date ? $payment->due_date->format('Y-m-d') : '') }}"
                                    placeholder="Select due date" autocomplete="off"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('due_date') ? 'border-red-400' : '' }}">
                                @error('due_date')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        {{-- Notes --}}
                        <div class="mt-5">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <input type="text" name="notes" value="{{ old('notes', $payment->notes) }}"
                                placeholder="Any additional notes..."
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                    </div>

                    <div class="mt-5 flex items-center gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Update Record
                        </button>
                        <a href="{{ route('payments.show', $payment) }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </x-common.component-card>
        </div>
    @else
        {{-- ── TENANT PAYMENT EDIT FORM ─────────────────────────────────── --}}
        <x-common.component-card title="Tenant Payment" desc="{{ $descText }}">
            <form action="{{ route('payments.update', $payment) }}" method="POST">
                @csrf
                @method('PUT')
                @include('payments._form')

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Record
                    </button>
                    <a href="{{ route('payments.show', $payment) }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </x-common.component-card>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if($isSelf)
                // ── Self-unit form pickers ───────────────────────────────────────
                flatpickr('#self_month', {
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

                flatpickr('#self_due_date', {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    disableMobile: true,
                });
            @else
                // ── Tenant form pickers ──────────────────────────────────────────
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

                // ── Tenant auto-fill ─────────────────────────────────────────────
                const tenantSelect = document.getElementById('tenant_id');
                const typeSelect   = document.getElementById('type');

                if (tenantSelect) {
                    tenantSelect.addEventListener('change', function () {
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
                }

                if (typeSelect) {
                    typeSelect.addEventListener('change', function () {
                        const tenantId = document.getElementById('tenant_id')?.value;
                        if (!tenantId) return;

                        fetch(`/ajax/agreement-by-tenant?tenant_id=${tenantId}`)
                            .then(r => r.json())
                            .then(data => {
                                if (data.agreement) fillAmount(data.agreement);
                            });
                    });
                }

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
            @endif
        });
    </script>
@endpush