@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Report Types" />

    <div class="mx-auto w-full space-y-4">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div>
                <h2 class="text-lg font-extrabold text-gray-800 dark:text-white/90">Report Types & Schedule Settings</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manage dynamic inspection modules, daily time-windows (09:00 AM - 08:00 PM), and system remarks.</p>
            </div>
            @can('report_types.create')
                <a href="{{ route('report-types.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-brand-600 transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Report Type
                </a>
            @endcan
        </div>

        {{-- Table --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Report Type</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Mode & Schedule</th>
                            <th class="px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">System Remarks</th>
                            <th class="px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Heads Linked</th>
                            <th class="px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($reportTypes as $rt)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-gray-900 dark:text-white text-sm">{{ $rt->name }}</div>
                                    <div class="font-mono text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $rt->key }}</div>
                                    @if($rt->description)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $rt->description }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($rt->is_daily)
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-900/40">
                                                📅 Daily Mode
                                            </span>
                                            <div class="text-xs text-gray-600 dark:text-gray-400 font-medium">
                                                ⏰ Window: <strong>{{ $rt->time_window_display }}</strong>
                                            </div>
                                            <div class="text-[11px] text-gray-400">🔒 Locked date · 1 report/user daily</div>
                                        </div>
                                    @else
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                📆 Anytime (Non-Daily)
                                            </span>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">✏️ Editable report date</div>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @can('report_types.edit')
                                        <a href="{{ route('report-types.remarks', $rt) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50/70 px-3 py-1.5 text-xs font-bold text-brand-600 hover:bg-brand-100 dark:border-brand-900/40 dark:bg-brand-950/40 dark:text-brand-400 transition-colors shadow-2xs">
                                            💬 {{ $rt->remarks->count() }} Remarks
                                        </a>
                                    @else
                                        <span class="text-xs font-bold text-gray-500">💬 {{ $rt->remarks->count() }} Remarks</span>
                                    @endcan
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold
                                        {{ $rt->inspection_heads_count > 0 ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                                        {{ $rt->inspection_heads_count }} Heads
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @can('report_types.edit')
                                        <button onclick="toggleTypeStatus({{ $rt->id }}, this)"
                                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold transition-colors
                                                    {{ $rt->is_active ? 'bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400' }}">
                                            <span class="status-dot w-1.5 h-1.5 rounded-full inline-block {{ $rt->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                            <span class="status-label">{{ $rt->is_active ? 'Active' : 'Inactive' }}</span>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold {{ $rt->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                                            {{ $rt->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    @endcan
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        @can('report_types.edit')
                                            <a href="{{ route('report-types.edit', $rt) }}"
                                               class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-50 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 transition-colors shadow-2xs">
                                                ⚙️ Settings
                                            </a>
                                        @endcan
                                        @can('report_types.delete')
                                            <form action="{{ route('report-types.destroy', $rt) }}" method="POST"
                                                  onsubmit="return confirm('Delete report type {{ $rt->name }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        {{ $rt->inspection_heads_count > 0 ? 'disabled' : '' }}
                                                        title="{{ $rt->inspection_heads_count > 0 ? 'Cannot delete: linked to inspection heads' : 'Delete' }}"
                                                        class="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-bold text-red-600 hover:bg-red-100 dark:border-red-800/30 dark:bg-red-900/20 dark:text-red-400 transition-colors {{ $rt->inspection_heads_count > 0 ? 'opacity-40 cursor-not-allowed' : '' }}">
                                                    🗑
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                    No report types found. <a href="{{ route('report-types.create') }}" class="text-brand-500 font-bold hover:underline">Create one</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reportTypes->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800">
                    {{ $reportTypes->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
    function toggleTypeStatus(id, btn) {
        fetch(`/report-types/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
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
