@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Note Pad" />

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div x-data="notePadApp">

        {{-- Top Bar & Filters --}}
        <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                
                {{-- Search Input --}}
                <form action="{{ route('note-pads.index') }}" method="GET" class="flex-1 flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-[200px]">
                        <span class="absolute -translate-y-1/2 pointer-events-none left-3.5 top-1/2 text-gray-400">
                            🔍
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search tasks & notes..."
                            class="w-full rounded-xl border border-gray-300 bg-gray-50 py-2 pl-10 pr-4 text-sm font-bold text-gray-900 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                    {{-- Date Filter --}}
                    <div>
                        <input type="text" id="filter_date" name="date" value="{{ request('date') }}" placeholder="Filter by Date" autocomplete="off"
                            class="rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer" />
                    </div>

                    {{-- Color Filter --}}
                    <div>
                        <select name="color" onchange="this.form.submit()"
                            class="rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2 text-sm font-bold text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="">All Colors</option>
                            <option value="default" {{ request('color') == 'default' ? 'selected' : '' }}>Default White</option>
                            <option value="yellow" {{ request('color') == 'yellow' ? 'selected' : '' }}>Yellow</option>
                            <option value="blue" {{ request('color') == 'blue' ? 'selected' : '' }}>Blue</option>
                            <option value="green" {{ request('color') == 'green' ? 'selected' : '' }}>Green</option>
                            <option value="pink" {{ request('color') == 'pink' ? 'selected' : '' }}>Pink</option>
                            <option value="purple" {{ request('color') == 'purple' ? 'selected' : '' }}>Purple</option>
                            <option value="orange" {{ request('color') == 'orange' ? 'selected' : '' }}>Orange</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-bold text-white shadow-xs hover:bg-brand-700 transition-all">
                        Filter
                    </button>

                    @if(request()->anyFilled(['search', 'date', 'color', 'status']))
                        <a href="{{ route('note-pads.index') }}"
                            class="rounded-xl border border-gray-300 px-3.5 py-2 text-sm font-bold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 transition-all">
                            Clear
                        </a>
                    @endif
                </form>

                {{-- Action Counters --}}
                <div class="flex items-center gap-2">
                    <span class="rounded-xl bg-brand-50 px-3 py-1.5 text-xs font-black text-brand-600 dark:bg-brand-950/40 dark:text-brand-300">
                        {{ $totalCount }} {{ Str::plural('Note', $totalCount) }}
                    </span>
                </div>

            </div>
        </div>

        {{-- GOOGLE KEEP QUICK CREATE CARD --}}
        <div class="mx-auto max-w-2xl mb-8">
            <div x-show="!expandedCreate" @click="expandedCreate = true; $nextTick(() => $refs.createTitleInput.focus())"
                class="cursor-pointer rounded-2xl border border-gray-300 bg-white p-4 shadow-md hover:shadow-lg transition-all dark:border-gray-700 dark:bg-gray-900 flex items-center justify-between">
                <span class="text-base font-bold text-gray-500 dark:text-gray-400">Take a note or add a task...</span>
                <div class="flex items-center gap-2 text-gray-400">
                    <span title="Checklist" class="hover:text-brand-600">☑️</span>
                </div>
            </div>

            <div x-show="expandedCreate" x-cloak x-transition @click.away="if (!title && !content && checklistItems.length === 0) expandedCreate = false"
                :style="getCardStyle(createColor)"
                class="rounded-2xl border-2 border-brand-500 bg-white p-5 shadow-2xl transition-all dark:bg-gray-900"
                :class="getColorClass(createColor)">
                
                <form action="{{ route('note-pads.store') }}" method="POST" @submit="handleCreateSubmit($event)">
                    @csrf
                    <input type="hidden" name="color" x-model="createColor">
                    <input type="hidden" name="is_pinned" :value="createPinned ? '1' : '0'">
                    <input type="hidden" name="is_checklist" :value="createIsChecklist ? '1' : '0'">

                    {{-- Header / Title & Pin --}}
                    <div class="flex items-center justify-between mb-3">
                        <input type="text" x-ref="createTitleInput" name="title" x-model="title" placeholder="Title"
                            class="w-full bg-transparent text-lg font-black text-gray-900 dark:text-white focus:outline-none placeholder-gray-400" />
                        <button type="button" @click="createPinned = !createPinned"
                            class="p-1.5 rounded-lg text-lg hover:bg-black/5 dark:hover:bg-white/5 transition-all"
                            :title="createPinned ? 'Unpin Note' : 'Pin Note'">
                            <span x-text="createPinned ? '📌' : '📍'"></span>
                        </button>
                    </div>

                    {{-- Text Content OR Checklist --}}
                    <div class="mb-4">
                        <template x-if="!createIsChecklist">
                            <textarea name="content" x-model="content" rows="3" placeholder="Take a note..."
                                class="w-full bg-transparent text-sm font-semibold text-gray-800 dark:text-gray-200 focus:outline-none placeholder-gray-400 resize-none"></textarea>
                        </template>

                        <template x-if="createIsChecklist">
                            <div class="space-y-2">
                                <template x-for="(item, idx) in checklistItems" :key="idx">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-400">☐</span>
                                        <input type="text" :name="'checklist[' + idx + ']'" x-model="checklistItems[idx]" placeholder="List item"
                                            @keydown.enter.prevent="addChecklistItem()"
                                            class="flex-1 bg-transparent text-sm font-semibold text-gray-800 dark:text-gray-200 focus:outline-none placeholder-gray-400" />
                                        <button type="button" @click="removeChecklistItem(idx)" class="text-xs text-rose-500 font-bold hover:underline">✕</button>
                                    </div>
                                </template>
                                <button type="button" @click="addChecklistItem()"
                                    class="text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 flex items-center gap-1 mt-1">
                                    + Add Item
                                </button>
                            </div>
                        </template>
                    </div>

                    {{-- Footer Controls: Date, Color Picker, Checklist Toggle, Close/Save --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-200/60 dark:border-gray-800/60">
                        <div class="flex items-center gap-2">
                            {{-- Date Input --}}
                            <input type="text" id="create_date" name="date" x-model="createDate" required autocomplete="off"
                                class="rounded-xl border border-gray-300 bg-white/80 px-2.5 py-1 text-xs font-bold text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer" />

                            {{-- Checklist Toggle --}}
                            <button type="button" @click="toggleChecklistMode()"
                                :class="createIsChecklist ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'"
                                class="px-2.5 py-1 rounded-xl text-xs font-bold transition-all">
                                ☑️ Checklist
                            </button>

                            {{-- Color Palette Buttons --}}
                            <div class="flex items-center gap-1.5 ml-1">
                                <template x-for="c in ['default', 'yellow', 'blue', 'green', 'pink', 'purple', 'orange']" :key="c">
                                    <button type="button" @click="createColor = c"
                                        :style="'background-color: ' + getColorHex(c)"
                                        class="h-6 w-6 rounded-full border-2 border-gray-300 dark:border-gray-600 shadow-2xs transition-all hover:scale-125 cursor-pointer"
                                        :class="createColor === c ? 'ring-2 ring-brand-500 ring-offset-1 scale-110 border-brand-600' : 'opacity-80 hover:opacity-100'"
                                        :title="c.charAt(0).toUpperCase() + c.slice(1)"></button>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="expandedCreate = false"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold text-gray-600 hover:bg-black/5 dark:text-gray-400 dark:hover:bg-white/5 transition-all">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-1.5 rounded-xl bg-brand-600 text-xs font-black text-white shadow-xs hover:bg-brand-700 transition-all cursor-pointer">
                                Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- PINNED NOTES SECTION --}}
        @if($pinnedNotes->isNotEmpty())
            <div class="mb-8">
                <h3 class="mb-4 text-xs font-black tracking-widest uppercase text-gray-400 dark:text-gray-500 flex items-center gap-2">
                    <span>📌 PINNED</span>
                </h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($pinnedNotes as $note)
                        @include('note_pads._card', ['note' => $note])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- OTHER NOTES SECTION --}}
        <div>
            @if($pinnedNotes->isNotEmpty())
                <h3 class="mb-4 text-xs font-black tracking-widest uppercase text-gray-400 dark:text-gray-500 flex items-center gap-2">
                    <span>OTHERS</span>
                </h3>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse($otherNotes as $note)
                    @include('note_pads._card', ['note' => $note])
                @empty
                    @if($pinnedNotes->isEmpty())
                        <div class="col-span-full py-12 text-center">
                            <span class="text-4xl">📝</span>
                            <h4 class="mt-2 text-base font-bold text-gray-700 dark:text-gray-300">No notes or tasks found</h4>
                            <p class="text-xs text-gray-400 mt-1">Click "Take a note..." above to add your first task note!</p>
                        </div>
                    @endif
                @endforelse
            </div>
        </div>

        {{-- EDIT NOTE MODAL (Supports Text & Checklists) --}}
        <div x-show="editModalOpen" x-cloak x-transition
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div @click.away="editModalOpen = false"
                :style="getCardStyle(editColor)"
                class="w-full max-w-lg rounded-3xl border-2 border-brand-500 bg-white p-6 shadow-2xl dark:bg-gray-900"
                :class="getColorClass(editColor)">
                
                <form :action="'/note-pads/' + editId" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="color" x-model="editColor">
                    <input type="hidden" name="is_pinned" :value="editPinned ? '1' : '0'">
                    <input type="hidden" name="is_checklist" :value="editIsChecklist ? '1' : '0'">

                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200/60 dark:border-gray-800/60">
                        <input type="text" name="title" x-model="editTitle" placeholder="Title"
                            class="w-full bg-transparent text-xl font-black text-gray-900 dark:text-white focus:outline-none" />
                        <button type="button" @click="editPinned = !editPinned" class="p-1 rounded-lg text-lg">
                            <span x-text="editPinned ? '📌' : '📍'"></span>
                        </button>
                    </div>

                    {{-- Date & Color in Modal --}}
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-gray-500">Date:</label>
                            <input type="text" id="edit_date" name="date" x-model="editDate" required autocomplete="off"
                                class="rounded-xl border border-gray-300 bg-white/80 px-3 py-1 text-xs font-bold text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white cursor-pointer" />
                        </div>

                        {{-- Color Palette Buttons --}}
                        <div class="flex items-center gap-1.5">
                            <template x-for="c in ['default', 'yellow', 'blue', 'green', 'pink', 'purple', 'orange']" :key="c">
                                <button type="button" @click="editColor = c"
                                    :style="'background-color: ' + getColorHex(c)"
                                    class="h-6 w-6 rounded-full border-2 border-gray-300 dark:border-gray-600 shadow-2xs transition-all hover:scale-125 cursor-pointer"
                                    :class="editColor === c ? 'ring-2 ring-brand-500 ring-offset-1 scale-110 border-brand-600' : 'opacity-80 hover:opacity-100'"
                                    :title="c.charAt(0).toUpperCase() + c.slice(1)"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Text Content OR Checklist in Modal --}}
                    <div class="mb-6">
                        <template x-if="!editIsChecklist">
                            <textarea name="content" x-model="editContent" rows="5" placeholder="Note content..."
                                class="w-full bg-transparent text-sm font-semibold text-gray-800 dark:text-gray-200 focus:outline-none resize-none"></textarea>
                        </template>

                        <template x-if="editIsChecklist">
                            <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                <template x-for="(item, idx) in editChecklistItems" :key="idx">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" :name="'checklist[' + idx + '][completed]'" value="1"
                                            x-model="editChecklistItems[idx].completed"
                                            class="h-4 w-4 rounded-md border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer" />
                                        <input type="text" :name="'checklist[' + idx + '][text]'"
                                            x-model="editChecklistItems[idx].text" placeholder="List item"
                                            @keydown.enter.prevent="addEditChecklistItem()"
                                            class="flex-1 bg-transparent text-sm font-semibold text-gray-800 dark:text-gray-200 focus:outline-none border-b border-gray-200/60 dark:border-gray-700/60 py-1" />
                                        <button type="button" @click="removeEditChecklistItem(idx)" class="text-xs text-rose-500 font-bold hover:underline">✕</button>
                                    </div>
                                </template>
                                <button type="button" @click="addEditChecklistItem()"
                                    class="text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400 flex items-center gap-1 mt-2">
                                    + Add Item
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200/60 dark:border-gray-800/60">
                        <button type="button" @click="editModalOpen = false"
                            class="px-4 py-2 rounded-xl text-xs font-bold text-gray-600 hover:bg-black/5 dark:text-gray-400 dark:hover:bg-white/5">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2 rounded-xl bg-brand-600 text-xs font-black text-white shadow-md hover:bg-brand-700 cursor-pointer">
                            Update Note
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', function () {
            Alpine.data('notePadApp', () => ({
                expandedCreate: false,
                title: '',
                content: '',
                createDate: '{{ now()->toDateString() }}',
                createColor: 'default',
                createPinned: false,
                createIsChecklist: false,
                checklistItems: [''],

                // Edit Modal state
                editModalOpen: false,
                editId: null,
                editTitle: '',
                editContent: '',
                editDate: '',
                editColor: 'default',
                editPinned: false,
                editIsChecklist: false,
                editChecklistItems: [],

                getColorHex(c) {
                    switch(c) {
                        case 'yellow': return '#fef08a';
                        case 'blue': return '#bae6fd';
                        case 'green': return '#a7f3d0';
                        case 'pink': return '#fecdd3';
                        case 'purple': return '#e9d5ff';
                        case 'orange': return '#fed7aa';
                        default: return '#ffffff';
                    }
                },

                getCardStyle(c) {
                    switch(c) {
                        case 'yellow': return 'background-color: #fef9c3;';
                        case 'blue': return 'background-color: #e0f2fe;';
                        case 'green': return 'background-color: #dcfce7;';
                        case 'pink': return 'background-color: #ffe4e6;';
                        case 'purple': return 'background-color: #f3e8ff;';
                        case 'orange': return 'background-color: #ffedd5;';
                        default: return 'background-color: #ffffff;';
                    }
                },

                toggleChecklistMode() {
                    this.createIsChecklist = !this.createIsChecklist;
                    if (this.createIsChecklist && this.checklistItems.length === 0) {
                        this.checklistItems = [''];
                    }
                },

                addChecklistItem() {
                    this.checklistItems.push('');
                },

                removeChecklistItem(idx) {
                    this.checklistItems.splice(idx, 1);
                },

                addEditChecklistItem() {
                    this.editChecklistItems.push({ text: '', completed: false });
                },

                removeEditChecklistItem(idx) {
                    this.editChecklistItems.splice(idx, 1);
                },

                getColorClass(color) {
                    switch(color) {
                        case 'yellow': return 'bg-amber-100 dark:bg-amber-950/60 border-amber-300 dark:border-amber-700';
                        case 'blue': return 'bg-sky-100 dark:bg-sky-950/60 border-sky-300 dark:border-sky-700';
                        case 'green': return 'bg-emerald-100 dark:bg-emerald-950/60 border-emerald-300 dark:border-emerald-700';
                        case 'pink': return 'bg-rose-100 dark:bg-rose-950/60 border-rose-300 dark:border-rose-700';
                        case 'purple': return 'bg-purple-100 dark:bg-purple-950/60 border-purple-300 dark:border-purple-700';
                        case 'orange': return 'bg-orange-100 dark:bg-orange-950/60 border-orange-300 dark:border-orange-700';
                        default: return 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-800';
                    }
                },

                handleCreateSubmit(e) {
                    if (!this.title && !this.content && this.checklistItems.filter(i => i.trim()).length === 0) {
                        e.preventDefault();
                        Swal.fire('Empty Note', 'Please enter a title, text note, or checklist item.', 'warning');
                    }
                },

                openEditModal(id, title, content, date, color, isPinned, isChecklist, checklistRaw) {
                    this.editId = id;
                    this.editTitle = title || '';
                    this.editContent = content || '';
                    this.editDate = date || '';
                    this.editColor = color || 'default';
                    this.editPinned = isPinned;
                    this.editIsChecklist = isChecklist;

                    if (isChecklist) {
                        try {
                            let parsed = typeof checklistRaw === 'string' ? JSON.parse(checklistRaw) : checklistRaw;
                            this.editChecklistItems = Array.isArray(parsed) ? parsed : [];
                        } catch(e) {
                            this.editChecklistItems = [];
                        }
                    } else {
                        this.editChecklistItems = [];
                    }

                    this.editModalOpen = true;

                    if (typeof flatpickr !== 'undefined') {
                        this.$nextTick(() => {
                            flatpickr('#edit_date', {
                                dateFormat: 'Y-m-d',
                                altInput: true,
                                altFormat: 'd M Y',
                                allowInput: true,
                                disableMobile: true,
                                defaultDate: date
                            });
                        });
                    }
                },

                confirmDelete(noteId) {
                    Swal.fire({
                        title: 'Delete Note?',
                        text: 'Are you sure you want to delete this note? This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Delete',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            confirmButton: 'inline-flex items-center justify-center rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-rose-700 transition-colors cursor-pointer mr-2',
                            cancelButton: 'inline-flex items-center justify-center rounded-xl bg-gray-200 dark:bg-gray-700 px-5 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-200 shadow-md hover:bg-gray-300 transition-colors cursor-pointer'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('delete-form-' + noteId).submit();
                        }
                    });
                },

                togglePin(id) {
                    fetch('/note-pads/' + id + '/toggle-pin', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        }
                    })
                    .catch(err => console.error(err));
                },

                toggleTaskItem(noteId, idx) {
                    fetch('/note-pads/' + noteId + '/toggle-task', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ index: idx })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Update badge text if present
                            let counter = document.getElementById('task-counter-' + noteId);
                            if (counter) {
                                counter.textContent = data.completed_count + '/' + data.total_count + ' done';
                            }
                        }
                    })
                    .catch(err => console.error(err));
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#create_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    defaultDate: '{{ now()->toDateString() }}'
                });

                flatpickr('#filter_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true
                });
            }
        });
    </script>
@endpush
