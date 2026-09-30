@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Vouchers" />

    @if(session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @php
        $canCreate = auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('vouchers.create');
        $canEdit = auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('vouchers.edit');
        $canDelete = auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('vouchers.delete');
        $filterKeys = ['search', 'type', 'source', 'payment_account_id', 'start_date', 'end_date'];
        $badge = [
            'cash_received' => 'bg-emerald-100 text-emerald-700',
            'bank_received' => 'bg-blue-100 text-blue-700',
            'cash_paid' => 'bg-orange-100 text-orange-700',
            'bank_paid' => 'bg-red-100 text-red-700',
        ];
        $sourceBadge = [
            'legacy' => 'bg-gray-100 text-gray-600',
            'stock_entry' => 'bg-purple-100 text-purple-700',
            'move_out' => 'bg-amber-100 text-amber-700',
        ];
    @endphp

    <x-common.component-card title="" desc="">
        @if($canCreate)
            <div class="flex flex-wrap items-center justify-center gap-3">
                @foreach(\App\Models\Voucher::TYPES as $key => $label)
                    <a href="{{ route('vouchers.create', ['type' => $key]) }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-600 transition-all shadow-md">
                        + {{ $label }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Filters --}}
        <div class="my-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white">🔍 Search & Filter Vouchers</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('vouchers.print-list', request()->query()) }}" target="_blank"
                        class="rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-600">🖨️ Print List</a>
                    @if(request()->anyFilled($filterKeys))
                        <a href="{{ route('vouchers.index') }}"
                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300">Clear Filters</a>
                    @endif
                </div>
            </div>
            <form action="{{ route('vouchers.index') }}" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-7">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Voucher no, manual no, reference, narration"
                    class="lg:col-span-2 h-11 rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:text-white/90">
                <select name="type" class="h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All Types</option>
                    @foreach(\App\Models\Voucher::TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="source" class="h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All Sources</option>
                    @foreach(\App\Models\Voucher::SOURCES as $key => $label)
                        <option value="{{ $key }}" @selected(request('source') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="payment_account_id" class="h-11 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All Accounts</option>
                    @foreach($paymentAccounts as $account)
                        <option value="{{ $account->id }}" @selected((string) request('payment_account_id') === (string) $account->id)>{{ $account->name }}</option>
                    @endforeach
                </select>
                <input type="text" id="start_date" name="start_date" value="{{ request('start_date') }}" placeholder="Date From" autocomplete="off"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 cursor-pointer">
                <input type="text" id="end_date" name="end_date" value="{{ request('end_date') }}" placeholder="Date To" autocomplete="off"
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 cursor-pointer">
                <div class="lg:col-span-7 flex justify-end">
                    <button type="submit" class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-600 cursor-pointer">Apply</button>
                </div>
            </form>
        </div>

        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-900/10">
                <p class="text-xs font-bold uppercase text-emerald-700">Total Received</p>
                <p class="text-xl font-black font-mono text-emerald-800 dark:text-emerald-400">Rs. {{ number_format($totals['received']) }}</p>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-900/10">
                <p class="text-xs font-bold uppercase text-red-700">Total Paid</p>
                <p class="text-xl font-black font-mono text-red-800 dark:text-red-400">Rs. {{ number_format($totals['paid']) }}</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/[0.03]">
                    <tr class="text-left text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                        <th class="px-4 py-3">Voucher No</th>
                        <th class="px-4 py-3">Manual No</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Account</th>
                        <th class="px-4 py-3">Narration</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $v)
                        <tr class="border-t border-gray-100 dark:border-gray-800 text-gray-800 dark:text-white/90">
                            <td class="px-4 py-3 font-mono font-bold">
                                <a href="{{ route('vouchers.print', $v) }}" class="text-brand-600 hover:underline">{{ $v->voucher_no }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $v->manual_voucher_no ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $v->date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $badge[$v->type] ?? '' }}">{{ $v->type_label }}</span>
                                @if($v->source !== 'form')
                                    <span class="ml-1 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $sourceBadge[$v->source] ?? '' }}">{{ $v->source_label }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $v->paymentAccount?->name ?? '—' }}</td>
                            <td class="px-4 py-3 max-w-xs truncate">{{ $v->narration ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold">{{ number_format($v->total_amount) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('vouchers.print', $v) }}" class="text-xs font-bold text-blue-600 hover:underline">Print</a>
                                @if($canEdit && !$v->isLocked())
                                    <a href="{{ route('vouchers.edit', $v) }}" class="ml-3 text-xs font-bold text-amber-600 hover:underline">Edit</a>
                                @endif
                                @if($canDelete && !$v->isLocked())
                                    <form action="{{ route('vouchers.destroy', $v) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Delete voucher {{ $v->voucher_no }}? All its entries will be reversed.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-3 text-xs font-bold text-red-600 hover:underline cursor-pointer">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-500">No vouchers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $vouchers->links() }}</div>
    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr === 'undefined') return;

            const options = { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd M Y', allowInput: true, disableMobile: true };
            const from = flatpickr('#start_date', {
                ...options,
                onChange: ([date]) => to.set('minDate', date || null),
            });
            const to = flatpickr('#end_date', {
                ...options,
                minDate: @js(request('start_date')) || null,
                onChange: ([date]) => from.set('maxDate', date || null),
            });
            if (@js(request('end_date'))) from.set('maxDate', @js(request('end_date')));
        });
    </script>
@endpush
