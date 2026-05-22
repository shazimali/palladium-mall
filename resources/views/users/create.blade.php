@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Create User" />

    <div class="mx-auto w-full max-w-[650px]">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90">User Account Details</h3>

            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="space-y-4 mb-6">
                    <div>
                        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Full Name
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g., Jane Doe"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('name') border-red-500 @enderror" required />
                        @error('name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Email Address
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="e.g., jane@example.com"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('email') border-red-500 @enderror" required />
                        @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Password
                        </label>
                        <input type="password" name="password" id="password" placeholder="••••••••"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('password') border-red-500 @enderror" required />
                        @error('password')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900" />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-400">Account is Active</span>
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <h4 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90 border-b border-gray-100 pb-2 dark:border-gray-800">Assign Roles</h4>
                    @error('roles')
                        <p class="mb-4 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($roles as $role)
                            <label class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50/50 cursor-pointer dark:border-gray-800 dark:hover:bg-white/[0.01]">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                    {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                                    class="mt-1 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900" />
                                <div>
                                    <span class="block text-sm text-gray-800 dark:text-white/90 font-medium leading-none">{{ $role->display_name }}</span>
                                    <span class="block text-xs text-gray-400 mt-1">{{ $role->description ?? 'No description.' }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
