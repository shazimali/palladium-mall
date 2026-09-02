<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, ui-sans-serif, system-ui, -apple-system, sans-serif;
            font-size: 12px;
            color: #0F172A;
            line-height: 1.4;
            padding: 16px 24px;
        }

        .header {
            border-bottom: 2px solid #0F172A;
            padding: 10px 0 12px 0;
            margin-bottom: 14px;
        }

        .header h1 { font-size: 20px; font-weight: 900; color: #0F172A; margin-bottom: 4px; text-transform: uppercase; }
        .header p  { font-size: 11px; font-weight: 600; color: #475569; }

        .meta {
            display: flex;
            gap: 10px;
            padding-bottom: 12px;
        }

        .meta-box {
            flex: 1;
            background: #F8FAFC;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .meta-box .label { font-size: 10px; font-weight: 700; color: #64748B; margin-bottom: 2px; text-transform: uppercase; }
        .meta-box .value { font-size: 13px; font-weight: 900; color: #0F172A; }

        /* Summary boxes */
        .summary {
            display: flex;
            gap: 8px;
            margin-bottom: 14px;
        }

        .s-box {
            flex: 1;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #CBD5E1;
        }

        .s-box.total-due   { background: #FEF2F2; border-color: #FCA5A5; }
        .s-box.total-paid  { background: #ECFDF5; border-color: #6EE7B7; }
        .s-box.outstanding { background: #EFF6FF; border-color: #93C5FD; }
        .s-box.rent        { background: #EEF2FF; border-color: #A5B4FC; }
        .s-box.utilities   { background: #FEF3C7; border-color: #FCD34D; }
        .s-box.fines       { background: #FCE7F3; border-color: #F472B6; }

        .s-box .s-label { font-size: 10px; font-weight: 800; color: #475569; text-transform: uppercase; }
        .s-box .s-value { font-size: 15px; font-weight: 900; margin-top: 3px; }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        thead tr { background: #F1F5F9; color: #0F172A; border-bottom: 2px solid #334155; }

        thead th {
            padding: 9px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            color: #0F172A;
            border: 1px solid #CBD5E1;
        }

        tbody tr:nth-child(even) { background: #F8FAFC; }

        tbody td {
            padding: 8px 10px;
            border: 1px solid #E2E8F0;
            font-size: 11px;
            color: #0F172A;
        }

        tfoot tr { background: #F1F5F9; font-weight: 900; }

        tfoot td {
            padding: 9px 10px;
            font-size: 12px;
            font-weight: 900;
            border: 1px solid #94A3B8;
            border-top: 2px solid #0F172A;
            color: #0F172A;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 800;
        }

        .badge-paid    { background: #D1FAE5; color: #047857; }
        .badge-unpaid  { background: #FEE2E2; color: #B91C1C; }
        .badge-partial { background: #FEF3C7; color: #B45309; }
        .badge-pending { background: #DBEAFE; color: #1E40AF; }
        .badge-self    { background: #EDE9FE; color: #5B21B6; }

        .type-rent        { background: #DBEAFE; color: #1E40AF; }
        .type-fine        { background: #FEE2E2; color: #B91C1C; }
        .type-electricity { background: #FEF3C7; color: #B45309; }
        .type-water       { background: #CFFAFE; color: #0E7490; }
        .type-gas         { background: #FFEDD5; color: #C2410C; }
        .type-maintenance { background: #EDE9FE; color: #5B21B6; }
        .type-other       { background: #F1F5F9; color: #334155; }

        .footer {
            margin-top: 18px;
            font-size: 10px;
            font-weight: 600;
            color: #64748B;
            text-align: center;
            border-top: 1px solid #CBD5E1;
            padding-top: 10px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 8mm;
            }
            body {
                background: white !important;
                color: black !important;
                font-size: 12px !important;
            }
            .header {
                background: transparent !important;
                border-bottom: 2px solid #000 !important;
                padding-bottom: 8px !important;
            }
            .header h1 {
                color: black !important;
                font-size: 22px !important;
                font-weight: 900 !important;
            }
            .header p {
                color: #222 !important;
                font-size: 11px !important;
            }
            thead th {
                background: #f1f5f9 !important;
                color: black !important;
                font-size: 11px !important;
                font-weight: 900 !important;
                border: 1px solid #94a3b8 !important;
            }
            tbody td {
                font-size: 11px !important;
                color: black !important;
                border: 1px solid #cbd5e1 !important;
            }
            tfoot td {
                font-size: 12px !important;
                font-weight: 900 !important;
                border: 1px solid #64748b !important;
                border-top: 2px solid #000 !important;
                color: black !important;
            }
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header" style="text-align: center; border-bottom: 2px solid #0F172A; padding-bottom: 10px; margin-bottom: 14px;">
        <h1 style="font-size: 20px; font-weight: 900; color: #0F172A; margin-bottom: 4px; text-transform: uppercase; text-align: center;">
            Palladium Mall Management System
        </h1>
        <p style="font-size: 11px; font-weight: 600; color: #475569; text-align: center;">
            {{ $label }} Report &bull; Period: {{ $period }} &bull; Generated: {{ now()->format('d M Y, H:i') }}
        </p>
    </div>

    {{-- Clean Inline Metadata & Summary Section --}}
    <div class="inline-summary-container" style="padding: 0 12px; margin-bottom: 16px; font-size: 11px; color: #1E293B; line-height: 1.6;">
        {{-- Line 1: Meta filters --}}
        <div style="margin-bottom: 6px;">
            <span style="font-weight: bold; color: #64748B;">Report Type:</span> <span style="font-weight: 800; color: #0F172A;">{{ $label }}</span>
            <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
            <span style="font-weight: bold; color: #64748B;">Period:</span> <span style="font-weight: 800; color: #0F172A;">{{ $period }}</span>
            <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
            <span style="font-weight: bold; color: #64748B;">Total Records:</span> <span style="font-weight: 800; color: #0F172A;">{{ number_format($summary['count']) }}</span>
            @if(!empty($filters['unit_id']))
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Flat/Shop:</span> <span style="font-weight: 800; color: #0F172A;">Filtered</span>
            @endif
            @if(!empty($filters['tenant_id']))
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Tenant:</span> <span style="font-weight: 800; color: #0F172A;">Filtered</span>
            @endif
            @if(!empty($filters['status']))
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Status:</span> <span style="font-weight: 800; color: #0F172A;">{{ ucfirst($filters['status']) }}</span>
            @endif
            @if(!empty($filters['unit_status']))
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Unit Status:</span> <span style="font-weight: 800; color: #0F172A;">{{ ucfirst($filters['unit_status']) }}</span>
            @endif
            @if(!empty($filters['owner_type']))
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Owner Type:</span> <span style="font-weight: 800; color: #0F172A;">{{ $filters['owner_type'] === 'pm_mall' ? 'PM Mall Owners' : 'Other Owners' }}</span>
            @endif
        </div>

        {{-- Line 2: Financial Summary Totals --}}
        <div style="margin-bottom: 6px;">
            @if($reportType === 'potential_revenue')
                <span style="font-weight: bold; color: #64748B;">Rented Units:</span> <span style="font-weight: 800; color: #059669;">{{ number_format($summary['rented_count']) }}</span>
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Vacant/Other:</span> <span style="font-weight: 800; color: #EA580C;">{{ number_format($summary['vacant_count']) }}</span>
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Potential Rent:</span> <span style="font-weight: 800; color: #2563EB;">Rs. {{ number_format($summary['total_rent'], 2) }}</span>
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Potential Maintenance:</span> <span style="font-weight: 800; color: #7C3AED;">Rs. {{ number_format($summary['total_maintenance'], 2) }}</span>
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Combined Revenue:</span> <span style="font-weight: 800; color: #16A34A;">Rs. {{ number_format($summary['total_combined'], 2) }}</span>
            @elseif(in_array($reportType, ['monthly_matrix', 'monthly_matrix_expected']))
                <span style="font-weight: bold; color: #64748B;">Total Due:</span> <span style="font-weight: 800; color: #DC2626;">Rs. {{ number_format($summary['total_amount'], 2) }}</span>
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Total Received:</span> <span style="font-weight: 800; color: #059669;">Rs. {{ number_format($summary['total_received'], 2) }}</span>
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Total Pending:</span> <span style="font-weight: 800; color: #1A56DB;">Rs. {{ number_format($summary['total_pending'], 2) }}</span>
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">🏠 Rent Due:</span> <span style="font-weight: 800; color: #3730A3;">Rs. {{ number_format($summary['total_rent'], 2) }}</span>
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">🛠️ Services Due:</span> <span style="font-weight: 800; color: #92400E;">Rs. {{ number_format($summary['total_serv'], 2) }}</span>
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">⚠️ Extra Charges:</span> <span style="font-weight: 800; color: #9D174D;">Rs. {{ number_format($summary['total_extra'], 2) }}</span>
            @else
                <span style="font-weight: bold; color: #64748B;">Total Due:</span> <span style="font-weight: 800; color: #DC2626;">Rs. {{ number_format($summary['total_due'], 2) }}</span>
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Total Collected:</span> <span style="font-weight: 800; color: #059669;">Rs. {{ number_format($summary['total_paid'], 2) }}</span>
                <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                <span style="font-weight: bold; color: #64748B;">Outstanding:</span> <span style="font-weight: 800; color: #1A56DB;">Rs. {{ number_format($summary['outstanding'], 2) }}</span>
                @if($reportType === 'rent')
                    <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                    <span style="font-weight: bold; color: #64748B;">🏠 Rent Collected:</span> <span style="font-weight: 800; color: #3730A3;">Rs. {{ number_format($summary['rent_collected'], 2) }}</span>
                @elseif($reportType === 'maintinance' || $reportType === 'maintenance')
                    <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                    <span style="font-weight: bold; color: #64748B;">🛠️ Maintenance Collected:</span> <span style="font-weight: 800; color: #7C3AED;">Rs. {{ number_format($summary['maintenance_collected'], 2) }}</span>
                @elseif($reportType === 'utilities')
                    <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                    <span style="font-weight: bold; color: #64748B;">⚡ Utilities Paid:</span> <span style="font-weight: 800; color: #92400E;">Rs. {{ number_format($summary['utilities_paid'], 2) }}</span>
                @elseif($reportType === 'fines')
                    <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                    <span style="font-weight: bold; color: #64748B;">⚠️ Fines Collected:</span> <span style="font-weight: 800; color: #9D174D;">Rs. {{ number_format($summary['fines_collected'], 2) }}</span>
                @elseif($reportType === 'other_owned' || $reportType === 'occupied' || $reportType === 'occupide' || $reportType === 'non_occupied' || $reportType === 'non_occupide')
                    <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                    <span style="font-weight: bold; color: #64748B;">Collected:</span> <span style="font-weight: 800; color: #7C3AED;">Rs. {{ number_format($summary['maintenance_collected'], 2) }}</span>
                @else
                    <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                    <span style="font-weight: bold; color: #64748B;">🏠 Rent Collected:</span> <span style="font-weight: 800; color: #3730A3;">Rs. {{ number_format($summary['rent_collected'], 2) }}</span>
                    <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                    <span style="font-weight: bold; color: #64748B;">🛠️ Maintenance:</span> <span style="font-weight: 800; color: #7C3AED;">Rs. {{ number_format($summary['maintenance_collected'], 2) }}</span>
                @endif
            @endif
        </div>

        {{-- Line 3: Payment Accounts Summary --}}
        @php
            $accSummaryList = [];
            if (in_array($reportType, ['monthly_matrix', 'monthly_matrix_expected'])) {
                if (!empty($summary['accounts_total'])) {
                    foreach ($summary['accounts_total'] as $accName => $accPaid) {
                        if ($accPaid > 0) {
                            $accSummaryList[$accName] = $accPaid;
                        }
                    }
                }
            } else {
                if (!empty($summary['accounts_summary'])) {
                    $accSummaryList = $summary['accounts_summary'];
                }
            }
        @endphp

        @if(!empty($accSummaryList))
            <div>
                <span style="font-weight: bold; color: #64748B;">Collected in Payment Accounts:</span>
                @foreach($accSummaryList as $accName => $accPaid)
                    <span style="margin-left: 6px; font-weight: 800; color: #047857;">💰 {{ $accName }}:</span>
                    <span style="font-weight: 800; color: #0F172A;">Rs. {{ number_format($accPaid, 2) }}</span>
                    @if(!$loop->last)
                        <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    {{-- Data Table --}}
    @if(in_array($reportType, ['monthly_matrix', 'monthly_matrix_expected']))
        <table>
            <thead>
                <tr>
                    <th style="text-align:center;">SR</th>
                    <th>Flat No</th>
                    <th>Owner</th>
                    <th>Tenant</th>
                    <th>Status</th>
                    <th>Serv</th>
                    <th>Extra</th>
                    <th>Sec. Dep</th>
                    <th>Expected Total</th>
                    <th>Rent</th>
                    <th>Total Amount</th>
                    <th>Received</th>
                    @foreach($paymentAccounts as $account)
                        <th>{{ $account->name }}</th>
                    @endforeach
                    <th>Accounts Total</th>
                    <th>Prev. Unpaid</th>
                    <th>Pending</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    @php
                        $isVacant = $entry['status'] === 'VACANT';
                        $isPending = $entry['pending'] > 0;
                        $expectedTotal = ($entry['prev_unpaid'] ?? 0) + ($entry['rent'] ?? 0) + ($entry['serv'] ?? 0) + ($entry['extra'] ?? 0) + ($entry['security_deposit'] ?? 0);
                        $accountsTotal = array_sum($entry['payment_accounts'] ?? []);
                    @endphp
                    <tr style="{{ $isVacant ? 'background-color: #F8FAFF; color: #94A3B8; font-style: italic;' : '' }}">
                        <td style="text-align:center;">{{ $entry['sr'] }}</td>
                        <td style="font-weight:600;">{{ $entry['flat_no'] }}</td>
                        <td>{{ $entry['owner'] }}</td>
                        <td>{{ $entry['tenant'] }}</td>
                        <td>
                            <span class="badge {{ $entry['status'] === 'RENTED' || $entry['status'] === 'OCCUPIED' ? 'badge-paid' : ($entry['status'] === 'VACANT' ? 'badge-unpaid' : 'badge-pending') }}">
                                {{ $entry['status'] }}
                            </span>
                            @if(!empty($entry['is_self']))
                                <br>
                                <span class="badge badge-self" style="margin-top: 3px;">
                                    Other-Owned
                                </span>
                            @endif
                        </td>
                        <td>Rs. {{ number_format($entry['serv'], 2) }}</td>
                        <td>Rs. {{ number_format($entry['extra'], 2) }}</td>
                        <td>Rs. {{ number_format($entry['security_deposit'], 2) }}</td>
                        <td style="font-weight:600;color:#3730A3;">Rs. {{ number_format($expectedTotal, 2) }}</td>
                        <td>Rs. {{ number_format($entry['rent'], 2) }}</td>
                        <td style="font-weight:600;">Rs. {{ number_format($entry['total_amount'], 2) }}</td>
                        <td style="color:#059669;font-weight:600;">Rs. {{ number_format($entry['received'], 2) }}</td>
                        @foreach($paymentAccounts as $account)
                            <td>
                                @if(($entry['payment_accounts'][$account->name] ?? 0) > 0)
                                    Rs. {{ number_format($entry['payment_accounts'][$account->name], 2) }}
                                @else
                                    —
                                @endif
                            </td>
                        @endforeach
                        <td style="color:#059669;font-weight:600;">Rs. {{ number_format($accountsTotal, 2) }}</td>
                        <td>Rs. {{ number_format($entry['prev_unpaid'], 2) }}</td>
                        <td style="font-weight:700;color:{{ $isPending ? '#DC2626' : '#059669' }};">
                            Rs. {{ number_format($entry['pending'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 16 + count($paymentAccounts) }}" style="text-align:center;padding:16px;color:#94A3B8;">
                            No records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="color:#1D3461;text-align:right;">Totals</td>
                    <td style="color:#1D3461;">Rs. {{ number_format($summary['total_serv'], 2) }}</td>
                    <td style="color:#1D3461;">Rs. {{ number_format($summary['total_extra'], 2) }}</td>
                    <td style="color:#1D3461;">Rs. {{ number_format($summary['total_security_deposit'] ?? 0, 2) }}</td>
                    <td style="color:#1D3461;font-weight:bold;">Rs. {{ number_format(($summary['total_prev_unpaid'] ?? 0) + ($summary['total_rent'] ?? 0) + ($summary['total_serv'] ?? 0) + ($summary['total_extra'] ?? 0) + ($summary['total_security_deposit'] ?? 0), 2) }}</td>
                    <td style="color:#1D3461;">Rs. {{ number_format($summary['total_rent'], 2) }}</td>
                    <td style="color:#1D3461;">Rs. {{ number_format($summary['total_amount'], 2) }}</td>
                    <td style="color:#059669;">Rs. {{ number_format($summary['total_received'], 2) }}</td>
                    @foreach($paymentAccounts as $account)
                        <td style="color:#1D3461;">Rs. {{ number_format($summary['accounts_total'][$account->name] ?? 0, 2) }}</td>
                    @endforeach
                    <td style="color:#059669;font-weight:bold;">Rs. {{ number_format(array_sum($summary['accounts_total'] ?? []), 2) }}</td>
                    <td style="color:#1D3461;">Rs. {{ number_format($summary['total_prev_unpaid'] ?? 0, 2) }}</td>
                    <td style="color:{{ $summary['total_pending'] > 0 ? '#DC2626' : '#059669' }};">
                        Rs. {{ number_format($summary['total_pending'], 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @elseif($reportType === 'security_deposit_matrix')
        <table>
            <thead>
                <tr>
                    <th style="text-align:center;">SR</th>
                    <th>Flat / Shop</th>
                    <th>Owner</th>
                    <th>Tenant</th>
                    <th>Status</th>
                    <th style="text-align:right;">Required Deposit</th>
                    <th style="text-align:right;">Collected Deposit</th>
                    <th style="text-align:right;">Pending Deposit</th>
                    <th style="text-align:right;">Deductions / Damage</th>
                    <th style="text-align:right;">Net Refundable</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    @php
                        $isVacant = $entry['status'] === 'VACANT';
                        $isPending = $entry['pending_deposit'] > 0;
                    @endphp
                    <tr style="{{ $isVacant ? 'background-color: #F8FAFF; color: #94A3B8; font-style: italic;' : '' }}">
                        <td style="text-align:center;">{{ $entry['sr'] }}</td>
                        <td style="font-weight:600;">{{ $entry['flat_no'] }}</td>
                        <td>{{ $entry['owner'] }}</td>
                        <td>{{ $entry['tenant'] }}</td>
                        <td>
                            <span class="badge {{ $entry['status'] === 'RENTED' || $entry['status'] === 'OCCUPIED' ? 'badge-paid' : ($entry['status'] === 'VACANT' ? 'badge-unpaid' : 'badge-pending') }}">
                                {{ $entry['status'] }}
                            </span>
                        </td>
                        <td style="text-align:right;font-weight:600;color:#3730A3;">Rs. {{ number_format($entry['required_deposit'], 2) }}</td>
                        <td style="text-align:right;font-weight:600;color:#059669;">Rs. {{ number_format($entry['collected_deposit'], 2) }}</td>
                        <td style="text-align:right;font-weight:700;color:{{ $isPending ? '#DC2626' : '#94A3B8' }};">Rs. {{ number_format($entry['pending_deposit'], 2) }}</td>
                        <td style="text-align:right;font-weight:600;color:#D97706;">Rs. {{ number_format($entry['deduction_deposit'], 2) }}</td>
                        <td style="text-align:right;font-weight:700;color:#7C3AED;">Rs. {{ number_format($entry['net_refundable'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center;padding:16px;color:#94A3B8;">
                            No records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="color:#1D3461;text-align:right;font-weight:bold;">Totals</td>
                    <td style="text-align:right;color:#3730A3;font-weight:bold;">Rs. {{ number_format($summary['total_required'] ?? 0, 2) }}</td>
                    <td style="text-align:right;color:#059669;font-weight:bold;">Rs. {{ number_format($summary['total_collected'] ?? 0, 2) }}</td>
                    <td style="text-align:right;color:#DC2626;font-weight:bold;">Rs. {{ number_format($summary['total_pending'] ?? 0, 2) }}</td>
                    <td style="text-align:right;color:#D97706;font-weight:bold;">Rs. {{ number_format($summary['total_deductions'] ?? 0, 2) }}</td>
                    <td style="text-align:right;color:#7C3AED;font-weight:bold;">Rs. {{ number_format($summary['total_net_refundable'] ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @elseif($reportType === 'potential_revenue')
        <table>
            <thead>
                <tr>
                    <th style="text-align:center;">SR</th>
                    <th>Flat/Shop</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Owner</th>
                    <th>Rent Source</th>
                    <th style="text-align:right;">Monthly Rent</th>
                    <th style="text-align:right;">Maintenance</th>
                    <th style="text-align:right;">Total Potential</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $i => $entry)
                    <tr>
                        <td style="text-align:center;color:#94A3B8;">{{ $i + 1 }}</td>
                        <td style="font-weight:600;">{{ $entry['unit_number'] }}</td>
                        <td style="text-transform:capitalize;">{{ $entry['type'] }}</td>
                        <td>
                            <span class="badge badge-{{ $entry['status'] === 'rented' ? 'paid' : ($entry['status'] === 'vacant' ? 'unpaid' : 'pending') }}">
                                {{ ucfirst($entry['status']) }}
                            </span>
                        </td>
                        <td>{{ $entry['landlord'] ?? '—' }}</td>
                        <td>{{ $entry['source'] }}</td>
                        <td style="text-align:right;font-weight:600;">Rs. {{ number_format($entry['rent'], 2) }}</td>
                        <td style="text-align:right;font-weight:600;">Rs. {{ number_format($entry['maintenance'], 2) }}</td>
                        <td style="text-align:right;font-weight:700;color:#0D9488;">Rs. {{ number_format($entry['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:16px;color:#94A3B8;">
                            No records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="font-weight:bold;">
                    <td colspan="6" style="color:#1D3461;">
                        Totals — {{ number_format($summary['count']) }} records
                    </td>
                    <td style="color:#1D3461;text-align:right;">Rs. {{ number_format($summary['total_rent'], 2) }}</td>
                    <td style="color:#1D3461;text-align:right;">Rs. {{ number_format($summary['total_maintenance'], 2) }}</td>
                    <td style="color:#0D9488;text-align:right;">Rs. {{ number_format($summary['total_combined'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th style="text-align:center;">Sr #</th>
                    <th>Flat/Shop</th>
                    <th>Tenant</th>
                    <th style="text-align:right;">Amount Due</th>
                    <th style="text-align:right;">Amount Paid</th>
                    <th>Payment Method</th>
                    <th>Payment Account</th>
                    <th>Paid At</th>
                    <th style="text-align:right;">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $i => $entry)
                    <tr>
                        <td style="text-align:center;color:#94A3B8;">{{ $i + 1 }}</td>
                        <td style="font-weight:bold;">{{ $entry['unit'] ?? '—' }}</td>
                        <td>{{ $entry['tenant'] ?? '—' }}</td>
                        <td style="text-align:right;font-weight:600;">{{ number_format($entry['amount_due'], 2) }}</td>
                        <td style="text-align:right;color:#059669;font-weight:600;">{{ number_format($entry['amount_paid'], 2) }}</td>
                        <td>{{ $entry['payment_method'] ?? '—' }}</td>
                        <td>{{ $entry['payment_account'] ?? '—' }}</td>
                        <td>{{ $entry['paid_at'] instanceof \Carbon\Carbon ? $entry['paid_at']->format('d M Y') : '—' }}</td>
                        <td style="text-align:right;font-weight:700;color:{{ $entry['balance'] > 0 ? '#DC2626' : '#059669' }};">
                            {{ number_format($entry['balance'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:16px;color:#94A3B8;">
                            No records found for the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="font-weight:bold;">
                    <td colspan="3" style="color:#1D3461;">
                        Totals — {{ number_format($summary['count']) }} records
                    </td>
                    <td style="text-align:right;color:#1D3461;">{{ number_format($summary['total_due'], 2) }}</td>
                    <td style="text-align:right;color:#059669;">{{ number_format($summary['total_paid'], 2) }}</td>
                    <td colspan="3"></td>
                    <td style="text-align:right;color:{{ $summary['outstanding'] > 0 ? '#DC2626' : '#059669' }};">
                        {{ number_format($summary['outstanding'], 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="footer">
        Palladium Mall Management System &bull; {{ $label }} Report &bull;
        Printed on {{ now()->format('d M Y, H:i') }}
    </div>

    @if(!empty($isPrint))
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () { window.print(); }, 400);
            });
        </script>
    @endif
</body>
</html>

