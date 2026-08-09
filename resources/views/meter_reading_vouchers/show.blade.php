@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Meter Reading Voucher Overview" />

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Action Bar --}}
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div>
                <h2 class="text-lg font-black text-gray-900 dark:text-white">Meter Reading Voucher #{{ $voucher->voucher_no }}</h2>
                <p class="text-xs text-gray-500">GEPCO Bill Detail & Meter Photo</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('meter-reading-vouchers.print', $voucher) }}" target="_blank"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-xs font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors">
                    🖨️ Print Voucher
                </a>
                @if(auth()->user()->hasPermission('meter_vouchers.edit') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('meter-reading-vouchers.edit', $voucher) }}"
                        class="rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-2 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        Edit
                    </a>
                @endif
                <a href="{{ route('meter-reading-vouchers.index') }}"
                    class="rounded-xl border border-gray-300 dark:border-gray-700 px-4 py-2 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Back to List
                </a>
            </div>
        </div>

        {{-- Details Card --}}
        <x-common.component-card title="Voucher Summary Information" desc="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Voucher & Date --}}
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-gray-400">Voucher Number</p>
                        <p class="text-xl font-black font-mono text-gray-900 dark:text-white mt-0.5">{{ $voucher->voucher_no }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-gray-400">Bill / Voucher Date</p>
                        <p class="text-base font-bold font-mono text-gray-800 dark:text-gray-200 mt-0.5">
                            {{ $voucher->date ? $voucher->date->format('d M Y') : '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-gray-400">Bill Due Date</p>
                        <p class="text-base font-bold font-mono text-gray-800 dark:text-gray-200 mt-0.5">
                            {{ $voucher->due_date ? $voucher->due_date->format('d M Y') : '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-gray-400">Flat / Shop</p>
                        <div class="mt-1">
                            @if($voucher->unit)
                                <span class="unit-badge-lg text-sm px-3 py-1 font-black">
                                    {{ $voucher->unit->unit_number }}
                                </span>
                                <span class="ml-2 text-xs font-bold text-gray-500">
                                    ({{ $voucher->unit->floor?->name ?? '—' }} / {{ $voucher->unit->block?->name ?? '—' }})
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-gray-400">Tenant Name</p>
                        <p class="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                            {{ $voucher->unit?->tenant?->name ?? ($voucher->unit?->otherTenant?->name ?? 'Vacant / Self') }}
                        </p>
                    </div>
                </div>

                {{-- Financial & Meter Details --}}
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-gray-400">GEPCO Meter Reference #</p>
                        <p class="text-lg font-black font-mono text-brand-600 dark:text-brand-400 mt-0.5">
                            {{ $voucher->meter_ref_no ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-gray-400">Current Meter Reading (kWh)</p>
                        <p class="text-lg font-black font-mono text-gray-900 dark:text-white mt-0.5">
                            {{ $voucher->current_reading ? number_format($voucher->current_reading, 2) . ' kWh' : '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-gray-400">GEPCO Bill Amount</p>
                        <p class="text-2xl font-black font-mono text-gray-900 dark:text-white mt-0.5">
                            Rs. {{ number_format($voucher->amount, 2) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-wider text-gray-400">Payment Status</p>
                        <div class="mt-1">
                            @if($voucher->status === 'paid')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300">
                                    PAID
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase bg-rose-100 text-rose-800 border border-rose-300 dark:bg-rose-950/60 dark:text-rose-300">
                                    UNPAID
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Meter Photo Preview --}}
                @if($voucher->meter_image_url)
                    <div class="md:col-span-2 border-t border-gray-200 dark:border-gray-800 pt-4">
                        <p class="text-xs font-black uppercase tracking-wider text-gray-400 mb-2">Meter / Bill Photo</p>
                        <a href="{{ $voucher->meter_image_url }}" target="_blank" class="inline-block">
                            <img src="{{ $voucher->meter_image_url }}" alt="Meter Photo" class="max-h-72 rounded-2xl border border-gray-300 dark:border-gray-700 shadow-md object-contain hover:opacity-95 transition-opacity" />
                        </a>
                    </div>
                @endif

                {{-- Notes --}}
                @if($voucher->notes)
                    <div class="md:col-span-2 border-t border-gray-200 dark:border-gray-800 pt-4">
                        <p class="text-xs font-black uppercase tracking-wider text-gray-400">Notes / Remarks</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 mt-1 whitespace-pre-line">{{ $voucher->notes }}</p>
                    </div>
                @endif

                {{-- Admin audit metadata --}}
                <div class="md:col-span-2 border-t border-gray-200 dark:border-gray-800 pt-4 flex items-center justify-between text-xs text-gray-400">
                    <p>Created by: <strong class="text-gray-600 dark:text-gray-300">{{ $voucher->user?->name ?? 'System Admin' }}</strong></p>
                    <p>Created at: {{ $voucher->created_at?->format('d M Y, h:i A') }}</p>
                </div>

            </div>
        </x-common.component-card>
    </div>
@endsection
