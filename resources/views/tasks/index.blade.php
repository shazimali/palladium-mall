@extends('layouts.app')

@section('content')
    <div class="space-y-6" x-data="taskBoardApp()">
        <!-- Page Header -->
        <x-common.page-breadcrumb pageTitle="Task Management" />

        <x-common.component-card title="" desc="">

            {{-- Top Bar --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
                <div class="flex flex-wrap gap-2 items-center">
                    <span
                        class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        Total Tasks:
                        {{ count($kanban['todo']) + count($kanban['in_progress']) + count($kanban['completed']) }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    @if(!empty($filters['assigned_to']) || !empty($filters['priority']) || !empty($filters['search']))
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
            <div
                class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <form action="{{ route('tasks.index') }}" method="GET"
                    class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-md">
                        <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                            <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20"
                                fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                            placeholder="Search title or description..."
                            class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-11 pr-4 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" />
                    </div>

                    <!-- Assignee Select -->
                    <select name="assigned_to" onchange="this.form.submit()"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">All Assignees</option>
                        <option value="me" {{ ($filters['assigned_to'] ?? '') === 'me' ? 'selected' : '' }}>Assigned to Me
                        </option>
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
                        <option value="urgent" {{ ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' }}>🔴 Urgent
                        </option>
                        <option value="high" {{ ($filters['priority'] ?? '') === 'high' ? 'selected' : '' }}>🟠 High</option>
                        <option value="medium" {{ ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' }}>🔵 Medium
                        </option>
                        <option value="low" {{ ($filters['priority'] ?? '') === 'low' ? 'selected' : '' }}>⚪ Low</option>
                    </select>

                    <button type="submit"
                        class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900 dark:bg-gray-700 dark:hover:bg-gray-600 cursor-pointer">
                        Filter
                    </button>
                </form>
            </div>

            <!-- 3-Column Kanban Board -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- 1. TO DO Column -->
                <div
                    class="flex flex-col bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-200 dark:border-gray-800 min-h-[500px]">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-200 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                            <h2 class="font-bold text-sm text-gray-800 dark:text-white uppercase tracking-wider">To Do</h2>
                        </div>
                        <span
                            class="px-2.5 py-0.5 text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 rounded-full">
                            {{ count($kanban['todo']) }}
                        </span>
                    </div>

                    <div id="kanban-todo" data-status="todo"
                        class="kanban-column space-y-3 flex-1 overflow-y-auto max-h-[700px] p-1">
                        @forelse($kanban['todo'] as $task)
                            @include('tasks.partials.task-card', ['task' => $task])
                        @empty
                            <div
                                class="text-center py-10 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-xl text-gray-400 text-xs">
                                No pending tasks in To Do
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 2. IN PROGRESS Column -->
                <div
                    class="flex flex-col bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-200 dark:border-gray-800 min-h-[500px]">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-200 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                            <h2 class="font-bold text-sm text-gray-800 dark:text-white uppercase tracking-wider">In Progress
                            </h2>
                        </div>
                        <span
                            class="px-2.5 py-0.5 text-xs font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 rounded-full">
                            {{ count($kanban['in_progress']) }}
                        </span>
                    </div>

                    <div id="kanban-in_progress" data-status="in_progress"
                        class="kanban-column space-y-3 flex-1 overflow-y-auto max-h-[700px] p-1">
                        @forelse($kanban['in_progress'] as $task)
                            @include('tasks.partials.task-card', ['task' => $task])
                        @empty
                            <div
                                class="text-center py-10 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-xl text-gray-400 text-xs">
                                No tasks currently in progress
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 3. COMPLETED Column -->
                <div
                    class="flex flex-col bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-200 dark:border-gray-800 min-h-[500px]">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-200 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <h2 class="font-bold text-sm text-gray-800 dark:text-white uppercase tracking-wider">Completed
                            </h2>
                        </div>
                        <span
                            class="px-2.5 py-0.5 text-xs font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded-full">
                            {{ count($kanban['completed']) }}
                        </span>
                    </div>

                    <div id="kanban-completed" data-status="completed"
                        class="kanban-column space-y-3 flex-1 overflow-y-auto max-h-[700px] p-1">
                        @forelse($kanban['completed'] as $task)
                            @include('tasks.partials.task-card', ['task' => $task])
                        @empty
                            <div
                                class="text-center py-10 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-xl text-gray-400 text-xs">
                                No completed tasks yet
                            </div>
                        @endforelse
                    </div>
                </div>
        </x-common.component-card>

        <!-- Task Detail & Comment Modal / Drawer -->
        <div x-show="activeTask !== null" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click.self="activeTask = null"
            class="fixed inset-0 z-50 flex items-center justify-end p-0 bg-black/60 backdrop-blur-xs"
            style="display: none;">
            <div
                class="w-full max-w-xl h-full bg-white dark:bg-gray-900 border-l border-gray-200 dark:border-gray-800 shadow-2xl flex flex-col justify-between overflow-hidden">
                <!-- Drawer Header -->
                <div
                    class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-start justify-between bg-gray-50/50 dark:bg-gray-900">
                    <div class="space-y-1 pr-4">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md"
                                :class="priorityBadgeClass(activeTask?.priority)">
                                <span x-text="activeTask?.priority"></span>
                            </span>
                            <span
                                class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                Status: <span x-text="formatStatus(activeTask?.status)"></span>
                            </span>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white" x-text="activeTask?.title"></h2>
                    </div>
                    <button type="button" @click="activeTask = null"
                        class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl font-bold">
                        ✕
                    </button>
                </div>

                <!-- Drawer Body -->
                <div class="flex-1 p-5 overflow-y-auto space-y-6">
                    <!-- Task Timestamps & Metadata -->
                    <div
                        class="grid grid-cols-2 gap-3 bg-gray-50 dark:bg-gray-800/40 p-4 rounded-xl text-xs border border-gray-200/60 dark:border-gray-800">
                        <div>
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Created By
                                (Assigner)</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200"
                                x-text="activeTask?.creator?.name"></span>
                            <span class="text-gray-400 block text-[10px] mt-1"
                                x-text="formatDate(activeTask?.created_at)"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Assigned To</span>
                            <div class="flex flex-wrap gap-1 mt-1">
                                <template x-for="assignee in activeTask?.assignees" :key="assignee.id">
                                    <span
                                        class="px-2 py-0.5 bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 text-[10px] font-semibold rounded-md"
                                        x-text="assignee.name"></span>
                                </template>
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Due Date & Time</span>
                            <span class="font-semibold"
                                :class="isOverdue(activeTask?.due_at) ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-200'"
                                x-text="activeTask?.due_at ? formatDate(activeTask?.due_at) : 'No due date'"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[10px] uppercase font-semibold">Completed Date &
                                Time</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400"
                                x-text="activeTask?.completed_at ? formatDate(activeTask?.completed_at) : 'Not completed yet'"></span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Description</h4>
                        <div class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800/30 p-4 rounded-xl border border-gray-200/50 dark:border-gray-800 whitespace-pre-line"
                            x-text="activeTask?.description || 'No description provided.'"></div>
                    </div>

                    <!-- Comments & Collaboration Feed -->
                    <div>
                        <h4
                            class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center justify-between">
                            <span>💬 Discussion & Comments</span>
                            <span class="text-[10px] font-normal text-gray-400"
                                x-text="(activeTask?.comments?.length || 0) + ' comments'"></span>
                        </h4>

                        <!-- Comment List -->
                        <div class="space-y-3 mb-4 max-h-60 overflow-y-auto p-1">
                            <template x-if="!activeTask?.comments || activeTask.comments.length === 0">
                                <div class="text-center py-6 text-xs text-gray-400 italic">
                                    No comments yet. Start the conversation below!
                                </div>
                            </template>

                            <template x-for="c in activeTask?.comments" :key="c.id">
                                <div
                                    class="bg-gray-50 dark:bg-gray-800 p-3 rounded-xl border border-gray-200/60 dark:border-gray-700 space-y-1">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span x-text="c.user?.name"></span>
                                            <span x-show="c.user_id === activeTask?.created_by"
                                                class="px-1.5 py-0.2 text-[9px] bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 font-semibold rounded">Assigner</span>
                                        </span>
                                        <span class="text-[10px] text-gray-400" x-text="formatDate(c.created_at)"></span>
                                    </div>
                                    <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line"
                                        x-text="c.comment"></p>
                                </div>
                            </template>
                        </div>

                        <!-- Post Comment Form -->
                        <form @submit.prevent="submitComment()" class="space-y-2">
                            <textarea x-model="newComment" rows="2" required
                                placeholder="Write a comment or status update..."
                                class="w-full p-3 text-xs bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20"></textarea>
                            <div class="flex justify-end">
                                <button type="submit" :disabled="commentLoading || !newComment.trim()"
                                    class="px-4 py-2 text-xs font-semibold text-white bg-brand-500 hover:bg-brand-600 rounded-lg shadow-xs">
                                    <span x-show="!commentLoading">Post Comment</span>
                                    <span x-show="commentLoading">Posting...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Drawer Footer Actions -->
                <div
                    class="p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @can('tasks.edit')
                            <a :href="'/tasks/' + activeTask?.id + '/edit'"
                                class="px-3 py-1.5 text-xs font-semibold text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-950/30 rounded-lg transition-colors">
                                ✏️ Edit Task
                            </a>
                        @endcan
                        @can('tasks.delete')
                            <button type="button" @click="deleteTask(activeTask)"
                                class="px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition-colors">
                                Delete Task
                            </button>
                        @endcan
                    </div>
                    <button type="button" @click="activeTask = null"
                        class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 rounded-xl">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Include SortableJS for Kanban Drag & Drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        function taskBoardApp() {
            return {
                showCreateModal: false,
                activeTask: null,
                loading: false,
                commentLoading: false,
                newComment: '',
                createForm: {
                    title: '',
                    description: '',
                    priority: 'medium',
                    due_at: '',
                    assignee_ids: []
                },

                init() {
                    this.initKanbanSortable();

                    // Open initial task if passed in query string
                    const activeId = "{{ $activeTaskId ?? '' }}";
                    if (activeId) {
                        const taskCard = document.querySelector(`[data-task-id="${activeId}"]`);
                        if (taskCard) {
                            try {
                                this.activeTask = JSON.parse(taskCard.dataset.taskJson);
                            } catch (e) { }
                        }
                    }
                },

                initKanbanSortable() {
                    const columns = ['todo', 'in_progress', 'completed'];
                    columns.forEach(status => {
                        const el = document.getElementById(`kanban-${status}`);
                        if (!el) return;

                        new Sortable(el, {
                            group: 'kanban',
                            animation: 150,
                            ghostClass: 'opacity-40',
                            onEnd: (evt) => {
                                const itemEl = evt.item;
                                const taskId = itemEl.dataset.taskId;
                                const newStatus = evt.to.dataset.status;
                                const newOrder = evt.newIndex;

                                this.updateTaskStatus(taskId, newStatus, newOrder);
                            }
                        });
                    });
                },

                openCreateModal() {
                    this.createForm = {
                        title: '',
                        description: '',
                        priority: 'medium',
                        due_at: '',
                        assignee_ids: []
                    };
                    this.showCreateModal = true;
                    this.$nextTick(() => {
                        const card = document.getElementById('createTaskModalCard');
                        flatpickr('#task_due_at', {
                            enableTime: true,
                            dateFormat: 'Y-m-d H:i:S',
                            altInput: true,
                            altFormat: 'M j, Y h:i K',
                            time_24hr: false,
                            disableMobile: true,
                            appendTo: card || undefined,
                            onChange: (selectedDates, dateStr) => {
                                this.createForm.due_at = dateStr;
                            }
                        });
                    });
                },

                async submitCreateTask() {
                    if (this.createForm.assignee_ids.length === 0) {
                        Swal.fire({ icon: 'warning', title: 'Assignee Required', text: 'Please select at least one registered admin person.' });
                        return;
                    }

                    this.loading = true;
                    try {
                        const res = await fetch("{{ route('tasks.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(this.createForm)
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.showCreateModal = false;
                            Swal.fire({ icon: 'success', title: 'Task Created!', text: data.message, timer: 1500, showConfirmButton: false })
                                .then(() => window.location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Validation failed.' });
                        }
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save task.' });
                    } finally {
                        this.loading = false;
                    }
                },

                async updateTaskStatus(taskId, newStatus, orderColumn) {
                    try {
                        const res = await fetch(`/tasks/${taskId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                status: newStatus,
                                order_column: orderColumn
                            })
                        });
                        const data = await res.json();
                        if (data.success && this.activeTask && this.activeTask.id == taskId) {
                            this.activeTask = data.task;
                        }
                    } catch (e) {
                        console.error('Failed to update status:', e);
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

                async deleteTask(task) {
                    if (!task) return;
                    const result = await Swal.fire({
                        title: 'Delete Task?',
                        text: `Are you sure you want to delete '${task.title}'?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Delete'
                    });

                    if (result.isConfirmed) {
                        try {
                            const res = await fetch(`/tasks/${task.id}`, {
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
                        } catch (e) { }
                    }
                },

                openTaskDrawer(taskJson) {
                    this.activeTask = taskJson;
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