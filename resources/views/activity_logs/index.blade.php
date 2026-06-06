@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="User Activity Logs" />

    {{-- Filters & Search --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <form action="{{ route('activity-logs.index') }}" method="GET" class="flex flex-col gap-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Search Input --}}
                <div class="relative">
                    <label class="mb-1.5 block text-xs font-semibold uppercase text-gray-400 dark:text-gray-500">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search IP, description, agent..."
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                {{-- User Filter --}}
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase text-gray-400 dark:text-gray-500">User</label>
                    <select name="user_id" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Filter --}}
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase text-gray-400 dark:text-gray-500">Action Type</label>
                    <select name="action" onchange="this.form.submit()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Date range --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase text-gray-400 dark:text-gray-500">From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase text-gray-400 dark:text-gray-500">To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                @if(request()->anyFilled(['search', 'user_id', 'action', 'date_from', 'date_to']))
                    <a href="{{ route('activity-logs.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                        Clear Filters
                    </a>
                @endif
                <button type="submit"
                    class="rounded-lg bg-gray-800 px-5 py-2 text-sm font-medium text-white hover:bg-gray-900 dark:bg-white/10 dark:hover:bg-white/20">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    {{-- Activity Logs Table --}}
    <div x-data="{ isOpen: false, activeLog: null, getPropertyPairs(props) {
        if (!props) return [];
        let keys = [];
        if (props.new) keys = Object.keys(props.new);
        if (props.old) keys = [...new Set([...keys, ...Object.keys(props.old)])];
        return keys.map(k => {
            let oldVal = props.old && props.old[k] !== undefined ? props.old[k] : '—';
            let newVal = props.new && props.new[k] !== undefined ? props.new[k] : '—';
            if (typeof oldVal === 'boolean') oldVal = oldVal ? 'true' : 'false';
            if (typeof newVal === 'boolean') newVal = newVal ? 'true' : 'false';
            if (typeof oldVal === 'object' && oldVal !== null) oldVal = JSON.stringify(oldVal);
            if (typeof newVal === 'object' && newVal !== null) newVal = JSON.stringify(newVal);
            return { key: k, oldVal: oldVal === null ? 'null' : oldVal, newVal: newVal === null ? 'null' : newVal };
        });
    }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="max-w-full overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[900px] table-auto">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/10">
                        <th class="px-5 py-3.5 text-left sm:px-6 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Timestamp</th>
                        <th class="px-5 py-3.5 text-left sm:px-6 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">User</th>
                        <th class="px-5 py-3.5 text-left sm:px-6 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Action</th>
                        <th class="px-5 py-3.5 text-left sm:px-6 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Description</th>
                        <th class="px-5 py-3.5 text-left sm:px-6 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">IP Address</th>
                        <th class="px-5 py-3.5 text-right sm:px-6 text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                            {{-- Timestamp --}}
                            <td class="px-5 py-4 sm:px-6 text-sm text-gray-600 dark:text-gray-300">
                                <span class="font-medium text-gray-800 dark:text-white">
                                    {{ $log->created_at->format('d M Y, h:i A') }}
                                </span>
                                <span class="block text-xs text-gray-400 dark:text-gray-500">
                                    {{ $log->created_at->diffForHumans() }}
                                </span>
                            </td>

                            {{-- User --}}
                            <td class="px-5 py-4 sm:px-6">
                                @if($log->user)
                                    <span class="block font-semibold text-gray-800 text-sm dark:text-white/90">{{ $log->user->name }}</span>
                                    <span class="block text-xs text-gray-400 dark:text-gray-500">{{ $log->user->email }}</span>
                                @else
                                    <span class="text-sm italic text-gray-400 dark:text-gray-500">System / Anonymous</span>
                                @endif
                            </td>

                            {{-- Action Badge --}}
                            <td class="px-5 py-4 sm:px-6">
                                @php
                                    $actionBadges = [
                                        'created'      => 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-500 border border-green-200/50 dark:border-green-500/20',
                                        'updated'      => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400 border border-blue-200/50 dark:border-blue-500/20',
                                        'deleted'      => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-500 border border-red-200/50 dark:border-red-500/20',
                                        'login'        => 'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400 border border-purple-200/50 dark:border-purple-500/20',
                                        'logout'       => 'bg-orange-50 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400 border border-orange-200/50 dark:border-orange-500/20',
                                        'export_excel' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-500/20',
                                        'export_pdf'   => 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-400 border border-rose-200/50 dark:border-rose-500/20',
                                    ];
                                    $badgeClass = $actionBadges[$log->action] ?? 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300 border border-gray-200/50 dark:border-white/10';
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider {{ $badgeClass }}">
                                    {{ str_replace('_', ' ', $log->action) }}
                                </span>
                            </td>

                            {{-- Description --}}
                            <td class="px-5 py-4 sm:px-6 text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate">
                                {{ $log->description }}
                            </td>

                            {{-- IP / Device --}}
                            <td class="px-5 py-4 sm:px-6 text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-mono text-xs">{{ $log->ip_address ?? '—' }}</span>
                                @if($log->user_agent)
                                    <span class="block text-[10px] text-gray-400 truncate max-w-xs mt-0.5" title="{{ $log->user_agent }}">
                                        {{ Str::limit($log->user_agent, 40) }}
                                    </span>
                                @endif
                            </td>

                            {{-- Details Button --}}
                            <td class="px-5 py-4 text-right sm:px-6">
                                <button type="button" 
                                    @click="activeLog = {{ json_encode($log->load('user')) }}; isOpen = true;"
                                    class="text-xs font-semibold text-brand-500 hover:text-brand-600 dark:hover:text-brand-400 inline-flex items-center gap-1.5 cursor-pointer">
                                    View Details &rarr;
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400 sm:px-6">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="text-3xl mb-2">📋</span>
                                    <p class="text-sm font-medium">No activity logs found matching the filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="border-t border-gray-150 p-4 dark:border-gray-800">
                {{ $logs->links() }}
            </div>
        @endif

        {{-- Slide-over Drawer/Modal Container --}}
        <div x-show="isOpen" 
            class="fixed inset-0 z-99999 overflow-hidden" 
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
            style="display: none;">
            <div class="absolute inset-0 overflow-hidden">
                {{-- Backdrop --}}
                <div x-show="isOpen" 
                    x-transition:enter="ease-in-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in-out duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="isOpen = false"
                    class="absolute inset-0 bg-gray-900/60 transition-opacity backdrop-blur-xs"></div>

                {{-- Drawer panel --}}
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="isOpen" 
                        x-transition:enter="transform transition ease-in-out duration-300"
                        x-transition:enter-start="translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in-out duration-300"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="translate-x-full"
                        class="pointer-events-auto w-screen max-w-xl">
                        <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl dark:bg-gray-900">
                            {{-- Header --}}
                            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                                <div class="flex items-start justify-between">
                                    <h2 class="text-base font-semibold leading-6 text-gray-800 dark:text-white" id="modal-title">
                                        Log Details #<span x-text="activeLog ? activeLog.id : ''"></span>
                                    </h2>
                                    <div class="ml-3 flex h-7 items-center">
                                        <button type="button" @click="isOpen = false" 
                                            class="relative rounded-md text-gray-400 hover:text-gray-500 focus:outline-hidden cursor-pointer">
                                            <span class="sr-only">Close panel</span>
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Content Body --}}
                            <div class="relative flex-1 px-6 py-6 space-y-6">
                                {{-- Core metadata summary --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.02]">
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">Action Type</p>
                                        <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-white uppercase tracking-wider" x-text="activeLog ? activeLog.action.replace('_', ' ') : ''"></p>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.02]">
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">Performed By</p>
                                        <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-white" x-text="activeLog && activeLog.user ? activeLog.user.name : 'System / Anonymous'"></p>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.02]">
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">IP Address</p>
                                        <p class="mt-0.5 text-sm font-mono text-gray-800 dark:text-white" x-text="activeLog ? activeLog.ip_address || '—' : ''"></p>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.02]">
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">Logged At</p>
                                        <p class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-white" x-text="activeLog ? new Date(activeLog.created_at).toLocaleString() : ''"></p>
                                    </div>
                                </div>

                                {{-- Description panel --}}
                                <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.02] border border-gray-150 dark:border-gray-800">
                                    <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">Description</p>
                                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white" x-text="activeLog ? activeLog.description : ''"></p>
                                </div>

                                {{-- User Agent --}}
                                <div x-show="activeLog && activeLog.user_agent" class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.02]">
                                    <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold uppercase">User Agent</p>
                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 font-mono break-all" x-text="activeLog ? activeLog.user_agent : ''"></p>
                                </div>

                                {{-- Changes properties details --}}
                                <div x-show="activeLog && activeLog.properties" class="space-y-3">
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-100 pb-1.5 dark:border-gray-800">Changed Attributes</h3>
                                    
                                    <div class="overflow-hidden border border-gray-200 dark:border-gray-800 rounded-lg">
                                        <table class="w-full text-left text-xs table-fixed">
                                            <thead class="bg-gray-50 dark:bg-gray-800/80 text-gray-500 dark:text-gray-400 uppercase font-semibold">
                                                <tr>
                                                    <th class="px-4 py-2 w-1/3">Field</th>
                                                    <template x-if="activeLog && activeLog.properties && activeLog.properties.old">
                                                        <th class="px-4 py-2 w-1/3">Old Value</th>
                                                    </template>
                                                    <template x-if="activeLog && activeLog.properties && activeLog.properties.new">
                                                        <th class="px-4 py-2 w-1/3">New Value</th>
                                                    </template>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-mono text-gray-700 dark:text-gray-300">
                                                <template x-for="item in getPropertyPairs(activeLog ? activeLog.properties : null)">
                                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                                        <td class="px-4 py-2.5 font-bold text-gray-800 dark:text-white break-all" x-text="item.key"></td>
                                                        <template x-if="activeLog && activeLog.properties && activeLog.properties.old">
                                                            <td class="px-4 py-2.5 text-red-600 dark:text-red-400 break-all" x-text="item.oldVal"></td>
                                                        </template>
                                                        <template x-if="activeLog && activeLog.properties && activeLog.properties.new">
                                                            <td class="px-4 py-2.5 text-green-600 dark:text-green-400 break-all" x-text="item.newVal"></td>
                                                        </template>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
