@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Inspection Heads" />

    <div class="mx-auto w-full space-y-4">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div>
                <h2 class="text-lg font-extrabold text-gray-800 dark:text-white/90">Inspection Heads</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manage checklist items for Flat Inspection and Cleaning reports.</p>
            </div>
            @can('inspection_heads.create')
                <a href="{{ route('inspection-heads.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-600 transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Inspection Head
                </a>
            @endcan
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('inspection-heads.index') }}"
              class="flex flex-wrap items-end gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Type</label>
                <select name="type" class="h-9 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All Types</option>
                    <option value="flat_inspection" @selected(request('type') === 'flat_inspection')>🏠 Flat Inspection</option>
                    <option value="cleaning" @selected(request('type') === 'cleaning')>🧹 Cleaning</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name..."
                       class="h-9 w-48 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            </div>
            <button type="submit" class="h-9 rounded-lg bg-brand-500 px-4 text-sm font-bold text-white hover:bg-brand-600">Filter</button>
            @if(request()->hasAny(['type', 'search']))
                <a href="{{ route('inspection-heads.index') }}" class="h-9 flex items-center rounded-lg border border-gray-300 px-4 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">Clear</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Key</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Order</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($heads as $head)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3 text-gray-400 text-xs">{{ $heads->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90">{{ $head->name }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-400 dark:text-gray-500">{{ $head->key }}</td>
                                <td class="px-4 py-3">
                                    @if($head->type === 'flat_inspection')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">🏠 Flat Inspection</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 text-xs font-bold text-green-700 dark:bg-green-900/30 dark:text-green-300">🧹 Cleaning</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $head->sort_order }}</td>
                                <td class="px-4 py-3">
                                    @can('inspection_heads.edit')
                                        <button
                                            onclick="toggleStatus({{ $head->id }}, this)"
                                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold transition-colors
                                                {{ $head->is_active
                                                    ? 'bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-300'
                                                    : 'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400' }}">
                                            <span class="status-dot w-1.5 h-1.5 rounded-full inline-block {{ $head->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                            <span class="status-label">{{ $head->is_active ? 'Active' : 'Inactive' }}</span>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold {{ $head->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                                            {{ $head->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    @endcan
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('inspection_heads.edit')
                                            <a href="{{ route('inspection-heads.edit', $head) }}"
                                               class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors">
                                                ✏️ Edit
                                            </a>
                                        @endcan
                                        @can('inspection_heads.delete')
                                            <form action="{{ route('inspection-heads.destroy', $head) }}" method="POST"
                                                  onsubmit="return confirm('Delete this inspection head?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-100 dark:border-red-800/30 dark:bg-red-900/20 dark:text-red-400 transition-colors">
                                                    🗑 Delete
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                    No inspection heads found. <a href="{{ route('inspection-heads.create') }}" class="text-brand-500 font-semibold hover:underline">Create one</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($heads->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800">
                    {{ $heads->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
    function toggleStatus(id, btn) {
        fetch(`/inspection-heads/${id}/toggle-status`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(r => r.json())
        .then(data => {
            const label = btn.querySelector('.status-label');
            const dot   = btn.querySelector('.status-dot');
            if (data.is_active) {
                label.textContent = 'Active';
                btn.className = btn.className.replace('bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400', 'bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-300');
                dot.className = dot.className.replace('bg-red-500', 'bg-green-500');
            } else {
                label.textContent = 'Inactive';
                btn.className = btn.className.replace('bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-300', 'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400');
                dot.className = dot.className.replace('bg-green-500', 'bg-red-500');
            }
        });
    }
    </script>
    @endpush
@endsection
