<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flat/Shop Master List</title>
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
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-border {
                border-width: 1px !important;
                border-color: #d1d5db !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen py-10 px-4 sm:px-6 lg:px-8">

    <div class="max-w-6xl w-full mx-auto bg-white rounded-2xl border border-gray-200 shadow-sm p-8 relative print-border">
        
        <!-- Action Buttons (Hidden during print) -->
        <div class="absolute top-6 right-6 flex items-center gap-3 no-print">
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print List
            </button>
            <button onclick="window.close()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                Close
            </button>
        </div>

        <div class="border-b border-gray-100 pb-6 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-6 print-border">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Palladium Mall</h1>
                <p class="text-sm text-gray-500 mt-1">Flat / Shop Master List</p>
            </div>
            <div class="text-left md:text-right">
                <p class="text-xs text-gray-400">Printed On: {{ now()->format('d M Y h:i A') }}</p>
                <p class="text-xs text-gray-400 mt-1">Total Records: {{ $units->count() }}</p>
            </div>
        </div>

        <!-- DataTable -->
        <div class="overflow-hidden border border-gray-200 rounded-xl">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Flat No.</th>
                        <th class="px-4 py-3">Floor</th>
                        <th class="px-4 py-3">Block</th>
                        <th class="px-4 py-3">Area / Zone</th>
                        <th class="px-4 py-3">Landlord</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Other-Owned</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($units as $index => $unit)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-800">
                                {{ $unit->unit_number }}
                            </td>
                            <td class="px-4 py-3">{{ $unit->floor->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $unit->block->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $unit->area->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                {{ $unit->landlord->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="capitalize">
                                    {{ $unit->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="capitalize">
                                    {{ $unit->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span>
                                    {{ $unit->is_self ? 'Yes' : 'No' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                                No flats or shops found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8 text-center text-xs text-gray-400 no-print">
            <p>This is a computer-generated Flat/Shop list report.</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
