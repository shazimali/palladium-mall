@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Voucher Details — {{ $voucher->voucher_no }}" />

    <x-common.component-card title="Receiving Voucher Details" desc="Voucher Reference #{{ $voucher->voucher_no }}">
        
        <div class="mb-6 flex justify-end gap-3 no-print">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('receiving_vouchers.edit') || auth()->user()->hasPermission('receiving-vouchers.edit'))
                <a href="{{ route('receiving-vouchers.edit', $voucher) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Voucher
                </a>
            @endif
            <a href="{{ route('receiving-vouchers.print', $voucher) }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print Voucher
            </a>
            <a href="{{ route('receiving-vouchers.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                Back to List
            </a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-6">
            @php
                $recipientName = '';
                if ($voucher->received_from_type === 'tenant') {
                    if ($voucher->tenant) {
                        $recipientName = $voucher->tenant->name;
                    } else {
                        $firstPayment = $voucher->payments->first();
                        $recipientName = ($firstPayment && $firstPayment->otherTenant) ? $firstPayment->otherTenant->name : 'N/A';
                    }
                } elseif ($voucher->received_from_type === 'owner') {
                    $recipientName = $voucher->owner->name ?? 'N/A';
                } else {
                    $recipientName = $voucher->other_name;
                }
            @endphp
            
            @foreach([
                ['Voucher Number',      $voucher->voucher_no],
                ['Voucher Date',        $voucher->date->format('d M Y')],
                ['Received From',       $recipientName],
                ['Received From Type',  ucfirst($voucher->received_from_type)],
                ['Voucher Amount',      'Rs. ' . number_format($voucher->amount, 2)],
                ['Payment Account',     $voucher->paymentAccount ? $voucher->paymentAccount->name : '—'],
                ['Payment Method',      $voucher->payment_method ? ucfirst(str_replace('_',' ',$voucher->payment_method)) : '—'],
                ['Reference / Cheque',  $voucher->reference ?? '—'],
                ['Recorded By',         $voucher->user->name ?? '—'],
            ] as [$label, $value])
                <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $label }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        @if($voucher->notes)
            <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03] mb-6">
                <p class="text-xs text-gray-400">Notes / Remarks</p>
                <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $voucher->notes }}</p>
            </div>
        @endif

        @if($voucher->received_from_type === 'tenant' && $voucher->payments->isNotEmpty())
            <div class="mt-8 border-t border-gray-150 pt-6 dark:border-gray-850">
                <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-750 dark:text-gray-350">
                    Paid / Allocated Bills Breakdown
                </h4>
                
                <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Flat/Shop</th>
                                <th class="px-4 py-3">Month</th>
                                <th class="px-4 py-3">Particular/Type</th>
                                <th class="px-4 py-3 text-right">Total Bill Due</th>
                                <th class="px-4 py-3 text-right">Allocated in this Receipt</th>
                                <th class="px-4 py-3 text-right">Remaining Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($voucher->payments as $payment)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white">
                                        {{ $payment->unit->unit_number ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs">{{ $payment->month ? $payment->month->format('M Y') : '—' }}</td>
                                    <td class="px-4 py-3 text-xs">{{ $payment->type_label }}</td>
                                    <td class="px-4 py-3 text-right">Rs. {{ number_format($payment->amount, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-green-600">
                                        Rs. {{ number_format($payment->pivot->amount_allocated, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-orange-500">
                                        Rs. {{ number_format($payment->balanceDue(), 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 dark:bg-gray-800 font-bold text-sm">
                                <td colspan="4" class="px-4 py-3 text-right">Total Paid/Vouchered:</td>
                                <td class="px-4 py-3 text-right text-green-600">Rs. {{ number_format($voucher->amount, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

    </x-common.component-card>
@endsection
