@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-common.page-breadcrumb pageTitle="Add Post Schedule Task" />

        <div class="max-w-4xl mx-auto rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] sm:p-8">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white/90 uppercase tracking-tight">
                        New Post Schedule Duty
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Assign daily tasks and shifts to an employee under a Post Head
                    </p>
                </div>
                <a href="{{ route('post-schedules.index', ['day' => request('day', $defaultDay)]) }}"
                    class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition-all">
                    Cancel
                </a>
            </div>

            <form action="{{ route('post-schedules.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Day of Week --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                            Day of Week *
                        </label>
                        <select name="day_of_week" required
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                            @foreach($days as $k => $d)
                                <option value="{{ $k }}" {{ old('day_of_week', request('day', $defaultDay)) == $k ? 'selected' : '' }}>
                                    {{ $d }}
                                </option>
                            @endforeach
                        </select>
                        @error('day_of_week') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Post Schedule Head --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                            Post Head / Category *
                        </label>
                        <select name="post_schedule_head_id" required
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                            <option value="">Select Post Head...</option>
                            @foreach($heads as $head)
                                <option value="{{ $head->id }}" {{ old('post_schedule_head_id') == $head->id ? 'selected' : '' }}>
                                    {{ $head->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('post_schedule_head_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Assigned Employee Name --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                            Assigned Employee / Staff *
                        </label>
                        <input type="text" name="employee_name" value="{{ old('employee_name') }}" required
                            placeholder="e.g. Muhammad Ali, Tariq Khan"
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                        @error('employee_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Linked System User (Optional) --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                            Linked User Account (Optional)
                        </label>
                        <select name="user_id"
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                            <option value="">None / External Staff</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Location / Post Area --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                            Location / Post Area
                        </label>
                        <input type="text" name="location" value="{{ old('location') }}"
                            placeholder="e.g. Main Entrance Gate 1, Basement B1, Food Court"
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                        @error('location') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Shift Timing (Start & End Time) --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                                Start Time
                            </label>
                            <input type="time" name="start_time" value="{{ old('start_time', '08:00') }}"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                            @error('start_time') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                                End Time
                            </label>
                            <input type="time" name="end_time" value="{{ old('end_time', '16:00') }}"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                            @error('end_time') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Task Title --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                        Task / Duty Title *
                    </label>
                    <input type="text" name="task_title" value="{{ old('task_title') }}" required
                        placeholder="e.g. Morning Entrance Security & Metal Detector Check"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                    @error('task_title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Detailed Duties / Checklist --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                        Specific Tasks & Duties (Instructions / Checklist)
                    </label>
                    <textarea name="duties" rows="4"
                        placeholder="Detail the tasks to perform on this day (e.g. 1. Inspect emergency exits, 2. Check visitor badges, 3. Log incident reports...)"
                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">{{ old('duties') }}</textarea>
                    @error('duties') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Notes & Sort Order --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                            Special Remarks / Notes
                        </label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                            placeholder="e.g. Wear high-visibility jacket, Report to supervisor at 10 AM"
                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                        @error('notes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-6 pt-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">
                                Sort Order
                            </label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                class="w-28 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2.5 text-sm text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                        </div>

                        <div class="pt-5">
                            <label class="flex items-center gap-2 cursor-pointer text-sm font-bold text-gray-800 dark:text-gray-200">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                Active Status
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ route('post-schedules.index', ['day' => request('day', $defaultDay)]) }}"
                        class="px-5 py-2.5 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-all">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 text-xs font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-xl shadow-md transition-all">
                        Save Post Schedule Task
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
