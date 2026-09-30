<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $voucher->type_label }} Voucher - {{ $voucher->voucher_no }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { size: A4; margin: 0.5cm; }
            .no-print { display: none !important; }
            body { background-color: white !important; color: black !important; padding: 0 !important; margin: 0 !important; font-weight: bold !important; zoom: 0.8; }
            .max-w-4xl { max-width: 100% !important; padding: 5px !important; margin: 0 !important; border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 antialiased min-h-screen py-8 px-4 sm:px-6">
    @php
        $entryTypes = \App\Models\Voucher::entryTypesFor($voucher->type);
        $isReceived = $voucher->isReceived();
    @endphp

    <div class="max-w-4xl w-full mx-auto mb-4 flex flex-wrap items-center justify-between gap-3 no-print">
        <a href="{{ route('vouchers.index') }}"
            class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50">← Back to List</a>
        <div class="flex items-center gap-3">
            @if(session('success'))
                <span class="text-sm font-bold text-emerald-700">{{ session('success') }}</span>
            @endif
            @if(session('error'))
                <span class="text-sm font-bold text-red-700">{{ session('error') }}</span>
            @endif
            <a href="{{ route('vouchers.create', ['type' => $voucher->type]) }}"
                class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-emerald-700">➕ New Voucher</a>
            <button onclick="window.print()"
                class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-blue-700 cursor-pointer">🖨️ Print Voucher</button>
        </div>
    </div>

    <div class="max-w-4xl w-full mx-auto rounded-3xl bg-white p-6 sm:p-10 border border-gray-300 shadow-xl text-gray-900">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200">
            <div class="hidden sm:block w-36"></div>
            <div class="text-center">
                <h1 class="text-2xl sm:text-3xl font-black tracking-wider uppercase mb-0.5">PALLADIUM MALL</h1>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Management Office</p>
                <h2 class="text-xl sm:text-2xl font-black tracking-tight text-blue-700 uppercase">{{ $voucher->type_label }} Voucher</h2>
            </div>
            <div class="rounded-xl bg-gray-100 border border-gray-300 px-4 py-2">
                <span class="text-xs font-bold text-gray-500 uppercase">Voucher No:</span>
                <span class="text-lg font-black font-mono text-blue-700">{{ $voucher->voucher_no }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-[2px] bg-gray-300 rounded-2xl overflow-hidden mb-5 border border-gray-300">
            @foreach([
                'Voucher Date' => $voucher->date->format('M. d, Y'),
                'Manual Voucher No' => $voucher->manual_voucher_no ?? '—',
                (($isReceived ? 'Debit' : 'Credit') . ' Account') => $voucher->paymentAccount?->name ?? '—',
                'Total Amount' => 'Rs. ' . number_format($voucher->total_amount),
            ] as $label => $value)
                <div class="grid grid-cols-3 min-h-[48px]">
                    <div class="bg-blue-700 text-white px-4 py-3 flex items-center font-bold text-xs sm:text-sm">{{ $label }}</div>
                    <div class="col-span-2 bg-gray-50 px-4 py-3 flex items-center font-black">{{ $value }}</div>
                </div>
            @endforeach
        </div>

        <table class="w-full text-sm border border-gray-300 mb-5">
            <thead class="bg-blue-700 text-white">
                <tr>
                    <th class="px-3 py-2 text-left w-10">#</th>
                    <th class="px-3 py-2 text-left">Entry</th>
                    <th class="px-3 py-2 text-left">{{ $isReceived ? 'Credit' : 'Debit' }} Account</th>
                    <th class="px-3 py-2 text-left">Notes</th>
                    <th class="px-3 py-2 text-right">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lines as $line)
                    <tr class="border-t border-gray-300">
                        <td class="px-3 py-2">{{ $line['line_no'] }}</td>
                        <td class="px-3 py-2">{{ $entryTypes[$line['entry_type']] ?? ucfirst($line['entry_type']) }}</td>
                        <td class="px-3 py-2 font-bold">{{ $line['label'] ?: '—' }}</td>
                        <td class="px-3 py-2">{{ $line['notes'] ?? '' }}</td>
                        <td class="px-3 py-2 text-right font-mono font-bold">{{ number_format($line['amount']) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-400 bg-gray-50">
                    <td colspan="4" class="px-3 py-2 text-right font-black uppercase">Total</td>
                    <td class="px-3 py-2 text-right font-mono font-black text-emerald-700">Rs. {{ number_format($voucher->total_amount) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="grid grid-cols-3 gap-3">
            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-300">
                <p class="text-sm font-bold text-gray-700">Prepared by: <span class="text-blue-700 font-extrabold">{{ $voucher->user->name ?? 'Management' }}</span></p>
            </div>
            <div class="col-span-2 bg-gray-50 border border-gray-300 rounded-2xl p-4">
                <p class="text-xs font-bold text-gray-700 uppercase mb-1">Narration:</p>
                <p class="text-base font-black">{{ $voucher->narration ?? '—' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-6 mt-12 text-center text-xs font-bold uppercase text-gray-600">
            <div class="border-t border-gray-400 pt-2">Prepared By</div>
            <div class="border-t border-gray-400 pt-2">Checked By</div>
            <div class="border-t border-gray-400 pt-2">{{ $isReceived ? 'Received By' : 'Approved By' }}</div>
        </div>
    </div>

    <div class="text-center text-xs text-gray-400 mt-6 no-print">
        Computer-generated voucher. Printed on {{ now()->format('d M Y H:i:s') }}
    </div>
</body>
</html>
