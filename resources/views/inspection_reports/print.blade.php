<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportType->name }} Report — {{ $report->report_date->format('d M Y') }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 13px;
            color: #1f2937;
            padding: 24px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .logo-title h1 {
            font-size: 20px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .logo-title p {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        .meta-box {
            text-align: right;
        }

        .meta-box .badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 4px 10px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 11px;
            margin-bottom: 4px;
        }

        .meta-box p {
            font-size: 12px;
            color: #4b5563;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .summary-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            background: #f9fafb;
        }

        .summary-card.pass {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        .summary-card.fail {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .summary-card .label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 800;
            opacity: 0.8;
        }

        .summary-card .value {
            font-size: 18px;
            font-weight: 900;
            margin-top: 2px;
        }

        .remarks-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            background: #fdfdfd;
        }

        .remarks-box strong {
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
            display: block;
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #f3f4f6;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 800;
            color: #374151;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 800;
        }

        .status-pass {
            background: #dcfce7;
            color: #15803d;
        }

        .status-fail {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-na {
            background: #f3f4f6;
            color: #6b7280;
        }

        .system-remark {
            font-weight: 700;
            color: #1d4ed8;
            font-size: 12px;
        }

        .user-remarks {
            font-size: 11px;
            color: #4b5563;
            margin-top: 2px;
        }

        .item-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #d1d5db;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .sig-block {
            width: 40%;
            border-top: 1px dashed #9ca3af;
            padding-top: 8px;
            text-align: center;
        }

        .sig-block p {
            font-size: 11px;
            color: #4b5563;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="margin-bottom: 16px; display: flex; gap: 8px;">
        <button onclick="window.print()"
            style="padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-weight: 700; cursor: pointer;">
            🖨️ Print Report
        </button>
        <button onclick="window.close()"
            style="padding: 8px 16px; background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 6px; font-weight: 600; cursor: pointer;">
            Close
        </button>
    </div>

    {{-- Header --}}
    <div style="text-align: center; border-bottom: 2px solid #111827; padding-bottom: 16px; margin-bottom: 20px;">
        <h1
            style="font-size: 26px; font-weight: 900; text-transform: uppercase; letter-spacing: 1.5px; color: #111827; margin-bottom: 4px;">
            Palladium Mall
        </h1>
        <div
            style="font-size: 17px; font-weight: 800; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
            {{ $reportType->name }} Inspection Report
        </div>
        <div
            style="display: flex; justify-content: center; align-items: center; gap: 24px; flex-wrap: wrap; font-size: 14px; font-weight: 800; color: #1f2937;">
            <span style="font-size: 14px; font-weight: 800; color: #111827;">
                Report Date: <span
                    style="font-weight: 900; color: #1d4ed8;">{{ $report->report_date->format('d M Y') }}</span>
            </span>
            @if($report->member)
                <span style="font-size: 14px; font-weight: 800; color: #111827;">
                    Member / Officer: <span style="font-weight: 900; color: #4338ca;">{{ $report->member->member_name }}</span>
                </span>
            @endif
            <span style="font-size: 14px; font-weight: 800; color: #111827;">
                Inspector: <span style="font-weight: 900;">{{ $report->reporter?->name ?? 'Admin Staff' }}</span>
            </span>
        </div>
    </div>

    {{-- Checklist Table --}}
    <table>
        <thead>
            <tr>
                <th style="width: 35px; text-align: center;">#</th>
                <th>Area / Item Description</th>
                <th style="width: 130px;">Status</th>
                <th>{{ $reportType->activeRemarks()->exists() ? 'System Remark & Remarks' : 'Remarks' }}</th>
                <th style="width: 70px; text-align: center;">Photo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->items->sortBy(fn($i) => $i->head?->sort_order ?? 999) as $idx => $item)
                <tr>
                    <td style="text-align: center; color: #6b7280; font-weight: bold; font-size: 13px;">
                        {{ $loop->iteration }}</td>
                    <td style="font-weight: 800; font-size: 14px; color: #111827; line-height: 1.3;">
                        {{ $item->head?->name ?? '—' }}</td>
                    <td>
                        @if($item->status === 'yes')
                            <span class="status-badge status-pass">✓ Pass / Clean</span>
                        @elseif($item->status === 'no')
                            <span class="status-badge status-fail">✗ Fail / Issue</span>
                        @else
                            <span class="status-badge status-na">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if($item->systemRemark)
                            <div class="system-remark">{{ $item->systemRemark->remark }}</div>
                        @endif
                        @if($item->remarks)
                            <div class="user-remarks">{{ $item->remarks }}</div>
                        @endif
                        @if(!$item->systemRemark && !$item->remarks)
                            <span style="color: #9ca3af;">—</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($item->image_path)
                            <img src="{{ $item->image_url }}" alt="item" class="item-img" />
                        @else
                            <span style="color: #9ca3af; font-size: 10px;">None</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f8fafc; font-weight: bold; border-top: 2px solid #cbd5e1;">
                <td colspan="2" style="padding: 10px; font-size: 11px; text-transform: uppercase; color: #475569;">
                    <strong>Total Items:</strong> {{ $report->items->count() }}
                </td>
                <td colspan="3" style="padding: 10px; font-size: 11px;">
                    <span style="color: #15803d; margin-right: 18px;">✓ Pass / Clean:
                        <strong>{{ $report->passCount() }}</strong></span>
                    <span style="color: #b91c1c; margin-right: 18px;">✗ Fail / Issue:
                        <strong>{{ $report->failCount() }}</strong></span>
                    <span style="color: #64748b;">N/A: <strong>{{ $report->naCount() }}</strong></span>
                </td>
            </tr>
        </tfoot>
    </table>

    {{-- Overall Remarks Box --}}
    @if($report->overall_remarks)
        <div class="remarks-box"
            style="margin-top: 5px; margin-bottom: 25px; border: 1px solid #e2e8f0; background: #fafafa; border-radius: 6px; padding: 12px;">
            <strong
                style="font-size: 11px; text-transform: uppercase; color: #475569; display: block; margin-bottom: 4px;">Overall
                Remarks:</strong>
            <p style="font-size: 12px; color: #1e293b; line-height: 1.5;">{{ $report->overall_remarks }}</p>
        </div>
    @endif

    {{-- Signatures --}}
    <div class="signatures">
        <div class="sig-block">
            <strong>{{ $report->reporter?->name ?? 'Inspector' }}</strong>
            <p>Inspector Signature & Date</p>
        </div>
        <div class="sig-block">
            <strong>Facility Manager</strong>
            <p>Verified & Approved Signature</p>
        </div>
    </div>
</body>

</html>