@extends('layouts.app')

@section('content')
    @php $isEdit = isset($employee); @endphp
    <x-common.page-breadcrumb pageTitle="{{ $isEdit ? 'Edit Employee' : 'Register Employee' }}" />

    <x-common.component-card
        title="{{ $isEdit ? 'Edit Employee Profile' : 'Register New Employee' }}"
        desc="{{ $isEdit ? 'Update salary and profile details for ' . $employee->name : 'Link an existing system user as an employee and set up their salary configuration' }}">

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <p class="text-sm font-semibold text-red-700 dark:text-red-400 mb-2">Please fix the following errors:</p>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li class="text-sm text-red-600 dark:text-red-400">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ $isEdit ? route('employees.update', $employee) : route('employees.store') }}" method="POST" class="space-y-6">
            @csrf
            @if($isEdit) @method('PUT') @endif

            {{-- User Selection (only on create) --}}
            @if(!$isEdit)
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">
                        System User <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id" required
                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                        <option value="">— Select a user to register as employee —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Only users not already registered as employees are shown.</p>
                </div>
            @else
                <div class="rounded-xl border border-brand-200 bg-brand-50 p-4 dark:border-brand-800 dark:bg-brand-900/20">
                    <p class="text-xs text-brand-600 dark:text-brand-400 font-semibold uppercase tracking-wider mb-0.5">Employee</p>
                    <p class="text-base font-bold text-gray-900 dark:text-white">{{ $employee->name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $employee->email }}</p>
                </div>
            @endif

            {{-- Profile Details --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">Employee Code</label>
                    <input type="text" name="employee_code" value="{{ old('employee_code', $profile->employee_code ?? '') }}"
                        placeholder="e.g. EMP-001"
                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">Designation</label>
                    <input type="text" name="designation" value="{{ old('designation', $profile->designation ?? '') }}"
                        placeholder="e.g. Office Manager"
                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">Department</label>
                    <input type="text" name="department" value="{{ old('department', $profile->department ?? '') }}"
                        placeholder="e.g. Operations"
                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">Joining Date</label>
                    <input type="text" id="joined_at" name="joined_at"
                        value="{{ old('joined_at', isset($profile) && $profile->joined_at ? $profile->joined_at->format('Y-m-d') : '') }}"
                        placeholder="Select joining date"
                        class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 cursor-pointer" readonly>
                </div>
            </div>

            {{-- Salary Setup --}}
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="h-4 w-4 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    Salary Configuration
                </h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">
                            Basic Salary (Rs.) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="basic_salary" step="0.01" min="0"
                            value="{{ old('basic_salary', $profile->basic_salary ?? 0) }}" required
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">Fuel Allowance (Rs.)</label>
                        <input type="number" name="fuel_allowance" step="0.01" min="0"
                            value="{{ old('fuel_allowance', $profile->fuel_allowance ?? 0) }}"
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">Attendance Incentive (Rs.)</label>
                        <input type="number" name="attendance_incentive" step="0.01" min="0"
                            value="{{ old('attendance_incentive', $profile->attendance_incentive ?? 0) }}"
                            class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                        <p class="text-[11px] text-gray-400 mt-1">Paid if 100% attendance</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-1.5">Collection Incentive (%)</label>
                        <div class="relative">
                            <input type="number" name="collection_incentive_pct" step="0.01" min="0" max="100"
                                value="{{ old('collection_incentive_pct', $profile->collection_incentive_pct ?? 0) }}"
                                class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 pr-10 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">%</span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">% of basic × performance %</p>
                    </div>
                </div>
            </div>

            {{-- Active Status --}}
            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                        @checked(old('is_active', $profile->is_active ?? true))>
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-brand-500 dark:bg-gray-700 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Employee</span>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                <a href="{{ route('employees.index') }}"
                   class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="rounded-xl bg-brand-500 px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-600 transition-colors shadow-sm cursor-pointer">
                    {{ $isEdit ? 'Update Employee' : 'Register Employee' }}
                </button>
            </div>
        </form>

    </x-common.component-card>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#joined_at', {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                allowInput: false,
                disableMobile: true,
                @if(isset($profile) && $profile->joined_at)
                defaultDate: '{{ $profile->joined_at->format('Y-m-d') }}',
                @elseif(old('joined_at'))
                defaultDate: '{{ old('joined_at') }}',
                @endif
            });
        }
    });
</script>
@endpush
