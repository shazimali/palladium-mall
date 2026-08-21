<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Receiving Vouchers Report - PALLADIUM MALL</title>
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
<body class="bg-gray-100 text-gray-800 antialiased min-h-screen p-4 sm:p-8">

    <!-- ACTION BUTTONS (HIDDEN DURING PRINT) -->
    <div class="max-w-6xl w-full mx-auto mb-6 flex justify-end gap-3 no-print">
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-blue-700 transition-all cursor-pointer">
            🖨️ Print List Report
        </button>
        <button onclick="window.close()"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all shadow-2xs cursor-pointer">
            Close Window
        </button>
    </div>

    <!-- PRINTABLE CONTAINER -->
    <div class="max-w-6xl w-full mx-auto printable-container rounded-3xl bg-white p-6 sm:p-10 border border-gray-300 shadow-xl text-gray-900 font-sans">

        <!-- COMPANY BRANDING HEADER -->
        <div class="text-center mb-6 pb-4 border-b border-gray-300">
            <h1 class="text-2xl sm:text-3xl font-black tracking-wider text-gray-900 uppercase mb-0.5">
                PALLADIUM MALL
            </h1>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
                Management Office
            </p>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-blue-700 uppercase">
                Tenant Receiving Vouchers Report
            </h2>
        </div>

        <!-- APPLIED FILTERS SUMMARY HEADER -->
        <div class="mb-6 rounded-2xl bg-gray-50 border border-gray-300 p-4 font-sans text-xs">
            <h3 class="text-xs font-black uppercase tracking-wider text-gray-700 mb-2.5 flex items-center gap-1.5">
                <span>🔍 Filter Criteria Applied</span>
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-gray-800 font-semibold">
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Date Range:</span>
                    <span class="font-bold text-gray-900">
                        @if(!empty($filters['date_from']) && !empty($filters['date_to']))
                            {{ \Carbon\Carbon::parse($filters['date_from'])->format('d M Y') }} to {{ \Carbon\Carbon::parse($filters['date_to'])->format('d M Y') }}
                        @elseif(!empty($filters['date_from']))
                            From {{ \Carbon\Carbon::parse($filters['date_from'])->format('d M Y') }}
                        @elseif(!empty($filters['date_to']))
                            Until {{ \Carbon\Carbon::parse($filters['date_to'])->format('d M Y') }}
                        @else
                            All Dates
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Flat / Shop:</span>
                    <span class="font-bold text-gray-900">{{ $selectedUnit ?? 'All Flats / Shops' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Deposit Account:</span>
                    <span class="font-bold text-gray-900">{{ $selectedAccount ?? 'All Payment Accounts' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Search Keyword:</span>
                    <span class="font-bold text-gray-900">{{ !empty($filters['search']) ? '"' . $filters['search'] . '"' : 'None' }}</span>
                </div>
            </div>
        </div>

        <!-- SUMMARY STATS BAR -->
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-2xl bg-gray-50 border border-gray-200 mb-6 text-sm font-bold">
            <div>
                <span class="text-gray-500">Total Vouchers:</span>
                <span class="text-gray-900 font-black ml-1">{{ number_format($vouchers->count()) }}</span>
            </div>
            <div>
                <span class="text-gray-500">Generated On:</span>
                <span class="text-gray-900 font-bold ml-1">{{ now()->format('d M Y, h:i A') }}</span>
            </div>
            <div>
                <span class="text-gray-500">Total Received:</span>
                <span class="text-emerald-700 font-mono font-black text-base ml-1">Rs. {{ number_format($totalAmount, 2) }}</span>
            </div>
        </div>

        <!-- PRINTABLE DATA TABLE -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-blue-700 text-white uppercase text-[11px] tracking-wider">
                        <th class="border border-blue-800 px-3 py-2.5 text-center">#</th>
                        <th class="border border-blue-800 px-3 py-2.5 font-mono">Voucher #</th>
                        <th class="border border-blue-800 px-3 py-2.5">Date</th>
                        <th class="border border-blue-800 px-3 py-2.5">Flat / Shop</th>
                        <th class="border border-blue-800 px-3 py-2.5">Received From</th>
                        <th class="border border-blue-800 px-3 py-2.5">Deposit Account</th>
                        <th class="border border-blue-800 px-3 py-2.5">Remarks</th>
                        <th class="border border-blue-800 px-3 py-2.5 text-right font-mono">Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($vouchers as $index => $voucher)
                        @php
                            $unitNo = $voucher->display_unit_number;
                            
                            $recipientName = '—';
                            if ($voucher->received_from_type === 'owner') {
                                $recipientName = $voucher->owner->name ?? 'Partner';
                            } elseif ($voucher->received_from_type === 'tenant') {
                                $recipientName = $voucher->tenant->name ?? $voucher->other_name ?? 'Tenant';
                            } else {
                                $recipientName = $voucher->other_name ?? 'N/A';
                            }
                        @endphp
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                            <td class="border border-gray-300 px-3 py-2 text-center font-bold text-gray-500">
                                {{ $loop->iteration }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 font-mono font-bold text-gray-900">
                                {{ $voucher->voucher_no }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 font-semibold">
                                {{ $voucher->date->format('d M Y') }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 font-black text-gray-900">
                                {{ $unitNo }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 font-semibold">
                                {{ $recipientName }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2">
                                {{ $voucher->paymentAccount->name ?? '—' }}
                                <span class="text-[10px] text-gray-500 uppercase">({{ $voucher->payment_method }})</span>
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-gray-600">
                                {{ $voucher->notes ?? '—' }}
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-right font-mono font-black text-gray-900">
                                {{ number_format($voucher->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="border border-gray-300 px-4 py-8 text-center text-gray-400 font-semibold">
                                No receiving vouchers match the selected filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 border-t-2 border-gray-400 font-black text-xs">
                        <td colspan="7" class="border border-gray-300 px-4 py-3 text-right uppercase tracking-wider text-gray-700">
                            Total Received Amount:
                        </td>
                        <td class="border border-gray-300 px-3 py-3 text-right font-mono text-emerald-700 text-sm">
                            Rs. {{ number_format($totalAmount, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- PRINT FOOTER -->
        <div class="mt-8 pt-4 border-t border-gray-300 flex justify-between items-center text-[11px] text-gray-400">
            <p>PALLADIUM MALL Management System</p>
            <p>Computer-generated report. Printed on {{ now()->format('d M Y H:i:s') }}</p>
        </div>

    </div>

</body>
</html>
