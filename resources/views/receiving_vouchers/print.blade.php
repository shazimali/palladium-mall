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
    <div class="max-w-4xl w-full mx-auto mb-4 flex justify-end gap-3 no-print">
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-blue-700 transition-all cursor-pointer">
            🖨️ Print Voucher
        </button>
        <button onclick="window.close()"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all shadow-2xs cursor-pointer">
            Close Window
        </button>
    </div>

    <!-- REFINED PRINTABLE VOUCHER CONTAINER -->
    <div
        class="max-w-4xl w-full mx-auto voucher-container rounded-3xl bg-white p-6 sm:p-10 border border-gray-300 shadow-xl text-gray-900 font-sans my-auto">

        <!-- CENTERED TITLE -->
        <div class="text-center mb-6 pb-4 border-b border-gray-200">
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-blue-700 uppercase">
                Tenant Receiving Voucher
            </h2>
        </div>

        <!-- TOP 2x2 METADATA GRID -->
        <div class="grid grid-cols-2 gap-[2px] bg-gray-300 rounded-2xl overflow-hidden mb-5 border border-gray-300">

            <!-- Row 1, Col 1: Date -->
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">
                    Voucher Date</div>
                <div
                    class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-extrabold text-xs sm:text-sm">
                    {{ $voucher->date->format('M. d, Y') }}
                </div>
            </div>

            <!-- Row 1, Col 2: Tenant Name -->
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">
                    Tenant Name</div>
                <div
                    class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-extrabold text-xs sm:text-sm">
                    {{ $recipientName }}
                </div>
            </div>

            <!-- Row 2, Col 1: Voucher No -->
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">
                    Voucher No.</div>
                <div
                    class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-extrabold text-xs sm:text-sm font-mono">
                    {{ $voucher->voucher_no }}
                </div>
            </div>

            <!-- Row 2, Col 2: Flat / Shop -->
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">
                    Flat / Shop</div>
                <div
                    class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-extrabold text-xs sm:text-sm">
                    {{ $voucher->payments->first()?->unit?->unit_number ? $voucher->payments->first()->unit->unit_number : 'N/A' }}
                </div>
            </div>

        </div>

        <!-- MIDDLE STACKED GRID -->
        <div class="flex flex-col gap-[2px] bg-gray-300 rounded-2xl overflow-hidden mb-5 border border-gray-300">

            <!-- Row 1: Payments List -->
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">
                    Payments List</div>
                <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3">
                    @if($voucher->payments->isNotEmpty())
                        <div class="space-y-1">
                            @foreach($voucher->payments as $payment)
                                <div class="text-xs font-bold text-gray-800">
                                    {{ $payment->month ? $payment->month->format('M Y') : '—' }} ({{ $payment->type_label }}) -
                                    Rs. {{ number_format($payment->pivot->amount_allocated, 2) }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-xs italic text-gray-500">No specific payments allocated.</span>
                    @endif
                </div>
            </div>

            <!-- Row 2: Payment Amount -->
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">
                    Payment Amount</div>
                <div
                    class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-base sm:text-lg font-mono text-emerald-700">
                    Rs. {{ number_format($voucher->amount, 2) }}
                </div>
            </div>

            <!-- Row 3: Payment Method -->
            <div class="grid grid-cols-3 min-h-[48px]">
                <div
                    class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">
                    Payment Method</div>
                <div
                    class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-extrabold text-xs sm:text-sm">
                    {{ $voucher->paymentAccount ? $voucher->paymentAccount->name : '—' }}
                    @if($voucher->payment_method)
                        <span
                            class="ml-2 text-xs text-gray-500">({{ ucfirst(str_replace('_', ' ', $voucher->payment_method)) }})</span>
                    @endif
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

            <!-- Right Box: Description of Goods/Services / Remarks -->
            <div class="col-span-2 bg-gray-50 border border-gray-300 rounded-2xl p-4">
                <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                    Description of Goods/Services / Remarks:
                </p>
                <p class="text-xs sm:text-sm font-semibold text-gray-800 leading-relaxed">
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