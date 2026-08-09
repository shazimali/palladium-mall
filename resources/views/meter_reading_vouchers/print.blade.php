<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meter Reading Voucher - {{ $voucher->voucher_no }}</title>
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
                zoom: 0.85;
            }
            .max-w-3xl, .max-w-4xl, .max-w-5xl, .max-w-6xl {
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

    <!-- ACTION BUTTONS (HIDDEN DURING PRINT) -->
    <div class="max-w-4xl w-full mx-auto mb-4 flex flex-wrap items-center justify-between gap-3 no-print">
        <div class="flex items-center gap-2">
            <a href="{{ route('meter-reading-vouchers.index') }}"
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

    <!-- REFINED PRINTABLE VOUCHER CONTAINER (MATCHING /create UI STRATEGY) -->
    <div class="max-w-4xl w-full mx-auto voucher-container rounded-3xl bg-white p-6 sm:p-10 border border-gray-300 shadow-xl text-gray-900 font-sans my-auto">

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
                    Meter Reading Voucher
                </h2>
            </div>
            <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 border border-gray-300 px-4 py-2 shadow-2xs">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Voucher No:</span>
                <span class="text-base sm:text-lg font-black font-mono text-blue-700">{{ $voucher->voucher_no }}</span>
            </div>
        </div>

        <!-- 2-COLUMN SIDE-BY-SIDE FORM GRID LAYOUT (MATCHING /create EXACT FIELD ORDER) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6 items-start">
            
            <!-- LEFT COLUMN: Voucher Date, Flat / Shop, Tenant Name, Due Date -->
            <div class="flex flex-col gap-[2px] bg-gray-300 rounded-2xl overflow-hidden border border-gray-300">
                
                <!-- Field 1: Voucher Date -->
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">Voucher Date</div>
                    <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-base sm:text-lg font-mono">
                        {{ $voucher->date ? $voucher->date->format('M. d, Y') : '—' }}
                    </div>
                </div>

                <!-- Field 2: Flat / Shop -->
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">Flat / Shop</div>
                    <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-base sm:text-lg">
                        {{ $voucher->unit ? 'Flat/Shop ' . $voucher->unit->unit_number : 'N/A' }}
                    </div>
                </div>

                <!-- Field 3: Tenant Name -->
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">Tenant Name</div>
                    <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-extrabold text-sm sm:text-base">
                        {{ $voucher->unit?->tenant?->name ?? ($voucher->unit?->otherTenant?->name ?? 'Vacant / Self') }}
                    </div>
                </div>

                <!-- Field 4: Due Date -->
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">Due Date</div>
                    <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-extrabold text-sm sm:text-base font-mono">
                        {{ $voucher->due_date ? $voucher->due_date->format('M. d, Y') : '—' }}
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: GEPCO Ref #, Reading (kWh), Bill Amount, Bill Status -->
            <div class="flex flex-col gap-[2px] bg-gray-300 rounded-2xl overflow-hidden border border-gray-300">

                <!-- Field 5: GEPCO Ref # -->
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">GEPCO Ref #</div>
                    <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-base sm:text-lg font-mono text-blue-700">
                        {{ $voucher->meter_ref_no ?? '—' }}
                    </div>
                </div>

                <!-- Field 6: Reading (kWh) -->
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">Reading (kWh)</div>
                    <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-base sm:text-lg font-mono">
                        {{ $voucher->current_reading ? number_format($voucher->current_reading, 2) . ' kWh' : '—' }}
                    </div>
                </div>

                <!-- Field 7: Bill Amount -->
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">Bill Amount</div>
                    <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-lg sm:text-xl font-mono text-gray-900">
                        Rs. {{ number_format($voucher->amount, 2) }}
                    </div>
                </div>

                <!-- Field 8: Bill Status -->
                <div class="grid grid-cols-3 min-h-[52px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm tracking-wide">Bill Status</div>
                    <div class="col-span-2 bg-gray-50 text-gray-900 px-4 py-3 flex items-center font-black text-base sm:text-lg">
                        @if($voucher->status === 'paid')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                                PAID
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase bg-rose-100 text-rose-800 border border-rose-300">
                                UNPAID
                            </span>
                        @endif
                    </div>
                </div>

            </div>

        </div>

        <!-- BOTTOM SECTION: Upload Photo & Remarks -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start mb-6">
            <!-- Meter Photo Field -->
            <div class="bg-gray-50 border border-gray-300 rounded-2xl p-4 shadow-2xs">
                <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    GEPCO Meter Photo:
                </p>
                @if($voucher->meter_image_url)
                    <a href="{{ $voucher->meter_image_url }}" target="_blank" class="inline-block">
                        <img src="{{ $voucher->meter_image_url }}" alt="Meter Photo" class="h-32 w-auto rounded-xl border border-gray-300 shadow-sm object-cover" />
                    </a>
                @else
                    <p class="text-xs font-semibold text-gray-400 italic">No meter photo uploaded.</p>
                @endif
            </div>

            <!-- Remarks / Notes Field -->
            <div class="bg-gray-50 border border-gray-300 rounded-2xl p-4 shadow-2xs min-h-[100px]">
                <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Remarks / Notes:
                </p>
                <p class="text-sm font-semibold text-gray-800 whitespace-pre-line">
                    {{ $voucher->notes ?? '—' }}
                </p>
            </div>
        </div>

        <!-- FOOTER: APPROVED BY & SIGNATURES -->
        <div class="pt-4 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8">
                <div class="text-xs sm:text-sm font-bold text-gray-700">
                    Approved by: <span class="text-blue-700 font-extrabold ml-1">{{ $voucher->user?->name ?? 'System Admin' }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-8 text-center text-xs font-bold text-gray-700">
                <div>
                    <div class="border-b border-black w-44 mx-auto mb-2"></div>
                    <p>Prepared By (Office)</p>
                </div>
                <div>
                    <div class="border-b border-black w-44 mx-auto mb-2"></div>
                    <p>Authorized Signature</p>
                </div>
            </div>
        </div>

    </div>

</body>

</html>
