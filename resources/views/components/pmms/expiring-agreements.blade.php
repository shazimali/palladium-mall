@props(['agreements' => collect()])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 transition-all hover:shadow-md">

    {{-- Header with amber accent bar --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800"
         style="border-left: 4px solid #f79009;">
        <div>
            <h3 class="text-base font-bold text-gray-800 dark:text-white/90">Expiring Agreements</h3>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Due in next 30 days</p>
        </div>
        <a href="{{ route('agreements.index') }}"
            class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold transition-all"
            style="color: #b54708; background: #fffaeb;"
            onmouseover="this.style.background='#fef0c7'" onmouseout="this.style.background='#fffaeb'">
            View All →
        </a>
    </div>

    <div class="divide-y divide-gray-50 dark:divide-gray-800 px-4 py-2">
        @forelse($agreements as $agreement)
            @php $days = $agreement->daysRemaining(); @endphp
            <div class="flex items-center justify-between py-3 transition-all">
                <div class="flex items-center gap-3">
                    {{-- Amber icon badge --}}
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                         style="background: #fffaeb;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f79009" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800 dark:text-white/90 leading-tight">
                            {{ $agreement->tenant?->name ?? '—' }}
                        </p>
                        <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">
                            {{ $agreement->unit?->unit_number ?? '—' }}
                        </span>
                    </div>
                </div>
                <div class="text-right shrink-0 ml-2">
                    <p class="text-sm font-extrabold"
                       style="{{ $days <= 7 ? 'color: #f04438;' : ($days <= 15 ? 'color: #f79009;' : 'color: #b54708;') }}">
                        {{ $days }}d left
                    </p>
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        {{ $agreement->end_date->format('d M Y') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="py-8 text-center">
                <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full" style="background: #fffaeb;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f79009" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-400 dark:text-gray-600">No agreements expiring soon.</p>
            </div>
        @endforelse
    </div>
</div>