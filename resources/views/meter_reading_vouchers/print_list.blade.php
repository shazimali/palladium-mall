<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Meter Reading Vouchers Report - PALLADIUM MALL</title>
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
                Management Office — Islamabad
            </p>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-blue-700 uppercase">
                Meter Reading Vouchers Report (GEPCO Bills)
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
                        @php
                            $df = $filters['date_from'] ?? ($filters['start_date'] ?? null);
                            $dt = $filters['date_to'] ?? ($filters['end_date'] ?? null);
                        @endphp
                        @if(!empty($df) && !empty($dt))
                            {{ \Carbon\Carbon::parse($df)->format('d M Y') }} to {{ \Carbon\Carbon::parse($dt)->format('d M Y') }}
                        @elseif(!empty($df))
                            From {{ \Carbon\Carbon::parse($df)->format('d M Y') }}
                        @elseif(!empty($dt))
                            Until {{ \Carbon\Carbon::parse($dt)->format('d M Y') }}
                        @else
                            All Recorded Dates
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Flat / Shop Filter:</span>
                    <span class="font-bold text-gray-900">
                        {{ $selectedUnit ? 'Unit ' . $selectedUnit : 'All Flat/Shops' }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Status Filter:</span>
                    <span class="font-bold text-gray-900">
                        {{ !empty($filters['status']) ? strtoupper($filters['status']) : 'All Statuses' }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Search Query:</span>
                    <span class="font-bold text-gray-900">
                        {{ !empty($filters['search']) ? '"' . $filters['search'] . '"' : 'None' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- KPI SUMMARY CARDS -->
        <div class="mb-6 grid grid-cols-3 gap-4 text-center">
            <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-3">
                <span class="text-[10px] font-black uppercase text-blue-700 block">Total Billed Amount</span>
                <span class="text-lg font-black font-mono text-blue-900">Rs. {{ number_format($totalAmount, 2) }}</span>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-3">
                <span class="text-[10px] font-black uppercase text-emerald-700 block">Total Paid Bills</span>
                <span class="text-lg font-black font-mono text-emerald-900">Rs. {{ number_format($totalPaidAmount, 2) }}</span>
            </div>
            <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-3">
                <span class="text-[10px] font-black uppercase text-rose-700 block">Total Unpaid Bills</span>
                <span class="text-lg font-black font-mono text-rose-900">Rs. {{ number_format($totalUnpaidAmount, 2) }}</span>
            </div>
        </div>

        <!-- MAIN REPORT TABLE -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-left text-xs border border-gray-300 print-border">
                <thead class="bg-gray-100 uppercase text-[10px] font-black text-gray-700 border-b border-gray-300">
                    <tr>
                        <th class="px-3 py-2 border-r border-gray-300">#</th>
                        <th class="px-3 py-2 border-r border-gray-300">Voucher #</th>
                        <th class="px-3 py-2 border-r border-gray-300">Date</th>
                        <th class="px-3 py-2 border-r border-gray-300">Flat / Shop</th>
                        <th class="px-3 py-2 border-r border-gray-300">GEPCO Ref #</th>
                        <th class="px-3 py-2 border-r border-gray-300 text-right">Reading (kWh)</th>
                        <th class="px-3 py-2 border-r border-gray-300 text-right">Amount (Rs.)</th>
                        <th class="px-3 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-semibold">
                    @forelse($vouchers as $index => $v)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 border-r border-gray-300 text-center font-mono">{{ $index + 1 }}</td>
                            <td class="px-3 py-2 border-r border-gray-300 font-black font-mono">{{ $v->voucher_no }}</td>
                            <td class="px-3 py-2 border-r border-gray-300 font-mono">{{ $v->date ? $v->date->format('d M Y') : '—' }}</td>
                            <td class="px-3 py-2 border-r border-gray-300 font-black font-mono">
                                Unit {{ $v->unit?->unit_number ?? '—' }}
                            </td>
                            <td class="px-3 py-2 border-r border-gray-300 font-mono font-bold">{{ $v->meter_ref_no ?? '—' }}</td>
                            <td class="px-3 py-2 border-r border-gray-300 text-right font-mono font-bold">
                                {{ $v->current_reading ? number_format($v->current_reading, 2) : '—' }}
                            </td>
                            <td class="px-3 py-2 border-r border-gray-300 text-right font-mono font-black">
                                Rs. {{ number_format($v->amount, 2) }}
                            </td>
                            <td class="px-3 py-2 text-center font-black uppercase text-[10px]">
                                {{ strtoupper($v->status) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-gray-500 font-bold">
                                No Meter Reading Vouchers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100 font-black border-t-2 border-gray-400">
                    <tr>
                        <td colspan="6" class="px-3 py-2.5 text-right uppercase text-xs">Total Bill Amount:</td>
                        <td class="px-3 py-2.5 text-right font-mono text-sm">Rs. {{ number_format($totalAmount, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- REPORT FOOTER & SIGNATURES -->
        <div class="grid grid-cols-2 gap-8 pt-8 border-t border-gray-300 text-center text-xs font-bold text-gray-700">
            <div>
                <div class="border-b border-black w-44 mx-auto mb-2"></div>
                <p>Prepared By (Officer)</p>
            </div>
            <div>
                <div class="border-b border-black w-44 mx-auto mb-2"></div>
                <p>Authorized Signature</p>
            </div>
        </div>

    </div>

</body>
</html>
