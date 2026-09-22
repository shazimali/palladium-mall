@props([
    'name',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select an option',
    'autoSubmit' => false,
    'clearable' => true,
])

{{--
    Reusable searchable dropdown. Renders as a text-input-styled trigger button;
    the option panel is teleported to <body> and positioned with `fixed` (computed
    from the trigger's bounding rect on open) so it always escapes any scrolling /
    overflow-clipped ancestor — needed here because these fields sit inside a
    horizontally-scrolling (overflow-x-auto) filter row.
--}}
<div
    x-data="{
        open: false,
        search: '',
        highlightedIndex: -1,
        value: '{{ addslashes((string) $selected) }}',
        panelStyle: '',
        options: [
            @foreach($options as $opt)
            { value: '{{ addslashes((string) $opt['value']) }}', label: '{{ addslashes((string) $opt['label']) }}' },
            @endforeach
        ],
        normalize(str) {
            return str.toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
        },
        get filteredOptions() {
            if (!this.search) return this.options;
            let s = this.normalize(this.search);
            return this.options.filter(o => this.normalize(o.label).includes(s));
        },
        get selectedLabel() {
            let sel = this.options.find(o => o.value === this.value);
            return sel ? sel.label : '';
        },
        reposition() {
            let rect = this.$refs.trigger.getBoundingClientRect();
            this.panelStyle = 'top:' + Math.round(rect.bottom + 6) + 'px; left:' + Math.round(rect.left) + 'px; width:' + Math.round(rect.width) + 'px;';
        },
        toggle() {
            if (this.open) { this.open = false; return; }
            this.reposition();
            this.open = true;
            this.$nextTick(() => this.$refs.search && this.$refs.search.focus());
        },
        select(opt) {
            this.value = opt.value;
            this.open = false;
            this.search = '';
            this.highlightedIndex = -1;
            @if($autoSubmit)
            this.$nextTick(() => this.$refs.hiddenInput.form.submit());
            @endif
        },
        clear() {
            this.value = '';
            this.open = false;
            this.search = '';
        },
        moveHighlight(dir) {
            let list = this.filteredOptions;
            if (!list.length) return;
            this.highlightedIndex = (this.highlightedIndex + dir + list.length) % list.length;
        },
        selectHighlighted() {
            let list = this.filteredOptions;
            if (this.highlightedIndex >= 0 && this.highlightedIndex < list.length) this.select(list[this.highlightedIndex]);
        },
        onDocumentClick(e) {
            if (!this.open) return;
            let insideTrigger = this.$refs.trigger.contains(e.target);
            let insidePanel = this.$refs.panel && this.$refs.panel.contains(e.target);
            if (!insideTrigger && !insidePanel) this.open = false;
        }
    }"
    x-init="
        document.addEventListener('click', onDocumentClick);
        window.addEventListener('scroll', () => { open = false }, true);
        window.addEventListener('resize', () => { open = false });
    "
    class="relative"
>
    <input type="hidden" name="{{ $name }}" x-ref="hiddenInput" x-bind:value="value">

    <button type="button" x-ref="trigger" @click="toggle()"
        class="w-full flex items-center justify-between rounded-xl border-2 border-gray-300 bg-white px-4 py-2.5 text-base font-bold text-gray-900 text-left focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        <span class="truncate" x-text="value !== '' ? selectedLabel : '{{ addslashes($placeholder) }}'" :class="value !== '' ? '' : 'text-gray-400 dark:text-gray-500 font-semibold'"></span>
        <svg class="h-4 w-4 flex-shrink-0 text-gray-500 transition-transform duration-200 ml-1" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak x-transition x-ref="panel" x-bind:style="panelStyle"
            class="fixed z-[99999] rounded-2xl border-2 border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">
            <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                <input type="text" x-ref="search" x-model="search" placeholder="Search..."
                    @keydown.arrow-down.prevent="moveHighlight(1)"
                    @keydown.arrow-up.prevent="moveHighlight(-1)"
                    @keydown.enter.prevent="selectHighlighted()"
                    @keydown.escape.prevent="open = false"
                    class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div class="max-h-96 overflow-y-auto p-1.5">
                @if($clearable)
                    <button type="button" @click="clear()" class="w-full text-left px-3 py-1.5 text-xs font-semibold text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg">
                        Clear Selection
                    </button>
                @endif
                <template x-for="(opt, index) in filteredOptions" :key="opt.value">
                    <button type="button" @click="select(opt)" @mouseenter="highlightedIndex = index"
                        class="w-full text-left px-3 py-2 text-sm rounded-lg transition-colors truncate"
                        :class="value === opt.value ? 'bg-brand-600 text-white font-black' : (highlightedIndex === index ? 'bg-brand-50 text-brand-900 dark:bg-brand-950/40 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5')"
                        x-text="opt.label">
                    </button>
                </template>
                <div x-show="filteredOptions.length === 0" class="px-3 py-4 text-center text-xs font-semibold text-gray-400">
                    No matches found
                </div>
            </div>
        </div>
    </template>
</div>
