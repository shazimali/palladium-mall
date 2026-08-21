@extends('layouts.app')

@section('content')
    @php
        $totalCount = $allTasks->count();
        $todoCount = count($kanban['todo']);
        $inProgressCount = count($kanban['in_progress']);
        $completedCount = count($kanban['completed']);
    @endphp

    <div class="space-y-6" x-data="taskTableApp()">
        <!-- Page Header -->
        <x-common.page-breadcrumb pageTitle="Task Management" />

        <x-common.component-card title="" desc="">

            {{-- Top Bar --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        Total: {{ $totalCount }}
                    </span>
                    <span class="inline-flex items-center rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                        To Do: {{ $todoCount }}
                    </span>
                    <span class="inline-flex items-center rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        In Progress: {{ $inProgressCount }}
                    </span>
                    <span class="inline-flex items-center rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                        Completed: {{ $completedCount }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    @if(!empty($filters['assigned_to']) || !empty($filters['priority']) || !empty($filters['search']) || !empty($filters['status']))
                        <a href="{{ route('tasks.index') }}"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                            Clear
                        </a>
                    @endif
                    @can('tasks.create')
                        <a href="{{ route('tasks.create') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Create New Task
                        </a>
                    @endcan
                </div>
            </div>

            {{-- Filters & Search Section --}}
            <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <form action="{{ route('tasks.index') }}" method="GET"
                    class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-md">
                        <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                            <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                            placeholder="Search title or description..."
                            class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-11 pr-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                    </div>

                    <!-- Status Filter -->
                    <select name="status" onchange="this.form.submit()"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">All Statuses</option>
                        <option value="todo" {{ ($filters['status'] ?? '') === 'todo' ? 'selected' : '' }}>To Do</option>
                        <option value="in_progress" {{ ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>

                    <!-- Assignee Select -->
                    <select name="assigned_to" onchange="this.form.submit()"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">All Assignees</option>
                        <option value="me" {{ ($filters['assigned_to'] ?? '') === 'me' ? 'selected' : '' }}>Assigned to Me</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ ($filters['assigned_to'] ?? '') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Priority Select -->
                    <select name="priority" onchange="this.form.submit()"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">All Priorities</option>
                        <option value="urgent" {{ ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                        <option value="high" {{ ($filters['priority'] ?? '') === 'high' ? 'selected' : '' }}>🟠 High</option>
                        <option value="medium" {{ ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' }}>🔵 Medium</option>
                        <option value="low" {{ ($filters['priority'] ?? '') === 'low' ? 'selected' : '' }}>⚪ Low</option>
                    </select>

                    <button type="submit"
                        class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 cursor-pointer">
                        Filter
                    </button>
                </form>
            </div>

            {{-- Tasks Table --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-8">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Priority</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Assigned To</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Created By</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Due Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">💬</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-950">
                        @forelse($allTasks as $i => $task)
                            @php
                                $isOverdue = $task->due_at && $task->due_at->isPast() && $task->status !== 'completed';
                                $priorityBadges = [
                                    'urgent' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                    'high'   => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
                                    'medium' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                    'low'    => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                ];
                                $statusConfig = [
                                    'todo'        => ['label' => 'To Do',       'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',     'dot' => 'bg-amber-500'],
                                    'in_progress' => ['label' => 'In Progress',  'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',         'dot' => 'bg-blue-500'],
                                    'completed'   => ['label' => 'Completed',    'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300', 'dot' => 'bg-emerald-500'],
                                ];
                                $sc = $statusConfig[$task->status] ?? $statusConfig['todo'];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors {{ $task->status === 'completed' ? 'opacity-75' : '' }}" style="position: relative;">
                                {{-- # --}}
                                <td class="px-4 py-3 text-xs text-gray-400 dark:text-gray-500">{{ $i + 1 }}</td>

                                {{-- Title --}}
                                <td class="px-4 py-3" style="min-width: 200px; max-width: 320px;">
                                    <button type="button"
                                        @click="openDrawer({{ json_encode($task->load(['creator', 'assignees', 'comments.user'])) }})"
                                        class="text-left group w-full">
                                        <p class="font-semibold text-sm text-gray-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors leading-snug {{ $task->status === 'completed' ? 'line-through text-gray-400 dark:text-gray-500' : '' }}">
                                            {{ $task->title }}
                                        </p>
                                    </button>
                                </td>

                                {{-- Priority --}}
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md {{ $priorityBadges[$task->priority] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $task->priority }}
                                    </span>
                                </td>

                                {{-- Status (one-click dropdown) --}}
                                <td class="px-4 py-3">
                                    @can('tasks.edit')
                                        <div x-data="{
                                                open: false,
                                                top: 0, left: 0,
                                                toggle(btn) {
                                                    if (this.open) { this.open = false; return; }
                                                    const r = btn.getBoundingClientRect();
                                                    this.top  = r.bottom + window.scrollY + 4;
                                                    this.left = r.left   + window.scrollX;
                                                    this.open = true;
                                                }
                                            }"
                                            @click.outside="open = false">
                                            <button type="button"
                                                @click="toggle($el)"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold rounded-full cursor-pointer hover:opacity-80 transition-opacity {{ $sc['class'] }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                                {{ $sc['label'] }}
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>

                                            {{-- Teleported fixed-position dropdown - escapes table stacking context --}}
                                            <template x-teleport="body">
                                                <div x-show="open"
                                                    @click.outside="open = false"
                                                    x-transition:enter="transition ease-out duration-100"
                                                    x-transition:enter-start="opacity-0 scale-95"
                                                    x-transition:enter-end="opacity-100 scale-100"
                                                    x-transition:leave="transition ease-in duration-75"
                                                    x-transition:leave-start="opacity-100 scale-100"
                                                    x-transition:leave-end="opacity-0 scale-95"
                                                    :style="`position:fixed; top:${top}px; left:${left}px; z-index:9999;`"
                                                    class="w-36 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 py-1 text-xs"
                                                    style="display:none">
                                                    @foreach(['todo' => ['To Do','bg-amber-500'], 'in_progress' => ['In Progress','bg-blue-500'], 'completed' => ['Completed','bg-emerald-500']] as $val => $info)
                                                        <button type="button"
                                                            @click="open = false; changeStatus({{ $task->id }}, '{{ $val }}')"
                                                            class="w-full flex items-center gap-2 px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors {{ $task->status === $val ? 'font-bold text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300' }}">
                                                            <span class="w-2 h-2 rounded-full {{ $info[1] }}"></span>
                                                            {{ $info[0] }}
                                                            @if($task->status === $val)
                                                                <svg class="ml-auto w-3 h-3 text-brand-500" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                            @endif
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </template>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold rounded-full {{ $sc['class'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                            {{ $sc['label'] }}
                                        </span>
                                    @endcan
                                </td>

                                {{-- Assigned To --}}
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($task->assignees as $assignee)
                                            <span class="px-2 py-0.5 bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 text-[10px] font-semibold rounded-md">
                                                {{ $assignee->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>

                                {{-- Created By --}}
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                    {{ $task->creator?->name ?? '—' }}
                                    <div class="text-[10px] text-gray-400">{{ $task->created_at?->format('M d, Y') ?? '—' }}</div>
                                </td>

                                {{-- Due Date --}}
                                <td class="px-4 py-3 text-xs whitespace-nowrap {{ $isOverdue ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-600 dark:text-gray-400' }}">
                                    @if($task->due_at)
                                        @if($isOverdue) ⚠️ @endif
                                        {{ $task->due_at?->format('M d, Y') ?? '' }}
                                        <div class="text-[10px]">{{ $task->due_at?->format('h:i A') ?? '' }}</div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- Comments count --}}
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                    {{ count($task->comments) }}
                                </td>

                                {{-- Actions --}}
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                            @click="openDrawer({{ json_encode($task->load(['creator', 'assignees', 'comments.user'])) }})"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-950/30 transition-colors"
                                            title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        @can('tasks.edit')
                                            <a href="{{ route('tasks.edit', $task->id) }}"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-colors"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </a>
                                        @endcan
                                        @can('tasks.delete')
                                            <button type="button"
                                                @click="deleteTask({{ $task->id }}, '{{ addslashes($task->title) }}')"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors"
                                                title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                    No tasks found. <a href="{{ route('tasks.create') }}" class="text-brand-500 hover:underline">Create one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-common.component-card>

        <!-- Task Detail Modal -->
        <div x-show="activeTask !== null"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click.self="activeTask = null"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            style="display: none;">

            <div
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                class="w-full max-w-2xl max-h-[90vh] bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 flex flex-col overflow-hidden">

                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/80 flex-shrink-0">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md"
                                    :class="priorityBadgeClass(activeTask?.priority)">
                                    <span x-text="activeTask?.priority"></span>
                                </span>
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                    <span x-text="formatStatus(activeTask?.status)"></span>
                                </span>
                            </div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white leading-snug break-words" x-text="activeTask?.title"></h2>
                        </div>
                        <button type="button" @click="activeTask = null"
                            class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-200 dark:hover:bg-gray-800 transition-colors text-lg font-bold">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-6 space-y-5">

                    <!-- Metadata Grid -->
                    <div class="grid grid-cols-2 gap-3 bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl text-xs border border-gray-200/60 dark:border-gray-800">
                        <div>
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold mb-1">Created By</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="activeTask?.creator?.name"></span>
                            <span class="text-gray-400 block text-[10px] mt-0.5" x-text="formatDate(activeTask?.created_at)"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold mb-1">Assigned To</span>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="assignee in activeTask?.assignees" :key="assignee.id">
                                    <span class="px-2 py-0.5 bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 text-[10px] font-semibold rounded-md"
                                        x-text="assignee.name"></span>
                                </template>
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold mb-1">Due Date</span>
                            <span class="font-semibold"
                                :class="isOverdue(activeTask?.due_at) ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-200'"
                                x-text="activeTask?.due_at ? formatDate(activeTask?.due_at) : 'No due date'"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold mb-1">Completed At</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400"
                                x-text="activeTask?.completed_at ? formatDate(activeTask?.completed_at) : 'Not completed yet'"></span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Description</h4>
                        <div class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800/30 p-4 rounded-xl border border-gray-200/50 dark:border-gray-800 whitespace-pre-wrap break-all overflow-x-hidden"
                            x-text="activeTask?.description || 'No description provided.'">
                        </div>
                    </div>

                    <!-- Comments -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center justify-between">
                            <span>💬 Discussion & Comments</span>
                            <span class="text-[10px] font-normal text-gray-400"
                                x-text="(activeTask?.comments?.length || 0) + ' comments'"></span>
                        </h4>

                        <div class="space-y-2 mb-4 max-h-48 overflow-y-auto pr-1">
                            <template x-if="!activeTask?.comments || activeTask.comments.length === 0">
                                <div class="text-center py-6 text-xs text-gray-400 italic">
                                    No comments yet. Start the conversation below!
                                </div>
                            </template>
                            <template x-for="c in activeTask?.comments" :key="c.id">
                                <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-xl border border-gray-200/60 dark:border-gray-700 space-y-1">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span x-text="c.user?.name"></span>
                                            <span x-show="c.user_id === activeTask?.created_by"
                                                class="px-1.5 text-[9px] bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 font-semibold rounded">Assigner</span>
                                        </span>
                                        <span class="text-[10px] text-gray-400" x-text="formatDate(c.created_at)"></span>
                                    </div>
                                    <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line" x-text="c.comment"></p>
                                </div>
                            </template>
                        </div>

                        <form @submit.prevent="submitComment()" class="space-y-2">
                            <textarea x-model="newComment" rows="2" required
                                placeholder="Write a comment or status update..."
                                class="w-full p-3 text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 resize-none"></textarea>
                            <div class="flex justify-end">
                                <button type="submit" :disabled="commentLoading || !newComment.trim()"
                                    class="px-4 py-2 text-xs font-semibold text-white bg-brand-500 hover:bg-brand-600 disabled:opacity-50 rounded-lg">
                                    <span x-show="!commentLoading">Post Comment</span>
                                    <span x-show="commentLoading">Posting...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/80 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2">
                        @can('tasks.edit')
                            <a :href="'/tasks/' + activeTask?.id + '/edit'"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-950/30 rounded-lg transition-colors">
                                ✏️ Edit Task
                            </a>
                        @endcan
                        @can('tasks.delete')
                            <button type="button" @click="deleteTask(activeTask?.id, activeTask?.title)"
                                class="px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors">
                                🗑 Delete
                            </button>
                        @endcan
                    </div>
                    <button type="button" @click="activeTask = null"
                        class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function taskTableApp() {
            return {
                activeTask: null,
                commentLoading: false,
                newComment: '',

                init() {
                    // Open initial task if passed in query string
                    const activeId = "{{ $activeTaskId ?? '' }}";
                    if (activeId) {
                        const btn = document.querySelector(`[data-task-id="${activeId}"]`);
                        if (btn) btn.click();
                    }
                },

                openDrawer(taskJson) {
                    this.activeTask = taskJson;
                    this.newComment = '';
                },

                async changeStatus(taskId, newStatus) {
                    try {
                        const res = await fetch(`/tasks/${taskId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ status: newStatus })
                        });
                        const data = await res.json();
                        if (data.success) {
                            // Update drawer if open for same task
                            if (this.activeTask && this.activeTask.id == taskId) {
                                this.activeTask = data.task;
                            }
                            const statusLabels = { todo: 'To Do', in_progress: 'In Progress', completed: 'Completed' };
                            await Swal.fire({
                                icon: 'success',
                                title: 'Status Updated!',
                                text: `Task status changed to "${statusLabels[newStatus] || newStatus}".`,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            window.location.reload();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to update status.' });
                        }
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update status.' });
                    }
                },

                async submitComment() {
                    if (!this.newComment.trim() || !this.activeTask) return;

                    this.commentLoading = true;
                    try {
                        const res = await fetch(`/tasks/${this.activeTask.id}/comments`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ comment: this.newComment })
                        });
                        const data = await res.json();
                        if (data.success) {
                            if (!this.activeTask.comments) this.activeTask.comments = [];
                            this.activeTask.comments.unshift(data.comment);
                            this.newComment = '';
                        }
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Could not post comment.' });
                    } finally {
                        this.commentLoading = false;
                    }
                },

                async deleteTask(taskId, taskTitle) {
                    if (!taskId) return;
                    const result = await Swal.fire({
                        title: 'Delete Task?',
                        text: `Are you sure you want to delete '${taskTitle}'?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Delete'
                    });

                    if (result.isConfirmed) {
                        try {
                            const res = await fetch(`/tasks/${taskId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.activeTask = null;
                                window.location.reload();
                            }
                        } catch (e) {}
                    }
                },

                formatStatus(status) {
                    const map = { todo: 'To Do', in_progress: 'In Progress', completed: 'Completed' };
                    return map[status] || status;
                },

                priorityBadgeClass(priority) {
                    const map = {
                        urgent: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                        high: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
                        medium: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                        low: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
                    };
                    return map[priority] || 'bg-gray-100 text-gray-700';
                },

                formatDate(str) {
                    if (!str) return '';
                    const d = new Date(str);
                    return d.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                },

                isOverdue(dueAt) {
                    if (!dueAt) return false;
                    return new Date(dueAt) < new Date();
                }
            };
        }
    </script>
@endpush