<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Receipt Voucher - {{ $voucher->voucher_no }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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
            .voucher-container {
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 antialiased min-h-screen flex flex-col justify-between py-8 px-4 sm:px-6">

    @php
        $recipientName = '—';
        $receivedFromTypeLabel = 'Party Head';
        if ($voucher->received_from_type === 'party') {
            $recipientName = $voucher->party->name ?? 'N/A';
            $receivedFromTypeLabel = 'Party Head';
        } elseif ($voucher->received_from_type === 'account') {
            $recipientName = $voucher->fromPaymentAccount ? $voucher->fromPaymentAccount->name . ' (' . ucfirst($voucher->fromPaymentAccount->type) . ')' : 'N/A';
            $receivedFromTypeLabel = 'Source Account';
        } elseif ($voucher->received_from_type === 'landlord') {
            $recipientName = $voucher->landlord->name ?? 'N/A';
            $receivedFromTypeLabel = 'Landlord / Owner';
        }
    @endphp

    <!-- ACTION BUTTONS (HIDDEN DURING PRINT) -->
    <div class="max-w-4xl w-full mx-auto mb-4 flex flex-wrap items-center justify-between gap-3 no-print">
        <div class="flex items-center gap-2">
            <a href="{{ route('general-receiving-vouchers.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 transition-all shadow-2xs cursor-pointer">
                ← Back to List
            </a>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-blue-700 transition-all cursor-pointer">
                🖨️ Print Voucher
            </button>
            <button onclick="if(window.history.length > 1) { window.history.back(); } else { window.close(); }"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all shadow-2xs cursor-pointer">
                Close
            </button>
        </div>
    </div>

    <!-- REFINED PRINTABLE VOUCHER CONTAINER -->
    <div
        class="max-w-4xl w-full mx-auto voucher-container rounded-3xl bg-white p-6 sm:p-10 border border-gray-300 shadow-xl text-gray-900 font-sans my-auto">

        <!-- CENTERED TITLE WITH COMPANY BRANDING & RIGHT CORNER VOUCHER NUMBER -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200">
            <div class="hidden sm:block w-36"></div>
            <div class="text-center">
                <h1 class="text-2xl sm:text-3xl font-black tracking-wider text-gray-900 uppercase mb-0.5">
                    PALLADIUM MALL
                </h1>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
                    Management Office
                </p>
                <h2 class="text-xl sm:text-2xl font-black tracking-tight text-blue-700 uppercase">
                    General Receiving Voucher
                </h2>
            </div>
            <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 border border-gray-300 px-4 py-2 shadow-2xs">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Voucher No:</span>
                <span class="text-base sm:text-lg font-black font-mono text-blue-700">{{ $voucher->voucher_no }}</span>
            </div>
        </div>

        <!-- TOP METADATA GRID -->
        <div class="grid grid-cols-2 gap-[2px] bg-gray-300 rounded-2xl overflow-hidden mb-5 border border-gray-300">
            
            <!-- Row 1, Col 1: Date -->
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">
                    Voucher Date</div>
                <div
                    class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-base sm:text-lg">
                    {{ $voucher->date->format('M. d, Y') }}
                </div>
            </div>

            <!-- Row 1, Col 2: Received From -->
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">
                    {{ $receivedFromTypeLabel }}</div>
                <div
                    class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-base sm:text-lg">
                    {{ $recipientName }}
                </div>
            </div>

            <!-- Row 2: Deposit Account -->
            <div class="grid grid-cols-3 min-h-[48px] col-span-2">
                <div
                    class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">
                    Deposit Account</div>
                <div
                    class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-base sm:text-lg">
                    {{ $voucher->paymentAccount ? $voucher->paymentAccount->name . ' (' . ucfirst($voucher->paymentAccount->type) . ')' : '—' }}
                </div>
            </div>

        </div>

        <!-- MIDDLE STACKED GRID -->
        <div class="flex flex-col gap-[2px] bg-gray-300 rounded-2xl overflow-hidden mb-5 border border-gray-300">
            
            <!-- Row 1: Payment Amount -->
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">
                    Payment Amount</div>
                <div
                    class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-xl sm:text-2xl font-mono text-emerald-700">
                    Rs. {{ number_format($voucher->amount, 2) }}
                </div>
            </div>

        </div>

        <!-- BOTTOM GRID SECTION -->
        <div class="grid grid-cols-3 gap-3 items-stretch">

            <!-- Left Box: Approved by Box -->
            <div
                class="bg-gray-50 text-gray-900 rounded-2xl p-4 flex flex-col justify-center border border-gray-300 shadow-xs">
                <p class="text-xs sm:text-sm font-bold text-gray-700">
                    Approved by: <span
                        class="text-blue-700 font-extrabold ml-1">{{ $voucher->user->name ?? 'Management' }}</span>
                </p>
            </div>

            <!-- Right Box: Remarks -->
            <div class="col-span-2 bg-gray-50 border border-gray-300 rounded-2xl p-4">
                <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Remarks:
                </p>
                <p class="text-base sm:text-lg font-black text-gray-900 leading-relaxed">
                    {{ $voucher->notes ?? 'No specific remarks entered.' }}
                </p>
            </div>

        </div>

    </div>

    <!-- Printed footer -->
    <div class="text-center text-xs text-gray-400 mt-6 no-print">
        <p>Computer-generated receipt voucher. Printed on {{ now()->format('d M Y H:i:s') }}</p>
    </div>

</body>

</html>
