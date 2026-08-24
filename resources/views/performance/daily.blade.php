@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="My Daily Entry" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(!$user->isEmployee())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-8 text-center dark:border-red-800 dark:bg-red-900/20">
            <p class="text-red-600 dark:text-red-400 font-semibold">You are not registered as an employee. Please contact the administrator.</p>
        </div>
    @elseif($templates->isEmpty())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center dark:border-amber-800 dark:bg-amber-900/20">
            <p class="text-amber-700 dark:text-amber-400 font-semibold">No task templates assigned to you yet. Please contact the Super Admin.</p>
        </div>
    @else
        <form action="{{ route('performance.daily.save') }}" method="POST" x-data="dailyEntry()">
            @csrf

            {{-- Date + Month Stats Header --}}
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Daily Performance Entry</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->name }} · Mark your tasks and attendance for the selected date</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-semibold text-gray-600 dark:text-gray-400">Date:</label>
                        <input type="date" name="date" value="{{ $date }}" max="{{ today()->toDateString() }}"
                            onchange="window.location.href='?date='+this.value"
                            class="rounded-xl border border-gray-300 px-3.5 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 cursor-pointer">
                    </div>
                </div>

                {{-- Month running total --}}
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @php
                        $gc = ['Excellent'=>'green','Good'=>'blue','Average'=>'amber','Poor'=>'red'][$monthScore['grade']] ?? 'gray';
                    @endphp
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs text-gray-400">Month Points</p>
                        <p class="text-base font-bold text-gray-900 dark:text-white">
                            {{ number_format($monthScore['total_earned_points'], 0) }}<span class="text-xs text-gray-400">/{{ number_format($monthScore['total_max_points'], 0) }}</span>
                        </p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs text-gray-400">Performance</p>
                        <p class="text-base font-bold text-{{ $gc }}-600 dark:text-{{ $gc }}-400">{{ $monthScore['performance_percentage'] }}%</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs text-gray-400">Grade</p>
                        <p class="text-base font-bold text-{{ $gc }}-600 dark:text-{{ $gc }}-400">{{ $monthScore['grade'] }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-gray-800 dark:bg-white/[0.03]">
                        <p class="text-xs text-gray-400">Attendance</p>
                        <p class="text-base font-bold text-gray-900 dark:text-white">{{ $monthScore['days_present'] }}/{{ $workingDays }} days</p>
                    </div>
                </div>
            </div>

            {{-- Attendance Card --}}
            <div class="mb-5 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                    </svg>
                    Attendance
                </h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">Status <span class="text-red-500">*</span></label>
                        <select name="attendance_status" required
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                            @foreach(['present'=>'Present','half_day'=>'Half Day','leave'=>'On Leave','absent'=>'Absent'] as $val => $label)
                                <option value="{{ $val }}" @selected($attendance?->status === $val || (!$attendance && $val === 'present'))>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">Check In</label>
                        <input type="time" name="check_in_at" value="{{ $attendance?->check_in_at }}"
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">Check Out</label>
                        <input type="time" name="check_out_at" value="{{ $attendance?->check_out_at }}"
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">Note</label>
                        <input type="text" name="attendance_note" value="{{ $attendance?->note }}" placeholder="Optional..."
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                    </div>
                </div>
            </div>

            {{-- Tasks Grid --}}
            <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        Daily Tasks
                    </h3>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Today's points: <span class="font-bold text-brand-600 dark:text-brand-400" x-text="todayPoints + ' pts'"></span>
                        </span>
                        <button type="button" @click="markAll(true)"
                            class="rounded-lg border border-green-300 px-3 py-1 text-xs font-medium text-green-700 hover:bg-green-50 dark:border-green-700 dark:text-green-400 dark:hover:bg-green-900/20 transition-colors">
                            Mark All Done
                        </button>
                        <button type="button" @click="markAll(false)"
                            class="rounded-lg border border-gray-300 px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                            Clear All
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($templates as $i => $template)
                        @php
                            $existing = $existingEntries->get($template->id);
                            $isDone   = $existing ? $existing->is_done : false;
                            $dailyPts = round($template->monthly_points / cal_days_in_month(CAL_GREGORIAN, now()->month, now()->year), 1);
                        @endphp
                        <div class="relative rounded-xl border-2 p-4 transition-all cursor-pointer"
                            :class="tasks[{{ $i }}].is_done
                                ? 'border-green-300 bg-green-50 dark:border-green-700 dark:bg-green-900/20'
                                : 'border-gray-200 bg-gray-50 hover:border-gray-300 dark:border-gray-800 dark:bg-white/[0.02]'"
                            @click="toggleTask({{ $i }})">

                            <input type="hidden" name="tasks[{{ $i }}][template_id]" value="{{ $template->id }}">
                            <input type="hidden" :name="`tasks[{{ $i }}][is_done]`" :value="tasks[{{ $i }}].is_done ? 1 : 0">

                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $template->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">+{{ $dailyPts }} pts today</p>
                                </div>
                                <div class="shrink-0">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full border-2 transition-colors"
                                        :class="tasks[{{ $i }}].is_done
                                            ? 'bg-green-500 border-green-500 text-white'
                                            : 'border-gray-300 dark:border-gray-600'">
                                        <svg x-show="tasks[{{ $i }}].is_done" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-8 py-3 text-sm font-bold text-white hover:bg-brand-600 transition-colors shadow-md cursor-pointer">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    Save Daily Entry
                </button>
            </div>
        </form>
    @endif
@endsection

@push('scripts')
<script>
function dailyEntry() {
    return {
        tasks: @json($templates->map(fn($t) => [
            'template_id'   => $t->id,
            'monthly_points'=> (float) $t->monthly_points,
            'is_done'       => (bool) ($existingEntries[$t->id]?->is_done ?? false),
        ])->values()),
        workingDays: {{ cal_days_in_month(CAL_GREGORIAN, now()->month, now()->year) }},

        get todayPoints() {
            return this.tasks
                .filter(t => t.is_done)
                .reduce((s, t) => s + parseFloat((t.monthly_points / this.workingDays).toFixed(1)), 0)
                .toFixed(1);
        },

        toggleTask(index) {
            this.tasks[index].is_done = !this.tasks[index].is_done;
        },

        markAll(state) {
            this.tasks = this.tasks.map(t => ({ ...t, is_done: state }));
        }
    }
}
</script>
@endpush
