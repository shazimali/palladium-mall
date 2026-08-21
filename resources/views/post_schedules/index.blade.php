@extends('layouts.app')

@section('content')
    <div class="space-y-6"
        x-data="{
            copyModalOpen: false,
            sourceDay: '{{ $currentDay === 'all' ? 'monday' : $currentDay }}',
            selectedTargetDays: [],
            replaceExisting: false,
            openCopyModal(day) {
                this.sourceDay = day;
                this.selectedTargetDays = [];
                this.replaceExisting = false;
                this.copyModalOpen = true;
            },
            toggleAllTargetDays() {
                let all = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].filter(d => d !== this.sourceDay);
                if (this.selectedTargetDays.length === all.length) {
                    this.selectedTargetDays = [];
                } else {
                    this.selectedTargetDays = all;
                }
            }
        }">
        <x-common.page-breadcrumb pageTitle="Post Schedule" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">

            {{-- Top Action & Title Bar --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-xl font-black text-gray-900 dark:text-white/90 uppercase tracking-tight">
                        Daily Post Schedule & Duty Roster
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Manage day-wise task allocations and duty posts for office employees and mall staff
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    {{-- Manage Heads Button --}}
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('post_schedule_heads.view'))
                        <a href="{{ route('post-schedule-heads.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-all">
                            ⚙️ Manage Heads
                        </a>
                    @endif

                    {{-- Copy Schedule Button --}}
                    @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('post_schedules.create')) && $currentDay !== 'all')
                        <button @click="openCopyModal('{{ $currentDay }}')"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3.5 py-2 text-xs font-bold text-blue-700 hover:bg-blue-100 dark:border-blue-900/50 dark:bg-blue-900/20 dark:text-blue-300 transition-all cursor-pointer">
                            📋 Copy Day Tasks
                        </button>
                    @endif

                    {{-- Print Daily Schedule Button --}}
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('post_schedules.print') || auth()->user()->hasPermission('post_schedules.view'))
                        <a href="{{ route('post-schedules.print-daily', array_merge(request()->query(), ['day' => $currentDay])) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gray-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-gray-800 transition-all">
                            🖨️ Print Daily Sheet
                        </a>
                    @endif

                    {{-- Add Schedule Task Button --}}
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('post_schedules.create'))
                        <a href="{{ route('post-schedules.create', ['day' => $currentDay !== 'all' ? $currentDay : 'monday']) }}"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-brand-600 transition-all">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Post Task
                        </a>
                    @endif
                </div>
            </div>

            {{-- Day Selector Tabs --}}
            <div class="mb-6 border-b border-gray-200 dark:border-gray-800">
                <div class="flex overflow-x-auto gap-2 pb-2 scrollbar-none">
                    @foreach($days as $key => $dayName)
                        @php
                            $isActive = ($currentDay === $key);
                            $count = $dayCounts[$key] ?? 0;
                        @endphp
                        <a href="{{ route('post-schedules.index', array_merge(request()->except('page'), ['day' => $key])) }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $isActive ? 'bg-brand-500 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            <span>{{ $dayName }}</span>
                            <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] rounded-full font-black {{ $isActive ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                {{ $count }}
                            </span>
                        </a>
                    @endforeach

                    @php
                        $isAllActive = ($currentDay === 'all');
                    @endphp
                    <a href="{{ route('post-schedules.index', array_merge(request()->except('page'), ['day' => 'all'])) }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $isAllActive ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                        <span>All Days</span>
                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] rounded-full font-black {{ $isAllActive ? 'bg-white/20 text-white dark:bg-gray-900/20 dark:text-gray-900' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                            {{ $dayCounts['all'] ?? 0 }}
                        </span>
                    </a>
                </div>
            </div>

            {{-- Filter Bar --}}
            <form action="{{ route('post-schedules.index') }}" method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input type="hidden" name="day" value="{{ $currentDay }}">

                <div>
                    <select name="post_schedule_head_id" onchange="this.form.submit()"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2 text-xs text-gray-800 dark:text-white/90 focus:border-brand-500 focus:outline-none">
                        <option value="">All Post Heads</option>
                        @foreach($heads as $head)
                            <option value="{{ $head->id }}" {{ request('post_schedule_head_id') == $head->id ? 'selected' : '' }}>
                                {{ $head->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2 flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search employee, duty, location..."
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2 text-xs text-gray-800 dark:text-white/90 focus:border-brand-500 focus:outline-none">
                    
                    <button type="submit"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl transition-all">
                        Filter
                    </button>
                    @if(request('search') || request('post_schedule_head_id'))
                        <a href="{{ route('post-schedules.index', ['day' => $currentDay]) }}"
                            class="px-3 py-2 text-gray-400 hover:text-gray-600 text-xs font-bold flex items-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            {{-- Post Schedule Tasks List --}}
            <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            @if($currentDay === 'all')
                                <th class="px-4 py-3">Day</th>
                            @endif
                            <th class="px-4 py-3">Post Head</th>
                            <th class="px-4 py-3">Location / Area</th>
                            <th class="px-4 py-3">Timing / Shift</th>
                            <th class="px-4 py-3">Assigned Employee</th>
                            <th class="px-4 py-3">Task & Duties</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($schedules as $index => $schedule)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $schedules->firstItem() + $index }}</td>
                                @if($currentDay === 'all')
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 uppercase">
                                            {{ $schedule->day_name }}
                                        </span>
                                    </td>
                                @endif
                                <td class="px-4 py-3">
                                    @if($schedule->head)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold border {{ $schedule->head->badge_class }}">
                                            {{ $schedule->head->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $schedule->location ?: '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs font-mono font-bold text-gray-700 dark:text-gray-300">
                                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                        ⏱️ {{ $schedule->shift_display }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-extrabold text-gray-900 dark:text-white/90 text-sm">
                                        {{ $schedule->employee_name }}
                                    </div>
                                    @if($schedule->user)
                                        <div class="text-[11px] text-brand-600 dark:text-brand-400 font-medium">
                                            👤 Account: {{ $schedule->user->name }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900 dark:text-white/90">
                                        {{ $schedule->task_title }}
                                    </div>
                                    @if($schedule->duties)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                            {{ $schedule->duties }}
                                        </div>
                                    @endif
                                    @if($schedule->notes)
                                        <div class="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5 italic">
                                            Note: {{ $schedule->notes }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('post_schedules.edit'))
                                            <a href="{{ route('post-schedules.edit', $schedule) }}"
                                                class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                                title="Edit">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                        @endif

                                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('post_schedules.delete'))
                                            <form action="{{ route('post-schedules.destroy', $schedule) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this schedule entry?');"
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
                                <td colspan="{{ $currentDay === 'all' ? 8 : 7 }}" class="px-4 py-12 text-center text-gray-400">
                                    <div class="text-3xl mb-2">📋</div>
                                    <p class="font-bold text-gray-600 dark:text-gray-300">No post duties scheduled for this day.</p>
                                    <p class="text-xs text-gray-400 mt-1">Click "Add Post Task" to schedule duties or copy from another day.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($schedules->hasPages())
                <div class="mt-4">
                    {{ $schedules->links() }}
                </div>
            @endif

            {{-- Copy to Other Days Modal --}}
            <div x-show="copyModalOpen" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
                <div @click.outside="copyModalOpen = false"
                    class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">
                            Copy Schedule Duties
                        </h3>
                        <button @click="copyModalOpen = false" class="text-gray-400 hover:text-gray-500 text-xl font-bold">✕</button>
                    </div>

                    <form action="{{ route('post-schedules.copy-days') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="source_day" :value="sourceDay">

                        <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-900/40 text-xs text-blue-800 dark:text-blue-300">
                            Copying all post tasks from <strong class="uppercase" x-text="sourceDay"></strong> to selected days:
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Target Days</label>
                                <button type="button" @click="toggleAllTargetDays()" class="text-xs text-brand-600 dark:text-brand-400 font-bold hover:underline">
                                    Select All / Deselect
                                </button>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($days as $dKey => $dName)
                                    <template x-if="sourceDay !== '{{ $dKey }}'">
                                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer text-xs font-semibold">
                                            <input type="checkbox" name="target_days[]" value="{{ $dKey }}" x-model="selectedTargetDays"
                                                class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                            <span>{{ $dName }}</span>
                                        </label>
                                    </template>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-2">
                            <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" name="replace_existing" value="1" x-model="replaceExisting"
                                    class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                <span>Replace existing duties on target days</span>
                            </label>
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <button type="button" @click="copyModalOpen = false"
                                class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-all">
                                Cancel
                            </button>
                            <button type="submit" :disabled="selectedTargetDays.length === 0"
                                class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 rounded-xl shadow-xs transition-all">
                                Copy Tasks
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
