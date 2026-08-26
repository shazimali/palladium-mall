@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Create User" />

    <div class="mx-auto w-full max-w-[750px]">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-4 mb-6">
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Create New User</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Set up login credentials, roles, and optional employee profile.</p>
                </div>
                <a href="{{ route('users.index') }}" class="text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                    ← Back to Users
                </a>
            </div>

            <form action="{{ route('users.store') }}" method="POST"
                x-data="{ 
                    isEmployee: {{ old('is_employee') ? 'true' : 'false' }},
                    showPassword: false,
                    showConfirmPassword: false,
                    password: '',
                    passwordConfirmation: ''
                }">
                @csrf

                {{-- Basic User Information --}}
                <div class="space-y-4 mb-6">
                    <div>
                        <label for="name" class="mb-1.5 block text-xs font-bold text-gray-700 dark:text-gray-300">
                            Full Name *
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g., Muhammad Ali"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('name') border-red-500 @enderror" required />
                        @error('name')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-1.5 block text-xs font-bold text-gray-700 dark:text-gray-300">
                            Email Address *
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="e.g., user@palladium.com"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('email') border-red-500 @enderror" required />
                        @error('email')
                            <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password and Re-type Password --}}
                    <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-900/30">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="mb-1.5 block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Password *
                                </label>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" 
                                        name="password" 
                                        id="password" 
                                        x-model="password"
                                        placeholder="••••••••"
                                        autocomplete="new-password"
                                        required
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('password') border-red-500 @enderror" />
                                    <button type="button" 
                                        @click="showPassword = !showPassword"
                                        class="absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer p-1 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none"
                                        tabindex="-1"
                                        aria-label="Toggle password visibility">
                                        <svg x-show="!showPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619ZM10.0002 4.04297C6.48191 4.04297 3.49489 6.30917 2.4155 9.4593C2.3615 9.61687 2.3615 9.78794 2.41549 9.94552C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C13.5184 15.3619 16.5055 13.0957 17.5849 9.94555C17.6389 9.78797 17.6389 9.6169 17.5849 9.45932C16.5055 6.30919 13.5184 4.04297 10.0002 4.04297ZM9.99151 7.84413C8.96527 7.84413 8.13333 8.67606 8.13333 9.70231C8.13333 10.7286 8.96527 11.5605 9.99151 11.5605H10.0064C11.0326 11.5605 11.8646 10.7286 11.8646 9.70231C11.8646 8.67606 11.0326 7.84413 10.0064 7.84413H9.99151Z" />
                                        </svg>
                                        <svg x-show="showPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229C16.716 16.13 16.716 15.6551 16.4231 15.3622L4.63803 3.57709ZM12.3608 13.4212L10.4475 11.5079C10.3061 11.5423 10.1584 11.5606 10.0002 11.5606H9.99151C8.96527 11.5606 8.13333 10.7286 8.13333 9.70237C8.13333 9.5461 8.15262 9.39434 8.18895 9.24933L5.91885 6.97923C5.03505 7.69015 4.34057 8.62704 3.92328 9.70247C4.86803 12.1373 7.23361 13.8619 10.0002 13.8619C10.8326 13.8619 11.6287 13.7058 12.3608 13.4212ZM16.0771 9.70249C15.7843 10.4569 15.3552 11.1432 14.8199 11.7311L15.8813 12.7925C16.6329 11.9813 17.2187 11.0143 17.5849 9.94561C17.6389 9.78803 17.6389 9.61696 17.5849 9.45938C16.5055 6.30925 13.5184 4.04303 10.0002 4.04303C9.13525 4.04303 8.30244 4.17999 7.52218 4.43338L8.75139 5.66259C9.1556 5.58413 9.57311 5.54303 10.0002 5.54303C12.7667 5.54303 15.1323 7.26768 16.0771 9.70249Z" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="mb-1.5 block text-xs font-bold text-gray-700 dark:text-gray-300">
                                    Re-type Password *
                                </label>
                                <div class="relative">
                                    <input :type="showConfirmPassword ? 'text' : 'password'" 
                                        name="password_confirmation" 
                                        id="password_confirmation" 
                                        x-model="passwordConfirmation"
                                        placeholder="••••••••"
                                        autocomplete="new-password"
                                        required
                                        :class="password && passwordConfirmation ? (password === passwordConfirmation ? 'border-green-500 dark:border-green-500 focus:border-green-500 focus:ring-green-500/20' : 'border-red-400 dark:border-red-500 focus:border-red-500 focus:ring-red-500/20') : ''"
                                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('password_confirmation') border-red-500 @enderror" />
                                    <button type="button" 
                                        @click="showConfirmPassword = !showConfirmPassword"
                                        class="absolute top-1/2 right-3 -translate-y-1/2 cursor-pointer p-1 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 focus:outline-none"
                                        tabindex="-1"
                                        aria-label="Toggle confirm password visibility">
                                        <svg x-show="!showConfirmPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M10.0002 13.8619C7.23361 13.8619 4.86803 12.1372 3.92328 9.70241C4.86804 7.26761 7.23361 5.54297 10.0002 5.54297C12.7667 5.54297 15.1323 7.26762 16.0771 9.70243C15.1323 12.1372 12.7667 13.8619 10.0002 13.8619ZM10.0002 4.04297C6.48191 4.04297 3.49489 6.30917 2.4155 9.4593C2.3615 9.61687 2.3615 9.78794 2.41549 9.94552C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C13.5184 15.3619 16.5055 13.0957 17.5849 9.94555C17.6389 9.78797 17.6389 9.6169 17.5849 9.45932C16.5055 6.30919 13.5184 4.04297 10.0002 4.04297ZM9.99151 7.84413C8.96527 7.84413 8.13333 8.67606 8.13333 9.70231C8.13333 10.7286 8.96527 11.5605 9.99151 11.5605H10.0064C11.0326 11.5605 11.8646 10.7286 11.8646 9.70231C11.8646 8.67606 11.0326 7.84413 10.0002 7.84413H9.99151Z" />
                                        </svg>
                                        <svg x-show="showConfirmPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M4.63803 3.57709C4.34513 3.2842 3.87026 3.2842 3.57737 3.57709C3.28447 3.86999 3.28447 4.34486 3.57737 4.63775L4.85323 5.91362C3.74609 6.84199 2.89363 8.06395 2.4155 9.45936C2.3615 9.61694 2.3615 9.78801 2.41549 9.94558C3.49488 13.0957 6.48191 15.3619 10.0002 15.3619C11.255 15.3619 12.4422 15.0737 13.4994 14.5598L15.3625 16.4229C15.6554 16.7158 16.1302 16.7158 16.4231 16.4229C16.716 16.13 16.716 15.6551 16.4231 15.3622L4.63803 3.57709ZM12.3608 13.4212L10.4475 11.5079C10.3061 11.5423 10.1584 11.5606 10.0002 11.5606H9.99151C8.96527 11.5606 8.13333 10.7286 8.13333 9.70237C8.13333 9.5461 8.15262 9.39434 8.18895 9.24933L5.91885 6.97923C5.03505 7.69015 4.34057 8.62704 3.92328 9.70247C4.86803 12.1373 7.23361 13.8619 10.0002 13.8619C10.8326 13.8619 11.6287 13.7058 12.3608 13.4212ZM16.0771 9.70249C15.7843 10.4569 15.3552 11.1432 14.8199 11.7311L15.8813 12.7925C16.6329 11.9813 17.2187 11.0143 17.5849 9.94561C17.6389 9.78803 17.6389 9.61696 17.5849 9.45938C16.5055 6.30925 13.5184 4.04303 10.0002 4.04303C9.13525 4.04303 8.30244 4.17999 7.52218 4.43338L8.75139 5.66259C9.1556 5.58413 9.57311 5.54303 10.0002 5.54303C12.7667 5.54303 15.1323 7.26768 16.0771 9.70249Z" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="mt-1">
                                    <template x-if="password && !passwordConfirmation">
                                        <span class="text-[11px] text-amber-500 font-medium">Please re-type to confirm password.</span>
                                    </template>
                                    <template x-if="password && passwordConfirmation && password !== passwordConfirmation">
                                        <span class="text-[11px] text-red-500 font-semibold">⚠️ Passwords do not match.</span>
                                    </template>
                                    <template x-if="password && passwordConfirmation && password === passwordConfirmation">
                                        <span class="text-[11px] text-green-600 dark:text-green-400 font-semibold">✓ Passwords match.</span>
                                    </template>
                                </div>
                                @error('password_confirmation')
                                    <p class="mt-1 text-xs text-red-500 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900" />
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Account is Active</span>
                        </label>
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════════════ --}}
                {{-- EMPLOYEE TOGGLE & ACCORDION --}}
                {{-- ══════════════════════════════════════════════════════ --}}
                <div class="mb-6 rounded-2xl border-2 transition-all p-5"
                    :class="isEmployee ? 'border-indigo-300 bg-indigo-50/40 dark:border-indigo-800 dark:bg-indigo-950/20' : 'border-gray-200 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-900/40'">
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 text-xl font-bold shadow-2xs">
                                👔
                            </span>
                            <div>
                                <label for="is_employee" class="text-sm font-extrabold text-gray-900 dark:text-white cursor-pointer">
                                    Work as Employee
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Enable if this user is a staff member who participates in daily activities, tasks & performance.
                                </p>
                            </div>
                        </div>

                        {{-- Toggle switch --}}
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" name="is_employee" value="1" id="is_employee"
                                x-model="isEmployee"
                                class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-gray-300 peer-checked:bg-indigo-600 peer-focus:ring-2 peer-focus:ring-indigo-500/20 dark:bg-gray-700 after:absolute after:top-0.5 after:left-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full"></div>
                        </label>
                    </div>

                    {{-- Employee Profile Fields (Revealed if isEmployee) --}}
                    <div x-show="isEmployee" x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="mt-5 pt-5 border-t border-indigo-200 dark:border-indigo-800 space-y-4">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Employee Code
                                </label>
                                <input type="text" name="employee_code" value="{{ old('employee_code') }}" placeholder="e.g. EMP-001"
                                    class="w-full h-10 px-3 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono">
                                @error('employee_code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Designation
                                </label>
                                <input type="text" name="designation" value="{{ old('designation') }}" placeholder="e.g. Operations Officer"
                                    class="w-full h-10 px-3 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                @error('designation') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Department
                                </label>
                                <input type="text" name="department" value="{{ old('department') }}" placeholder="e.g. Maintenance / Security"
                                    class="w-full h-10 px-3 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                @error('department') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Date of Joining
                                </label>
                                <input type="date" name="joined_at" value="{{ old('joined_at', today()->toDateString()) }}"
                                    class="w-full h-10 px-3 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono">
                                @error('joined_at') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Basic Salary (Rs.)
                                </label>
                                <input type="number" step="0.01" min="0" name="basic_salary" value="{{ old('basic_salary', '0') }}" placeholder="0.00"
                                    class="w-full h-10 px-3 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono font-bold">
                                @error('basic_salary') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Fuel Allowance (Rs.)
                                </label>
                                <input type="number" step="0.01" min="0" name="fuel_allowance" value="{{ old('fuel_allowance', '0') }}"
                                    class="w-full h-10 px-3 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Attendance Incentive (Rs.)
                                </label>
                                <input type="number" step="0.01" min="0" name="attendance_incentive" value="{{ old('attendance_incentive', '0') }}"
                                    class="w-full h-10 px-3 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                    Collection Incentive (%)
                                </label>
                                <input type="number" step="0.01" min="0" max="100" name="collection_incentive_pct" value="{{ old('collection_incentive_pct', '0') }}"
                                    class="w-full h-10 px-3 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-mono">
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Roles --}}
                <div class="mb-6">
                    <h4 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-100 pb-2 dark:border-gray-800">
                        Assign Roles
                    </h4>
                    @error('roles')
                        <p class="mb-3 text-xs text-red-500 font-semibold">{{ $message }}</p>
                    @enderror

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($roles as $role)
                            <label class="flex items-start gap-3 rounded-xl border border-gray-200 p-3 hover:bg-gray-50/70 cursor-pointer dark:border-gray-800 dark:hover:bg-white/[0.02] transition-colors">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                    {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                                    class="mt-1 h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900" />
                                <div>
                                    <span class="block text-xs text-gray-900 dark:text-white font-extrabold">{{ $role->display_name }}</span>
                                    <span class="block text-[11px] text-gray-400 mt-0.5">{{ $role->description ?? 'No description.' }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ route('users.index') }}" class="rounded-xl border border-gray-300 px-5 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-xl bg-brand-600 px-6 py-2.5 text-xs font-extrabold text-white hover:bg-brand-700 shadow-md transition-colors cursor-pointer">
                        💾 Create User Account
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
