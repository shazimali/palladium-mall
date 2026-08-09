@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Meter Reading Voucher" />

    <form action="{{ route('meter-reading-vouchers.update', $voucher) }}" method="POST" enctype="multipart/form-data"
        @submit.prevent="handleSubmit($event)"
        x-data="{
            unitId: '{{ old('unit_id', $voucher->unit_id) }}',
            meterRefNo: '{{ old('meter_ref_no', $voucher->meter_ref_no) }}',
            voucherAmount: '{{ old('amount', $voucher->amount) }}',
            displayAmount: '{{ number_format((float)$voucher->amount, 2) }}',
            status: '{{ old('status', $voucher->status) }}',
            open: false,
            search: '',
            highlightedIndex: -1,
            imagePreview: '{{ $voucher->meter_image_url }}',
            unitMeterMap: @js($unitMeterMap),

            options: [
                @foreach($units as $unit)
                {
                    id: '{{ $unit->id }}',
                    unit: '{{ addslashes($unit->unit_number) }}',
                    meterRef: '{{ $unitMeterMap[$unit->id] ?? "" }}',
                    tenantName: '{{ $unit->tenant ? addslashes($unit->tenant->name) : ($unit->otherTenant ? addslashes($unit->otherTenant->name) : "Vacant") }}',
                    tenant: '{{ $unit->tenant ? "(Tenant: " . addslashes($unit->tenant->name) . ")" : ($unit->otherTenant ? "(Other Tenant: " . addslashes($unit->otherTenant->name) . ")" : "(Vacant)") }}',
                    searchLabel: '{{ strtolower($unit->unit_number . " " . ($unit->tenant?->name ?? ($unit->otherTenant?->name ?? "vacant")) . " " . ($unitMeterMap[$unit->id] ?? "")) }}'
                },
                @endforeach
            ],

            init() {
                this.$watch('unitId', (newVal) => {
                    if (newVal && this.unitMeterMap[newVal] && !this.meterRefNo) {
                        this.meterRefNo = this.unitMeterMap[newVal];
                    }
                });
            },

            get filteredOptions() {
                if (!this.search) return this.options;
                let s = this.search.toLowerCase();
                return this.options.filter(opt => opt.searchLabel.includes(s));
            },

            get selectedUnit() {
                let selected = this.options.find(opt => opt.id == this.unitId);
                return selected ? selected.unit : '';
            },

            get selectedTenantName() {
                if (!this.unitId) return 'Select a Flat/Shop...';
                let selected = this.options.find(opt => opt.id == this.unitId);
                return selected ? selected.tenantName : 'N/A';
            },

            selectOption(opt) {
                this.unitId = opt.id;
                this.meterRefNo = opt.meterRef || this.unitMeterMap[opt.id] || '';
                this.open = false;
                this.search = '';
                this.highlightedIndex = -1;
            },

            moveHighlight(dir) {
                let list = this.filteredOptions;
                if (list.length === 0) return;
                this.highlightedIndex = (this.highlightedIndex + dir + list.length) % list.length;
            },

            selectHighlighted() {
                let list = this.filteredOptions;
                if (this.highlightedIndex >= 0 && this.highlightedIndex < list.length) {
                    this.selectOption(list[this.highlightedIndex]);
                }
            },

            clearSelection() {
                this.unitId = '';
                this.open = false;
                this.search = '';
                this.highlightedIndex = -1;
            },

            formatAmount(val) {
                let clean = val.replace(/[^\d.]/g, '');
                let parts = clean.split('.');
                if (parts.length > 2) {
                    parts = [parts[0], parts.slice(1).join('')];
                }
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                this.displayAmount = parts.join('.');
                this.voucherAmount = clean ? parseFloat(clean) : '';
            },

            handleImageChange(event) {
                const file = event.target.files[0];
                if (file) {
                    this.imagePreview = URL.createObjectURL(file);
                }
            },

            handleSubmit(event) {
                if (!this.unitId) {
                    Swal.fire({
                        title: 'Flat/Shop Required',
                        text: 'Please select a Flat / Shop.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                if (!this.meterRefNo.trim()) {
                    Swal.fire({
                        title: 'GEPCO Ref # Required',
                        text: 'Please enter a valid GEPCO Meter Reference Number.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                let amt = parseFloat(this.voucherAmount || 0);
                if (isNaN(amt) || amt <= 0) {
                    Swal.fire({
                        title: 'Invalid Amount',
                        text: 'Please enter a valid bill amount greater than zero.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                Swal.fire({
                    title: 'Confirm Update Voucher',
                    text: 'Are you sure you want to update Meter Reading Voucher #{{ $voucher->voucher_no }}?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Update Voucher',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer mr-2',
                        cancelButton: 'inline-flex items-center justify-center rounded-xl bg-gray-200 dark:bg-gray-700 px-6 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-200 shadow-md hover:bg-gray-300 transition-colors cursor-pointer'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        event.target.submit();
                    }
                });
            }
        }">

        @csrf
        @method('PUT')
        <input type="hidden" name="unit_id" x-model="unitId" required>

        {{-- FORM CONTAINER WITH NO OUTER BG COLOR, CENTERED TITLE, ADAPTED TO THEME --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm text-gray-900 dark:text-white font-sans relative">

            @if ($errors->any() || session('error'))
                <div class="mb-6 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 p-4 text-rose-800 dark:text-rose-200">
                    <div class="flex items-center gap-2 font-black text-base mb-1">
                        ⚠️ Validation Error
                    </div>
                    <ul class="list-disc list-inside text-sm space-y-1 font-bold">
                        @if(session('error'))
                            <li>{{ session('error') }}</li>
                        @endif
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- FORM HEADER WITH CENTERED TITLE & RIGHT CORNER VOUCHER NUMBER --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">
                <div class="hidden sm:block w-36"></div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-brand-600 dark:text-brand-400 uppercase text-center">
                    Edit Meter Reading Voucher
                </h2>
                <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-2 shadow-2xs">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Voucher No:</span>
                    <span class="text-base sm:text-lg font-black font-mono text-brand-600 dark:text-brand-400">{{ $voucher->voucher_no }}</span>
                </div>
            </div>

            {{-- 2-COLUMN SIDE-BY-SIDE FORM GRID LAYOUT (COL-6 / LG:GRID-COLS-2) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6 items-start">
                
                {{-- LEFT COLUMN: Voucher Date, Flat / Shop (Searchable), Tenant Name, Due Date --}}
                <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-visible border border-gray-200 dark:border-gray-700">
                    
                    {{-- Field 1: Voucher Date --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide rounded-tl-2xl">Voucher Date <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center rounded-tr-2xl">
                            <input type="text" id="voucher_date" name="date" value="{{ old('date', $voucher->date ? $voucher->date->toDateString() : now()->toDateString()) }}" required autocomplete="off"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all cursor-pointer">
                        </div>
                    </div>

                    {{-- Field 2: Searchable Flat / Shop Dropdown (Directly after Voucher Date) --}}
                    <div class="grid grid-cols-3 min-h-[52px] relative z-50" @click.away="open = false; highlightedIndex = -1">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Flat / Shop <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <button type="button" @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                                class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                <span x-text="selectedUnit ? 'Flat/Shop ' + selectedUnit : 'Select Flat / Shop...'" class="truncate"></span>
                                <span class="ml-2 text-sm opacity-60">▼</span>
                            </button>

                            {{-- Expanded Searchable Dropdown Modal --}}
                            <div x-show="open" x-transition x-cloak
                                class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                
                                <div class="p-3.5 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                    <input type="text" x-ref="searchInput" x-model="search" placeholder="Type flat number, tenant, or ref #..."
                                        @keydown.arrow-down.prevent="moveHighlight(1)"
                                        @keydown.arrow-up.prevent="moveHighlight(-1)"
                                        @keydown.enter.prevent="selectHighlighted()"
                                        @keydown.escape="open = false; highlightedIndex = -1"
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-base sm:text-lg text-gray-900 dark:text-white font-black focus:border-brand-500 focus:outline-none">
                                </div>

                                <div class="max-h-[360px] overflow-y-auto p-2 space-y-1 text-sm">
                                    <button type="button" @click="clearSelection()"
                                        class="w-full text-left px-4 py-2.5 font-black text-red-600 text-sm sm:text-base hover:bg-red-50 dark:hover:bg-red-950/20 rounded-xl transition-colors">
                                        ❌ Clear Selection
                                    </button>
                                    
                                    <template x-for="(opt, index) in filteredOptions" :key="opt.id">
                                        <button type="button" @click="selectOption(opt)"
                                            @mouseenter="highlightedIndex = index"
                                            class="w-full text-left px-4 py-3.5 rounded-xl transition-colors flex items-center justify-between"
                                            :class="unitId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                            <span class="flex items-center gap-2.5 truncate">
                                                <span x-text="'Flat/Shop ' + opt.unit" class="font-black text-base sm:text-lg"></span>
                                                <span x-text="opt.tenant" class="opacity-80 truncate text-xs sm:text-sm font-bold"></span>
                                            </span>
                                            <span x-show="unitId == opt.id" class="font-black text-lg">✓</span>
                                        </button>
                                    </template>

                                    <div x-show="filteredOptions.length === 0" class="px-4 py-4 text-center text-xs sm:text-sm font-semibold text-gray-400">
                                        No matching Flat/Shop found
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Field 3: Tenant Name --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Tenant Name</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base">
                            <span x-text="selectedTenantName"></span>
                        </div>
                    </div>

                    {{-- Field 4: Due Date --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide rounded-bl-2xl">Due Date</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center rounded-br-2xl">
                            <input type="text" id="due_date" name="due_date" value="{{ old('due_date', $voucher->due_date ? $voucher->due_date->toDateString() : '') }}" autocomplete="off" placeholder="Select bill due date..."
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all cursor-pointer">
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN: GEPCO Ref # (REQUIRED), Reading, Bill Amount, Bill Status --}}
                <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-visible border border-gray-200 dark:border-gray-700">

                    {{-- Field 5: GEPCO Meter Reference # (REQUIRED) --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide rounded-tl-2xl">GEPCO Ref # <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center rounded-tr-2xl">
                            <input type="text" name="meter_ref_no" x-model="meterRefNo" required placeholder="GEPCO Meter Reference #..."
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black font-mono text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>
                    </div>

                    {{-- Field 6: Current Reading (kWh) --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Reading (kWh)</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <input type="number" step="0.01" min="0" name="current_reading" value="{{ old('current_reading', $voucher->current_reading) }}" placeholder="Current kWh reading..."
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black font-mono text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>
                    </div>

                    {{-- Field 7: Bill Amount --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Bill Amount <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex flex-col justify-center">
                            <input type="text" x-model="displayAmount" @input="formatAmount($event.target.value)" required placeholder="0.00"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            <input type="hidden" name="amount" x-model="voucherAmount">
                        </div>
                    </div>

                    {{-- Field 8: Payment Status --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide rounded-bl-2xl">Bill Status <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center rounded-br-2xl">
                            <select name="status" x-model="status" required
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                <option value="unpaid">UNPAID</option>
                                <option value="paid">PAID</option>
                            </select>
                        </div>
                    </div>

                </div>

            </div>

            {{-- BOTTOM SECTION: Upload Photo & Remarks --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start mb-6">
                {{-- Meter Photo Field --}}
                <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 shadow-xs">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                        Upload GEPCO Meter Photo:
                    </label>
                    <input type="file" name="meter_image" accept="image/*" @change="handleImageChange($event)"
                        class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-950/40 dark:file:text-brand-300 cursor-pointer" />
                    
                    <template x-if="imagePreview">
                        <div class="mt-3 relative inline-block">
                            <img :src="imagePreview" alt="Meter Photo Preview" class="h-28 w-auto rounded-xl border border-gray-300 shadow-sm object-cover" />
                        </div>
                    </template>
                </div>

                {{-- Remarks / Notes Field --}}
                <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 shadow-xs">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                        Remarks / Notes:
                    </label>
                    <textarea name="notes" rows="2" placeholder="GEPCO bill details or additional remarks..."
                        class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl p-3 text-sm font-bold text-gray-900 dark:text-white placeholder-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all">{{ old('notes', $voucher->notes) }}</textarea>
                </div>
            </div>

            {{-- Approved By & Action Buttons Bar --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                <div class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                    Approved by: <span class="text-brand-600 dark:text-brand-400 font-extrabold ml-1">{{ auth()->user()->name }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('meter-reading-vouchers.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold transition-all text-xs sm:text-sm">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-black text-xs sm:text-sm shadow-md transition-all cursor-pointer">
                        Update Voucher
                    </button>
                </div>
            </div>

        </div>

    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#voucher_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    defaultDate: '{{ old('date', $voucher->date ? $voucher->date->toDateString() : now()->toDateString()) }}'
                });
                flatpickr('#due_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    defaultDate: '{{ old('due_date', $voucher->due_date ? $voucher->due_date->toDateString() : '') }}'
                });
            }
        });
    </script>
@endpush
