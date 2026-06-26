@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-6">
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('stock-entries.index') }}" class="hover:text-brand-500">Stock Inflows</a>
                <span>/</span>
                <span class="text-gray-800 dark:text-white/90">{{ $entry->entry_no }}</span>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('stock-entries.index') }}"
                   class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-750 transition-colors">
                    Back to List
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            {{-- Main Details --}}
            <div class="md:col-span-2 space-y-6">
                <x-common.component-card title="Stock Entry Detail — {{ $entry->entry_no }}" desc="Chronological receipt of materials checked-in to mall stock inventory.">
                    <div class="grid grid-cols-2 gap-y-4 gap-x-2 text-sm border-b border-gray-100 pb-5 dark:border-gray-800">
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">Transaction Date</span>
                            <span class="font-medium text-gray-800 dark:text-white">{{ $entry->date->format('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">Entry Type</span>
                            @if($entry->type === 'IN')
                                <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400">
                                    🟢 Stock Inflow
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-950/20 dark:text-amber-400">
                                    ⚙️ Adjustment
                                </span>
                            @endif
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">Payment Account</span>
                            <span class="font-semibold text-gray-800 dark:text-white">{{ $entry->paymentAccount->name ?? 'None (No Cash Flow)' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">Recorded By</span>
                            <span class="font-medium text-gray-800 dark:text-white">{{ $entry->user->name ?? 'System' }}</span>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="pt-4 text-sm">
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Notes / Particulars</span>
                        <div class="text-gray-800 dark:text-white bg-gray-50/50 dark:bg-white/[0.01] p-3 rounded-lg border border-gray-100 dark:border-gray-800 min-h-[60px]">
                            {{ $entry->notes ?? 'No particulars recorded.' }}
                        </div>
                    </div>
                </x-common.component-card>

                {{-- Line Items Card --}}
                <x-common.component-card title="Line Items" desc="Inventory materials checked-in under this record">
                    <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800">
                                <tr>
                                    <th class="px-5 py-3">Item Details</th>
                                    <th class="px-5 py-3 text-right">Quantity</th>
                                    <th class="px-5 py-3 text-right">Unit Price</th>
                                    <th class="px-5 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                                @php $grandTotal = 0.00; @endphp
                                @foreach($entry->items as $item)
                                    @php
                                        $subtotal = $item->quantity * $item->unit_price;
                                        $grandTotal += $subtotal;
                                    @endphp
                                    <tr class="align-middle">
                                        <td class="px-5 py-3.5">
                                            <div class="font-semibold text-gray-900 dark:text-white">{{ $item->inventoryItem->name ?? 'Deleted Item' }}</div>
                                            <div class="text-xs text-gray-450 dark:text-gray-500 font-mono mt-0.5">Code: {{ $item->inventoryItem->code ?? '—' }}</div>
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-mono font-medium">
                                            {{ number_format($item->quantity, 2) }} {{ $item->inventoryItem->unit_of_measure ?? '' }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-mono">
                                            Rs. {{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-mono font-bold text-gray-900 dark:text-white">
                                            Rs. {{ number_format($subtotal, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50/50 dark:bg-white/[0.01]">
                                    <td colspan="3" class="px-5 py-4 font-bold text-right text-gray-500 uppercase tracking-wider text-xs">Grand Total:</td>
                                    <td class="px-5 py-4 text-right font-mono font-extrabold text-brand-600 dark:text-brand-400 text-base">
                                        Rs. {{ number_format($grandTotal, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </x-common.component-card>
            </div>

            {{-- Sidebar Status Widget --}}
            <div class="space-y-6">
                {{-- Financial Impact --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Financial Integration</h3>

                    @if($entry->expense)
                        <div class="space-y-3.5">
                            <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 text-sm font-semibold">
                                <span>✔️ Cash Outflow Logged</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                A linked operational expense voucher was automatically generated for this purchase inflow.
                            </p>
                            <div class="border-t border-gray-100 pt-3.5 dark:border-gray-800">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Expense Voucher Reference</span>
                                <a href="{{ route('expenses.show', $entry->expense_id) }}" 
                                   class="inline-flex items-center gap-1.5 mt-1.5 text-sm font-mono font-bold text-brand-500 hover:underline">
                                    📄 {{ $entry->expense->voucher_no }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="space-y-3.5">
                            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 text-sm font-semibold">
                                <span>⚙️ No Financial Impact</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Recorded as a manual adjustment/opening balance. No cash outflow or expense voucher was generated.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Operational Rollbacks --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Audit Controls</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-4">
                        To maintain strict material and financial auditing logs, saved stock entry vouchers cannot be edited.
                    </p>

                    @if(auth()->user()->hasPermission('inventory.manage') || auth()->user()->isSuperAdmin())
                        <form action="{{ route('stock-entries.destroy', $entry) }}" method="POST" x-data
                              @submit.prevent="if(confirm('Are you sure you want to delete this stock entry? This will rollback item quantities and delete any linked purchase expense voucher.')) $el.submit()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full text-center rounded-lg border border-red-200 bg-red-50 py-2.5 text-xs font-semibold text-red-600 hover:bg-red-100 transition-colors dark:border-red-950/20 dark:bg-red-950/10 dark:text-red-400">
                                🗑️ Delete & Rollback Stock
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
