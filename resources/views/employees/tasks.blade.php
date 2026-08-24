@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Task Templates — {{ $employee->name }}" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <x-common.component-card
        title="Manage Task Templates"
        desc="Define the daily tasks and their monthly point values for {{ $employee->name }}. Super Admin only.">

        <div class="mb-4 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-400">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            Each task's daily points = <strong class="ml-1">Monthly Points ÷ Days in Month</strong>. The system automatically calculates earned points when employees mark tasks as done.
        </div>

        <form action="{{ route('employees.tasks.store', $employee) }}" method="POST" x-data="taskManager()" @submit.prevent="submitForm">
            @csrf

            <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800 mb-4">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3 w-10">⠿</th>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Task Name</th>
                            <th class="px-4 py-3 w-40">Monthly Points</th>
                            <th class="px-4 py-3 w-24 text-center">Active</th>
                            <th class="px-4 py-3 w-20 text-right">Remove</th>
                        </tr>
                    </thead>
                    <tbody id="tasks-body">
                        <template x-for="(task, index) in tasks" :key="task.key">
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors border-b border-gray-100 dark:border-gray-800">
                                <td class="px-4 py-2 text-gray-300 cursor-grab">⠿</td>
                                <td class="px-4 py-2 text-gray-400 text-xs" x-text="index + 1"></td>
                                <td class="px-4 py-2">
                                    <input type="text" :name="`tasks[${index}][name]`" x-model="task.name"
                                        placeholder="e.g. Daily Attendance" required
                                        class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                                    <input type="hidden" :name="`tasks[${index}][id]`" :value="task.id || ''">
                                    <input type="hidden" :name="`tasks[${index}][sort_order]`" :value="index">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" :name="`tasks[${index}][monthly_points]`" x-model="task.monthly_points"
                                        min="0" step="1" placeholder="5000" required
                                        class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <input type="hidden" :name="`tasks[${index}][is_active]`" value="0">
                                    <input type="checkbox" :name="`tasks[${index}][is_active]`" value="1"
                                        x-model="task.is_active"
                                        class="w-4 h-4 rounded text-brand-500 border-gray-300 focus:ring-brand-500">
                                </td>
                                <td class="px-4 py-2 text-right">
                                    @if(auth()->user()->isSuperAdmin())
                                        <template x-if="task.id">
                                            <form :action="`/employees/{{ $employee->id }}/tasks/${task.id}`" method="POST"
                                                @submit.prevent="deleteTask(task, $el.closest('form'))">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="rounded-lg p-1.5 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </template>
                                        <template x-if="!task.id">
                                            <button type="button" @click="removeTask(index)"
                                                class="rounded-lg p-1.5 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </template>
                                    @endif
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 dark:bg-gray-800/50">
                            <td colspan="3" class="px-4 py-2 text-xs font-bold uppercase text-gray-500">Total Monthly Points</td>
                            <td class="px-4 py-2 font-bold text-gray-900 dark:text-white font-mono" x-text="totalPoints().toLocaleString() + ' pts'"></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex items-center justify-between">
                <button type="button" @click="addTask()"
                    class="inline-flex items-center gap-2 rounded-xl border border-dashed border-brand-300 px-4 py-2 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-700 dark:text-brand-400 dark:hover:bg-brand-900/20 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Task
                </button>

                <div class="flex items-center gap-3">
                    <a href="{{ route('employees.show', $employee) }}"
                       class="rounded-xl border border-gray-300 px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                        Back
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-brand-500 px-6 py-2 text-sm font-bold text-white hover:bg-brand-600 transition-colors shadow-sm cursor-pointer">
                        Save All Tasks
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
    'name'           => $t->name,
    'monthly_points' => (int) $t->monthly_points,
    'sort_order'     => $t->sort_order,
    'is_active'      => (bool) $t->is_active,
    'key'            => $t->id,
])->values();
@endphp
<script>
function taskManager() {
    return {
        tasks: @json($tasksJson),
        keyCounter: {{ $templates->count() + 1 }},


        addTask() {
            this.tasks.push({
                id: null,
                name: '',
                monthly_points: 0,
                sort_order: this.tasks.length,
                is_active: true,
                key: 'new_' + this.keyCounter++,
            });
        },

        removeTask(index) {
            this.tasks.splice(index, 1);
        },

        deleteTask(task, form) {
            if (!confirm('Remove this task? Existing daily entries will be deleted.')) return;
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
