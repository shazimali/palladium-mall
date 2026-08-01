@extends('layouts.app')

@section('content')
<style>
    @media print {
        @page {
            size: A4;
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
            zoom: 0.8;
        }
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 13px !important;
        }
        th, td {
            border: 1px solid #9ca3af !important;
            padding: 8px 10px !important;
            color: black !important;
        }
        tfoot {
            display: table-row-group !important;
        }
        tfoot tr {
            font-weight: 900 !important;
            font-size: 15px !important;
            background-color: #e5e7eb !important;
        }
    }
</style>

<div class="hidden print:block mb-6 text-center border-b-2 border-black pb-4">
    <h1 class="text-2xl font-black uppercase tracking-wider text-black">PALLADIUM MALL</h1>
    <p class="text-xs font-bold text-gray-700 uppercase">Management Office — Islamabad</p>
    <h2 class="text-lg font-black uppercase text-black mt-2">{{ $title }}</h2>
    <p class="text-sm font-bold text-black mt-1">
        Statement Period: {{ $dateFrom ? date('d M Y', strtotime($dateFrom)) : 'Start' }} —
        {{ $dateTo ? date('d M Y', strtotime($dateTo)) : 'End' }}
    </p>
</div>

    <x-common.page-breadcrumb pageTitle="{{ $title }}" />

    {{-- STICKY HEADER --}}
    <div class="sticky mb-6 rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 p-5 shadow-xl backdrop-blur-md no-print"
        style="position: sticky; top: 72px; z-index: 990;">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-500 text-white shadow-md text-3xl font-black">
                    📊
                </div>
                <div class="min-w-0">
                    <p class="text-xl font-extrabold uppercase tracking-wider text-brand-600 dark:text-brand-400">
                        {{ $title }}
                    </p>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900 dark:text-white mt-0.5">
                        {{ $dateFrom ? date('d M Y', strtotime($dateFrom)) : 'Start' }} — {{ $dateTo ? date('d M Y', strtotime($dateTo)) : 'End' }}
                    </h2>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('reports.account_summary.pdf', request()->all()) }}" target="_blank"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors w-full sm:w-auto">
                    📄 PDF
                </a>
                <button onclick="window.print()"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-transparent bg-brand-500 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition-colors w-full sm:w-auto">
                    🖨️ Print
                </button>
                <a href="{{ route('reports.account_summary.excel', request()->all()) }}"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-transparent bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors w-full sm:w-auto">
                    📊 Excel
                </a>
            </div>
        </div>
    </div>

    {{-- Date Range Selector Panel --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] no-print">
        <form method="GET" action="{{ route('reports.account_summary') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end justify-between">
            <div class="flex flex-col sm:flex-row gap-4 flex-1">
                @php
                    $inputClass = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 w-full sm:w-48';
                    $labelClass = 'mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400';
                @endphp
                <div>
                    <label class="{{ $labelClass }}">Date From</label>
                    <input type="text" name="date_from" id="date_from" value="{{ $dateFrom }}" placeholder="YYYY-MM-DD" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Date To</label>
                    <input type="text" name="date_to" id="date_to" value="{{ $dateTo }}" placeholder="YYYY-MM-DD" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="{{ $labelClass }}">Account Type</label>
                    <select name="account_type" id="account_type" class="{{ $inputClass }}">
                        <option value="all" {{ $accountType == 'all' ? 'selected' : '' }}>All Accounts</option>
                        <option value="asset" {{ $accountType == 'asset' ? 'selected' : '' }}>Assets (Banks/Cash)</option>
                        <option value="liability" {{ $accountType == 'liability' ? 'selected' : '' }}>Equity / Liabilities (Owners)</option>
                        <option value="receivable" {{ $accountType == 'receivable' ? 'selected' : '' }}>Receivables (Tenants)</option>
                        <option value="expense" {{ $accountType == 'expense' ? 'selected' : '' }}>Expenses</option>
                    </select>
                </div>
                <div class="flex gap-2 items-end w-full sm:w-auto">
                    <button type="submit" class="h-10 rounded-lg bg-brand-500 px-5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors w-full sm:w-auto">Apply Filter</button>
                    <a href="{{ route('reports.account_summary') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors w-full sm:w-auto">Reset</a>
                </div>
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('reports.account_summary', ['date_from' => date('Y-m-d'), 'date_to' => date('Y-m-d'), 'account_type' => $accountType]) }}"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    Today
                </a>
                <a href="{{ route('reports.account_summary', ['date_from' => date('Y-m-d', strtotime('-1 day')), 'date_to' => date('Y-m-d', strtotime('-1 day')), 'account_type' => $accountType]) }}"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    Yesterday
                </a>
                <a href="{{ route('reports.account_summary', ['date_from' => date('Y-m-01'), 'date_to' => date('Y-m-t'), 'account_type' => $accountType]) }}"
                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    This Month
                </a>
            </div>
        </form>
    </div>

<div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] mb-6">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white/90">
            Account Balances Statement
        </h3>
    </div>
    
    <div class="overflow-x-auto border border-gray-100 rounded-lg dark:border-gray-800">
        <table class="w-full text-base text-left text-gray-600 dark:text-gray-400 table-auto print:table">
            <thead class="text-sm uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 w-1/3">Account Name</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Opening Balance</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total Debit</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total Credit</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Closing Balance</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700 text-base">
                @php 
                    $grandTotalOpening = 0;
                    $grandTotalDebit = 0;
                    $grandTotalCredit = 0;
                    $grandTotalClosing = 0;
                @endphp

                @forelse($summary as $groupName => $entries)
                    @php 
                        $groupLabel = '';
                        if ($groupName === 'asset') $groupLabel = 'Assets (Bank & Cash)';
                        elseif ($groupName === 'liability') $groupLabel = 'Equity & Liabilities (Owners)';
                        elseif ($groupName === 'receivable') $groupLabel = 'Receivables (Tenants)';
                        elseif ($groupName === 'expense') $groupLabel = 'Expenses';

                        $groupOpening = $entries->sum('opening');
                        $groupDebit = $entries->sum('debit');
                        $groupCredit = $entries->sum('credit');
                        $groupClosing = $entries->sum('closing');

                        // We only sum up for grand totals logically. But mixing assets and expenses in a single grand total might not make accounting sense. 
                        // We will just show the grand totals as a raw sum of what's displayed.
                        $grandTotalOpening += $groupOpening;
                        $grandTotalDebit += $groupDebit;
                        $grandTotalCredit += $groupCredit;
                        $grandTotalClosing += $groupClosing;
                    @endphp

                    <tr class="bg-gray-100 dark:bg-gray-700 font-bold border-t-2 border-gray-300 dark:border-gray-600">
                        <td colspan="5" class="px-6 py-3 text-sm text-gray-900 dark:text-white uppercase tracking-wider">
                            {{ $groupLabel }}
                        </td>
                    </tr>

                    @foreach($entries as $entry)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                                <a href="{{ $entry['url'] }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 no-print underline" target="_blank" title="View Ledger">
                                    {{ $entry['name'] }}
                                </a>
                                <span class="hidden print:inline">{{ $entry['name'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-300">
                                {{ number_format($entry['opening'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-300">
                                {{ number_format($entry['debit'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-300">
                                {{ number_format($entry['credit'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold {{ $entry['closing'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ number_format($entry['closing'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                    
                    <tr class="bg-gray-50 dark:bg-gray-800 font-semibold border-b-2 border-gray-300 dark:border-gray-600">
                        <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-white">Group Total:</td>
                        <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-white">{{ number_format($groupOpening, 2) }}</td>
                        <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-white">{{ number_format($groupDebit, 2) }}</td>
                        <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-white">{{ number_format($groupCredit, 2) }}</td>
                        <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-white">{{ number_format($groupClosing, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                            No accounts found for the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($summary->isNotEmpty())
            <tfoot class="bg-gray-200 dark:bg-gray-900 border-t-4 border-gray-300 dark:border-gray-700 print:bg-gray-200 print:text-black">
                <tr>
                    <th scope="row" class="px-6 py-4 text-left text-base font-bold text-gray-900 dark:text-white uppercase print:text-black">Grand Total</th>
                    <td class="px-6 py-4 text-right text-base font-bold text-gray-900 dark:text-white print:text-black">{{ number_format($grandTotalOpening, 2) }}</td>
                    <td class="px-6 py-4 text-right text-base font-bold text-gray-900 dark:text-white print:text-black">{{ number_format($grandTotalDebit, 2) }}</td>
                    <td class="px-6 py-4 text-right text-base font-bold text-gray-900 dark:text-white print:text-black">{{ number_format($grandTotalCredit, 2) }}</td>
                    <td class="px-6 py-4 text-right text-base font-bold text-gray-900 dark:text-white print:text-black">{{ number_format($grandTotalClosing, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#date_from', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                });

                flatpickr('#date_to', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                });
            }
        });
    </script>
@endpush
