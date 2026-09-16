<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->tenant->name ?? 'N/A' }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .font-urdu {
            font-family: 'Noto Nastaliq Urdu', serif;
            line-height: 1.7;
        }
        .print-sheet {
            width: 210mm;
            max-width: 100%;
        }
        @media print {
            @page {
                size: A4;
                margin: 6mm;
            }
            .no-print {
                display: none !important;
            }
            html, body {
                background-color: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-sheet {
                width: 100% !important;
                margin: 0 !important;
                box-shadow: none !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 antialiased min-h-screen flex flex-col justify-between py-10 px-4 sm:px-6 lg:px-8 text-[13px]">

    <div class="print-sheet mx-auto bg-white border-2 border-gray-800 shadow-sm relative my-auto">

        <!-- Action Buttons (Hidden during print) -->
        <div class="absolute -top-14 right-0 flex items-center gap-3 no-print">
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-700 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print Receipt
            </button>
            <button onclick="window.close()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                Close Window
            </button>
        </div>

        @php
            $unitLabel = $payment->unit
                ? trim((($payment->unit->block->name ?? null) ? $payment->unit->block->name . '-' : '') . $payment->unit->unit_number)
                : 'N/A';
        @endphp

        <!-- Top header: company / title -->
        <div class="grid grid-cols-5 border-b-2 border-gray-800">
            <div class="col-span-3 p-3 border-r-2 border-gray-800">
                <h1 class="text-lg font-extrabold tracking-tight">PALLADIUM MALL</h1>
                <p class="text-[10px] text-gray-500 mt-0.5">Garden Town, Gujranwala.</p>

                <div class="grid grid-cols-3 gap-px bg-gray-800 mt-2 text-[10px] border border-gray-800">
                    <div class="bg-gray-100 px-1.5 py-0.5 font-semibold">Billing Month</div>
                    <div class="bg-gray-100 px-1.5 py-0.5 font-semibold">Issue Date</div>
                    <div class="bg-gray-100 px-1.5 py-0.5 font-semibold">Due Date</div>
                    <div class="bg-white px-1.5 py-1">{{ $payment->month ? $payment->month->format('M-Y') : 'N/A' }}</div>
                    <div class="bg-white px-1.5 py-1">{{ now()->format('d M Y') }}</div>
                    <div class="bg-white px-1.5 py-1">{{ $payment->due_date ? $payment->due_date->format('d M Y') : 'N/A' }}</div>
                </div>
            </div>
            <div class="col-span-2 p-3 flex flex-col justify-center items-end text-right">
                <p class="text-[9px] uppercase tracking-widest text-gray-400 font-semibold">Receipt No. {{ $payment->receipt_no ?? ('PM-PAY-' . str_pad($payment->id, 5, '0', STR_PAD_LEFT)) }}</p>
                <h2 class="text-sm font-extrabold tracking-tight leading-tight">{{ strtoupper($payment->type_label) }} RECEIPT</h2>
                <p class="mt-0.5 text-[10px] font-bold uppercase
                    @if($payment->isPaid()) text-green-600
                    @elseif($payment->isPartial()) text-amber-600
                    @else text-red-600
                    @endif">
                    {{ $payment->status }}
                </p>
            </div>
        </div>

        <!-- Name & address + unit box -->
        <div class="grid grid-cols-5 border-b-2 border-gray-800">
            <div class="col-span-3 p-3 border-r-2 border-gray-800">
                <h3 class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-1">Name &amp; Address</h3>
                <p class="font-bold text-[12px]">{{ $payment->tenant->name ?? 'N/A' }}</p>
                @if($payment->tenant && $payment->tenant->cnic)
                    <p class="text-[10px] text-gray-600 mt-0.5">CNIC # {{ $payment->tenant->cnic }}</p>
                @endif
                <p class="text-[10px] text-gray-600">Unit No: {{ $unitLabel }} @if($payment->unit)&middot; {{ ucfirst($payment->unit->type) }}@endif</p>
                @if($payment->tenant && $payment->tenant->address)
                    <p class="text-[10px] text-gray-600">{{ $payment->tenant->address }}</p>
                @endif
            </div>
            <div class="col-span-2 p-3 flex items-center justify-center">
                <div class="border-2 border-gray-800 px-4 py-1.5 text-base font-extrabold tracking-wide">
                    {{ $unitLabel }}
                </div>
            </div>
        </div>

        <!-- Body: particulars + totals (left) / instructions + history (right) -->
        <div class="grid grid-cols-5">
            <div class="col-span-3 border-r-2 border-gray-800 flex flex-col">
                <table class="w-full text-left text-[11px]">
                    <thead>
                        <tr class="border-b-2 border-gray-800 bg-gray-100 text-[10px] uppercase">
                            <th class="px-2.5 py-1 font-semibold">Description</th>
                            <th class="px-2.5 py-1 text-right font-semibold">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-2.5 py-1">
                                <p class="font-medium">{{ $payment->type_label }}</p>
                                @if(in_array($payment->type, ['electricity', 'water', 'gas']))
                                    <p class="text-[9px] text-gray-500">
                                        Prev: {{ number_format($payment->previous_reading, 2) }} &middot; Curr: {{ number_format($payment->current_reading, 2) }} &middot;
                                        {{ number_format($payment->units_consumed, 2) }} units @ Rs. {{ number_format($payment->rate_per_unit, 2) }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-2.5 py-1 text-right font-medium">{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="px-2.5 py-2 border-t-2 border-gray-800 text-[10px] text-gray-600 flex-1">
                    <h4 class="text-[9px] font-bold uppercase tracking-wider text-gray-400 mb-0.5">Notes</h4>
                    @if($payment->notes)
                        <p class="leading-relaxed">{{ $payment->notes }}</p>
                    @else
                        <p class="text-gray-400 italic">&mdash;</p>
                    @endif

                    @if($payment->isPaid() || $payment->isPartial())
                        <div class="mt-1.5 space-y-0 text-[9px] text-gray-500">
                            <p><span class="text-gray-400">Payment Date:</span> {{ $payment->paid_at ? $payment->paid_at->format('d M Y h:i A') : '—' }}</p>
                            <p><span class="text-gray-400">Method:</span> {{ $payment->payment_method ? ucfirst(str_replace('_', ' ', $payment->payment_method)) : '—' }}</p>
                            @if($payment->reference)
                                <p><span class="text-gray-400">Ref / Cheque #:</span> {{ $payment->reference }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <table class="w-full text-[11px] border-t-2 border-gray-800">
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-2.5 py-1 font-semibold">Total</td>
                            <td class="px-2.5 py-1 text-right font-semibold">{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-2.5 py-1 text-gray-600">Amount Paid</td>
                            <td class="px-2.5 py-1 text-right text-green-700">{{ number_format($payment->amount_paid, 2) }}</td>
                        </tr>
                        <tr class="bg-gray-100">
                            <td class="px-2.5 py-1 font-extrabold">Balance Due</td>
                            <td class="px-2.5 py-1 text-right font-extrabold">{{ number_format($payment->balanceDue(), 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="px-2.5 py-1.5 border-t border-gray-300 text-[9px] text-gray-500 leading-relaxed">
                    <p class="font-semibold text-gray-600">Note:</p>
                    <p>1. Please retain this receipt for your records.</p>
                    <p>2. For any billing queries, contact the management office quoting the receipt number above.</p>
                </div>
            </div>

            <div class="col-span-2 flex flex-col">
                <div class="p-2.5 border-b-2 border-gray-800 bg-gray-50" dir="rtl">
                    <h4 class="font-urdu text-[12px] font-bold text-gray-600 mb-0.5 text-right">ضروری ہدایات</h4>
                    <ol class="font-urdu list-decimal list-inside text-[11px] text-gray-700 space-y-0 text-right">
                        <li>براہ کرم مقررہ تاریخ سے پہلے واجبات ادا کریں تاکہ اضافی جرمانہ عائد نہ ہو۔</li>
                        <li>ادائیگی صرف پیلاڈیم مال مینجمنٹ آفس میں جمع کروائیں۔</li>
                        <li>یہ رسید ادائیگی کے ثبوت کے طور پر محفوظ رکھیں۔</li>
                        <li>رسید میں کسی بھی غلطی کی صورت میں 7 دن کے اندر مینجمنٹ آفس سے رابطہ کریں۔</li>
                    </ol>
                </div>

                <div class="p-2.5 border-b-2 border-gray-800 flex-1">
                    <h4 class="text-[9px] font-bold uppercase tracking-wider text-gray-500 mb-1">Billing History</h4>
                    @if($billingHistory->isNotEmpty())
                        <table class="w-full text-[10px]">
                            <thead>
                                <tr class="border-b border-gray-400 text-gray-500">
                                    <th class="text-left py-0.5 font-semibold">Month</th>
                                    <th class="text-right py-0.5 font-semibold">Amount</th>
                                    <th class="text-right py-0.5 font-semibold">Received</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($billingHistory as $row)
                                    <tr>
                                        <td class="py-0.5">{{ \Illuminate\Support\Carbon::parse($row->month)->format('M-Y') }}</td>
                                        <td class="py-0.5 text-right">{{ number_format($row->billed, 0) }}</td>
                                        <td class="py-0.5 text-right">{{ number_format($row->received, 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-[10px] text-gray-400 italic">No prior billing history.</p>
                    @endif
                </div>

                <div class="p-2.5 text-[10px] text-gray-600 space-y-0">
                    <h4 class="text-[9px] font-bold uppercase tracking-wider text-gray-500 mb-0.5">Contact / Complaints</h4>
                    <p>Management Office: +92-51-1234567</p>
                    <p>Email: info@palladiummall.com</p>
                    <p>Office Timing: 10:00 AM &ndash; 6:00 PM</p>
                </div>
            </div>
        </div>

        <!-- Footer signatures -->
        <div class="flex justify-between p-3 border-t-2 border-gray-800 text-center text-[10px] text-gray-400">
            <div class="w-28">
                <div class="border-b border-gray-300 h-8 mb-1"></div>
                <p>Tenant's Signature</p>
            </div>
            <div class="w-28">
                <div class="border-b border-gray-300 h-8 mb-1"></div>
                <p>Authorized Signature</p>
            </div>
        </div>

    </div>

    <!-- Printed footer -->
    <div class="text-center text-[10px] text-gray-400 mt-4 no-print">
        <p>This is a computer-generated receipt/invoice and does not require a physical stamp. Printed on {{ now()->format('d M Y H:i:s') }}</p>
    </div>

</body>
</html>
