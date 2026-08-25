@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Task Templates — {{ $user->name }}" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <x-common.component-card
        title="Manage Daily Task Templates & Performance Weights"
        desc="Assign dynamic inspection reports, linked tasks, and custom daily tasks with editable monthly performance amounts for {{ $user->name }}. Super Admin only.">

        <div class="mb-5 flex items-center gap-3 rounded-xl border border-indigo-200 bg-indigo-50/50 p-4 text-xs text-indigo-900 dark:border-indigo-800 dark:bg-indigo-950/30 dark:text-indigo-200">
            <svg class="h-5 w-5 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            <div class="space-y-0.5">
                <p class="font-bold">How Performance Scoring Works:</p>
                <p>Each task's daily score = <strong class="font-bold">Monthly Points Amount ÷ Days in Month</strong>. When this employee completes dynamic reports, assigned tasks, or daily tasks, the system calculates their earned performance score.</p>
            </div>
        </div>

        <form action="{{ route('users.tasks.store', $user) }}" method="POST" x-data="taskManager()" @submit.prevent="submitForm">
            @csrf

            <div class="overflow-hidden border border-gray-200 rounded-2xl dark:border-gray-800 mb-5 shadow-2xs">
                <table class="w-full text-xs text-left text-gray-600 dark:text-gray-400">
                    <thead class="uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400 text-[11px] border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3 w-10 text-center">#</th>
                            <th class="px-4 py-3 w-40">Type</th>
                            <th class="px-4 py-3">Task Source & Title</th>
                            <th class="px-4 py-3 w-56">Division / Frequency</th>
                            <th class="px-4 py-3 w-40">Monthly Amount</th>
                            <th class="px-4 py-3 w-20 text-center">Active</th>
                            <th class="px-4 py-3 w-16 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tasks-body" class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        <template x-for="(task, index) in tasks" :key="task.key">
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3 text-gray-400 font-mono text-center" x-text="index + 1"></td>
                                
                                {{-- Type Selection --}}
                                <td class="px-4 py-3">
                                    <select :name="`tasks[${index}][type]`" x-model="task.type" @change="onTypeChange(task)"
                                        class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs font-bold dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                                        <option value="custom">✍️ Custom Task</option>
                                        <option value="dynamic_report">📋 Dynamic Report</option>
                                        <option value="task">📌 Assigned Task</option>
                                    </select>
                                </td>

                                {{-- Details / Source & Title --}}
                                <td class="px-4 py-3 space-y-2">
                                    {{-- If Dynamic Report --}}
                                    <template x-if="task.type === 'dynamic_report'">
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-brand-600 dark:text-brand-400">Select Report:</span>
                                                <select :name="`tasks[${index}][report_type_id]`" x-model="task.report_type_id" @change="onReportTypeChange(task)"
                                                    class="flex-1 rounded-xl border border-brand-300 bg-brand-50/40 px-2.5 py-1.5 text-xs font-bold text-gray-900 dark:border-brand-800 dark:bg-brand-950/40 dark:text-brand-200 focus:outline-none focus:ring-2 focus:ring-brand-500/30">
                                                    <option value="">-- Choose Dynamic Report --</option>
                                                    <template x-for="r in reportTypes" :key="r.id">
                                                        <option :value="r.id" x-text="r.name + (r.is_daily ? ' (Daily)' : '')"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <input type="text" :name="`tasks[${index}][name]`" x-model="task.name" placeholder="Display Title on Performance Log" required
                                                class="w-full rounded-xl border border-gray-300 px-3 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/30">
                                        </div>
                                    </template>

                                    {{-- If Assigned Task --}}
                                    <template x-if="task.type === 'task'">
                                        <div class="space-y-1.5">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Select Task:</span>
                                                <select :name="`tasks[${index}][task_id]`" x-model="task.task_id" @change="onTaskChange(task)"
                                                    class="flex-1 rounded-xl border border-indigo-300 bg-indigo-50/40 px-2.5 py-1.5 text-xs font-bold text-gray-900 dark:border-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                                    <option value="">-- Choose Task --</option>
                                                    <template x-for="t in tasksList" :key="t.id">
                                                        <option :value="t.id" x-text="t.title"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <input type="text" :name="`tasks[${index}][name]`" x-model="task.name" placeholder="Display Title on Performance Log" required
                                                class="w-full rounded-xl border border-gray-300 px-3 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                                        </div>
                                    </template>

                                    {{-- If Custom Task --}}
                                    <template x-if="task.type === 'custom'">
                                        <div>
                                            <input type="text" :name="`tasks[${index}][name]`" x-model="task.name"
                                                placeholder="e.g. Daily Area Inspection, Floor Maintenance..." required
                                                class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 font-medium">
                                        </div>
                                    </template>

                                    <input type="hidden" :name="`tasks[${index}][id]`" :value="task.id || ''">
                                    <input type="hidden" :name="`tasks[${index}][sort_order]`" :value="index">
                                </td>

                                {{-- Frequency & Division (Daily vs Count based) --}}
                                <td class="px-4 py-3 space-y-1.5">
                                    <div class="flex items-center gap-3">
                                        <label class="inline-flex items-center gap-1 cursor-pointer">
                                            <input type="radio" :name="`tasks[${index}][is_daily]`" value="1"
                                                :checked="task.is_daily" @change="task.is_daily = true"
                                                class="w-3.5 h-3.5 text-brand-500 focus:ring-brand-500">
                                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200">Daily</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1 cursor-pointer">
                                            <input type="radio" :name="`tasks[${index}][is_daily]`" value="0"
                                                :checked="!task.is_daily" @change="task.is_daily = false"
                                                class="w-3.5 h-3.5 text-brand-500 focus:ring-brand-500">
                                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200">Count-Based</span>
                                        </label>
                                    </div>

                                    <template x-if="!task.is_daily">
                                        <div class="flex items-center gap-1.5 pt-1">
                                            <span class="text-[10px] text-gray-500 uppercase font-bold">Target Count:</span>
                                            <input type="number" :name="`tasks[${index}][target_count]`" x-model="task.target_count"
                                                min="1" max="100" placeholder="1"
                                                class="w-16 rounded-lg border border-gray-300 px-2 py-1 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white font-mono">
                                            <span class="text-[10px] text-gray-400">tasks/mo</span>
                                        </div>
                                    </template>

                                    <div class="text-[10px] font-bold text-brand-600 dark:text-brand-400">
                                        <span x-text="getUnitPreview(task)"></span>
                                    </div>
                                </td>

                                {{-- Editable Monthly Points / Amount --}}
                                <td class="px-4 py-3">
                                    <div class="relative">
                                        <input type="number" :name="`tasks[${index}][monthly_points]`" x-model="task.monthly_points"
                                            min="0" step="10" placeholder="5000" required
                                            class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs font-mono font-bold dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                                        <span class="absolute right-3 top-2 text-[10px] font-bold text-gray-400">pts</span>
                                    </div>
                                </td>

                                {{-- Active Toggle --}}
                                <td class="px-4 py-3 text-center">
                                    <input type="hidden" :name="`tasks[${index}][is_active]`" value="0">
                                    <input type="checkbox" :name="`tasks[${index}][is_active]`" value="1"
                                        x-model="task.is_active"
                                        class="w-4 h-4 rounded text-brand-500 border-gray-300 focus:ring-brand-500 cursor-pointer">
                                </td>

                                {{-- Remove Action --}}
                                <td class="px-4 py-3 text-right">
                                    <template x-if="task.id">
                                        <form :action="`/users/{{ $user->id }}/tasks/${task.id}`" method="POST"
                                            @submit.prevent="deleteTask(task, $el.closest('form'))">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Delete Template"
                                                class="rounded-lg p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors cursor-pointer">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </template>
                                    <template x-if="!task.id">
                                        <button type="button" @click="removeTask(index)" title="Remove Row"
                                            class="rounded-lg p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors cursor-pointer">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                            <td colspan="3" class="px-4 py-3 text-xs font-extrabold uppercase text-gray-700 dark:text-gray-300">Total Monthly Active Max Points</td>
                            <td class="px-4 py-3 font-black text-gray-900 dark:text-white font-mono text-sm" x-text="totalPoints().toLocaleString() + ' pts'"></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Action Toolbar --}}
            <div class="flex flex-wrap items-center justify-between gap-4 pt-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" @click="addTask('custom')"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-700 px-3.5 py-2 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-2xs cursor-pointer">
                        ✍️ Add Custom Task
                    </button>
                    <button type="button" @click="addTask('dynamic_report')"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-brand-300 bg-brand-50/60 dark:bg-brand-950/30 dark:border-brand-800 px-3.5 py-2 text-xs font-bold text-brand-700 dark:text-brand-300 hover:bg-brand-100 transition-colors shadow-2xs cursor-pointer">
                        📋 Add Dynamic Report
                    </button>
                    <button type="button" @click="addTask('task')"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-300 bg-indigo-50/60 dark:bg-indigo-950/30 dark:border-indigo-800 px-3.5 py-2 text-xs font-bold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 transition-colors shadow-2xs cursor-pointer">
                        📌 Add Assigned Task
                    </button>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('users.show', $user) }}"
                       class="rounded-xl border border-gray-300 px-5 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-brand-500 px-6 py-2.5 text-xs font-black text-white hover:bg-brand-600 transition-colors shadow-md cursor-pointer">
                        💾 Save All Task Templates
                    </button>
                </div>
            </div>
        </form>

    </x-common.component-card>
@endsection

@push('scripts')
@php
$tasksJson = $templates->map(fn($t) => [
    'id'             => $t->id,
    'type'           => $t->type ?? 'custom',
    'name'           => $t->name,
    'report_type_id' => $t->report_type_id ? (int) $t->report_type_id : null,
    'task_id'        => $t->task_id ? (int) $t->task_id : null,
    'monthly_points' => (float) $t->monthly_points,
    'is_daily'       => (bool) ($t->is_daily ?? true),
    'target_count'   => (int) ($t->target_count ?? 1),
    'sort_order'     => (int) $t->sort_order,
    'is_active'      => (bool) $t->is_active,
    'key'            => 'task_' . $t->id,
])->values();

$reportTypesJson = $reportTypes->map(fn($r) => [
    'id'       => $r->id,
    'name'     => $r->name,
    'key'      => $r->key,
    'is_daily' => (bool) $r->is_daily,
])->values();

$tasksListJson = $tasks->map(fn($t) => [
    'id'    => $t->id,
    'title' => '#' . $t->id . ' ' . $t->title . ($t->category ? ' (' . $t->category->name . ')' : ''),
])->values();
@endphp
<script>
function taskManager() {
    return {
        tasks: @json($tasksJson),
        reportTypes: @json($reportTypesJson),
        tasksList: @json($tasksListJson),
        keyCounter: {{ $templates->count() + 1 }},

        addTask(type = 'custom') {
            let defaultName = '';
            let defaultReportTypeId = null;
            let defaultTaskId = null;
            let defaultIsDaily = true;

            if (type === 'dynamic_report' && this.reportTypes.length > 0) {
                defaultReportTypeId = this.reportTypes[0].id;
                defaultName = this.reportTypes[0].name;
                defaultIsDaily = this.reportTypes[0].is_daily;
            } else if (type === 'task' && this.tasksList.length > 0) {
                defaultTaskId = this.tasksList[0].id;
                defaultName = this.tasksList[0].title;
            }

            this.tasks.push({
                id: null,
                type: type,
                name: defaultName,
                report_type_id: defaultReportTypeId,
                task_id: defaultTaskId,
                monthly_points: 0,
                is_daily: defaultIsDaily,
                target_count: 1,
                sort_order: this.tasks.length,
                is_active: true,
                key: 'new_' + this.keyCounter++,
            });
        },

        getUnitPreview(task) {
            const pts = parseFloat(task.monthly_points) || 0;
            if (pts <= 0) return '0 pts/unit';
            if (task.is_daily) {
                const days = 31;
                const daily = Math.round(pts / days);
                return `~${daily} pts/day (÷ ${days} days)`;
            } else {
                const count = Math.max(1, parseInt(task.target_count) || 1);
                const perTask = Math.round(pts / count);
                return `~${perTask} pts/task (÷ ${count} count)`;
            }
        },

        onTypeChange(task) {
            if (task.type === 'dynamic_report' && this.reportTypes.length > 0) {
                if (!task.report_type_id) {
                    task.report_type_id = this.reportTypes[0].id;
                    task.name = this.reportTypes[0].name;
                    task.is_daily = this.reportTypes[0].is_daily;
                }
            } else if (task.type === 'task' && this.tasksList.length > 0) {
                if (!task.task_id) {
                    task.task_id = this.tasksList[0].id;
                    task.name = this.tasksList[0].title;
                }
            }
        },

        onReportTypeChange(task) {
            const selected = this.reportTypes.find(r => r.id == task.report_type_id);
            if (selected) {
                if (!task.name || this.reportTypes.some(r => r.name === task.name)) {
                    task.name = selected.name;
                }
                task.is_daily = selected.is_daily;
            }
        },

        onTaskChange(task) {
            const selected = this.tasksList.find(t => t.id == task.task_id);
            if (selected && (!task.name || this.tasksList.some(t => t.title === task.name))) {
                task.name = selected.title;
            }
        },

        removeTask(index) {
            this.tasks.splice(index, 1);
        },

        deleteTask(task, form) {
            if (!confirm('Remove this task template? Existing daily performance entries for this task will be removed.')) return;
            this.tasks = this.tasks.filter(t => t.key !== task.key);
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: new FormData(form),
            }).then(r => { if (!r.ok) alert('Failed to delete task.'); });
        },

        totalPoints() {
            return this.tasks.filter(t => t.is_active).reduce((s, t) => s + (parseFloat(t.monthly_points) || 0), 0);
        },

        submitForm() {
            this.$el.submit();
        }
    }
}
</script>
@endpush

