<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Flat Inspection — {{ $report->type_label }} | {{ $report->agreement?->unit?->flat?->flat_number ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #222; }
        .page { max-width: 900px; margin: 0 auto; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #222; padding-bottom: 12px; margin-bottom: 16px; }
        .mall-heading { font-size: 24px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; color: #111827; line-height: 1.1; }
        .title { font-size: 15px; font-weight: 700; color: #4b5563; margin-top: 2px; }
        .date-large { font-size: 16px; font-weight: 800; color: #1e40af; background: #eff6ff; padding: 4px 10px; border-radius: 6px; border: 1px solid #bfdbfe; display: inline-block; }
        .subtitle { font-size: 11px; color: #555; margin-top: 4px; }
        .meta-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 16px; padding: 12px; background: #f8f8f8; border: 1px solid #ddd; border-radius: 6px; }
        .meta-item label { display: block; font-size: 9px; text-transform: uppercase; font-weight: 700; color: #888; margin-bottom: 2px; }
        .meta-item span { font-weight: 600; font-size: 12px; }
        .summary { display: flex; gap: 12px; margin-bottom: 16px; }
        .summary-card { flex: 1; text-align: center; padding: 10px; border-radius: 6px; border: 1px solid #ddd; }
        .summary-card .number { font-size: 20px; font-weight: 800; }
        .summary-card .label { font-size: 10px; font-weight: 600; margin-top: 2px; }
        .pass-card { background: #f0fff4; border-color: #6ee7b7; color: #065f46; }
        .fail-card { background: #fff5f5; border-color: #fca5a5; color: #7f1d1d; }
        .total-card { background: #f9fafb; border-color: #d1d5db; color: #374151; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f1f5f9; text-align: left; padding: 8px 10px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid #ddd; }
        td { padding: 7px 10px; border: 1px solid #e5e7eb; vertical-align: middle; }
        tr:nth-child(even) td { background: #f9fafb; }
        .badge-pass { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; }
        .badge-fail { background: #fee2e2; color: #7f1d1d; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; }
        .badge-na { background: #f3f4f6; color: #6b7280; padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; }
        .img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px; margin-top: 30px; }
        .sig-box { border-top: 1px solid #222; padding-top: 8px; text-align: center; font-size: 11px; font-weight: 600; }
        .print-btn { position: fixed; top: 12px; right: 12px; padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; font-size: 12px; }
        @media print { .print-btn { display: none; } body { font-size: 11px; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ Print</button>

    <div class="page">
        <div class="header">
            <div>
                <div class="mall-heading">PALLADIUM MALL</div>
                <div class="title">
                    {{ $report->type === 'move_in' ? '🏠 Move In' : '🚪 Move Out' }} Flat Inspection Report
                </div>
                <div class="subtitle">
                    Flat: {{ $report->agreement?->unit?->flat?->flat_number ?? '—' }} &nbsp;|&nbsp;
                    Tenant: {{ $report->tenant?->name ?? '—' }} &nbsp;|&nbsp;
                    Agreement #{{ $report->agreement_id }}
                </div>
            </div>
            <div style="text-align:right">
                <div class="date-large">
                    DATE: {{ $report->inspected_at?->format('d M Y') ?? now()->format('d M Y') }}
                </div>
                <div style="font-size:10px; color:#777; margin-top:5px;">Generated: {{ now()->format('d M Y, h:i A') }}</div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="summary">
            <div class="summary-card pass-card">
                <div class="number">{{ $report->passCount() }}</div>
                <div class="label">✅ Pass</div>
            </div>
            <div class="summary-card fail-card">
                <div class="number">{{ $report->failCount() }}</div>
                <div class="label">❌ Fail</div>
            </div>
            <div class="summary-card total-card">
                <div class="number">{{ $report->totalCount() }}</div>
                <div class="label">Total</div>
            </div>
        </div>

        {{-- Meta --}}
        <div class="meta-grid">
            <div class="meta-item"><label>Inspector</label><span>{{ $report->inspection_member ?: ($report->inspectionPerson?->name ?? '—') }}</span></div>
            <div class="meta-item"><label>Inspection Date</label><span>{{ $report->inspected_at?->format('d M Y') ?? '—' }}</span></div>
            <div class="meta-item"><label>Flat Condition</label><span>{{ ucfirst($report->flat_condition ?? '—') }}</span></div>
            <div class="meta-item"><label>Type</label><span>{{ $report->type_label }}</span></div>
        </div>
        @if($report->remarks)
            <p style="margin-bottom:14px; font-size:11px; background:#fffde7; padding:8px; border-radius:5px; border:1px solid #fde68a;"><strong>Overall Remarks:</strong> {{ $report->remarks }}</p>
        @endif

        {{-- Items Table --}}
        <table>
            <thead>
                <tr>
                    <th style="width:30px">#</th>
                    <th>Inspection Head</th>
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
                                <span class="badge-pass">✅ Pass</span>
                            @elseif($item->status === false)
                                <span class="badge-fail">❌ Fail</span>
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
            <div class="sig-box">Inspector / {{ $report->inspection_member ?? 'Inspector' }}</div>
            <div class="sig-box">Tenant / {{ $report->tenant?->name ?? 'Tenant' }}</div>
            <div class="sig-box">Management</div>
        </div>
    </div>
</body>
</html>
