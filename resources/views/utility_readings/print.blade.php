<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Utility Meter Readings Report ({{ $selectedMonthName }}) - PALLADIUM MALL</title>
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
                zoom: 0.82;
            }

            .printable-container {
                max-width: 100% !important;
                padding: 5px !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }

            table {
                font-size: 11px !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800 antialiased min-h-screen p-4 sm:p-8">

    <!-- ACTION BUTTONS (HIDDEN DURING PRINT) -->
    <div class="max-w-7xl w-full mx-auto mb-6 flex justify-end gap-3 no-print">
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-blue-700 transition-all cursor-pointer">
            🖨️ Print Utility Report
        </button>
        <button onclick="window.close()"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all shadow-2xs cursor-pointer">
            Close Window
        </button>
    </div>

    <!-- PRINTABLE CONTAINER -->
    <div
        class="max-w-7xl w-full mx-auto printable-container rounded-3xl bg-white p-6 sm:p-10 border border-gray-300 shadow-xl text-gray-900 font-sans">

        <!-- COMPANY BRANDING HEADER -->
        <div class="text-center mb-6 pb-4 border-b border-gray-300">
            <h1 class="text-2xl sm:text-3xl font-black tracking-wider text-gray-900 uppercase mb-0.5">
                PALLADIUM MALL
            </h1>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">
                Management & Utility Operations
            </p>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight text-blue-700 uppercase">
                Utility Meter Readings Statement — {{ $selectedMonthName }}
            </h2>
        </div>

        <!-- SUMMARY BAR -->
        <div class="mb-4 flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-[10px] font-black uppercase tracking-wide">
            <span class="text-gray-400 font-extrabold mr-1">Meter Status:</span>
            <span class="inline-flex items-center gap-1 rounded bg-emerald-100 px-2 py-0.5 text-emerald-800 border border-emerald-300">● Active: {{ $activeMeters }}</span>
            <span class="inline-flex items-center gap-1 rounded bg-gray-200 px-2 py-0.5 text-gray-600 border border-gray-300">● Inactive: {{ $inactiveMeters }}</span>
            <span class="mx-2 text-gray-300">|</span>
            <span class="text-gray-400 font-extrabold mr-1">Breaker:</span>
            <span class="inline-flex items-center gap-1 rounded bg-emerald-100 px-2 py-0.5 text-emerald-800 border border-emerald-300">⚡ ON: {{ $breakerOn }}</span>
            <span class="inline-flex items-center gap-1 rounded bg-rose-100 px-2 py-0.5 text-rose-800 border border-rose-300">🔌 OFF: {{ $breakerOff }}</span>
            <span class="mx-2 text-gray-300">|</span>
            <span class="text-gray-400 font-extrabold mr-1">Payment:</span>
            <span class="inline-flex items-center gap-1 rounded bg-emerald-100 px-2 py-0.5 text-emerald-800 border border-emerald-300">✅ Paid: {{ $paidCount }}</span>
            <span class="inline-flex items-center gap-1 rounded bg-rose-100 px-2 py-0.5 text-rose-800 border border-rose-300">❌ Unpaid: {{ $unpaidCount }}</span>
            <span class="inline-flex items-center gap-1 rounded bg-amber-100 px-2 py-0.5 text-amber-800 border border-amber-300">⏳ Pending: {{ $pendingCount }}</span>
        </div>

        <!-- READINGS DATA TABLE -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr
                        class="bg-gray-100 text-gray-900 border-b-2 border-gray-400 font-extrabold uppercase text-[10px]">
                        <th class="py-2.5 px-3 border border-gray-300">#</th>
                        <th class="py-2.5 px-3 border border-gray-300">Flat / Shop</th>
                        <th class="py-2.5 px-3 border border-gray-300">Floor & Block</th>
                        <th class="py-2.5 px-3 border border-gray-300">Meter Type</th>
                        <th class="py-2.5 px-3 border border-gray-300">Ref Number</th>
                        <th class="py-2.5 px-3 border border-gray-300">Consumer ID</th>
                        <th class="py-2.5 px-3 border border-gray-300 text-center">Breaker</th>
                        <th class="py-2.5 px-3 border border-gray-300 text-center">Meter Status</th>
                        <th class="py-2.5 px-3 border border-gray-300 text-right">Prev Reading</th>
                        <th class="py-2.5 px-3 border border-gray-300 text-right">Meter Reading</th>
                        <th class="py-2.5 px-3 border border-gray-300 text-right">Units Consumed</th>
                        <th class="py-2.5 px-3 border border-gray-300 text-center">Available</th>
                        <th class="py-2.5 px-3 border border-gray-300 text-right">Amount (Rs.)</th>
                        <th class="py-2.5 px-3 border border-gray-300 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300 font-semibold text-gray-900">
                    @forelse($readings as $index => $row)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-3 border border-gray-300 text-center font-mono">{{ $index + 1 }}</td>
                            <td class="py-2 px-3 border border-gray-300 font-black text-sm text-blue-900">{{ $row['unit_number'] }}
                            </td>
                            <td class="py-2 px-3 border border-gray-300">{{ $row['floor'] }}
                                {{ $row['block'] ? '• ' . $row['block'] : '' }}
                            </td>
                            <td class="py-2 px-3 border border-gray-300 font-bold uppercase">{{ $row['meter_type_label'] }}
                            </td>
                            <td class="py-2 px-3 border border-gray-300 font-mono">{{ $row['meter_ref_no'] }}</td>
                            <td class="py-2 px-3 border border-gray-300 font-mono">{{ $row['meter_consumer_id'] }}</td>
                            <td class="py-2 px-3 border border-gray-300 text-center font-bold">
                                @if(strtoupper($row['breaker_status'] ?? 'OFF') === 'ON')
                                    <span class="inline-flex items-center gap-1 rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800 border border-emerald-300">
                                        ⚡ ON
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded bg-rose-100 px-2 py-0.5 text-xs text-rose-800 border border-rose-300">
                                        🔌 OFF
                                    </span>
                                @endif
                            </td>
                            {{-- Meter Status Column --}}
                            <td class="py-2 px-3 border border-gray-300 text-center font-bold">
                                @if($row['is_active'])
                                    <span class="inline-flex items-center gap-1 rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800 border border-emerald-300 font-extrabold uppercase">
                                        ● Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-600 border border-gray-300 font-extrabold uppercase">
                                        ● Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 px-3 border border-gray-300 text-right font-mono text-gray-600">
                                {{ number_format($row['previous_reading'], 2) }}
                            </td>
                            <td class="py-2 px-3 border border-gray-300 text-right font-mono font-bold text-gray-900">
                                {{ number_format($row['current_reading'], 2) }}
                            </td>
                            <td class="py-2 px-3 border border-gray-300 text-right font-mono font-black text-indigo-900">
                                {{ number_format($row['units_consumed'], 2) }}
                            </td>
                            <td class="py-2 px-3 border border-gray-300 text-center text-xs font-semibold">
                                {{ $row['available'] ?: '—' }}
                            </td>
                            <td class="py-2 px-3 border border-gray-300 text-right font-mono font-bold">Rs.
                                {{ number_format($row['amount'], 2) }}
                            </td>
                            <td class="py-2 px-3 border border-gray-300 text-center font-bold">
                                @if(strtolower($row['status']) === 'paid')
                                    <span class="text-emerald-700 uppercase">PAID</span>
                                @elseif(strtolower($row['status']) === 'unpaid')
                                    <span class="text-rose-700 uppercase">UNPAID</span>
                                @else
                                    <span class="text-amber-700 uppercase">PENDING</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="py-8 text-center text-gray-500 font-bold">
                                No utility readings found for {{ $selectedMonthName }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    {{-- Totals Row --}}
                    <tr class="bg-gray-100 font-black border-t-2 border-gray-400">
                        <td colspan="10" class="py-3 px-3 text-right uppercase border border-gray-300">Total Units Consumed:</td>
                        <td class="py-3 px-3 text-right font-mono border border-gray-300 text-blue-900">
                            {{ number_format($totalUnitsConsumed, 2) }}
                        </td>
                        <td class="py-3 px-3 border border-gray-300"></td>
                        <td class="py-3 px-3 text-right font-mono border border-gray-300 text-gray-900">Rs.
                            {{ number_format($totalBilled, 2) }}
                        </td>
                        <td class="py-3 px-3 border border-gray-300"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- FOOTER SIGNATURES & TIME STAMP -->
        <div class="mt-8 pt-6 border-t border-gray-300 flex justify-between items-end text-xs text-gray-600">
            <div>
                <p>Printed On: <span class="font-bold text-gray-900">{{ now()->format('d M Y, h:i A') }}</span></p>
                <p>Printed By: <span class="font-bold text-gray-900">{{ auth()->user()->name ?? 'System' }}</span></p>
            </div>
            <div class="text-center">
                <div class="w-48 border-b border-gray-400 mb-1"></div>
                <p class="font-bold text-gray-800">Authorized Officer Signature</p>
            </div>
        </div>

    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => { window.print(); }, 400);
        });
    </script>
</body>

</html>