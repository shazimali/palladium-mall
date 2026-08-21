@php
    $priorityBadges = [
        'urgent' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 border-red-200 dark:border-red-800',
        'high'   => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300 border-orange-200 dark:border-orange-800',
        'medium' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200 dark:border-blue-800',
        'low'    => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700',
    ];

    $isOverdue = $task->due_at && $task->due_at->isPast() && $task->status !== 'completed';
@endphp

<div
    data-task-id="{{ $task->id }}"
    data-task-json="{{ json_encode($task->load(['creator', 'assignees', 'comments.user'])) }}"
    @click="openTaskDrawer({{ json_encode($task->load(['creator', 'assignees', 'comments.user'])) }})"
    class="task-card group cursor-pointer bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200/80 dark:border-gray-800 shadow-xs hover:shadow-md transition-all space-y-3 relative hover:border-brand-300 dark:hover:border-brand-800"
>
    <!-- Card Header: Title & Priority -->
    <div class="flex items-start justify-between gap-2">
        <h3 class="font-bold text-sm text-gray-900 dark:text-white leading-snug group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
            {{ $task->title }}
        </h3>
        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md border flex-shrink-0 {{ $priorityBadges[$task->priority] ?? 'bg-gray-100' }}">
            {{ $task->priority }}
        </span>
    </div>

    <!-- Description excerpt -->
    @if($task->description)
        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-normal">
            {{ $task->description }}
        </p>
    @endif

    <!-- Assignees & Assigner Badges -->
    <div class="flex items-center justify-between pt-1 text-[11px] border-t border-gray-100 dark:border-gray-800">
        <div class="flex items-center gap-1 flex-wrap">
            <span class="text-gray-400 text-[10px]">Assigned to:</span>
            @foreach($task->assignees as $assignee)
                <span class="px-2 py-0.5 bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 font-semibold rounded-md text-[10px]">
                    {{ $assignee->name }}
                </span>
            @endforeach
        </div>

        <div class="flex items-center gap-1 text-gray-400 text-[10px]">
            <span>💬 {{ count($task->comments) }}</span>
        </div>
    </div>

    <!-- Prominent Date & Time Section -->
    <div class="space-y-1 pt-2 border-t border-dashed border-gray-100 dark:border-gray-800 text-[11px]">
        <!-- Created Time -->
        <div class="flex items-center justify-between text-gray-400 text-[10px]">
            <span>Created:</span>
            <span class="font-medium text-gray-600 dark:text-gray-300">{{ $task->created_at?->format('M d, Y h:i A') ?? '—' }}</span>
        </div>

        <!-- Due Time -->
        @if($task->due_at)
            <div class="flex items-center justify-between text-[10px] {{ $isOverdue ? 'text-red-600 dark:text-red-400 font-bold' : 'text-gray-500 dark:text-gray-400' }}">
                <span>Due:</span>
                <span class="font-semibold flex items-center gap-1">
                    @if($isOverdue) ⚠️ @endif
                    {{ $task->due_at?->format('M d, Y h:i A') ?? '—' }}
                </span>
            </div>
        @endif

        <!-- Completed Time -->
        @if($task->completed_at)
            <div class="flex items-center justify-between text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">
                <span>Completed:</span>
                <span>{{ $task->completed_at?->format('M d, Y h:i A') ?? '—' }}</span>
            </div>
        @endif
    </div>
</div>
