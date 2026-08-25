<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Flat Inspection — {{ $report->type_label }} | {{ $report->effective_unit?->unit_number ?? '' }}</title>
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
                    {{ $report->type_label }} Report
                </div>
                <div class="subtitle">
                    Unit / Flat: <strong>{{ $report->effective_unit?->unit_number ?? '—' }}</strong> ({{ ucfirst($report->effective_unit?->type ?? 'Flat') }})
                    @if($report->tenant)
                        &nbsp;|&nbsp; Tenant: <strong>{{ $report->tenant->name }}</strong>
                    @endif
                    @if($report->agreement_id)
                        &nbsp;|&nbsp; Agreement #<strong>{{ $report->agreement_id }}</strong>
                    @endif
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
            <div class="meta-item">
                <label>Unit / Flat</label>
                <span>Unit {{ $report->effective_unit?->unit_number ?? '—' }} ({{ ucfirst($report->effective_unit?->type ?? 'Unit') }})</span>
            </div>
            <div class="meta-item">
                <label>Stage / Type</label>
                <span>{{ $report->type_label }}</span>
            </div>
            <div class="meta-item">
                <label>Inspector</label>
                <span>{{ $report->inspector?->name ?? 'Admin' }}</span>
            </div>
            <div class="meta-item">
                <label>Inspection Officer</label>
                <span>{{ $report->inspectionPerson?->name ?? ($report->inspection_member ?: '—') }}</span>
            </div>
        </div>

        @if($report->remarks)
            <div style="margin-bottom:14px; padding:10px; background:#fffbeb; border:1px solid #fef3c7; border-radius:6px; font-size:11px; color:#92400e;">
                <strong>Overall Remarks:</strong> {{ $report->remarks }}
            </div>
        @endif

        {{-- Checklist Table --}}
        <table>
            <thead>
                <tr>
                    <th style="width:30px">#</th>
                    <th style="width:200px">Inspection Head</th>
                    <th style="width:70px">Status</th>
                    <th>Remarks</th>
                    <th style="width:60px">Image</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report->items as $i => $item)
                    <tr>
                        <td style="text-align:center; color:#888">{{ $i + 1 }}</td>
                        <td><strong>{{ $item->head?->name ?? '—' }}</strong></td>
                        <td>
                            @if($item->status === true)
                                <span class="badge-pass">PASS</span>
                            @elseif($item->status === false)
                                <span class="badge-fail">FAIL</span>
                            @else
                                <span class="badge-na">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($item->systemRemark)
                                <div style="font-weight:700; color:#111; margin-bottom:2px;">
                                    🏷️ {{ $item->systemRemark->remark }}
                                </div>
                            @endif
                            <div style="color:#555;">{{ $item->remarks ?: '—' }}</div>
                        </td>
                        <td style="text-align:center">
                            @if($item->image_path)
                                <img src="{{ Storage::url($item->image_path) }}" class="img-thumb" />
                            @else
                                <span style="color:#ccc">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Super Admin Remarks & Feedback Box --}}
        @if($report->admin_rating || $report->admin_remarks || $report->admin_photo)
            <div style="margin-bottom:20px; padding:12px; background:#f5f7ff; border:1.5px solid #6366f1; border-radius:6px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <strong style="font-size:11px; text-transform:uppercase; color:#4338ca;">
                        👑 Admin Evaluation & Remarks:
                    </strong>
                    @if($report->admin_rating === 'good')
                        <span style="background:#dcfce7; color:#15803d; padding:3px 8px; border-radius:4px; font-weight:800; font-size:10px;">
                            ✨ SATISFACTORY
                        </span>
                    @elseif($report->admin_rating === 'bad')
                        <span style="background:#fee2e2; color:#b91c1c; padding:3px 8px; border-radius:4px; font-weight:800; font-size:10px;">
                            ⚠️ UNSATISFACTORY
                        </span>
                    @endif
                </div>
                @if($report->admin_remarks)
                    <p style="font-size:11px; color:#1e1b4b; line-height:1.5; margin-bottom:6px;">{{ $report->admin_remarks }}</p>
                @endif
                @if($report->admin_photo)
                    <div style="margin-top:6px;">
                        <span style="font-size:9px; font-weight:bold; color:#6366f1; text-transform:uppercase; display:block; margin-bottom:4px;">Attached Admin Photo:</span>
                        <img src="{{ $report->admin_photo_url }}" alt="Admin Photo" style="max-height:100px; max-width:180px; object-fit:cover; border-radius:4px; border:1px solid #c7d2fe;" />
                    </div>
                @endif
            </div>
        @endif

        {{-- Signatures --}}
        <div class="signatures">
            <div class="sig-box">
                <p>Inspection Officer</p>
                <p style="font-size:10px; color:#888; margin-top:2px;">{{ $report->inspectionPerson?->name ?? ($report->inspection_member ?: 'Inspector') }}</p>
            </div>
            <div class="sig-box">
                <p>{{ $report->tenant ? 'Tenant Signature' : 'Maintenance Supervisor' }}</p>
                <p style="font-size:10px; color:#888; margin-top:2px;">{{ $report->tenant?->name ?? 'Office Admin' }}</p>
            </div>
            <div class="sig-box">
                <p>Operations Manager</p>
                <p style="font-size:10px; color:#888; margin-top:2px;">Palladium Mall</p>
            </div>
        </div>
    </div>
</body>
</html>
