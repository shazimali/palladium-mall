<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Cleaning Inspection Report — {{ $report->report_date->format('d M Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #222;
        }

        .page {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .title {
            font-size: 18px;
            font-weight: 800;
        }

        .subtitle {
            font-size: 11px;
            color: #555;
            margin-top: 2px;
        }

        .summary {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .summary-card {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .summary-card .number {
            font-size: 20px;
            font-weight: 800;
        }

        .summary-card .label {
            font-size: 10px;
            font-weight: 600;
            margin-top: 2px;
        }

        .clean-card {
            background: #f0fff4;
            border-color: #6ee7b7;
            color: #065f46;
        }

        .issue-card {
            background: #fff5f5;
            border-color: #fca5a5;
            color: #7f1d1d;
        }

        .total-card {
            background: #f9fafb;
            border-color: #d1d5db;
            color: #374151;
        }

        .meta {
            display: flex;
            gap: 20px;
            margin-bottom: 14px;
            padding: 10px 14px;
            background: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 11px;
        }

        .meta span {
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: #f1f5f9;
            text-align: left;
            padding: 8px 10px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: 1px solid #ddd;
        }

        td {
            padding: 7px 10px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        tr:nth-child(even) td {
            background: #f9fafb;
        }

        .badge-clean {
            background: #d1fae5;
            color: #065f46;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .badge-issue {
            background: #fee2e2;
            color: #7f1d1d;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .badge-na {
            background: #f3f4f6;
            color: #6b7280;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .img-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }

        .sig-box {
            border-top: 1px solid #222;
            padding-top: 8px;
            text-align: center;
            font-size: 11px;
            font-weight: 600;
        }

        .print-btn {
            position: fixed;
            top: 12px;
            right: 12px;
            padding: 8px 16px;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            font-size: 12px;
        }

        @media print {
            .print-btn {
                display: none;
            }

            body {
                font-size: 11px;
            }
        }
    </style>
</head>

<body>
    <button class="print-btn" onclick="window.print()">🖨️ Print</button>

    <div class="page">
        <div class="header">
            <div>
                <div class="mall-heading">PALLADIUM MALL</div>
                <div class="title"> Daily Cleaning Inspection Report</div>
            </div>
            <div style="text-align:right">
                <div class="date-large">
                    DATE: {{ $report->report_date->format('d M Y') }}
                </div>
                <div style="font-size:11px; color:#555; margin-top:5px;">Reported By:
                    <strong>{{ $report->reporter?->name ?? '—' }}</strong>
                </div>
                <div style="font-size:10px; color:#777; margin-top:2px;">Generated: {{ now()->format('d M Y, h:i A') }}
                </div>
            </div>
        </div>

        @if($report->overall_remarks)
            <p
                style="margin-bottom:12px; font-size:11px; background:#fffde7; padding:8px; border-radius:5px; border:1px solid #fde68a;">
                <strong>Overall Remarks:</strong> {{ $report->overall_remarks }}
            </p>
        @endif

        {{-- Items Table --}}
        <table>
            <thead>
                <tr>
                    <th style="width:30px">#</th>
                    <th>Area / Item</th>
                    <th style="width:90px">Status</th>
                    <th>Remarks</th>
                    <th style="width:70px">Image</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><strong>{{ $item->head?->name ?? '—' }}</strong></td>
                        <td>
                            @if($item->status === true)
                                <span class="badge-clean">✅ YES</span>
                            @elseif($item->status === false)
                                <span class="badge-issue">❌ NO</span>
                            @else
                                <span class="badge-na">— N/A</span>
                            @endif
                        </td>
                        <td>{{ $item->remarks ?: '—' }}</td>
                        <td>
                            @if($item->image_path)
                                <img src="{{ Storage::url($item->image_path) }}" class="img-thumb" />
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Signatures --}}
        <div class="signatures">
            <div class="sig-box">Reported By: {{ $report->reporter?->name ?? '—' }}</div>
            <div class="sig-box">Supervisor</div>
            <div class="sig-box">Management</div>
        </div>
    </div>
</body>

</html>