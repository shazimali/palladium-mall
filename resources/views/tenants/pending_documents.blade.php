@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pending Documents Check" />

    <x-common.component-card title="Pending Documents Check" desc="Monitor documents upload, government documents, and move-in checklists status for active tenant agreements.">
        
        {{-- Top Bar with Search & Info --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <form action="{{ route('tenants.pending-documents') }}" method="GET" class="relative flex-1 max-w-md">
                <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                    <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by tenant name or flat number..."
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            </form>

            <div class="flex items-center gap-2">
                @if(request()->filled('search'))
                    <a href="{{ route('tenants.pending-documents') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Clear Search
                    </a>
                @endif
                <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Active Tenants Count: {{ $tenants->total() }}
                </span>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 w-16">#</th>
                        <th class="px-4 py-3">Tenant Name</th>
                        <th class="px-4 py-3">Flat/Shop</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Active Agreement Period</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3">Uploaded Docs Summary</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($tenants as $index => $tenant)
                        @php
                            $agreement = $tenant->activeAgreement;
                            $checklist = $agreement?->documentChecklist;
                            $isCommercial = $agreement?->unit?->type === 'commercial';
                            
                            $totalDocs = $isCommercial ? 23 : 21;
                            $checkedDocs = $checklist ? $checklist->countChecked() : 0;
                            
                            $docsChecklistComplete = $checklist ? $checklist->allDocumentsUploaded() : false;
                            $govtDocumentUploaded = !empty($agreement?->govt_document);
                            $moveInChecklistCompleted = $agreement?->moveInChecklist ? $agreement->moveInChecklist->isComplete() : false;
                            
                            $overallComplete = $docsChecklistComplete && $govtDocumentUploaded && $moveInChecklistCompleted;
                            $missingCount = $totalDocs - $checkedDocs;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 text-gray-400">{{ $tenants->firstItem() + $index }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('tenants.show', $tenant) }}" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                    {{ $tenant->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                @if($tenant->unit)
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                                        {{ $tenant->unit->unit_number }} ({{ ucfirst($tenant->unit->type) }})
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $tenant->phone }}</td>
                            <td class="px-4 py-3 text-xs">
                                @if($agreement && $agreement->start_date && $agreement->end_date)
                                    <span class="font-medium text-gray-700 dark:text-gray-300">
                                        {{ $agreement->start_date->format('d M Y') }} to {{ $agreement->end_date->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($overallComplete)
                                    <span class="inline-flex items-center justify-center rounded-full bg-green-100 p-1 text-green-600 dark:bg-green-950/20 dark:text-green-400" title="All documents & checklists complete">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center rounded-full bg-red-100 p-1 text-red-600 dark:bg-red-950/20 dark:text-red-400" title="Documents or checklist pending">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <div class="flex flex-col gap-1 font-medium">
                                    {{-- 1. Document Checklist --}}
                                    <div class="flex items-center gap-1.5">
                                        @if($docsChecklistComplete)
                                            <span class="text-green-600 dark:text-green-400 font-bold" title="Docs checklist complete">✓</span>
                                            <span class="text-gray-600 dark:text-gray-400 text-[10px]">
                                                Docs: {{ $checkedDocs }}/{{ $totalDocs }} Complete
                                            </span>
                                        @else
                                            <span class="text-red-500 font-bold" title="Docs checklist incomplete">✗</span>
                                            <span class="text-red-600 dark:text-red-400 font-semibold text-[10px]">
                                                Docs: {{ $checkedDocs }}/{{ $totalDocs }} ({{ $missingCount }} missing)
                                            </span>
                                        @endif
                                    </div>
                                    
                                    {{-- 2. Agreement Govt Document --}}
                                    <div class="flex items-center gap-1.5">
                                        @if($govtDocumentUploaded)
                                            <span class="text-green-600 dark:text-green-400 font-bold" title="Govt document uploaded">✓</span>
                                            <a href="{{ $agreement->govt_document_url }}" target="_blank" class="text-brand-600 dark:text-brand-400 hover:underline text-[10px]">
                                                Govt Document
                                            </a>
                                        @else
                                            <span class="text-red-500 font-bold" title="Govt document missing">✗</span>
                                            <span class="text-red-600 dark:text-red-400 font-semibold text-[10px]">Govt Document Missing</span>
                                        @endif
                                    </div>

                                    {{-- 3. Move In Checklist --}}
                                    <div class="flex items-center gap-1.5">
                                        @if($moveInChecklistCompleted)
                                            <span class="text-green-600 dark:text-green-400 font-bold" title="Move-in checklist completed">✓</span>
                                            <span class="text-gray-600 dark:text-gray-400 text-[10px]">Move-In Checklist Complete</span>
                                        @else
                                            <span class="text-red-500 font-bold" title="Move-in checklist missing/incomplete">✗</span>
                                            <span class="text-red-600 dark:text-red-400 font-semibold text-[10px]">
                                                @if($agreement?->moveInChecklist)
                                                    Move-In: Incomplete ({{ $agreement->moveInChecklist->countChecked() }}/{{ $agreement->moveInChecklist->countTotal() }} OK)
                                                @else
                                                    Move-In Checklist Missing
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    <a href="{{ route('tenants.showStep', [$tenant, 3]) }}" 
                                       class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                                       title="Upload Agreement Government Document (Step 3)">
                                        Govt Doc (Step 3)
                                    </a>
                                    <a href="{{ route('tenants.showStep', [$tenant, 4]) }}" 
                                       class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                                       title="Manage Documents Checklist (Step 4)">
                                        Docs Checklist (Step 4)
                                    </a>
                                    <a href="{{ route('tenants.showStep', [$tenant, 5]) }}" 
                                       class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                                       title="Manage Move In Checklist (Step 5)">
                                        Move-In (Step 5)
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                No active tenants found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                {{ $tenants->links() }}
            </div>
        @endif

    </x-common.component-card>
@endsection
