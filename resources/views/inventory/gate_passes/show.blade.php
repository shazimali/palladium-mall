@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-6">
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('gate-passes.index') }}" class="hover:text-brand-500">Gate Passes</a>
                <span>/</span>
                <span class="text-gray-800 dark:text-white/90">{{ $gatePass->gatepass_no }}</span>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('gate-passes.print', $gatePass) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600 transition-colors shadow-sm">
                    🖨️ Print Pass
                </a>
                <a href="{{ route('gate-passes.index') }}"
                   class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-750 transition-colors">
                    Back to List
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            {{-- Main Details --}}
            <div class="md:col-span-2 space-y-6">
                <x-common.component-card title="Gate Pass Details — {{ $gatePass->gatepass_no }}" desc="Material checkout authorization permit issued to mall maintenance staff.">
                    <div class="grid grid-cols-2 gap-y-4 gap-x-2 text-sm border-b border-gray-100 pb-5 dark:border-gray-800">
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">Permit Date</span>
                            <span class="font-medium text-gray-800 dark:text-white">{{ $gatePass->date->format('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">Status</span>
                            @if($gatePass->status === 'Issued')
                                <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400">
                                    Issued / Out
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-950/20 dark:text-rose-400">
                                    Cancelled
                                </span>
                            @endif
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">Issued To (Recipient)</span>
                            <span class="font-semibold text-gray-800 dark:text-white">{{ $gatePass->issued_to }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">Flat / Shop Location</span>
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $gatePass->unit ? 'Flat/Shop: ' . $gatePass->unit->unit_number : 'Common Area / Mall General' }}
                            </span>
                        </div>
                        <div class="col-span-2">
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-0.5">Purpose / Description of work</span>
                            <span class="font-medium text-gray-800 dark:text-white">{{ $gatePass->purpose }}</span>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="pt-4 text-sm">
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Additional Notes / Remarks</span>
                        <div class="text-gray-800 dark:text-white bg-gray-50/50 dark:bg-white/[0.01] p-3 rounded-lg border border-gray-100 dark:border-gray-800 min-h-[60px]">
                            {{ $gatePass->notes ?? 'No additional remarks recorded.' }}
                        </div>
                    </div>
                </x-common.component-card>

                {{-- Dispatched Items List --}}
                <x-common.component-card title="Dispatched Inventory Items" desc="Maintenance items checked out under this gate pass">
                    <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800">
                                <tr>
                                    <th class="px-5 py-3">Item Details</th>
                                    <th class="px-5 py-3 text-right">Quantity Dispatched</th>
                                    <th class="px-5 py-3">Remarks / Usage Location</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                                @foreach($gatePass->items as $item)
                                    <tr class="align-middle">
                                        <td class="px-5 py-3.5">
                                            <div class="font-semibold text-gray-900 dark:text-white">{{ $item->inventoryItem->name ?? 'Deleted Item' }}</div>
                                            <div class="text-xs text-gray-450 dark:text-gray-500 font-mono mt-0.5">Code: {{ $item->inventoryItem->code ?? '—' }}</div>
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-mono font-bold text-brand-600 dark:text-brand-400">
                                            {{ number_format($item->quantity, 2) }} {{ $item->inventoryItem->unit_of_measure ?? '' }}
                                        </td>
                                        <td class="px-5 py-3.5 text-xs text-gray-500">
                                            {{ $item->notes ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-common.component-card>
            </div>

            {{-- Audit Widget Sidebar --}}
            <div class="space-y-6">
                {{-- User Audit details --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Audit Details</h3>
                    <div class="space-y-3.5 text-xs">
                        <div>
                            <span class="block text-gray-400 mb-0.5">Issued By</span>
                            <span class="font-semibold text-gray-800 dark:text-white">{{ $gatePass->user->name ?? 'System' }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-400 mb-0.5">Created At</span>
                            <span class="font-semibold text-gray-800 dark:text-white font-mono">{{ $gatePass->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Cancel & Delete Actions --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Controls</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                        Saved gatepass dispatches cannot be edited. You can cancel or delete this pass, which will restore the quantities to stock.
                    </p>

                    @if($gatePass->status === 'Issued' && (auth()->user()->hasPermission('gatepasses.manage') || auth()->user()->isSuperAdmin()))
                        <form action="{{ route('gate-passes.cancel', $gatePass) }}" method="POST" x-data
                              @submit.prevent="if(confirm('Are you sure you want to CANCEL this Gate Pass? All item quantities will be returned to stock.')) $el.submit()">
                            @csrf
                            <button type="submit" 
                                    class="w-full text-center rounded-lg border border-amber-300 bg-amber-50 py-2.5 text-xs font-semibold text-amber-700 hover:bg-amber-100 transition-colors dark:border-amber-950/20 dark:bg-amber-950/10 dark:text-amber-400">
                                🚫 Cancel & Return Stock
                            </button>
                        </form>
                    @endif

                    @if(auth()->user()->hasPermission('gatepasses.manage') || auth()->user()->isSuperAdmin())
                        <form action="{{ route('gate-passes.destroy', $gatePass) }}" method="POST" x-data
                              @submit.prevent="if(confirm('Are you sure you want to DELETE this Gate Pass? This will rollback stock levels.')) $el.submit()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full text-center rounded-lg border border-red-200 bg-red-50 py-2.5 text-xs font-semibold text-red-600 hover:bg-red-100 transition-colors dark:border-red-950/20 dark:bg-red-950/10 dark:text-red-400">
                                🗑️ Delete Gate Pass
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
