@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ $otherTenant->name }}" />

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

    @if(session('error') || $errors->any())
        <div x-data="{ show: true }" x-show="show"
            class="mb-4 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
            <svg class="h-4 w-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            <div>
                @if(session('error'))
                    <div>{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Profile Header ── --}}
    <x-common.component-card title="Tenant Profile" desc="Other tenant details and current assignment">
        <div class="flex flex-col md:flex-row gap-6">
            @if($otherTenant->photo)
                <div class="flex-shrink-0">
                    <img src="{{ $otherTenant->photo_url }}" alt="{{ $otherTenant->name }}" class="h-28 w-28 rounded-xl object-cover border border-gray-200 dark:border-gray-800 shadow-sm">
                </div>
            @endif
            <div class="flex-1 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400">Full Name</p>
                    <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $otherTenant->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400">CNIC / INC</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $otherTenant->cnic ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400">Phone</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $otherTenant->phone ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400">WhatsApp</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $otherTenant->whatsapp_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400">Status</p>
                    <p class="mt-1">
                        @if($otherTenant->status === 'active')
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
                        @else
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">Inactive</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400">Current Unit</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                        @if($otherTenant->unit)
                            <span class="font-bold text-gray-900 dark:text-white">Unit {{ $otherTenant->unit->unit_number }}</span>
                            <span class="text-xs text-gray-400">— {{ $otherTenant->unit->floor?->name }} / {{ $otherTenant->unit->block?->name }}</span>
                        @else
                            <span class="text-gray-400">Not attached</span>
                        @endif
                    </p>
                </div>
                <div class="sm:col-span-2 lg:col-span-1">
                    <p class="text-xs font-medium uppercase text-gray-400">Address</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $otherTenant->address ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Quick action links --}}
        <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-gray-200 pt-4 dark:border-gray-800">
            <a href="{{ route('other-tenants.statement-print', $otherTenant) }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-extrabold text-white hover:bg-gray-800 transition-colors shadow-sm">
                🖨️ Print Statement
            </a>

            @if($otherTenant->unit)
                <form action="{{ route('units.toggle-breaker', $otherTenant->unit) }}" method="POST" class="inline">
                    @csrf
                    <button type="button" onclick="confirmAction(this.form, 'Are you sure you want to toggle the electricity breaker status for Unit {{ $otherTenant->unit->unit_number }}?', 'Toggle Breaker Status?', 'Yes, Toggle Breaker')"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-black uppercase transition-all cursor-pointer shadow-xs {{ $otherTenant->unit->isBreakerOn() ? 'bg-emerald-100 text-emerald-800 border border-emerald-300 hover:bg-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300 hover:bg-rose-200 dark:bg-rose-950/60 dark:text-rose-300' }}">
                        ⚡ BREAKER {{ strtoupper($otherTenant->unit->breaker_status ?? 'OFF') }} (Click to Toggle)
                    </button>
                </form>
            @endif

            @if(auth()->user()->hasPermission('other_tenants.edit') || auth()->user()->isSuperAdmin())
                <a href="{{ route('other-tenants.edit', $otherTenant) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            @endif
            <a href="{{ route('other-tenants.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                Back to List
            </a>
        </div>
    </x-common.component-card>

    {{-- ── Unit Attachment History ── --}}
    @php
        $lastDetached = $unitHistory->whereNotNull('detached_at')->first();
        $canEditHistory = auth()->user()->isSuperAdmin();
    @endphp
    <div class="mt-6" x-data="{
        showEditModal: false,
        historyId: '',
        unitNumber: '',
        attachedPicker: null,
        detachedPicker: null,
        openEditModal(id, unit, attached, detached) {
            this.historyId = id;
            this.unitNumber = unit;
            this.showEditModal = true;
            this.$nextTick(() => {
                if (typeof flatpickr !== 'undefined') {
                    if (this.attachedPicker) {
                        this.attachedPicker.setDate(attached, true);
                    } else if (this.$refs.attachedInput) {
                        this.attachedPicker = flatpickr(this.$refs.attachedInput, {
                            dateFormat: 'Y-m-d',
                            altInput: true,
                            altFormat: 'd M Y',
                            defaultDate: attached,
                            allowInput: false,
                            disableMobile: true,
                            static: true
                        });
                    }

                    if (this.detachedPicker) {
                        this.detachedPicker.setDate(detached, true);
                    } else if (this.$refs.detachedInput) {
                        this.detachedPicker = flatpickr(this.$refs.detachedInput, {
                            dateFormat: 'Y-m-d',
                            altInput: true,
                            altFormat: 'd M Y',
                            defaultDate: detached,
                            allowInput: false,
                            disableMobile: true,
                            static: true
                        });
                    }
                }
            });
        }
    }">
        <x-common.component-card title="Unit History" desc="Timeline of unit attachments and detachments">
            @if($unitHistory->isEmpty())
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-6 text-center text-sm text-gray-400 dark:border-gray-800 dark:bg-white/[0.02]">
                    No unit history records yet.
                </div>
            @else
                <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Unit</th>
                                <th class="px-4 py-3">Floor / Block</th>
                                <th class="px-4 py-3">Attached</th>
                                <th class="px-4 py-3">Detached</th>
                                <th class="px-4 py-3">Duration</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($unitHistory as $i => $h)
                                @php
                                    $isLastDetached = $lastDetached && $lastDetached->id === $h->id;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors {{ $isLastDetached ? 'bg-amber-50/20 dark:bg-amber-950/10' : '' }}">
                                    <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                        Unit {{ $h->unit->unit_number ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                        {{ $h->unit->floor?->name ?? '' }} — {{ $h->unit->block?->name ?? '' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-green-600 dark:text-green-400 font-medium">{{ $h->attached_at->format('d M Y') }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($h->detached_at)
                                            <span class="text-red-500 dark:text-red-400 font-medium">{{ $h->detached_at->format('d M Y') }}</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                                Current
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-400">
                                        @php
                                            $end = $h->detached_at ?? now();
                                            $diff = round($h->attached_at->diffInDays($end), 1);
                                        @endphp
                                        {{ $diff }} day{{ $diff == 1 ? '' : 's' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if($isLastDetached && $canEditHistory)
                                            <button type="button"
                                                @click="openEditModal('{{ $h->id }}', '{{ $h->unit->unit_number ?? '—' }}', '{{ $h->attached_at ? $h->attached_at->format('Y-m-d') : '' }}', '{{ $h->detached_at ? $h->detached_at->format('Y-m-d') : '' }}')"
                                                class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-300 dark:hover:bg-amber-900/50 transition-colors cursor-pointer shadow-xs">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                                </svg>
                                                Edit Dates
                                            </button>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ── Edit Dates Modal ── --}}
            @if($canEditHistory)
                <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="background: rgba(0,0,0,0.5);">
                    <div @click.outside="showEditModal = false"
                        class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">

                        <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Edit Detached Unit Dates</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Last detached record for <span class="font-semibold text-brand-600 dark:text-brand-400" x-text="'Unit ' + unitNumber"></span>
                                </p>
                            </div>
                            <button type="button" @click="showEditModal = false"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <form :action="`/other-tenants/{{ $otherTenant->id }}/unit-history/${historyId}`" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">
                                        Attached Date <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="attached_at" x-ref="attachedInput" required
                                            placeholder="Select attached date"
                                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 cursor-pointer">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">
                                        Detached Date <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="detached_at" x-ref="detachedInput" required
                                            placeholder="Select detached date"
                                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 cursor-pointer">
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-1">Detached date must be on or after attached date.</p>
                                </div>
                            </div>

                            <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                                <button type="button" @click="showEditModal = false"
                                    class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="rounded-xl bg-brand-500 px-5 py-2 text-sm font-bold text-white hover:bg-brand-600 transition-colors shadow-sm cursor-pointer">
                                    Update Dates
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </x-common.component-card>
    </div>

    {{-- ── Payment History ── --}}
    <div class="mt-6">
        <x-common.component-card title="Payment History" desc="All maintenance payments linked to this tenant">
            @if($payments->isEmpty())
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-6 text-center text-sm text-gray-400 dark:border-gray-800 dark:bg-white/[0.02]">
                    No payments found.
                </div>
            @else
                <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Month</th>
                                <th class="px-4 py-3">Unit</th>
                                <th class="px-4 py-3">Amount</th>
                                <th class="px-4 py-3">Paid</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Due Date</th>
                                <th class="px-4 py-3">Paid At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($payments as $idx => $pay)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-4 py-3 text-gray-400">{{ $payments->firstItem() + $idx }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                        {{ $pay->month->format('M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        {{ $pay->unit?->unit_number ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90">
                                        Rs. {{ number_format($pay->amount, 0) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        Rs. {{ number_format($pay->amount_paid, 0) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($pay->status === 'paid')
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Paid</span>
                                        @elseif($pay->status === 'partial')
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Partial</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">Unpaid</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ $pay->due_date?->format('d M Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ $pay->paid_at?->format('d M Y') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($payments->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-800">
                        {{ $payments->links() }}
                    </div>
                @endif
            @endif
        </x-common.component-card>
    </div>

@endsection
