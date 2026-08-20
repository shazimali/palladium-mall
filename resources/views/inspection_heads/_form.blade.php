@php
    $head = $head ?? null;
    $reportTypes = $reportTypes ?? \App\Models\ReportType::active()->ordered()->get();
    $selectedTypes = old('types', $head ? $head->types_list : []);
@endphp

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Name <span class="text-red-500">*</span></label>
    <input type="text" name="name" id="insp_head_name" value="{{ old('name', $head?->name) }}" required
           placeholder="e.g., Floor Condition"
           class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('name') border-red-500 @enderror" />
    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Key (auto-generated if blank)</label>
    <input type="text" name="key" id="insp_head_key" value="{{ old('key', $head?->key) }}"
           placeholder="e.g., floor_condition"
           class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm font-mono text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('key') border-red-500 @enderror" />
    @error('key') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
</div>

<div>
    <div class="flex items-center justify-between mb-1.5">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-400">
            Report Types <span class="text-red-500">*</span>
            <span class="text-xs font-normal text-gray-400 ml-1">(Select one or more)</span>
        </label>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 p-3 rounded-xl border border-gray-200 bg-gray-50/50 dark:border-gray-700 dark:bg-gray-900/50 @error('types') border-red-500 @enderror">
        @foreach($reportTypes as $rt)
            @php
                $isChecked = in_array($rt->key, (array) $selectedTypes);
            @endphp
            <label class="relative flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-all duration-150
                          {{ $isChecked ? 'border-brand-500 bg-brand-50/60 dark:bg-brand-950/30 dark:border-brand-500/80 shadow-xs' : 'border-gray-200 bg-white hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-gray-700' }}">
                <input type="checkbox" name="types[]" value="{{ $rt->key }}" @checked($isChecked)
                       class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 cursor-pointer" />
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-gray-800 dark:text-white/90">{{ $rt->name }}</span>
                    @if($rt->description)
                        <span class="text-[11px] text-gray-400 mt-0.5 line-clamp-1">{{ $rt->description }}</span>
                    @endif
                </div>
            </label>
        @endforeach
    </div>
    @error('types') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    @error('types.*') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
</div>

<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sort Order</label>
    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $head?->sort_order ?? 0) }}"
           class="h-11 w-32 rounded-lg border border-gray-300 px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    <p class="mt-1 text-xs text-gray-400">Lower number = appears first in the list.</p>
</div>

<div class="flex items-center gap-3">
    <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $head?->is_active ?? true))
           class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 cursor-pointer" />
    <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-400 cursor-pointer">Active (shown in inspection forms)</label>
</div>

@push('scripts')
<script>
// Auto-slug key from name
document.getElementById('insp_head_name')?.addEventListener('input', function() {
    const keyField = document.getElementById('insp_head_key');
    if (!keyField.dataset.manual) {
        keyField.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    }
});
document.getElementById('insp_head_key')?.addEventListener('input', function() {
    this.dataset.manual = '1';
});
</script>
@endpush
