@extends('layouts.app')

@section('title', 'Edit Task: ' . $task->title)

@push('styles')
<style>
    .flatpickr-calendar {
        z-index: 999999 !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Edit Task" />

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-xs text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <x-common.component-card title="Edit Task Details" desc="Update title, status, due date, or assigned personnel.">
        <form action="{{ route('tasks.update', $task) }}" method="POST" class="space-y-6 max-w-3xl">
            @csrf
            @method('PUT')

            <!-- Task Title -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Task Title *</label>
                <input
                    type="text"
                    name="title"
                    value="{{ old('title', $task->title) }}"
                    required
                    placeholder="e.g. Inspect shop electrical meters"
                    class="w-full h-11 px-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                />
                @error('title')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Description & Specific Instructions</label>
                <textarea
                    name="description"
                    rows="4"
                    placeholder="Provide details, scope, or requirements for this task..."
                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                >{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Status -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Kanban Status *</label>
                    <select name="status" required class="w-full h-11 px-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20">
                        <option value="todo" {{ old('status', $task->status) === 'todo' ? 'selected' : '' }}>📌 To Do</option>
                        <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>⚡ In Progress</option>
                        <option value="completed" {{ old('status', $task->status) === 'completed' ? 'selected' : '' }}>✅ Completed</option>
                    </select>
                    @error('status')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Priority *</label>
                    <select name="priority" required class="w-full h-11 px-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20">
                        <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>⚪ Low</option>
                        <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>🔵 Medium</option>
                        <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>🟠 High</option>
                        <option value="urgent" {{ old('priority', $task->priority) === 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                    </select>
                    @error('priority')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Due Date & Time Picker -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Due Date & Time</label>
                    <input
                        type="text"
                        id="task_due_at"
                        name="due_at"
                        value="{{ old('due_at', $task->due_at ? $task->due_at->format('Y-m-d H:i:s') : '') }}"
                        placeholder="Select due date & time..."
                        readonly
                        class="w-full h-11 px-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white cursor-pointer focus:ring-2 focus:ring-brand-500/20"
                    />
                    @error('due_at')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Assignees Selection -->
            @php
                $assignedIds = old('assignee_ids', $task->assignees->pluck('id')->toArray());
            @endphp
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Assign To Registered Admin Person(s) *</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-56 overflow-y-auto border border-gray-300 dark:border-gray-700 rounded-xl p-4 bg-gray-50/50 dark:bg-gray-800/40">
                    @foreach($users as $u)
                        <label class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-brand-400 transition-colors">
                            <input
                                type="checkbox"
                                name="assignee_ids[]"
                                value="{{ $u->id }}"
                                {{ in_array($u->id, $assignedIds) ? 'checked' : '' }}
                                class="w-4 h-4 rounded text-brand-500 border-gray-300 dark:border-gray-700 focus:ring-brand-500/20"
                            />
                            <div class="text-xs">
                                <span class="font-bold text-gray-900 dark:text-white block">{{ $u->name }}</span>
                                <span class="text-gray-400 text-[11px]">{{ $u->email }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('assignee_ids')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Form Action Buttons -->
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                <button type="submit" class="px-6 py-2.5 text-xs font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-xl shadow-xs transition-all cursor-pointer">
                    Update Task
                </button>
                <a href="{{ route('tasks.index') }}" class="px-5 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-common.component-card>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#task_due_at', {
                enableTime: true,
                dateFormat: 'Y-m-d H:i:S',
                altInput: true,
                altFormat: 'M j, Y h:i K',
                time_24hr: false,
                disableMobile: true,
                defaultDate: "{{ $task->due_at ? $task->due_at->format('Y-m-d H:i:s') : '' }}"
            });
        }
    });
</script>
@endpush
