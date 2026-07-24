<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1E293B; line-height: 1.4; padding: 20px; }
        
        .header { margin-bottom: 15px; border-bottom: 2px solid #1D3461; padding-bottom: 10px; }
        .header h1 { font-size: 16px; color: #1D3461; font-weight: bold; margin-bottom: 4px; }
        .header p { font-size: 8.5px; color: #64748B; margin-top: 2px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #94A3B8; }
        table.data-table th { background: #1D3461; color: white; padding: 7px 8px; font-size: 8px; text-transform: uppercase; font-weight: bold; text-align: left; border: 1px solid #475569; }
        table.data-table td { padding: 6px 8px; border: 1px solid #CBD5E1; font-size: 8px; vertical-align: middle; }
        table.data-table tr:nth-child(even) td { background: #F9FBFF; }
        
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 7.5px; font-weight: bold; text-transform: uppercase; }
        .badge-rented { background-color: #DCFCE7; color: #15803D; }
        .badge-vacant { background-color: #FEF9C3; color: #A16207; }
        .badge-self { background-color: #F3E8FF; color: #6B21A8; }
        
        .text-right { text-align: right; }
        .font-mono { font-family: Courier, monospace; }
        .footer { position: fixed; bottom: 15px; left: 20px; right: 20px; border-top: 1px solid #E2E8F0; padding-top: 6px; text-align: center; font-size: 7.5px; color: #94A3B8; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Palladium Mall — {{ $title }}</h1>
        <p>Generated on: {{ now()->format('d M Y h:i A') }} &bull; Total Units: {{ $units->count() }}</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 14%;">Flat No.</th>
                <th style="width: 22%;">Owner</th>
                <th style="width: 16%;">Contact Number</th>
                <th style="width: 11%;">Floor</th>
                <th style="width: 11%;">Block</th>
                <th style="width: 11%;">Area / Zone</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($units as $index => $unit)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $unit->unit_number }}</strong></td>
                    <td>{{ $unit->landlord->name ?? '—' }}</td>
                    <td>{{ $unit->landlord->phone ?? '—' }}</td>
                    <td>{{ $unit->floor->name ?? '—' }}</td>
                    <td>{{ $unit->block->name ?? '—' }}</td>
                    <td>{{ $unit->area->name ?? '—' }}</td>
                    <td>
                        @if($unit->status === 'rented' || ($unit->is_self && $unit->otherTenant))
                            <span class="badge badge-rented">Rented</span>
                        @elseif($unit->status === 'vacant')
                            <span class="badge badge-vacant">Vacant</span>
                        @else
                            <span class="badge badge-self">{{ ucfirst($unit->status ?? 'Other') }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 15px; color: #94A3B8;">No units found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Palladium Mall Management System &bull; Confidential
    </div>

</body>
</html>
