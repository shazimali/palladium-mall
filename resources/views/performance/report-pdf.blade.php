<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Worker Daily Working Sheet & Performance — {{ $employee->name }} — {{ $monthName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 8px; color: #111; background: #fff; padding: 15px; }

        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .header-table td { vertical-align: middle; }
        .title { font-size: 14px; font-weight: bold; color: #0f172a; }
        .subtitle { font-size: 9px; color: #64748b; margin-top: 2px; }

        .yellow-box { background: #fff200; border: 2px solid #eab308; padding: 6px 12px; text-align: center; border-radius: 4px; display: inline-block; }
        .yellow-box .lbl { font-size: 7px; font-weight: bold; text-transform: uppercase; color: #713f12; }
        .yellow-box .val { font-size: 14px; font-weight: bold; color: #000; font-family: monospace; }

        .green-box { background: #dcfce7; border: 1.5px solid #22c55e; padding: 6px 12px; text-align: center; border-radius: 4px; display: inline-block; margin-left: 8px; }
        .green-box .lbl { font-size: 7px; font-weight: bold; text-transform: uppercase; color: #15803d; }
        .green-box .val { font-size: 14px; font-weight: bold; color: #15803d; font-family: monospace; }

        .matrix-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 7.5px; }
        .matrix-table th, .matrix-table td { border: 1px solid #cbd5e1; padding: 3px 2px; text-align: center; }
        .matrix-table th { background: #f1f5f9; font-weight: bold; color: #334155; }
        .matrix-table .th-fri { background: #fce5cd !important; color: #7c2d12 !important; font-weight: bold; }
        .matrix-table .td-fri { background: #fff7ed; }
        .matrix-table .task-name { text-align: left; padding-left: 5px; font-weight: bold; font-family: sans-serif; white-space: nowrap; max-width: 130px; overflow: hidden; }

        .matrix-table .cell-done { background: #dcfce7; color: #166534; font-weight: bold; }
        .matrix-table .cell-unsatisfied { background: #fee2e2; color: #dc2626; font-weight: bold; text-decoration: line-through; }
        .matrix-table .cell-undone { background: #f8fafc; color: #94a3b8; }
        .matrix-table .cell-na { background: #1e293b; color: transparent; }

        .matrix-table tfoot td { font-weight: bold; background: #f1f5f9; border-top: 2px solid #94a3b8; }

        .summary-section { width: 100%; margin-top: 15px; border-collapse: collapse; }
        .summary-section td { vertical-align: top; }

        .salary-table { width: 100%; border-collapse: collapse; font-size: 8px; }
        .salary-table th, .salary-table td { border: 1px solid #e2e8f0; padding: 4px 8px; }
        .salary-table th { background: #f8fafc; text-align: left; }
        .salary-table .final-row { background: #0f172a; color: #fff; font-weight: bold; font-size: 9px; }

        .footer { margin-top: 15px; font-size: 7.5px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="title">{{ $employee->name }} — Worker Daily Working Sheet</div>
                <div class="subtitle">{{ $profile->designation ?? 'Employee' }} &bull; {{ $monthName }} &bull; Performance: {{ $gridSheet['summary']['performance_percentage'] }}% ({{ $gridSheet['summary']['grade'] }})</div>
            </td>
            <td style="width: 50%; text-align: right;">
                <div class="yellow-box">
                    <div class="lbl">Total Monthly Max</div>
                    <div class="val">{{ number_format($gridSheet['summary']['total_monthly_max'], 0) }}</div>
                </div>
                <div class="green-box">
                    <div class="lbl">Total Earned (Payable)</div>
                    <div class="val">Rs. {{ number_format($gridSheet['summary']['total_earned'], 0) }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Spreadsheet Matrix --}}
    <table class="matrix-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">SN</th>
                <th rowspan="2" style="width: 140px; text-align: left; padding-left: 5px;">Days & Works</th>
                <th rowspan="2" style="width: 45px;">Monthly Amt</th>
                @foreach($gridSheet['days'] as $dayMeta)
                    <th class="{{ $dayMeta['is_friday'] ? 'th-fri' : '' }}">{{ $dayMeta['day_name'] }}</th>
                @endforeach
                <th rowspan="2" style="width: 45px;">Earned</th>
                <th rowspan="2" style="width: 40px;">Deduct</th>
            </tr>
            <tr>
                @foreach($gridSheet['days'] as $dayMeta)
                    <th class="{{ $dayMeta['is_friday'] ? 'th-fri' : '' }}">{{ $dayMeta['day'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($gridSheet['rows'] as $idx => $row)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="task-name">{{ $row['name'] }}</td>
                    <td style="font-weight: bold; background: #f8fafc;">{{ number_format($row['monthly_amount'], 0) }}</td>

                    @foreach($gridSheet['days'] as $d => $dayMeta)
                        @php
                            $cell = $row['days_data'][$d];
                            $status = $cell['status'];
                            $earned = $cell['earned'];
                        @endphp
                        <td class="@if($status === 'done') cell-done @elseif($status === 'unsatisfied') cell-unsatisfied @elseif($status === 'undone') cell-undone @elseif(!$row['is_daily'] && $status === 'na') cell-na @else {{ $dayMeta['is_friday'] ? 'td-fri' : '' }} @endif">
                            @if($status === 'done')
                                {{ round($earned) }}
                            @elseif($status === 'unsatisfied')
                                {{ round($row['unit_amount']) }}
                            @elseif($status === 'undone')
                                {{ round($row['unit_amount']) }}
                            @elseif(!$row['is_daily'] && $status === 'na')
                                &nbsp;
                            @else
                                -
                            @endif
                        </td>
                    @endforeach

                    <td style="font-weight: bold; color: #166534; background: #f0fdf4;">{{ number_format($row['total_earned'], 0) }}</td>
                    <td style="font-weight: bold; color: #dc2626; background: #fef2f2;">
                        {{ $row['total_deducted'] > 0 ? '-' . number_format($row['total_deducted'], 0) : '0' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align: left; padding-left: 5px;">Daily Earned Totals</td>
                <td style="background: #fef08a;">{{ number_format($gridSheet['summary']['total_monthly_max'], 0) }}</td>
                @foreach($gridSheet['days'] as $d => $dayMeta)
                    <td class="{{ $dayMeta['is_friday'] ? 'th-fri' : '' }}">{{ round($gridSheet['dailyTotals'][$d]) }}</td>
                @endforeach
                <td style="background: #bbf7d0; color: #166534;">{{ number_format($gridSheet['summary']['total_earned'], 0) }}</td>
                <td style="background: #fecaca; color: #dc2626;">-{{ number_format($gridSheet['summary']['total_deducted'], 0) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Bottom Summary Table --}}
    @if($report)
    <table class="summary-section">
        <tr>
            <td style="width: 50%; padding-right: 10px;">
                <div style="font-weight: bold; margin-bottom: 4px; text-transform: uppercase; font-size: 8px;">Attendance & Performance</div>
                <table class="salary-table">
                    <tr><td>Total Days in Month</td><td style="text-align:right; font-weight:bold;">{{ $report->working_days }}</td></tr>
                    <tr><td>Days Present</td><td style="text-align:right; font-weight:bold; color: #166534;">{{ $report->days_present }}</td></tr>
                    <tr><td>Days Absent</td><td style="text-align:right; font-weight:bold; color: #dc2626;">{{ $report->days_absent }}</td></tr>
                    <tr><td>Performance Score</td><td style="text-align:right; font-weight:bold;">{{ $report->performance_percentage }}% ({{ $report->grade }})</td></tr>
                </table>
            </td>
            <td style="width: 50%; padding-left: 10px;">
                <div style="font-weight: bold; margin-bottom: 4px; text-transform: uppercase; font-size: 8px;">Salary Calculation</div>
                <table class="salary-table">
                    <tr><td>Basic Salary</td><td style="text-align:right;">Rs. {{ number_format($report->basic_salary, 0) }}</td></tr>
                    <tr><td>Allowances (Fuel + Attendance)</td><td style="text-align:right;">Rs. {{ number_format($report->fuel_allowance + $report->attendance_incentive, 0) }}</td></tr>
                    <tr><td>Task Performance Earnings</td><td style="text-align:right; font-weight:bold; color:#166534;">+ Rs. {{ number_format($report->other_works_amount, 0) }}</td></tr>
                    <tr class="final-row"><td>Final Payable Salary</td><td style="text-align:right;">Rs. {{ number_format($report->final_salary, 0) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
    @endif

    <div class="footer">
        Generated by Palladium Mall System &bull; {{ now()->format('d M Y, h:i A') }}
    </div>

</body>
</html>
