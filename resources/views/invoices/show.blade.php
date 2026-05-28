@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Invoice — {{ $invoice->invoice_number }}" />

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
        title="{{ $invoice->invoice_number }}"
        desc="{{ $invoice->tenant->name }} · {{ $invoice->unit->unit_number }} · {{ $invoice->month->format('F Y') }}">

        {{-- Invoice header info --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach([
                ['Invoice No.',  $invoice->invoice_number],
                ['Tenant',       $invoice->tenant->name],
                ['Unit',         $invoice->unit->unit_number],
                ['Month',        $invoice->month->format('F Y')],
                ['Due Date',     $invoice->due_date->format('d M Y')],
                ['Status',       ucfirst($invoice->status)],
                ['Subtotal',     'Rs. '.number_format($invoice->subtotal)],
                ['Total',        'Rs. '.number_format($invoice->total)],
                ['Sent At',      $invoice->sent_at ? $invoice->sent_at->format('d M Y H:i') : '—'],
            ] as [$label, $value])
                <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $label }}</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-800 dark:text-white/90">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        {{-- Items --}}
        <div>
            <h4 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Invoice Items</h4>
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Type</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($invoice->items as $i => $item)
                            <tr class="{{ $i % 2 === 0 ? '' : 'bg-gray-50 dark:bg-white/[0.02]' }}">
                                <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-gray-800 dark:text-white/90">{{ $item->description }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $item->type_badge_class }}">
                                        {{ ucfirst($item->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-white/90">
                                    Rs. {{ number_format($item->amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-brand-500">
                            <td colspan="3" class="px-4 py-3 text-right text-sm font-bold text-white">Total</td>
                            <td class="px-4 py-3 text-right text-base font-bold text-white">
                                Rs. {{ number_format($invoice->total, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Notes --}}
        @if($invoice->notes)
            <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-400">Notes</p>
                <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $invoice->notes }}</p>
            </div>
        @endif

        {{-- Action buttons --}}
        <div class="flex flex-wrap items-center gap-3 pt-2">

            {{-- Download PDF --}}
            @if($invoice->pdf_path)
                <a href="{{ route('invoices.download', $invoice) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download PDF
                </a>
            @endif

            {{-- Mark Sent --}}
            @if($invoice->isDraft())
                <form action="{{ route('invoices.mark-sent', $invoice) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-600 transition-colors">
                        Mark as Sent
                    </button>
                </form>
            @endif

            {{-- Mark Paid --}}
            @if(! $invoice->isPaid())
                <form action="{{ route('invoices.mark-paid', $invoice) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                        Mark as Paid
                    </button>
                </form>
            @endif

            {{-- Edit (draft only) --}}
            @if($invoice->isDraft() && (auth()->user()->hasPermission('invoices.edit') || auth()->user()->isSuperAdmin()))
                <a href="{{ route('invoices.edit', $invoice) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-brand-500 px-4 py-2.5 text-sm font-medium text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors">
                    Edit Invoice
                </a>
            @endif

            <a href="{{ route('invoices.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                Back to Invoices
            </a>
        </div>
    </x-common.component-card>
@endsection