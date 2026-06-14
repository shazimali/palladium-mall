{{-- ── Tenant Photo Banner (shown in all wizard steps) ──────────────────── --}}
@if(isset($tenant) && $tenant->id)
<div class="mb-5 flex items-center gap-4 rounded-xl border border-gray-100 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-white/[0.02]">
    @if($tenant->passport_photo)
        <img src="{{ $tenant->passport_photo_url }}"
             class="h-14 w-14 rounded-full object-cover border-2 border-brand-200 shadow flex-shrink-0"
             alt="{{ $tenant->name }}">
    @else
        <div class="h-14 w-14 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center flex-shrink-0 border-2 border-brand-200">
            <span class="text-xl font-bold text-brand-600 dark:text-brand-400">{{ strtoupper(substr($tenant->name ?? '?', 0, 1)) }}</span>
        </div>
    @endif
    <div>
        <p class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $tenant->name }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400">CNIC: {{ $tenant->cnic }}</p>
        @if($tenant->unit)
            <p class="text-xs text-brand-600 dark:text-brand-400 mt-0.5">
                📍 {{ $tenant->unit->unit_number }}
                {{ $tenant->unit->floor ? '— ' . $tenant->unit->floor->name : '' }}
                {{ $tenant->unit->block ? '/ ' . $tenant->unit->block->name : '' }}
            </p>
        @endif
    </div>
</div>
@endif
