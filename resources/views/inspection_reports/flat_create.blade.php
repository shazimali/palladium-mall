@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="New Vacant Flat Inspection" />

    <div class="mx-auto w-full">
        <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">

            <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                        Vacant Flat / Shop Inspection
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Inspect vacant units before agreement move-in. This data will automatically pre-fill when an agreement is created.
                    </p>
                </div>
                <a href="{{ route('inspection-reports.index', 'flat_inspection') }}"
                    class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition-all">
                    ← Back to Inspection History
                </a>
            </div>

            <form action="{{ route('inspection-reports.store', 'flat_inspection') }}"
                  method="POST" enctype="multipart/form-data" class="p-6 space-y-7">
                @csrf

                {{-- Unit, Date, Inspector & Condition Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    
                    {{-- Searchable Vacant Flat / Shop Dropdown --}}
                    <div x-data="{
                        unitId: '{{ old('unit_id', request('unit_id', '')) }}',
                        open: false,
                        search: '',
                        highlightedIndex: -1,
                        options: [
                            @foreach($units as $unit)
                                {
                                    id: '{{ $unit->id }}',
                                    unit_number: '{{ addslashes($unit->unit_number) }}',
                                    type: '{{ ucfirst($unit->type) }}',
                                    floor: '{{ addslashes($unit->floor ? $unit->floor->name : '') }}',
                                    block: '{{ addslashes($unit->block ? $unit->block->name : '') }}',
                                    searchLabel: '{{ strtolower(addslashes("unit " . $unit->unit_number . " " . $unit->type . " " . ($unit->floor ? $unit->floor->name : "") . " " . ($unit->block ? $unit->block->name : ""))) }}'
                                },
                            @endforeach
                        ],
                        get filteredOptions() {
                            if (!this.search) return this.options;
                            let s = this.search.toLowerCase().trim();
                            return this.options.filter(opt => opt.searchLabel.includes(s));
                        },
                        get selectedOption() {
                            return this.options.find(opt => String(opt.id) === String(this.unitId));
                        },
                        get selectedDisplay() {
                            if (!this.selectedOption) return 'Search & Select Vacant Flat / Shop...';
                            return 'Unit ' + this.selectedOption.unit_number + ' (' + this.selectedOption.type + (this.selectedOption.floor ? ' - ' + this.selectedOption.floor : '') + ')';
                        },
                        selectOption(opt) {
                            this.unitId = opt.id;
                            this.open = false;
                            this.search = '';
                            this.highlightedIndex = -1;
                        }
                    }" @click.outside="open = false" class="relative">
                        
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Select Vacant Flat / Shop <span class="text-red-500">*</span>
                        </label>

                        {{-- Hidden real input --}}
                        <input type="hidden" name="unit_id" :value="unitId" required />

                        {{-- Trigger Button --}}
                        <button type="button" @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()); }"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3.5 text-left text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 flex items-center justify-between shadow-xs transition-all @error('unit_id') border-red-500 @enderror">
                            <span x-text="selectedDisplay" :class="!unitId ? 'text-gray-400 font-normal' : 'font-bold text-gray-900 dark:text-white'"></span>
                            <svg class="h-4 w-4 text-gray-400 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open" x-cloak
                            class="absolute z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900 overflow-hidden">
                            
                            {{-- Search Input inside Dropdown --}}
                            <div class="p-2 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50">
                                <div class="relative">
                                    <input type="text" x-ref="searchInput" x-model="search" placeholder="Type unit #, floor, type..."
                                        @keydown.escape="open = false"
                                        class="h-9 w-full rounded-lg border border-gray-300 bg-white pl-8 pr-3 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            {{-- Options List --}}
                            <ul class="max-h-60 overflow-y-auto py-1 text-xs">
                                <template x-for="(opt, idx) in filteredOptions" :key="opt.id">
                                    <li @click="selectOption(opt)"
                                        class="flex items-center justify-between px-3.5 py-2.5 cursor-pointer transition-colors hover:bg-brand-50 dark:hover:bg-brand-900/30"
                                        :class="String(opt.id) === String(unitId) ? 'bg-brand-50 font-bold text-brand-600 dark:bg-brand-900/40 dark:text-brand-400' : 'text-gray-800 dark:text-gray-200'">
                                        <div>
                                            <span class="font-extrabold text-sm" x-text="'Unit ' + opt.unit_number"></span>
                                            <span class="text-[11px] text-gray-400 ml-1.5" x-text="'(' + opt.type + (opt.floor ? ' • ' + opt.floor : '') + ')'"></span>
                                        </div>
                                        <span class="text-[10px] uppercase font-bold text-amber-600 bg-amber-50 dark:bg-amber-900/30 dark:text-amber-300 px-2 py-0.5 rounded-full">
                                            Vacant
                                        </span>
                                    </li>
                                </template>
                                <template x-if="filteredOptions.length === 0">
                                    <li class="px-3.5 py-4 text-center text-gray-400 italic">
                                        No vacant units match your search.
                                    </li>
                                </template>
                            </ul>
                        </div>

                        @error('unit_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Inspection Date with Flatpickr Date Picker --}}
                    <div x-data="{
                        initPicker() {
                            const init = () => {
                                if (typeof flatpickr !== 'undefined') {
                                    flatpickr(this.$refs.picker, {
                                        dateFormat: 'Y-m-d',
                                        altInput: true,
                                        altFormat: 'd M Y',
                                        defaultDate: '{{ old('inspected_at', $today) }}',
                                        allowInput: false,
                                        static: true
                                    });
                                } else {
                                    setTimeout(init, 50);
                                }
                            };
                            this.$nextTick(init);
                        }
                    }" x-init="initPicker()">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Inspection Date <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" x-ref="picker" id="inspected_at" name="inspected_at"
                                value="{{ old('inspected_at', $today) }}" required
                                placeholder="Select inspection date..."
                                class="h-11 w-full rounded-lg border border-gray-300 pl-10 pr-3 text-sm bg-white text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('inspected_at') border-red-500 @enderror" />
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </span>
                        </div>
                        @error('inspected_at') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Inspection Person (Official Staff) --}}
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Inspection Person / Officer
                        </label>
                        <select name="inspection_person_id"
                            class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('inspection_person_id') border-red-500 @enderror">
                            <option value="">Select Official Officer...</option>
                            @foreach($inspectionPersons as $person)
                                <option value="{{ $person->id }}" {{ old('inspection_person_id') == $person->id ? 'selected' : '' }}>
                                    {{ $person->name }} ({{ $person->phone ?? 'Inspector' }})
                                </option>
                            @endforeach
                        </select>
                        @error('inspection_person_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Overall Flat Condition --}}
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Flat Condition
                        </label>
                        <select name="flat_condition"
                            class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="good" {{ old('flat_condition') == 'good' ? 'selected' : '' }}>🟢 Good Condition</option>
                            <option value="average" {{ old('flat_condition') == 'average' ? 'selected' : '' }}>🟡 Average Condition</option>
                            <option value="poor" {{ old('flat_condition') == 'poor' ? 'selected' : '' }}>🔴 Poor / Needs Repair</option>
                        </select>
                    </div>
                </div>

                {{-- Additional Member / Inspector Name --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Accompanying Inspection Member (Optional)
                        </label>
                        <input type="text" name="inspection_member" value="{{ old('inspection_member') }}"
                            placeholder="e.g. Supervisor Name, Guard on duty..."
                            class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Overall Inspection Remarks
                        </label>
                        <input type="text" name="remarks" value="{{ old('remarks') }}"
                            placeholder="Overall condition summary, keys handed over, electricity status..."
                            class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>
                </div>

                {{-- Checklist Table --}}
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-base font-extrabold text-gray-800 dark:text-white/90 flex items-center gap-2">
                            📋 Flat Inspection Checklist Items
                            <span class="text-sm font-normal text-gray-400">({{ $heads->count() }} heads)</span>
                        </h4>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-extrabold text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-3 w-10">#</th>
                                    <th class="px-4 py-3 w-52">Inspection Head</th>
                                    <th class="px-4 py-3 w-48 text-center">Status</th>
                                    <th class="px-4 py-3">Remarks / Description</th>
                                    <th class="px-4 py-3 w-44">Photo (Max 200KB)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($heads as $index => $head)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 font-bold text-gray-800 dark:text-white/90">
                                            {{ $head->name }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 p-1 gap-1 bg-white dark:bg-gray-900">
                                                <label class="flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold cursor-pointer transition-colors has-checked:bg-green-500 has-checked:text-white text-gray-600 dark:text-gray-300">
                                                    <input type="radio" name="items[{{ $head->id }}][status]" value="pass" class="sr-only"
                                                        {{ old("items.{$head->id}.status", 'pass') === 'pass' ? 'checked' : '' }} />
                                                    ✅ Pass
                                                </label>
                                                <label class="flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold cursor-pointer transition-colors has-checked:bg-red-500 has-checked:text-white text-gray-600 dark:text-gray-300">
                                                    <input type="radio" name="items[{{ $head->id }}][status]" value="fail" class="sr-only"
                                                        {{ old("items.{$head->id}.status") === 'fail' ? 'checked' : '' }} />
                                                    ❌ Fail
                                                </label>
                                                <label class="flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold cursor-pointer transition-colors has-checked:bg-gray-500 has-checked:text-white text-gray-600 dark:text-gray-300">
                                                    <input type="radio" name="items[{{ $head->id }}][status]" value="na" class="sr-only"
                                                        {{ old("items.{$head->id}.status") === 'na' ? 'checked' : '' }} />
                                                    N/A
                                                </label>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" name="items[{{ $head->id }}][remarks]"
                                                value="{{ old("items.{$head->id}.remarks") }}"
                                                placeholder="Remarks for {{ $head->name }}..."
                                                class="w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-1.5 text-xs text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none" />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="file" name="items[{{ $head->id }}][image]" accept="image/*"
                                                class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-gray-300" />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                            No active inspection heads found for Flat Inspection.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Action Footer --}}
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ route('inspection-reports.index', 'flat_inspection') }}"
                        class="px-5 py-2.5 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 text-xs font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-xl shadow-md transition-all">
                        Save Vacant Flat Inspection
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
