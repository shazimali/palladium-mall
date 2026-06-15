<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance & Utilities Bill - {{ $payment->tenant->name ?? 'N/A' }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-border {
                border-width: 1px !important;
                border-color: #d1d5db !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col justify-between py-10 px-4 sm:px-6 lg:px-8">

    <div class="max-w-3xl w-full mx-auto bg-white rounded-2xl border border-gray-200 shadow-sm p-8 sm:p-12 relative print-border my-auto">
        
        <!-- Action Buttons (Hidden during print) -->
        <div class="absolute top-6 right-6 flex items-center gap-3 no-print">
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print Bill
            </button>
            <button onclick="window.close()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                Close Window
            </button>
        </div>

        <!-- Header -->
        <div class="border-b border-gray-100 pb-8 mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6 print-border">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">PALLADIUM MALL</h1>
                <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Consolidated Maintenance & Utilities Bill</p>
                <div class="text-xs text-gray-400 mt-3 space-y-0.5">
                    <p>Main G.T. Road, Palladium Mall, Islamabad</p>
                    <p>Contact: +92-51-1234567 | info@palladiummall.com</p>
                </div>
            </div>
            
            <div class="text-left md:text-right">
                <div class="inline-block rounded-xl px-4 py-2 bg-gray-50 text-gray-800 text-xs font-semibold print-border mb-3">
                    Billing Month: {{ $payment->month ? $payment->month->format('F Y') : 'N/A' }}
                </div>
                <div class="space-y-1 text-xs">
                    <p><span class="text-gray-400">Date Generated:</span> {{ now()->format('d M Y') }}</p>
                    <p><span class="text-gray-400">Due Date:</span> {{ $payment->due_date ? $payment->due_date->format('d M Y') : 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Meta Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8 bg-gray-50/50 rounded-2xl p-6 print-border">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Bill To (Tenant)</h3>
                <div class="space-y-1.5 text-sm">
                    <p class="font-semibold text-gray-900">{{ $payment->tenant->name ?? 'N/A' }}</p>
                    @if($payment->tenant && $payment->tenant->cnic)
                        <p class="text-gray-500 text-xs"><span class="text-gray-400">CNIC:</span> {{ $payment->tenant->cnic }}</p>
                    @endif
                    @if($payment->tenant && $payment->tenant->phone)
                        <p class="text-gray-500 text-xs"><span class="text-gray-400">Phone:</span> {{ $payment->tenant->phone }}</p>
                    @endif
                </div>
            </div>
            
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Unit Details</h3>
                <div class="space-y-1.5 text-sm">
                    <p class="font-semibold text-gray-900">Unit Number: {{ $payment->unit->unit_number ?? 'N/A' }}</p>
                    <p class="text-gray-500 text-xs"><span class="text-gray-400">Unit Type:</span> {{ $payment->unit ? ucfirst($payment->unit->type) : 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Description Table -->
        <div class="mb-8">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Billing Particulars</h3>
            
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 text-gray-400 text-xs uppercase font-semibold">
                        <th class="py-3 font-semibold">Description</th>
                        <th class="py-3 text-right font-semibold">Details / Readings</th>
                        <th class="py-3 text-right font-semibold">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $totalBilled = 0;
                        $totalPaid = 0;
                        $balanceDue = 0;
                    @endphp
                    @foreach($groupedPayments as $record)
                        @php
                            $totalBilled += $record->amount;
                            $totalPaid += $record->amount_paid;
                            $balanceDue += $record->balanceDue();
                        @endphp
                        <tr>
                            <td class="py-4">
                                <p class="font-medium text-gray-900">{{ $record->type_label }}</p>
                                <p class="text-xs text-gray-400">Ref: PM-PAY-{{ str_pad($record->id, 5, '0', STR_PAD_LEFT) }}</p>
                            </td>
                            <td class="py-4 text-right text-gray-500 text-xs">
                                @if(in_array($record->type, ['electricity', 'water', 'gas']))
                                    <div class="space-y-0.5">
                                        <p>Prev: {{ number_format($record->previous_reading, 2) }} | Curr: {{ number_format($record->current_reading, 2) }}</p>
                                        <p>Consumed: {{ number_format($record->units_consumed, 2) }} units @ Rs. {{ number_format($record->rate_per_unit, 2) }}/unit</p>
                                    </div>
                                @elseif($record->type === 'maintenance')
                                    Regular monthly maintenance fee
                                @else
                                    Particular record
                                @endif
                            </td>
                            <td class="py-4 text-right font-medium text-gray-900">
                                Rs. {{ number_format($record->amount, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals & Payment Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-8 border-t border-gray-100 print-border">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Status & Instructions</h3>
                @if($balanceDue <= 0)
                    <div class="rounded-xl border border-dashed border-green-200 bg-green-50/50 p-4 text-xs text-green-800 print-border">
                        <p class="font-semibold mb-1 uppercase tracking-wide">Paid In Full</p>
                        <p>Thank you! All maintenance and utility payments for this billing cycle have been cleared in full.</p>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-red-200 bg-red-50/50 p-4 text-xs text-red-700 print-border">
                        <p class="font-semibold mb-1">Due Date: {{ $payment->due_date ? $payment->due_date->format('d M Y') : 'N/A' }}</p>
                        <p>Please clear your outstanding maintenance and utility dues of <strong>Rs. {{ number_format($balanceDue, 2) }}</strong> to avoid late payment fines or utility service disconnections.</p>
                    </div>
                @endif
            </div>

            <div class="flex flex-col justify-end">
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Total Billed:</span>
                        <span class="font-medium text-gray-900">Rs. {{ number_format($totalBilled, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Total Paid:</span>
                        <span class="font-medium text-green-600">Rs. {{ number_format($totalPaid, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-3 print-border font-bold text-base">
                        <span class="text-gray-900">Total Balance Due:</span>
                        <span class="text-brand-600">Rs. {{ number_format($balanceDue, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer signatures -->
        <div class="mt-16 pt-8 border-t border-gray-100 flex justify-between text-center text-xs text-gray-400 print-border">
            <div class="w-32">
                <div class="border-b border-gray-200 h-10 mb-2"></div>
                <p>Tenant's Signature</p>
            </div>
            <div class="w-32">
                <div class="border-b border-gray-200 h-10 mb-2"></div>
                <p>Authorized Signature</p>
            </div>
        </div>

    </div>

    <!-- Printed footer -->
    <div class="text-center text-xs text-gray-400 mt-8 no-print">
        <p>This is a computer-generated consolidated bill and does not require a physical stamp. Printed on {{ now()->format('d M Y H:i:s') }}</p>
    </div>

</body>
</html>
