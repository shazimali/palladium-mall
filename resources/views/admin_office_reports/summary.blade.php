@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Admin Office Reports Summary" />

    <x-common.component-card title="" desc="">
        
        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- STICKY INLINE FILTER BAR (LEDGER STRATEGY) --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <form action="{{ route('admin-office-reports.summary') }}" method="GET" id="report-summary-filter-form"
            class="sticky top-[72px] z-[990] bg-white/95 dark:bg-gray-900/95 p-3.5 sm:p-4 rounded-2xl border-2 border-brand-500 shadow-xl backdrop-blur-md mb-6"
            x-data="{
                reportType: '{{ $selectedReportType ?? '' }}',
                search: '',
                open: false,
                highlightedIndex: -1,
                dateFrom: '{{ $dateFrom }}',
                dateTo: '{{ $dateTo }}',
                groupBy: '{{ $groupBy ?? "day" }}',
                options: [
                    {
                        id: '',
                        name: '📊 All Reports (Section-wise Summary)',
                        badge: 'All Modules',
                        searchLabel: 'all reports overview section summary'
                    },
                    @foreach($reportTypes as $rt)
                    {
                        id: '{{ $rt->key }}',
                        name: '{{ addslashes($rt->name) }}',
                        badge: '{{ $rt->is_daily ? "Daily Report" : "Inspection" }}',
                        searchLabel: '{{ strtolower($rt->name . " " . $rt->key . ($rt->is_daily ? " daily" : "")) }}'
                    },
                    @endforeach
                ],
                get filteredOptions() {
                    if (!this.search) return this.options;
                    let s = this.search.toLowerCase();
                    return this.options.filter(opt => opt.searchLabel.includes(s));
                },
                get selectedReportName() {
                    let selected = this.options.find(opt => opt.id === this.reportType);
                    return selected ? selected.name : '📊 All Reports (Section-wise Summary)';
                },
                selectOption(opt) {
                    this.reportType = opt.id;
                    this.open = false;
                    this.search = '';
                    this.highlightedIndex = -1;
                    this.$nextTick(() => {
                        document.getElementById('report-summary-filter-form').submit();
                    });
                },
                setPreset(from, to) {
                    if (window._fpFrom) {
                        window._fpFrom.setDate(from, true);
                    } else {
                        let fromEl = document.getElementById('date_from');
                        if (fromEl) fromEl.value = from;
                    }
                    if (window._fpTo) {
                        window._fpTo.setDate(to, true);
                    } else {
                        let toEl = document.getElementById('date_to');
                        if (toEl) toEl.value = to;
                    }
                    this.dateFrom = from;
                    this.dateTo = to;
                    setTimeout(() => {
                        let form = document.getElementById('report-summary-filter-form');
                        if (form) form.submit();
                    }, 50);
                },
                setGrouping(val) {
                    this.groupBy = val;
                    document.getElementById('group_by_input').value = val;
                    this.$nextTick(() => {
                        document.getElementById('report-summary-filter-form').submit();
                    });
                },
                clearSelection() {
                    this.reportType = '';
                    this.open = false;
                    this.search = '';
                    this.highlightedIndex = -1;
                }
            }">

            <!-- Hidden input for grouping view -->
            <input type="hidden" name="group_by" id="group_by_input" :value="groupBy">

            <!-- Sticky Inline Filter Controls (Single Row) -->
            <div class="flex flex-wrap xl:flex-nowrap items-end gap-3">
                
                <!-- Report Type Selector Dropdown -->
                <div class="flex-1 min-w-[240px] relative" :class="open ? 'relative z-[99999]' : 'relative'" @click.away="open = false; highlightedIndex = -1">
                    <label class="mb-1 block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Select Report Type <span class="text-brand-500">*</span>
                    </label>
                    
                    {{-- Trigger Button --}}
                    <button type="button" @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                        class="w-full h-11 flex items-center justify-between rounded-xl border-2 border-gray-300 bg-white px-3.5 py-2 text-sm sm:text-base font-bold text-gray-900 text-left focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <span class="flex items-center gap-2 truncate">
                            <span x-text="selectedReportName" class="font-extrabold text-brand-600 dark:text-brand-400 truncate"></span>
                        </span>
                        <svg class="h-4 w-4 text-gray-500 transition-transform duration-200 shrink-0 ml-1.5" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Hidden input --}}
                    <input type="hidden" name="report_type" :value="reportType">

                    {{-- Dropdown Container --}}
                    <div x-show="open" x-transition x-cloak
                        class="absolute left-0 right-0 z-[99999] mt-2 w-full rounded-2xl border-2 border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                        
                        {{-- Search field --}}
                        <div class="p-2.5 border-b border-gray-100 dark:border-gray-800">
                            <div class="relative">
                                <input type="text" x-ref="searchInput" x-model="search" placeholder="Type report name (e.g. Cleaning, Security)..."
                                    class="w-full rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2 pl-9 text-sm font-semibold text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
                            </div>
                        </div>

                        {{-- Options --}}
                        <div class="max-h-64 overflow-y-auto p-1.5 space-y-0.5">
                            <template x-for="(opt, index) in filteredOptions" :key="opt.id">
                                <button type="button" @click="selectOption(opt)"
                                    @mouseenter="highlightedIndex = index"
                                    class="w-full text-left px-3 py-2 text-sm rounded-xl transition-colors flex items-center justify-between"
                                    :class="reportType == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedIndex === index ? 'bg-brand-50 text-brand-900 dark:bg-brand-950/40 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5')">
                                    <span class="flex items-center gap-2 flex-1 min-w-0">
                                        <span x-text="opt.name" class="font-bold truncate"></span>
                                    </span>
                                    <span x-text="opt.badge" class="text-[9px] px-1.5 py-0.5 rounded font-extrabold uppercase shrink-0 ml-1 opacity-80"
                                        :class="reportType == opt.id ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Date From -->
                <div class="w-full sm:w-36 lg:w-40 shrink-0">
                    <label class="mb-1 block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Date From
                    </label>
                    <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}" placeholder="YYYY-MM-DD" autocomplete="off"
                        class="w-full h-11 rounded-xl border-2 border-gray-300 bg-white px-3 py-2 text-sm sm:text-base font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <!-- Date To -->
                <div class="w-full sm:w-36 lg:w-40 shrink-0">
                    <label class="mb-1 block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Date To
                    </label>
                    <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}" placeholder="YYYY-MM-DD" autocomplete="off"
                        class="w-full h-11 rounded-xl border-2 border-gray-300 bg-white px-3 py-2 text-sm sm:text-base font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>

                <!-- Action Buttons: Filter, Reset, Print -->
                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit"
                        class="h-11 inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-5 text-sm sm:text-base font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer">
                        Filter
                    </button>
                    @if($selectedReportType || $dateFrom !== now()->subDays(6)->format('Y-m-d') || $dateTo !== now()->format('Y-m-d'))
                        <a href="{{ route('admin-office-reports.summary') }}"
                            class="h-11 inline-flex items-center justify-center rounded-xl border-2 border-gray-300 px-3.5 text-sm sm:text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            Reset
                        </a>
                    @endif
                    <a href="{{ route('admin-office-reports.summary.print', request()->all()) }}"
                        onclick="window.open(this.href,'_blank','width=1150,height=850,scrollbars=yes'); return false;"
                        class="h-11 inline-flex items-center gap-1.5 rounded-xl bg-gray-900 px-4 text-sm sm:text-base font-extrabold text-white shadow-md hover:bg-gray-800 transition-colors cursor-pointer">
                        🖨️ Print
                    </a>
                </div>

            </div>

            <!-- Inline Quick Date Preset Chips & Grouping View Switcher -->
            <div class="mt-2.5 pt-2.5 border-t border-gray-200 dark:border-gray-800 flex items-center justify-between gap-2 flex-wrap">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-[11px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400 mr-1">Quick Presets:</span>
                    
                    <button type="button" @click="setPreset('{{ now()->format('Y-m-d') }}', '{{ now()->format('Y-m-d') }}')"
                        class="px-2.5 py-1 text-xs font-extrabold rounded-lg border transition-colors cursor-pointer"
                        :class="dateFrom === '{{ now()->format('Y-m-d') }}' && dateTo === '{{ now()->format('Y-m-d') }}' ? 'border-brand-500 bg-brand-600 text-white shadow-2xs' : 'border-gray-200 dark:border-gray-700 hover:border-brand-500 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-900/30'">
                        ⚡ Today
                    </button>

                    <button type="button" @click="setPreset('{{ now()->subDay()->format('Y-m-d') }}', '{{ now()->subDay()->format('Y-m-d') }}')"
                        class="px-2.5 py-1 text-xs font-extrabold rounded-lg border transition-colors cursor-pointer"
                        :class="dateFrom === '{{ now()->subDay()->format('Y-m-d') }}' && dateTo === '{{ now()->subDay()->format('Y-m-d') }}' ? 'border-brand-500 bg-brand-600 text-white shadow-2xs' : 'border-gray-200 dark:border-gray-700 hover:border-brand-500 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-900/30'">
                        ⚡ Yesterday
                    </button>

                    <button type="button" @click="setPreset('{{ now()->subDays(6)->format('Y-m-d') }}', '{{ now()->format('Y-m-d') }}')"
                        class="px-2.5 py-1 text-xs font-extrabold rounded-lg border transition-colors cursor-pointer"
                        :class="dateFrom === '{{ now()->subDays(6)->format('Y-m-d') }}' && dateTo === '{{ now()->format('Y-m-d') }}' ? 'border-brand-500 bg-brand-600 text-white shadow-2xs' : 'border-gray-200 dark:border-gray-700 hover:border-brand-500 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-900/30'">
                        📅 Last 7 Days
                    </button>

                    <button type="button" @click="setPreset('{{ now()->startOfMonth()->format('Y-m-d') }}', '{{ now()->format('Y-m-d') }}')"
                        class="px-2.5 py-1 text-xs font-extrabold rounded-lg border transition-colors cursor-pointer"
                        :class="dateFrom === '{{ now()->startOfMonth()->format('Y-m-d') }}' && dateTo === '{{ now()->format('Y-m-d') }}' ? 'border-brand-500 bg-brand-600 text-white shadow-2xs' : 'border-gray-200 dark:border-gray-700 hover:border-brand-500 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-900/30'">
                        🗓️ This Month
                    </button>

                    <button type="button" @click="setPreset('{{ now()->subMonth()->startOfMonth()->format('Y-m-d') }}', '{{ now()->subMonth()->endOfMonth()->format('Y-m-d') }}')"
                        class="px-2.5 py-1 text-xs font-extrabold rounded-lg border transition-colors cursor-pointer"
                        :class="dateFrom === '{{ now()->subMonth()->startOfMonth()->format('Y-m-d') }}' && dateTo === '{{ now()->subMonth()->endOfMonth()->format('Y-m-d') }}' ? 'border-brand-500 bg-brand-600 text-white shadow-2xs' : 'border-gray-200 dark:border-gray-700 hover:border-brand-500 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-brand-50 dark:hover:bg-brand-900/30'">
                        🗓️ Last Month
                    </button>
                </div>

                @if(!$isSingleReport)
                    {{-- Section Grouping Toggle --}}
                    <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
                        <button type="button" @click="setGrouping('day')"
                            class="px-3 py-1 text-xs font-extrabold rounded-lg transition-colors cursor-pointer"
                            :class="groupBy === 'day' ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-2xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'">
                            📅 Group by Day (Dates)
                        </button>
                        <button type="button" @click="setGrouping('report')"
                            class="px-3 py-1 text-xs font-extrabold rounded-lg transition-colors cursor-pointer"
                            :class="groupBy === 'report' ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-2xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'">
                            🏢 Group by Module
                        </button>
                    </div>
                @endif
            </div>

        </form>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- SECTION-WISE REPORT TABLES (PURE TABLE VIEW) --}}
        {{-- ══════════════════════════════════════════════════════════ --}}

        @if($isSingleReport)

            {{-- ─────────────────────────────────────────────────────── --}}
            {{-- CASE 1: SPECIFIC REPORT TYPE SELECTED (DAY-WISE SECTIONS) --}}
            {{-- ─────────────────────────────────────────────────────── --}}
            <div class="space-y-6">

                {{-- Header banner --}}
                <div class="px-5 py-3.5 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-700 text-white flex items-center justify-between flex-wrap gap-3 shadow-md">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">📊</span>
                        <div>
                            <h2 class="text-lg font-black">{{ $activeReportName }}</h2>
                            <p class="text-xs text-brand-100">
                                Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }} ({{ $totalDays }} Days) • Total: <strong>{{ $totalReports }}</strong> Reports
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin-office-reports.summary') }}"
                            class="px-3 py-1.5 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition-colors">
                            ← View All Modules
                        </a>
                        <a href="{{ route('inspection-reports.index', $activeReportKey) }}"
                            class="px-3 py-1.5 rounded-xl bg-white text-brand-700 hover:bg-gray-100 text-xs font-extrabold transition-colors">
                            Manage Records →
                        </a>
                    </div>
                </div>

                {{-- Day-wise Sections --}}
                @php $hasAnyData = false; @endphp
                @foreach($dayWiseGroups as $day)
                    @if(!empty($day['reports']))
                        @php $hasAnyData = true; @endphp
                        <div class="rounded-2xl border-2 border-gray-200 dark:border-gray-800 overflow-hidden shadow-md">
                            
                            {{-- Section Header for this Day --}}
                            <div class="px-5 py-3 bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2.5">
                                    <span class="px-3 py-1 rounded-xl text-xs font-black bg-brand-600 text-white shadow-2xs">
                                        📅 {{ $day['carbon']->format('d M Y') }} ({{ $day['carbon']->format('l') }})
                                    </span>
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                        <strong>{{ $day['count'] }}</strong> record(s)
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 font-extrabold">
                                        ✅ {{ $day['pass_items'] }} Passed
                                    </span>
                                    @if($day['fail_items'] > 0)
                                        <span class="px-2.5 py-0.5 rounded-lg bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 font-extrabold">
                                            ⚠️ {{ $day['fail_items'] }} Issues
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Data Table for this Day's Reports --}}
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs font-semibold">
                                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-600 dark:text-gray-400 font-extrabold uppercase text-[11px] border-b border-gray-200 dark:border-gray-700">
                                        <tr>
                                            <th class="py-2.5 px-4 w-24">Time</th>
                                            <th class="py-2.5 px-4">Reporter / Inspector</th>
                                            <th class="py-2.5 px-4">{{ $activeReportKey === 'flat_inspection' ? 'Flat / Tenant' : 'Member / Staff' }}</th>
                                            <th class="py-2.5 px-4 text-center">Checks Status</th>
                                            <th class="py-2.5 px-4 text-center">Admin Rating</th>
                                            <th class="py-2.5 px-4">Remarks / Notes</th>
                                            <th class="py-2.5 px-4 text-center">Photo</th>
                                            <th class="py-2.5 px-4 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                                        @foreach($day['reports'] as $rep)
                                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors">
                                                <td class="py-3 px-4 font-mono text-gray-500 font-bold whitespace-nowrap">{{ $rep['time'] }}</td>
                                                <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $rep['reported_by'] }}</td>
                                                <td class="py-3 px-4 font-bold text-brand-600 dark:text-brand-400">{{ $rep['member_or_unit'] }}</td>
                                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 font-extrabold">
                                                        ✅ {{ $rep['pass_count'] }}
                                                    </span>
                                                    @if($rep['fail_count'] > 0)
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 font-extrabold ml-1">
                                                            ⚠️ {{ $rep['fail_count'] }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    @if(!empty($rep['admin_rating']))
                                                        <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 font-black">
                                                            ⭐ {{ $rep['admin_rating'] }}/5
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4 max-w-xs text-gray-600 dark:text-gray-400 truncate" title="{{ $rep['overall_remarks'] }}">
                                                    {{ $rep['overall_remarks'] ?: '—' }}
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    @if(!empty($rep['admin_photo_url']))
                                                        <a href="{{ $rep['admin_photo_url'] }}" target="_blank" class="inline-block hover:opacity-80">
                                                            <img src="{{ $rep['admin_photo_url'] }}" class="h-8 w-8 rounded-lg object-cover border border-gray-300">
                                                        </a>
                                                    @else
                                                        <span class="text-gray-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    <a href="{{ $rep['view_url'] }}" target="_blank"
                                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-brand-50 text-brand-600 dark:text-brand-400 font-bold transition-colors">
                                                        View →
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    @endif
                @endforeach

                @if(!$hasAnyData)
                    <div class="py-16 text-center rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800">
                        <p class="text-sm font-bold text-gray-500 dark:text-gray-400">
                            No records found for {{ $activeReportName }} between {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} and {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}.
                        </p>
                    </div>
                @endif

            </div>

        @else

            {{-- ─────────────────────────────────────────────────────── --}}
            {{-- CASE 2: ALL REPORTS SELECTED (SECTION-WISE TABLES)     --}}
            {{-- ─────────────────────────────────────────────────────── --}}
            <div class="space-y-6">

                @if(($groupBy ?? 'day') === 'day')
                    
                    {{-- ── GROUP BY DAY (DATES) SECTIONS ── --}}
                    @php $hasAnyData = false; @endphp
                    @foreach($daySections as $day)
                        @if(!empty($day['reports']))
                            @php $hasAnyData = true; @endphp
                            <div class="rounded-2xl border-2 border-gray-200 dark:border-gray-800 overflow-hidden shadow-md">
                                
                                {{-- Section Header for this Day --}}
                                <div class="px-5 py-3.5 bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-3">
                                        <span class="px-3.5 py-1 rounded-xl text-xs font-black bg-brand-600 text-white shadow-2xs">
                                            📅 {{ $day['carbon']->format('d M Y') }} ({{ $day['carbon']->format('l') }})
                                        </span>
                                        <span class="text-xs font-extrabold text-gray-800 dark:text-gray-200">
                                            <strong>{{ $day['count'] }}</strong> Report(s) Filed
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 font-extrabold">
                                            ✅ {{ $day['pass_count'] }} Passed Checks
                                        </span>
                                        @if($day['fail_count'] > 0)
                                            <span class="px-2.5 py-0.5 rounded-lg bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 font-extrabold">
                                                ⚠️ {{ $day['fail_count'] }} Issues
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Table of all reports for this date --}}
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs font-semibold">
                                        <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-600 dark:text-gray-400 font-extrabold uppercase text-[11px] border-b border-gray-200 dark:border-gray-700">
                                            <tr>
                                                <th class="py-2.5 px-4 w-24">Time</th>
                                                <th class="py-2.5 px-4">Report Module</th>
                                                <th class="py-2.5 px-4">Reporter / Inspector</th>
                                                <th class="py-2.5 px-4">Member / Unit</th>
                                                <th class="py-2.5 px-4 text-center">Checks Status</th>
                                                <th class="py-2.5 px-4 text-center">Admin Rating</th>
                                                <th class="py-2.5 px-4">Remarks</th>
                                                <th class="py-2.5 px-4 text-center">Photo</th>
                                                <th class="py-2.5 px-4 text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                                            @foreach($day['reports'] as $item)
                                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors">
                                                    <td class="py-3 px-4 font-mono text-gray-500 font-bold whitespace-nowrap">{{ $item['time'] }}</td>
                                                    <td class="py-3 px-4 font-black text-brand-600 dark:text-brand-400">
                                                        <a href="{{ route('admin-office-reports.summary', ['report_type' => $item['report_key'], 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="hover:underline">
                                                            {{ $item['report_name'] }}
                                                        </a>
                                                    </td>
                                                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $item['reported_by'] }}</td>
                                                    <td class="py-3 px-4">{{ $item['member_or_unit'] }}</td>
                                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 font-extrabold">
                                                            ✅ {{ $item['pass_count'] }}
                                                        </span>
                                                        @if($item['fail_count'] > 0)
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 font-extrabold ml-1">
                                                                ⚠️ {{ $item['fail_count'] }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-4 text-center">
                                                        @if(!empty($item['admin_rating']))
                                                            <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 font-black">
                                                                ⭐ {{ $item['admin_rating'] }}/5
                                                            </span>
                                                        @else
                                                            <span class="text-gray-400">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-4 max-w-xs text-gray-600 dark:text-gray-400 truncate" title="{{ $item['overall_remarks'] }}">
                                                        {{ $item['overall_remarks'] ?: '—' }}
                                                    </td>
                                                    <td class="py-3 px-4 text-center">
                                                        @if(!empty($item['admin_photo_url']))
                                                            <a href="{{ $item['admin_photo_url'] }}" target="_blank" class="inline-block hover:opacity-80">
                                                                <img src="{{ $item['admin_photo_url'] }}" class="h-8 w-8 rounded-lg object-cover border border-gray-300">
                                                            </a>
                                                        @else
                                                            <span class="text-gray-400">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-4 text-center">
                                                        <a href="{{ $item['view_url'] }}" target="_blank"
                                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-brand-50 text-brand-600 dark:text-brand-400 font-bold transition-colors">
                                                            View →
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        @endif
                    @endforeach

                    @if(!$hasAnyData)
                        <div class="py-16 text-center rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800">
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400">
                                No reports found across any modules between {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} and {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}.
                            </p>
                        </div>
                    @endif

                @else

                    {{-- ── GROUP BY DEPARTMENT / MODULE SECTIONS ── --}}
                    @php $hasAnyData = false; @endphp
                    @foreach($reportSections as $section)
                        @if(!empty($section['reports']))
                            @php $hasAnyData = true; @endphp
                            <div class="rounded-2xl border-2 border-gray-200 dark:border-gray-800 overflow-hidden shadow-md">
                                
                                {{-- Section Header for this Department --}}
                                <div class="px-5 py-3.5 bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-base font-black text-gray-900 dark:text-white flex items-center gap-2">
                                            <span>📋 {{ $section['name'] }}</span>
                                            @if($section['is_daily'])
                                                <span class="px-2 py-0.5 rounded-md bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400 text-[10px] font-black uppercase">
                                                    Daily
                                                </span>
                                            @endif
                                        </h3>
                                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">
                                            (<strong>{{ $section['count'] }}</strong> reports in period)
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 font-extrabold">
                                            ✅ {{ $section['pass_count'] }} Passed
                                        </span>
                                        @if($section['fail_count'] > 0)
                                            <span class="px-2.5 py-0.5 rounded-lg bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 font-extrabold">
                                                ⚠️ {{ $section['fail_count'] }} Issues
                                            </span>
                                        @endif
                                        @if($section['avg_rating'] !== null)
                                            <span class="px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 font-black">
                                                ⭐ {{ $section['avg_rating'] }}/5
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Data Table for this Department --}}
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs font-semibold">
                                        <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-600 dark:text-gray-400 font-extrabold uppercase text-[11px] border-b border-gray-200 dark:border-gray-700">
                                            <tr>
                                                <th class="py-2.5 px-4">Date & Day</th>
                                                <th class="py-2.5 px-4">Time</th>
                                                <th class="py-2.5 px-4">Reporter / Inspector</th>
                                                <th class="py-2.5 px-4">{{ $section['key'] === 'flat_inspection' ? 'Flat / Tenant' : 'Member / Staff' }}</th>
                                                <th class="py-2.5 px-4 text-center">Checks Status</th>
                                                <th class="py-2.5 px-4 text-center">Rating</th>
                                                <th class="py-2.5 px-4">Remarks</th>
                                                <th class="py-2.5 px-4 text-center">Photo</th>
                                                <th class="py-2.5 px-4 text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                                            @foreach($section['reports'] as $item)
                                                <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors">
                                                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                                        {{ \Carbon\Carbon::parse($item['date'])->format('d M Y') }}
                                                        <span class="text-[10px] text-gray-400 font-normal">({{ $item['day_name'] }})</span>
                                                    </td>
                                                    <td class="py-3 px-4 font-mono text-gray-500 font-bold whitespace-nowrap">{{ $item['time'] }}</td>
                                                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $item['reported_by'] }}</td>
                                                    <td class="py-3 px-4">{{ $item['member_or_unit'] }}</td>
                                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 font-extrabold">
                                                            ✅ {{ $item['pass_count'] }}
                                                        </span>
                                                        @if($item['fail_count'] > 0)
                                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 font-extrabold ml-1">
                                                                ⚠️ {{ $item['fail_count'] }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-4 text-center">
                                                        @if(!empty($item['admin_rating']))
                                                            <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 font-black">
                                                                ⭐ {{ $item['admin_rating'] }}/5
                                                            </span>
                                                        @else
                                                            <span class="text-gray-400">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-4 max-w-xs text-gray-600 dark:text-gray-400 truncate" title="{{ $item['overall_remarks'] }}">
                                                        {{ $item['overall_remarks'] ?: '—' }}
                                                    </td>
                                                    <td class="py-3 px-4 text-center">
                                                        @if(!empty($item['admin_photo_url']))
                                                            <a href="{{ $item['admin_photo_url'] }}" target="_blank" class="inline-block hover:opacity-80">
                                                                <img src="{{ $item['admin_photo_url'] }}" class="h-8 w-8 rounded-lg object-cover border border-gray-300">
                                                            </a>
                                                        @else
                                                            <span class="text-gray-400">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 px-4 text-center">
                                                        <a href="{{ $item['view_url'] }}" target="_blank"
                                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-brand-50 text-brand-600 dark:text-brand-400 font-bold transition-colors">
                                                            View →
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        @endif
                    @endforeach

                    @if(!$hasAnyData)
                        <div class="py-16 text-center rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800">
                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400">
                                No reports found across any modules between {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} and {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}.
                            </p>
                        </div>
                    @endif

                @endif

            </div>

        @endif

    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                window._fpFrom = flatpickr('#date_from', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    defaultDate: '{{ $dateFrom }}'
                });
                window._fpTo = flatpickr('#date_to', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    defaultDate: '{{ $dateTo }}'
                });
            }
        });
    </script>
@endpush