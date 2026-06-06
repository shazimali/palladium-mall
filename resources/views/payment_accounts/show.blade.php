@extends('layouts.app')
 
@section('content')
    <x-common.page-breadcrumb pageTitle="Payment Account Details" />
 
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Profile Panel --}}
        <div class="lg:col-span-1 space-y-6">
            <x-common.component-card title="Account Information" desc="Configured details and status">
                <div class="space-y-4">
                    {{-- Account Name --}}
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Account Name</span>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">{{ $paymentAccount->name }}</p>
                    </div>
 
                    {{-- Bank Name --}}
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Bank Name</span>
                        <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $paymentAccount->bank_name ?? '—' }}</p>
                    </div>
 
                    {{-- Account Number --}}
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Account / Wallet Number</span>
                        <p class="font-mono text-sm text-gray-800 dark:text-white">{{ $paymentAccount->account_number ?? '—' }}</p>
                    </div>
 
                    {{-- Holder Name --}}
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Account Holder Name</span>
                        <p class="text-sm text-gray-800 dark:text-white">{{ $paymentAccount->account_holder ?? '—' }}</p>
                    </div>
 
                    {{-- Total Received --}}
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Received</span>
                        <p class="text-2xl font-black text-green-600 dark:text-green-400">
                            Rs. {{ number_format($paymentAccount->total_received ?? 0) }}
                        </p>
                    </div>
 
                    {{-- Status --}}
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Status</span>
                        <div>
                            @if($paymentAccount->is_active)
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">
                                    Active for Collections
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/20 dark:text-red-400">
                                    Inactive / Suspended
                                </span>
                            @endif
                        </div>
                    </div>
 
                    {{-- Notes --}}
                    @if($paymentAccount->notes)
                        <div class="border-t border-gray-100 pt-3 dark:border-gray-800">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Notes / Remarks</span>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $paymentAccount->notes }}</p>
                        </div>
                    @endif
 
                    {{-- Actions --}}
                    @if(auth()->user()->hasPermission('payment_accounts.edit') || auth()->user()->isSuperAdmin())
                        <div class="border-t border-gray-100 pt-4 dark:border-gray-800">
                            <a href="{{ route('payment-accounts.edit', $paymentAccount) }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Account Details
                            </a>
                        </div>
                    @endif
                </div>
            </x-common.component-card>
        </div>
 
        {{-- Associated Payments Panel --}}
        <div class="lg:col-span-2">
            <x-common.component-card title="Collection History" desc="All payment receipts received on this account">
                <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Paid Date</th>
                                <th class="px-4 py-3">Flat/Shop</th>
                                <th class="px-4 py-3">Tenant</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3 text-right">Amount Paid</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($payments as $payment)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-4 py-3 text-xs">
                                        {{ $payment->paid_at ? $payment->paid_at->format('d M Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                                            {{ $payment->unit?->unit_number ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                        {{ $payment->tenant?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $payment->type_badge_class }}">
                                            {{ $payment->type_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-green-600">
                                        Rs. {{ number_format($payment->amount_paid) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('payments.show', $payment) }}"
                                            class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white transition-colors"
                                            title="View Payment Detail">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-gray-600">
                                        No payments received on this account yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
 
                @if($payments->hasPages())
                    <div class="mt-4">
                        {{ $payments->links() }}
                    </div>
                @endif
            </x-common.component-card>
        </div>
    </div>
@endsection
