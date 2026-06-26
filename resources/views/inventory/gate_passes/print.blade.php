<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gate Pass - {{ $gatePass->gatepass_no }}</title>
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
                            950: '#03233a',
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
<body class="bg-gray-50 text-gray-800 antialiased min-h-screen flex flex-col justify-between py-10 px-4 sm:px-6 lg:px-8">

    <div class="max-w-3xl w-full mx-auto bg-white rounded-2xl border border-gray-200 shadow-sm p-8 sm:p-12 relative print-border my-auto">
        
        <!-- Action Buttons (Hidden during print) -->
        <div class="absolute top-6 right-6 flex items-center gap-3 no-print">
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print Gate Pass
            </button>
            <button onclick="window.close()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                Close Window
            </button>
        </div>

        <!-- Header -->
        <div class="border-b border-gray-100 pb-6 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-6 print-border">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-brand-950">PALLADIUM MALL</h1>
                <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-semibold">Store Inventory Outflow Permit</p>
                <h2 class="text-sm font-extrabold text-brand-600 uppercase tracking-widest mt-2">GATE PASS</h2>
                <div class="text-xs text-gray-400 mt-3 space-y-0.5">
                    <p>Main G.T. Road, Palladium Mall, Islamabad</p>
                </div>
            </div>
            
            <div class="text-left md:text-right">
                <div class="inline-block rounded-xl px-4 py-2 bg-gray-50 text-gray-800 text-xs font-semibold print-border mb-3">
                    Gate Pass #: {{ $gatePass->gatepass_no }}
                </div>
                <div class="space-y-1 text-xs">
                    <p><span class="text-gray-400">Date:</span> {{ $gatePass->date->format('d M Y') }}</p>
                    <p><span class="text-gray-400">Printed On:</span> {{ now()->format('d M Y h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Receipt Particulars -->
        <div class="space-y-4 mb-6 text-sm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-3 print-border">
                <div class="text-gray-450 font-medium">Issued To (Recipient):</div>
                <div class="md:col-span-2 font-bold text-gray-900 text-base">{{ $gatePass->issued_to }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-3 print-border">
                <div class="text-gray-450 font-medium">Work Area / Shop:</div>
                <div class="md:col-span-2 font-semibold text-gray-800">
                    {{ $gatePass->unit ? 'Flat/Shop: ' . $gatePass->unit->unit_number : 'Common Area / Mall General' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-3 print-border">
                <div class="text-gray-450 font-medium">Purpose of Issue:</div>
                <div class="md:col-span-2 text-gray-700 font-medium">{{ $gatePass->purpose }}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-gray-100 pb-3 print-border">
                <div class="text-gray-450 font-medium">Permit Status:</div>
                <div class="md:col-span-2 font-bold uppercase tracking-wider text-xs {{ $gatePass->status === 'Issued' ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $gatePass->status }}
                </div>
            </div>
        </div>

        {{-- Dispatched Items List --}}
        <div class="mb-8">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Dispatched Items</h3>
            
            <table class="w-full text-left text-sm print-border">
                <thead>
                    <tr class="border-b border-gray-200 text-gray-450 text-xs uppercase font-semibold">
                        <th class="py-2.5 font-semibold">SKU Code</th>
                        <th class="py-2.5 font-semibold">Item Name</th>
                        <th class="py-2.5 text-right font-semibold">Quantity</th>
                        <th class="py-2.5 font-semibold pl-6">Line Remarks / Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($gatePass->items as $item)
                        <tr>
                            <td class="py-3 text-xs font-mono font-bold text-gray-700">{{ $item->inventoryItem->code ?? '—' }}</td>
                            <td class="py-3 font-semibold text-gray-900">{{ $item->inventoryItem->name ?? 'Deleted Item' }}</td>
                            <td class="py-3 text-right font-bold text-gray-950">
                                {{ number_format($item->quantity, 2) }} {{ $item->inventoryItem->unit_of_measure ?? '' }}
                            </td>
                            <td class="py-3 pl-6 text-xs text-gray-500 italic">{{ $item->notes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Remarks & Signature -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6 border-t border-gray-100 print-border">
            <div>
                @if($gatePass->notes)
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Remarks / Notes:</h4>
                    <p class="text-xs text-gray-500 leading-relaxed italic bg-gray-50 p-3 rounded-lg">{{ $gatePass->notes }}</p>
                @endif
            </div>

            <div class="flex flex-col justify-end">
                <div class="space-y-1 text-xs text-right text-gray-500">
                    <p><span class="text-gray-400">Issued By:</span> {{ $gatePass->user->name ?? '—' }}</p>
                </div>
            </div>
        </div>

        <!-- Footer signatures -->
        <div class="mt-20 pt-8 border-t border-gray-100 flex justify-between text-center text-xs text-gray-400 print-border">
            <div class="w-32">
                <div class="border-b border-gray-200 h-10 mb-2"></div>
                <p>Receiver's Signature</p>
            </div>
            <div class="w-32">
                <div class="border-b border-gray-200 h-10 mb-2"></div>
                <p>Store Keeper</p>
            </div>
            <div class="w-32">
                <div class="border-b border-gray-200 h-10 mb-2"></div>
                <p>Authorized Signature</p>
            </div>
        </div>

    </div>

    <!-- Printed footer -->
    <div class="text-center text-xs text-gray-400 mt-8 no-print">
        <p>This is a computer-generated gate pass voucher. Printed on {{ now()->format('d M Y H:i:s') }}</p>
    </div>

</body>
</html>
