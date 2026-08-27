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
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">
                        📅 Filter Month <span class="text-brand-500">*</span>
                    </label>
                    <input type="text" id="month_filter" name="month" value="{{ $selectedMonth }}" placeholder="Select Month"
                        class="w-full h-11 px-3 text-xs sm:text-sm font-bold bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20 cursor-pointer">
                </div>

                {{-- Flat/Shop Filter --}}
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">
                        🏢 Flat / Shop
                    </label>
                    <select name="unit_id" onchange="this.form.submit()"
                        class="w-full h-11 px-3 text-xs sm:text-sm font-bold bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
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
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">
                        ⚡ Meter Type
                    </label>
                    <select name="type" onchange="this.form.submit()"
                        class="w-full h-11 px-3 text-xs sm:text-sm font-bold bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Meter Types</option>
                        <option value="electricity" {{ $selectedType === 'electricity' ? 'selected' : '' }}>⚡ Electricity</option>
                        <option value="water" {{ $selectedType === 'water' ? 'selected' : '' }}>💧 Water</option>
                        <option value="gas" {{ $selectedType === 'gas' ? 'selected' : '' }}>🔥 Gas</option>
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">
                        Status
                    </label>
                    <select name="status" onchange="this.form.submit()"
                        class="w-full h-11 px-3 text-xs sm:text-sm font-bold bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                        <option value="">All Statuses</option>
                        <option value="paid" {{ $selectedStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ $selectedStatus === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                {{-- Search Input --}}
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">
                        Search Ref / ID
                    </label>
                    <input type="text" name="search" value="{{ $searchTerm }}" placeholder="Search Ref / ID..."
                        class="w-full h-11 px-3 text-xs sm:text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                </div>

                {{-- Actions: Filter, Print & Reset --}}
                <div class="flex items-center gap-1.5">
                    <button type="submit" class="h-11 px-3.5 rounded-xl bg-brand-500 text-white font-bold text-xs hover:bg-brand-600 transition-colors flex items-center justify-center">
                        Filter
                    </button>
                    <a href="{{ route('utility-readings.print', request()->query()) }}" target="_blank"
                        class="h-11 px-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs flex items-center justify-center gap-1 shadow-xs transition-colors" title="Print Report">
                        🖨️ Print
                    </a>
                    <a href="{{ route('utility-readings.index') }}" class="h-11 px-3 rounded-xl border border-gray-300 dark:border-gray-700 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="Reset Filters">
                        🔄
                    </a>
                </div>

            </form>
        </div>

        {{-- Floating Toast --}}
        <div x-show="toastShow" x-transition
            class="fixed bottom-6 right-6 z-99999 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl border text-sm font-bold text-white"
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
                <table class="w-full text-left text-xs sm:text-sm">
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
                            <th class="py-3.5 px-4 text-center">Meter Status</th>
                            <th class="py-3.5 px-4 text-center">Edited By</th>
                            <th class="py-3.5 px-4 text-center">Action</th>
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
                                            <div class="absolute inset-0 bg-black/40 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs transition-opacity">
                                                🔍
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="!row.meter_image_url">
                                        <span class="inline-flex h-10 w-10 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 items-center justify-center text-gray-400 text-xs" title="No photo uploaded">
                                            📷
                                        </span>
                                    </template>
                                </td>

                                {{-- Flat/Shop Column --}}
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <a :href="'/units/' + row.unit_id" class="inline-block hover:opacity-90 transition-opacity">
                                            <span class="unit-badge-lg text-sm px-2.5 py-0.5 font-black" x-text="row.unit_number"></span>
                                        </a>
                                        <template x-if="row.meter_type === 'electricity'">
                                            <span class="inline-flex items-center gap-0.5 rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40" title="Electricity Meter">
                                                ⚡ Elect
                                            </span>
                                        </template>
                                        <template x-if="row.meter_type === 'water'">
                                            <span class="inline-flex items-center gap-0.5 rounded-md bg-blue-50 px-1.5 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40" title="Water Meter">
                                                💧 Water
                                            </span>
                                        </template>
                                        <template x-if="row.meter_type === 'gas'">
                                            <span class="inline-flex items-center gap-0.5 rounded-md bg-rose-50 px-1.5 py-0.5 text-[10px] font-bold text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40" title="Gas Meter">
                                                🔥 Gas
                                            </span>
                                        </template>
                                    </div>
                                    <span class="text-xs text-gray-400 font-medium block mt-1" x-text="row.floor + (row.block ? ' • ' + row.block : '')"></span>
                                </td>

                                {{-- Ref Number Column --}}
                                <td class="py-3.5 px-4 font-mono text-xs text-gray-600 dark:text-gray-400" x-text="row.meter_ref_no"></td>

                                {{-- Consumer ID Column --}}
                                <td class="py-3.5 px-4 font-mono text-xs text-gray-600 dark:text-gray-400" x-text="row.meter_consumer_id"></td>

                                {{-- Prev Reading Column --}}
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                    <span class="inline-block bg-gray-100 dark:bg-gray-800/80 px-2.5 py-1 rounded-lg border border-gray-200 dark:border-gray-700"
                                        x-text="parseFloat(row.previous_reading || 0).toFixed(2)"></span>
                                </td>

                                {{-- Meter Reading Column --}}
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-xs sm:text-sm text-gray-900 dark:text-white">
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
                                        x-text="parseFloat(row.current_reading || 0).toFixed(2)"></span>
                                </td>

                                {{-- Units Consumed Column --}}
                                <td class="py-3.5 px-4 text-right font-mono font-black text-xs sm:text-sm text-indigo-600 dark:text-indigo-400">
                                    <span class="inline-block bg-indigo-50 dark:bg-indigo-950/40 px-2.5 py-1 rounded-lg border border-indigo-200 dark:border-indigo-800"
                                        x-text="((parseFloat(row.current_reading || 0) > 0 && parseFloat(row.current_reading || 0) >= parseFloat(row.previous_reading || 0)) ? (parseFloat(row.current_reading) - parseFloat(row.previous_reading || 0)) : 0).toFixed(2)"></span>
                                </td>

                                {{-- Available Column --}}
                                <td class="py-3.5 px-4 text-center">
                                    <template x-if="row.available">
                                        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold rounded-lg bg-blue-50 border border-blue-200 text-blue-700 dark:bg-blue-950/40 dark:border-blue-800 dark:text-blue-300"
                                            x-text="row.available"></span>
                                    </template>
                                    <template x-if="!row.available">
                                        <span class="text-xs text-gray-400 font-semibold">—</span>
                                    </template>
                                </td>

                                {{-- Bill Amount Column --}}
                                <td class="py-3.5 px-4 text-right font-mono font-extrabold text-xs sm:text-sm text-gray-900 dark:text-white">
                                    <span x-text="'Rs. ' + parseFloat(row.amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                </td>

                                {{-- Status Column --}}
                                <td class="py-3.5 px-4 text-center">
                                    <template x-if="row.status === 'paid'">
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-extrabold uppercase rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-700 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300">
                                            Paid
                                        </span>
                                    </template>
                                    <template x-if="row.status === 'unpaid'">
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-extrabold uppercase rounded-lg bg-rose-50 border border-rose-300 text-rose-700 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300">
                                            Unpaid
                                        </span>
                                    </template>
                                    <template x-if="row.status === 'pending'">
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-extrabold uppercase rounded-lg bg-amber-50 border border-amber-300 text-amber-700 dark:bg-amber-950/40 dark:border-amber-800 dark:text-amber-300">
                                            Pending
                                        </span>
                                    </template>
                                </td>

                                {{-- Meter Status Column --}}
                                <td class="py-3.5 px-4 text-center">
                                    <template x-if="row.is_active">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-extrabold uppercase rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-700 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 inline-block"></span> Active
                                        </span>
                                    </template>
                                    <template x-if="!row.is_active">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-extrabold uppercase rounded-lg bg-gray-100 border border-gray-300 text-gray-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400 inline-block"></span> Inactive
                                        </span>
                                    </template>
                                </td>

                                {{-- Edited By Column --}}
                                <td class="py-3.5 px-4 text-center">
                                    <template x-if="row.edited_by">
                                        <div class="inline-flex flex-col items-center">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 shadow-2xs"
                                                :title="row.last_updated ? 'Last updated: ' + row.last_updated : ''">
                                                <span class="h-1.5 w-1.5 rounded-full bg-brand-500 inline-block"></span>
                                                <span x-text="row.edited_by"></span>
                                            </span>
                                            <template x-if="row.last_updated">
                                                <span class="text-[10px] text-gray-400 font-medium mt-0.5" x-text="row.last_updated"></span>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!row.edited_by">
                                        <span class="text-xs text-gray-400 font-semibold">—</span>
                                    </template>
                                </td>

                                {{-- Action Column --}}
                                <td class="py-3.5 px-4 text-center">
                                    <template x-if="canEdit">
                                        <div>
                                            <template x-if="row.is_paid_locked">
                                                <button type="button" x-on:click="showToast('🔒 This record is marked as Paid. Only Super Admin can edit it.', 'error')"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 text-xs font-bold border border-gray-200 dark:border-gray-700 cursor-not-allowed"
                                                    title="Paid record locked — Super Admin only">
                                                    🔒 Paid
                                                </button>
                                            </template>
                                            <template x-if="!row.is_paid_locked">
                                                <button type="button" x-on:click="openEditModal(row)"
                                                    class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-brand-50 hover:bg-brand-100 dark:bg-brand-900/30 dark:hover:bg-brand-900/50 text-brand-600 dark:text-brand-400 text-xs font-extrabold border border-brand-200 dark:border-brand-800 shadow-2xs transition-colors cursor-pointer">
                                                    ✏️ Edit
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!canEdit">
                                        <span class="text-xs text-gray-400 font-semibold uppercase">Read Only</span>
                                    </template>
                                </td>

                            </tr>
                        </template>

                        <template x-if="readings.length === 0">
                            <tr>
                                <td colspan="13" class="py-12 text-center text-gray-400 dark:text-gray-500">
                                    <p class="text-3xl mb-2">⚡</p>
                                    <p class="font-bold text-sm">No utility meters found matching your filter criteria.</p>
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

                            {{-- Available (Manual) --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                    📦 Available (Manual)
                                </label>
                                <input type="text" x-model="modalForm.available"
                                    placeholder="e.g. Yes, No, Available, etc."
                                    class="w-full h-10 px-3 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:outline-none">
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
                        edited_by: row.edited_by || '',
                        last_updated: row.last_updated || '',
                        meter_image_url: row.meter_image_url || '',
                        new_image_file: null,
                        new_image_preview: null,
                    };
                    let fileInput = document.getElementById('modal_meter_image_input');
                    if (fileInput) fileInput.value = '';
                    this.modalOpen = true;
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
