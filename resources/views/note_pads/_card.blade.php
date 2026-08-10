@php
    $colors = $note->color_classes;
@endphp

<div x-data="{ checkedItems: @js($note->is_checklist ? array_column($note->checklist_items, 'completed') : []) }"
    style="{{ $note->bg_style }}"
    class="group relative flex flex-col justify-between rounded-2xl border p-4 shadow-xs transition-all hover:shadow-md dark:shadow-none {{ $colors['card'] }}">

    <div>
        {{-- Card Top Row: Title, Date Badge & Pin Toggle --}}
        <div class="flex items-start justify-between gap-2 mb-2">
            <div class="flex-1">
                @if($note->title)
                    <h4 class="font-black text-base leading-snug {{ $colors['header'] }}">
                        {{ $note->title }}
                    </h4>
                @endif
                @if($note->date)
                    <span class="inline-block mt-1 text-[11px] font-bold px-2 py-0.5 rounded-lg {{ $colors['badge'] }}">
                        📅 {{ $note->date->format('M d, Y') }}
                    </span>
                @endif
            </div>

            <button type="button" @click="togglePin({{ $note->id }})"
                class="opacity-60 group-hover:opacity-100 p-1 text-base transition-opacity rounded-lg hover:bg-black/5 dark:hover:bg-white/5"
                title="{{ $note->is_pinned ? 'Unpin Note' : 'Pin Note' }}">
                <span>{{ $note->is_pinned ? '📌' : '📍' }}</span>
            </button>
        </div>

        {{-- Card Body: Checklist vs Text Note --}}
        <div class="my-3 text-sm">
            @if($note->is_checklist)
                <div class="space-y-1.5">
                    @foreach($note->checklist_items as $index => $item)
                        <label class="flex items-center gap-2 cursor-pointer text-xs sm:text-sm font-semibold select-none group/item">
                            <input type="checkbox"
                                x-model="checkedItems[{{ $index }}]"
                                @change="toggleTaskItem({{ $note->id }}, {{ $index }})"
                                class="h-4 w-4 rounded-md border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                            <span :class="checkedItems[{{ $index }}] ? 'line-through opacity-50' : 'text-gray-900 dark:text-white'">
                                {{ $item['text'] }}
                            </span>
                        </label>
                    @endforeach
                </div>
            @else
                <p class="whitespace-pre-line text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 leading-relaxed">
                    {{ $note->content }}
                </p>
            @endif
        </div>
    </div>

    {{-- Card Footer: Progress Badge & Actions --}}
    <div class="mt-3 pt-2 flex items-center justify-between border-t border-black/5 dark:border-white/5 text-xs">
        @if($note->is_checklist)
            <span id="task-counter-{{ $note->id }}" class="font-bold text-[11px] text-gray-500 dark:text-gray-400">
                {{ $note->completed_tasks_count }}/{{ $note->total_tasks_count }} done
            </span>
        @else
            <span></span>
        @endif

        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
            <button type="button"
                @click="openEditModal({{ $note->id }}, @js($note->title), @js($note->content), @js($note->date ? $note->date->format('Y-m-d') : ''), '{{ $note->color }}', {{ $note->is_pinned ? 'true' : 'false' }}, {{ $note->is_checklist ? 'true' : 'false' }}, @js($note->content))"
                class="p-1 rounded-lg hover:bg-black/10 dark:hover:bg-white/10 transition-all text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1 cursor-pointer">
                ✏️ Edit
            </button>

            <button type="button" @click="confirmDelete({{ $note->id }})"
                class="p-1 rounded-lg hover:bg-red-100 dark:hover:bg-red-950/40 text-xs font-bold text-red-600 dark:text-red-400 transition-all cursor-pointer" title="Delete Note">
                🗑️
            </button>

            <form id="delete-form-{{ $note->id }}" action="{{ route('note-pads.destroy', $note->id) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

</div>
