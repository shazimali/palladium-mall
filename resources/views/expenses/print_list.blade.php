<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Expense Vouchers Report - PALLADIUM MALL</title>
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
            class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-all cursor-pointer">
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
            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-red-600 uppercase">
                Expense Vouchers Report
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
                        @if(!empty($filters['start_date']) && !empty($filters['end_date']))
                            {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} to {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}
                        @elseif(!empty($filters['start_date']))
                            From {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }}
                        @elseif(!empty($filters['end_date']))
                            Until {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}
                        @else
                            All Dates
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Expense Category:</span>
                    <span class="font-bold text-gray-900">{{ $selectedHead ?? 'All Categories' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Payment Method:</span>
                    <span class="font-bold text-gray-900">{{ !empty($filters['payment_method']) ? $filters['payment_method'] : 'All Methods' }}</span>
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
                <span class="text-gray-900 font-black ml-1">{{ number_format(count($expenses)) }}</span>
            </div>
            <div>
                <span class="text-gray-500">Total Expenses:</span>
                <span class="text-red-600 font-black ml-1 text-base">Rs. {{ number_format($totalExpenses, 2) }}</span>
            </div>
            <div>
                <span class="text-gray-500">Generated On:</span>
                <span class="text-gray-900 font-bold ml-1">{{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>

        <!-- VOUCHERS TABLE -->
        <div class="overflow-x-auto rounded-2xl border border-gray-300">
            <table class="w-full text-left text-xs font-sans">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-300 text-gray-700 uppercase tracking-wider font-extrabold">
                        <th class="py-3 px-3">Date</th>
                        <th class="py-3 px-3">Voucher #</th>
                        <th class="py-3 px-3">Expense Category</th>
                        <th class="py-3 px-3">Paid From</th>
                        <th class="py-3 px-3">Method</th>
                        <th class="py-3 px-3">Remarks / Description</th>
                        <th class="py-3 px-3 text-right">Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white font-medium text-gray-900">
                    @forelse($expenses as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2.5 px-3 whitespace-nowrap font-bold">
                                {{ $item->date->format('d M Y') }}
                            </td>
                            <td class="py-2.5 px-3 font-mono font-black text-gray-900">
                                {{ $item->voucher_no }}
                            </td>
                            <td class="py-2.5 px-3 font-bold text-gray-900">
                                {{ $item->expenseHead->name ?? '—' }}
                            </td>
                            <td class="py-2.5 px-3">
                                {{ $item->paymentAccount->name ?? '—' }}
                            </td>
                            <td class="py-2.5 px-3 font-semibold">
                                {{ $item->payment_method }}
                            </td>
                            <td class="py-2.5 px-3 max-w-xs truncate text-gray-600">
                                {{ $item->notes ?? '—' }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-black text-red-600 font-mono text-sm whitespace-nowrap">
                                {{ number_format($item->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400 font-bold text-sm">
                                No expense vouchers found matching the criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($expenses) > 0)
                    <tfoot>
                        <tr class="bg-gray-100 border-t-2 border-gray-300 font-black text-xs text-gray-900 uppercase">
                            <td colspan="6" class="py-3 px-3 text-right">Total Amount:</td>
                            <td class="py-3 px-3 text-right text-red-600 font-mono text-base whitespace-nowrap">
                                Rs. {{ number_format($totalExpenses, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <!-- FOOTER SIGNATURE & TIMESTAMP -->
        <div class="mt-12 pt-6 border-t border-gray-300 flex items-end justify-between text-xs text-gray-500 font-bold">
            <div>
                <p>Printed By: <span class="text-gray-900 font-black">{{ auth()->user()->name }}</span></p>
                <p class="text-[10px] text-gray-400 mt-0.5">Palladium Mall ERP Software System</p>
            </div>
            <div class="text-center w-48 border-t border-gray-400 pt-1">
                <p class="uppercase font-bold text-gray-700">Authorized Signature</p>
            </div>
        </div>

    </div>

</body>
</html>
