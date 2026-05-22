@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Roles Management" />

    @if(session('success'))
        <div
            class="mb-4 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 dark:border-green-800/30 dark:bg-green-500/10 dark:text-green-500">
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div
            class="mb-4 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 dark:border-red-800/30 dark:bg-red-500/10 dark:text-red-500">
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="mb-6 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Roles List</h3>

        @can('roles.create')
            <a href="{{ route('roles.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Role
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[800px] table-auto">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Display Name</p>
                        </th>
                        <th class="px-5 py-3 text-left sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">System Name (Slug)</p>
                        </th>
                        <th class="px-5 py-3 text-left sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Description</p>
                        </th>
                        <th class="px-5 py-3 text-left sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Permissions Count</p>
                        </th>
                        <th class="px-5 py-3 text-left sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Assigned Users</p>
                        </th>
                        @canany(['roles.edit', 'roles.delete'])
                            <th class="px-5 py-3 text-right sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                            </th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                            <td class="px-5 py-4 sm:px-6">
                                <span
                                    class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $role->display_name }}</span>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <code
                                    class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $role->name }}</code>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <span
                                    class="text-theme-sm text-gray-500 dark:text-gray-400">{{ $role->description ?? 'No description provided.' }}</span>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <span
                                    class="text-theme-xs inline-block rounded-full bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400 px-2.5 py-0.5 font-medium">
                                    @if($role->name === 'super-admin')
                                        All (Override)
                                    @else
                                        {{ $role->permissions_count }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <span
                                    class="text-theme-sm text-gray-700 dark:text-white/90 font-medium">{{ $role->users_count }}</span>
                            </td>

                            @canany(['roles.edit', 'roles.delete'])
                                <td class="px-5 py-4 text-right sm:px-6">
                                    <div class="flex justify-end gap-2">
                                        @can('roles.edit')
                                            <a href="{{ route('roles.edit', $role) }}"
                                                class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </a>
                                        @endcan

                                        @can('roles.delete')
                                            @if($role->name !== 'super-admin')
                                                <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this role?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            @endcanany
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-4 text-center text-gray-500 sm:px-6">No roles defined.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection