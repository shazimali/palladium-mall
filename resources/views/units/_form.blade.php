{{-- ══════════════════════════════════════════════════════════════
     Unit Edit: Billing-Only Form
     Structural fields are managed through the Landlord form.
═══════════════════════════════════════════════════════════════ --}}

{{-- Read-Only Unit Header Banner --}}
<div class="mb-5 flex items-center gap-4 rounded-xl border border-brand-100 bg-brand-50/50 px-5 py-4 dark:border-brand-900/30 dark:bg-brand-900/10">
    <div class="flex-shrink-0 text-2xl">🏢</div>
    <div class="flex-1 min-w-0">
        <p class="text-xs font-semibold uppercase tracking-wide text-brand-400 mb-0.5">Unit Overview (Read-only)</p>
        <p class="text-sm font-bold text-gray-800 dark:text-white">
            {{ $unit->unit_number }}
            <span class="ml-2 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium capitalize text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $unit->type }}</span>
        </p>
        <p class="text-xs text-gray-500 mt-0.5 dark:text-gray-400">
            {{ $unit->floor?->name ?? '—' }} &nbsp;·&nbsp; {{ $unit->block?->name ?? '—' }} &nbsp;·&nbsp; {{ $unit->area?->name ?? '—' }}
        </p>
    </div>
    <div class="flex-shrink-0 text-right">
        <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Owner</p>
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
            @if($unit->landlord)
                <a href="{{ route('landlords.show', $unit->landlord_id) }}" class="text-brand-500 hover:underline">
                    {{ $unit->landlord->name }}
                </a>
            @else
                <span class="text-gray-400">No owner</span>
            @endif
        </p>
        @if($unit->landlord)
            <a href="{{ route('landlords.edit', $unit->landlord_id) }}"
               class="text-xs text-gray-400 hover:text-brand-500 underline dark:text-gray-500">
                Edit structural fields →
            </a>
        @endif
    </div>
</div>

{{-- Utility Meters Section --}}
@include('meters._panel', ['unit' => $unit, 'existingMeters' => $existingMeters])

{{-- Notes --}}
<div class="mt-5 rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Billing Notes
    </label>
    <textarea name="notes" rows="3"
        placeholder="Notes about utility readings, billing remarks, or any special conditions..."
        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">{{ old('notes', $unit->notes) }}</textarea>
    @error('notes')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>