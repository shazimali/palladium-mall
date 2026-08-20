@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="System Remarks — {{ $reportType->name }}" />

    <div class="mx-auto w-full max-w-4xl space-y-6">
        {{-- Header Card --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-extrabold text-gray-800 dark:text-white/90">System Remarks: {{ $reportType->name }}</h2>
                    <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-bold text-brand-600 dark:bg-brand-950/40 dark:text-brand-400">
                        {{ $reportType->remarks->count() }} Remarks
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    These predefined remarks populate the mandatory dropdown on every checklist item row when creating a <strong>{{ $reportType->name }}</strong> report.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('report-types.edit', $reportType) }}"
                   class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                    ⚙️ Edit Settings
                </a>
                <a href="{{ route('report-types.index') }}"
                   class="inline-flex items-center gap-1 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                    ← Back to Report Types
                </a>
            </div>
        </div>

        {{-- Add New Remark Form --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-3">➕ Add New System Remark</h3>
            <form action="{{ route('report-types.remarks.store', $reportType) }}" method="POST" class="flex flex-col sm:flex-row items-center gap-3">
                @csrf
                <div class="flex-1 w-full">
                    <input type="text" name="remark" required
                           placeholder="e.g., Clean & Satisfactory, Needs Repair, Dusty, Water Leakage..."
                           class="h-11 w-full rounded-lg border border-gray-300 px-3.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 focus:border-brand-500 focus:outline-none" />
                </div>
                <button type="submit"
                        class="w-full sm:w-auto h-11 inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 text-sm font-bold text-white hover:bg-brand-600 shadow-sm transition-colors shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Remark
                </button>
            </form>
        </div>

        {{-- Remarks List Table --}}
        <div class="rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/[0.01]">
                <h3 class="font-extrabold text-gray-800 dark:text-white text-sm">Predefined Remarks List</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-16">#</th>
                            <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">System Remark</th>
                            <th class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 w-24">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($reportType->remarks as $idx => $rem)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                <td class="px-4 py-3 text-gray-400 font-mono text-xs font-semibold">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90">
                                    {{ $rem->remark }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form action="{{ route('report-types.remarks.destroy', ['reportType' => $reportType->id, 'remark' => $rem->id]) }}" method="POST"
                                          onsubmit="return confirm('Delete this system remark?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-600 hover:bg-red-100 dark:border-red-800/30 dark:bg-red-900/20 dark:text-red-400 transition-colors">
                                            🗑 Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-400 dark:text-gray-500">
                                    No system remarks added yet for this report type. Use the form above to add remarks.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
