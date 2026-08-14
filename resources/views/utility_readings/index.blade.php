@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ $title }}" />

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

    {{-- Toast Notification Component --}}
    <div x-data="{
        toastShow: false,
        toastMessage: '',
        toastType: 'success',
        showToast(msg, type = 'success') {
            this.toastMessage = msg;
            this.toastType = type;
            this.toastShow = true;
            setTimeout(() => { this.toastShow = false; }, 3500);
        }
    }" 
    x-on:show-toast.window="showToast($event.detail.message, $event.detail.type)"
    class="relative">

        {{-- Floating Toast --}}
        <div x-show="toastShow" x-transition
            class="fixed bottom-6 right-6 z-99999 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl border text-sm font-bold text-white"
            :class="toastType === 'success' ? 'bg-emerald-600 border-emerald-500' : 'bg-rose-600 border-rose-500'"
            style="display: none;">
            <span x-text="toastType === 'success' ? '✅' : '⚠️'"></span>
            <span x-text="toastMessage"></span>
        </div>

        {{-- Meter Readings Table --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white">
                        Monthly Meter Readings Directory ({{ count($readings) }} Items)
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
                            <th class="py-3.5 px-4">Type</th>
                            <th class="py-3.5 px-4">Ref Number</th>
                            <th class="py-3.5 px-4">Consumer ID</th>
                            <th class="py-3.5 px-4 text-right">Units KV</th>
                            <th class="py-3.5 px-4 text-right">Bill Amount (Rs.)</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">Meter Status</th>
                            <th class="py-3.5 px-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-800 dark:text-gray-200 font-semibold">
                        @forelse($readings as $row)
                            <tr x-data="{
                                meter_id: {{ $row['meter_id'] }},
                                month: '{{ $selectedMonth }}',
                                current_reading: '{{ $row['current_reading'] }}',
                                amount: '{{ $row['amount'] }}',
                                status: '{{ $row['status'] }}',
                                meter_image_url: '{{ $row['meter_image_url'] }}',
                                is_active: {{ $row['is_active'] ? 'true' : 'false' }},
                                isSaving: false,
                                isUploading: false,
                                previewModal: false,

                                async saveRow() {
                                    this.isSaving = true;
                                    try {
                                        let res = await fetch('{{ route('utility-readings.update-row') }}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                meter_id: this.meter_id,
                                                month: this.month,
                                                current_reading: this.current_reading,
                                                amount: this.amount,
                                                status: this.status
                                            })
                                        });

                                        let data = await res.json();
                                        if (data.success) {
                                            $dispatch('show-toast', { message: data.message, type: 'success' });
                                        } else {
                                            $dispatch('show-toast', { message: data.message || 'Error saving reading.', type: 'error' });
                                        }
                                    } catch (e) {
                                        $dispatch('show-toast', { message: 'Server error while saving.', type: 'error' });
                                    } finally {
                                        this.isSaving = false;
                                    }
                                },

                                async uploadImage(event) {
                                    let file = event.target.files[0];
                                    if (!file) return;

                                    if (file.size > 200 * 1024) {
                                        $dispatch('show-toast', { message: 'Meter photo file size must not exceed 200 KB.', type: 'error' });
                                        event.target.value = '';
                                        return;
                                    }

                                    let formData = new FormData();
                                    formData.append('meter_id', this.meter_id);
                                    formData.append('month', this.month);
                                    formData.append('meter_image', file);
                                    formData.append('_token', '{{ csrf_token() }}');

                                    this.isUploading = true;
                                    try {
                                        let res = await fetch('{{ route('utility-readings.upload-image') }}', {
                                            method: 'POST',
                                            headers: { 'Accept': 'application/json' },
                                            body: formData
                                        });
                                        let data = await res.json();
                                        if (data.success) {
                                            this.meter_image_url = data.image_url;
                                            $dispatch('show-toast', { message: 'Meter photo uploaded successfully.', type: 'success' });
                                        } else {
                                            let errorMsg = data.message || 'Failed to upload photo.';
                                            if (data.errors && data.errors.meter_image) {
                                                errorMsg = data.errors.meter_image[0];
                                            }
                                            $dispatch('show-toast', { message: errorMsg, type: 'error' });
                                        }
                                    } catch (e) {
                                        $dispatch('show-toast', { message: 'Server error during photo upload.', type: 'error' });
                                    } finally {
                                        this.isUploading = false;
                                    }
                                }
                            }" class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors">
                                
                                {{-- Meter Image Column --}}
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <template x-if="meter_image_url">
                                            <div class="relative group cursor-pointer" x-on:click="previewModal = true">
                                                <img :src="meter_image_url" alt="Meter Photo" class="h-10 w-10 rounded-xl object-cover border-2 border-brand-300 dark:border-brand-800 shadow-xs">
                                                <div class="absolute inset-0 bg-black/40 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs transition-opacity">
                                                    🔍
                                                </div>
                                            </div>
                                        </template>

                                        <template x-if="!meter_image_url">
                                            <div class="h-10 w-10 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 text-xs">
                                                📷
                                            </div>
                                        </template>

                                        {{-- File Upload Button (Edit Permission Required) --}}
                                        @if($canEdit)
                                            <label class="cursor-pointer p-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 hover:bg-brand-50 hover:border-brand-300 dark:hover:bg-brand-900/30 text-gray-600 dark:text-gray-300 transition-colors" title="Upload / Update Photo (Max 200 KB)">
                                                <input type="file" accept="image/*" x-on:change="uploadImage($event)" class="hidden">
                                                <span x-show="!isUploading">📷</span>
                                                <span x-show="isUploading" class="animate-spin text-xs">⏳</span>
                                            </label>
                                        @endif
                                    </div>

                                    {{-- Lightbox Image Modal --}}
                                    <template x-if="previewModal">
                                        <div class="fixed inset-0 z-99999 flex items-center justify-center bg-black/80 p-4" x-on:click.self="previewModal = false">
                                            <div class="relative max-w-lg w-full bg-white dark:bg-gray-900 rounded-3xl p-4 shadow-2xl">
                                                <button x-on:click="previewModal = false" class="absolute top-3 right-3 h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold flex items-center justify-center">✕</button>
                                                <h4 class="text-sm font-bold mb-3 text-gray-900 dark:text-white">Meter Photo — {{ $row['unit_number'] }} ({{ $row['meter_type_label'] }})</h4>
                                                <img :src="meter_image_url" class="w-full h-auto max-h-[70vh] rounded-2xl object-contain">
                                            </div>
                                        </div>
                                    </template>
                                </td>

                                {{-- Flat/Shop Column --}}
                                <td class="py-3 px-4">
                                    <a href="{{ route('units.show', $row['unit_id']) }}" class="inline-block hover:opacity-90 transition-opacity">
                                        <span class="unit-badge-lg text-sm px-2.5 py-0.5 font-black">
                                            {{ $row['unit_number'] }}
                                        </span>
                                    </a>
                                    <span class="text-xs text-gray-400 font-medium block mt-1">
                                        {{ $row['floor'] }} {{ $row['block'] ? '• ' . $row['block'] : '' }}
                                    </span>
                                </td>

                                {{-- Meter Type Column --}}
                                <td class="py-3 px-4">
                                    @if($row['meter_type'] === 'electricity')
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800/40">
                                            ⚡ Electricity
                                        </span>
                                    @elseif($row['meter_type'] === 'water')
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800/40">
                                            💧 Water
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 dark:bg-rose-900/30 dark:text-rose-300 border border-rose-200 dark:border-rose-800/40">
                                            🔥 Gas
                                        </span>
                                    @endif
                                </td>

                                {{-- Ref Number Column --}}
                                <td class="py-3 px-4 font-mono text-xs text-gray-600 dark:text-gray-400">
                                    {{ $row['meter_ref_no'] }}
                                </td>

                                {{-- Consumer ID Column --}}
                                <td class="py-3 px-4 font-mono text-xs text-gray-600 dark:text-gray-400">
                                    {{ $row['meter_consumer_id'] }}
                                </td>

                                {{-- Units KV Column --}}
                                <td class="py-3 px-4 text-right font-mono font-bold text-xs sm:text-sm text-gray-900 dark:text-white">
                                    @if($canEdit)
                                        <input type="number" step="0.01" min="0" x-model="current_reading"
                                            class="w-28 h-9 px-2 text-right font-bold text-xs sm:text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg focus:border-brand-500 focus:outline-none dark:text-white">
                                    @else
                                        {{ number_format($row['current_reading'], 2) }}
                                    @endif
                                </td>

                                {{-- Bill Amount Column --}}
                                <td class="py-3 px-4 text-right font-mono font-extrabold text-xs sm:text-sm text-gray-900 dark:text-white">
                                    @if($canEdit)
                                        <input type="number" step="0.01" min="0" x-model="amount"
                                            class="w-32 h-9 px-2 text-right font-extrabold text-xs sm:text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg focus:border-brand-500 focus:outline-none text-gray-900 dark:text-white">
                                    @else
                                        Rs. {{ number_format($row['amount'], 2) }}
                                    @endif
                                </td>

                                {{-- Status Column --}}
                                <td class="py-3 px-4 text-center">
                                    @if($canEdit)
                                        <select x-model="status"
                                            class="h-9 px-2 text-xs font-bold rounded-lg border focus:outline-none transition-colors"
                                            :class="{
                                                'bg-emerald-50 border-emerald-300 text-emerald-700 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300': status === 'paid',
                                                'bg-rose-50 border-rose-300 text-rose-700 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300': status === 'unpaid',
                                                'bg-amber-50 border-amber-300 text-amber-700 dark:bg-amber-950/40 dark:border-amber-800 dark:text-amber-300': status === 'pending'
                                            }">
                                            <option value="paid">Paid</option>
                                            <option value="unpaid">Unpaid</option>
                                            <option value="pending">Pending</option>
                                        </select>
                                    @else
                                        @if(strtolower($row['status']) === 'paid')
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-extrabold uppercase rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-700 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300">Paid</span>
                                        @elseif(strtolower($row['status']) === 'unpaid')
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-extrabold uppercase rounded-lg bg-rose-50 border border-rose-300 text-rose-700 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300">Unpaid</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-extrabold uppercase rounded-lg bg-amber-50 border border-amber-300 text-amber-700 dark:bg-amber-950/40 dark:border-amber-800 dark:text-amber-300">Pending</span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Meter Status Column --}}
                                <td class="py-3 px-4 text-center">
                                    @if($row['is_active'])
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-extrabold uppercase rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-700 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 inline-block"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-extrabold uppercase rounded-lg bg-gray-100 border border-gray-300 text-gray-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400 inline-block"></span> Inactive
                                        </span>
                                    @endif
                                </td>

                                {{-- Save Action Button --}}
                                <td class="py-3 px-4 text-center">
                                    @if($canEdit)
                                        <template x-if="is_active">
                                            <button type="button" x-on:click="saveRow()" :disabled="isSaving"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-xs transition-colors cursor-pointer disabled:opacity-50">
                                                <span x-show="!isSaving">💾 Save</span>
                                                <span x-show="isSaving" class="flex items-center gap-1">
                                                    <svg class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                                    </svg>
                                                    Saving...
                                                </span>
                                            </button>
                                        </template>
                                        <template x-if="!is_active">
                                            <span class="inline-flex items-center px-2.5 py-1.5 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed" title="Meter is inactive — cannot record readings">
                                                🚫 Inactive
                                            </span>
                                        </template>
                                    @else
                                        <span class="text-xs text-gray-400 font-semibold uppercase">Read Only</span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-12 text-center text-gray-400 dark:text-gray-500">
                                    <p class="text-3xl mb-2">⚡</p>
                                    <p class="font-bold text-sm">No utility meters found matching your filter criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
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
