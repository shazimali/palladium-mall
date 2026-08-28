@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Flat Inspection History" />

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 space-y-6">

        {{-- Top Action & Title Bar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                    Flat & Shop Inspection History
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Track the lifecycle and condition history of flats across Vacant inspections, Move-In, and Move-Out stages
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('flat_inspections.create'))
                    <a href="{{ route('inspection-reports.create', 'flat_inspection') }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-brand-500 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-brand-600 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Inspect Vacant Flat
                    </a>
                @endif
            </div>
        </div>

        {{-- Filters Bar --}}
        <form method="GET" action="{{ route('inspection-reports.index', 'flat_inspection') }}" id="filterForm"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-700/60">
            
            {{-- Unit Filter (Searchable Combobox) --}}
            <div x-data="{
                unitId: '{{ request('unit_id', '') }}',
                open: false,
                search: '',
                options: [
                    { id: '', unit_number: '', searchLabel: 'all units', display: 'All Units' },
                    @foreach($units as $unit)
                        {
                            id: '{{ $unit->id }}',
                            unit_number: '{{ addslashes($unit->unit_number) }}',
                            type: '{{ ucfirst($unit->type) }}',
                            floor: '{{ addslashes($unit->floor ? $unit->floor->name : '') }}',
                            searchLabel: '{{ strtolower(addslashes("unit " . $unit->unit_number . " " . $unit->type . " " . ($unit->floor ? $unit->floor->name : ""))) }}',
                            display: 'Unit {{ addslashes($unit->unit_number) }} ({{ ucfirst($unit->type) }}{{ $unit->floor ? ' - ' . addslashes($unit->floor->name) : '' }})'
                        },
                    @endforeach
                ],
                get filteredOptions() {
                    if (!this.search) return this.options;
                    let s = this.search.toLowerCase().trim();
                    return this.options.filter(opt => opt.searchLabel.includes(s));
                },
                get selectedOption() {
                    return this.options.find(opt => String(opt.id) === String(this.unitId)) || this.options[0];
                },
                selectOption(opt) {
                    this.unitId = opt.id;
                    this.open = false;
                    this.search = '';
                    $nextTick(() => {
                        document.getElementById('filterForm').submit();
                    });
                }
            }" @click.outside="open = false" class="relative">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Unit / Flat</label>
                <input type="hidden" name="unit_id" :value="unitId" />

                <button type="button" @click="open = !open; if(open) { $nextTick(() => $refs.unitSearchInput.focus()); }"
                    class="h-9 w-full rounded-xl border border-gray-300 bg-white px-3 text-left text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white flex items-center justify-between shadow-2xs">
                    <span x-text="selectedOption.display" class="truncate font-semibold"></span>
                    <svg class="h-3.5 w-3.5 text-gray-400 shrink-0 ml-1 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-cloak
                    class="absolute z-50 mt-1 w-64 sm:w-full rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900 overflow-hidden">
                    <div class="p-2 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50">
                        <input type="text" x-ref="unitSearchInput" x-model="search" placeholder="Search unit..."
                            @keydown.escape="open = false"
                            class="h-8 w-full rounded-lg border border-gray-300 bg-white px-2.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <ul class="max-h-56 overflow-y-auto py-1 text-xs">
                        <template x-for="opt in filteredOptions" :key="opt.id">
                            <li @click="selectOption(opt)"
                                class="px-3 py-2 cursor-pointer transition-colors hover:bg-brand-50 dark:hover:bg-brand-900/30"
                                :class="String(opt.id) === String(unitId) ? 'bg-brand-50 font-bold text-brand-600 dark:bg-brand-900/40 dark:text-brand-400' : 'text-gray-800 dark:text-gray-200'">
                                <span x-text="opt.display"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- Stage Filter --}}
            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Inspection Stage</label>
                <select name="stage" onchange="this.form.submit()"
                    class="h-9 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 text-xs text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none font-semibold">
                    <option value="">All Stages</option>
                    <option value="vacant" {{ request('stage') == 'vacant' ? 'selected' : '' }}>🟡 Vacant Inspection</option>
                    <option value="move_in" {{ request('stage') == 'move_in' ? 'selected' : '' }}>🟢 Move-In Inspection</option>
                    <option value="move_out" {{ request('stage') == 'move_out' ? 'selected' : '' }}>🔴 Move-Out Inspection</option>
                </select>
            </div>

            {{-- Employee / Inspector Filter --}}
            <div>
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Inspector / Employee</label>
                <select name="employee_id" onchange="this.form.submit()"
                    class="h-9 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 text-xs text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none font-semibold">
                    <option value="">All Inspectors</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id || request('reported_by') == $emp->id)>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Date From with Flatpickr --}}
            <div x-data="{
                initPicker() {
                    const init = () => {
                        if (typeof flatpickr !== 'undefined') {
                            flatpickr(this.$refs.fromPicker, {
                                dateFormat: 'Y-m-d',
                                altInput: true,
                                altFormat: 'd M Y',
                                defaultDate: '{{ request('date_from') }}',
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
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">From Date</label>
                <div class="relative">
                    <input type="text" x-ref="fromPicker" name="date_from" value="{{ request('date_from') }}"
                        placeholder="Select from date..."
                        class="h-9 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 pl-8 pr-2.5 text-xs text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none" />
                    <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                </div>
            </div>

            {{-- Date To with Flatpickr --}}
            <div x-data="{
                initPicker() {
                    const init = () => {
                        if (typeof flatpickr !== 'undefined') {
                            flatpickr(this.$refs.toPicker, {
                                dateFormat: 'Y-m-d',
                                altInput: true,
                                altFormat: 'd M Y',
                                defaultDate: '{{ request('date_to') }}',
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
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">To Date</label>
                <div class="relative">
                    <input type="text" x-ref="toPicker" name="date_to" value="{{ request('date_to') }}"
                        placeholder="Select to date..."
                        class="h-9 w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 pl-8 pr-2.5 text-xs text-gray-800 dark:text-white focus:border-brand-500 focus:outline-none" />
                    <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="w-full h-9 px-4 bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold rounded-xl transition-all shadow-2xs">
                    Filter
                </button>
                @if(request()->anyFilled(['unit_id', 'stage', 'employee_id', 'reported_by', 'date_from', 'date_to']))
                    <a href="{{ route('inspection-reports.index', 'flat_inspection') }}"
                        class="h-9 px-3 text-xs font-bold text-gray-500 hover:text-gray-800 flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        {{-- Inspection History Table --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-800">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400 font-extrabold">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Flat / Unit</th>
                        <th class="px-4 py-3">Inspection Stage / Status</th>
                        <th class="px-4 py-3">Tenant / Agreement</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Inspector / Officer</th>
                        <th class="px-4 py-3 text-center">Results</th>
                        <th class="px-4 py-3 text-left">Admin Feedback</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($reports as $index => $rep)
                        @php
                            $effectiveUnit = $rep->effective_unit;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $reports->firstItem() + $index }}</td>
                            
                            {{-- Unit / Flat --}}
                            <td class="px-4 py-3">
                                <div class="font-extrabold text-gray-900 dark:text-white text-sm">
                                    Unit {{ $effectiveUnit?->unit_number ?? '—' }}
                                </div>
                                <div class="text-[11px] text-gray-400">
                                    {{ ucfirst($effectiveUnit?->type ?? 'Flat') }}{{ $effectiveUnit?->floor ? ' • ' . $effectiveUnit->floor->name : '' }}
                                </div>
                            </td>

                            {{-- Inspection Stage Badge --}}
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black border {{ $rep->stage_badge_class }}">
                                    @if($rep->type === 'vacant')
                                        🟡 Vacant Inspection
                                    @elseif($rep->type === 'move_in')
                                        🟢 Move In
                                    @elseif($rep->type === 'move_out')
                                        🔴 Move Out
                                    @else
                                        🔵 {{ $rep->type_label }}
                                    @endif
                                </span>
                            </td>

                            {{-- Tenant / Agreement --}}
                            <td class="px-4 py-3">
                                @if($rep->tenant)
                                    <div class="font-bold text-gray-900 dark:text-white text-xs">
                                        {{ $rep->tenant->name }}
                                    </div>
                                    @if($rep->agreement_id)
                                        <div class="text-[10px] text-brand-600 dark:text-brand-400 font-semibold">
                                            Agreement #{{ $rep->agreement_id }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-amber-600 dark:text-amber-400 font-semibold italic">
                                        (Vacant Flat Inspection)
                                    </span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="px-4 py-3 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ $rep->inspected_at?->format('d M Y') ?? '—' }}
                            </td>

                            {{-- Inspector & Officer --}}
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-800 dark:text-gray-200 text-xs">
                                    {{ $rep->inspectionPerson?->name ?? ($rep->inspection_member ?: ($rep->inspector?->name ?? 'Admin')) }}
                                </div>
                                @if($rep->inspector && $rep->inspectionPerson)
                                    <div class="text-[10px] text-gray-400">
                                        Logged by: {{ $rep->inspector->name }}
                                    </div>
                                @endif
                            </td>

                            {{-- Pass / Fail Counts --}}
                            <td class="px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-1.5 text-xs font-bold font-mono">
                                    <span class="text-green-600 bg-green-50 dark:bg-green-900/30 px-2 py-0.5 rounded-md">
                                        ✅ {{ $rep->passCount() }}
                                    </span>
                                    @if($rep->failCount() > 0)
                                        <span class="text-red-600 bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-md">
                                            ❌ {{ $rep->failCount() }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Admin Feedback --}}
                            <td class="px-4 py-3 text-xs">
                                @php
                                    $satCount = $rep->items->where('admin_rating', 'good')->count();
                                    $unsatCount = $rep->items->where('admin_rating', 'bad')->count();
                                    $totalEvaluated = $satCount + $unsatCount;
                                @endphp
                                @if($totalEvaluated > 0)
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @if($satCount > 0)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                ✨ {{ $satCount }} Sat
                                            </span>
                                        @endif
                                        @if($unsatCount > 0)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-2 py-0.5 text-[11px] font-bold text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                ⚠️ {{ $unsatCount }} Unsat
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('inspection-reports.print', ['type' => 'flat_inspection', 'report' => $rep->id]) }}" target="_blank"
                                        class="rounded-lg p-1.5 text-gray-600 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                        title="Print Inspection Report">
                                        🖨️
                                    </a>

                                    <a href="{{ route('inspection-reports.show', ['type' => 'flat_inspection', 'report' => $rep->id]) }}"
                                        class="rounded-lg p-1.5 text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors"
                                        title="View Details">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('flat_inspections.edit'))
                                        <a href="{{ route('inspection-reports.edit', ['type' => 'flat_inspection', 'report' => $rep->id]) }}"
                                            class="rounded-lg p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                            title="Edit Inspection">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif

                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('flat_inspections.delete'))
                                        <form action="{{ route('inspection-reports.destroy', ['type' => 'flat_inspection', 'report' => $rep->id]) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this inspection record?');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                title="Delete">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                <div class="text-3xl mb-2">📋</div>
                                <p class="font-bold text-gray-600 dark:text-gray-300">No flat inspections found.</p>
                                <p class="text-xs text-gray-400 mt-1">Click "Inspect Vacant Flat" to conduct an inspection for a vacant unit.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div>
                {{ $reports->links() }}
            </div>
        @endif

    </div>
@endsection
