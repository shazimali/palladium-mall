@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Post Schedule Heads" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6"
            x-data="{
                modalOpen: false,
                isEditing: false,
                formAction: '{{ route('post-schedule-heads.store') }}',
                formData: {
                    id: null,
                    name: '',
                    color: 'blue',
                    description: '',
                    sort_order: 0,
                    is_active: true
                },
                openCreate() {
                    this.isEditing = false;
                    this.formAction = '{{ route('post-schedule-heads.store') }}';
                    this.formData = { id: null, name: '', color: 'blue', description: '', sort_order: 0, is_active: true };
                    this.modalOpen = true;
                },
                openEdit(head) {
                    this.isEditing = true;
                    this.formAction = '/post-schedule-heads/' + head.id;
                    this.formData = {
                        id: head.id,
                        name: head.name,
                        color: head.color,
                        description: head.description || '',
                        sort_order: head.sort_order || 0,
                        is_active: Boolean(head.is_active)
                    };
                    this.modalOpen = true;
                }
            }">

            {{-- Header Action Bar --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white/90">Post & Duty Categories</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manage dynamic post schedule heads (Security, Janitorial, Maintenance, etc.)</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('post-schedules.index') }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-all">
                        ← Back to Schedule
                    </a>
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('post_schedule_heads.create'))
                        <button @click="openCreate()"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-brand-600 transition-all cursor-pointer">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Post Head
                        </button>
                    @endif
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Head Name</th>
                            <th class="px-4 py-3">Badge Color</th>
                            <th class="px-4 py-3">Assigned Tasks</th>
                            <th class="px-4 py-3">Sort Order</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($heads as $index => $head)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3 text-gray-400">{{ $heads->firstItem() + $index }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900 dark:text-white/90">
                                        {{ $head->name }}
                                    </div>
                                    @if($head->description)
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $head->description }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border {{ $head->badge_class }}">
                                        <span class="h-2 w-2 rounded-full bg-current"></span>
                                        {{ ucfirst($head->color) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200">
                                        {{ $head->schedules_count }} duties
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono font-semibold">{{ $head->sort_order }}</td>
                                <td class="px-4 py-3">
                                    @if($head->is_active)
                                        <span class="inline-flex items-center rounded-lg px-2 py-0.5 text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-lg px-2 py-0.5 text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('post_schedule_heads.edit'))
                                            <button @click="openEdit({{ json_encode($head) }})"
                                                class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                                title="Edit">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                        @endif

                                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('post_schedule_heads.delete'))
                                            <form action="{{ route('post-schedule-heads.destroy', $head) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this Post Head?');"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="rounded-lg p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                                    title="Delete">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400 font-semibold">
                                    No post schedule heads configured yet. Click "Add Post Head" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($heads->hasPages())
                <div class="mt-4">
                    {{ $heads->links() }}
                </div>
            @endif

            {{-- Create/Edit Modal --}}
            <div x-show="modalOpen" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
                <div @click.outside="modalOpen = false"
                    class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white" x-text="isEditing ? 'Edit Post Schedule Head' : 'New Post Schedule Head'"></h3>
                        <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-500 text-xl font-bold">✕</button>
                    </div>

                    <form :action="formAction" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <template x-if="isEditing">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Head Name *</label>
                            <input type="text" name="name" x-model="formData.name" required placeholder="e.g. Security Post, Janitorial Post"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Badge Color *</label>
                            <select name="color" x-model="formData.color" required
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                <option value="blue">Blue</option>
                                <option value="emerald">Emerald / Green</option>
                                <option value="amber">Amber / Yellow</option>
                                <option value="purple">Purple</option>
                                <option value="rose">Rose / Red</option>
                                <option value="indigo">Indigo</option>
                                <option value="cyan">Cyan</option>
                                <option value="gray">Gray</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Description (Optional)</label>
                            <textarea name="description" x-model="formData.description" rows="2" placeholder="Brief details of this post/head..."
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Sort Order</label>
                                <input type="number" name="sort_order" x-model="formData.sort_order" min="0"
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3.5 py-2 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                            </div>

                            <div class="flex items-center pt-6">
                                <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    <input type="checkbox" name="is_active" value="1" x-model="formData.is_active"
                                        class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                    Active
                                </label>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <button type="button" @click="modalOpen = false"
                                class="px-4 py-2 text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-all">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-5 py-2 text-xs font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-xl shadow-xs transition-all">
                                Save Post Head
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
