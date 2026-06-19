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

    {{-- ── Profile Header ── --}}
    <x-common.component-card title="Tenant Profile" desc="Other tenant details and current assignment">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
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

        {{-- Quick action links --}}
        <div class="mt-6 flex items-center gap-3 border-t border-gray-200 pt-4 dark:border-gray-800">
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
    <div class="mt-6">
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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($unitHistory as $i => $h)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
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
                                            <span class="text-red-500 dark:text-red-400">{{ $h->detached_at->format('d M Y') }}</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                                Current
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-400">
                                        @php
                                            $end = $h->detached_at ?? now();
                                            $diff = $h->attached_at->diffInDays($end);
                                        @endphp
                                        {{ $diff }} day{{ $diff === 1 ? '' : 's' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
