@forelse($tasks as $index => $task)
    {{-- ── Main Task Row ── --}}
    <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors align-top" id="task-row-{{ $task->id }}">

        <td class="px-3 py-3 text-gray-400 text-xs font-mono">{{ $index + 1 }}</td>

        {{-- Category / Task --}}
        <td class="px-4 py-3 max-w-xs">
            @if($task->category)
                <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                    {{ $task->category->name }}
                </span>
            @else
                <span class="text-gray-400 text-xs italic">{{ $task->title }}</span>
            @endif
            @if($task->description)
                <p class="mt-1 text-xs text-gray-400 whitespace-pre-line leading-relaxed">{{ $task->description }}</p>
            @endif
        </td>

        {{-- Assigned To --}}
        <td class="px-4 py-3">
            <div class="flex flex-wrap gap-1">
                @foreach($task->assignees as $assignee)
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $assignee->name }}
                    </span>
                @endforeach
            </div>
        </td>

        {{-- Priority --}}
        <td class="px-3 py-3 text-center">
            @php
                $priorityMap = [
                    'low'    => ['label' => '⚪ Low',    'class' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'],
                    'medium' => ['label' => '🔵 Medium', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'],
                    'high'   => ['label' => '🟠 High',   'class' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300'],
                    'urgent' => ['label' => '🔴 Urgent', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'],
                ];
                $p = $priorityMap[$task->priority] ?? $priorityMap['medium'];
            @endphp
            <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold {{ $p['class'] }}">
                {{ $p['label'] }}
            </span>
        </td>

        {{-- Status (Display Only — updated exclusively in Edit section) --}}
        <td class="px-4 py-3 text-center">
            @php
                $statusMap = [
                    'todo'        => ['label' => '📌 To Do',      'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
                    'in_progress' => ['label' => '⚡ In Progress', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'],
                    'completed'   => ['label' => '✅ Completed',  'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'],
                ];
                $s = $statusMap[$task->status] ?? $statusMap['todo'];
            @endphp

            <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold {{ $s['class'] }}">
                {{ $s['label'] }}
            </span>

            {{-- Completion Time Indicator: Green if on/before time, Red if late --}}
            @if($task->status === 'completed' && $task->completed_at)
                @php
                    $isOnTime = $task->due_at ? $task->completed_at->lte($task->due_at) : true;
                @endphp
                <div class="mt-1.5 inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-extrabold border {{ $isOnTime ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60' : 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-800/60' }}">
                    <svg class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        @if($isOnTime)
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        @else
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        @endif
                    </svg>
                    <span>
                        {{ $task->completed_at->format('d M, h:i A') }}
                        ({{ $isOnTime ? 'On Time' : 'Late' }})
                    </span>
                </div>
            @endif
        </td>

        {{-- Creator Remarks --}}
        <td class="px-4 py-3 max-w-xs">
            @if($task->creator_rating === 'good' || $task->creator_rating === 'satisfactory')
                <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-100 px-2 py-0.5 text-[11px] font-extrabold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                    ✨ Satisfactory
                </span>
            @elseif($task->creator_rating === 'bad' || $task->creator_rating === 'unsatisfactory')
                <span class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-2 py-0.5 text-[11px] font-extrabold text-red-700 dark:bg-red-900/30 dark:text-red-300">
                    ⚠️ Unsatisfactory
                </span>
            @endif

            @if($task->creator_remarks)
                <p class="{{ $task->creator_rating ? 'mt-1' : '' }} text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                    {{ $task->creator_remarks }}
                </p>
            @elseif(!$task->creator_rating && !$task->admin_photo)
                <span class="text-gray-300 dark:text-gray-700 text-xs italic">—</span>
            @endif

            @if($task->admin_photo && $task->admin_photo_url)
                <div class="mt-1.5">
                    <a href="{{ $task->admin_photo_url }}" target="_blank" class="inline-flex items-center gap-1.5 p-1 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-brand-400 group transition-all" title="View attached photo">
                        <img src="{{ $task->admin_photo_url }}" alt="Photo" class="h-8 w-8 rounded-md object-cover">
                        <span class="text-[10px] font-bold text-gray-600 dark:text-gray-400 group-hover:text-brand-600 dark:group-hover:text-brand-400">📷 View Photo</span>
                    </a>
                </div>
            @endif
        </td>

        {{-- Assignee Remarks --}}
        <td class="px-4 py-3 max-w-xs">
            @if($task->assignee_remarks)
                <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $task->assignee_remarks }}</p>
            @else
                <span class="text-gray-300 dark:text-gray-700 text-xs italic">—</span>
            @endif
        </td>

        {{-- Due Date --}}
        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
            @if($task->due_at)
                <div class="font-semibold text-gray-700 dark:text-gray-300">{{ $task->due_at->format('d M Y') }}</div>
                <div class="text-gray-400">{{ $task->due_at->format('h:i A') }}</div>
            @else
                <span class="text-gray-400">—</span>
            @endif
        </td>

        {{-- Actions --}}
        <td class="px-3 py-3 text-right">
            <div class="flex items-center justify-end gap-1">
                @if(auth()->user()->hasPermission('tasks.edit') || auth()->user()->isSuperAdmin())
                    <button type="button" @click="openEditModal({{ $task->id }})"
                        class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50 transition-colors"
                        title="Edit Task / Remarks / Status">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </button>
                @endif

                @if(auth()->user()->hasPermission('tasks.delete') || auth()->user()->isSuperAdmin())
                    <button type="button" onclick="deleteTask({{ $task->id }})"
                        class="inline-flex items-center rounded-lg p-1.5 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                        title="Delete">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                @endif
            </div>
        </td>
    </tr>

@empty
    <tr id="tasks-empty-row">
        <td colspan="9" class="px-4 py-14 text-center">
            <svg class="mx-auto mb-3 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-sm font-semibold text-gray-400">No tasks for {{ !empty($date) ? \Carbon\Carbon::parse($date)->format('d M Y') : 'the selected filter' }}</p>
        </td>
    </tr>
@endforelse
