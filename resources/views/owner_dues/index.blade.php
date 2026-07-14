@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Owner Dues Report" />

    {{-- Income Summary Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Income Collected</p>
            <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">Rs. {{ number_format($totalIncome, 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-400">Tenant rent + Party collections</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Owner Dues Paid</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">Rs. {{ number_format($totalOwnersPaid, 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-400">Paid via Payment Vouchers</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pending Owner Dues</p>
            <p class="mt-2 text-2xl font-bold {{ $totalOwnersPending > 0 ? 'text-orange-500' : 'text-green-600 dark:text-green-400' }}">
                Rs. {{ number_format($totalOwnersPending, 2) }}
            </p>
            <p class="mt-1 text-[10px] text-gray-400">Still owed to owners</p>
        </div>
        <div class="rounded-xl border {{ $disposableAmount >= 0 ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/10' : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/10' }} p-5">
            <p class="text-xs font-semibold uppercase tracking-wider {{ $disposableAmount >= 0 ? 'text-green-600' : 'text-red-500' }}">Disposable Amount</p>
            <p class="mt-2 text-2xl font-bold {{ $disposableAmount >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                Rs. {{ number_format($disposableAmount, 2) }}
            </p>
            <p class="mt-1 text-[10px] {{ $disposableAmount >= 0 ? 'text-green-500' : 'text-red-400' }}">After all dues deducted</p>
        </div>
    </div>

    {{-- Disposable Amount Breakdown + Export --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.02] p-4">
        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
            <p class="font-semibold text-gray-800 dark:text-white/90">Disposable Amount Breakdown</p>
            <p>Cash in Accounts: <strong class="text-gray-800 dark:text-white/80">Rs. {{ number_format($totalCashBalance, 2) }}</strong></p>
            <p>− Pending Owner Dues: <strong class="text-orange-600">Rs. {{ number_format($totalOwnersPending, 2) }}</strong></p>
            <p class="border-t border-gray-200 dark:border-gray-700 pt-1 font-semibold {{ $disposableAmount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                = Disposable Amount: Rs. {{ number_format($disposableAmount, 2) }}
            </p>
        </div>
        <div class="flex gap-2 shrink-0">
            <a href="{{ route('reports.owner-dues.pdf') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-red-400 px-4 py-2.5 text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export PDF
            </a>
        </div>
    </div>

    {{-- Per-Owner Table --}}
    <x-common.component-card title="Owner Share Summary" desc="Running balance — all income collected vs amounts paid out to each managing owner">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th class="pb-3 pr-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Owner / Partner</th>
                        <th class="pb-3 pr-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">Share %</th>
                        <th class="pb-3 pr-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-right">Total Income Due</th>
                        <th class="pb-3 pr-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-right">Total Paid Out</th>
                        <th class="pb-3 pr-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-right">Pending Balance</th>
                        <th class="pb-3 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800/60">
                    @forelse($ownerRows as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3.5 pr-4">
                                <div class="font-semibold text-gray-800 dark:text-white/90">{{ $row['owner']->name }}</div>
                                @if($row['owner']->phone)
                                    <div class="text-xs text-gray-400">{{ $row['owner']->phone }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 pr-4 text-center">
                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950/30 dark:text-blue-400">
                                    {{ number_format($row['percentage'], 2) }}%
                                </span>
                            </td>
                            <td class="py-3.5 pr-4 text-right font-medium text-gray-700 dark:text-gray-300">
                                Rs. {{ number_format($row['due'], 2) }}
                            </td>
                            <td class="py-3.5 pr-4 text-right font-medium text-green-600 dark:text-green-400">
                                Rs. {{ number_format($row['paid'], 2) }}
                            </td>
                            <td class="py-3.5 pr-4 text-right font-bold {{ $row['pending'] > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-400' }}">
                                Rs. {{ number_format($row['pending'], 2) }}
                            </td>
                            <td class="py-3.5 text-center">
                                @if($row['pending'] <= 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-950/30 dark:text-green-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Clear
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 dark:bg-orange-950/30 dark:text-orange-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span> Pending
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-gray-400">
                                No managing owners found. <a href="{{ route('owners.create') }}" class="text-brand-500 hover:underline">Add an owner</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($ownerRows) > 0)
                <tfoot class="border-t-2 border-gray-200 dark:border-gray-700">
                    <tr class="font-bold text-gray-800 dark:text-white/90">
                        <td class="pt-3 pr-4 text-sm">TOTALS</td>
                        <td class="pt-3 pr-4 text-center text-xs text-gray-400">
                            {{ number_format(collect($ownerRows)->sum('percentage'), 2) }}%
                        </td>
                        <td class="pt-3 pr-4 text-right">Rs. {{ number_format($totalOwnersDue, 2) }}</td>
                        <td class="pt-3 pr-4 text-right text-green-600 dark:text-green-400">Rs. {{ number_format($totalOwnersPaid, 2) }}</td>
                        <td class="pt-3 pr-4 text-right {{ $totalOwnersPending > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-gray-400' }}">
                            Rs. {{ number_format($totalOwnersPending, 2) }}
                        </td>
                        <td class="pt-3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-common.component-card>

    {{-- Income Source Breakdown --}}
    <div class="mt-6">
        <x-common.component-card title="Income Sources (All Time)" desc="Breakdown of all income that forms the owner share calculation base">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 max-w-lg">
                <div class="rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-white/[0.02] p-4">
                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Tenant Rent Collected</p>
                    <p class="mt-1.5 text-xl font-bold text-gray-800 dark:text-white/90">Rs. {{ number_format($totalTenantIncome, 2) }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">From Receiving Vouchers (tenant)</p>
                </div>
                <div class="rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-white/[0.02] p-4">
                    <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Party / External Income</p>
                    <p class="mt-1.5 text-xl font-bold text-gray-800 dark:text-white/90">Rs. {{ number_format($totalPartyIncome, 2) }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">From General Receiving Vouchers</p>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Report generated at: {{ $generatedAt->format('d M Y, h:i A') }}</p>
        </x-common.component-card>
    </div>
@endsection
