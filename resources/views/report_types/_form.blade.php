@csrf

<div class="space-y-6">
    {{-- Basic Information --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-300">
                Report Type Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" id="report_type_name"
                   value="{{ old('name', $reportType->name) }}" required
                   placeholder="e.g., Cleaning, Security Inspection, Electrical"
                   class="h-11 w-full rounded-lg border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-brand-500 focus:outline-none @error('name') border-red-500 @enderror" />
            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-300">
                Key / URL Slug
            </label>
            <input type="text" name="key" id="report_type_key"
                   value="{{ old('key', $reportType->key) }}"
                   placeholder="e.g., cleaning, security_inspection"
                   class="h-11 w-full rounded-lg border border-gray-300 px-3.5 font-mono text-xs text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-brand-500 focus:outline-none @error('key') border-red-500 @enderror" />
            <span class="text-[11px] text-gray-400">Used in URLs: /inspection-reports/<strong>key</strong> (auto-generated if left empty)</span>
            @error('key') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-300">Description (optional)</label>
            <input type="text" name="description"
                   value="{{ old('description', $reportType->description) }}"
                   placeholder="Brief note describing what this service inspection report covers..."
                   class="h-11 w-full rounded-lg border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-brand-500 focus:outline-none" />
        </div>
        <div>
            <label class="mb-2 block text-sm font-bold text-gray-700 dark:text-gray-300">Sidebar Display Order</label>
            <input type="number" name="sort_order" min="0"
                   value="{{ old('sort_order', $reportType->sort_order ?? 0) }}"
                   class="h-11 w-full rounded-lg border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-brand-500 focus:outline-none" />
        </div>
    </div>

    {{-- Daily Report Mode Settings Card --}}
    <div x-data="{ isDaily: {{ old('is_daily', $reportType->is_daily ? 'true' : 'false') }} }"
         class="p-5 rounded-xl border border-amber-200 bg-amber-50/40 dark:border-amber-900/40 dark:bg-amber-950/20 space-y-4">
        
        <div class="flex items-center gap-3">
            <input type="checkbox" name="is_daily" id="is_daily_toggle" value="1"
                   x-model="isDaily"
                   @change="$nextTick(() => initFlatpickrTimes())"
                   class="h-5 w-5 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20 cursor-pointer" />
            <div>
                <label for="is_daily_toggle" class="text-sm font-extrabold text-gray-900 dark:text-white cursor-pointer flex items-center gap-1.5">
                    <span>📅 Daily Report Mode</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400">Enforce daily inspections with locked date and everyday time-window restrictions.</p>
            </div>
        </div>

        <div x-show="isDaily" x-transition.duration.150ms class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-3 border-t border-amber-200/60 dark:border-amber-900/40">
            <div>
                <label class="mb-1.5 block text-xs font-bold text-gray-700 dark:text-gray-300">
                    Allowed Start Time Window <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" name="daily_start_time" id="daily_start_time"
                           value="{{ old('daily_start_time', $reportType->daily_start_time ? substr($reportType->daily_start_time, 0, 5) : '09:00') }}"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-white pl-10 pr-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-brand-500 focus:outline-none"
                           placeholder="Select Start Time (e.g. 09:00 AM)" />
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                </div>
                <span class="text-[11px] text-gray-400">Default: 09:00 AM</span>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold text-gray-700 dark:text-gray-300">
                    Allowed End Time Window <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" name="daily_end_time" id="daily_end_time"
                           value="{{ old('daily_end_time', $reportType->daily_end_time ? substr($reportType->daily_end_time, 0, 5) : '20:00') }}"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-white pl-10 pr-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-brand-500 focus:outline-none"
                           placeholder="Select End Time (e.g. 08:00 PM)" />
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                </div>
                <span class="text-[11px] text-gray-400">Default: 08:00 PM (20:00)</span>
            </div>

            <div class="sm:col-span-2">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="one_per_user_daily" value="1"
                           class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20"
                           @checked(old('one_per_user_daily', $reportType->one_per_user_daily ?? true)) />
                    <span class="text-xs font-bold text-gray-800 dark:text-gray-200">
                        Limit to 1 report per logged-in admin user per day
                    </span>
                </label>
            </div>

            <div class="sm:col-span-2 text-xs text-amber-900 dark:text-amber-200 bg-white/80 dark:bg-black/30 p-3 rounded-lg border border-amber-200 dark:border-amber-900/40">
                ℹ️ <strong>Rules when Daily Mode is enabled:</strong>
                <ul class="list-disc list-inside mt-1 space-y-1">
                    <li>Report Date will be <strong>locked (read-only)</strong> to today's date during creation.</li>
                    <li>Submissions are restricted between the specified daily start and end times.</li>
                    <li>If <em>1 report per user daily</em> is checked, users cannot submit duplicate reports for the same date.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Active Status --}}
    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" value="1"
               class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20 cursor-pointer"
               @checked(old('is_active', $reportType->is_active ?? true)) />
        <label for="is_active" class="text-sm font-bold text-gray-700 dark:text-gray-300 cursor-pointer">
            Active in Sidebar & Inspections
        </label>
    </div>

    {{-- Submit / Cancel --}}
    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
        <a href="{{ route('report-types.index') }}"
           class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
            Cancel
        </a>
        <button type="submit"
                class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-bold text-white hover:bg-brand-600 shadow-sm transition-colors">
            💾 {{ $reportType->exists ? 'Update Report Type' : 'Save Report Type' }}
        </button>
    </div>
</div>

@push('scripts')
<script>
function initFlatpickrTimes() {
    if (typeof flatpickr === 'undefined') return;

    const startEl = document.getElementById('daily_start_time');
    const endEl   = document.getElementById('daily_end_time');

    if (startEl) {
        flatpickr(startEl, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: false,
            altInput: true,
            altFormat: "h:i K",
            defaultDate: startEl.value || '09:00'
        });
    }

    if (endEl) {
        flatpickr(endEl, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: false,
            altInput: true,
            altFormat: "h:i K",
            defaultDate: endEl.value || '20:00'
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    initFlatpickrTimes();

    // Auto-generate key slug from name if not manually modified
    const nameInput = document.getElementById('report_type_name');
    const keyInput  = document.getElementById('report_type_key');
    let keyManuallyEdited = {{ $reportType->exists ? 'true' : 'false' }};

    if (keyInput) {
        keyInput.addEventListener('input', () => { keyManuallyEdited = true; });
    }
    if (nameInput && keyInput) {
        nameInput.addEventListener('input', () => {
            if (!keyManuallyEdited) {
                keyInput.value = nameInput.value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
            }
        });
    }
});
</script>
@endpush
