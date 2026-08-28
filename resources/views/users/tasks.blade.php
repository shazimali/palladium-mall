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
                <p class="font-bold">How Task Sources & Performance Scoring Work:</p>
                <p>Task source (Dynamic Report, Assigned Task, or Custom) is locked once configured to prevent accidental modifications. Click <strong>Edit Source</strong> on any row if you need to reconfigure its source. Daily score = <strong class="font-bold">Monthly Points ÷ Days in Month</strong>.</p>
            </div>
        </div>

        <form action="{{ route('users.tasks.store', $user) }}" method="POST" x-data="taskManager()" @submit.prevent="submitForm">
            @csrf

            <div class="overflow-hidden border border-gray-200 rounded-2xl dark:border-gray-800 mb-5 shadow-2xs">
                <table class="w-full text-xs text-left text-gray-600 dark:text-gray-400">
                    <thead class="uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400 text-[11px] border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3 w-10 text-center">#</th>
                            <th class="px-4 py-3">Task Source & Title</th>
                            <th class="px-4 py-3 w-56">Division / Frequency</th>
                            <th class="px-4 py-3 w-40">Monthly Amount</th>
                            <th class="px-4 py-3 w-20 text-center">Active</th>
                            <th class="px-4 py-3 w-16 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tasks-body" class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        <template x-for="(task, index) in tasks" :key="task.key">
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors"
                                :class="{'bg-amber-50/40 dark:bg-amber-950/10': task.is_editing_source}">
                                <td class="px-4 py-3 text-gray-400 font-mono text-center align-top pt-4" x-text="index + 1"></td>
                                
                                {{-- Task Source & Title (Locked Card vs Edit Mode) --}}
                                <td class="px-4 py-3">
                                    {{-- 1. LOCKED DISPLAY MODE (Read-only source, not changeable without clicking Edit) --}}
                                    <div x-show="!task.is_editing_source" class="space-y-2">
                                        <div class="p-3 rounded-xl border transition-all"
                                             :class="{
                                                 'border-blue-200 bg-blue-50/50 dark:border-blue-900/40 dark:bg-blue-950/20': task.type === 'dynamic_report',
                                                 'border-purple-200 bg-purple-50/50 dark:border-purple-900/40 dark:bg-purple-950/20': task.type === 'task',
                                                 'border-gray-200 bg-gray-50/70 dark:border-gray-700 dark:bg-gray-800/40': task.type === 'custom'
                                             }">
                                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                                <div class="flex items-center gap-1.5">
                                                    {{-- Dynamic Report Badge --}}
                                                    <template x-if="task.type === 'dynamic_report'">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200 border border-blue-200 dark:border-blue-800">
                                                            <span>📋</span> Dynamic Report
                                                        </span>
                                                    </template>
                                                    {{-- Assigned Task Badge --}}
                                                    <template x-if="task.type === 'task'">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-200 border border-purple-200 dark:border-purple-800">
                                                            <span>📌</span> Assigned Task
                                                        </span>
                                                    </template>
                                                    {{-- Custom Task Badge --}}
                                                    <template x-if="task.type === 'custom'">
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600">
                                                            <span>✍️</span> Custom Task
                                                        </span>
                                                    </template>

                                                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400" x-text="getSourceLabel(task)"></span>
                                                </div>

                                                {{-- Edit Source Button --}}
                                                <button type="button" @click="editSource(task)"
                                                    class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-[11px] font-bold text-gray-600 hover:text-brand-600 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-brand-300 dark:hover:border-brand-600 shadow-2xs transition-all cursor-pointer">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                                    Edit Source
                                                </button>
                                            </div>

                                            <div class="mt-1 flex items-center justify-between gap-2">
                                                <div class="flex items-baseline gap-2">
                                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 dark:text-gray-500">Title:</span>
                                                    <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="task.name"></span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Hidden Fields when locked --}}
                                        <input type="hidden" :name="`tasks[${index}][id]`" :value="task.id || ''">
                                        <input type="hidden" :name="`tasks[${index}][name]`" :value="task.name">
                                        <input type="hidden" :name="`tasks[${index}][type]`" :value="task.type">
                                        <input type="hidden" :name="`tasks[${index}][report_type_id]`" :value="task.report_type_id || ''">
                                        <input type="hidden" :name="`tasks[${index}][task_id]`" :value="task.task_id || ''">
                                        <input type="hidden" :name="`tasks[${index}][sort_order]`" :value="index">
                                    </div>

                                    {{-- 2. EDIT SOURCE MODE (Active when user clicks Edit or adds a new row) --}}
                                    <div x-show="task.is_editing_source" class="p-3.5 rounded-xl border border-amber-300 bg-amber-50/70 dark:border-amber-700/60 dark:bg-amber-950/30 space-y-3 shadow-xs">
                                        <div class="flex items-center justify-between gap-2 border-b border-amber-200/80 dark:border-amber-800/60 pb-2">
                                            <span class="text-xs font-extrabold text-amber-950 dark:text-amber-200 flex items-center gap-1.5">
                                                <span>⚙️</span> Choose Task Source
                                            </span>
                                            <button type="button" @click="lockSource(task)"
                                                class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-emerald-700 shadow-2xs transition-colors cursor-pointer">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                Done / Lock Source
                                            </button>
                                        </div>

                                        {{-- Source Type Segmented Buttons (Dynamic Report vs Assigned Task) --}}
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" @click="setType(task, 'dynamic_report')"
                                                class="px-3 py-2 rounded-lg text-xs font-bold transition-all text-center border cursor-pointer"
                                                :class="task.type === 'dynamic_report'
                                                    ? 'bg-blue-600 text-white border-blue-600 shadow-xs'
                                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-700 hover:bg-gray-50'">
                                                📋 Dynamic Report
                                            </button>
                                            <button type="button" @click="setType(task, 'task')"
                                                class="px-3 py-2 rounded-lg text-xs font-bold transition-all text-center border cursor-pointer"
                                                :class="task.type === 'task'
                                                    ? 'bg-purple-600 text-white border-purple-600 shadow-xs'
                                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-700 hover:bg-gray-50'">
                                                📌 Assigned Task
                                            </button>
                                        </div>

                                        {{-- Dynamic Report Selector --}}
                                        <template x-if="task.type === 'dynamic_report'">
                                            <div class="space-y-1.5">
                                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300">Select Linked Dynamic Report:</label>
                                                <select x-model="task.report_type_id" @change="onReportTypeChange(task)"
                                                    class="w-full rounded-lg border border-blue-300 bg-white px-2.5 py-1.5 text-xs font-bold text-gray-900 dark:border-blue-700 dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                                                    <option value="">-- Choose Dynamic Report --</option>
                                                    <template x-for="r in reportTypes" :key="r.id">
                                                        <option :value="r.id" :selected="task.report_type_id == r.id" x-text="r.name + (r.is_daily ? ' (Daily)' : '')"></option>
                                                    </template>
                                                </select>
                                            </div>
                                        </template>

                                        {{-- Assigned Task Selector (Single option covering all assigned tasks) --}}
                                        <template x-if="task.type === 'task'">
                                            <div class="p-3 rounded-lg bg-purple-50/80 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 text-xs text-purple-900 dark:text-purple-200 space-y-1">
                                                <div class="font-bold flex items-center gap-1.5">
                                                    <span>📌</span> All Employee Assigned Tasks
                                                </div>
                                                <p class="text-[11px] text-purple-800 dark:text-purple-300">
                                                    This template automatically tracks and includes <strong>all tasks assigned to {{ $user->name }}</strong> in the Tasks system.
                                                </p>
                                            </div>
                                        </template>

                                        {{-- Title Input in Edit Mode --}}
                                        <div class="space-y-1">
                                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300">Display Title on Performance Log:</label>
                                            <input type="text" x-model="task.name" placeholder="e.g. Plaza Cleaning, Keys Management..." required
                                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/30">
                                        </div>
                                    </div>
                                </td>

                                {{-- Frequency & Division (Daily vs Count based) --}}
                                <td class="px-4 py-3 space-y-1.5 align-top pt-4">
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
                                <td class="px-4 py-3 align-top pt-4">
                                    <div class="relative">
                                        <input type="number" :name="`tasks[${index}][monthly_points]`" x-model="task.monthly_points"
                                            min="0" step="10" placeholder="5000" required
                                            class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs font-mono font-bold dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                                        <span class="absolute right-3 top-2 text-[10px] font-bold text-gray-400">pts</span>
                                    </div>
                                </td>

                                {{-- Active Toggle --}}
                                <td class="px-4 py-3 text-center align-top pt-5">
                                    <input type="hidden" :name="`tasks[${index}][is_active]`" value="0">
                                    <input type="checkbox" :name="`tasks[${index}][is_active]`" value="1"
                                        x-model="task.is_active"
                                        class="w-4 h-4 rounded text-brand-500 border-gray-300 focus:ring-brand-500 cursor-pointer">
                                </td>

                                {{-- Remove Action --}}
                                <td class="px-4 py-3 text-right align-top pt-4">
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
                            <td colspan="2" class="px-4 py-3 text-xs font-extrabold uppercase text-gray-700 dark:text-gray-300">Total Monthly Active Max Points</td>
                            <td colspan="2" class="px-4 py-3 font-black text-gray-900 dark:text-white font-mono text-sm" x-text="totalPoints().toLocaleString() + ' pts'"></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Action Toolbar --}}
            <div class="flex flex-wrap items-center justify-between gap-4 pt-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" @click="addTask('dynamic_report')"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-blue-300 bg-blue-50/70 dark:bg-blue-950/30 dark:border-blue-800 px-4 py-2.5 text-xs font-bold text-blue-700 dark:text-blue-300 hover:bg-blue-100 transition-colors shadow-2xs cursor-pointer">
                        📋 Add Dynamic Report Task
                    </button>
                    <button type="button" @click="addTask('task')"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-purple-300 bg-purple-50/70 dark:bg-purple-950/30 dark:border-purple-800 px-4 py-2.5 text-xs font-bold text-purple-700 dark:text-purple-300 hover:bg-purple-100 transition-colors shadow-2xs cursor-pointer">
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
    'id'                => $t->id,
    'type'              => $t->type ?? 'dynamic_report',
    'name'              => $t->name,
    'report_type_id'    => $t->report_type_id ? (int) $t->report_type_id : null,
    'task_id'           => $t->task_id ? (int) $t->task_id : null,
    'monthly_points'    => (float) $t->monthly_points,
    'is_daily'          => (bool) ($t->is_daily ?? true),
    'target_count'      => (int) ($t->target_count ?? 1),
    'sort_order'        => (int) $t->sort_order,
    'is_active'         => (bool) $t->is_active,
    'is_editing_source' => false,
    'key'               => 'task_' . $t->id,
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

        addTask(type = 'dynamic_report') {
            let defaultName = '';
            let defaultReportTypeId = null;
            let defaultTaskId = null;
            let defaultIsDaily = true;

            if (type === 'dynamic_report' && this.reportTypes.length > 0) {
                defaultReportTypeId = this.reportTypes[0].id;
                defaultName = this.reportTypes[0].name;
                defaultIsDaily = this.reportTypes[0].is_daily;
            } else if (type === 'task') {
                defaultName = 'Assigned Tasks';
                defaultIsDaily = false;
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
                is_editing_source: true, // starts in edit source mode
                key: 'new_' + this.keyCounter++,
            });
        },

        editSource(task) {
            task.is_editing_source = true;
        },

        lockSource(task) {
            if (!task.name || task.name.trim() === '') {
                if (task.type === 'dynamic_report') {
                    const r = this.reportTypes.find(x => x.id == task.report_type_id);
                    task.name = r ? r.name : 'Dynamic Report';
                } else if (task.type === 'task') {
                    task.name = 'Assigned Tasks';
                } else {
                    task.name = 'Task Template';
                }
            }
            task.is_editing_source = false;
        },

        setType(task, newType) {
            task.type = newType;
            if (newType === 'dynamic_report' && this.reportTypes.length > 0) {
                if (!task.report_type_id) {
                    task.report_type_id = this.reportTypes[0].id;
                    task.name = this.reportTypes[0].name;
                    task.is_daily = this.reportTypes[0].is_daily;
                }
            } else if (newType === 'task') {
                task.task_id = null;
                if (!task.name || task.name === 'Custom Task' || this.reportTypes.some(r => r.name === task.name)) {
                    task.name = 'Assigned Tasks';
                }
            }
        },

        getSourceLabel(task) {
            if (task.type === 'dynamic_report') {
                const r = this.reportTypes.find(x => x.id == task.report_type_id);
                return r ? `Linked: ${r.name}` : 'Linked Report';
            } else if (task.type === 'task') {
                return 'All Employee Assigned Tasks';
            }
            return 'Manual Evaluation';
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
