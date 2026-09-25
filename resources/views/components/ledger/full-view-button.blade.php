@props(['size' => 'base'])

{{-- Opens the current ledger (same filters) in a new window with only the table: no sidebar, header or filters. --}}
@unless(request()->boolean('full_view'))
    <button type="button"
        onclick="window.open(@js(request()->fullUrlWithQuery(['full_view' => 1, 'paginate' => 0, 'page' => null])), '_blank', 'width=' + screen.availWidth + ',height=' + screen.availHeight + ',scrollbars=yes,resizable=yes')"
        {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 rounded-xl border-2 border-gray-900 px-5 py-2.5 font-extrabold text-gray-900 shadow-md hover:bg-gray-100 dark:border-gray-300 dark:text-gray-100 dark:hover:bg-white/5 transition-colors cursor-pointer ' . ($size === 'sm' ? 'text-sm' : 'text-base')]) }}>
        ⛶ Full View
    </button>
@endunless
