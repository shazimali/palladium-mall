<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Payable Voucher - {{ $payable->voucher_no }}</title>
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
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 transition-colors shadow-sm cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print Voucher
            </button>
            <button onclick="window.close()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm cursor-pointer">
                Close Window
            </button>
        </div>

        <!-- Header -->
        <div class="border-b border-gray-100 pb-8 mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6 print-border">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-brand-950">PALLADIUM MALL</h1>
                <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Owner Payable Voucher</p>
                <div class="text-xs text-gray-400 mt-3 space-y-0.5">
                    <p>Main G.T. Road, Palladium Mall, Islamabad</p>
                    <p>Contact: +92-51-1234567 | info@palladiummall.com</p>
                </div>
            </div>
            
            <div class="text-left md:text-right">
                <div class="inline-block rounded-xl px-4 py-2 bg-gray-50 text-gray-800 text-xs font-semibold print-border mb-3">
                    Voucher ID: {{ $payable->voucher_no }}
                </div>
                <div class="space-y-1 text-xs">
                    <p><span class="text-gray-400">Date:</span> {{ $payable->date->format('d M Y') }}</p>
                    <p><span class="text-gray-400">Printed On:</span> {{ now()->format('d M Y h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Voucher Particulars -->
        <div class="space-y-6 mb-8 text-sm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-4 print-border">
                <div class="text-gray-450 font-medium">Managing Owner:</div>
                <div class="md:col-span-2 font-bold text-gray-900 text-base">
                    {{ $payable->owner->name ?? '—' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-4 print-border">
                <div class="text-gray-450 font-medium">Amount Paid:</div>
                <div class="md:col-span-2 font-bold text-red-600 text-lg">Rs. {{ number_format($payable->amount, 2) }}/-</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-4 print-border">
                <div class="text-gray-450 font-medium">Paid From (Payment Account):</div>
                <div class="md:col-span-2 text-gray-900 font-medium">
                    {{ $payable->paymentAccount->name ?? '—' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-4 print-border">
                <div class="text-gray-450 font-medium">Reference / Cheque:</div>
                <div class="md:col-span-2 font-medium text-gray-900">{{ $payable->reference ?? '—' }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-4 print-border">
                <div class="text-gray-450 font-medium">Recorded By:</div>
                <div class="md:col-span-2 text-gray-600 font-semibold">{{ $payable->user->name ?? '—' }}</div>
            </div>
        </div>

        @if($payable->notes)
            <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 mb-8 text-sm print-border">
                <p class="font-semibold text-gray-600 mb-1">Description / Notes:</p>
                <p class="text-gray-700 leading-relaxed">{{ $payable->notes }}</p>
            </div>
        @endif

        <!-- Signature Boxes -->
        <div class="grid grid-cols-2 gap-12 pt-16 border-t border-gray-100 print-border text-center text-xs">
            <div>
                <div class="w-full border-b border-gray-300 mx-auto max-w-[200px] mb-2"></div>
                <p class="text-gray-450 uppercase font-semibold">Prepared By</p>
            </div>
            <div>
                <div class="w-full border-b border-gray-300 mx-auto max-w-[200px] mb-2"></div>
                <p class="text-gray-450 uppercase font-semibold">Managing Owner Signature</p>
            </div>
        </div>

    </div>

</body>
</html>
