@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Members — {{ $reportType->name }}" />

    <div class="mx-auto w-full max-w-4xl space-y-6" x-data="{
        editModalOpen: false,
        editingMember: { id: null, member_name: '', status: 1, sort_order: 0 },
        openEdit(member) {
            this.editingMember = { ...member };
            this.editModalOpen = true;
        }
    }">
        {{-- Header Card --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-extrabold text-gray-800 dark:text-white/90">👥 Report Members: {{ $reportType->name }}</h2>
                    <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-bold text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                        {{ $reportType->members->count() }} Members
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Assign members (e.g., Guards, Staff, Cleaners) to <strong>{{ $reportType->name }}</strong>. When creating reports, only active members will be available for selection.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('report-types.edit', $reportType) }}"
                   class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    ⚙️ Settings
                </a>
                <a href="{{ route('report-types.remarks', $reportType) }}"
                   class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    🏷️ Remarks
                </a>
                <a href="{{ route('report-types.index') }}"
                   class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                    ← Back
                </a>
            </div>
        </div>

        {{-- Info Alert: Immutable Members --}}
        <div class="rounded-xl border border-blue-200 bg-blue-50/70 p-4 text-xs text-blue-800 dark:border-blue-900/40 dark:bg-blue-950/20 dark:text-blue-300 flex items-start gap-2.5">
            <span class="text-base leading-none">ℹ️</span>
            <div>
                <span class="font-bold">Historical Record Protection:</span> Once created, members cannot be deleted to preserve the integrity of past inspection reports. You can update member names or deactivate them at any time.
            </div>
        </div>

        {{-- Add New Member Form --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-3">➕ Add New Member</h3>
            <form action="{{ route('report-types.members.store', $reportType) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                @csrf
                <div class="sm:col-span-7">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Member / Officer Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="member_name" required
                           placeholder="e.g., Guard John Doe, Officer Alex, Cleaner Sarah..."
                           class="h-11 w-full rounded-lg border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-brand-500 focus:outline-none" />
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select name="status" class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="1" selected>🟢 Active</option>
                        <option value="0">🔴 Inactive</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <button type="submit"
                            class="w-full h-11 inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-4 text-sm font-bold text-white hover:bg-brand-600 shadow-sm transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add
                    </button>
                </div>
            </form>
        </div>

        {{-- Members List Table --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/[0.01] flex items-center justify-between">
                <h3 class="font-extrabold text-gray-800 dark:text-white text-sm">Configured Members</h3>
                <span class="text-xs text-gray-400 font-medium">{{ $reportType->members->count() }} total</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-16">#</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Member Name</th>
                            <th class="px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-32">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-32">Reports Logged</th>
                            <th class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($reportType->members as $i => $member)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                                <td class="px-4 py-3.5 text-xs text-gray-400 font-mono">{{ $i + 1 }}</td>
                                <td class="px-4 py-3.5 font-bold text-gray-800 dark:text-white/90">
                                    {{ $member->member_name }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <button type="button" @click="
                                        fetch('{{ route('report-types.members.toggle-status', [$reportType, $member]) }}', {
                                            method: 'POST',
                                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                                        }).then(() => location.reload());
                                    " class="cursor-pointer">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold border {{ $member->status_badge_class }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $member->status ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                            {{ $member->status_label }}
                                        </span>
                                    </button>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="font-mono text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        {{ $member->reports()->count() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <button type="button"
                                            @click="openEdit({ id: {{ $member->id }}, member_name: '{{ addslashes($member->member_name) }}', status: {{ $member->status ? 1 : 0 }}, sort_order: {{ $member->sort_order }} })"
                                            class="rounded-lg p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                            title="Edit Member">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">
                                    No members configured yet for <strong>{{ $reportType->name }}</strong>. Add your first member above!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Edit Member Modal --}}
        <div x-show="editModalOpen" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs">
            <div @click.outside="editModalOpen = false"
                 class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white">✏️ Edit Member</h3>
                    <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <form :action="'{{ url('report-types/' . $reportType->id . '/members') }}/' + editingMember.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Member Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="member_name" x-model="editingMember.member_name" required
                               class="h-11 w-full rounded-lg border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-brand-500 focus:outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" x-model="editingMember.status"
                                class="h-11 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            <option value="1">🟢 Active</option>
                            <option value="0">🔴 Inactive</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editModalOpen = false"
                                class="px-4 py-2 text-xs font-bold text-gray-600 hover:bg-gray-100 rounded-xl transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-5 py-2 text-xs font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-xl shadow-sm transition-all">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
