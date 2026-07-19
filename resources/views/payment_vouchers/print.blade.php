@php
    if ($voucher->paid_to_type === 'owner') {
        $payeeName = $voucher->owner->name ?? 'Partner';
        $payeeTypeLabel = 'Managing Owner / Partner';
    } elseif ($voucher->paid_to_type === 'tenant') {
        $payeeName = ($voucher->tenant ? $voucher->tenant->name : $voucher->other_name) . ($voucher->unit ? ' (Unit ' . $voucher->unit->unit_number . ')' : '');
        $payeeTypeLabel = 'Tenant (Refund Security Deposit)';
    } else {
        $payeeName = $voucher->party ? $voucher->party->name : $voucher->other_name;
        $payeeTypeLabel = 'Other Payee';
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Voucher - {{ $voucher->voucher_no }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
                            950: '#03233a',
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print Voucher
            </button>
            <button onclick="window.close()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                Close Window
            </button>
        </div>

        <!-- Header -->
        <div class="border-b border-gray-100 pb-8 mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6 print-border">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-brand-950">PALLADIUM MALL</h1>
                <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Management Office Payment Voucher</p>
                <div class="text-xs text-gray-400 mt-3 space-y-0.5">
                    <p>Main G.T. Road, Palladium Mall, Islamabad</p>
                    <p>Contact: +92-51-1234567 | info@palladiummall.com</p>
                </div>
            </div>
            
            <div class="text-left md:text-right">
                <div class="inline-block rounded-xl px-4 py-2 bg-gray-50 text-gray-800 text-xs font-semibold print-border mb-3">
                    Voucher ID: {{ $voucher->voucher_no }}
                </div>
                <div class="space-y-1 text-xs">
                    <p><span class="text-gray-400">Date:</span> {{ $voucher->date->format('d M Y') }}</p>
                    <p><span class="text-gray-400">Printed On:</span> {{ now()->format('d M Y h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Voucher Particulars -->
        <div class="space-y-6 mb-8 text-sm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-4 print-border">
                <div class="text-gray-450 font-medium">Paid To (Payee):</div>
                <div class="md:col-span-2 font-bold text-gray-900 text-base">{{ $payeeName }} 
                    <span class="text-xs font-semibold text-gray-450">({{ $payeeTypeLabel }})</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-4 print-border">
                <div class="text-gray-450 font-medium">Amount Paid:</div>
                <div class="md:col-span-2 font-bold text-red-600 text-lg">Rs. {{ number_format($voucher->amount, 2) }}/-</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-4 print-border">
                <div class="text-gray-450 font-medium">Amount in Words:</div>
                <div id="amount-in-words" class="md:col-span-2 italic text-gray-700 font-medium"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-4 print-border">
                <div class="text-gray-450 font-medium">Payment Mode & Account:</div>
                <div class="md:col-span-2 font-medium text-gray-800">
                    {{ $voucher->payment_method ? ucfirst(str_replace('_',' ',$voucher->payment_method)) : '—' }} 
                    @if($voucher->reference) (Ref/Cheque: {{ $voucher->reference }}) @endif
                    <span class="text-xs text-gray-450 font-normal">paid from</span> 
                    <strong>{{ $voucher->paymentAccount->name ?? '—' }}</strong>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-4 print-border">
                <div class="text-gray-450 font-medium">Advance Payout?</div>
                <div class="md:col-span-2 font-semibold">
                    @if($voucher->is_advance)
                        <span class="text-amber-600">Yes (Advance Payment)</span>
                    @else
                        <span class="text-green-600">No (Standard Payout)</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Remarks & Signature -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6 border-t border-gray-100 print-border">
            <div>
                @if($voucher->notes)
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Remarks / Notes:</h4>
                    <p class="text-xs text-gray-500 leading-relaxed italic bg-gray-50 p-3 rounded-lg">{{ $voucher->notes }}</p>
                @endif
            </div>

            <div class="flex flex-col justify-end">
                <div class="space-y-1.5 text-xs text-right text-gray-500">
                    <p><span class="text-gray-400">Created By:</span> {{ $voucher->user->name ?? '—' }}</p>
                    <p>Receiver and Office Signatures Required Below</p>
                </div>
            </div>
        </div>

        <!-- Footer signatures -->
        <div class="mt-20 pt-8 border-t border-gray-100 flex justify-between text-center text-xs text-gray-400 print-border">
            <div class="w-36">
                <div class="border-b border-gray-200 h-10 mb-2"></div>
                <p>Receiver's Signature</p>
            </div>
            <div class="w-36">
                <div class="border-b border-gray-200 h-10 mb-2"></div>
                <p>Authorized Signature</p>
            </div>
        </div>

    </div>

    <!-- Printed footer -->
    <div class="text-center text-xs text-gray-400 mt-8 no-print">
        <p>This is a computer-generated payment voucher copy. Printed on {{ now()->format('d M Y H:i:s') }}</p>
    </div>

    <!-- Script to convert number to words -->
    <script>
        function numberToWords(num) {
            const a = ['', 'One ', 'Two ', 'Three ', 'Four ', 'Five ', 'Six ', 'Seven ', 'Eight ', 'Nine ', 'Ten ', 'Eleven ', 'Twelve ', 'Thirteen ', 'Fourteen ', 'Fifteen ', 'Sixteen ', 'Seventeen ', 'Eighteen ', 'Nineteen '];
            const b = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

            if ((num = num.toString()).length > 9) return 'overflow';
            let n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
            if (!n) return '';
            let str = '';
            str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + 'Crore ' : '';
            str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + 'Lakh ' : '';
            str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + 'Thousand ' : '';
            str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + 'Hundred ' : '';
            str += (n[5] != 0) ? ((str != '') ? 'and ' : '') + (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) + 'Rupees Only' : 'Rupees Only';
            return str;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const amount = {{ (float) $voucher->amount }};
            const wordsEl = document.getElementById('amount-in-words');
            if (wordsEl) {
                wordsEl.innerText = numberToWords(Math.floor(amount));
            }
        });
    </script>

</body>
</html>
