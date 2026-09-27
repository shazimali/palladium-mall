    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 15px;
        }

        .header-container {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 16px;
        }
        .header-brand {
            font-size: 16px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0 0 4px 0;
        }
        .header-title {
            font-size: 22px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 6px 0;
        }
        .header-highlight {
            font-size: 28px;
            font-weight: bold;
            color: #1d4ed8;
            margin: 4px 0 10px 0;
        }

        .tags-container {
            text-align: center;
            margin-top: 8px;
        }
        .tag-pill {
            display: inline-block;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #334155;
            padding: 5px 12px;
            border-radius: 14px;
            font-size: 10px;
            font-weight: bold;
            margin: 3px 4px;
            text-transform: uppercase;
        }
        .tag-pill strong {
            color: #0f172a;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }
        .summary-box {
            background: #f8fafc;
            padding: 9px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }
        .summary-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
        }
        .summary-value {
            font-size: 14px;
            font-weight: 900;
            color: #0f172a;
            margin-top: 2px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .data-table th {
            background: #f8fafc;
            color: #475569;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: bold;
            padding: 9px 7px;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }
        .data-table td {
            padding: 8px 7px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }
        .data-table tfoot td {
            padding: 10px 7px;
            font-weight: 900;
            font-size: 12px;
        }
        .text-right, .data-table th.text-right { text-align: right; }
        .text-center, .data-table th.text-center { text-align: center; }
        .nowrap { white-space: nowrap; }
        .font-bold { font-weight: bold; }
        .data-table tr.group-header td {
            background: #e2e8f0;
            color: #0f172a;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-top: 2px solid #94a3b8;
        }
        .data-table tr.group-total td {
            background: #f8fafc;
            font-weight: 900;
            border-bottom: 2px solid #94a3b8;
        }
        .text-green { color: #059669; }
        .text-red { color: #e11d48; }
    </style>
