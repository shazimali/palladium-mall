@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Permissions Management" />

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 dark:border-green-800/30 dark:bg-green-500/10 dark:text-green-500">
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="mb-6 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Permissions List</h3>
        <a href="{{ route('admin.permissions.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create Permission
        </a>
    </div>

    <div class="space-y-6">
        @foreach($permissions as $group => $groupPermissions)
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 bg-gray-50/50 px-5 py-4 dark:border-gray-800 dark:bg-white/[0.02]">
                    <h4 class="font-semibold text-gray-800 dark:text-white/90 text-sm uppercase tracking-wider">{{ $group }}</h4>
                </div>
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <table class="w-full min-w-[600px] table-auto">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Display Name</p>
                                </th>
                                <th class="px-5 py-3 text-left sm:px-6">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">System Name (Slug)</p>
                                </th>
                                <th class="px-5 py-3 text-right sm:px-6">
                                    <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupPermissions as $permission)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                                    <td class="px-5 py-4 sm:px-6">
                                        <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $permission->display_name }}</span>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <code class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $permission->name }}</code>
                                    </td>
                                    <td class="px-5 py-4 text-right sm:px-6">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.permissions.edit', $permission) }}" class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this permission?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endsection
