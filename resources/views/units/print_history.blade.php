<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst($unit->type) }} {{ $unit->unit_number }} History</title>
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
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* @page MUST be top-level — nesting inside @media print breaks @bottom-right in all browsers */
        @page {
            size: A4;
            margin: 1.5cm 0.5cm 1.8cm 0.5cm;

            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 0.75rem;
                font-weight: 800;
                color: #475569;
            }
        }

        @media print {
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

            /* position:fixed repeats on every printed page — cross-browser fallback */
            .print-page-number {
                display: block !important;
                position: fixed;
                bottom: 6px;
                right: 10px;
                font-size: 0.72rem;
                font-weight: 800;
                color: #475569;
            }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen py-10 px-4 sm:px-6 lg:px-8">

    <div class="max-w-6xl w-full mx-auto bg-white rounded-2xl border border-gray-300 shadow-sm p-8 relative print-border">

        <!-- Action Buttons (Hidden during print) -->
        <div class="absolute top-6 right-6 flex items-center gap-3 no-print">
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print
            </button>
            <button onclick="window.close()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                Close
            </button>
        </div>

        <div class="border-b border-gray-200 pb-6 mb-6 text-center print-border">
            <p class="text-sm font-bold text-gray-600">Palladium Mall</p>
            <h1 class="text-3xl font-black uppercase tracking-wide text-gray-900 mt-1">Flat / Shop History</h1>
            <p class="text-xs font-bold text-gray-600 mt-2">
                Printed On: {{ now()->format('d M Y h:i A') }} &nbsp;·&nbsp; Total Records: {{ $history->count() }}
            </p>
        </div>

        <div class="mb-6 text-center">
            <h2 class="inline-block rounded-full border-2 border-gray-800 px-8 py-2 text-xl font-black uppercase tracking-wide text-gray-900">
                {{ ucfirst($unit->type) }} No {{ $unit->unit_number }}
            </h2>
            <p class="text-sm font-bold text-gray-700 mt-2">
                {{ $unit->floor->name ?? '' }}{{ $unit->block ? ' · ' . $unit->block->name : '' }}{{ $unit->landlord ? ' · Owner: ' . $unit->landlord->name : '' }}
            </p>
        </div>

        <div class="overflow-hidden border border-gray-300 rounded-xl">
            <table class="w-full text-sm text-left text-gray-600 border-collapse border border-gray-300">
                <thead class="text-xs uppercase bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-3 py-3 border border-gray-300">#</th>
                        <th class="px-3 py-3 border border-gray-300">Name</th>
                        <th class="px-3 py-3 border border-gray-300">Contact No</th>
                        <th class="px-3 py-3 border border-gray-300 text-right">Rent</th>
                        <th class="px-3 py-3 border border-gray-300 text-right">Advance</th>
                        <th class="px-3 py-3 border border-gray-300">Agreement Start</th>
                        <th class="px-3 py-3 border border-gray-300">Agreement End</th>
                        <th class="px-3 py-3 border border-gray-300">Vacated On</th>
                        <th class="px-3 py-3 border border-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($history as $row)
                        <tr>
                            <td class="px-3 py-3 border border-gray-300 text-gray-400">{{ $loop->iteration }}</td>
                            <td class="px-3 py-3 border border-gray-300 font-semibold text-gray-800">
                                {{ $row['name'] }}
                                @if($row['kind'] !== 'Tenant')
                                    <span class="text-[10px] text-gray-500">({{ $row['kind'] }})</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 border border-gray-300 font-mono">{{ $row['phone'] ?: '—' }}</td>
                            <td class="px-3 py-3 border border-gray-300 text-right font-mono">{{ $row['rent'] ? number_format($row['rent']) : '—' }}</td>
                            <td class="px-3 py-3 border border-gray-300 text-right font-mono">{{ $row['advance'] ? number_format($row['advance']) : '—' }}</td>
                            <td class="px-3 py-3 border border-gray-300 whitespace-nowrap">{{ $row['start_date']?->format('d M Y') ?? '—' }}</td>
                            <td class="px-3 py-3 border border-gray-300 whitespace-nowrap">{{ $row['end_date']?->format('d M Y') ?? '—' }}</td>
                            <td class="px-3 py-3 border border-gray-300 whitespace-nowrap">{{ $row['vacated_at']?->format('d M Y') ?? '—' }}</td>
                            <td class="px-3 py-3 border border-gray-300">{{ $row['status'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 border border-gray-300 text-center text-gray-400">
                                No history found for this flat / shop.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8 text-center text-xs text-gray-400 no-print">
            <p>This is a computer-generated Flat/Shop history report.</p>
        </div>
    </div>

    <!-- Fixed page-number footer: repeats on every printed page (cross-browser fallback) -->
    <div class="print-page-number" style="display:none;"></div>
</body>
</html>
