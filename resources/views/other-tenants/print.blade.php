<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Flat/Shop Tenants List</title>
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
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen py-10 px-4 sm:px-6 lg:px-8">

    <div class="max-w-6xl w-full mx-auto bg-white rounded-2xl border border-gray-300 shadow-sm p-8 relative print-border">
        
        <!-- Action Buttons (Hidden during print) -->
        <div class="absolute top-6 right-6 flex items-center gap-3 no-print">
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 transition-colors shadow-sm cursor-pointer">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print List
            </button>
            <button onclick="window.close()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm cursor-pointer">
                Close
            </button>
        </div>

        <div class="border-b border-gray-200 pb-6 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-6 print-border">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Palladium Mall</h1>
                <p class="text-sm text-gray-500 mt-1">Other Flat / Shop Tenants List</p>
            </div>
            <div class="text-left md:text-right">
                <p class="text-xs text-gray-400">Printed On: {{ now()->format('d M Y h:i A') }}</p>
                <p class="text-xs text-gray-400 mt-1">Total Records: {{ $otherTenants->count() }}</p>
            </div>
        </div>

        <!-- DataTable -->
        <div class="overflow-hidden border border-gray-300 rounded-xl">
            <table class="w-full text-sm text-left text-gray-600 border-collapse border border-gray-300">
                <thead class="text-xs uppercase bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 border border-gray-300">#</th>
                        <th class="px-4 py-3 border border-gray-300">Tenant Name</th>
                        <th class="px-4 py-3 border border-gray-300">Posation Date</th>
                        <th class="px-4 py-3 border border-gray-300">Flat / Shop No.</th>
                        <th class="px-4 py-3 border border-gray-300">Phone</th>
                        <th class="px-4 py-3 border border-gray-300">Occupancy</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($otherTenants as $index => $ot)
                        @php
                            $activeHist = $ot->unit ? $ot->unitHistory->where('unit_id', $ot->unit_id)->whereNull('detached_at')->first() : null;
                            $attachDate = $activeHist && $activeHist->attached_at ? $activeHist->attached_at : null;
                            $lastHistory = $ot->unitHistory->sortByDesc('id')->first();
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 border border-gray-300 text-gray-400">{{ $index + 1 }}</td>
                            
                            {{-- Tenant Name --}}
                            <td class="px-4 py-3 border border-gray-300">
                                <div class="font-bold text-gray-900">{{ $ot->name }}</div>
                                @if($ot->cnic)
                                    <div class="text-xs text-gray-500">CNIC: {{ $ot->cnic }}</div>
                                @endif
                            </td>

                            {{-- Posation Date --}}
                            <td class="px-4 py-3 border border-gray-300 whitespace-nowrap">
                                @if($attachDate)
                                    <span>{{ $attachDate->format('d M Y') }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Flat / Shop No. --}}
                            <td class="px-4 py-3 border border-gray-300">
                                @if($ot->unit)
                                    <span class="font-bold text-gray-900">{{ $ot->unit->unit_number }}</span>
                                @elseif($lastHistory && $lastHistory->unit)
                                    <div class="text-xs">
                                        <span class="text-gray-400 uppercase font-semibold block">Last Unit</span>
                                        <span class="font-bold text-gray-800">{{ $lastHistory->unit->unit_number }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Phone --}}
                            <td class="px-4 py-3 border border-gray-300">
                                {{ $ot->phone ?? ($ot->whatsapp_number ?? '—') }}
                            </td>

                            {{-- Occupancy --}}
                            <td class="px-4 py-3 border border-gray-300">
                                @if($ot->unit_id)
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold bg-green-100 text-green-800">Attached</span>
                                @elseif($lastHistory && $lastHistory->unit)
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold bg-red-100 text-red-800">Detached</span>
                                @else
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold bg-gray-100 text-gray-700">Unassigned</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm border border-gray-300">
                                No other tenants found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
