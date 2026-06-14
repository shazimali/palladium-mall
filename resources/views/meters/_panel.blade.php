@php use Illuminate\Support\Facades\Storage; @endphp
{{--
    ═══════════════════════════════════════════════════════════════
    Utility Meters Panel
    Included from: units/_form.blade.php (unit edit page)

    Props:
      $unit           — App\Models\Unit (with meters loaded)
      $existingMeters — Collection keyed by type (electricity/water/gas)
    ═══════════════════════════════════════════════════════════════
--}}

<div
    x-data="metersPanel({{ $unit->id }}, @js($existingMeters->map(fn($m) => [
        'id'               => $m->id,
        'type'             => $m->type,
        'meter_ref_no'     => $m->meter_ref_no,
        'meter_consumer_id'=> $m->meter_consumer_id,
        'is_active'        => $m->is_active,
        'notes'            => $m->notes,
        'image_url'        => $m->meter_image ? Storage::url($m->meter_image) : null,
    ])->values()))"
    class="mt-5 rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]"
>
    {{-- ── Panel Header ─────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-lg dark:bg-brand-900/20">⚡</span>
            <div>
                <p class="text-sm font-semibold text-gray-800 dark:text-white">Utility Meters</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Electricity · Water · Gas connection references</p>
            </div>
        </div>
        <span x-show="saving"
              class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-900/20 dark:text-brand-400 animate-pulse">
            <span>Saving…</span>
        </span>
        <span x-show="!saving && saved"
              x-transition
              class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-600 dark:bg-green-900/20 dark:text-green-400">
            ✓ Saved
        </span>
    </div>

    {{-- ── Global Error ─────────────────────────────────────────────── --}}
    <div x-show="globalError" x-transition
         class="mx-5 mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"
         x-text="globalError">
    </div>

    {{-- ── Meter Cards ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-3">

        <template x-for="meter in meterTypes" :key="meter.type">
            <div
                :class="getMeterData(meter.type) ? 'border-brand-200 dark:border-brand-800/60' : 'border-dashed border-gray-300 dark:border-gray-700'"
                class="relative rounded-xl border bg-gray-50/50 p-4 transition-all dark:bg-white/[0.02]"
            >
                {{-- ── Card Header ──────────────────────────────────── --}}
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg" x-text="meter.icon"></span>
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300" x-text="meter.label"></p>
                    </div>
                    {{-- Status badge --}}
                    <template x-if="getMeterData(meter.type)">
                        <span
                            :class="getMeterData(meter.type).is_active
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                : 'bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-500'"
                            class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide"
                            x-text="getMeterData(meter.type).is_active ? 'Active' : 'Inactive'">
                        </span>
                    </template>
                    <template x-if="!getMeterData(meter.type)">
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-400 dark:bg-gray-800">
                            Not Set
                        </span>
                    </template>
                </div>

                {{-- ── Display State ────────────────────────────────── --}}
                <div x-show="editingType !== meter.type">
                    <template x-if="getMeterData(meter.type)">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400 dark:text-gray-500 w-20">Ref No.</span>
                                <span class="text-sm font-mono font-semibold text-gray-800 dark:text-white"
                                      x-text="getMeterData(meter.type).meter_ref_no || '—'"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400 dark:text-gray-500 w-20">Consumer ID</span>
                                <span class="text-xs text-gray-600 dark:text-gray-300"
                                      x-text="getMeterData(meter.type).meter_consumer_id || '—'"></span>
                            </div>
                            <div x-show="getMeterData(meter.type).notes" class="flex items-start gap-2">
                                <span class="text-xs text-gray-400 dark:text-gray-500 w-20 mt-0.5">Notes</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 italic"
                                      x-text="getMeterData(meter.type).notes"></span>
                            </div>
                            {{-- Meter Image --}}
                            <template x-if="getMeterData(meter.type).image_url">
                                <div class="mt-2">
                                    <a :href="getMeterData(meter.type).image_url" target="_blank"
                                       class="group relative inline-block overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                                        <img :src="getMeterData(meter.type).image_url"
                                             alt="Meter image"
                                             class="h-16 w-full object-cover transition group-hover:opacity-80">
                                        <span class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 text-white text-xs font-medium bg-black/40 transition">View</span>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!getMeterData(meter.type)">
                        <p class="text-xs text-gray-400 dark:text-gray-500 italic">No meter registered yet.</p>
                    </template>

                    {{-- Action Buttons --}}
                    <div class="mt-3 flex items-center gap-2">
                        <button
                            type="button"
                            @click="startEdit(meter.type)"
                            class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600 transition-colors">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                            </svg>
                            <span x-text="getMeterData(meter.type) ? 'Edit' : 'Add Meter'"></span>
                        </button>
                        <template x-if="getMeterData(meter.type)">
                            <button
                                type="button"
                                @click="removeMeter(meter.type)"
                                class="inline-flex items-center gap-1 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Remove
                            </button>
                        </template>
                    </div>
                </div>

                {{-- ── Edit State ───────────────────────────────────── --}}
                <div x-show="editingType === meter.type" x-cloak>
                    <div class="space-y-3">
                        {{-- Meter Ref No --}}
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                                Reference No. <span class="text-red-400">*</span>
                            </label>
                            <input type="text"
                                   x-model="editForm.meter_ref_no"
                                   placeholder="e.g. LESCO-12345"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <p x-show="editErrors.meter_ref_no" class="mt-1 text-xs text-red-500" x-text="editErrors.meter_ref_no"></p>
                        </div>

                        {{-- Consumer ID --}}
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Consumer ID</label>
                            <input type="text"
                                   x-model="editForm.meter_consumer_id"
                                   placeholder="Consumer account number"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>

                        {{-- Active toggle --}}
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="editForm.is_active = !editForm.is_active"
                                :class="editForm.is_active ? 'bg-brand-500' : 'bg-gray-300 dark:bg-gray-600'"
                                class="relative inline-flex h-5 w-10 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none">
                                <span :class="editForm.is_active ? 'translate-x-5' : 'translate-x-0'"
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out scale-90"></span>
                            </button>
                            <span class="text-xs text-gray-600 dark:text-gray-400">
                                <span x-text="editForm.is_active ? 'Active connection' : 'Inactive / disconnected'"></span>
                            </span>
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Notes</label>
                            <textarea x-model="editForm.notes" rows="2" placeholder="Optional notes..."
                                      class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 resize-none"></textarea>
                        </div>

                        {{-- Image upload --}}
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Meter Photo</label>
                            <input type="file"
                                   :id="'meter-image-' + meter.type"
                                   accept="image/*"
                                   @change="handleImageSelect($event)"
                                   class="w-full text-xs text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:text-gray-400 dark:file:bg-brand-900/20 dark:file:text-brand-400">
                            {{-- Image preview --}}
                            <template x-if="editForm.imagePreview">
                                <img :src="editForm.imagePreview" alt="Preview"
                                     class="mt-2 h-16 w-full rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                            </template>
                        </div>

                        {{-- Inline error --}}
                        <p x-show="editErrors._general" class="text-xs text-red-500" x-text="editErrors._general"></p>

                        {{-- Form Actions --}}
                        <div class="flex gap-2 pt-1">
                            <button
                                type="button"
                                @click="saveMeter(meter.type)"
                                :disabled="saving"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-600 disabled:opacity-60 transition-colors">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span x-text="saving ? 'Saving…' : 'Save'"></span>
                            </button>
                            <button
                                type="button"
                                @click="cancelEdit()"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.04] transition-colors">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
function metersPanel(unitId, initialMeters) {
    return {
        unitId,

        // Meter type definitions
        meterTypes: [
            { type: 'electricity', label: 'Electricity', icon: '⚡' },
            { type: 'water',       label: 'Water',       icon: '💧' },
            { type: 'gas',         label: 'Gas',         icon: '🔥' },
        ],

        // State keyed by type
        meters: Object.fromEntries((initialMeters || []).map(m => [m.type, m])),

        // Edit state
        editingType: null,
        editForm: {
            meter_ref_no: '',
            meter_consumer_id: '',
            is_active: true,
            notes: '',
            imageFile: null,
            imagePreview: null,
        },
        editErrors: {},

        // UI feedback
        saving: false,
        saved: false,
        globalError: '',

        // ── Helpers ────────────────────────────────────────────────────────
        getMeterData(type) {
            return this.meters[type] ?? null;
        },

        // ── Start editing a meter ──────────────────────────────────────────
        startEdit(type) {
            const existing = this.getMeterData(type);
            this.editingType = type;
            this.editErrors = {};
            this.editForm = {
                meter_ref_no:      existing?.meter_ref_no      ?? '',
                meter_consumer_id: existing?.meter_consumer_id ?? '',
                is_active:         existing?.is_active         ?? true,
                notes:             existing?.notes             ?? '',
                imageFile:         null,
                imagePreview:      existing?.image_url         ?? null,
            };
        },

        cancelEdit() {
            this.editingType = null;
            this.editErrors  = {};
        },

        // ── Handle image file selection ────────────────────────────────────
        handleImageSelect(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.editForm.imageFile = file;
            const reader = new FileReader();
            reader.onload = e => { this.editForm.imagePreview = e.target.result; };
            reader.readAsDataURL(file);
        },

        // ── Save meter via AJAX ────────────────────────────────────────────
        async saveMeter(type) {
            this.editErrors  = {};
            this.globalError = '';
            this.saving      = true;

            const existing = this.getMeterData(type);
            const formData = new FormData();

            // Append all fields
            formData.append('unit_id',            this.unitId);
            formData.append('type',               type);
            formData.append('meter_ref_no',       this.editForm.meter_ref_no);
            formData.append('meter_consumer_id',  this.editForm.meter_consumer_id ?? '');
            formData.append('is_active',          this.editForm.is_active ? '1' : '0');
            formData.append('notes',              this.editForm.notes ?? '');
            if (this.editForm.imageFile) {
                formData.append('meter_image', this.editForm.imageFile);
            }

            try {
                let url, method;

                if (existing?.id) {
                    // PUT via POST + _method spoofing (FormData can't do PUT natively)
                    url    = `/ajax/meters/${existing.id}`;
                    method = 'POST';
                    formData.append('_method', 'PUT');
                } else {
                    url    = '/ajax/meters';
                    method = 'POST';
                }

                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                const response = await fetch(url, { method, body: formData });
                const json     = await response.json();

                if (response.status === 422) {
                    // Validation errors
                    const errs = json.errors ?? {};
                    this.editErrors = {
                        meter_ref_no: errs.meter_ref_no?.[0],
                        _general:     errs.type?.[0] ?? errs.unit_id?.[0] ?? null,
                    };
                    this.saving = false;
                    return;
                }

                if (!json.success) {
                    this.editErrors = { _general: json.message ?? 'Something went wrong.' };
                    this.saving = false;
                    return;
                }

                // Update local state
                this.meters[type] = {
                    id:                json.meter.id,
                    type:              json.meter.type,
                    meter_ref_no:      json.meter.meter_ref_no,
                    meter_consumer_id: json.meter.meter_consumer_id,
                    is_active:         json.meter.is_active,
                    notes:             json.meter.notes,
                    image_url:         json.image_url ?? null,
                };

                this.editingType = null;
                this.saved       = true;
                setTimeout(() => { this.saved = false; }, 3000);

            } catch (e) {
                this.globalError = 'Network error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        // ── Remove meter ──────────────────────────────────────────────────
        async removeMeter(type) {
            const existing = this.getMeterData(type);
            if (!existing?.id) return;

            if (!confirm(`Remove the ${type} meter (${existing.meter_ref_no})?`)) return;

            this.saving      = true;
            this.globalError = '';

            try {
                const response = await fetch(`/ajax/meters/${existing.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
                        'Accept':        'application/json',
                        'Content-Type':  'application/json',
                    },
                });

                const json = await response.json();

                if (json.success) {
                    delete this.meters[type];
                    this.saved = true;
                    setTimeout(() => { this.saved = false; }, 3000);
                } else {
                    this.globalError = json.message ?? 'Could not remove meter.';
                }
            } catch (e) {
                this.globalError = 'Network error. Please try again.';
            } finally {
                this.saving = false;
            }
        },
    };
}
</script>
@endpush
