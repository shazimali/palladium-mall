<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Party Ledger Statement — Palladium Mall</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 13px; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #111827; background: #fff; padding: 32px 40px; line-height: 1.5; }
        
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #0284c7; padding-bottom: 14px; margin-bottom: 20px; }
        .logo-section { display: flex; align-items: center; gap: 10px; }
        .logo-icon { width: 32px; height: 32px; background: #0284c7; border-radius: 6px; }
        .logo-text { font-size: 1.3rem; font-weight: 800; color: #0f172a; }
        .doc-title { text-align: right; }
        .doc-title h2 { font-size: 1.1rem; font-weight: 700; color: #0284c7; }
        .doc-title p { font-size: 0.8rem; color: #6b7280; margin-top: 2px; }

        .party-info { margin-bottom: 20px; font-size: 0.95rem; }
        .party-info p { margin-bottom: 4px; }
        .party-info strong { color: #0f172a; }

        .summary-boxes { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
        .summary-box { border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; background: #f8fafc; }
        .summary-box h3 { font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: #475569; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 10px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.85rem; }
        .summary-row:last-child { margin-bottom: 0; font-weight: 700; border-top: 1px dashed #cbd5e1; padding-top: 6px; }
        
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; margin-bottom: 30px; }
        thead tr { background: #f1f5f9; }
        thead th { padding: 10px; text-align: left; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; color: #475569; border-bottom: 2px solid #e2e8f0; }
        thead th.text-right, tbody td.text-right { text-align: right; }
        tbody tr { border-bottom: 1px solid #f1f5f9; }
        tbody td { padding: 9px 10px; color: #334155; }
        
        .mono { font-family: monospace; font-size: 0.8rem; }
        .debit { color: #dc2626; font-weight: 600; }
        .credit { color: #16a34a; font-weight: 600; }
        
        .footer { margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 12px; display: flex; justify-content: space-between; font-size: 0.75rem; color: #94a3b8; }
        .no-print { text-align: center; margin-bottom: 24px; }
        .print-btn { display: inline-flex; align-items: center; gap: 8px; background: #0284c7; color: #fff; border: none; border-radius: 6px; padding: 8px 20px; font-size: 0.9rem; font-weight: 600; cursor: pointer; }
        .print-btn:hover { background: #0369a1; }
        
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

    <!-- Financial Dues Summary boxes -->
    <div class="summary-boxes">
        {{-- Receivable Dues Summary --}}
        <div class="summary-box">
            <h3>📥 Mall Receivables from Party</h3>
            <div class="summary-row">
                <span>Total Due Receivable:</span>
                <span class="mono">Rs. {{ number_format($summary['total_due_receivable'], 0) }}</span>
            </div>
            <div class="summary-row">
                <span>Total Received (Receipts):</span>
                <span class="mono credit">Rs. {{ number_format($summary['total_received'], 0) }}</span>
            </div>
            <div class="summary-row">
                <span>Net Receivable Balance:</span>
                <span class="mono {{ $summary['net_receivable'] > 0 ? 'debit' : '' }}">Rs. {{ number_format($summary['net_receivable'], 0) }}</span>
            </div>
        </div>

        {{-- Payable Dues Summary --}}
        <div class="summary-box">
            <h3>📤 Mall Payables to Party</h3>
            <div class="summary-row">
                <span>Total Due Payable:</span>
                <span class="mono">Rs. {{ number_format($summary['total_due_payable'], 0) }}</span>
            </div>
            <div class="summary-row">
                <span>Total Paid (Payments):</span>
                <span class="mono debit">Rs. {{ number_format($summary['total_paid'], 0) }}</span>
            </div>
            <div class="summary-row">
                <span>Net Payable Balance:</span>
                <span class="mono {{ $summary['net_payable'] > 0 ? 'debit' : '' }}">Rs. {{ number_format($summary['net_payable'], 0) }}</span>
            </div>
        </div>
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
                    <td class="mono">{{ $entry['date']->format('d M Y') }}</td>
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
