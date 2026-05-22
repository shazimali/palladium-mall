@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Users Management" />

    @if(session('success'))
        <div class="mb-4 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700 dark:border-green-800/30 dark:bg-green-500/10 dark:text-green-500">
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700 dark:border-red-800/30 dark:bg-red-500/10 dark:text-red-500">
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Filters & Search -->
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <form action="{{ route('users.index') }}" method="GET"
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center">
                <!-- Search Input -->
                <div class="relative flex-1 max-w-md">
                    <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search by name or email..."
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <!-- Role Filter -->
                <div class="relative">
                    <select name="role" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'role']))
                    <a href="{{ route('users.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                        Clear Filters
                    </a>
                @endif
                <button type="submit"
                    class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900 dark:bg-white/10 dark:hover:bg-white/20">
                    Apply Filter
                </button>

                @can('users.create')
                    <a href="{{ route('users.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                        Create User
                    </a>
                @endcan
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[800px] table-auto">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">User Details</p>
                        </th>
                        <th class="px-5 py-3 text-left sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Email</p>
                        </th>
                        <th class="px-5 py-3 text-left sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Roles</p>
                        </th>
                        <th class="px-5 py-3 text-left sm:px-6">
                            <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p>
                        </th>
                        @canany(['users.edit', 'users.delete'])
                            <th class="px-5 py-3 text-right sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                            </th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800">
                            <!-- User Details -->
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 overflow-hidden rounded-full bg-brand-500 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="block font-semibold text-gray-800 text-theme-sm dark:text-white/90">{{ $user->name }}</span>
                                        <span class="block text-gray-500 text-theme-xs dark:text-gray-400">ID: #{{ $user->id }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Email -->
                            <td class="px-5 py-4 sm:px-6">
                                <span class="text-theme-sm text-gray-700 dark:text-white/90">{{ $user->email }}</span>
                            </td>

                            <!-- Roles -->
                            <td class="px-5 py-4 sm:px-6">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $role)
                                        <span class="text-[10px] uppercase font-bold rounded-full px-2 py-0.5 {{ $role->name === 'super-admin' ? 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-500' : ($role->name === 'administrator' ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400' : 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300') }}">
                                            {{ $role->display_name }}
                                        </span>
                                    @empty
                                        <span class="text-[10px] text-gray-400 italic">No role assigned</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4 sm:px-6">
                                @can('users.edit')
                                    <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                            class="text-theme-xs inline-block rounded-full px-2.5 py-0.5 font-semibold transition cursor-pointer
                                                {{ $user->is_active ? 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-500 hover:bg-green-100' : 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-500 hover:bg-red-100' }}
                                                {{ $user->id === auth()->id() ? 'opacity-50 cursor-not-allowed' : '' }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-theme-xs inline-block rounded-full px-2.5 py-0.5 font-semibold
                                        {{ $user->is_active ? 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-500' : 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-500' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                @endcan
                            </td>

                            <!-- Actions -->
                            @canany(['users.edit', 'users.delete'])
                                <td class="px-5 py-4 text-right sm:px-6">
                                    <div class="flex justify-end gap-2">
                                        @can('users.edit')
                                            <a href="{{ route('users.edit', $user) }}"
                                                class="text-gray-500 hover:text-brand-500 dark:text-gray-400 dark:hover:text-brand-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </a>
                                        @endcan

                                        @can('users.delete')
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('users.destroy', $user) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this user?')" class="inline">
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
                            <td colspan="5" class="px-5 py-6 text-center text-gray-500 sm:px-6">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection