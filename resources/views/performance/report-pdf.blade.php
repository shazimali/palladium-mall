<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Performance Report — {{ $employee->name }} — {{ $monthName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; }

        .header { background: #0f172a; color: #fff; padding: 22px 30px; }
        .header .title { font-size: 18px; font-weight: bold; letter-spacing: 0.5px; }
        .header .subtitle { font-size: 11px; color: #94a3b8; margin-top: 3px; }

        .meta { display: flex; justify-content: space-between; align-items: flex-start; padding: 18px 30px; background: #f8fafc; border-bottom: 2px solid #e2e8f0; }
        .meta-left .name { font-size: 15px; font-weight: bold; }
        .meta-left .detail { font-size: 10px; color: #64748b; margin-top: 3px; }
        .meta-right { text-align: right; }
        .grade-badge { display: inline-block; padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .grade-Excellent { background: #dcfce7; color: #15803d; }
        .grade-Good { background: #dbeafe; color: #1d4ed8; }
        .grade-Average { background: #fef9c3; color: #a16207; }
        .grade-Poor { background: #fee2e2; color: #dc2626; }

        .stats-row { display: flex; gap: 0; border-bottom: 1px solid #e2e8f0; }
        .stat-box { flex: 1; padding: 12px 20px; border-right: 1px solid #e2e8f0; }
        .stat-box:last-child { border-right: none; }
        .stat-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; }
        .stat-value { font-size: 16px; font-weight: bold; color: #1e293b; margin-top: 2px; }
        .stat-sub { font-size: 9px; color: #94a3b8; margin-top: 1px; }

        .section { padding: 20px 30px; }
        .section-title { font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; text-align: left; padding: 8px 10px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; border-bottom: 1px solid #e2e8f0; }
        td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 10px; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        .tfoot td { background: #f8fafc; font-weight: bold; border-top: 1.5px solid #e2e8f0; }

        .progress-bar { height: 5px; background: #e2e8f0; border-radius: 3px; width: 60px; display: inline-block; vertical-align: middle; margin-right: 5px; }
        .progress-fill { height: 100%; border-radius: 3px; }
        .green { background: #22c55e; }
        .blue { background: #3b82f6; }
        .amber { background: #f59e0b; }
        .red { background: #ef4444; }

        .salary-box { max-width: 340px; margin: 0 auto; }
        .salary-row { display: flex; justify-content: space-between; padding: 9px 16px; border-bottom: 1px solid #f1f5f9; }
        .salary-row.divider { border-top: 2px solid #e2e8f0; border-bottom: 2px solid #e2e8f0; background: #f8fafc; font-weight: bold; }
        .salary-row.final { background: #eff6ff; font-size: 13px; font-weight: bold; }
        .salary-label { color: #475569; }
        .salary-value { font-weight: 600; color: #1e293b; }
        .salary-value.accent { color: #2563eb; }

        .footer { margin-top: 30px; padding: 14px 30px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 9px; color: #94a3b8; }
        .sign-line { border-top: 1px solid #cbd5e1; width: 150px; margin: 40px auto 5px; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="title">Employee Performance Report</div>
        <div class="subtitle">{{ $monthName }} &bull; Generated {{ now()->format('d M Y') }}</div>
    </div>

    {{-- Employee Meta --}}
    <div class="meta">
        <div class="meta-left">
            <div class="name">{{ $employee->name }}</div>
            <div class="detail">{{ $profile->designation ?? '' }} {{ $profile->department ? '&bull; ' . $profile->department : '' }}</div>
            @if($profile->employee_code)
                <div class="detail">Code: {{ $profile->employee_code }}</div>
            @endif
        </div>
        <div class="meta-right">
            @if($report)
                <div class="grade-badge grade-{{ $report->grade }}">{{ $report->grade }}</div>
                <div style="font-size:18px;font-weight:bold;margin-top:6px;color:#1e293b;">{{ $report->performance_percentage }}%</div>
                <div style="font-size:9px;color:#94a3b8;">Performance Score</div>
            @endif
        </div>
    </div>

    {{-- Stats Row --}}
    @if($report)
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-label">Points Earned</div>
            <div class="stat-value">{{ number_format($report->total_earned_points, 0) }}</div>
            <div class="stat-sub">of {{ number_format($report->total_max_points, 0) }} max</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Days Present</div>
            <div class="stat-value">{{ $report->days_present }}</div>
            <div class="stat-sub">of {{ $report->working_days }} working days</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Days Absent</div>
            <div class="stat-value">{{ $report->days_absent }}</div>
            <div class="stat-sub">This month</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Final Salary</div>
            <div class="stat-value">Rs. {{ number_format($report->final_salary, 0) }}</div>
            <div class="stat-sub">Take home</div>
        </div>
    </div>
    @endif

    {{-- Task Breakdown --}}
    <div class="section">
        <div class="section-title">Task Performance Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Task</th>
                    <th>Monthly Points</th>
                    <th>Days Done</th>
                    <th>Points Earned</th>
                    <th>Achievement</th>
                </tr>
            </thead>
            <tbody>
                @foreach($taskBreakdown as $i => $row)
                    @php
                        $pct = $row['template']->monthly_points > 0
                            ? round(($row['points_earned'] / $row['template']->monthly_points) * 100, 1)
                            : 0;
                        $bc = $pct >= 90 ? 'green' : ($pct >= 75 ? 'blue' : ($pct >= 60 ? 'amber' : 'red'));
                        $barW = max(0, min(100, $pct));
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row['template']->name }}</td>
                        <td>{{ number_format($row['template']->monthly_points, 0) }}</td>
                        <td>{{ $row['days_done'] }} days</td>
                        <td><strong>{{ number_format($row['points_earned'], 1) }}</strong></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:5px;">
                                <div class="progress-bar"><div class="progress-fill {{ $bc }}" style="width:{{ $barW }}%"></div></div>
                                <span style="font-size:10px;color:#64748b;">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="tfoot">Total</td>
                    <td class="tfoot">{{ number_format(collect($taskBreakdown)->sum(fn($r) => $r['template']->monthly_points), 0) }}</td>
                    <td class="tfoot">—</td>
                    <td class="tfoot">{{ number_format(collect($taskBreakdown)->sum('points_earned'), 1) }}</td>
                    <td class="tfoot"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Salary Calculation --}}
    @if($report)
    <div class="section">
        <div class="section-title">Salary Calculation</div>
        <div class="salary-box">
            <div class="salary-row">
                <span class="salary-label">Basic Salary</span>
                <span class="salary-value">Rs. {{ number_format($report->basic_salary, 0) }}</span>
            </div>
            <div class="salary-row">
                <span class="salary-label">Fuel Allowance</span>
                <span class="salary-value">Rs. {{ number_format($report->fuel_allowance, 0) }}</span>
            </div>
            <div class="salary-row">
                <span class="salary-label">Attendance Incentive</span>
                <span class="salary-value">Rs. {{ number_format($report->attendance_incentive, 0) }}</span>
            </div>
            <div class="salary-row">
                <span class="salary-label">Collection Incentive ({{ $report->collection_incentive_pct }}%)</span>
                <span class="salary-value">Rs. {{ number_format($report->collection_incentive_amt, 0) }}</span>
            </div>
            <div class="salary-row divider">
                <span class="salary-label">Total Basic</span>
                <span class="salary-value">Rs. {{ number_format($report->total_basic, 0) }}</span>
            </div>
            <div class="salary-row">
                <span class="salary-label">Other Works (Task Points)</span>
                <span class="salary-value accent">Rs. {{ number_format($report->other_works_amount, 0) }}</span>
            </div>
            <div class="salary-row final">
                <span class="salary-label">Final Salary</span>
                <span class="salary-value accent">Rs. {{ number_format($report->final_salary, 0) }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Signature --}}
    <div style="padding: 10px 30px 0; text-align:right;">
        <div class="sign-line" style="margin: 30px 0 5px auto;"></div>
        <div style="font-size:10px;color:#64748b;">Authorized Signature</div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        Generated by Palladium Mall Management System &bull; {{ now()->format('d M Y, h:i A') }}
        @if($report && $report->generatedByUser)
            &bull; Report by {{ $report->generatedByUser->name }}
        @endif
    </div>

</body>
</html>
