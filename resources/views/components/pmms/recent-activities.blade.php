@props(['activities' => collect()])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
        <div>
            <h3 class="text-base font-bold text-gray-800 dark:text-white/90">Recent User Activity</h3>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Latest actions performed by logged-in users</p>
        </div>
        @if(auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('activity_logs.view')))
            <a href="{{ route('activity-logs.index') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                All Activity Logs →
            </a>
        @endif
    </div>

    {{-- Content timeline --}}
    <div class="p-6">
        <div class="flow-root">
            <ul role="list" class="-mb-8">
                @forelse($activities as $activity)
                    <li>
                        <div class="relative pb-8">
                            @if (!$loop->last)
                                <span class="absolute left-5 top-5 -ml-px h-full w-0.5 bg-gray-150 dark:bg-gray-800" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    @php
                                        $actionColor = match($activity->action) {
                                            'created' => 'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400',
                                            'updated' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                                            'deleted' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                                            'login' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400',
                                            'logout' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400',
                                            default => 'bg-gray-50 text-gray-700 dark:bg-white/5 dark:text-gray-400'
                                        };
                                    @endphp
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full font-bold text-sm {{ $actionColor }}">
                                        {{ $activity->user ? strtoupper(substr($activity->user->name, 0, 1)) : 'S' }}
                                    </span>
                                </div>
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-sm text-gray-750 dark:text-gray-300">
                                            {{ $activity->description }}
                                        </p>
                                        <span class="text-xs text-gray-400 dark:text-gray-500">
                                            via IP: {{ $activity->ip_address ?? 'Local' }}
                                        </span>
                                    </div>
                                    <div class="whitespace-nowrap text-right text-xs text-gray-450 dark:text-gray-500">
                                        <time datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->diffForHumans() }}</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @empty
                    <div class="text-center py-6 text-gray-400 dark:text-gray-600">
                        <span class="text-2xl mb-1 block">📋</span>
                        <p class="text-sm">No recent activities recorded yet.</p>
                    </div>
                @endforelse
            </ul>
        </div>
    </div>
</div>
