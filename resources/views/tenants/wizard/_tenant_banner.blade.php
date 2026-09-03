{{-- ── Tenant Photo Banner (shown in all wizard steps) ──────────────────── --}}
@if(isset($tenant) && $tenant->id)
<div class="mb-5 flex flex-col sm:flex-row items-center justify-center text-center gap-4 rounded-xl border border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-white/[0.02]">
    @if($tenant->passport_photo)
        <img src="{{ $tenant->passport_photo_url }}"
             class="h-16 w-16 rounded-full object-cover border-2 border-brand-300 shadow-sm flex-shrink-0"
             alt="{{ $tenant->name }}">
    @else
        <div class="h-16 w-16 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center flex-shrink-0 border-2 border-brand-300">
            <span class="text-2xl font-black text-brand-600 dark:text-brand-400">{{ strtoupper(substr($tenant->name ?? '?', 0, 1)) }}</span>
        </div>
    @endif
    <div class="text-center sm:text-left space-y-0.5">
        <p class="text-lg sm:text-xl font-black text-gray-900 dark:text-white">{{ $tenant->name }}</p>
        <p class="text-sm font-bold text-gray-700 dark:text-gray-300">CNIC: {{ $tenant->cnic }}</p>
        @if($tenant->unit)
            <p class="text-sm font-extrabold text-brand-600 dark:text-brand-400 mt-1">
                📍 Flat/Shop: {{ $tenant->unit->unit_number }}
                {{ $tenant->unit->floor ? '— ' . $tenant->unit->floor->name : '' }}
                {{ $tenant->unit->block ? '/ ' . $tenant->unit->block->name : '' }}
            </p>
        @endif
    </div>
</div>
@endif
