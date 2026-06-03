<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1E293B;
            padding: 24px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #0B1C3D;
        }

        .header-left h1 {
            font-size: 20px;
            font-weight: bold;
            color: #0B1C3D;
            margin-bottom: 4px;
        }

        .header-left p {
            font-size: 9px;
            color: #64748B;
            line-height: 1.6;
        }

        .header-right {
            text-align: right;
        }

        .invoice-label {
            font-size: 24px;
            font-weight: bold;
            color: #F59E0B;
            letter-spacing: 2px;
        }

        .invoice-number {
            font-size: 11px;
            color: #0B1C3D;
            font-weight: bold;
            margin-top: 4px;
        }

        .invoice-date {
            font-size: 9px;
            color: #64748B;
            margin-top: 2px;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 16px;
        }

        .meta-box {
            flex: 1;
            background: #F8FAFF;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 10px 12px;
        }

        .meta-box .label {
            font-size: 8px;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .meta-box .value {
            font-size: 11px;
            font-weight: bold;
            color: #0B1C3D;
        }

        .meta-box .sub {
            font-size: 9px;
            color: #64748B;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        thead tr {
            background: #0B1C3D;
        }

        thead th {
            padding: 8px 10px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        thead th:last-child {
            text-align: right;
        }

        tbody tr:nth-child(even) {
            background: #F8FAFF;
        }

        tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 9.5px;
        }

        tbody td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: bold;
        }

        .badge-rent {
            background: #DBEAFE;
            color: #1A56DB;
        }

        .badge-maintenance {
            background: #EDE9FE;
            color: #7C3AED;
        }

        .badge-electricity {
            background: #FEF3C7;
            color: #DC7609;
        }

        .badge-water {
            background: #CFFAFE;
            color: #0694A2;
        }

        .badge-gas {
            background: #FEE2E2;
            color: #DC2626;
        }

        .badge-fine {
            background: #FEE2E2;
            color: #DC2626;
        }

        .badge-other {
            background: #F1F5F9;
            color: #64748B;
        }

        .totals {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 16px;
        }

        .totals-box {
            width: 260px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #E2E8F0;
            font-size: 10px;
        }

        .totals-row.total {
            background: #0B1C3D;
            color: white;
            padding: 8px 10px;
            border-radius: 6px;
            margin-top: 6px;
            border: none;
        }

        .totals-row.total .t-label {
            font-weight: bold;
            font-size: 11px;
        }

        .totals-row.total .t-value {
            font-weight: bold;
            font-size: 13px;
            color: #F59E0B;
        }

        .notes {
            background: #F8FAFF;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 16px;
        }

        .notes .label {
            font-size: 8px;
            color: #64748B;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .notes p {
            font-size: 9px;
            color: #334155;
            line-height: 1.6;
        }

        .footer {
            border-top: 1px solid #E2E8F0;
            padding-top: 10px;
            text-align: center;
            font-size: 8px;
            color: #94A3B8;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: bold;
        }

        .status-draft {
            background: #F1F5F9;
            color: #64748B;
        }

        .status-sent {
            background: #DBEAFE;
            color: #1A56DB;
        }

        .status-paid {
            background: #D1FAE5;
            color: #059669;
        }
    </style>
</head>

<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <h1>Palladium Mall</h1>
            <p>
                123 Mall Road, Gujranwala, Punjab, Pakistan<br>
                Phone: 0300-1234567 &bull; Email: admin@palladiummall.pk<br>
                www.palladiummall.pk
            </p>
        </div>
        <div class="header-right">
            <div class="invoice-label">INVOICE</div>
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <div class="invoice-date">Generated: {{ now()->format('d M Y') }}</div>
            <div style="margin-top:6px;">
                <span class="status-badge status-{{ $invoice->status }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Meta --}}
    <div class="meta">
        <div class="meta-box">
            <div class="label">Billed To</div>
            <div class="value">{{ $invoice->tenant->name }}</div>
            <div class="sub">CNIC: {{ $invoice->tenant->cnic }}</div>
            <div class="sub">Phone: {{ $invoice->tenant->phone }}</div>
        </div>
        <div class="meta-box">
            <div class="label">Unit</div>
            <div class="value">{{ $invoice->unit->unit_number }}</div>
            <div class="sub">{{ ucfirst($invoice->unit->type) }}</div>
            @if($invoice->unit->floor)
                <div class="sub">{{ $invoice->unit->floor->name }} {{ $invoice->unit->block ? '/ ' . $invoice->unit->block->name : '' }}
                </div>
            @endif
        </div>
        <div class="meta-box">
            <div class="label">Billing Month</div>
            <div class="value">{{ $invoice->month->format('F Y') }}</div>
            <div class="sub">Due Date: {{ $invoice->due_date->format('d M Y') }}</div>
            @if($invoice->sent_at)
                <div class="sub">Sent: {{ $invoice->sent_at->format('d M Y') }}</div>
            @endif
        </div>
    </div>

    {{-- Items table --}}
    <table>
        <thead>
            <tr>
                <th style="width:30px">#</th>
                <th>Description</th>
                <th style="width:100px">Type</th>
                <th style="width:100px; text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td><span class="badge badge-{{ $item->type }}">{{ ucfirst($item->type) }}</span></td>
                    <td>Rs. {{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="totals">
        <div class="totals-box">
            <div class="totals-row">
                <span class="t-label">Subtotal</span>
                <span>Rs. {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            <div class="totals-row total">
                <span class="t-label">Total Due</span>
                <span class="t-value">Rs. {{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if($invoice->notes)
        <div class="notes">
            <div class="label">Notes</div>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        This invoice was generated by Palladium Mall Management System &bull;
        {{ $invoice->invoice_number }} &bull; {{ now()->format('d M Y H:i') }}
    </div>

</body>

</html>