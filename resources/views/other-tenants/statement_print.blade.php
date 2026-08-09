<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — Palladium Mall</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                font-size: 13px !important;
            }
            .print-container {
                padding: 15px !important;
                width: 100% !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 12px !important;
            }
            th, td {
                border: 1px solid #9ca3af !important;
                padding: 6px 8px !important;
                color: black !important;
            }
            tfoot tr td {
                font-weight: 900 !important;
                font-size: 13px !important;
                background-color: #f3f4f6 !important;
            }
            .unit-badge-lg {
                border: 1px solid #4b5563 !important;
                background-color: #f3f4f6 !important;
                color: #111827 !important;
                font-weight: 900 !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased p-4 sm:p-8">

    {{-- Top Action Bar --}}
    <div class="no-print max-w-5xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-md border border-gray-200">
        <div>
            <h2 class="text-lg font-black text-gray-900">Tenant Account Statement</h2>
            <p class="text-xs text-gray-500">Printable statement for {{ $otherTenant->name }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer">
                🖨️ Print Statement
            </button>
            <a href="{{ route('other-tenants.show', $otherTenant) }}"
                class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                Back to Profile
            </a>
        </div>
    </div>

    {{-- Printable Statement Document Container --}}
    <div class="print-container max-w-5xl mx-auto bg-white p-8 rounded-2xl shadow-xl border border-gray-200">

        {{-- Company Header --}}
        <div class="text-center border-b-2 border-black pb-4 mb-6">
            <h1 class="text-3xl font-black uppercase tracking-wider text-black">PALLADIUM MALL</h1>
            <p class="text-xs font-bold text-gray-700 uppercase">Management Office — Islamabad</p>
            <h2 class="text-xl font-black uppercase text-black mt-2">TENANT STATEMENT OF ACCOUNT</h2>
            <p class="text-xs text-gray-600 mt-1">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
        </div>

        {{-- Tenant & Current Unit Profile Box --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-2 border-gray-300 rounded-xl p-4 mb-6 bg-gray-50/50">
            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Tenant Information</h3>
                <p class="text-lg font-black text-gray-900 mt-1">{{ $otherTenant->name }}</p>
                <div class="text-xs font-semibold text-gray-700 space-y-0.5 mt-1">
                    <p><span class="text-gray-500 font-normal">CNIC / ID:</span> {{ $otherTenant->cnic ?? '—' }}</p>
                    <p><span class="text-gray-500 font-normal">Phone:</span> {{ $otherTenant->phone ?? '—' }}</p>
                    <p><span class="text-gray-500 font-normal">Address:</span> {{ $otherTenant->address ?? '—' }}</p>
                </div>
            </div>
            <div>
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">Current Occupancy & Electricity Specifications</h3>
                <div class="mt-1 flex items-center gap-2">
                    @if($unit)
                        <span class="unit-badge-lg text-sm font-black">{{ $unit->unit_number }}</span>
                        <span class="text-xs font-bold text-gray-500">({{ $unit->floor?->name ?? '—' }} / {{ $unit->block?->name ?? '—' }})</span>
                    @else
                        <span class="text-gray-400 font-normal">No Active Flat/Shop Attached</span>
                    @endif
                </div>
                @if($unit)
                    <div class="flex flex-wrap items-center gap-3 mt-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-black uppercase border {{ $unit->isBreakerOn() ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : 'bg-rose-100 text-rose-800 border-rose-300' }}">
                            ⚡ BREAKER {{ strtoupper($unit->breaker_status ?? 'OFF') }}
                        </span>
                        @if($latestBreakerInsp)
                            <span class="text-xs font-bold text-gray-800 bg-gray-200/80 px-2.5 py-1 rounded-lg">
                                Meter Reading: <strong class="font-mono">{{ number_format($latestBreakerInsp->meter_reading, 2) }} kWh</strong>
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Attached Units History Box (Past & Present) --}}
        @if($unitHistory->isNotEmpty())
            <div class="border-2 border-gray-300 rounded-xl p-4 mb-6 bg-gray-50/30">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-700 mb-2">Attached Flat/Shop History (Past & Present)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border border-gray-300">
                        <thead class="bg-gray-100 uppercase text-[10px] font-extrabold text-gray-700">
                            <tr>
                                <th class="px-3 py-1.5">Flat/Shop</th>
                                <th class="px-3 py-1.5">Floor / Block</th>
                                <th class="px-3 py-1.5">Attached On</th>
                                <th class="px-3 py-1.5">Detached On</th>
                                <th class="px-3 py-1.5 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 font-semibold">
                            @foreach($unitHistory as $h)
                                <tr>
                                    <td class="px-3 py-1.5 font-bold">
                                        <span class="unit-badge-lg text-xs px-2 py-0.5 font-black">{{ $h->unit->unit_number ?? '—' }}</span>
                                    </td>
                                    <td class="px-3 py-1.5 font-bold text-gray-700">
                                        {{ $h->unit->floor?->name ?? '—' }} / {{ $h->unit->block?->name ?? '—' }}
                                    </td>
                                    <td class="px-3 py-1.5 font-bold text-emerald-700">
                                        {{ $h->attached_at ? $h->attached_at->format('d M Y') : '—' }}
                                    </td>
                                    <td class="px-3 py-1.5 font-bold text-rose-700">
                                        {{ $h->detached_at ? $h->detached_at->format('d M Y') : '—' }}
                                    </td>
                                    <td class="px-3 py-1.5 text-center font-bold">
                                        @if(!$h->detached_at)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800">CURRENTLY ATTACHED</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-gray-200 text-gray-700">DETACHED</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Financial Summary (What He Paid Us & What He Pending) --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            {{-- Total Billed --}}
            <div class="border-2 border-blue-200 bg-blue-50/60 rounded-xl p-4 text-center">
                <p class="text-xs font-black uppercase tracking-wider text-blue-700">Total Charges / Billed</p>
                <p class="text-2xl font-black font-mono text-blue-900 mt-1">Rs. {{ number_format($totalBilled, 2) }}</p>
            </div>

            {{-- Total Paid (What He Paid Us) --}}
            <div class="border-2 border-emerald-200 bg-emerald-50/60 rounded-xl p-4 text-center">
                <p class="text-xs font-black uppercase tracking-wider text-emerald-700">What Paid To Us (Paid)</p>
                <p class="text-2xl font-black font-mono text-emerald-900 mt-1">Rs. {{ number_format($totalPaid, 2) }}</p>
            </div>

            {{-- Total Pending (What He Pending) --}}
            <div class="border-2 border-rose-200 bg-rose-50/60 rounded-xl p-4 text-center">
                <p class="text-xs font-black uppercase tracking-wider text-rose-700">What Is Pending (Dues)</p>
                <p class="text-2xl font-black font-mono text-rose-900 mt-1">Rs. {{ number_format($totalPending, 2) }}</p>
            </div>
        </div>

        {{-- Detailed Billing & Receipt Statement Table --}}
        <div class="mb-8 overflow-x-auto">
            <h3 class="text-sm font-black uppercase tracking-wider text-gray-800 mb-2">Itemized Dues & Payments Ledger</h3>
            <table class="w-full text-left text-xs text-gray-800 border-2 border-gray-300">
                <thead class="bg-gray-100 uppercase text-[11px] font-extrabold text-gray-700 border-b-2 border-gray-300">
                    <tr>
                        <th class="px-3 py-2.5">Month</th>
                        <th class="px-3 py-2.5">Flat/Shop</th>
                        <th class="px-3 py-2.5">Billing Type / Description</th>
                        <th class="px-3 py-2.5 text-right">Billed Amount</th>
                        <th class="px-3 py-2.5 text-right text-emerald-700">Paid (Paid Us)</th>
                        <th class="px-3 py-2.5 text-right text-rose-700">Pending (Dues)</th>
                        <th class="px-3 py-2.5 text-center">Status</th>
                        <th class="px-3 py-2.5 text-center">Paid Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-semibold">
                    @forelse($payments as $pay)
                        @php
                            $pending = max(0.00, (float)$pay->amount - (float)$pay->amount_paid);
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2.5 font-bold font-mono">{{ $pay->month->format('M Y') }}</td>
                            <td class="px-3 py-2.5 font-bold">
                                @if($pay->unit)
                                    <span class="unit-badge-lg text-xs px-2 py-0.5 font-black">{{ $pay->unit->unit_number }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 font-bold">
                                {{ ucfirst(str_replace('_', ' ', $pay->type)) }} Billing
                            </td>
                            <td class="px-3 py-2.5 text-right font-mono font-bold">
                                Rs. {{ number_format($pay->amount, 2) }}
                            </td>
                            <td class="px-3 py-2.5 text-right font-mono font-bold text-emerald-700">
                                Rs. {{ number_format($pay->amount_paid, 2) }}
                            </td>
                            <td class="px-3 py-2.5 text-right font-mono font-bold text-rose-700">
                                {{ $pending > 0 ? 'Rs. ' . number_format($pending, 2) : '—' }}
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                @if($pay->status === 'paid')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800">PAID</span>
                                @elseif($pay->status === 'partial')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800">PARTIAL</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-rose-100 text-rose-800">UNPAID</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-center font-mono text-[11px]">
                                {{ $pay->paid_at ? $pay->paid_at->format('d M Y') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-400 font-bold">
                                No billing or payment records found for this tenant.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100 font-black border-t-2 border-gray-300">
                    <tr>
                        <td colspan="3" class="px-3 py-3 text-sm">TOTAL ACCUMULATED DUES & RECEIPTS</td>
                        <td class="px-3 py-3 text-right font-mono text-sm">Rs. {{ number_format($totalBilled, 2) }}</td>
                        <td class="px-3 py-3 text-right font-mono text-sm text-emerald-700">Rs. {{ number_format($totalPaid, 2) }}</td>
                        <td class="px-3 py-3 text-right font-mono text-sm text-rose-700">Rs. {{ number_format($totalPending, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Verification & Signatures --}}
        <div class="grid grid-cols-2 gap-8 pt-12 border-t border-gray-300 mt-12 text-center text-xs font-bold text-gray-700">
            <div>
                <div class="border-b border-black w-48 mx-auto mb-2"></div>
                <p>Tenant Signature</p>
            </div>
            <div>
                <div class="border-b border-black w-48 mx-auto mb-2"></div>
                <p>Authorized Officer Signature</p>
                <p class="text-[10px] font-normal text-gray-500">Palladium Mall Management Office</p>
            </div>
        </div>

    </div>

</body>
</html>
