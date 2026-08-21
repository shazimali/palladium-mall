<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Receipt Voucher - {{ $voucher->voucher_no }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0.5cm;
            }
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
                font-weight: bold !important;
                zoom: 0.8;
            }
            .max-w-3xl, .max-w-5xl, .max-w-6xl {
                max-width: 100% !important;
                padding: 5px !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            .print-border {
                border-width: 1px !important;
                border-color: #d1d5db !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800 antialiased min-h-screen flex flex-col justify-between py-8 px-4 sm:px-6">

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

    <!-- ACTION BUTTONS (HIDDEN DURING PRINT) -->
    <div class="max-w-4xl w-full mx-auto mb-4 flex flex-wrap items-center justify-between gap-3 no-print">
        <div class="flex items-center gap-2">
            <a href="{{ route('receiving-vouchers.index') }}"
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

        <!-- HEADER: CENTERED TITLE WITH COMPANY BRANDING & RIGHT CORNER VOUCHER NUMBER -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200">
            <div class="hidden sm:block w-36"></div>
            <div class="text-center">
                <h1 class="text-2xl sm:text-3xl font-black tracking-wider text-gray-900 uppercase mb-0.5">
                    PALLADIUM MALL
                </h1>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">
                    Management Office
                </p>
                <h2 class="text-xl sm:text-2xl font-black tracking-tight text-blue-700 uppercase">
                    Tenant Receiving Voucher
                </h2>
            </div>
            <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 border border-gray-300 px-4 py-2 shadow-2xs">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Voucher No:</span>
                <span class="text-base sm:text-lg font-black font-mono text-blue-700">{{ $voucher->voucher_no }}</span>
            </div>
        </div>

        <!-- 2-COLUMN SIDE-BY-SIDE FORM GRID LAYOUT -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6 items-start">
            
            <!-- LEFT COLUMN: Date, Flat/Shop, Tenant Name, Payment Amount, Payment Method -->
            <div class="flex flex-col gap-[2px] bg-gray-300 rounded-2xl overflow-hidden border border-gray-300">
                
                <!-- Field 1: Voucher Date -->
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">Voucher Date</div>
                    <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-base sm:text-lg">
                        {{ $voucher->date->format('M. d, Y') }}
                    </div>
                </div>

                <!-- Field 2: Flat / Shop -->
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">Flat / Shop</div>
                    <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-base sm:text-lg">
                        {{ $voucher->display_unit_number !== '—' ? $voucher->display_unit_number : 'N/A' }}
                    </div>
                </div>

                <!-- Field 3: Tenant Name -->
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">Tenant Name</div>
                    <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-extrabold text-sm sm:text-base">
                        {{ $recipientName }}
                    </div>
                </div>

                <!-- Field 4: Payment Amount -->
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">Payment Amount</div>
                    <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-lg sm:text-xl font-mono text-emerald-700">
                        Rs. {{ number_format($voucher->amount, 2) }}
                    </div>
                </div>

                <!-- Field 5: Payment Method -->
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">Payment Method</div>
                    <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-base sm:text-lg">
                        {{ $voucher->paymentAccount ? $voucher->paymentAccount->name : '—' }}
                        @if($voucher->payment_method)
                            <span class="ml-2 text-xs font-semibold text-gray-500">({{ ucfirst(str_replace('_', ' ', $voucher->payment_method)) }})</span>
                        @endif
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: ONLY Payments List -->
            <div class="flex flex-col gap-[2px] bg-gray-300 rounded-2xl border border-gray-300 overflow-hidden min-h-[260px] self-stretch">
                <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">
                    <span>Payments List</span>
                </div>
                <div class="bg-gray-50 text-gray-900 p-4 flex-1 flex flex-col justify-start">
                    @if($voucher->payments->isNotEmpty())
                        <div class="space-y-2.5">
                            @foreach($voucher->payments as $payment)
                                <div class="flex items-center justify-between p-3.5 rounded-xl border border-gray-200 bg-white shadow-2xs">
                                    <span class="font-black text-sm sm:text-base text-gray-900">
                                        {{ $payment->month ? $payment->month->format('M Y') : '—' }} - {{ $payment->type_label }}
                                    </span>
                                    <span class="text-sm sm:text-base font-black text-blue-700 font-mono">
                                        Rs. {{ number_format($payment->pivot->amount_allocated, 2) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-base sm:text-lg font-black text-gray-400 my-auto text-center p-6">
                            No specific payments allocated.
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- BOTTOM GRID SECTION -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">

            <!-- Left Box: Approved by Box -->
            <div class="bg-gray-50 text-gray-900 rounded-2xl p-4 flex flex-col justify-center border border-gray-300 shadow-xs">
                <p class="text-xs sm:text-sm font-bold text-gray-700">
                    Approved by: <span class="text-blue-700 font-extrabold ml-1">{{ $voucher->user->name ?? 'Management' }}</span>
                </p>
            </div>

            <!-- Right Box: Remarks -->
            <div class="md:col-span-2 bg-gray-50 border border-gray-300 rounded-2xl p-4 shadow-xs">
                <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
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