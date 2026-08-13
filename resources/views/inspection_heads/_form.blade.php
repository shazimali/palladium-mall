{{-- Shared form fields for create/edit inspection heads --}}
@php $head = $head ?? null; @endphp

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
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Type <span class="text-red-500">*</span></label>
    <select name="type" required
            class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('type') border-red-500 @enderror">
        <option value="">— Select Type —</option>
        <option value="flat_inspection" @selected(old('type', $head?->type) === 'flat_inspection')>🏠 Flat Inspection</option>
        <option value="cleaning" @selected(old('type', $head?->type) === 'cleaning')>🧹 Cleaning</option>
    </select>
    @error('type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
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
