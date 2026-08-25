{{-- ─── Manage Categories Slide-Over Panel ─── --}}
<div x-show="panelOpen" class="fixed inset-0 z-50 flex" style="display: none;">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeCategories()"></div>

    {{-- Panel --}}
    <div class="relative ml-auto h-full w-full max-w-md bg-white dark:bg-gray-900 shadow-2xl flex flex-col"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full">

        {{-- Panel Header --}}
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-800 px-5 py-4">
            <div>
                <h2 class="text-base font-extrabold text-gray-900 dark:text-white">Task Categories</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Add categories and toggle active/inactive status.</p>
            </div>
            <button type="button" @click="closeCategories()"
                class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Add New Category --}}
        <div class="border-b border-gray-200 dark:border-gray-800 px-5 py-4">
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Add New Category</label>
            <div class="flex gap-2">
                <input
                    type="text"
                    x-model="newCategoryName"
                    placeholder="e.g. C.C.T.V. Report"
                    @keydown.enter.prevent="saveCategory()"
                    class="flex-1 h-10 rounded-xl border border-gray-300 bg-gray-50 px-3 text-sm text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:outline-none"
                />
                <button type="button" @click="saveCategory()"
                    :disabled="saving || !newCategoryName.trim()"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-brand-600 px-4 py-2 text-xs font-extrabold text-white hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    <span x-text="saving ? 'Saving…' : 'Add'"></span>
                </button>
            </div>
        </div>

        {{-- Categories List --}}
        <div class="flex-1 overflow-y-auto px-5 py-3">
            <template x-if="categories.length === 0">
                <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                    <svg class="h-10 w-10 mb-3 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <p class="text-sm font-medium">No categories yet.</p>
                    <p class="text-xs mt-1">Add your first category above.</p>
                </div>
            </template>

            <div class="space-y-2">
                <template x-for="cat in categories" :key="cat.id">
                    <div class="flex items-center justify-between gap-3 rounded-xl border px-4 py-3 transition-colors"
                         :class="cat.is_active
                            ? 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800'
                            : 'border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/60'">

                        {{-- Name + Badge --}}
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="h-2 w-2 rounded-full flex-shrink-0 transition-colors"
                                  :class="cat.is_active ? 'bg-emerald-400' : 'bg-gray-300'"></span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate" x-text="cat.name"></span>
                        </div>

                        {{-- Toggle Button --}}
                        <button type="button"
                            @click="toggleCategory(cat)"
                            class="flex-shrink-0 inline-flex items-center rounded-lg px-3 py-1 text-xs font-bold transition-colors"
                            :class="cat.is_active
                                ? 'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40'
                                : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40'">
                            <span x-text="cat.is_active ? 'Set Inactive' : 'Set Active'"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Panel Footer --}}
        <div class="border-t border-gray-200 dark:border-gray-800 px-5 py-3">
            <p class="text-xs text-gray-400 dark:text-gray-500">
                💡 Inactive categories won't appear in the task create/edit dropdown.
            </p>
        </div>
    </div>
</div>
