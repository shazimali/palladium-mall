@extends('layouts.app')

@section('title', 'Edit Task')

@push('styles')
<style>.flatpickr-calendar { z-index: 999999 !important; }</style>
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

    <x-common.component-card title="Edit Task" desc="Update the category, assignees, status, or remarks.">
        @if($isAssigneeOnly)
            <div class="mb-5 rounded-xl border border-blue-200 bg-blue-50 p-4 text-xs text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                👤 <strong>Assignee Mode:</strong> You are assigned to this task. You can update your <strong>Assignee Remarks</strong> and the <strong>Task Status</strong> below.
            </div>
        @endif

        <form action="{{ route('tasks.update', $task) }}" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-3xl">
            @csrf
            @method('PUT')

            @if($isAssigneeOnly)
                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- ASSIGNEE-ONLY VIEW: Shows ONLY Description, Status & Remarks --}}
                {{-- ══════════════════════════════════════════════════════ --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-800/60 p-5 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Category / Task</span>
                            <span class="text-sm font-black text-indigo-700 dark:text-indigo-400">{{ $task->category?->name ?? $task->title }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Due Date & Time</span>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                {{ $task->due_at ? $task->due_at->format('d M Y, h:i A') : '—' }}
                            </span>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            Task Description / Instructions:
                        </label>
                        <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line leading-relaxed bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                            {{ $task->description ?: 'No description provided.' }}
                        </p>
                    </div>

                    {{-- Creator Rating & Remarks if present --}}
                    @if($task->creator_rating || $task->creator_remarks)
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700 space-y-2">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                Creator / Admin Remarks:
                            </label>
                            @if($task->creator_rating === 'good')
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-extrabold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    ✨ Satisfactory
                                </span>
                            @elseif($task->creator_rating === 'bad')
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-red-100 px-3 py-1.5 text-xs font-extrabold text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                    ⚠️ Unsatisfactory
                                </span>
                            @endif
                            @if($task->creator_remarks)
                                <p class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-line leading-relaxed bg-white dark:bg-gray-900 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                                    {{ $task->creator_remarks }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Status --}}
                @php
                    $isCompletedLocked = ($task->status === 'completed' && !auth()->user()->isSuperAdmin());
                @endphp
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        Task Status *
                        @if($isCompletedLocked)
                            <span class="text-amber-500 font-normal text-[11px] block mt-0.5">
                                (🔒 Only Super Admin can change a completed task)
                            </span>
                        @endif
                    </label>
                    <select name="status" required
                        {{ $isCompletedLocked ? 'disabled' : '' }}
                        class="w-full h-11 px-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 disabled:opacity-60 disabled:cursor-not-allowed">
                        <option value="todo"        {{ old('status', $task->status) === 'todo'        ? 'selected' : '' }}>📌 To Do</option>
                        <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>⚡ In Progress</option>
                        <option value="completed"   {{ old('status', $task->status) === 'completed'   ? 'selected' : '' }}>✅ Completed</option>
                    </select>
                    @if($isCompletedLocked)
                        <input type="hidden" name="status" value="{{ $task->status }}">
                    @endif
                    @error('status')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Assignee Remarks --}}
                <div class="rounded-xl border border-indigo-200 bg-indigo-50/50 dark:border-indigo-900/40 dark:bg-indigo-950/20 p-4">
                    <label class="block text-xs font-bold text-indigo-900 dark:text-indigo-300 mb-1.5">
                        ✍️ Assignee Remarks / Work Description
                    </label>
                    <textarea name="assignee_remarks" rows="4"
                        placeholder="Write what work was done, progress details, or findings here..."
                        class="w-full p-3 text-sm bg-white dark:bg-gray-800 border border-indigo-300 dark:border-indigo-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none resize-none"
                    >{{ old('assignee_remarks', $task->assignee_remarks) }}</textarea>
                    @error('assignee_remarks')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

            @else
                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- FULL VIEW (For Creator / Super Admin) --}}
                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- Category --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Task Category *</label>
                    <select name="category_id" required
                        class="w-full h-11 px-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">— Select a category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $task->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                        @if($task->category && !$task->category->is_active)
                            @php $alreadyListed = $categories->contains('id', $task->category->id); @endphp
                            @unless($alreadyListed)
                                <option value="{{ $task->category->id }}" selected>
                                    {{ $task->category->name }} (Inactive)
                                </option>
                            @endunless
                        @endif
                    </select>
                    @error('category_id')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Description / Instructions</label>
                    <textarea name="description" rows="3"
                        placeholder="Additional details or instructions..."
                        class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                    >{{ old('description', $task->description) }}</textarea>
                    @error('description')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Creator / Admin Remarks & Rating --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 bg-gray-50/50 dark:bg-gray-800/40 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Creator / Admin Remarks & Rating
                        </label>
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="creator_rating" value="good"
                                    {{ old('creator_rating', $task->creator_rating) === 'good' ? 'checked' : '' }}
                                    class="w-4 h-4 text-emerald-500 border-gray-300 dark:border-gray-700 focus:ring-emerald-500/20">
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-extrabold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    ✨ Satisfactory
                                </span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="creator_rating" value="bad"
                                    {{ old('creator_rating', $task->creator_rating) === 'bad' ? 'checked' : '' }}
                                    class="w-4 h-4 text-red-500 border-gray-300 dark:border-gray-700 focus:ring-red-500/20">
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-red-100 px-3 py-1.5 text-xs font-extrabold text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                    ⚠️ Unsatisfactory
                                </span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="creator_rating" value=""
                                    {{ !in_array(old('creator_rating', $task->creator_rating), ['good', 'bad']) ? 'checked' : '' }}
                                    class="w-4 h-4 text-gray-400 border-gray-300 dark:border-gray-700 focus:ring-gray-500/20">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">None</span>
                            </label>
                        </div>
                    </div>

                    {{-- Remarks Input Field --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Remarks Text / Instructions
                        </label>
                        <textarea name="creator_remarks" rows="2"
                            placeholder="Type remarks or instructions here..."
                            class="w-full p-3 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                        >{{ old('creator_remarks', $task->creator_remarks) }}</textarea>
                        @error('creator_remarks')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Admin Remarks Photo Upload --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            📷 Attach / Update Photo (Optional)
                        </label>
                        
                        @if($task->admin_photo && $task->admin_photo_url)
                            <div class="mb-3 flex items-center gap-3 p-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                <img src="{{ $task->admin_photo_url }}" alt="Current Photo" class="h-12 w-12 rounded-lg object-cover border border-gray-300 shadow-2xs">
                                <div>
                                    <a href="{{ $task->admin_photo_url }}" target="_blank" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline block">
                                        View Current Attachment ↗
                                    </a>
                                    <label class="inline-flex items-center gap-1.5 mt-1 text-xs text-red-600 font-semibold cursor-pointer">
                                        <input type="checkbox" name="remove_admin_photo" value="1" class="rounded text-red-600 focus:ring-red-500">
                                        <span>Delete this photo</span>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <input type="file" name="admin_photo" accept="image/*"
                            class="w-full text-xs text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300">
                        <span class="text-[11px] text-gray-400 block mt-1">JPEG, PNG, WEBP up to 200 KB</span>
                        @error('admin_photo')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Assignee Remarks --}}
                <div class="rounded-xl border border-indigo-200 bg-indigo-50/50 dark:border-indigo-900/40 dark:bg-indigo-950/20 p-4">
                    <label class="block text-xs font-bold text-indigo-900 dark:text-indigo-300 mb-1.5 flex items-center justify-between">
                        <span>✍️ Assignee Remarks / Work Description</span>
                        <span class="text-[11px] font-normal text-indigo-600 dark:text-indigo-400">(Updated by assignee)</span>
                    </label>
                    <textarea name="assignee_remarks" rows="3"
                        placeholder="Write what work was done, progress details, or findings here..."
                        class="w-full p-3 text-sm bg-white dark:bg-gray-800 border border-indigo-300 dark:border-indigo-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none resize-none"
                    >{{ old('assignee_remarks', $task->assignee_remarks) }}</textarea>
                    @error('assignee_remarks')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- Status --}}
                    @php
                        $isCompletedLocked = ($task->status === 'completed' && !auth()->user()->isSuperAdmin());
                    @endphp
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Status *
                            @if($isCompletedLocked)
                                <span class="text-amber-500 font-normal text-[11px] block mt-0.5">
                                    (🔒 Only Super Admin can change a completed task)
                                </span>
                            @endif
                        </label>
                        <select name="status" required
                            {{ $isCompletedLocked ? 'disabled' : '' }}
                            class="w-full h-11 px-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 disabled:opacity-60 disabled:cursor-not-allowed">
                            <option value="todo"        {{ old('status', $task->status) === 'todo'        ? 'selected' : '' }}>📌 To Do</option>
                            <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>⚡ In Progress</option>
                            <option value="completed"   {{ old('status', $task->status) === 'completed'   ? 'selected' : '' }}>✅ Completed</option>
                        </select>
                        @if($isCompletedLocked)
                            <input type="hidden" name="status" value="{{ $task->status }}">
                        @endif
                        @error('status')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Priority --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Priority *</label>
                        <select name="priority" required
                            class="w-full h-11 px-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20">
                            <option value="low"    {{ old('priority', $task->priority) === 'low'    ? 'selected' : '' }}>⚪ Low</option>
                            <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>🔵 Medium</option>
                            <option value="high"   {{ old('priority', $task->priority) === 'high'   ? 'selected' : '' }}>🟠 High</option>
                            <option value="urgent" {{ old('priority', $task->priority) === 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                        </select>
                        @error('priority')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Due Date & Time --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Due Date & Time *</label>
                        <input type="text" id="task_due_at" name="due_at"
                            value="{{ old('due_at', $task->due_at ? $task->due_at->format('Y-m-d H:i:s') : '') }}"
                            placeholder="Select due date & time..."
                            readonly
                            required
                            class="w-full h-11 px-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white cursor-pointer focus:ring-2 focus:ring-brand-500/20">
                        @error('due_at')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Assignees --}}
                @php $assignedIds = old('assignee_ids', $task->assignees->pluck('id')->toArray()); @endphp
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Assign To *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-56 overflow-y-auto border border-gray-300 dark:border-gray-700 rounded-xl p-4 bg-gray-50/50 dark:bg-gray-800/40">
                        @foreach($users as $u)
                            <label class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-brand-400 transition-colors">
                                <input type="checkbox" name="assignee_ids[]" value="{{ $u->id }}"
                                    {{ in_array($u->id, $assignedIds) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded text-brand-500 border-gray-300 dark:border-gray-700 focus:ring-brand-500/20">
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
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                <button type="submit"
                    class="px-6 py-2.5 text-xs font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-xl shadow-xs transition-all cursor-pointer">
                    Update Task
                </button>
                <a href="{{ route('tasks.index', ['date' => $task->due_at?->toDateString()]) }}"
                    class="px-5 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition-colors">
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
