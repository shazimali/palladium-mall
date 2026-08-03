<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print JV Vouchers Report - PALLADIUM MALL</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                size: A4 landscape;
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
                Journal Vouchers (JV) Report
            </h2>
        </div>

        <!-- SUMMARY CARDS HEADER -->
        <div class="grid grid-cols-3 gap-4 mb-6 text-center text-xs">
            <div class="p-3 bg-blue-50 rounded-2xl border border-blue-200">
                <span class="text-[10px] text-blue-700 font-bold block uppercase tracking-wider">Total JV Amount</span>
                <span class="text-lg font-black text-blue-900 font-mono">Rs. {{ number_format($totalJvAmount, 2) }}</span>
            </div>
            <div class="p-3 bg-amber-50 rounded-2xl border border-amber-200">
                <span class="text-[10px] text-amber-700 font-bold block uppercase tracking-wider">Total Unpaid (Accrued)</span>
                <span class="text-lg font-black text-amber-900 font-mono">Rs. {{ number_format($totalUnpaidAmount, 2) }}</span>
            </div>
            <div class="p-3 bg-green-50 rounded-2xl border border-green-200">
                <span class="text-[10px] text-green-700 font-bold block uppercase tracking-wider">Total Paid</span>
                <span class="text-lg font-black text-green-900 font-mono">Rs. {{ number_format($totalPaidAmount, 2) }}</span>
            </div>
        </div>

        <!-- DATA TABLE -->
        <div class="overflow-hidden border border-gray-300 rounded-2xl mb-6">
            <table class="w-full text-xs text-left text-gray-800">
                <thead class="bg-gray-100 font-bold uppercase text-gray-700 border-b border-gray-300">
                    <tr>
                        <th class="p-3 border-r border-gray-300">Voucher #</th>
                        <th class="p-3 border-r border-gray-300">Voucher Date</th>
                        <th class="p-3 border-r border-gray-300">Category</th>
                        <th class="p-3 border-r border-gray-300 text-right">Amount</th>
                        <th class="p-3 border-r border-gray-300">Status</th>
                        <th class="p-3 border-r border-gray-300">Settlement Info</th>
                        <th class="p-3">Ref / Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-medium">
                    @forelse($vouchers as $voucher)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 border-r border-gray-200 font-mono font-bold text-blue-700">{{ $voucher->voucher_no }}</td>
                            <td class="p-3 border-r border-gray-200 whitespace-nowrap">{{ $voucher->date ? $voucher->date->format('M. d, Y') : '—' }}</td>
                            <td class="p-3 border-r border-gray-200 font-bold">{{ $voucher->expenseHead->name ?? 'Uncategorized' }}</td>
                            <td class="p-3 border-r border-gray-200 text-right font-black font-mono text-emerald-700">Rs. {{ number_format($voucher->amount, 2) }}</td>
                            <td class="p-3 border-r border-gray-200 font-bold uppercase">
                                @if($voucher->status === 'paid')
                                    <span class="text-green-700">● Paid</span>
                                @else
                                    <span class="text-amber-700">● Unpaid</span>
                                @endif
                            </td>
                            <td class="p-3 border-r border-gray-200 text-[11px]">
                                @if($voucher->status === 'paid')
                                    {{ $voucher->paymentAccount->name ?? '' }} {{ $voucher->paid_date ? '(' . $voucher->paid_date->format('M. d, Y') . ')' : '' }}
                                @else
                                    Not Settled
                                @endif
                            </td>
                            <td class="p-3 text-gray-600 text-[11px]">
                                {{ $voucher->reference ? $voucher->reference . ' — ' : '' }}{{ Str::limit($voucher->notes, 40) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-6 text-center text-gray-500 font-bold">
                                No JV Vouchers found for the selected filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- REPORT FOOTER -->
        <div class="flex items-center justify-between text-xs text-gray-500 font-semibold pt-4 border-t border-gray-300">
            <span>Total Records: {{ count($vouchers) }}</span>
            <span>Computer-generated report. Printed on {{ now()->format('d M Y H:i:s') }}</span>
        </div>
    </div>
</body>
</html>
