@extends('layouts.app')

@section('title', 'Daily Tasks')

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
<div class="space-y-6" x-data="dailyTasksApp()" x-init="init()">
    <x-common.page-breadcrumb pageTitle="Daily Tasks" />

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <x-common.component-card title="" desc="">

        {{-- ── Top Bar ── --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-5">
            {{-- Reactive Status Badges --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100 px-3.5 py-2 text-xs font-extrabold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Total: <span class="font-black font-mono" x-text="counts.total">{{ $counts['total'] }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-xl bg-amber-100 px-3.5 py-2 text-xs font-extrabold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                    📌 To Do: <span class="font-black font-mono" x-text="counts.todo">{{ $counts['todo'] }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-xl bg-blue-100 px-3.5 py-2 text-xs font-extrabold text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                    ⚡ In Progress: <span class="font-black font-mono" x-text="counts.in_progress">{{ $counts['in_progress'] }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-100 px-3.5 py-2 text-xs font-extrabold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                    ✅ Completed: <span class="font-black font-mono" x-text="counts.completed">{{ $counts['completed'] }}</span>
                </span>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('tasks.print', request()->query()) }}" target="_blank"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-extrabold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition-colors shadow-2xs">
                    <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print Tasks
                </a>
                <button type="button" @click="openCategories()"
                    class="inline-flex items-center gap-2 rounded-xl border-2 border-gray-300 bg-white px-4 py-2 text-xs font-extrabold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Manage Categories
                </button>
                @if(auth()->user()->hasPermission('tasks.create') || auth()->user()->isSuperAdmin())
                    <button type="button" @click="openCreateModal()"
                        class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-xs font-extrabold text-white shadow hover:bg-brand-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Assign Task
                    </button>
                @endif
            </div>
        </div>

        {{-- ── Filters ── --}}
        <form id="task-filter-form" method="GET" action="{{ route('tasks.index') }}"
            class="mb-5 flex flex-wrap gap-3 rounded-xl border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
            {{-- From Date --}}
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">📅 From</label>
                <input type="text" name="date_from" id="task-date-from"
                    value="{{ $dateFrom }}"
                    placeholder="Start date…"
                    readonly
                    class="h-9 rounded-lg border border-gray-300 bg-white px-3 text-xs font-medium text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 cursor-pointer w-32">
            </div>
            {{-- To Date --}}
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-400 whitespace-nowrap">To</label>
                <input type="text" name="date_to" id="task-date-to"
                    value="{{ $dateTo }}"
                    placeholder="End date…"
                    readonly
                    class="h-9 rounded-lg border border-gray-300 bg-white px-3 text-xs font-medium text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 cursor-pointer w-32">
            </div>
            <select name="category_id" onchange="this.form.submit()"
                class="h-9 rounded-lg border border-gray-300 bg-white px-3 text-xs font-medium text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}{{ !$cat->is_active ? ' (Inactive)' : '' }}
                    </option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()"
                class="h-9 rounded-lg border border-gray-300 bg-white px-3 text-xs font-medium text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                <option value="">All Statuses</option>
                <option value="todo" {{ ($filters['status'] ?? '') === 'todo' ? 'selected' : '' }}>📌 To Do</option>
                <option value="in_progress" {{ ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' }}>⚡ In Progress</option>
                <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>✅ Completed</option>
            </select>
            <select name="priority" onchange="this.form.submit()"
                class="h-9 rounded-lg border border-gray-300 bg-white px-3 text-xs font-medium text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                <option value="">All Priorities</option>
                <option value="low" {{ ($filters['priority'] ?? '') === 'low' ? 'selected' : '' }}>⚪ Low</option>
                <option value="medium" {{ ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' }}>🔵 Medium</option>
                <option value="high" {{ ($filters['priority'] ?? '') === 'high' ? 'selected' : '' }}>🟠 High</option>
                <option value="urgent" {{ ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
            </select>
            <select name="assigned_to" onchange="this.form.submit()"
                class="h-9 rounded-lg border border-gray-300 bg-white px-3 text-xs font-medium text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                <option value="">All Assignees</option>
                <option value="me" {{ ($filters['assigned_to'] ?? '') === 'me' ? 'selected' : '' }}>My Tasks</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ ($filters['assigned_to'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
            @if(array_filter($filters))
                <a href="{{ route('tasks.index') }}"
                    class="inline-flex items-center h-9 rounded-lg border border-gray-300 px-3 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                    Clear
                </a>
            @endif
        </form>

        {{-- ── Daily Register Table ── --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-3 py-3 w-8">#</th>
                            <th class="px-4 py-3">Category / Task</th>
                            <th class="px-4 py-3">Assigned To</th>
                            <th class="px-3 py-3 text-center">Priority</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3">Creator Remarks</th>
                            <th class="px-4 py-3">Assignee Remarks</th>
                            <th class="px-4 py-3 whitespace-nowrap">Due Date</th>
                            <th class="px-3 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="task-table-body" class="divide-y divide-gray-100 dark:divide-gray-800">
                        @include('tasks.partials._table_rows', ['tasks' => $tasks, 'date' => $dateFrom ?? ''])
                    </tbody>
                </table>
            </div>

            {{-- Table Footer --}}
            <div id="task-table-footer"
                class="border-t border-gray-100 dark:border-gray-800 px-4 py-2.5 text-xs text-gray-500 dark:text-gray-400 flex items-center justify-between"
                @if($tasks->count() === 0) style="display:none" @endif>
                <span>
                    Showing <strong class="text-gray-700 dark:text-gray-300" x-text="counts.total">{{ $tasks->count() }}</strong> task(s)
                    @if($dateFrom || $dateTo)
                        for
                        <strong class="text-gray-700 dark:text-gray-300">
                            @if($dateFrom && $dateTo)
                                {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                            @elseif($dateFrom)
                                from {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
                            @else
                                until {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                            @endif
                        </strong>
                    @else
                        (all dates)
                    @endif
                </span>
                <span x-show="counts.completed > 0" class="text-emerald-600 font-semibold">
                    <span x-text="counts.completed"></span>/<span x-text="counts.total"></span> completed
                </span>
            </div>
        </div>

    </x-common.component-card>

    {{-- ── Manage Categories Slide-Over ── --}}
    @include('tasks.partials._manage_categories_modal')

    {{-- ── Task Create / Edit Modal ── --}}
    @include('tasks.partials._task_form_modal')

</div>
@endsection

@push('styles')
<style>.flatpickr-calendar { z-index: 999999 !important; }</style>
@endpush

@push('scripts')
<script>
    // ── Date Filter Flatpickr ──
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#task-date-from', {
                dateFormat:    'Y-m-d',
                altInput:      true,
                altFormat:     'M j, Y',
                disableMobile: true,
                defaultDate:   '{{ $dateFrom }}' || null,
                onChange: function () {
                    document.getElementById('task-filter-form').submit();
                }
            });
            flatpickr('#task-date-to', {
                dateFormat:    'Y-m-d',
                altInput:      true,
                altFormat:     'M j, Y',
                disableMobile: true,
                defaultDate:   '{{ $dateTo }}' || null,
                onChange: function () {
                    document.getElementById('task-filter-form').submit();
                }
            });
        }
    });

    const CSRF        = '{{ csrf_token() }}';
    const CURRENT_UID = {{ auth()->id() }};
    const FILTER_QS   = '{{ http_build_query(request()->only(["date_from","date_to","category_id","status","priority","assigned_to"])) }}';

    function dailyTasksApp() {
        return {
            // ── Count badges (reactive) ──
            counts: {
                total:       {{ $counts['total'] }},
                todo:        {{ $counts['todo'] }},
                in_progress: {{ $counts['in_progress'] }},
                completed:   {{ $counts['completed'] }},
            },

            // ── Category Panel ──
            panelOpen: false,
            categories: [],
            newCategoryName: '',
            saving: false,

            // ── Task Form Modal ──
            modalOpen:          false,
            modalMode:          'create',   // 'create' | 'edit'
            editTaskId:         null,
            originalStatus:     null,
            isSuperAdmin:       {{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }},
            isAssigneeOnly:     false,
            loadingModal:       false,
            submittingTask:     false,
            formErrors:         {},
            taskInfo: {
                category_name:    '',
                formatted_due_at: '',
                creator_name:     '',
            },
            taskForm: {
                category_id:      '',
                description:      '',
                creator_remarks:  '',
                creator_rating:   '',
                assignee_remarks: '',
                status:           'todo',
                priority:         'medium',
                due_at:           '',
                assignee_ids:     [],
            },

            // ──────────────────────────────
            init() {},

            // ── Category Methods ──
            openCategories() { this.panelOpen = true; this.loadCategories(); },
            closeCategories() { this.panelOpen = false; },
            loadCategories() {
                fetch('{{ route('task-categories.index') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json()).then(d => { this.categories = d.categories; });
            },
            saveCategory() {
                if (!this.newCategoryName.trim()) return;
                this.saving = true;
                fetch('{{ route('task-categories.store') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ name: this.newCategoryName.trim() })
                }).then(r => r.json()).then(d => {
                    this.saving = false;
                    if (d.success) { this.newCategoryName = ''; this.loadCategories(); }
                    else alert(d.message || 'Failed.');
                }).catch(() => { this.saving = false; });
            },
            toggleCategory(cat) {
                fetch(`/task-categories/${cat.id}/toggle`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
                }).then(r => r.json()).then(d => { if (d.success) cat.is_active = d.is_active; });
            },

            // ── Flatpickr Date & Time Picker ──
            dueAtPicker: null,
            initDueAtPicker(initialDate) {
                if (typeof flatpickr !== 'undefined' && this.$refs.dueAtInput) {
                    if (this.dueAtPicker) {
                        try { this.dueAtPicker.destroy(); } catch(e){}
                    }
                    this.dueAtPicker = flatpickr(this.$refs.dueAtInput, {
                        enableTime:    true,
                        dateFormat:    'Y-m-d H:i:S',
                        altInput:      true,
                        altFormat:     'M j, Y h:i K',
                        time_24hr:     false,
                        disableMobile: true,
                        defaultDate:   initialDate || null,
                        onChange: (selectedDates, dateStr) => {
                            this.taskForm.due_at = dateStr;
                        }
                    });
                }
            },

            // Photo State
            photoPreviewUrl:  null,
            photoFileName:    '',
            removeAdminPhoto: false,

            handlePhotoChange(event) {
                const file = event.target.files[0];
                if (file) {
                    if (file.size > 200 * 1024) {
                        alert('⚠️ Photo size exceeds 200 KB. Please select a smaller photo (max 200 KB).');
                        this.formErrors = Object.assign({}, this.formErrors, { admin_photo: ['Photo size must not exceed 200 KB.'] });
                        event.target.value = '';
                        this.removePhoto();
                        return;
                    }
                    if (this.formErrors.admin_photo) {
                        delete this.formErrors.admin_photo;
                    }
                    this.photoFileName    = file.name;
                    this.photoPreviewUrl  = URL.createObjectURL(file);
                    this.removeAdminPhoto = false;
                }
            },

            removePhoto() {
                this.photoPreviewUrl  = null;
                this.photoFileName    = '';
                this.removeAdminPhoto = true;
                if (this.$refs.adminPhotoInput) {
                    this.$refs.adminPhotoInput.value = '';
                }
            },

            // ── Modal Methods ──
            openCreateModal() {
                this.modalMode        = 'create';
                this.editTaskId       = null;
                this.originalStatus   = null;
                this.isAssigneeOnly   = false;
                this.formErrors       = {};
                this.photoPreviewUrl  = null;
                this.photoFileName    = '';
                this.removeAdminPhoto = false;
                if (this.$refs.adminPhotoInput) {
                    this.$refs.adminPhotoInput.value = '';
                }

                const today = new Date().toISOString().slice(0, 10);
                const defaultDue = today + ' 18:00:00';
                this.taskInfo = { category_name: '', formatted_due_at: '', creator_name: '' };
                this.taskForm = {
                    category_id:      '',
                    description:      '',
                    creator_remarks:  '',
                    creator_rating:   '',
                    assignee_remarks: '',
                    status:           'todo',
                    priority:         'medium',
                    due_at:           defaultDue,
                    assignee_ids:     [],
                };
                this.modalOpen = true;
                this.$nextTick(() => {
                    this.initDueAtPicker(defaultDue);
                });
            },

            openEditModal(taskId) {
                this.modalMode        = 'edit';
                this.editTaskId       = taskId;
                this.originalStatus   = null;
                this.isAssigneeOnly   = false;
                this.loadingModal     = true;
                this.formErrors       = {};
                this.photoPreviewUrl  = null;
                this.photoFileName    = '';
                this.removeAdminPhoto = false;
                if (this.$refs.adminPhotoInput) {
                    this.$refs.adminPhotoInput.value = '';
                }

                this.modalOpen        = true;
                this.taskInfo         = { category_name: '', formatted_due_at: '', creator_name: '' };
                this.taskForm         = { category_id: '', description: '', creator_remarks: '', creator_rating: '', assignee_remarks: '', status: 'todo', priority: 'medium', due_at: '', assignee_ids: [] };

                fetch(`/tasks/${taskId}/data`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(d => {
                    const t = d.task;
                    this.originalStatus = t.status;
                    this.isSuperAdmin   = !!t.is_super_admin;
                    this.isAssigneeOnly = !!t.is_assignee_only;
                    this.photoPreviewUrl = t.admin_photo_url || null;
                    this.photoFileName   = t.admin_photo ? 'Attached Photo' : '';
                    this.taskInfo = {
                        category_name:    t.category_name    || '',
                        formatted_due_at: t.formatted_due_at || '',
                        creator_name:     t.creator_name     || '',
                    };
                    this.taskForm = {
                        category_id:      t.category_id      || '',
                        description:      t.description      || '',
                        creator_remarks:  t.creator_remarks  || '',
                        creator_rating:   t.creator_rating   || '',
                        assignee_remarks: t.assignee_remarks || '',
                        status:           t.status           || 'todo',
                        priority:         t.priority         || 'medium',
                        due_at:           t.due_at           || '',
                        assignee_ids:     t.assignee_ids     || [],
                    };
                    this.loadingModal = false;
                    this.$nextTick(() => {
                        this.initDueAtPicker(t.due_at || null);
                    });
                })
                .catch(() => { this.loadingModal = false; this.closeModal(); });
            },

            closeModal() {
                this.modalOpen    = false;
                this.loadingModal = false;
                this.formErrors   = {};
            },

            submitTaskForm() {
                this.submittingTask = true;
                this.formErrors     = {};

                const isCreate = this.modalMode === 'create';
                const url      = isCreate ? '/tasks' : `/tasks/${this.editTaskId}`;

                let formData = new FormData();
                if (!isCreate) {
                    formData.append('_method', 'PUT');
                }
                formData.append('category_id', this.taskForm.category_id || '');
                formData.append('description', this.taskForm.description || '');
                formData.append('creator_remarks', this.taskForm.creator_remarks || '');
                formData.append('creator_rating', this.taskForm.creator_rating || '');
                formData.append('assignee_remarks', this.taskForm.assignee_remarks || '');
                formData.append('status', this.taskForm.status || 'todo');
                formData.append('priority', this.taskForm.priority || 'medium');
                formData.append('due_at', this.taskForm.due_at || '');

                if (this.removeAdminPhoto) {
                    formData.append('remove_admin_photo', '1');
                }

                if (this.$refs.adminPhotoInput && this.$refs.adminPhotoInput.files[0]) {
                    formData.append('admin_photo', this.$refs.adminPhotoInput.files[0]);
                }

                (this.taskForm.assignee_ids || []).forEach(id => {
                    formData.append('assignee_ids[]', id);
                });

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN':     CSRF,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept':           'application/json',
                    },
                    body: formData
                })
                .then(r => {
                    if (!r.ok) return r.json().then(err => Promise.reject(err));
                    return r.json();
                })
                .then(data => {
                    if (data.success) {
                        this.closeModal();
                        this.reloadTableRows();
                    }
                })
                .catch(err => {
                    this.submittingTask = false;
                    if (err.errors) {
                        this.formErrors = err.errors;
                    } else {
                        this.formErrors = { _global: err.message || 'An error occurred. Please try again.' };
                    }
                });
            },

            reloadTableRows() {
                fetch(`/tasks/rows?${FILTER_QS}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    this.submittingTask = false;

                    // Replace table body content
                    const tbody = document.getElementById('task-table-body');
                    if (tbody) {
                        tbody.innerHTML = data.html;
                    }

                    // Update reactive count badges
                    this.counts.total       = data.counts.total;
                    this.counts.todo        = data.counts.todo;
                    this.counts.in_progress = data.counts.in_progress;
                    this.counts.completed   = data.counts.completed;

                    // Show/hide footer
                    const footer = document.getElementById('task-table-footer');
                    if (footer) footer.style.display = data.counts.total > 0 ? '' : 'none';
                })
                .catch(() => { this.submittingTask = false; });
            },
        };
    }

    // ── Global helpers (called from onclick in _table_rows partial) ──
    function cycleStatus(taskId, currentStatus) {
        const cycle = { todo: 'in_progress', in_progress: 'completed', completed: 'todo' };
        fetch(`/tasks/${taskId}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ status: cycle[currentStatus] || 'todo' })
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (ok && data.success) {
                const comp = document.querySelector('[x-data]').__x?.$data || Alpine.$data(document.querySelector('[x-data]'));
                if (comp && comp.reloadTableRows) comp.reloadTableRows();
                else location.reload();
            } else {
                alert(data.message || 'Failed to update task status.');
            }
        })
        .catch(() => { alert('An error occurred while updating task status.'); });
    }

    function deleteTask(taskId) {
        if (!confirm('Are you sure you want to delete this task?')) return;
        fetch(`/tasks/${taskId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const comp = document.querySelector('[x-data]').__x?.$data || Alpine.$data(document.querySelector('[x-data]'));
                if (comp && comp.reloadTableRows) comp.reloadTableRows();
                else location.reload();
            }
        });
    }
</script>
@endpush