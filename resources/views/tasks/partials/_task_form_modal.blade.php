{{-- ─── Task Form Modal (Create / Edit) ─── --}}
<div x-show="modalOpen" class="fixed inset-0 z-50 flex" style="display:none;">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal()"></div>

    {{-- Panel --}}
    <div class="relative ml-auto h-full w-full max-w-lg bg-white dark:bg-gray-900 shadow-2xl flex flex-col overflow-hidden"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 px-5 py-4 flex-shrink-0">
            <div>
                <h2 class="text-base font-extrabold text-gray-900 dark:text-white"
                    x-text="modalMode === 'create' ? '📋 Assign New Task' : (isAssigneeOnly ? '📝 Update Task Progress' : '✏️ Edit Task')"></h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"
                   x-text="modalMode === 'create' ? 'Select a category and assign to team members.' : (isAssigneeOnly ? 'Review description and update status & remarks.' : 'Update task details, remarks, or status.')"></p>
            </div>
            <button type="button" @click="closeModal()"
                class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Loading Spinner --}}
        <div x-show="loadingModal" class="flex-1 flex items-center justify-center">
            <div class="flex flex-col items-center gap-3">
                <svg class="animate-spin h-8 w-8 text-brand-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span class="text-xs text-gray-500">Loading task data…</span>
            </div>
        </div>

        {{-- Form --}}
        <form x-show="!loadingModal" @submit.prevent="submitTaskForm()" class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

            {{-- ══════════════════════════════════════════════════════ --}}
            {{-- ASSIGNEE-ONLY VIEW: Shows ONLY Description, Status & Remarks --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            <template x-if="modalMode === 'edit' && isAssigneeOnly">
                <div class="space-y-4">
                    {{-- Task Info Card --}}
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-800/60 p-4 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-200 dark:border-gray-700">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-gray-400 block">Category</span>
                                <span class="text-xs font-black text-indigo-700 dark:text-indigo-400" x-text="taskInfo.category_name"></span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-gray-400 block">Due Date</span>
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300" x-text="taskInfo.formatted_due_at || '—'"></span>
                            </div>
                        </div>

                        {{-- Task Description --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Task Description / Instructions:
                            </label>
                            <p class="text-xs text-gray-800 dark:text-gray-200 whitespace-pre-line leading-relaxed bg-white dark:bg-gray-900 p-3 rounded-lg border border-gray-200 dark:border-gray-700"
                               x-text="taskForm.description || 'No description provided.'"></p>
                        </div>

                        {{-- Creator Rating / Remarks if present --}}
                        <template x-if="taskForm.creator_rating || taskForm.creator_remarks">
                            <div class="pt-2 border-t border-gray-200 dark:border-gray-700 space-y-1.5">
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Creator / Admin Remarks:
                                </label>
                                <div class="flex items-center gap-2">
                                    <template x-if="taskForm.creator_rating === 'good'">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-extrabold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                            👍 Good
                                        </span>
                                    </template>
                                    <template x-if="taskForm.creator_rating === 'bad'">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-red-100 px-2.5 py-1 text-xs font-extrabold text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                            👎 Bad
                                        </span>
                                    </template>
                                </div>
                                <template x-if="taskForm.creator_remarks">
                                    <p class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-line leading-relaxed bg-white dark:bg-gray-900 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700"
                                       x-text="taskForm.creator_remarks"></p>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Status Dropdown --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Task Status *
                            <span x-show="originalStatus === 'completed' && !isSuperAdmin" class="text-amber-500 font-normal text-[11px] ml-1">
                                (🔒 Completed — Super Admin only)
                            </span>
                        </label>
                        <select x-model="taskForm.status"
                            :disabled="originalStatus === 'completed' && !isSuperAdmin"
                            class="w-full h-11 px-3 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed">
                            <option value="todo">📌 To Do</option>
                            <option value="in_progress">⚡ In Progress</option>
                            <option value="completed">✅ Completed</option>
                        </select>
                    </div>

                    {{-- Assignee Remarks --}}
                    <div class="rounded-xl border border-indigo-200 bg-indigo-50/50 dark:border-indigo-900/40 dark:bg-indigo-950/20 p-4">
                        <label class="block text-xs font-bold text-indigo-900 dark:text-indigo-300 mb-1.5">
                            ✍️ Assignee Remarks / Work Done:
                        </label>
                        <textarea x-model="taskForm.assignee_remarks" rows="4"
                            placeholder="Describe the work done, findings, progress, or notes..."
                            class="w-full p-3 text-sm bg-white dark:bg-gray-800 border border-indigo-300 dark:border-indigo-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none resize-none"></textarea>
                        <template x-if="formErrors.assignee_remarks">
                            <span class="text-xs text-red-500 mt-1 block" x-text="formErrors.assignee_remarks[0]"></span>
                        </template>
                    </div>
                </div>
            </template>

            {{-- ══════════════════════════════════════════════════════ --}}
            {{-- FULL VIEW (For Creator / Super Admin / Create Mode) --}}
            {{-- ══════════════════════════════════════════════════════ --}}
            <div x-show="!isAssigneeOnly" class="space-y-4">
                {{-- Category --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Task Category *</label>
                    @if($categories->isEmpty())
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                            ⚠️ No active categories. Click <strong>Manage Categories</strong> to add some.
                        </div>
                    @else
                        <select x-model="taskForm.category_id" required
                            class="w-full h-11 px-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:outline-none">
                            <option value="">— Select a category —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <template x-if="formErrors.category_id">
                        <span class="text-xs text-red-500 mt-1 block" x-text="formErrors.category_id[0]"></span>
                    </template>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Description / Instructions</label>
                    <textarea x-model="taskForm.description" rows="2"
                        placeholder="Additional details or instructions..."
                        class="w-full p-3 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:outline-none resize-none"></textarea>
                </div>

                {{-- Creator / Admin Remarks (Rating Option + Input Field) --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3.5 bg-gray-50/50 dark:bg-gray-800/40 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Creator / Admin Remarks & Rating
                        </label>
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" value="good" x-model="taskForm.creator_rating"
                                    class="w-4 h-4 text-emerald-500 border-gray-300 dark:border-gray-700 focus:ring-emerald-500/20">
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-extrabold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    ✨ Satisfactory
                                </span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" value="bad" x-model="taskForm.creator_rating"
                                    class="w-4 h-4 text-red-500 border-gray-300 dark:border-gray-700 focus:ring-red-500/20">
                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-red-100 px-3 py-1.5 text-xs font-extrabold text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                    ⚠️ Unsatisfactory
                                </span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" value="" x-model="taskForm.creator_rating"
                                    class="w-4 h-4 text-gray-400 border-gray-300 dark:border-gray-700 focus:ring-gray-500/20">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold">None</span>
                            </label>
                        </div>
                    </div>

                    {{-- Remarks Input Field --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">
                            Remarks Text / Notes
                        </label>
                        <textarea x-model="taskForm.creator_remarks" rows="2"
                            placeholder="Type remarks or instructions here..."
                            class="w-full p-2.5 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:outline-none resize-none"></textarea>
                    </div>

                    {{-- Admin Remarks Photo Upload --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 mb-1">
                            📷 Attach Photo / Document Image (Optional)
                        </label>
                        
                        <div class="flex items-center gap-3">
                            <label class="cursor-pointer inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 text-xs font-bold text-gray-700 dark:text-gray-300 transition-colors shadow-2xs">
                                <svg class="w-4 h-4 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Choose Photo</span>
                                <input type="file" accept="image/*" x-ref="adminPhotoInput" x-on:change="handlePhotoChange($event)" class="hidden">
                            </label>

                            <template x-if="photoPreviewUrl">
                                <div class="relative flex items-center gap-2 p-1.5 bg-brand-50/60 dark:bg-brand-950/40 rounded-xl border border-brand-200 dark:border-brand-800">
                                    <img :src="photoPreviewUrl" alt="Admin Photo" class="h-10 w-10 object-cover rounded-lg border border-brand-300 shadow-2xs">
                                    <span class="text-[11px] font-bold text-brand-900 dark:text-brand-300 truncate max-w-[120px]" x-text="photoFileName"></span>
                                    <button type="button" x-on:click="removePhoto()" class="text-rose-500 hover:text-rose-700 p-1 text-xs font-bold" title="Remove photo">✕</button>
                                </div>
                            </template>
                        </div>
                        <span class="text-[10px] text-gray-400 block mt-1">JPEG, PNG, WEBP up to 200 KB</span>
                        <template x-if="formErrors.admin_photo">
                            <span class="text-xs text-red-500 mt-1 block" x-text="formErrors.admin_photo[0]"></span>
                        </template>
                    </div>
                </div>

                {{-- Assignee Remarks --}}
                <div class="rounded-xl border border-indigo-200 bg-indigo-50/50 dark:border-indigo-900/40 dark:bg-indigo-950/20 p-3.5">
                    <label class="block text-xs font-bold text-indigo-900 dark:text-indigo-300 mb-1.5 flex items-center justify-between">
                        <span>✍️ Assignee Remarks / Work Description</span>
                        <span class="text-[11px] font-normal text-indigo-600 dark:text-indigo-400">(Updated by assignee)</span>
                    </label>
                    <textarea x-model="taskForm.assignee_remarks" rows="3"
                        placeholder="Write what work was done, progress details, or findings here..."
                        class="w-full p-3 text-sm bg-white dark:bg-gray-800 border border-indigo-300 dark:border-indigo-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none resize-none"></textarea>
                    <template x-if="formErrors.assignee_remarks">
                        <span class="text-xs text-red-500 mt-1 block" x-text="formErrors.assignee_remarks[0]"></span>
                    </template>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Priority --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Priority *</label>
                        <select x-model="taskForm.priority" required
                            class="w-full h-11 px-3 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:outline-none">
                            <option value="low">⚪ Low</option>
                            <option value="medium">🔵 Medium</option>
                            <option value="high">🟠 High</option>
                            <option value="urgent">🔴 Urgent</option>
                        </select>
                        <template x-if="formErrors.priority">
                            <span class="text-xs text-red-500 mt-1 block" x-text="formErrors.priority[0]"></span>
                        </template>
                    </div>

                    {{-- Status (edit only) --}}
                    <div x-show="modalMode === 'edit'">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Status *
                            <span x-show="originalStatus === 'completed' && !isSuperAdmin" class="text-amber-500 font-normal text-[11px] ml-1">
                                (🔒 Super Admin only)
                            </span>
                        </label>
                        <select x-model="taskForm.status"
                            :disabled="originalStatus === 'completed' && !isSuperAdmin"
                            class="w-full h-11 px-3 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:outline-none disabled:opacity-60 disabled:cursor-not-allowed">
                            <option value="todo">📌 To Do</option>
                            <option value="in_progress">⚡ In Progress</option>
                            <option value="completed">✅ Completed</option>
                        </select>
                    </div>
                </div>

                {{-- Due Date & Time (Flatpickr Date & Time Picker) --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Due Date & Time *</label>
                    <div class="relative">
                        <input type="text" x-ref="dueAtInput" id="modal_due_at" required
                            placeholder="Select due date & time..."
                            readonly
                            class="w-full h-11 px-4 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:outline-none cursor-pointer">
                    </div>
                    <template x-if="formErrors.due_at">
                        <span class="text-xs text-red-500 mt-1 block" x-text="formErrors.due_at[0]"></span>
                    </template>
                </div>

                {{-- Assignees --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Assign To *</label>
                    <div class="grid grid-cols-1 gap-2 max-h-40 overflow-y-auto border border-gray-300 dark:border-gray-700 rounded-xl p-3 bg-gray-50/50 dark:bg-gray-800/40">
                        @foreach($users as $u)
                            <label class="flex items-center gap-3 p-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-brand-400 transition-colors">
                                <input type="checkbox"
                                    :value="{{ $u->id }}"
                                    x-model="taskForm.assignee_ids"
                                    class="w-4 h-4 rounded text-brand-500 border-gray-300 dark:border-gray-700 focus:ring-brand-500/20">
                                <div class="text-xs">
                                    <span class="font-bold text-gray-900 dark:text-white block">{{ $u->name }}</span>
                                    <span class="text-gray-400 text-[11px]">{{ $u->email }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <template x-if="formErrors.assignee_ids">
                        <span class="text-xs text-red-500 mt-1 block" x-text="formErrors.assignee_ids[0]"></span>
                    </template>
                </div>
            </div>

            {{-- Global form error --}}
            <template x-if="formErrors._global">
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"
                     x-text="formErrors._global"></div>
            </template>

        </form>

        {{-- Footer Actions --}}
        <div x-show="!loadingModal"
            class="flex-shrink-0 border-t border-gray-200 dark:border-gray-800 px-5 py-4 flex items-center gap-3 bg-white dark:bg-gray-900">
            <button type="button" @click="submitTaskForm()"
                :disabled="submittingTask || (!isAssigneeOnly && (!taskForm.category_id || !taskForm.assignee_ids.length || !taskForm.due_at))"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-5 py-2.5 text-xs font-extrabold text-white hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                <svg x-show="submittingTask" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span x-text="submittingTask ? 'Saving…' : (modalMode === 'create' ? 'Assign Task' : 'Update Task')"></span>
            </button>
            <button type="button" @click="closeModal()"
                :disabled="submittingTask"
                class="px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition-colors disabled:opacity-50">
                Cancel
            </button>
        </div>

    </div>
</div>
