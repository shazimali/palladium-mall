@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="General Receiving Vouchers" />

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <x-common.component-card title="General Cash/Bank Receipts" desc="Manage general receiving vouchers recorded from parties">

        {{-- Top bar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2 items-center">
                <span
                    class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Total: {{ $vouchers->total() }} Vouchers
                </span>
                <span
                    class="inline-flex items-center rounded-lg bg-green-50 dark:bg-green-950/20 px-3 py-1 text-xs font-semibold text-green-700 dark:text-green-400">
                    Total Collected: Rs. {{ number_format($totalAmount, 0) }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'party_id', 'payment_account_id', 'start_date', 'end_date']))
                    <a href="{{ route('general-receiving-vouchers.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Clear
                    </a>
                @endif
                @if(auth()->user()->hasPermission('general_receiving_vouchers.create') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('general-receiving-vouchers.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        New General Receipt
                    </a>
                @endif
            </div>
        </div>

        <!-- Filters & Search -->
        <div
            class="my-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('general-receiving-vouchers.index') }}" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">

                <!-- Search Input -->
                <div class="relative col-span-1 lg:col-span-2">
                    <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search voucher #, ref..."
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <!-- Party Filter -->
                <div>
                    <select name="party_id" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Parties</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->id }}" {{ request('party_id') == $party->id ? 'selected' : '' }}>
                                {{ $party->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Account Filter -->
                <div>
                    <select name="payment_account_id" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Accounts</option>
                        @foreach($paymentAccounts as $account)
                            <option value="{{ $account->id }}" {{ request('payment_account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Picker Fields -->
                <div class="flex items-center gap-2">
                    <input type="text" id="start_date" name="start_date" value="{{ request('start_date') }}" placeholder="Date From" autocomplete="off"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-xs text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    <span class="text-xs text-gray-400">to</span>
                    <input type="text" id="end_date" name="end_date" value="{{ request('end_date') }}" placeholder="Date To" autocomplete="off"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-xs text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <button type="submit" class="hidden">Submit</button>
            </form>
        </div>

        {{-- DataTable --}}
        <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Voucher #</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Party Head</th>
                        <th class="px-4 py-3">Payment Account</th>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($vouchers as $voucher)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 font-mono font-semibold text-gray-800 dark:text-white/90">
                                {{ $voucher->voucher_no }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                {{ $voucher->date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 font-semibold">
                                {{ $voucher->party ? $voucher->party->name : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                {{ $voucher->paymentAccount ? $voucher->paymentAccount->name : 'N/A' }}
                                <span class="ml-1 text-[10px] text-gray-400 uppercase">({{ $voucher->payment_method }})</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $voucher->reference ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white font-mono">
                                Rs. {{ number_format($voucher->amount, 0) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('general-receiving-vouchers.show', $voucher) }}"
                                        class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                        View
                                    </a>
                                    <a href="{{ route('general-receiving-vouchers.print', $voucher) }}"
                                        onclick="window.open(this.href,'_blank','width=800,height=800,scrollbars=yes'); return false;"
                                        class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                        Print
                                    </a>
                                    @if(auth()->user()->isSuperAdmin())
                                        <a href="{{ route('general-receiving-vouchers.edit', $voucher) }}"
                                            class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-100 dark:border-blue-900/30 dark:bg-blue-950/20 dark:text-blue-400 dark:hover:bg-blue-900/30 transition-colors">
                                            Edit
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('general_receiving_vouchers.delete') || auth()->user()->isSuperAdmin())
                                        <form action="{{ route('general-receiving-vouchers.destroy', $voucher) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this general receiving voucher?');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 dark:border-red-900/30 dark:bg-red-950/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-gray-600">
                                No general receiving vouchers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $vouchers->links() }}
        </div>

    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#start_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true
                });

                flatpickr('#end_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true
                });
            }
        });
    </script>
@endpush
