<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1E293B;
        }

        .header {
            background: #1D3461;
            color: white;
            padding: 16px 20px;
            margin-bottom: 12px;
        }

        .header h1 { font-size: 16px; font-weight: bold; margin-bottom: 2px; }
        .header p  { font-size: 9px; opacity: 0.75; }

        .meta {
            display: flex;
            gap: 8px;
            padding-bottom: 12px;
        }

        .meta-box {
            flex: 1;
            background: #F8FAFF;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .meta-box .label { font-size: 8px; color: #64748B; margin-bottom: 2px; }
        .meta-box .value { font-size: 11px; font-weight: bold; color: #1D3461; }

        /* Summary boxes */
        .summary {
            display: flex;
            gap: 6px;
            margin-bottom: 12px;
        }

        .s-box {
            flex: 1;
            padding: 8px 10px;
            border-radius: 6px;
        }

        .s-box.total-due   { background: #FEE2E2; }
        .s-box.total-paid  { background: #D1FAE5; }
        .s-box.outstanding { background: #DBEAFE; }
        .s-box.rent        { background: #EEF2FF; }
        .s-box.utilities   { background: #FEF3C7; }
        .s-box.fines       { background: #FCE7F3; }

        .s-box .s-label { font-size: 7.5px; color: #64748B; }
        .s-box .s-value { font-size: 12px; font-weight: bold; margin-top: 2px; }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        thead tr { background: #1D3461; color: white; }

        thead th {
            padding: 7px 7px;
            text-align: left;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        tbody tr:nth-child(even) { background: #F8FAFF; }

        tbody td {
            padding: 5px 7px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 8.5px;
        }

        tfoot tr { background: #E8F0FE; font-weight: bold; }

        tfoot td {
            padding: 6px 7px;
            font-size: 9px;
            border-top: 2px solid #1D3461;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: bold;
        }

        .badge-paid    { background: #D1FAE5; color: #059669; }
        .badge-unpaid  { background: #FEE2E2; color: #DC2626; }
        .badge-partial { background: #FEF3C7; color: #DC7609; }
        .badge-pending { background: #DBEAFE; color: #1D4ED8; }
        .badge-self    { background: #EDE9FE; color: #6D28D9; }

        .type-rent        { background: #DBEAFE; color: #1D4ED8; }
        .type-fine        { background: #FEE2E2; color: #DC2626; }
        .type-electricity { background: #FEF3C7; color: #B45309; }
        .type-water       { background: #CFFAFE; color: #0E7490; }
        .type-gas         { background: #FFEDD5; color: #C2410C; }
        .type-maintenance { background: #EDE9FE; color: #7C3AED; }
        .type-other       { background: #F1F5F9; color: #475569; }

        .footer {
            margin-top: 14px;
            font-size: 7.5px;
            color: #94A3B8;
            text-align: center;
            border-top: 1px solid #E2E8F0;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h1>Palladium Mall Management System</h1>
        <p>
            {{ $label }} Report &bull;
            Period: {{ $period }} &bull;
            Generated: {{ now()->format('d M Y, H:i') }}
        </p>
    </div>

    {{-- Meta info row --}}
    <div class="meta">
        <div class="meta-box">
            <div class="label">Report Type</div>
            <div class="value">{{ $label }}</div>
        </div>
        <div class="meta-box">
            <div class="label">Period</div>
            <div class="value">{{ $period }}</div>
        </div>
        @if(!empty($filters['unit_id']))
            <div class="meta-box">
                <div class="label">Flat/Shop Filter</div>
                <div class="value">Applied</div>
            </div>
        @endif
        @if(!empty($filters['tenant_id']))
            <div class="meta-box">
                <div class="label">Tenant Filter</div>
                <div class="value">Applied</div>
            </div>
        @endif
        @if(!empty($filters['status']))
            <div class="meta-box">
                <div class="label">Status</div>
                <div class="value">{{ ucfirst($filters['status']) }}</div>
            </div>
        @endif
        @if(!empty($filters['unit_status']))
            <div class="meta-box">
                <div class="label">Unit Status</div>
                <div class="value">{{ ucfirst($filters['unit_status']) }}</div>
            </div>
        @endif
        @if(!empty($filters['owner_type']))
            <div class="meta-box">
                <div class="label">Owner Type</div>
                <div class="value">{{ $filters['owner_type'] === 'pm_mall' ? 'PM Mall Owners' : 'Other Owners' }}</div>
            </div>
        @endif
        <div class="meta-box">
            <div class="label">Total Records</div>
            <div class="value">{{ number_format($summary['count']) }}</div>
        </div>
    </div>

    {{-- Summary boxes --}}
    @if($reportType === 'potential_revenue')
        <div class="summary">
            <div class="s-box" style="background:#F3F4F6; border: 1px solid #E5E7EB;">
                <div class="s-label">Total Flats/Shops</div>
                <div class="s-value" style="color:#374151;">{{ number_format($summary['count']) }}</div>
            </div>
            <div class="s-box" style="background:#ECFDF5; border: 1px solid #A7F3D0;">
                <div class="s-label">Rented Units</div>
                <div class="s-value" style="color:#059669;">{{ number_format($summary['rented_count']) }}</div>
            </div>
            <div class="s-box" style="background:#FFF7ED; border: 1px solid #FFEDD5;">
                <div class="s-label">Vacant/Other Units</div>
                <div class="s-value" style="color:#EA580C;">{{ number_format($summary['vacant_count']) }}</div>
            </div>
            <div class="s-box" style="background:#EFF6FF; border: 1px solid #BFDBFE;">
                <div class="s-label">Potential Rent</div>
                <div class="s-value" style="color:#2563EB;">Rs. {{ number_format($summary['total_rent'], 2) }}</div>
            </div>
            <div class="s-box" style="background:#F5F3FF; border: 1px solid #DDD6FE;">
                <div class="s-label">Potential Maintenance</div>
                <div class="s-value" style="color:#7C3AED;">Rs. {{ number_format($summary['total_maintenance'], 2) }}</div>
            </div>
            <div class="s-box" style="background:#F0FDF4; border: 1px solid #BBF7D0;">
                <div class="s-label">Combined Potential Monthly Revenue</div>
                <div class="s-value" style="color:#16A34A;">Rs. {{ number_format($summary['total_combined'], 2) }}</div>
            </div>
        </div>
    @elseif($reportType === 'monthly_matrix')
        <div class="summary">
            <div class="s-box total-due">
                <div class="s-label">Total Amount Due</div>
                <div class="s-value" style="color:#DC2626;">Rs. {{ number_format($summary['total_amount'], 2) }}</div>
            </div>
            <div class="s-box total-paid">
                <div class="s-label">Total Received</div>
                <div class="s-value" style="color:#059669;">Rs. {{ number_format($summary['total_received'], 2) }}</div>
            </div>
            <div class="s-box outstanding">
                <div class="s-label">Total Pending</div>
                <div class="s-value" style="color:#1A56DB;">Rs. {{ number_format($summary['total_pending'], 2) }}</div>
            </div>
            <div class="s-box rent">
                <div class="s-label">🏠 Rent Due</div>
                <div class="s-value" style="color:#3730A3;">Rs. {{ number_format($summary['total_rent'], 2) }}</div>
            </div>
            <div class="s-box utilities">
                <div class="s-label">🛠️ Services Due</div>
                <div class="s-value" style="color:#92400E;">Rs. {{ number_format($summary['total_serv'], 2) }}</div>
            </div>
            <div class="s-box fines">
                <div class="s-label">⚠️ Extra Charges Due</div>
                <div class="s-value" style="color:#9D174D;">Rs. {{ number_format($summary['total_extra'], 2) }}</div>
            </div>
        </div>

        {{-- Payment Accounts Summary boxes --}}
        @if(!empty($summary['accounts_total']))
            <div style="font-size: 8px; font-weight: bold; color: #64748B; margin-bottom: 4px; text-transform: uppercase;">Received in Payment Accounts</div>
            <div class="summary" style="margin-bottom: 12px;">
                @foreach($summary['accounts_total'] as $accName => $accPaid)
                    @if($accPaid > 0)
                        <div class="s-box" style="background: #ECFDF5; border: 1px solid #A7F3D0;">
                            <div class="s-label" style="color:#065F46;">💰 {{ $accName }}</div>
                            <div class="s-value" style="color:#047857; font-size: 11px;">Rs. {{ number_format($accPaid, 2) }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    @else
        <div class="summary">
            <div class="s-box total-due">
                <div class="s-label">Total Due</div>
                <div class="s-value" style="color:#DC2626;">Rs. {{ number_format($summary['total_due'], 2) }}</div>
            </div>
            <div class="s-box total-paid">
                <div class="s-label">Total Collected</div>
                <div class="s-value" style="color:#059669;">Rs. {{ number_format($summary['total_paid'], 2) }}</div>
            </div>
            <div class="s-box outstanding">
                <div class="s-label">Outstanding</div>
                <div class="s-value" style="color:#1A56DB;">Rs. {{ number_format($summary['outstanding'], 2) }}</div>
            </div>
            @if($reportType === 'rent')
                <div class="s-box rent">
                    <div class="s-label">🏠 Rent Collected</div>
                    <div class="s-value" style="color:#3730A3;">Rs. {{ number_format($summary['rent_collected'], 2) }}</div>
                </div>
            @elseif($reportType === 'maintinance' || $reportType === 'maintenance')
                <div class="s-box rent" style="background: #F5F3FF;">
                    <div class="s-label" style="color:#7C3AED;">🛠️ Maintenance Collected</div>
                    <div class="s-value" style="color:#7C3AED;">Rs. {{ number_format($summary['maintenance_collected'], 2) }}</div>
                </div>
            @elseif($reportType === 'utilities')
                <div class="s-box utilities">
                    <div class="s-label">⚡ Utilities Paid</div>
                    <div class="s-value" style="color:#92400E;">Rs. {{ number_format($summary['utilities_paid'], 2) }}</div>
                </div>
            @elseif($reportType === 'fines')
                <div class="s-box fines">
                    <div class="s-label">⚠️ Fines Collected</div>
                    <div class="s-value" style="color:#9D174D;">Rs. {{ number_format($summary['fines_collected'], 2) }}</div>
                </div>
            @elseif($reportType === 'other_owned' || $reportType === 'occupied' || $reportType === 'occupide' || $reportType === 'non_occupied' || $reportType === 'non_occupide')
                <div class="s-box rent" style="background: #F5F3FF;">
                    <div class="s-label" style="color:#7C3AED;">
                        @if($reportType === 'occupied' || $reportType === 'occupide')
                            👥 Occupied (Ext) Collected
                        @elseif($reportType === 'non_occupied' || $reportType === 'non_occupide')
                            🚪 Vacant (Ext) Collected
                        @else
                            🔑 Other Owned Collected
                        @endif
                    </div>
                    <div class="s-value" style="color:#7C3AED;">Rs. {{ number_format($summary['maintenance_collected'], 2) }}</div>
                </div>
            @else
                <div class="s-box rent">
                    <div class="s-label">🏠 Rent Collected</div>
                    <div class="s-value" style="color:#3730A3;">Rs. {{ number_format($summary['rent_collected'], 2) }}</div>
                </div>
                <div class="s-box rent" style="background: #F5F3FF;">
                    <div class="s-label" style="color:#7C3AED;">🛠️ Maintenance Collected</div>
                    <div class="s-value" style="color:#7C3AED;">Rs. {{ number_format($summary['maintenance_collected'], 2) }}</div>
                </div>
                <div class="s-box utilities">
                    <div class="s-label">⚡ Utilities Paid</div>
                    <div class="s-value" style="color:#92400E;">Rs. {{ number_format($summary['utilities_paid'], 2) }}</div>
                </div>
            @endif
        </div>

        {{-- Payment Accounts Summary boxes --}}
        @if(!empty($summary['accounts_summary']))
            <div style="font-size: 8px; font-weight: bold; color: #64748B; margin-bottom: 4px; text-transform: uppercase;">Collected in Payment Accounts</div>
            <div class="summary" style="margin-bottom: 12px;">
                @foreach($summary['accounts_summary'] as $accName => $accPaid)
                    <div class="s-box" style="background: #ECFDF5; border: 1px solid #A7F3D0;">
                        <div class="s-label" style="color:#065F46;">💰 {{ $accName }}</div>
                        <div class="s-value" style="color:#047857; font-size: 11px;">Rs. {{ number_format($accPaid, 2) }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- Data Table --}}
    @if($reportType === 'monthly_matrix')
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
                    <th>#</th>
                    <th>Created Date</th>
                    <th>Voucher #</th>
                    <th>Flat/Shop</th>
                    <th>Type</th>
                    <th>Landlord</th>
                    <th>Tenant</th>
                    <th>Security Deposit</th>
                    <th>Amount Due</th>
                    <th>Amount Paid</th>
                    <th>Balance</th>
                    <th>Payment Status</th>
                    <th>Paid At</th>
                    <th>Payment Account</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $i => $entry)
                    <tr style="{{ $entry['status'] === 'pending' ? 'background-color: #F0F9FF; color: #64748B; font-style: italic;' : '' }}">
                        <td style="color:#94A3B8;">{{ $i + 1 }}</td>
                        <td>{{ $entry['created_date'] instanceof \Carbon\Carbon ? $entry['created_date']->format('d M Y') : '—' }}</td>
                        <td style="font-weight:600;">{{ $entry['voucher_number'] }}</td>
                        <td style="font-weight:600;">{{ $entry['unit'] ?? '—' }}</td>
                        <td>
                            <span class="badge type-{{ $entry['type'] ?? 'other' }}">
                                {{ ucfirst($entry['type'] ?? '') }}
                            </span>
                        </td>
                        <td>{{ $entry['landlord'] ?? '—' }}</td>
                        <td>{{ $entry['tenant'] ?? '—' }}</td>
                        <td style="font-weight:600;">{{ $entry['security_deposit'] > 0 ? ('Rs. ' . number_format($entry['security_deposit'], 2)) : '—' }}</td>
                        <td style="font-weight:600;">Rs. {{ number_format($entry['amount_due'], 2) }}</td>
                        <td style="color:#059669;font-weight:600;">Rs. {{ number_format($entry['amount_paid'], 2) }}</td>
                        <td style="font-weight:700;color:{{ $entry['balance'] > 0 ? '#DC2626' : '#059669' }};">
                            Rs. {{ number_format($entry['balance'], 2) }}
                        </td>
                        <td>
                            <span class="badge badge-{{ $entry['status'] ?? 'unpaid' }}">
                                {{ ucfirst($entry['status'] ?? '') }}
                            </span>
                            @if(!empty($entry['is_self']))
                                <br>
                                <span class="badge badge-self" style="margin-top: 3px;">
                                    Other-Owned
                                </span>
                            @endif
                        </td>
                        <td>{{ $entry['paid_at'] instanceof \Carbon\Carbon ? $entry['paid_at']->format('d M Y') : '—' }}</td>
                        <td>{{ $entry['payment_account'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" style="text-align:center;padding:16px;color:#94A3B8;">
                            No records found for the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8" style="color:#1D3461;">
                        Totals — {{ number_format($summary['count']) }} records
                    </td>
                    <td style="color:#1D3461;">Rs. {{ number_format($summary['total_due'], 2) }}</td>
                    <td style="color:#059669;">Rs. {{ number_format($summary['total_paid'], 2) }}</td>
                    <td style="color:{{ $summary['outstanding'] > 0 ? '#DC2626' : '#059669' }};">
                        Rs. {{ number_format($summary['outstanding'], 2) }}
                    </td>
                    <td colspan="3"></td>
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

