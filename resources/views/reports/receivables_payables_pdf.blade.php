<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #1E293B; line-height: 1.35; padding: 20px; }
        
        .header { margin-bottom: 15px; border-bottom: 2px solid #1D3461; padding-bottom: 8px; }
        .header h1 { font-size: 14px; color: #1D3461; font-weight: bold; margin-bottom: 3px; }
        .header p { font-size: 8px; color: #64748B; margin-top: 1px; }
        
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .summary-td { width: 20%; padding-right: 8px; }
        .summary-td:last-child { padding-right: 0; }
        .summary-card { background: #F8FAFF; border: 1px solid #E2E8F0; padding: 8px 10px; border-radius: 4px; }
        .summary-card .label { font-size: 6.5px; color: #64748B; text-transform: uppercase; font-weight: bold; }
        .summary-card .value { font-size: 10px; font-weight: bold; color: #1D3461; margin-top: 3px; }
        
        .section-title { font-size: 10px; font-weight: bold; color: #1D3461; margin: 15px 0 6px 0; border-bottom: 1px solid #CBD5E1; padding-bottom: 3px; text-transform: uppercase; }
        .section-desc { font-size: 7.5px; color: #64748B; margin-bottom: 8px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th { background: #1D3461; color: white; padding: 6px 8px; font-size: 7px; text-transform: uppercase; font-weight: bold; text-align: left; }
        table.data-table td { padding: 5px 8px; border-bottom: 1px solid #E2E8F0; font-size: 7.5px; vertical-align: middle; }
        table.data-table tr:nth-child(even) td { background: #F9FBFF; }
        table.data-table tfoot td { background: #F1F5F9; font-weight: bold; border-top: 2px solid #CBD5E1; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-green { color: #10B981; font-weight: bold; }
        .text-red { color: #EF4444; font-weight: bold; }
        .font-mono { font-family: Courier, monospace; }
        .footer { position: fixed; bottom: 15px; left: 20px; right: 20px; border-top: 1px solid #E2E8F0; padding-top: 6px; text-align: center; font-size: 7px; color: #94A3B8; }
    </style>
</head>
<body>

    {{-- Report Header --}}
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>
            <strong>Palladium Mall Management System</strong> &bull; 
            Statement Period: {{ $dateFrom ? Carbon\Carbon::parse($dateFrom)->format('d M Y') : 'Beginning' }} to {{ $dateTo ? Carbon\Carbon::parse($dateTo)->format('d M Y') : 'Present' }}
        </p>
        <p>Generated on: {{ now()->format('d M Y h:i A') }}</p>
    </div>

    {{-- Summary Widgets Cards --}}
    <table class="summary-table">
        <tr>
            @if($type === 'receivables')
                <td class="summary-td" style="width: 33.33%;">
                    <div class="summary-card" style="border-left: 3px solid #10B981;">
                        <div class="label">Total Receivables</div>
                        <div class="value" style="color: #10B981;">Rs. {{ number_format($totalReceivablesDue, 2) }}</div>
                    </div>
                </td>
                <td class="summary-td" style="width: 33.33%;">
                    <div class="summary-card" style="border-left: 3px solid #0EA5E9;">
                        <div class="label">Total Received</div>
                        <div class="value" style="color: #0EA5E9;">Rs. {{ number_format($totalReceivablesPaid, 2) }}</div>
                    </div>
                </td>
                <td class="summary-td" style="width: 33.33%;">
                    <div class="summary-card" style="border-left: 3px solid #10B981;">
                        <div class="label">Balance</div>
                        <div class="value" style="color: #10B981;">Rs. {{ number_format($totalReceivablesNet, 2) }}</div>
                    </div>
                </td>
            @else
                <td class="summary-td" style="width: 33.33%;">
                    <div class="summary-card" style="border-left: 3px solid #EF4444;">
                        <div class="label">Total Payables</div>
                        <div class="value" style="color: #EF4444;">Rs. {{ number_format($totalPayablesDue, 2) }}</div>
                    </div>
                </td>
                <td class="summary-td" style="width: 33.33%;">
                    <div class="summary-card" style="border-left: 3px solid #0EA5E9;">
                        <div class="label">Total Paid</div>
                        <div class="value" style="color: #0EA5E9;">Rs. {{ number_format($totalPayablesPaid, 2) }}</div>
                    </div>
                </td>
                <td class="summary-td" style="width: 33.33%;">
                    <div class="summary-card" style="border-left: 3px solid #EF4444;">
                        <div class="label">Balance</div>
                        <div class="value" style="color: #EF4444;">Rs. {{ number_format($totalPayablesNet, 2) }}</div>
                    </div>
                </td>
            @endif
        </tr>
    </table>

    @if($type === 'payables')
        {{-- SECTION 1: Payables --}}
        <div class="section-title">Payables Summary (Owed/Held by Building)</div>
        <div class="section-desc">List of collections (e.g. tenant security deposits, general party receipts) that are held or owed by the building.</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Entity Name</th>
                    <th style="width: 20%;">Category</th>
                    <th style="width: 20%;">Details</th>
                    <th style="width: 10%;" class="text-right">Owed/Held</th>
                    <th style="width: 10%;" class="text-right">Paid/Settled</th>
                    <th style="width: 10%;" class="text-right">Net Payable</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payables as $row)
                    <tr>
                        <td style="font-weight: bold;">{{ $row['name'] }}</td>
                        <td>{{ $row['category'] }}</td>
                        <td style="color: #64748B;">{{ $row['details'] }}</td>
                        <td class="text-right">Rs. {{ number_format($row['due'], 2) }}</td>
                        <td class="text-right text-red">Rs. {{ number_format($row['paid'], 2) }}</td>
                        <td class="text-right font-mono" style="font-weight: bold;">Rs. {{ number_format($row['net'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="color: #94A3B8; padding: 15px 0;">No active payables found matching filters.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Total Building Payables</td>
                    <td class="text-right">Rs. {{ number_format(collect($payables)->sum('due'), 2) }}</td>
                    <td class="text-right">Rs. {{ number_format(collect($payables)->sum('paid'), 2) }}</td>
                    <td class="text-right font-mono">Rs. {{ number_format($totalPayables, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        {{-- SECTION 2: Receivables --}}
        <div class="section-title">
            {{ ($receivableScope ?? 'pm_mall') === 'other' ? 'Other Receivables Summary (Not Managed by PM Mall)' : 'PM Mall Receivables Summary (Managed by PM Mall)' }}
        </div>
        <div class="section-desc">
            {{ ($receivableScope ?? 'pm_mall') === 'other' ? 'List of outstanding dues for self-owned / external units not managed by PM Mall.' : 'List of outstanding dues (tenant rent, maintenance, landlord credits, party receivables) for units/accounts managed by PM Mall.' }}
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Entity Name</th>
                    <th style="width: 20%;">Category</th>
                    <th style="width: 20%;">Details</th>
                    <th style="width: 10%;" class="text-right">Due/Credit</th>
                    <th style="width: 10%;" class="text-right">Paid/Received</th>
                    <th style="width: 10%;" class="text-right">Net Receivable</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receivables as $row)
                    <tr>
                        <td style="font-weight: bold;">{{ $row['name'] }}</td>
                        <td>{{ $row['category'] }}</td>
                        <td style="color: #64748B;">{{ $row['unit'] ? 'Unit ' . $row['unit'] : '—' }}</td>
                        <td class="text-right">Rs. {{ number_format($row['due'], 2) }}</td>
                        <td class="text-right text-green">Rs. {{ number_format($row['paid'], 2) }}</td>
                        <td class="text-right font-mono" style="font-weight: bold;">Rs. {{ number_format($row['net'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="color: #94A3B8; padding: 15px 0;">No active receivables found matching filters.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">
                        {{ ($receivableScope ?? 'pm_mall') === 'other' ? 'Total Other Receivables (Not Managed by PM Mall)' : 'Total PM Mall Receivables' }}
                    </td>
                    <td class="text-right">Rs. {{ number_format(collect($receivables)->sum('due'), 2) }}</td>
                    <td class="text-right">Rs. {{ number_format(collect($receivables)->sum('paid'), 2) }}</td>
                    <td class="text-right font-mono">Rs. {{ number_format($totalReceivables, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="footer">
        Palladium Mall Summary Report &bull; Page 1 of 1
    </div>

</body>
</html>
