@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ $title }}" />

    <div x-data="utilityApp({
        readings: @js($readings),
        month: '{{ $selectedMonth }}',
        isSuperAdmin: {{ $isSuperAdmin ? 'true' : 'false' }},
        canEdit: {{ $canEdit ? 'true' : 'false' }}
    })" class="relative">

        {{-- Sticky Filters & Actions Bar --}}
        <div class="sticky top-16 z-30 mb-6 rounded-2xl border border-gray-200 bg-white/95 p-4 shadow-md backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/95">
            <form id="utilityFilterForm" method="GET" action="{{ route('utility-readings.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                
                {{-- Month Filter (Flatpickr Date Picker enabled) --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">
                        📅 Filter Month <span class="text-brand-500">*</span>
                    </label>
                    <input type="text" id="month_filter" name="month" value="{{ $selectedMonth }}" placeholder="Select Month"
                        class="w-full h-11 px-3 text-xs font-bold bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20 cursor-pointer">
                </div>

                {{-- Flat/Shop Filter --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">
                        🏢 Flat / Shop
                    </label>
                    <select name="unit_id" onchange="this.form.submit()"
                        class="w-full h-11 px-3 text-xs font-bold bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Flats / Shops</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ $selectedUnitId == $u->id ? 'selected' : '' }}>
                                {{ $u->unit_number }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Meter Type Filter --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">
                        ⚡ Meter Type
                    </label>
                    <select name="type" onchange="this.form.submit()"
                        class="w-full h-11 px-3 text-xs font-bold bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Meter Types</option>
                        <option value="electricity" {{ $selectedType === 'electricity' ? 'selected' : '' }}>⚡ Electricity</option>
                        <option value="water" {{ $selectedType === 'water' ? 'selected' : '' }}>💧 Water</option>
                        <option value="gas" {{ $selectedType === 'gas' ? 'selected' : '' }}>🔥 Gas</option>
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">
                        Status
                    </label>
                    <select name="status" onchange="this.form.submit()"
                        class="w-full h-11 px-3 text-xs font-bold bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Statuses</option>
                        <option value="paid" {{ $selectedStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ $selectedStatus === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                {{-- Search Input --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">
                        Search Ref / ID
                    </label>
                    <input type="text" name="search" value="{{ $searchTerm }}" placeholder="Search Ref / ID..."
                        class="w-full h-11 px-3 text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>

                {{-- Actions: Filter, Bulk Entry & Print --}}
                <div class="flex items-center gap-1.5">
                    <button type="submit" class="h-11 px-3.5 rounded-xl bg-brand-500 text-white font-bold text-[11px] hover:bg-brand-600 transition-colors flex items-center justify-center">
                        Filter
                    </button>
                    @if($canEdit)
                        <button type="button" x-on:click="openBulkModal()"
                            class="h-11 px-3.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-[11px] flex items-center justify-center gap-1 shadow-xs transition-colors whitespace-nowrap" title="Add all meter readings of a month at once">
                            📋 Bulk Entry
                        </button>
                    @endif
                    <a href="{{ route('utility-readings.print', request()->query()) }}" target="_blank"
                        class="h-11 px-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-[11px] flex items-center justify-center gap-1 shadow-xs transition-colors" title="Print Report">
                        🖨️ Print
                    </a>
                </div>

            </form>
        </div>

        {{-- Floating Toast --}}
        <div x-show="toastShow" x-transition
            class="fixed bottom-6 right-6 z-100000 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl border text-sm font-bold text-white"
            :class="toastType === 'success' ? 'bg-emerald-600 border-emerald-500' : 'bg-rose-600 border-rose-500'"
            style="display: none;">
            <span x-text="toastType === 'success' ? '✅' : '⚠️'"></span>
            <span x-text="toastMessage"></span>
        </div>

        {{-- Meter Readings Table Directory --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white">
                        Monthly Meter Readings Directory (<span x-text="readings.length"></span> Items)
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Listing all Flat/Shop meters for month: <span class="font-bold text-brand-600 dark:text-brand-400">{{ $selectedMonthName }}</span>
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-[11px]">
                    <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-600 dark:text-gray-400 uppercase tracking-wider font-extrabold text-[11px] border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th class="py-3.5 px-4 text-center">Meter Image</th>
                            <th class="py-3.5 px-4">Flat / Shop</th>
                            <th class="py-3.5 px-4">Ref Number</th>
                            <th class="py-3.5 px-4">Consumer ID</th>
                            <th class="py-3.5 px-4 text-right">Prev Reading</th>
                            <th class="py-3.5 px-4 text-right">Meter Reading</th>
                            <th class="py-3.5 px-4 text-right">Units Consumed</th>
                            <th class="py-3.5 px-4 text-center">Available</th>
                            <th class="py-3.5 px-4 text-right">Bill Amount (Rs.)</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">Bill Gen. Date</th>
                            <th class="py-3.5 px-4 text-center">Due Date</th>
                            <th class="py-3.5 px-4 text-center">Meter Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200 font-semibold">
                        <template x-for="(row, index) in readings" :key="row.meter_id">
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors">
                                
                                {{-- Meter Image Column --}}
                                <td class="py-3.5 px-4 text-center">
                                    <template x-if="row.meter_image_url">
                                        <div class="relative group cursor-pointer inline-block" x-on:click="openImagePreview(row)">
                                            <img :src="row.meter_image_url" alt="Meter Photo" class="h-10 w-10 rounded-xl object-cover border-2 border-brand-300 dark:border-brand-800 shadow-xs">
                                            <div class="absolute inset-0 bg-black/40 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[11px] transition-opacity">
                                                🔍
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="!row.meter_image_url">
                                        <span class="inline-flex h-10 w-10 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 items-center justify-center text-gray-400 text-[11px]" title="No photo uploaded">
                                            📷
                                        </span>
                                    </template>
                                </td>

                                {{-- Flat/Shop Column --}}
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a :href="'/units/' + row.unit_id" class="inline-block hover:opacity-90 transition-opacity">
                                            <span class="unit-badge-lg text-xs px-2.5 py-0.5 font-black" x-text="row.unit_number"></span>
                                        </a>
                                        <template x-if="row.meter_type === 'electricity'">
                                            <span class="inline-flex items-center gap-0.5 rounded-md bg-amber-50 px-1.5 py-0.5 text-[9px] font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40" title="Electricity Meter">
                                                ⚡ Elect
                                            </span>
                                        </template>
                                        <template x-if="row.meter_type === 'water'">
                                            <span class="inline-flex items-center gap-0.5 rounded-md bg-blue-50 px-1.5 py-0.5 text-[9px] font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40" title="Water Meter">
                                                💧 Water
                                            </span>
                                        </template>
                                        <template x-if="row.meter_type === 'gas'">
                                            <span class="inline-flex items-center gap-0.5 rounded-md bg-rose-50 px-1.5 py-0.5 text-[9px] font-bold text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40" title="Gas Meter">
                                                🔥 Gas
                                            </span>
                                        </template>
                                    </div>
                                    <span class="text-[11px] text-gray-400 font-medium block mt-1" x-text="row.floor + (row.block ? ' • ' + row.block : '')"></span>
                                </td>

                                {{-- Ref Number Column --}}
                                <td class="py-3.5 px-4 font-mono text-[11px] text-gray-600 dark:text-gray-400" x-text="row.meter_ref_no"></td>

                                {{-- Consumer ID Column --}}
                                <td class="py-3.5 px-4 font-mono text-[11px] text-gray-600 dark:text-gray-400" x-text="row.meter_consumer_id"></td>

                                {{-- Prev Reading Column --}}
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-[11px] text-gray-600 dark:text-gray-400">
                                    <span class="inline-block bg-gray-100 dark:bg-gray-800/80 px-2.5 py-1 rounded-lg border border-gray-200 dark:border-gray-700"
                                        x-text="parseFloat(row.previous_reading || 0).toFixed(2)"></span>
                                </td>

                                {{-- Meter Reading Column --}}
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-[11px] text-gray-900 dark:text-white">
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
                                        x-text="parseFloat(row.current_reading || 0).toFixed(2)"></span>
                                </td>

                                {{-- Units Consumed Column --}}
                                <td class="py-3.5 px-4 text-right font-mono font-black text-[11px] text-indigo-600 dark:text-indigo-400">
                                    <span class="inline-block bg-indigo-50 dark:bg-indigo-950/40 px-2.5 py-1 rounded-lg border border-indigo-200 dark:border-indigo-800"
                                        x-text="((parseFloat(row.current_reading || 0) > 0 && parseFloat(row.current_reading || 0) >= parseFloat(row.previous_reading || 0)) ? (parseFloat(row.current_reading) - parseFloat(row.previous_reading || 0)) : 0).toFixed(2)"></span>
                                </td>

                                {{-- Available Column --}}
                                <td class="py-3.5 px-4 text-center">
                                    <template x-if="row.available">
                                        <span class="inline-flex items-center px-2.5 py-0.5 text-[11px] font-bold rounded-lg bg-blue-50 border border-blue-200 text-blue-700 dark:bg-blue-950/40 dark:border-blue-800 dark:text-blue-300"
                                            x-text="row.available"></span>
                                    </template>
                                    <template x-if="!row.available">
                                        <span class="text-[11px] text-gray-400 font-semibold">—</span>
                                    </template>
                                </td>

                                {{-- Bill Amount Column --}}
                                <td class="py-3.5 px-4 text-right font-mono font-extrabold text-[11px] text-gray-900 dark:text-white">
                                    <span x-text="'Rs. ' + parseFloat(row.amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                </td>

                                {{-- Status Column --}}
                                <td class="py-3.5 px-4 text-center">
                                    <template x-if="row.status === 'paid'">
                                        <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-extrabold uppercase rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-700 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300">
                                            Paid
                                        </span>
                                    </template>
                                    <template x-if="row.status === 'unpaid'">
                                        <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-extrabold uppercase rounded-lg bg-rose-50 border border-rose-300 text-rose-700 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300">
                                            Unpaid
                                        </span>
                                    </template>
                                    <template x-if="row.status === 'pending'">
                                        <span class="inline-flex items-center px-2.5 py-1 text-[11px] font-extrabold uppercase rounded-lg bg-amber-50 border border-amber-300 text-amber-700 dark:bg-amber-950/40 dark:border-amber-800 dark:text-amber-300">
                                            Pending
                                        </span>
                                    </template>
                                </td>

                                {{-- Bill Generate Date Column --}}
                                <td class="py-3.5 px-4 text-center">
                                    <template x-if="row.bill_generate_date_label">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300"
                                            x-text="row.bill_generate_date_label"></span>
                                    </template>
                                    <template x-if="!row.bill_generate_date_label">
                                        <span class="text-[11px] text-gray-400 font-semibold">—</span>
                                    </template>
                                </td>

                                {{-- Due Date Column --}}
                                <td class="py-3.5 px-4 text-center">
                                    <template x-if="row.due_date_label">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300"
                                            x-text="row.due_date_label"></span>
                                    </template>
                                    <template x-if="!row.due_date_label">
                                        <span class="text-[11px] text-gray-400 font-semibold">—</span>
                                    </template>
                                </td>

                                {{-- Meter Status Column --}}
                                <td class="py-3.5 px-4 text-center">
                                    <template x-if="row.is_active">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-extrabold uppercase rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-700 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 inline-block"></span> Active
                                        </span>
                                    </template>
                                    <template x-if="!row.is_active">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-extrabold uppercase rounded-lg bg-gray-100 border border-gray-300 text-gray-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400 inline-block"></span> Inactive
                                        </span>
                                    </template>
                                </td>
                            </tr>
                        </template>

                        <template x-if="readings.length === 0">
                            <tr>
                                <td colspan="13" class="py-12 text-center text-gray-400 dark:text-gray-500">
                                    <p class="text-2xl mb-2">⚡</p>
                                    <p class="font-bold text-xs">No utility meters found matching your filter criteria.</p>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════ --}}
        {{-- EDIT METER READING MODAL --}}
        {{-- ══════════════════════════════════════════════════════ --}}
        <div x-show="modalOpen" x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center p-4"
            aria-modal="true" role="dialog">
            
            {{-- Backdrop --}}
            <div x-show="modalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 backdrop-blur-xs"
                x-on:click="closeEditModal()"></div>

            {{-- Modal Panel --}}
            <div x-show="modalOpen" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-4xl rounded-2xl bg-white p-5 sm:p-6 shadow-2xl border border-gray-200 dark:bg-gray-900 dark:border-gray-800 z-10 space-y-4 my-auto max-h-[95vh] overflow-y-auto">
                
                {{-- Header --}}
                <div class="flex items-start justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 text-xl font-bold shrink-0">
                            ⚡
                        </span>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-base font-extrabold text-gray-900 dark:text-white">
                                    Update Meter Reading
                                </h3>
                                <span class="unit-badge-lg text-xs px-2.5 py-0.5 font-black" x-text="modalForm.unit_number"></span>
                                <span class="text-xs font-bold text-gray-600 dark:text-gray-300" x-text="modalForm.meter_type_label"></span>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mt-1 flex-wrap">
                                <span>Ref: <strong class="font-mono text-gray-800 dark:text-gray-200" x-text="modalForm.meter_ref_no"></strong></span>
                                <span>•</span>
                                <span>Consumer ID: <strong class="font-mono text-gray-800 dark:text-gray-200" x-text="modalForm.meter_consumer_id"></strong></span>
                                <template x-if="modalForm.edited_by">
                                    <span class="text-brand-600 dark:text-brand-400 font-semibold">• Last by: <span x-text="modalForm.edited_by + (modalForm.last_updated ? ' (' + modalForm.last_updated + ')' : '')"></span></span>
                                </template>
                            </div>
                        </div>
                    </div>
                    <button type="button" x-on:click="closeEditModal()"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Form Fields in 2-Column Responsive Layout --}}
                <form x-on:submit.prevent="saveModalReading()" class="space-y-4 pt-1">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        {{-- Left Column: Readings & Billing --}}
                        <div class="space-y-3">
                            {{-- Readings Inputs (Prev Reading & Current Meter Reading) --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                            📊 Prev Reading
                                        </label>
                                        <span class="text-[9px] font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/30 px-1 rounded">
                                            Auto
                                        </span>
                                    </div>
                                    <input type="number" step="0.01" min="0" x-model="modalForm.previous_reading"
                                        :readonly="!isSuperAdmin"
                                        class="w-full h-10 px-3 font-mono font-bold text-sm bg-gray-100 dark:bg-gray-800/80 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:outline-none cursor-not-allowed readonly:opacity-90">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        ⚡ Meter Reading *
                                    </label>
                                    <input type="number" step="0.01" min="0" x-model="modalForm.current_reading" required
                                        placeholder="0.00"
                                        class="w-full h-10 px-3 font-mono font-bold text-sm bg-white dark:bg-gray-800 border border-brand-400 dark:border-brand-600 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:outline-none">
                                </div>
                            </div>

                            {{-- Live Units Consumed Preview Card --}}
                            <div class="rounded-xl border border-indigo-200 bg-indigo-50/70 dark:border-indigo-900/50 dark:bg-indigo-950/30 px-3.5 py-2 flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-bold text-indigo-900 dark:text-indigo-300 block">Units Consumed (Net):</span>
                                    <span class="text-[10px] text-indigo-700/80 dark:text-indigo-400 font-medium">
                                        Current (<span x-text="parseFloat(modalForm.current_reading || 0).toFixed(2)"></span>) — Prev (<span x-text="parseFloat(modalForm.previous_reading || 0).toFixed(2)"></span>)
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-base font-black font-mono text-indigo-600 dark:text-indigo-300"
                                        x-text="computedUnitsConsumed()"></span>
                                    <span class="text-xs font-bold text-indigo-900 dark:text-indigo-300 ml-0.5">Units</span>
                                </div>
                            </div>

                            {{-- Bill Amount & Status --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        💰 Bill Amount (Rs.)
                                    </label>
                                    <input type="number" step="0.01" min="0" x-model="modalForm.amount"
                                        placeholder="0.00"
                                        class="w-full h-10 px-3 font-mono font-extrabold text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        🏷️ Status *
                                        <span x-show="modalForm.original_status === 'paid' && !isSuperAdmin" class="text-amber-500 font-normal text-[9px]">
                                            (🔒 Locked)
                                        </span>
                                    </label>
                                    <select x-model="modalForm.status" required
                                        :disabled="modalForm.original_status === 'paid' && !isSuperAdmin"
                                        class="w-full h-10 px-3 text-xs font-bold bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed">
                                        <option value="unpaid">Unpaid</option>
                                        <option value="paid">Paid</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Available (Manual) & Bill Generate Date --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        📦 Available (Manual)
                                    </label>
                                    <input type="text" x-model="modalForm.available"
                                        placeholder="e.g. Yes, No, Available, etc."
                                        class="w-full h-10 px-3 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                        🧾 Bill Generate Date
                                    </label>
                                    <input type="text" id="modal_bill_generate_date" x-model="modalForm.bill_generate_date"
                                        autocomplete="off" placeholder="Select date"
                                        x-init="billDatePicker = flatpickr($el, {
                                            dateFormat: 'Y-m-d',
                                            allowInput: true,
                                            disableMobile: true,
                                            onChange: (selectedDates, dateStr) => { modalForm.bill_generate_date = dateStr; }
                                        })"
                                        class="w-full h-10 px-3 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:outline-none cursor-pointer">
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Photo Upload & Remarks --}}
                        <div class="space-y-3 flex flex-col justify-between">
                            {{-- Meter Photo Upload Inside Modal --}}
                            <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-3 dark:border-gray-800 dark:bg-gray-800/40">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5 flex items-center justify-between">
                                    <span>📷 Meter Photo (Max 200 KB)</span>
                                    <template x-if="modalForm.new_image_preview || modalForm.meter_image_url">
                                        <span class="text-[10px] text-brand-600 dark:text-brand-400 font-bold">Photo attached</span>
                                    </template>
                                </label>
                                <div class="flex items-center gap-3">
                                    {{-- Thumbnail / Preview --}}
                                    <template x-if="modalForm.new_image_preview || modalForm.meter_image_url">
                                        <div class="relative group cursor-pointer shrink-0" x-on:click="openImagePreview({meter_image_url: modalForm.new_image_preview || modalForm.meter_image_url, unit_number: modalForm.unit_number, meter_type_label: modalForm.meter_type_label})">
                                            <img :src="modalForm.new_image_preview || modalForm.meter_image_url" alt="Meter Photo" class="h-14 w-14 rounded-xl object-cover border-2 border-brand-300 dark:border-brand-700 shadow-xs">
                                            <div class="absolute inset-0 bg-black/40 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs transition-opacity">
                                                🔍
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!modalForm.new_image_preview && !modalForm.meter_image_url">
                                        <div class="h-14 w-14 rounded-xl bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-400 text-base shrink-0">
                                            📷
                                        </div>
                                    </template>

                                    <div class="flex-1 min-w-0">
                                        <input type="file" id="modal_meter_image_input" accept="image/*" x-on:change="handleModalFileChange($event)" class="hidden">
                                        <div class="flex items-center gap-2">
                                            <label for="modal_meter_image_input" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200 shadow-2xs transition-colors cursor-pointer">
                                                <span>📁 Choose Image</span>
                                            </label>
                                            <template x-if="modalForm.new_image_file">
                                                <button type="button" x-on:click="removeModalSelectedFile()" class="text-xs text-red-500 hover:underline font-semibold">
                                                    ✕ Remove
                                                </button>
                                            </template>
                                        </div>
                                        <span class="text-[10px] text-gray-400 block mt-1">JPEG, PNG, WEBP (Max 200 KB). Uploads when saved.</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="flex-1 flex flex-col">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    📝 Remarks / Notes (Optional)
                                </label>
                                <textarea x-model="modalForm.notes" rows="3" placeholder="Add any notes or details..."
                                    class="w-full flex-1 min-h-[75px] p-2.5 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:outline-none resize-none"></textarea>
                            </div>
                        </div>

                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" x-on:click="closeEditModal()"
                            class="px-4 py-2 text-xs font-bold rounded-xl border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" :disabled="savingModal"
                            class="inline-flex items-center gap-2 px-6 py-2 text-xs font-extrabold rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-md transition-colors disabled:opacity-50 cursor-pointer">
                            <span x-show="!savingModal">💾 Save Reading</span>
                            <span x-show="savingModal" class="flex items-center gap-1">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════ --}}
        {{-- BULK MONTHLY READINGS ENTRY MODAL --}}
        {{-- ══════════════════════════════════════════════════════ --}}
        @if($canEdit)
        <div x-show="bulkOpen" x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center p-4"
            aria-modal="true" role="dialog">

            {{-- Backdrop --}}
            <div x-show="bulkOpen" x-transition.opacity
                class="fixed inset-0 bg-black/60 backdrop-blur-xs"
                x-on:click="closeBulkModal()"></div>

            {{-- Modal Panel --}}
            <div x-show="bulkOpen" x-transition
                class="relative w-full max-w-7xl rounded-2xl bg-white p-5 sm:p-6 shadow-2xl border border-gray-200 dark:bg-gray-900 dark:border-gray-800 z-10 my-auto max-h-[95vh] flex flex-col gap-4">

                {{-- Header --}}
                <div class="flex items-start justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 text-xl font-bold shrink-0">
                            📋
                        </span>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Bulk Meter Readings Entry</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Enter readings for all meters of <span class="font-bold text-brand-600 dark:text-brand-400" x-text="bulkMonthName"></span>. Rows left blank are not saved. Press Enter to jump to the next meter.
                            </p>
                        </div>
                    </div>
                    <button type="button" x-on:click="closeBulkModal()" class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold flex items-center justify-center shrink-0">✕</button>
                </div>

                {{-- Controls --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">
                            📅 Month <span class="text-brand-500">*</span>
                        </label>
                        <input type="text" id="bulk_month" placeholder="Select Month"
                            x-init="initBulkMonthPicker($el)"
                            class="w-full h-11 px-3 text-xs sm:text-sm font-bold bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">⚡ Meter Type</label>
                        <select x-model="bulkType"
                            class="w-full h-11 px-3 text-xs sm:text-sm font-bold bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <option value="">All Meter Types</option>
                            <option value="electricity">⚡ Electricity</option>
                            <option value="water">💧 Water</option>
                            <option value="gas">🔥 Gas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">Search Flat / Ref</label>
                        <input type="text" x-model="bulkSearch" placeholder="Search..."
                            class="w-full h-11 px-3 text-xs sm:text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                    </div>
                </div>

                {{-- Errors --}}
                <div x-show="bulkErrors.length > 0" x-cloak
                    class="relative rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-700 dark:border-rose-800/50 dark:bg-rose-900/20 dark:text-rose-300">
                    <button type="button" x-on:click="bulkErrors = []" class="absolute top-2 right-3 font-bold" title="Dismiss">✕</button>
                    <p class="font-extrabold mb-1">⚠️ Please fix the following:</p>
                    <ul class="list-disc pl-5 space-y-0.5 max-h-24 overflow-y-auto">
                        <template x-for="(err, i) in bulkErrors" :key="i">
                            <li x-text="err"></li>
                        </template>
                    </ul>
                </div>

                {{-- Grid --}}
                <div class="flex-1 min-h-0 overflow-auto rounded-xl border border-gray-200 dark:border-gray-800">
                    <template x-if="bulkLoading">
                        <div class="p-10 text-center text-sm font-bold text-gray-500">Loading meters...</div>
                    </template>
                    <template x-if="!bulkLoading && bulkFilteredRows().length === 0">
                        <div class="p-10 text-center text-sm font-bold text-gray-500">No meters found.</div>
                    </template>
                    <table x-show="!bulkLoading && bulkFilteredRows().length > 0" class="w-full text-left text-xs sm:text-sm">
                        <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 uppercase tracking-wider font-extrabold text-[11px] border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="py-3 px-3">Flat / Shop</th>
                                <th class="py-3 px-3">Ref Number</th>
                                <th class="py-3 px-3 text-right">Prev Reading</th>
                                <th class="py-3 px-3 text-right">Meter Reading</th>
                                <th class="py-3 px-3 text-right">Units</th>
                                <th class="py-3 px-3 text-right">Bill Amount (Rs.)</th>
                                <th class="py-3 px-3 text-center">Status</th>
                                <th class="py-3 px-3 text-center">Bill Gen. Date</th>
                                <th class="py-3 px-3 text-center">Due Date</th>
                                <th class="py-3 px-3 text-center">Bill Image</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200 font-semibold">
                            <template x-for="row in bulkFilteredRows()" :key="row.meter_id">
                                <tr :class="row.locked ? 'bg-gray-50 dark:bg-gray-800/40 opacity-60' : (bulkRowWarning(row) ? 'bg-rose-50 dark:bg-rose-900/20' : '')">
                                    <td class="py-2 px-3">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="unit-badge-lg text-xs px-2 py-0.5 font-black" x-text="row.unit_number"></span>
                                            <span class="text-[10px] font-bold text-gray-500" x-text="row.meter_type_label"></span>
                                            <template x-if="!row.is_active">
                                                <span class="text-[10px] font-bold text-rose-600">Inactive</span>
                                            </template>
                                            <template x-if="row.locked">
                                                <span class="text-[10px] font-bold text-amber-600" title="Marked as Paid. Only Super Admin can change its reading and image.">🔒 Paid</span>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3 font-mono text-xs text-gray-600 dark:text-gray-400" x-text="row.meter_ref_no"></td>
                                    <td class="py-2 px-3 text-right">
                                        <input type="number" step="0.01" min="0" x-model="row.previous_reading" :disabled="row.locked"
                                            class="w-28 h-9 px-2 text-right font-mono text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                                    </td>
                                    <td class="py-2 px-3 text-right">
                                        <input type="number" step="0.01" min="0" x-model="row.current_reading" :disabled="row.locked" placeholder="—"
                                            data-bulk-current
                                            x-on:keydown.enter.prevent="focusNextBulkInput($event)"
                                            class="w-32 h-9 px-2 text-right font-mono text-xs font-bold bg-white dark:bg-gray-800 border border-brand-300 dark:border-brand-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/30 focus:outline-none">
                                    </td>
                                    <td class="py-2 px-3 text-right font-mono text-xs"
                                        :class="bulkRowWarning(row) ? 'text-rose-600 font-extrabold' : 'text-emerald-600'"
                                        :title="bulkRowWarning(row) ? 'Meter reading is lower than previous reading' : ''"
                                        x-text="bulkUnits(row)"></td>
                                    <td class="py-2 px-3 text-right">
                                        <input type="number" step="0.01" min="0" x-model="row.amount" :disabled="row.locked" placeholder="0"
                                            class="w-28 h-9 px-2 text-right font-mono text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        <select x-model="row.status" :disabled="row.locked"
                                            class="h-9 px-2 text-xs font-bold bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:outline-none">
                                            <option value="unpaid">Unpaid</option>
                                            <option value="pending">Pending</option>
                                            <option value="paid">Paid</option>
                                        </select>
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        <input type="text" x-model="row.bill_generate_date" :disabled="row.locked"
                                            autocomplete="off" placeholder="Select date"
                                            x-init="flatpickr($el, {
                                                dateFormat: 'Y-m-d',
                                                allowInput: true,
                                                disableMobile: true,
                                                defaultDate: row.bill_generate_date || null,
                                                onChange: (selectedDates, dateStr) => { row.bill_generate_date = dateStr; }
                                            })"
                                            class="w-28 h-9 px-2 text-xs cursor-pointer bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        <input type="text" x-model="row.due_date" :disabled="row.locked"
                                            autocomplete="off" placeholder="Select date"
                                            x-init="flatpickr($el, {
                                                dateFormat: 'Y-m-d',
                                                allowInput: true,
                                                disableMobile: true,
                                                defaultDate: row.due_date || null,
                                                onChange: (selectedDates, dateStr) => { row.due_date = dateStr; }
                                            })"
                                            class="w-28 h-9 px-2 text-xs cursor-pointer bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                                    </td>
                                    <td class="py-2 px-3">
                                        <div class="flex items-center justify-center gap-2">
                                            <template x-if="row.image_preview || row.meter_image_url">
                                                <div class="relative shrink-0">
                                                    <img :src="row.image_preview || row.meter_image_url" alt="Bill Image"
                                                        x-on:click="openImagePreview({meter_image_url: row.image_preview || row.meter_image_url, unit_number: row.unit_number, meter_type_label: row.meter_type_label})"
                                                        class="h-9 w-9 rounded-lg object-cover cursor-pointer border-2"
                                                        :class="row.image_file ? 'border-amber-400' : 'border-gray-200 dark:border-gray-700'"
                                                        :title="row.image_file ? 'New image (uploads when saved)' : 'Current image'">
                                                    <template x-if="row.image_file">
                                                        <button type="button" x-on:click="removeBulkImage(row)"
                                                            class="absolute -top-1.5 -right-1.5 h-4 w-4 rounded-full bg-rose-600 text-white text-[9px] font-bold flex items-center justify-center" title="Remove selected image">✕</button>
                                                    </template>
                                                </div>
                                            </template>
                                            <label x-show="!row.locked"
                                                class="inline-flex items-center gap-1 px-2 h-9 rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-[11px] font-bold text-gray-700 dark:text-gray-200 cursor-pointer whitespace-nowrap">
                                                📷 <span x-text="(row.image_file || row.meter_image_url) ? 'Change' : 'Upload'"></span>
                                                <input type="file" accept="image/*" class="hidden" x-on:change="handleBulkFileChange($event, row)">
                                            </label>
                                            <span x-show="row.locked" class="text-[10px] font-bold text-amber-600 whitespace-nowrap" title="Marked as Paid. Only Super Admin can change its reading and image.">🔒 Super Admin only</span>
                                        </div>
                                        <p x-show="row.image_error" x-text="row.image_error"
                                            class="mt-1 max-w-[180px] mx-auto text-center text-[10px] font-bold leading-tight text-rose-600 dark:text-rose-400"></p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Footer --}}
                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <p class="text-xs font-bold text-gray-600 dark:text-gray-400">
                        <span x-text="bulkFilledCount()"></span> of <span x-text="bulkRows.length"></span> meters have a reading entered
                        • <span x-text="bulkImageCount()"></span> new image(s) selected
                    </p>
                    <div class="flex items-center gap-3">
                        <button type="button" x-on:click="closeBulkModal()"
                            class="px-4 py-2 text-xs font-bold rounded-xl border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button type="button" x-on:click="saveBulkReadings()" :disabled="bulkSaving || bulkLoading || (bulkFilledCount() === 0 && bulkImageCount() === 0)"
                            class="inline-flex items-center gap-2 px-6 py-2 text-xs font-extrabold rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-md transition-colors disabled:opacity-50 cursor-pointer">
                            <span x-show="!bulkSaving">💾 Save All Readings</span>
                            <span x-show="bulkSaving" x-text="bulkProgress || 'Saving...'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════ --}}
        {{-- IMAGE PREVIEW LIGHTBOX MODAL --}}
        {{-- ══════════════════════════════════════════════════════ --}}
        <div x-show="previewModal" x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center bg-black/80 p-4"
            x-on:click.self="previewModal = false">
            <div class="relative max-w-lg w-full bg-white dark:bg-gray-900 rounded-3xl p-4 shadow-2xl">
                <button x-on:click="previewModal = false" class="absolute top-3 right-3 h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold flex items-center justify-center">✕</button>
                <h4 class="text-sm font-bold mb-3 text-gray-900 dark:text-white" x-text="previewTitle"></h4>
                <img :src="previewImageUrl" class="w-full h-auto max-h-[70vh] rounded-2xl object-contain">
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function utilityApp(config) {
            return {
                readings: config.readings || [],
                month: config.month,
                isSuperAdmin: config.isSuperAdmin,
                canEdit: config.canEdit,

                toastShow: false,
                toastMessage: '',
                toastType: 'success',
                showToast(msg, type = 'success') {
                    this.toastMessage = msg;
                    this.toastType = type;
                    this.toastShow = true;
                    setTimeout(() => { this.toastShow = false; }, 3500);
                },

                // Modal state
                modalOpen: false,
                savingModal: false,
                billDatePicker: null,
                modalForm: {
                    meter_id: null,
                    unit_number: '',
                    floor: '',
                    block: '',
                    meter_type_label: '',
                    meter_ref_no: '',
                    meter_consumer_id: '',
                    previous_reading: 0,
                    current_reading: '',
                    available: '',
                    amount: '',
                    status: 'unpaid',
                    original_status: '',
                    is_paid_locked: false,
                    notes: '',
                    bill_generate_date: '',
                    edited_by: '',
                    last_updated: '',
                    meter_image_url: '',
                    new_image_file: null,
                    new_image_preview: null,
                },

                openEditModal(row) {
                    if (row.is_paid_locked && !this.isSuperAdmin) {
                        this.showToast('🔒 This record is marked as Paid. Only Super Admin can edit it.', 'error');
                        return;
                    }
                    this.modalForm = {
                        meter_id: row.meter_id,
                        unit_number: row.unit_number,
                        floor: row.floor,
                        block: row.block,
                        meter_type_label: row.meter_type_label,
                        meter_ref_no: row.meter_ref_no,
                        meter_consumer_id: row.meter_consumer_id,
                        previous_reading: row.previous_reading || 0,
                        current_reading: row.current_reading > 0 ? row.current_reading : (row.current_reading === 0 ? '0' : ''),
                        available: row.available || '',
                        amount: row.amount || '',
                        status: row.status || 'unpaid',
                        original_status: row.status || 'unpaid',
                        is_paid_locked: !!row.is_paid_locked,
                        notes: row.notes || '',
                        bill_generate_date: row.bill_generate_date || '',
                        edited_by: row.edited_by || '',
                        last_updated: row.last_updated || '',
                        meter_image_url: row.meter_image_url || '',
                        new_image_file: null,
                        new_image_preview: null,
                    };
                    let fileInput = document.getElementById('modal_meter_image_input');
                    if (fileInput) fileInput.value = '';
                    this.modalOpen = true;
                    this.$nextTick(() => {
                        if (this.billDatePicker) {
                            this.billDatePicker.setDate(this.modalForm.bill_generate_date || null, false);
                        }
                    });
                },

                closeEditModal() {
                    this.modalOpen = false;
                },

                handleModalFileChange(event) {
                    let file = event.target.files[0];
                    if (!file) return;

                    if (file.size > 200 * 1024) {
                        this.showToast('Meter photo file size must not exceed 200 KB.', 'error');
                        event.target.value = '';
                        return;
                    }

                    this.modalForm.new_image_file = file;
                    let reader = new FileReader();
                    reader.onload = (e) => {
                        this.modalForm.new_image_preview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                removeModalSelectedFile() {
                    this.modalForm.new_image_file = null;
                    this.modalForm.new_image_preview = null;
                    let fileInput = document.getElementById('modal_meter_image_input');
                    if (fileInput) fileInput.value = '';
                },

                computedUnitsConsumed() {
                    let prev = parseFloat(this.modalForm.previous_reading) || 0;
                    let curr = parseFloat(this.modalForm.current_reading);
                    if (isNaN(curr)) return '0.00';
                    return Math.max(0, curr - prev).toFixed(2);
                },

                async saveModalReading() {
                    this.savingModal = true;
                    try {
                        let formData = new FormData();
                        formData.append('meter_id', this.modalForm.meter_id);
                        formData.append('month', this.month);
                        formData.append('previous_reading', this.modalForm.previous_reading);
                        formData.append('current_reading', this.modalForm.current_reading === '' ? 0 : this.modalForm.current_reading);
                        formData.append('available', this.modalForm.available || '');
                        formData.append('amount', this.modalForm.amount === '' ? 0 : this.modalForm.amount);
                        formData.append('status', this.modalForm.status);
                        formData.append('notes', this.modalForm.notes || '');
                        formData.append('bill_generate_date', this.modalForm.bill_generate_date || '');
                        formData.append('_token', '{{ csrf_token() }}');
                        if (this.modalForm.new_image_file) {
                            formData.append('meter_image', this.modalForm.new_image_file);
                        }

                        let res = await fetch('{{ route('utility-readings.update-row') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        let data = await res.json();
                        if (data.success) {
                            // Update matching item in readings array
                            let target = this.readings.find(r => r.meter_id === this.modalForm.meter_id);
                            if (target) {
                                target.previous_reading = data.data.previous_reading;
                                target.current_reading  = data.data.current_reading;
                                target.units_consumed   = data.data.units_consumed;
                                target.available        = data.data.available;
                                target.amount           = data.data.amount;
                                target.status           = data.data.status;
                                target.is_paid_locked   = (data.data.status === 'paid' && !this.isSuperAdmin);
                                target.bill_generate_date = data.data.bill_generate_date;
                                target.bill_generate_date_label = data.data.bill_generate_date_label;
                                if (data.data.meter_image_url) {
                                    target.meter_image_url = data.data.meter_image_url;
                                }
                                if (data.data.edited_by) {
                                    target.edited_by = data.data.edited_by;
                                }
                                if (data.data.last_updated) {
                                    target.last_updated = data.data.last_updated;
                                }
                            }
                            this.showToast(data.message, 'success');
                            this.closeEditModal();
                        } else {
                            let errorMsg = data.message || 'Error saving reading.';
                            if (data.errors) {
                                let firstError = Object.values(data.errors)[0];
                                if (Array.isArray(firstError)) errorMsg = firstError[0];
                            }
                            this.showToast(errorMsg, 'error');
                        }
                    } catch (e) {
                        this.showToast('Server error while saving reading.', 'error');
                    } finally {
                        this.savingModal = false;
                    }
                },

                // Bulk monthly entry
                bulkOpen: false,
                bulkLoading: false,
                bulkSaving: false,
                bulkMonth: config.month,
                bulkMonthName: '',
                bulkType: '',
                bulkSearch: '',
                bulkRows: [],
                bulkMonthPicker: null,
                bulkProgress: '',
                bulkErrors: [],

                openBulkModal() {
                    this.bulkOpen = true;
                    this.loadBulkRows(this.month);
                },

                closeBulkModal() {
                    if (this.bulkSaving) return;
                    this.bulkOpen = false;
                },

                initBulkMonthPicker(el) {
                    if (typeof flatpickr === 'undefined') return;
                    let plugins = [];
                    if (typeof monthSelectPlugin !== 'undefined') {
                        plugins.push(new monthSelectPlugin({ shorthand: false, dateFormat: 'Y-m', altFormat: 'F Y', theme: 'light' }));
                    }
                    this.bulkMonthPicker = flatpickr(el, {
                        dateFormat: 'Y-m',
                        altInput: true,
                        altFormat: 'F Y',
                        defaultDate: this.bulkMonth,
                        disableMobile: true,
                        plugins: plugins,
                        onChange: (selectedDates, dateStr) => {
                            if (dateStr && dateStr !== this.bulkMonth) {
                                if ((this.bulkFilledCount() > 0 || this.bulkImageCount() > 0) && !confirm('Switching month will discard the readings and images entered in this form. Continue?')) {
                                    this.bulkMonthPicker.setDate(this.bulkMonth, false);
                                    return;
                                }
                                this.loadBulkRows(dateStr);
                            }
                        }
                    });
                },

                async loadBulkRows(month) {
                    this.bulkLoading = true;
                    this.bulkErrors = [];
                    this.bulkMonth = month;
                    try {
                        let url = '{{ route('utility-readings.bulk-data') }}?month=' + encodeURIComponent(month);
                        let res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        let data = await res.json();
                        if (!data.success) {
                            this.showToast(data.message || 'Unable to load meters.', 'error');
                            return;
                        }
                        this.bulkMonth = data.month;
                        this.bulkMonthName = data.monthName;
                        if (this.bulkMonthPicker) this.bulkMonthPicker.setDate(data.month, false);
                        this.bulkRows = data.readings.map(r => ({
                            meter_id: r.meter_id,
                            unit_number: r.unit_number,
                            meter_type: r.meter_type,
                            meter_type_label: r.meter_type_label,
                            meter_ref_no: r.meter_ref_no,
                            is_active: r.is_active,
                            locked: !!r.is_paid_locked,
                            previous_reading: r.previous_reading || 0,
                            current_reading: r.current_reading > 0 ? r.current_reading : '',
                            amount: r.amount > 0 ? r.amount : '',
                            status: r.status || 'unpaid',
                            bill_generate_date: r.bill_generate_date || '',
                            due_date: r.due_date || '',
                            meter_image_url: r.meter_image_url || '',
                            image_file: null,
                            image_preview: null,
                            image_error: '',
                        }));
                    } catch (e) {
                        this.showToast('Server error while loading meters.', 'error');
                    } finally {
                        this.bulkLoading = false;
                    }
                },

                bulkFilteredRows() {
                    let term = this.bulkSearch.trim().toLowerCase();
                    return this.bulkRows.filter(r =>
                        (!this.bulkType || r.meter_type === this.bulkType) &&
                        (!term || String(r.unit_number).toLowerCase().includes(term) || String(r.meter_ref_no).toLowerCase().includes(term))
                    );
                },

                bulkHasReading(row) {
                    return row.current_reading !== '' && row.current_reading !== null;
                },

                bulkFilledCount() {
                    return this.bulkRows.filter(r => !r.locked && this.bulkHasReading(r)).length;
                },

                bulkUnits(row) {
                    if (!this.bulkHasReading(row)) return '—';
                    let prev = parseFloat(row.previous_reading) || 0;
                    let curr = parseFloat(row.current_reading) || 0;
                    return Math.max(0, curr - prev).toFixed(2);
                },

                bulkRowWarning(row) {
                    if (!this.bulkHasReading(row)) return false;
                    return (parseFloat(row.current_reading) || 0) < (parseFloat(row.previous_reading) || 0);
                },

                focusNextBulkInput(event) {
                    let inputs = Array.from(this.$root.querySelectorAll('[data-bulk-current]:not(:disabled)'));
                    let next = inputs[inputs.indexOf(event.target) + 1];
                    if (next) {
                        next.focus();
                        next.select();
                    }
                },

                bulkImageCount() {
                    return this.bulkRows.filter(r => !r.locked && r.image_file).length;
                },

                handleBulkFileChange(event, row) {
                    let file = event.target.files[0];
                    event.target.value = '';
                    if (!file) return;

                    let error = '';
                    if (!file.type.startsWith('image/')) {
                        error = 'Please select an image file (JPEG, PNG, WEBP).';
                    } else if (file.size > 200 * 1024) {
                        error = `Image must not exceed 200 KB (selected ${Math.ceil(file.size / 1024)} KB).`;
                    }
                    if (error) {
                        row.image_error = error;
                        this.showToast(`${row.unit_number} (${row.meter_type_label}): ${error}`, 'error');
                        return;
                    }

                    row.image_error = '';
                    row.image_file = file;
                    let reader = new FileReader();
                    reader.onload = (e) => { row.image_preview = e.target.result; };
                    reader.readAsDataURL(file);
                },

                removeBulkImage(row) {
                    row.image_file = null;
                    row.image_preview = null;
                    row.image_error = '';
                },

                bulkErrorMessage(data, fallback) {
                    let msg = (data && data.message) || fallback;
                    if (data && data.errors) {
                        let firstError = Object.values(data.errors)[0];
                        if (Array.isArray(firstError)) msg = firstError[0];
                    }
                    return msg;
                },

                async saveBulkReadings() {
                    let rows = this.bulkRows
                        .filter(r => !r.locked && this.bulkHasReading(r))
                        .map(r => ({
                            meter_id: r.meter_id,
                            previous_reading: r.previous_reading === '' ? 0 : r.previous_reading,
                            current_reading: r.current_reading,
                            amount: r.amount,
                            status: r.status,
                            bill_generate_date: r.bill_generate_date || null,
                            due_date: r.due_date || null,
                        }));
                    let imageRows = this.bulkRows.filter(r => !r.locked && r.image_file);

                    if (rows.length === 0 && imageRows.length === 0) {
                        this.showToast('Enter at least one meter reading or image before saving.', 'error');
                        return;
                    }

                    let warnings = this.bulkRows.filter(r => !r.locked && this.bulkRowWarning(r)).length;
                    if (warnings > 0 && !confirm(warnings + ' meter(s) have a reading lower than the previous reading (highlighted in red). Save anyway?')) {
                        return;
                    }

                    this.bulkSaving = true;
                    this.bulkErrors = [];
                    this.bulkRows.forEach(r => { r.image_error = ''; });
                    let messages = [];
                    let failed = [];
                    let reloading = false;
                    try {
                        // 1) Upload images first, one by one (keeps each request under PHP's max_file_uploads).
                        //    Images go before readings so a row being marked Paid in this same save
                        //    still gets its image before the Paid lock applies.
                        let failedMeterIds = [];
                        for (let i = 0; i < imageRows.length; i++) {
                            let row = imageRows[i];
                            this.bulkProgress = `Uploading images ${i + 1}/${imageRows.length}...`;
                            try {
                                let formData = new FormData();
                                formData.append('meter_id', row.meter_id);
                                formData.append('month', this.bulkMonth);
                                formData.append('meter_image', row.image_file);
                                formData.append('_token', '{{ csrf_token() }}');
                                let res = await fetch('{{ route('utility-readings.upload-image') }}', {
                                    method: 'POST',
                                    headers: { 'Accept': 'application/json' },
                                    body: formData
                                });
                                let data = res.status === 413
                                    ? { message: 'Image is too large for the server.' }
                                    : await res.json();
                                if (data.success) {
                                    row.meter_image_url = data.image_url;
                                    this.removeBulkImage(row);
                                } else {
                                    row.image_error = this.bulkErrorMessage(data, 'Image upload failed.');
                                }
                            } catch (e) {
                                row.image_error = 'Image upload failed.';
                            }
                            if (row.image_error) {
                                failedMeterIds.push(row.meter_id);
                                failed.push(`${row.unit_number} (${row.meter_type_label}): ${row.image_error}`);
                            }
                        }
                        if (imageRows.length > 0) {
                            messages.push(`${imageRows.length - failed.length} of ${imageRows.length} image(s) uploaded.`);
                        }

                        // 2) Save readings in one request. Rows whose image failed are held back
                        //    so they can be fixed and saved together (and not get Paid-locked first).
                        let readingRows = rows.filter(r => !failedMeterIds.includes(r.meter_id));
                        if (readingRows.length > 0) {
                            this.bulkProgress = 'Saving readings...';
                            let res = await fetch('{{ route('utility-readings.bulk-save') }}', {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    month: this.bulkMonth,
                                    rows: readingRows,
                                })
                            });
                            let data = await res.json();
                            if (!data.success) {
                                let msg = this.bulkErrorMessage(data, 'Error saving readings.');
                                this.bulkErrors = [msg, ...failed];
                                this.showToast(msg, 'error');
                                return;
                            }
                            messages.push(data.message);
                            (data.skipped || []).forEach(name => failed.push(`${name}: marked as Paid — only Super Admin can change it.`));

                            // Rows just saved as Paid are now locked for non-Super Admins
                            if (!this.isSuperAdmin) {
                                let savedIds = readingRows.map(r => r.meter_id);
                                this.bulkRows.forEach(r => {
                                    if (savedIds.includes(r.meter_id) && r.status === 'paid') {
                                        r.locked = true;
                                        this.removeBulkImage(r);
                                    }
                                });
                            }
                        }

                        if (failed.length > 0) {
                            // Keep the modal open so the failed rows can be fixed and saved again
                            this.bulkErrors = failed;
                            this.showToast(messages.join(' ') + ` ${failed.length} row(s) not saved — see details in the form.`, 'error');
                            return;
                        }

                        this.showToast(messages.join(' '), 'success');
                        // Reload the grid on the month that was just entered
                        reloading = true;
                        this.bulkProgress = 'Reloading...';
                        setTimeout(() => {
                            window.location.href = '{{ route('utility-readings.index') }}?month=' + encodeURIComponent(this.bulkMonth);
                        }, 1200);
                    } catch (e) {
                        this.bulkErrors = ['Server error while saving readings.'];
                        this.showToast('Server error while saving readings.', 'error');
                    } finally {
                        if (!reloading) {
                            this.bulkSaving = false;
                            this.bulkProgress = '';
                        }
                    }
                },

                // Photo lightbox
                previewModal: false,
                previewImageUrl: '',
                previewTitle: '',
                openImagePreview(row) {
                    this.previewImageUrl = row.meter_image_url;
                    this.previewTitle = `Meter Photo — ${row.unit_number} (${row.meter_type_label})`;
                    this.previewModal = true;
                },
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                var monthPlugins = [];
                if (typeof monthSelectPlugin !== 'undefined') {
                    monthPlugins.push(new monthSelectPlugin({
                        shorthand: false,
                        dateFormat: "Y-m",
                        altFormat: "F Y",
                        theme: "light"
                    }));
                }

                flatpickr('#month_filter', {
                    dateFormat: 'Y-m',
                    altInput: true,
                    altFormat: 'F Y',
                    defaultDate: "{{ $selectedMonth }}",
                    disableMobile: true,
                    plugins: monthPlugins,
                    onChange: function (selectedDates, dateStr, instance) {
                        document.getElementById('utilityFilterForm').submit();
                    }
                });
            }
        });
    </script>
@endpush
