<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flat/Shop Details - {{ $unit->unit_number }}</title>
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

    <div class="max-w-4xl w-full mx-auto bg-white rounded-2xl border border-gray-200 shadow-sm p-8 relative print-border">
        
        <!-- Action Buttons (Hidden during print) -->
        <div class="absolute top-6 right-6 flex items-center gap-3 no-print">
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                </svg>
                Print Details
            </button>
            <button onclick="window.close()" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
                Close
            </button>
        </div>

        <div class="border-b border-gray-100 pb-6 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-6 print-border">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Palladium Mall</h1>
                <p class="text-sm text-gray-500 mt-1">Flat / Shop Specification Detail</p>
            </div>
            <div class="text-left md:text-right">
                <p class="text-xs text-gray-400">Printed On: {{ now()->format('d M Y h:i A') }}</p>
                <p class="text-xs text-gray-400 mt-1">Flat/Shop Number: <span class="font-bold text-gray-900">{{ $unit->unit_number }}</span></p>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Section 0: Landlord Association --}}
            <div class="rounded-xl border border-gray-150 bg-gray-50/50 p-5 print-border">
                <h4 class="mb-4 text-xs font-semibold uppercase tracking-wide text-gray-500 border-b pb-2 border-gray-200">
                    Landlord Association
                </h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Landlord / Owner Name</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $unit->landlord->name ?? 'No Landlord (Unassigned)' }}</span>
                    </div>
                    @if($unit->landlord)
                        <div>
                            <span class="block text-xs font-medium text-gray-400">CNIC</span>
                            <span class="text-sm font-semibold text-gray-800">{{ $unit->landlord->cnic ?? '—' }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Section 1: Unit Identity --}}
            <div class="rounded-xl border border-gray-155 bg-gray-50/50 p-5 print-border">
                <h4 class="mb-4 text-xs font-semibold uppercase tracking-wide text-gray-500 border-b pb-2 border-gray-200">
                    Flat/Shop Specification
                </h4>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Flat/Shop No.</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $unit->unit_number }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Type</span>
                        <span class="text-sm font-semibold text-gray-800 capitalize">{{ $unit->type }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Size (sqft)</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $unit->area_sqft ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Date</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $unit->date ? $unit->date->format('d M Y') : '—' }}</span>
                    </div>
                    <div class="mt-2">
                        <span class="block text-xs font-medium text-gray-400">Floor</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $unit->floor->name ?? '—' }}</span>
                    </div>
                    <div class="mt-2">
                        <span class="block text-xs font-medium text-gray-400">Block</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $unit->block->name ?? '—' }}</span>
                    </div>
                    <div class="mt-2">
                        <span class="block text-xs font-medium text-gray-400">Area / Zone</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $unit->area->name ?? '—' }}</span>
                    </div>
                    <div class="mt-2">
                        <span class="block text-xs font-medium text-gray-400">Status</span>
                        <span class="text-sm font-semibold text-gray-800 capitalize">{{ $unit->status }}</span>
                    </div>
                </div>
            </div>

            {{-- Section 2: Default Pricing & Estimates --}}
            <div class="rounded-xl border border-gray-150 bg-gray-50/50 p-5 print-border">
                <h4 class="mb-4 text-xs font-semibold uppercase tracking-wide text-gray-500 border-b pb-2 border-gray-200">
                    Default Pricing & Estimates (For Projections)
                </h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Default Monthly Rent</span>
                        <span class="text-sm font-semibold text-gray-800">
                            {{ $unit->default_monthly_rent ? 'Rs. ' . number_format($unit->default_monthly_rent, 2) : '—' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Default Maintenance Charge</span>
                        <span class="text-sm font-semibold text-gray-800">
                            {{ $unit->default_maintenance_charge ? 'Rs. ' . number_format($unit->default_maintenance_charge, 2) : '—' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Section 3: Nominee Details --}}
            <div class="rounded-xl border border-gray-150 bg-gray-50/50 p-5 print-border">
                <h4 class="mb-4 text-xs font-semibold uppercase tracking-wide text-gray-500 border-b pb-2 border-gray-200">
                    Nominee Details
                </h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Nominee Name</span>
                        <span class="text-sm font-semibold text-gray-800">
                            {{ $unit->currentOwnership?->nominee_name ?? '—' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Relation</span>
                        <span class="text-sm font-semibold text-gray-800 capitalize">
                            @if(($unit->currentOwnership?->nominee_relation_type ?? '') === 'son_of')
                                Son of
                            @elseif(($unit->currentOwnership?->nominee_relation_type ?? '') === 'daughter_of')
                                Daughter of
                            @elseif(($unit->currentOwnership?->nominee_relation_type ?? '') === 'wife_of')
                                Wife of
                            @else
                                —
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Of (Father/Husband Name)</span>
                        <span class="text-sm font-semibold text-gray-800">
                            {{ $unit->currentOwnership?->nominee_relation_name ?? '—' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Section 4: Financial Summary --}}
            @php
                $total = (float)($unit->currentOwnership?->total_amount ?? 0);
                $received = (float)($unit->currentOwnership?->received_amount ?? 0);
                $balance = $total - $received;
            @endphp
            <div class="rounded-xl border border-gray-150 bg-gray-50/50 p-5 print-border">
                <h4 class="mb-4 text-xs font-semibold uppercase tracking-wide text-gray-500 border-b pb-2 border-gray-200">
                    Financial Summary
                </h4>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Total Amount</span>
                        <span class="text-sm font-semibold text-gray-800">
                            Rs. {{ number_format($total, 2) }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Received Amount</span>
                        <span class="text-sm font-semibold text-gray-800">
                            Rs. {{ number_format($received, 2) }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Credit / Balance</span>
                        <span class="text-sm font-bold text-red-600">
                            Rs. {{ number_format($balance, 2) }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Received From</span>
                        <span class="text-sm font-semibold text-gray-800">
                            {{ $unit->currentOwnership?->received_from ?? '—' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Section 5: Office Record --}}
            <div class="rounded-xl border border-gray-150 bg-gray-50/50 p-5 print-border">
                <h4 class="mb-4 text-xs font-semibold uppercase tracking-wide text-gray-500 border-b pb-2 border-gray-200">
                    Office Record
                </h4>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <span class="block text-xs font-medium text-gray-400">File No.</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $unit->file_no ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Approved By</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $unit->currentOwnership?->approved_by ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Received By</span>
                        <span class="text-sm font-semibold text-gray-800">{{ $unit->currentOwnership?->received_by ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Approved Date</span>
                        <span class="text-sm font-semibold text-gray-800">
                            {{ $unit->currentOwnership?->approved_date ? $unit->currentOwnership->approved_date->format('d M Y') : '—' }}
                        </span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200 print-border">
                    <span class="block text-xs font-medium text-gray-405">Billing Notes / Remarks</span>
                    <span class="text-sm text-gray-700 whitespace-pre-line">{{ $unit->notes ?? 'No remarks or billing notes.' }}</span>
                </div>
            </div>

            {{-- Section 6: Other-Owned Status --}}
            <div class="rounded-xl border border-gray-150 bg-gray-50/50 p-5 print-border">
                <h4 class="mb-4 text-xs font-semibold uppercase tracking-wide text-gray-500 border-b pb-2 border-gray-200">
                    Ownership Classification
                </h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <span class="block text-xs font-medium text-gray-400">Other-Owned Flat/Shop Status</span>
                        <span class="text-sm font-semibold text-gray-800">
                            {{ $unit->is_self ? 'Yes (Marked as Other-Owned)' : 'No (Managed by Palladium Mall)' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 text-center text-xs text-gray-400 no-print">
            <p>This is a computer-generated Flat/Shop specification document.</p>
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
