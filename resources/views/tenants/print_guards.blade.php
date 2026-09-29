<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} — Palladium Mall</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            font-size: 15px;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #000;
            background: #fff;
            padding: 24px 32px;
            line-height: 1.5;
            font-weight: 700;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .logo-text {
            font-size: 1.4rem;
            font-weight: 900;
            color: #0f172a;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title h2 {
            font-size: 1.15rem;
            font-weight: 900;
            color: #0f172a;
        }

        .doc-title p {
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 24px;
        }

        thead tr {
            background: #e2e8f0;
        }

        thead th {
            padding: 10px;
            text-align: left;
            font-weight: 900;
            font-size: 0.82rem;
            text-transform: uppercase;
            color: #0f172a;
            border-bottom: 2px solid #0f172a;
        }

        tbody tr {
            border-bottom: 1px solid #e2e8f0;
            page-break-inside: avoid;
        }

        tbody td {
            padding: 9px 10px;
            color: #000;
            font-weight: 700;
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .mono {
            font-family: monospace;
            font-size: 0.9rem;
            font-weight: 800;
        }

        .unit {
            font-size: 1.05rem;
            font-weight: 900;
            color: #0f172a;
        }

        .name {
            font-weight: 900;
            color: #0f172a;
        }

        .emergency {
            color: #dc2626;
            font-weight: 900;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: 800;
            border-radius: 4px;
            text-transform: uppercase;
            background: #e0e7ff;
            color: #3730a3;
            margin-left: 4px;
        }

        .tenant-img,
        .no-photo {
            width: 48px;
            height: 48px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }

        .tenant-img {
            object-fit: cover;
            display: block;
        }

        .no-photo {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #94a3b8;
            border-style: dashed;
        }

        .block-title {
            font-size: 1.1rem;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .block-count {
            margin-left: 8px;
            font-size: 0.85rem;
            font-weight: 800;
            color: #475569;
            text-transform: none;
        }

        .doc-title .total {
            color: #0f172a;
            font-weight: 900;
        }

        .toolbar {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
        }

        .block-filter {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .block-filter select {
            padding: 4px 8px;
            border: 1px solid #94a3b8;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        tbody td,
        thead th {
            border: 1px solid #94a3b8;
        }

        .contacts div {
            line-height: 1.4;
            white-space: nowrap;
        }

        .footer {
            margin-top: 30px;
            border-top: 2px solid #0f172a;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            font-weight: 800;
            color: #475569;
        }

        .no-print {
            text-align: center;
            margin-bottom: 24px;
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 24px;
            font-size: 0.95rem;
            font-weight: 800;
            cursor: pointer;
        }

        .print-btn:hover {
            background: #000;
        }

        /* @page MUST be top-level — nesting inside @media print breaks @bottom-right in all browsers */
        @page {
            size: A4 portrait;
            margin: 1.5cm 0.5cm 1.8cm 0.5cm;

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
                background-color: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
                font-weight: bold !important;
                zoom: 0.8;
            }

            thead {
                display: table-header-group;
            }

            /* Each block starts on its own page */
            .block-section + .block-section {
                page-break-before: always;
                break-before: page;
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

    <div class="no-print toolbar">
        <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
        <form method="GET" class="block-filter">
            @foreach(request()->except('block_id') as $key => $value)
                @if(!is_array($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <label for="block_id">Block:</label>
            <select name="block_id" id="block_id" onchange="this.form.submit()">
                <option value="">All Blocks</option>
                @foreach($blocks as $block)
                    <option value="{{ $block->id }}" @selected((string) $selectedBlockId === (string) $block->id)>{{ $block->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @forelse($blockGroups as $blockName => $blockOccupants)
        <section class="block-section">
            <div class="header">
                <div>
                    <span class="logo-text">PALLADIUM MALL</span>
                    <div class="block-title">
                        {{ Str::contains(strtolower($blockName), 'block') ? $blockName : $blockName . ' Block' }}
                        <span class="block-count">{{ number_format(count($blockOccupants)) }} {{ Str::plural('Tenant', count($blockOccupants)) }}</span>
                    </div>
                </div>
                <div class="doc-title">
                    <h2>{{ $pageTitle }}</h2>
                    <p>Printed: {{ now()->format('d M Y, h:i A') }}</p>
                    <p class="total">Total {{ $statusLabel }} Occupants: {{ number_format(count($occupants)) }}</p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th class="text-center" style="width: 45px;">Sr #</th>
                        <th class="text-center" style="width: 70px;">Photo</th>
                        <th style="width: 90px;">Flat No</th>
                        <th style="width: 90px;">Floor</th>
                        <th>Tenant Name</th>
                        <th style="width: 190px;">Contact No / ER No</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($blockOccupants->values() as $index => $occupant)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">
                                @if(!empty($occupant['photo_url']))
                                    <img src="{{ $occupant['photo_url'] }}" alt="{{ $occupant['tenant_name'] }}" class="tenant-img">
                                @else
                                    <div class="no-photo">👤</div>
                                @endif
                            </td>
                            <td class="unit">{{ $occupant['unit_number'] }}</td>
                            <td>{{ $occupant['floor'] }}</td>
                            <td class="name">
                                {{ $occupant['tenant_name'] }}
                                @if($occupant['is_other_owned'])
                                    <span class="badge">Other-Owned</span>
                                @endif
                            </td>
                            <td class="mono contacts">
                                @foreach(array_unique(array_filter([$occupant['phone'], $occupant['secondary_phone']], fn($n) => $n !== '—')) as $number)
                                    <div>{{ $number }}</div>
                                @endforeach
                                @if($occupant['emergency_phone'] !== '—')
                                    <div class="emergency">ER: {{ $occupant['emergency_phone'] }}</div>
                                @endif
                                @if($occupant['phone'] === '—' && $occupant['secondary_phone'] === '—' && $occupant['emergency_phone'] === '—')
                                    <div>—</div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @empty
        <div class="header">
            <span class="logo-text">PALLADIUM MALL</span>
            <div class="doc-title">
                <h2>{{ $pageTitle }}</h2>
                <p>Printed: {{ now()->format('d M Y, h:i A') }}</p>
            </div>
        </div>
        <table>
            <tbody>
                <tr>
                    <td style="text-align: center; color: #94a3b8; padding: 40px 0;">
                        No {{ strtolower($statusLabel) }} occupants found matching current filters.
                    </td>
                </tr>
            </tbody>
        </table>
    @endforelse

    <div class="footer">
        <span>Palladium Mall Security &amp; Gate Control</span>
        <span>Generated on {{ now()->format('d M Y \a\t h:i A') }}</span>
    </div>

    <!-- Fixed page-number footer: repeats on every printed page (cross-browser fallback) -->
    <div class="print-page-number" style="display:none;"></div>
</body>

</html>
