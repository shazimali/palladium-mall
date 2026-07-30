<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Party Ledger Statement — Palladium Mall</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 15px; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #000; background: #fff; padding: 24px 32px; line-height: 1.5; font-weight: 700; }
        
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
        .logo-section { display: flex; align-items: center; gap: 10px; }
        .logo-text { font-size: 1.4rem; font-weight: 900; color: #0f172a; }
        .doc-title { text-align: right; }
        .doc-title h2 { font-size: 1.15rem; font-weight: 900; color: #0f172a; }
        .doc-title p { font-size: 0.85rem; font-weight: 700; color: #475569; margin-top: 2px; }

        .party-info { margin-bottom: 16px; font-size: 0.95rem; font-weight: 800; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; background: #f8fafc; }
        .party-info p { margin-bottom: 4px; }
        .party-info strong { color: #0f172a; font-weight: 900; }

        table { width: 100%; border-collapse: collapse; font-size: 0.92rem; font-weight: 700; margin-bottom: 24px; }
        thead tr { background: #e2e8f0; }
        thead th { padding: 10px; text-align: left; font-weight: 900; font-size: 0.82rem; text-transform: uppercase; color: #0f172a; border-bottom: 2px solid #0f172a; }
        thead th.text-right, tbody td.text-right { text-align: right; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody td { padding: 9px 10px; color: #000; font-weight: 700; }
        
        .mono { font-family: monospace; font-size: 0.9rem; font-weight: 800; }
        .debit { color: #dc2626; font-weight: 900; }
        .credit { color: #16a34a; font-weight: 900; }
        
        .footer { margin-top: 30px; border-top: 2px solid #0f172a; padding-top: 10px; display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 800; color: #475569; }
        .no-print { text-align: center; margin-bottom: 24px; }
        .print-btn { display: inline-flex; align-items: center; gap: 8px; background: #0f172a; color: #fff; border: none; border-radius: 6px; padding: 10px 24px; font-size: 0.95rem; font-weight: 800; cursor: pointer; }
        .print-btn:hover { background: #000; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
        @page { size: A4; margin: 15mm; }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print / Save Statement</button>
    </div>

    <!-- Statement Header -->
    <div class="header">
        <div class="logo-section">
            <span class="logo-text">PALLADIUM MALL</span>
        </div>
        <div class="doc-title">
            <h2>Party Statement of Account</h2>
            <p>Printed: {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>

    <!-- Party Details -->
    <div class="party-info">
        <p><strong>Party Head Name:</strong> {{ $selectedParty->name }}</p>
        <p><strong>Contact phone:</strong> {{ $selectedParty->phone ?? '—' }}</p>
        <p><strong>WhatsApp Number:</strong> {{ $selectedParty->whatsapp_number ?? '—' }}</p>
    </div>

    <!-- Statement transactions list -->
    <table>
        <thead>
            <tr>
                <th style="width: 15%">Date</th>
                <th style="width: 20%">Ref/Voucher #</th>
                <th style="width: 20%">Transaction Type</th>
                <th style="width: 25%">Details</th>
                <th style="width: 10%" class="text-right">Debit (Dr)</th>
                <th style="width: 10%" class="text-right">Credit (Cr)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ledgerEntries as $entry)
                <tr>
                    <td class="mono">{{ ($entry['date'] instanceof \Carbon\Carbon ? $entry['date'] : \Carbon\Carbon::parse($entry['date']))->format('d M Y') }}</td>
                    <td class="mono"><strong>{{ $entry['ref'] }}</strong></td>
                    <td>{{ $entry['type'] }}</td>
                    <td>{{ $entry['description'] }}</td>
                    <td class="text-right mono">{{ $entry['debit'] > 0 ? 'Rs. ' . number_format($entry['debit'], 0) : '—' }}</td>
                    <td class="text-right mono">{{ $entry['credit'] > 0 ? 'Rs. ' . number_format($entry['credit'], 0) : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 40px 0;">No ledger transactions found.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($ledgerEntries) > 0)
            @php
                $totalDebit = collect($ledgerEntries)->sum('debit');
                $totalCredit = collect($ledgerEntries)->sum('credit');
            @endphp
            <tfoot>
                <tr style="background: #e2e8f0; border-top: 3px solid #0f172a; border-bottom: 3px solid #0f172a; font-weight: 900;">
                    <td colspan="4" style="padding: 12px 10px; font-weight: 900; font-size: 1.05rem; color: #000;">TOTAL SUMMARY</td>
                    <td class="text-right mono debit" style="padding: 12px 10px; font-weight: 900; font-size: 1.05rem;">Rs. {{ number_format($totalDebit, 0) }}</td>
                    <td class="text-right mono credit" style="padding: 12px 10px; font-weight: 900; font-size: 1.05rem;">Rs. {{ number_format($totalCredit, 0) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Printed Footer -->
    <div class="footer">
        <span>Palladium Mall Management Office, Islamabad</span>
        <span>Generated on {{ now()->format('d M Y \a\t h:i A') }}</span>
    </div>

    <script>
        window.addEventListener('load', function () {
            if (window.opener) { setTimeout(function () { window.print(); }, 400); }
        });
    </script>
</body>
</html>
