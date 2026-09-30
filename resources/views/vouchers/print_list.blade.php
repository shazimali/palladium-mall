<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vouchers List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page { size: A4 landscape; margin: 0.5cm; }
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 p-6">
    <div class="no-print mb-4 flex justify-end">
        <button onclick="window.print()" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-md cursor-pointer">🖨️ Print</button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-300 p-6">
        <div class="text-center mb-4">
            <h1 class="text-2xl font-black uppercase">PALLADIUM MALL</h1>
            <h2 class="text-lg font-black text-blue-700 uppercase">Vouchers List</h2>
            <p class="text-xs text-gray-500">
                @if(request('type')) {{ \App\Models\Voucher::TYPES[request('type')] ?? '' }} · @endif
                @if(request('start_date') || request('end_date')) {{ request('start_date') ?: '…' }} to {{ request('end_date') ?: '…' }} · @endif
                Printed {{ now()->format('d M Y H:i') }}
            </p>
        </div>

        <table class="w-full text-sm border border-gray-300">
            <thead class="bg-blue-700 text-white">
                <tr>
                    <th class="px-2 py-2 text-left">Voucher No</th>
                    <th class="px-2 py-2 text-left">Manual No</th>
                    <th class="px-2 py-2 text-left">Date</th>
                    <th class="px-2 py-2 text-left">Type</th>
                    <th class="px-2 py-2 text-left">Account</th>
                    <th class="px-2 py-2 text-left">Narration</th>
                    <th class="px-2 py-2 text-right">Received</th>
                    <th class="px-2 py-2 text-right">Paid</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vouchers as $v)
                    <tr class="border-t border-gray-300">
                        <td class="px-2 py-1.5 font-mono">{{ $v->voucher_no }}</td>
                        <td class="px-2 py-1.5">{{ $v->manual_voucher_no ?? '—' }}</td>
                        <td class="px-2 py-1.5">{{ $v->date->format('d M Y') }}</td>
                        <td class="px-2 py-1.5">{{ $v->type_label }}</td>
                        <td class="px-2 py-1.5">{{ $v->paymentAccount?->name }}</td>
                        <td class="px-2 py-1.5">{{ $v->narration }}</td>
                        <td class="px-2 py-1.5 text-right font-mono">{{ $v->isReceived() ? number_format($v->total_amount) : '' }}</td>
                        <td class="px-2 py-1.5 text-right font-mono">{{ $v->isReceived() ? '' : number_format($v->total_amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-400 bg-gray-50 font-black">
                    <td colspan="6" class="px-2 py-2 text-right uppercase">Total</td>
                    <td class="px-2 py-2 text-right font-mono">{{ number_format($vouchers->filter->isReceived()->sum('total_amount')) }}</td>
                    <td class="px-2 py-2 text-right font-mono">{{ number_format($vouchers->reject->isReceived()->sum('total_amount')) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
