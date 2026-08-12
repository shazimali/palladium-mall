@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Create Role" />

    <div class="mx-auto w-full">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Role Details</h3>

            <form action="{{ route('roles.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-4">
                        <div>
                            <label for="display_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Display Name
                            </label>
                            <input type="text" name="display_name" id="display_name" value="{{ old('display_name') }}" placeholder="e.g., Finance Manager"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('display_name') border-red-500 @enderror" required />
                            @error('display_name')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                System Name (Slug)
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g., finance-manager"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('name') border-red-500 @enderror" required />
                            @error('name')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="5" placeholder="Enter description for this role..."
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div x-data="{
                    selected: {{ json_encode(array_map('intval', old('permissions', []))) }},
                    selectedGroups: {{ json_encode(array_map('intval', old('permission_groups', []))) }},
                    toggleAll(checked) {
                        if (checked) {
                            this.selected = {{ json_encode($majorGroups->flatMap->permissions->pluck('id')->map(fn($id) => (int)$id)->values()) }};
                            this.selectedGroups = {{ json_encode($majorGroups->pluck('id')->map(fn($id) => (int)$id)->values()) }};
                        } else {
                            this.selected = [];
                            this.selectedGroups = [];
                        }
                    },
                    toggleMajorGroup(groupId, permissionIds, checked) {
                        const ids = permissionIds.map(id => Number(id));
                        const gId = Number(groupId);
                        if (checked) {
                            this.selected = Array.from(new Set([...this.selected, ...ids]));
                            if (!this.selectedGroups.includes(gId)) this.selectedGroups.push(gId);
                        } else {
                            this.selected = this.selected.filter(id => !ids.includes(id));
                            this.selectedGroups = this.selectedGroups.filter(id => id !== gId);
                        }
                    },
                    isMajorGroupAllChecked(permissionIds) {
                        const ids = permissionIds.map(id => Number(id));
                        return ids.length > 0 && ids.every(id => this.selected.includes(id));
                    },
                    toggleSubGroup(permissionIds, checked) {
                        const ids = permissionIds.map(id => Number(id));
                        if (checked) {
                            this.selected = Array.from(new Set([...this.selected, ...ids]));
                        } else {
                            this.selected = this.selected.filter(id => !ids.includes(id));
                        }
                    },
                    isSubGroupAllChecked(permissionIds) {
                        const ids = permissionIds.map(id => Number(id));
                        return ids.length > 0 && ids.every(id => this.selected.includes(id));
                    },
                    isAllChecked() {
                        const allIds = {{ json_encode($majorGroups->flatMap->permissions->pluck('id')->map(fn($id) => (int)$id)->values()) }};
                        return allIds.length > 0 && allIds.every(id => this.selected.includes(id));
                    }
                }" class="mb-6">
                    <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4 dark:border-gray-800">
                        <div>
                            <h4 class="text-base font-extrabold text-gray-800 dark:text-white/90">Assign Permissions & Major Groups</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Select a Major Permission Group to grant all underlying module permissions at once, or configure individual module settings.</p>
                        </div>
                        
                        <label class="inline-flex items-center gap-2 cursor-pointer bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 px-4 py-2.5 rounded-xl text-xs font-black text-gray-700 dark:text-gray-200 transition-colors shadow-xs">
                            <input type="checkbox" 
                                :checked="isAllChecked()" 
                                @change="toggleAll($event.target.checked)"
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 cursor-pointer" />
                            <span>Select All Permissions</span>
                        </label>
                    </div>

                    @error('permissions')
                        <p class="mb-4 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    {{-- Render Each Major Permission Group (Admin & Finance) --}}
                    <div class="space-y-8">
                        @foreach($majorGroups as $majorGroup)
                            @php
                                $allMajorGroupPermissionIds = $majorGroup->permissions->pluck('id')->map(fn($id) => (int)$id)->toArray();
                                $subGroups = $majorGroup->permissions->groupBy('group');
                            @endphp

                            <div class="rounded-2xl border-2 border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02] shadow-sm">
                                {{-- Major Group Header --}}
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-200 dark:border-gray-800 pb-4 mb-5">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 font-extrabold flex items-center justify-center text-lg">
                                            {{ $majorGroup->name === 'admin' ? '🏛️' : '💰' }}
                                        </div>
                                        <div>
                                            <h4 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                                                {{ $majorGroup->display_name }}
                                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                                                    {{ $majorGroup->permissions->count() }} Permissions
                                                </span>
                                            </h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $majorGroup->description }}</p>
                                        </div>
                                    </div>

                                    {{-- Major Group Master Checkbox Switch --}}
                                    <label class="inline-flex items-center gap-2 cursor-pointer bg-brand-50 dark:bg-brand-950/40 border border-brand-200 dark:border-brand-800 px-4 py-2 rounded-xl text-xs font-black text-brand-700 dark:text-brand-300 hover:bg-brand-100 transition-colors">
                                        <input type="checkbox" name="permission_groups[]" value="{{ $majorGroup->id }}"
                                            :checked="isMajorGroupAllChecked({{ json_encode($allMajorGroupPermissionIds) }})"
                                            @change="toggleMajorGroup({{ $majorGroup->id }}, {{ json_encode($allMajorGroupPermissionIds) }}, $event.target.checked)"
                                            class="h-4 w-4 rounded border-brand-300 text-brand-600 focus:ring-brand-500/30 cursor-pointer" />
                                        <span>Grant All {{ $majorGroup->display_name }}</span>
                                    </label>
                                </div>

                                {{-- Sub-Group Cards Grid --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                    @foreach($subGroups as $subGroupName => $subGroupPermissions)
                                        @php
                                            $subGroupIds = $subGroupPermissions->pluck('id')->map(fn($id) => (int)$id)->toArray();
                                        @endphp

                                        <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                                            <div class="flex items-center justify-between mb-3 border-b border-gray-200 pb-2 dark:border-gray-800">
                                                <h5 class="font-extrabold text-gray-800 dark:text-white/90 text-xs tracking-wider uppercase flex items-center gap-1.5">
                                                    <span>📌</span> {{ $subGroupName }}
                                                </h5>
                                                
                                                <label class="inline-flex items-center gap-1 cursor-pointer text-xs font-bold text-brand-600 dark:text-brand-400 hover:text-brand-700 transition-colors">
                                                    <input type="checkbox"
                                                        :checked="isSubGroupAllChecked({{ json_encode($subGroupIds) }})"
                                                        @change="toggleSubGroup({{ json_encode($subGroupIds) }}, $event.target.checked)"
                                                        class="h-3.5 w-3.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 cursor-pointer" />
                                                    <span>Select All</span>
                                                </label>
                                            </div>

                                            <div class="space-y-2.5">
                                                @foreach($subGroupPermissions as $permission)
                                                    <label class="flex items-start gap-3 cursor-pointer group">
                                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                                            x-model.number="selected"
                                                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 cursor-pointer" />
                                                        <div>
                                                            <span class="block text-sm text-gray-800 dark:text-white/90 font-medium leading-none group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">{{ $permission->display_name }}</span>
                                                            <span class="block text-xs text-gray-400 mt-0.5">{{ $permission->name }}</span>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('roles.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 font-bold">
                        Create Role
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
