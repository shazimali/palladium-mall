<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} — Palladium Mall</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, ui-sans-serif, system-ui, -apple-system, sans-serif;
            font-size: 11px;
            color: #0F172A;
            line-height: 1.4;
            padding: 16px 24px;
            background: #fff;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #0F172A;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: 900;
            color: #0F172A;
            margin-bottom: 4px;
            text-transform: uppercase;
            text-align: center;
        }

        .header p {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            text-align: center;
        }

        .inline-summary-container {
            padding: 0 12px;
            margin-bottom: 14px;
            font-size: 11px;
            color: #1E293B;
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        thead tr {
            background: #F1F5F9;
            color: #0F172A;
            border-bottom: 2px solid #334155;
        }

        thead th {
            padding: 8px 10px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
            border: 1px solid #CBD5E1;
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid #E2E8F0;
        }

        tbody tr:nth-child(even) {
            background: #F8FAFC;
        }

        tbody td {
            padding: 7px 10px;
            font-size: 11px;
            vertical-align: middle;
            border: 1px solid #CBD5E1;
        }

        .unit-badge {
            font-family: monospace;
            font-weight: 900;
            font-size: 12px;
            background: #EFF6FF;
            color: #1D4ED8;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #BFDBFE;
            display: inline-block;
        }

        .status-badge-ok {
            display: inline-block;
            font-weight: 800;
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 4px;
            background: #DCFCE7;
            color: #15803D;
            border: 1px solid #86EFAC;
        }

        .status-badge-pending {
            display: inline-block;
            font-weight: 800;
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 4px;
            background: #FEE2E2;
            color: #B91C1C;
            border: 1px solid #FCA5A5;
        }

        .footer {
            margin-top: 20px;
            border-top: 1px solid #CBD5E1;
            padding-top: 10px;
            font-size: 10px;
            color: #64748B;
            text-align: center;
        }

        .no-print {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0F172A;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
        }

        .print-btn:hover {
            background: #000;
        }

        .close-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            color: #334155;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
        }

        .close-btn:hover {
            background: #F8FAFC;
        }

        /* @page MUST be top-level — nesting inside @media print breaks @bottom-right in all browsers */
        @page {
            size: landscape;
            margin: 1.2cm 0.8cm 1.5cm 0.8cm;

            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 0.75rem;
                font-weight: 800;
                color: #475569;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                font-size: 11px !important;
                padding: 0 !important;
            }
            .header {
                background: transparent !important;
                border-bottom: 2px solid #000 !important;
                padding-bottom: 8px !important;
            }
            .header h1 {
                color: black !important;
                font-size: 20px !important;
                font-weight: 900 !important;
            }
            .header p {
                color: #222 !important;
                font-size: 10px !important;
            }
            thead th {
                background: #F1F5F9 !important;
                color: black !important;
                font-size: 10px !important;
                font-weight: 900 !important;
                border: 1px solid #94A3B8 !important;
            }
            tbody td {
                font-size: 10px !important;
                color: black !important;
                border: 1px solid #CBD5E1 !important;
            }

            /* position:fixed repeats on every printed page — cross-browser fallback */
            .print-page-number {
                display: block !important;
                position: fixed;
                bottom: 6px;
                right: 10px;
                font-size: 0.72rem;
                font-weight: 800;
                color: #475569;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
        <button class="close-btn" onclick="window.close()">Close</button>
    </div>

    {{-- Centered Header --}}
    <div class="header">
        <h1>Palladium Mall Management System</h1>
        <p>
            {{ $pageTitle }} &bull; Generated: {{ now()->format('d M Y, H:i') }}
        </p>
    </div>

    {{-- Inline Summary Section --}}
    <div class="inline-summary-container">
        <div>
            <span style="font-weight: bold; color: #64748B;">Report:</span> <span style="font-weight: 800; color: #0F172A;">Pending Documents & Checklists Check</span>
            <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
            <span style="font-weight: bold; color: #64748B;">Total Active Tenants:</span> <span style="font-weight: 800; color: #0F172A;">{{ number_format(count($tenants)) }}</span>
            @if(request()->filled('search'))
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Search Filter:</span> <span style="font-weight: 800; color: #0F172A;">"{{ request('search') }}"</span>
            @endif
            <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
            <span style="font-weight: bold; color: #64748B;">Printed On:</span> <span style="font-weight: 800; color: #0F172A;">{{ now()->format('d M Y, H:i') }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 40px; text-align: center;">#</th>
                <th style="width: 100px;">Flat / Shop</th>
                <th>Tenant Name</th>
                <th style="width: 110px;">Phone</th>
                <th style="width: 160px;">Agreement Period</th>
                <th style="width: 80px; text-align: center;">Status</th>
                <th>Docs Checklist</th>
                <th>Govt Document</th>
                <th>Move-In Checklist</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenants as $index => $tenant)
                @php
                    $agreement = $tenant->activeAgreement;
                    $checklist = $agreement?->documentChecklist;
                    $isCommercial = $agreement?->unit?->type === 'commercial';
                    
                    $totalDocs = $isCommercial ? 23 : 21;
                    $checkedDocs = $checklist ? $checklist->countChecked() : 0;
                    
                    $docsChecklistComplete = $checklist ? $checklist->allDocumentsUploaded() : false;
                    $govtDocumentUploaded = !empty($agreement?->govt_document);
                    $moveInChecklistCompleted = $agreement?->moveInChecklist ? $agreement->moveInChecklist->isComplete() : false;
                    
                    $overallComplete = $docsChecklistComplete && $govtDocumentUploaded && $moveInChecklistCompleted;
                    $missingCount = $totalDocs - $checkedDocs;
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #475569;">{{ $index + 1 }}</td>
                    <td>
                        @if($tenant->unit)
                            <span class="unit-badge">{{ $tenant->unit->unit_number }} ({{ ucfirst($tenant->unit->type) }})</span>
                        @else
                            <span style="color: #94A3B8;">—</span>
                        @endif
                    </td>
                    <td style="font-weight: 900;">{{ $tenant->name }}</td>
                    <td style="font-family: monospace; font-weight: 700;">{{ $tenant->phone }}</td>
                    <td style="font-size: 10px;">
                        @if($agreement && $agreement->start_date && $agreement->end_date)
                            {{ $agreement->start_date->format('d M Y') }} – {{ $agreement->end_date->format('d M Y') }}
                        @else
                            <span style="color: #94A3B8;">—</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($overallComplete)
                            <span class="status-badge-ok">COMPLETE</span>
                        @else
                            <span class="status-badge-pending">PENDING</span>
                        @endif
                    </td>
                    <td>
                        @if($docsChecklistComplete)
                            <span style="color: #15803D; font-weight: 800;">✓ {{ $checkedDocs }}/{{ $totalDocs }} Complete</span>
                        @else
                            <span style="color: #B91C1C; font-weight: 800;">✗ {{ $checkedDocs }}/{{ $totalDocs }} ({{ $missingCount }} missing)</span>
                        @endif
                    </td>
                    <td>
                        @if($govtDocumentUploaded)
                            <span style="color: #15803D; font-weight: 800;">✓ Uploaded</span>
                        @else
                            <span style="color: #B91C1C; font-weight: 800;">✗ Missing</span>
                        @endif
                    </td>
                    <td>
                        @if($moveInChecklistCompleted)
                            <span style="color: #15803D; font-weight: 800;">✓ Complete</span>
                        @elseif($agreement?->moveInChecklist)
                            <span style="color: #B91C1C; font-weight: 800;">✗ Incomplete ({{ $agreement->moveInChecklist->countChecked() }}/{{ $agreement->moveInChecklist->countTotal() }})</span>
                        @else
                            <span style="color: #B91C1C; font-weight: 800;">✗ Missing</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 30px; font-size: 12px; font-weight: 800; color: #64748B;">
                        No active tenants found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Palladium Mall Management Office &bull; {{ $pageTitle }} &bull; Printed on {{ now()->format('d M Y, H:i') }}
    </div>

    <!-- Fixed page-number footer: repeats on every printed page (cross-browser fallback) -->
    <div class="print-page-number" style="display:none;"></div>
</body>
</html>
