<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Admin Office Reports Summary - PALLADIUM MALL</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 0.5cm;
            }
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
                font-weight: bold !important;
                zoom: 0.85;
            }
            .max-w-6xl, .max-w-7xl {
                max-width: 100% !important;
                padding: 5px !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 antialiased min-h-screen p-4 sm:p-8">

    <!-- ACTION BUTTONS (HIDDEN DURING PRINT) -->
    <div class="max-w-7xl w-full mx-auto mb-4 flex justify-end gap-3 no-print">
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-blue-700 transition-all cursor-pointer">
            🖨️ Print Summary Report
        </button>
        <button onclick="window.close()"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all shadow-2xs cursor-pointer">
            Close Window
        </button>
    </div>

    <!-- PRINTABLE CONTAINER -->
    <div class="max-w-7xl w-full mx-auto printable-container rounded-2xl bg-white p-6 sm:p-8 border border-gray-300 shadow-xl text-gray-900 font-sans">

        <!-- COMPANY BRANDING HEADER -->
        <div class="text-center mb-6 pb-4 border-b-2 border-gray-400">
            <h1 class="text-2xl sm:text-3xl font-black tracking-wider text-gray-900 uppercase mb-0.5">
                PALLADIUM MALL
            </h1>
            <p class="text-xs text-gray-600 uppercase tracking-widest font-bold">
                Management Office • Administration & Inspection Reports Summary
            </p>
            <h2 class="text-lg font-extrabold text-blue-900 mt-2 uppercase tracking-wide">
                {{ $isSingleReport ? $activeReportName . ' Summary' : 'All Reports Comprehensive Summary' }}
            </h2>
            <div class="mt-1 flex items-center justify-center gap-4 text-xs text-gray-700 font-semibold">
                <span>Period: <strong>{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}</strong> to <strong>{{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</strong> ({{ $totalDays }} Days)</span>
                <span>•</span>
                <span>Total Reports: <strong>{{ $isSingleReport ? $totalReports : $totalSubmissions }}</strong></span>
                <span>•</span>
                <span>Generated On: <strong>{{ now()->format('d M Y, h:i A') }}</strong></span>
            </div>
        </div>

        <!-- SUMMARY METRICS ROW -->
        <div class="grid grid-cols-4 gap-3 mb-6">
            <div class="p-3 border border-gray-300 rounded-xl bg-gray-50 text-center">
                <span class="text-[10px] font-bold text-gray-500 uppercase">Total Submissions</span>
                <div class="text-xl font-black text-gray-900">{{ $isSingleReport ? $totalReports : $totalSubmissions }}</div>
            </div>
            <div class="p-3 border border-gray-300 rounded-xl bg-gray-50 text-center">
                <span class="text-[10px] font-bold text-emerald-700 uppercase">Passed Checks</span>
                <div class="text-xl font-black text-emerald-700">{{ $isSingleReport ? $totalPassItems : $totalPass }}</div>
            </div>
            <div class="p-3 border border-gray-300 rounded-xl bg-gray-50 text-center">
                <span class="text-[10px] font-bold text-rose-700 uppercase">Issues / Non-Compliant</span>
                <div class="text-xl font-black text-rose-700">{{ $isSingleReport ? $totalFailItems : $totalFail }}</div>
            </div>
            <div class="p-3 border border-gray-300 rounded-xl bg-gray-50 text-center">
                <span class="text-[10px] font-bold text-amber-700 uppercase">Avg Admin Rating</span>
                <div class="text-xl font-black text-amber-800">
                    @if($isSingleReport)
                        {{ $avgRating !== null ? $avgRating . ' / 5.0' : 'N/A' }}
                    @else
                        {{ $overallAvgRating !== null ? $overallAvgRating . ' / 5.0' : 'N/A' }}
                    @endif
                </div>
            </div>
        </div>

        @if($isSingleReport)
            
            <!-- SINGLE REPORT DAY-WISE SECTIONS -->
            <div class="space-y-5">
                @php $hasRows = false; @endphp
                @foreach($dayWiseGroups as $day)
                    @if(!empty($day['reports']))
                        @php $hasRows = true; @endphp
                        <div class="border border-gray-300 rounded-xl overflow-hidden">
                            <div class="px-4 py-2 bg-gray-100 border-b border-gray-300 flex justify-between items-center text-xs font-bold">
                                <span>📅 {{ $day['carbon']->format('d M Y') }} ({{ $day['carbon']->format('l') }}) — {{ $day['count'] }} Record(s)</span>
                                <span class="text-emerald-800">✅ {{ $day['pass_items'] }} Passed @if($day['fail_items'] > 0) • ⚠️ {{ $day['fail_items'] }} Issues @endif</span>
                            </div>
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-800 border-b border-gray-300 font-extrabold uppercase text-[10px]">
                                        <th class="py-1.5 px-3">Time</th>
                                        <th class="py-1.5 px-3">Reporter / Inspector</th>
                                        <th class="py-1.5 px-3">{{ $activeReportKey === 'flat_inspection' ? 'Flat / Tenant' : 'Member / Staff' }}</th>
                                        <th class="py-1.5 px-3 text-center">Passed</th>
                                        <th class="py-1.5 px-3 text-center">Issues</th>
                                        <th class="py-1.5 px-3 text-center">Rating</th>
                                        <th class="py-1.5 px-3">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 text-gray-900">
                                    @foreach($day['reports'] as $rep)
                                        <tr>
                                            <td class="py-1.5 px-3 font-mono font-bold">{{ $rep['time'] }}</td>
                                            <td class="py-1.5 px-3 font-bold">{{ $rep['reported_by'] }}</td>
                                            <td class="py-1.5 px-3 font-bold">{{ $rep['member_or_unit'] }}</td>
                                            <td class="py-1.5 px-3 text-center font-bold text-emerald-800">{{ $rep['pass_count'] }}</td>
                                            <td class="py-1.5 px-3 text-center font-bold {{ $rep['fail_count'] > 0 ? 'text-rose-800' : 'text-gray-400' }}">{{ $rep['fail_count'] }}</td>
                                            <td class="py-1.5 px-3 text-center font-bold">{{ $rep['admin_rating'] ? $rep['admin_rating'] . '/5' : '—' }}</td>
                                            <td class="py-1.5 px-3 text-xs">{{ $rep['overall_remarks'] ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endforeach

                @if(!$hasRows)
                    <div class="py-8 text-center text-gray-500 font-bold border border-gray-300 rounded-xl">
                        No inspection reports found in this date range.
                    </div>
                @endif
            </div>

        @else

            <!-- ALL REPORTS SECTION-WISE TABLES -->
            <div class="space-y-5">
                @if(($groupBy ?? 'day') === 'day')
                    
                    @php $hasRows = false; @endphp
                    @foreach($daySections as $day)
                        @if(!empty($day['reports']))
                            @php $hasRows = true; @endphp
                            <div class="border border-gray-300 rounded-xl overflow-hidden">
                                <div class="px-4 py-2 bg-gray-100 border-b border-gray-300 flex justify-between items-center text-xs font-bold">
                                    <span>📅 {{ $day['carbon']->format('d M Y') }} ({{ $day['carbon']->format('l') }}) — {{ $day['count'] }} Report(s) Filed</span>
                                    <span class="text-emerald-800">✅ {{ $day['pass_count'] }} Passed @if($day['fail_count'] > 0) • ⚠️ {{ $day['fail_count'] }} Issues @endif</span>
                                </div>
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-800 border-b border-gray-300 font-extrabold uppercase text-[10px]">
                                            <th class="py-1.5 px-3">Time</th>
                                            <th class="py-1.5 px-3">Module</th>
                                            <th class="py-1.5 px-3">Reporter</th>
                                            <th class="py-1.5 px-3">Member / Unit</th>
                                            <th class="py-1.5 px-3 text-center">Passed</th>
                                            <th class="py-1.5 px-3 text-center">Issues</th>
                                            <th class="py-1.5 px-3 text-center">Rating</th>
                                            <th class="py-1.5 px-3">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 text-gray-900">
                                        @foreach($day['reports'] as $item)
                                            <tr>
                                                <td class="py-1.5 px-3 font-mono font-bold">{{ $item['time'] }}</td>
                                                <td class="py-1.5 px-3 font-black text-blue-900">{{ $item['report_name'] }}</td>
                                                <td class="py-1.5 px-3 font-bold">{{ $item['reported_by'] }}</td>
                                                <td class="py-1.5 px-3">{{ $item['member_or_unit'] }}</td>
                                                <td class="py-1.5 px-3 text-center font-bold text-emerald-800">{{ $item['pass_count'] }}</td>
                                                <td class="py-1.5 px-3 text-center font-bold {{ $item['fail_count'] > 0 ? 'text-rose-800' : 'text-gray-400' }}">{{ $item['fail_count'] }}</td>
                                                <td class="py-1.5 px-3 text-center font-bold">{{ $item['admin_rating'] ? $item['admin_rating'] . '/5' : '—' }}</td>
                                                <td class="py-1.5 px-3 text-xs">{{ $item['overall_remarks'] ?: '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endforeach

                    @if(!$hasRows)
                        <div class="py-8 text-center text-gray-500 font-bold border border-gray-300 rounded-xl">
                            No inspection reports found in this date range.
                        </div>
                    @endif

                @else

                    @php $hasRows = false; @endphp
                    @foreach($reportSections as $section)
                        @if(!empty($section['reports']))
                            @php $hasRows = true; @endphp
                            <div class="border border-gray-300 rounded-xl overflow-hidden">
                                <div class="px-4 py-2 bg-gray-100 border-b border-gray-300 flex justify-between items-center text-xs font-bold">
                                    <span>📋 {{ $section['name'] }} ({{ $section['count'] }} Reports)</span>
                                    <span class="text-emerald-800">✅ {{ $section['pass_count'] }} Passed @if($section['fail_count'] > 0) • ⚠️ {{ $section['fail_count'] }} Issues @endif</span>
                                </div>
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 text-gray-800 border-b border-gray-300 font-extrabold uppercase text-[10px]">
                                            <th class="py-1.5 px-3">Date & Day</th>
                                            <th class="py-1.5 px-3">Time</th>
                                            <th class="py-1.5 px-3">Reporter</th>
                                            <th class="py-1.5 px-3">{{ $section['key'] === 'flat_inspection' ? 'Flat / Tenant' : 'Member / Staff' }}</th>
                                            <th class="py-1.5 px-3 text-center">Passed</th>
                                            <th class="py-1.5 px-3 text-center">Issues</th>
                                            <th class="py-1.5 px-3 text-center">Rating</th>
                                            <th class="py-1.5 px-3">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 text-gray-900">
                                        @foreach($section['reports'] as $item)
                                            <tr>
                                                <td class="py-1.5 px-3 font-bold whitespace-nowrap">{{ \Carbon\Carbon::parse($item['date'])->format('d M Y') }} ({{ $item['day_name'] }})</td>
                                                <td class="py-1.5 px-3 font-mono font-bold">{{ $item['time'] }}</td>
                                                <td class="py-1.5 px-3 font-bold">{{ $item['reported_by'] }}</td>
                                                <td class="py-1.5 px-3">{{ $item['member_or_unit'] }}</td>
                                                <td class="py-1.5 px-3 text-center font-bold text-emerald-800">{{ $item['pass_count'] }}</td>
                                                <td class="py-1.5 px-3 text-center font-bold {{ $item['fail_count'] > 0 ? 'text-rose-800' : 'text-gray-400' }}">{{ $item['fail_count'] }}</td>
                                                <td class="py-1.5 px-3 text-center font-bold">{{ $item['admin_rating'] ? $item['admin_rating'] . '/5' : '—' }}</td>
                                                <td class="py-1.5 px-3 text-xs">{{ $item['overall_remarks'] ?: '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endforeach

                @endif
            </div>

        @endif

        <!-- FOOTER SIGNATURES & TIME STAMP -->
        <div class="mt-8 pt-6 border-t border-gray-300 flex justify-between items-end text-xs text-gray-600">
            <div>
                <p>Printed On: <span class="font-bold text-gray-900">{{ now()->format('d M Y, h:i A') }}</span></p>
                <p>Generated By: <span class="font-bold text-gray-900">{{ auth()->user()->name ?? 'System' }}</span></p>
            </div>
            <div class="text-center">
                <div class="w-48 border-b border-gray-400 mb-1"></div>
                <p class="font-bold text-gray-800">Authorized Admin Officer</p>
            </div>
        </div>

    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => { window.print(); }, 400);
        });
    </script>
</body>
</html>
