@extends('layouts.app')

@section('title', 'Assign Daily Task')

@push('styles')
<style>.flatpickr-calendar { z-index: 999999 !important; }</style>
@endpush

@section('content')
<div class="space-y-6">
    <x-common.page-breadcrumb pageTitle="Assign Daily Task" />

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="p-4 rounded-xl border border-red-200 bg-red-50 text-xs text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <x-common.component-card title="Assign Task" desc="Select a task category and assign it to one or more team members.">
        <form action="{{ route('tasks.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-3xl">
            @csrf

            {{-- Category --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Task Category *</label>
                    <a href="{{ route('tasks.index') }}" onclick="event.preventDefault(); history.back();"
                        class="text-xs text-brand-500 hover:underline font-semibold">← Back to Tasks</a>
                </div>
                @if($categories->isEmpty())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                        ⚠️ No active task categories found.
                        <a href="{{ route('tasks.index') }}" class="font-bold underline">Go to Tasks</a>
                        and click <strong>Manage Categories</strong> to add some first.
                    </div>
                @else
                    <select name="category_id" required
                        class="w-full h-11 px-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        <option value="">— Select a category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                @endif
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Description / Instructions</label>
                <textarea name="description" rows="3"
                    placeholder="Additional details or instructions for this task..."
                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                >{{ old('description') }}</textarea>
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
                                {{ old('creator_rating') === 'good' ? 'checked' : '' }}
                                class="w-4 h-4 text-emerald-500 border-gray-300 dark:border-gray-700 focus:ring-emerald-500/20">
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-extrabold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                ✨ Satisfactory
                            </span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="creator_rating" value="bad"
                                {{ old('creator_rating') === 'bad' ? 'checked' : '' }}
                                class="w-4 h-4 text-red-500 border-gray-300 dark:border-gray-700 focus:ring-red-500/20">
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-red-100 px-3 py-1.5 text-xs font-extrabold text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                ⚠️ Unsatisfactory
                            </span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="creator_rating" value=""
                                {{ !in_array(old('creator_rating'), ['good', 'bad']) ? 'checked' : '' }}
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
                    >{{ old('creator_remarks') }}</textarea>
                    @error('creator_remarks')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Admin Remarks Photo Upload --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        📷 Attach Photo / Document Image (Optional)
                    </label>
                    <input type="file" name="admin_photo" accept="image/*"
                        class="w-full text-xs text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300">
                    <span class="text-[11px] text-gray-400 block mt-1">JPEG, PNG, WEBP up to 200 KB</span>
                    @error('admin_photo')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Assignee Remarks --}}
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Assignee Remarks / Description</label>
                <textarea name="assignee_remarks" rows="2"
                    placeholder="Assignee notes, work done, or feedback about this task..."
                    class="w-full p-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"
                >{{ old('assignee_remarks') }}</textarea>
                @error('assignee_remarks')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Priority --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Priority *</label>
                    <select name="priority" required
                        class="w-full h-11 px-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20">
                        <option value="low"    {{ old('priority') === 'low'    ? 'selected' : '' }}>⚪ Low</option>
                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>🔵 Medium</option>
                        <option value="high"   {{ old('priority') === 'high'   ? 'selected' : '' }}>🟠 High</option>
                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>🔴 Urgent</option>
                    </select>
                    @error('priority')
                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Due Date & Time --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Due Date & Time *</label>
                    <input type="text" id="task_due_at" name="due_at"
                        value="{{ old('due_at') }}"
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
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Assign To *</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-56 overflow-y-auto border border-gray-300 dark:border-gray-700 rounded-xl p-4 bg-gray-50/50 dark:bg-gray-800/40">
                    @foreach($users as $u)
                        <label class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-brand-400 transition-colors">
                            <input type="checkbox" name="assignee_ids[]" value="{{ $u->id }}"
                                {{ is_array(old('assignee_ids')) && in_array($u->id, old('assignee_ids')) ? 'checked' : '' }}
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

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                <button type="submit"
                    class="px-6 py-2.5 text-xs font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-xl shadow-xs transition-all cursor-pointer">
                    Assign Task
                </button>
                <a href="{{ route('tasks.index') }}"
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
                defaultDate: new Date()
            });
        }
    });
</script>
@endpush
