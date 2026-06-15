@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Payment — {{ $payment->tenant?->name ?? 'Deleted Tenant' }}" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <x-common.component-card
        title="{{ $payment->tenant?->name ?? 'Deleted Tenant' }}"
        desc="{{ ucfirst($payment->type) }} — {{ $payment->month->format('F Y') }} — Unit {{ $payment->unit?->unit_number ?? 'Deleted Unit' }}">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['Tenant',          $payment->tenant?->name ?? 'Deleted Tenant'],
                ['Unit',            $payment->unit?->unit_number ?? 'Deleted Unit'],
                ['Type',            ucfirst($payment->type)],
                ['Month',           $payment->month->format('F Y')],
                ['Amount Due',      'Rs. '.number_format($payment->amount)],
                ['Amount Paid',     'Rs. '.number_format($payment->amount_paid)],
                ['Balance',         'Rs. '.number_format($payment->balanceDue())],
                ['Payment Account', $payment->paymentAccount ? $payment->paymentAccount->name : '—'],
                ['Payment Method',  $payment->payment_method ? ucfirst(str_replace('_',' ',$payment->payment_method)) : '—'],
                ['Reference',       $payment->reference ?? '—'],
                ['Due Date',        $payment->due_date->format('d M Y')],
                ['Paid At',         $payment->paid_at ? $payment->paid_at->format('d M Y') : '—'],
                ['Status',          ucfirst($payment->status)],
            ] as [$label, $value])
                <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $label }}</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-800 dark:text-white/90">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        @if($payment->notes)
            <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-400">Notes</p>
                <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $payment->notes }}</p>
            </div>
        @endif

        @if($payment->receipt)
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <a href="{{ $payment->receipt_url }}" target="_blank"
                    class="text-sm text-brand-500 hover:underline">View Receipt</a>
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-3 pt-2">
            @if(auth()->user()->hasPermission('payments.edit') || auth()->user()->isSuperAdmin())
                <a href="{{ route('payments.edit', $payment) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    Edit Payment
                </a>
            @endif
            <a href="{{ route('payments.print', $payment) }}" target="_blank"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                {{ $payment->type === 'rent' ? 'Print Rent Bill' : (in_array($payment->type, ['maintenance', 'electricity', 'water', 'gas']) ? 'Print Maintenance Bill' : 'Print Receipt') }}
            </a>

            @if(auth()->user()->hasPermission('payments.whatsapp') || auth()->user()->isSuperAdmin())
                @php
                    $phone = $payment->tenant->whatsapp_number ?: $payment->tenant->phone;
                    $phoneClean = preg_replace('/\D/', '', $phone);
                    if (strpos($phoneClean, '0') === 0 && strlen($phoneClean) === 11) {
                        $phoneClean = '92' . substr($phoneClean, 1);
                    }

                    $typeStr = ucfirst($payment->type);
                    $monthStr = $payment->month ? $payment->month->format('M Y') : '';
                    $amountStr = number_format($payment->amount);
                    $paidStr = number_format($payment->amount_paid);
                    $dueDateStr = $payment->due_date ? $payment->due_date->format('d M Y') : '';
                    $statusStr = ucfirst($payment->status);
                    $paymentUrl = $payment->public_url;

                    $message = "Dear {$payment->tenant->name},\n\nThis is a notification for your {$typeStr} payment towards Unit {$payment->unit->unit_number} for {$monthStr}.\n\nBill Details:\n- Type: {$typeStr}\n- Month: {$monthStr}\n- Total Amount: Rs. {$amountStr}\n- Amount Paid: Rs. {$paidStr}\n- Due Date: {$dueDateStr}\n- Status: {$statusStr}\n\nYou can view/print your bill copy here: {$paymentUrl}\n\nRegards,\nPalladium Mall Management";
                    $whatsappUrl = "https://api.whatsapp.com/send?phone=" . urlencode($phoneClean) . "&text=" . urlencode($message);
                @endphp
                <a href="{{ $whatsappUrl }}" target="_blank"
                    class="inline-flex items-center gap-2 rounded-lg border border-green-300 bg-white px-4 py-2.5 text-sm font-medium text-green-600 hover:bg-green-50 dark:border-green-800 dark:bg-gray-800 dark:text-green-400 dark:hover:bg-green-950/20 transition-colors">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.012 2c-5.506 0-9.988 4.482-9.988 9.988 0 1.76.46 3.413 1.258 4.868L2 22l5.29-1.387c1.405.766 3 1.205 4.722 1.205 5.506 0 9.988-4.482 9.988-9.988C22 6.482 17.518 2 12.012 2zm6.262 14.373c-.258.73-1.468 1.413-2.025 1.48-.48.06-1.106.1-3.23-.787-2.716-1.137-4.46-3.906-4.594-4.088-.135-.183-.996-1.328-.996-2.534s.623-1.802.846-2.052c.222-.25.48-.312.642-.312.163 0 .326.01.467.01.147.01.343-.06.538.41.196.48.674 1.638.73 1.75.056.113.093.243.017.393-.075.15-.112.24-.225.37-.113.13-.238.29-.338.39-.11.1-.225.21-.096.43.128.22.57 1.004 1.22 1.58.84.75 1.55.98 1.77 1.1.22.12.35.1.48-.05.13-.15.56-.65.71-.87.15-.22.3-.18.5-.1.21.08 1.32.62 1.55.73.23.11.38.16.44.27.06.1.06.59-.19 1.32z"/>
                    </svg>
                    Share on WhatsApp
                </a>
            @endif

            <a href="{{ route('payments.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                Back to Payments
            </a>
        </div>
    </x-common.component-card>
@endsection