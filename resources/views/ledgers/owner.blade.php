@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Managing Owner Ledger" />

    <x-common.component-card title="" desc="">

        <form action="{{ route('ledgers.owner') }}" method="GET" id="owner-ledger-form"
            class="sticky top-[72px] z-[990] bg-white/95 dark:bg-gray-900/95 p-4 rounded-2xl border-2 border-brand-500 shadow-xl backdrop-blur-md mb-6">
            <div class="flex flex-wrap md:flex-nowrap items-end gap-3.5">
                <!-- Owner Dropdown -->
                <div class="shrink-0 w-72">
                    <label
                        class="mb-1.5 block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Select Managing Owner <span class="text-red-500">*</span>
                    </label>
                    <select name="owner_id" onchange="this.form.submit()" required
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-base font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Choose an Owner</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" {{ $ownerId == $owner->id ? 'selected' : '' }}>
                                {{ $owner->name }} ({{ $owner->email ?? '—' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Year -->
                <div class="shrink-0 w-28">
                    <label
                        class="mb-1.5 block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Year
                    </label>
                    <select name="year"
                        class="w-full rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-base font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @for($y = now()->year + 1; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Month(s) multi-select dropdown -->
                <div class="shrink-0 w-60" x-data="{ open: false }" @click.outside="open = false">
                    <label
                        class="mb-1.5 block text-xs font-black uppercase tracking-wider text-gray-700 dark:text-gray-300">
                        Select Month(s) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between gap-2 rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-base font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <span class="truncate text-left">
                                @if(count($months) === 12)
                                    All Months
                                @elseif(count($months) > 0)
                                    {{ collect($months)->map(fn($m) => \Carbon\Carbon::create(2000, $m, 1)->format('M'))->implode(', ') }}
                                @else
                                    Choose Month(s)
                                @endif
                            </span>
                            <svg class="h-4 w-4 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                            class="absolute z-50 mt-2 w-full max-h-64 overflow-y-auto rounded-xl border-2 border-gray-300 bg-white p-2 shadow-xl dark:border-gray-700 dark:bg-gray-900">
                            @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $i => $m)
                                @php $mn = $i + 1; @endphp
                                <label
                                    class="flex items-center gap-2 rounded-lg px-2.5 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 cursor-pointer dark:text-gray-300 dark:hover:bg-white/5">
                                    <input type="checkbox" name="months[]" value="{{ $mn }}" class="accent-brand-600"
                                        {{ in_array($mn, $months) ? 'checked' : '' }}>
                                    {{ $m }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Action Buttons: Filter, Clear, Print -->
                <div class="shrink-0 flex items-center gap-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-base font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer">
                        Filter
                    </button>
                    @if($ownerId || $year != now()->year || $months != [now()->month])
                        <a href="{{ route('ledgers.owner') }}"
                            class="rounded-xl border-2 border-gray-300 px-4 py-2.5 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                            Clear
                        </a>
                    @endif
                    @if($ledgerData)
                        <a href="{{ route('ledgers.owner.print', request()->all()) }}"
                            onclick="window.open(this.href,'_blank','width=1100,height=800,scrollbars=yes'); return false;"
                            class="inline-flex items-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-base font-extrabold text-white shadow-md hover:bg-gray-800 transition-colors cursor-pointer">
                            🖨️ Print
                        </a>
                    @endif
                </div>
            </div>
        </form>

        @if($ledgerData)

            {{-- Table --}}
            @php
                $openingEntry = $ledgerData['entries']->firstWhere('is_opening', true);
            @endphp
            <div class="overflow-hidden border-2 border-gray-200 rounded-2xl dark:border-gray-800 shadow-md">
                <table class="w-full text-xs sm:text-[13.5px] text-left text-gray-900 dark:text-gray-100">
                    <thead
                        class="text-xs font-black uppercase tracking-wider bg-brand-600 text-white dark:bg-brand-700 border-b-2 border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-3.5 py-2.5 text-white">Date</th>
                            <th class="px-3.5 py-2.5 text-white">Voucher #</th>
                            <th class="px-3.5 py-2.5 text-white">Payment Account</th>
                            <th class="px-3.5 py-2.5 text-white">Reference</th>
                            <th class="px-3.5 py-2.5 text-white">Notes</th>
                            <th class="px-3.5 py-2.5 text-right text-white">Debit (Paid)</th>
                            <th class="px-3.5 py-2.5 text-right text-white">Credit (Received)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-900 dark:text-gray-100 font-semibold text-xs sm:text-[13px]">
                        @if($openingEntry)
                            <tr class="bg-amber-50 dark:bg-amber-950/40 font-black">
                                <td class="px-3.5 py-2 text-xs sm:text-[13px] font-mono font-bold whitespace-nowrap">
                                    {{ $openingEntry['date']->format('d M Y') }}
                                </td>
                                <td class="px-3.5 py-2 text-xs sm:text-[13px] font-mono font-black whitespace-nowrap">
                                    {{ $openingEntry['voucher_no'] }}
                                </td>
                                <td class="px-3.5 py-2 text-xs sm:text-[13px] font-semibold">{{ $openingEntry['account'] }}</td>
                                <td class="px-3.5 py-2 text-xs sm:text-[13px] font-semibold">{{ $openingEntry['reference'] }}</td>
                                <td class="px-3.5 py-2 text-xs sm:text-[13px] font-semibold">{{ $openingEntry['notes'] }}</td>
                                <td class="px-3.5 py-2 text-right font-black text-rose-600 dark:text-rose-400 text-xs sm:text-[13.5px] font-mono whitespace-nowrap">
                                    {{ $openingEntry['debit'] > 0 ? 'Rs. ' . number_format($openingEntry['debit'], 2) : '—' }}
                                </td>
                                <td class="px-3.5 py-2 text-right font-black text-emerald-600 dark:text-emerald-400 text-xs sm:text-[13.5px] font-mono whitespace-nowrap">
                                    {{ $openingEntry['credit'] > 0 ? 'Rs. ' . number_format($openingEntry['credit'], 2) : '—' }}
                                </td>
                            </tr>
                        @endif

                        @forelse($ledgerData['monthly_subtotals'] as $monthKey => $monthInfo)
                            {{-- Month section header --}}
                            <tr class="bg-brand-50 dark:bg-brand-500/10">
                                <td colspan="7" class="px-3.5 py-1.5 text-xs font-black uppercase tracking-wider text-brand-700 dark:text-brand-300">
                                    {{ $monthInfo['label'] }}
                                </td>
                            </tr>

                            @php $monthEntries = $ledgerData['entries']->where('month_key', $monthKey); @endphp
                            @forelse($monthEntries as $entry)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-3.5 py-2 text-xs sm:text-[13px] font-mono font-bold whitespace-nowrap">
                                        {{ $entry['date']->format('d M Y') }}
                                    </td>
                                    <td class="px-3.5 py-2 text-xs sm:text-[13px] font-mono font-black whitespace-nowrap">
                                        @if(!empty($entry['type']) && !empty($entry['id']))
                                            @if($entry['type'] === 'payment_voucher')
                                                <a href="{{ route('payment-vouchers.show', $entry['id']) }}"
                                                    class="text-brand-600 hover:underline font-black dark:text-brand-400">
                                                    {{ $entry['voucher_no'] }}
                                                </a>
                                            @elseif($entry['type'] === 'receiving_voucher')
                                                <a href="{{ route('receiving-vouchers.show', $entry['id']) }}"
                                                    class="text-brand-600 hover:underline font-black dark:text-brand-400">
                                                    {{ $entry['voucher_no'] }}
                                                </a>
                                            @elseif($entry['type'] === 'withdrawal')
                                                <a href="{{ route('withdrawals.show', $entry['id']) }}"
                                                    class="text-brand-600 hover:underline font-black dark:text-brand-400">
                                                    {{ $entry['voucher_no'] }}
                                                </a>
                                            @else
                                                {{ $entry['voucher_no'] }}
                                            @endif
                                        @else
                                            {{ $entry['voucher_no'] }}
                                        @endif
                                    </td>
                                    <td class="px-3.5 py-2 text-xs sm:text-[13px] font-semibold">
                                        {{ $entry['account'] }}
                                    </td>
                                    <td class="px-3.5 py-2 text-xs sm:text-[13px] font-semibold">
                                        {{ $entry['reference'] }}
                                    </td>
                                    <td class="px-3.5 py-2 text-xs sm:text-[13px] font-semibold">
                                        {{ $entry['notes'] }}
                                    </td>
                                    <td class="px-3.5 py-2 text-right font-black text-rose-600 dark:text-rose-400 text-xs sm:text-[13.5px] font-mono whitespace-nowrap">
                                        {{ $entry['debit'] > 0 ? 'Rs. ' . number_format($entry['debit'], 2) : '—' }}
                                    </td>
                                    <td class="px-3.5 py-2 text-right font-black text-emerald-600 dark:text-emerald-400 text-xs sm:text-[13.5px] font-mono whitespace-nowrap">
                                        {{ $entry['credit'] > 0 ? 'Rs. ' . number_format($entry['credit'], 2) : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-4 text-center text-gray-400 dark:text-gray-600 text-xs font-bold">
                                        No transactions in {{ $monthInfo['label'] }}.
                                    </td>
                                </tr>
                            @endforelse

                            {{-- Month subtotal --}}
                            <tr class="bg-gray-50 dark:bg-white/5 border-y-2 border-gray-200 dark:border-gray-700">
                                <td colspan="5" class="px-3.5 py-2 text-xs font-black uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                    Subtotal — {{ $monthInfo['label'] }}
                                    <span class="ml-2 font-mono normal-case tracking-normal text-gray-500 dark:text-gray-400">
                                        (Net: Rs. {{ number_format($monthInfo['net'], 2) }})
                                    </span>
                                </td>
                                <td class="px-3.5 py-2 text-right text-rose-600 dark:text-rose-400 font-mono font-black whitespace-nowrap">
                                    Rs. {{ number_format($monthInfo['debit'], 2) }}
                                </td>
                                <td class="px-3.5 py-2 text-right text-emerald-600 dark:text-emerald-400 font-mono font-black whitespace-nowrap">
                                    Rs. {{ number_format($monthInfo['credit'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400 dark:text-gray-600 text-xs sm:text-sm font-bold">
                                    No month selected.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($ledgerData['entries']) > 0)
                        @php
                            $sumDebit = $ledgerData['summary']['total_debit'];
                            $sumCredit = $ledgerData['summary']['total_credit'];
                            $netBalance = $ledgerData['summary']['net_balance'];
                        @endphp
                        <tfoot
                            class="bg-gray-100 dark:bg-gray-800 border-t-2 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-white font-black text-xs sm:text-sm">
                            <tr>
                                <td colspan="5"
                                    class="px-3.5 py-2.5 uppercase tracking-wider font-black text-gray-900 dark:text-white">
                                    Total Summary
                                    <span class="ml-2 font-mono normal-case tracking-normal text-gray-500 dark:text-gray-400">
                                        (Net: Rs. {{ number_format($netBalance, 2) }})
                                    </span>
                                </td>
                                <td class="px-3.5 py-2.5 text-right text-rose-600 dark:text-rose-400 font-mono font-black whitespace-nowrap">
                                    Rs. {{ number_format($sumDebit, 2) }}
                                </td>
                                <td class="px-3.5 py-2.5 text-right text-emerald-600 dark:text-emerald-400 font-mono font-black whitespace-nowrap">
                                    Rs. {{ number_format($sumCredit, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        @else
            <div
                class="p-8 text-center text-gray-400 dark:text-gray-600 bg-gray-50 dark:bg-white/[0.01] border border-dashed border-gray-200 dark:border-gray-800 rounded-xl font-bold">
                Please select a Managing Owner to generate the ledger statement.
            </div>
        @endif

    </x-common.component-card>
@endsection