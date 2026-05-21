@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Role" />

    <div class="mx-auto w-full">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">Role Details</h3>

            <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-4">
                        <div>
                            <label for="display_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Display Name
                            </label>
                            <input type="text" name="display_name" id="display_name" value="{{ old('display_name', $role->display_name) }}" placeholder="e.g., Manager"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('display_name') border-red-500 @enderror" required />
                            @error('display_name')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                System Name (Slug)
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" placeholder="e.g., manager"
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
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('description') border-red-500 @enderror">{{ old('description', $role->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <h4 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-2 dark:border-gray-800">Assign Permissions</h4>
                    @error('permissions')
                        <p class="mb-4 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    @if($role->name === 'super-admin')
                        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-yellow-800 dark:border-yellow-800/30 dark:bg-yellow-500/10 dark:text-yellow-400">
                            <p class="text-sm font-medium">The super-admin role automatically overrides all permission checks in code. Adding or removing checkboxes here has no physical restriction.</p>
                        </div>
                        <div class="mt-4"></div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($permissions as $group => $groupPermissions)
                            <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.01]">
                                <h5 class="mb-3 font-semibold text-gray-800 dark:text-white/90 text-sm tracking-wider uppercase border-b border-gray-200 pb-1 dark:border-gray-800">{{ $group }}</h5>
                                <div class="space-y-2">
                                    @foreach($groupPermissions as $permission)
                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                                {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}
                                                class="mt-1 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900" />
                                            <div>
                                                <span class="block text-sm text-gray-800 dark:text-white/90 font-medium leading-none">{{ $permission->display_name }}</span>
                                                <span class="block text-xs text-gray-400 mt-0.5">{{ $permission->name }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.roles.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
