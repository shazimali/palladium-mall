<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Tasks Register - {{ $dateRangeLabel ?? (isset($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '') }} - PALLADIUM MALL</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 0.5cm;
            }
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: #111827 !important;
                padding: 0 !important;
                margin: 0 !important;
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .printable-container {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 antialiased min-h-screen p-4 sm:p-6 font-sans">

    <!-- ACTION BUTTONS (HIDDEN DURING PRINT) -->
    <div class="max-w-[1300px] w-full mx-auto mb-4 flex items-center justify-between no-print">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tasks Register Print View</span>
            <span class="text-xs bg-indigo-100 text-indigo-800 px-2.5 py-0.5 rounded-full font-extrabold">
                Date: {{ $dateRangeLabel ?? (isset($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '') }}
            </span>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tasks.print', array_merge(request()->query(), ['download' => 'pdf'])) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2 text-xs font-bold text-white shadow-md hover:bg-emerald-700 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Download PDF
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2 text-xs font-bold text-white shadow-md hover:bg-indigo-700 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Register
            </button>
            <button onclick="window.close()"
                class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-all shadow-xs cursor-pointer">
                Close
            </button>
        </div>
    </div>

    <!-- PRINTABLE REGISTER CONTAINER -->
    <div class="max-w-[1300px] w-full mx-auto printable-container rounded-xl bg-white p-5 sm:p-7 border border-gray-300 shadow-md text-gray-900">

        <!-- REGISTER HEADER BAR -->
        <div class="flex items-center justify-between pb-3 mb-3 border-b-2 border-red-500">
            <div>
                <h1 class="text-xl sm:text-2xl font-black tracking-wider text-gray-900 uppercase leading-none">
                    PALLADIUM MALL
                </h1>
                <p class="text-[11px] font-bold text-gray-500 uppercase tracking-widest mt-0.5">
                    Daily Operations & Maintenance Register
                </p>
            </div>
            <div class="text-right">
                <div class="inline-block px-3 py-1 bg-red-50 border border-red-200 rounded-lg">
                    <span class="text-xs font-bold text-red-600 uppercase">Date: </span>
                    <strong class="text-sm font-black text-gray-900 ml-1">
                        {{ $dateRangeLabel ?? (isset($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '') }}
                    </strong>
                    @if(isset($date) && empty($dateFrom) && empty($dateTo))
                        <span class="text-xs text-gray-500 ml-1 font-semibold">({{ \Carbon\Carbon::parse($date)->format('l') }})</span>
                    @elseif(!empty($dateFrom) && !empty($dateTo) && $dateFrom === $dateTo)
                        <span class="text-xs text-gray-500 ml-1 font-semibold">({{ \Carbon\Carbon::parse($dateFrom)->format('l') }})</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- SELECTED FILTERS & CRITERIA BAR -->
        <div class="mb-3 rounded-lg bg-gray-50 border border-gray-300 p-2.5 text-xs">
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Register Date:</span>
                    <span class="font-extrabold text-gray-900">{{ $dateRangeLabel ?? (isset($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '') }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Category Filter:</span>
                    <span class="font-extrabold text-indigo-900">{{ $selectedCategory }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Assigned To:</span>
                    <span class="font-extrabold text-gray-900">{{ $selectedAssignee }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Status Filter:</span>
                    <span class="font-extrabold text-gray-900">
                        @if(!empty($filters['status']))
                            @if($filters['status'] === 'todo') 📌 To Do
                            @elseif($filters['status'] === 'in_progress') ⚡ In Progress
                            @elseif($filters['status'] === 'completed') ✅ Completed
                            @else {{ ucfirst($filters['status']) }}
                            @endif
                        @else
                            All Statuses
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-gray-500 block text-[10px] uppercase font-bold">Priority Filter:</span>
                    <span class="font-extrabold text-gray-900">
                        @if(!empty($filters['priority']))
                            {{ ucfirst($filters['priority']) }}
                        @else
                            All Priorities
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- STATS BAR (COMPACT) -->
        <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg bg-gray-100 border border-gray-300 mb-3 text-xs">
            <div class="flex items-center gap-4 font-bold">
                <span class="text-gray-700">Total: <strong class="text-gray-900 font-black ml-0.5">{{ $counts['total'] }}</strong></span>
                <span class="text-amber-700">📌 To Do: <strong class="font-black ml-0.5">{{ $counts['todo'] }}</strong></span>
                <span class="text-blue-700">⚡ In Progress: <strong class="font-black ml-0.5">{{ $counts['in_progress'] }}</strong></span>
                <span class="text-emerald-700">✅ Completed / OK: <strong class="font-black ml-0.5">{{ $counts['completed'] }}</strong></span>
            </div>
            <div class="text-[11px] text-gray-500">
                Printed: <span class="font-bold text-gray-800">{{ now()->format('d/m/Y h:i A') }}</span>
            </div>
        </div>

        @php
            $groupedTasks = $tasks->groupBy(function($task) {
                return $task->category?->name ?? 'General Tasks';
            });
            $overallIndex = 0;
        @endphp

        <!-- REGISTER TABLE (MATCHING NOTEBOOK FORMAT) -->
        <div class="overflow-x-auto border-2 border-gray-700 rounded-lg">
            <table class="w-full text-left text-xs border-collapse font-sans">
                <thead>
                    <tr class="bg-gray-200 border-b-2 border-gray-700 text-gray-900 uppercase tracking-wider font-black text-[11px]">
                        <th class="py-2 px-2 w-8 text-center border-r border-gray-400">#</th>
                        <th class="py-2 px-3 border-r border-gray-400">Description / Instructions</th>
                        <th class="py-2 px-2 w-20 text-center border-r border-gray-400">Priority</th>
                        <th class="py-2 px-3 w-36 text-center border-r border-gray-400">Due Date & Time</th>
                        <th class="py-2 px-3 border-r border-gray-400">Remarks (Assignee)</th>
                        <th class="py-2 px-2 w-24 text-center border-r border-gray-400">OK / Pending</th>
                        <th class="py-2 px-3 w-48 text-center">Admin Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300 bg-white">
                    @forelse($groupedTasks as $categoryName => $categoryTasks)
                        {{-- ── Category Section Banner (Like Notebook Highlight) ── --}}
                        <tr class="bg-amber-100/80 border-t-2 border-b border-amber-300">
                            <td colspan="7" class="py-1.5 px-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md bg-amber-400/90 text-amber-950 font-black text-xs uppercase tracking-wide shadow-xs">
                                        {{ $categoryName }}
                                    </span>
                                    <span class="text-[11px] font-bold text-amber-900">
                                        ({{ $categoryTasks->count() }} {{ \Illuminate\Support\Str::plural('task', $categoryTasks->count()) }})
                                    </span>
                                </div>
                            </td>
                        </tr>

                        {{-- ── Tasks within Category ── --}}
                        @foreach($categoryTasks as $taskIndex => $task)
                            @php
                                $overallIndex++;
                                $isCompleted = ($task->status === 'completed');
                            @endphp
                            <tr class="{{ $taskIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50/70' }} align-top hover:bg-amber-50/40 transition-colors">
                                
                                {{-- Sr # in category --}}
                                <td class="py-2 px-2 text-center font-mono font-bold text-gray-700 border-r border-gray-300">
                                    {{ $taskIndex + 1 }}
                                </td>

                                {{-- Description / Instructions --}}
                                <td class="py-2 px-3 text-gray-900 border-r border-gray-300 whitespace-pre-line leading-relaxed">
                                    <span class="font-medium">{{ $task->description ?: '—' }}</span>
                                </td>

                                {{-- Priority (Immediately after Description) --}}
                                <td class="py-2 px-2 text-center border-r border-gray-300 font-bold text-[11px] whitespace-nowrap">
                                    @if($task->priority === 'urgent')
                                        <span class="text-red-700 font-black">🔴 Urgent</span>
                                    @elseif($task->priority === 'high')
                                        <span class="text-orange-700 font-black">🟠 High</span>
                                    @elseif($task->priority === 'low')
                                        <span class="text-gray-500 font-semibold">⚪ Low</span>
                                    @else
                                        <span class="text-blue-700 font-semibold">🔵 Med</span>
                                    @endif
                                </td>

                                {{-- Due Date & Time --}}
                                <td class="py-2 px-3 text-center border-r border-gray-300 text-[11px] font-mono leading-tight whitespace-nowrap">
                                    @if($task->due_at)
                                        <div class="font-bold text-gray-900">
                                            {{ $task->due_at->format('h:i A') }}
                                        </div>
                                        <div class="text-[10px] text-gray-500">
                                            {{ $task->due_at->format('d/m/Y') }}
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                {{-- Remarks (Assignee) --}}
                                <td class="py-2 px-3 text-gray-800 border-r border-gray-300 whitespace-pre-line leading-relaxed">
                                    @if($task->assignee_remarks)
                                        <span class="font-medium text-gray-900">{{ $task->assignee_remarks }}</span>
                                    @elseif($isCompleted)
                                        <span class="text-emerald-700 font-bold italic">Completed</span>
                                    @else
                                        <span class="text-gray-400 italic">—</span>
                                    @endif
                                </td>

                                {{-- OK / Pending (Status) --}}
                                <td class="py-2 px-2 text-center border-r border-gray-300">
                                    @if($isCompleted)
                                        <span class="inline-block px-2 py-0.5 rounded font-black text-xs bg-emerald-100 text-emerald-900 border border-emerald-400">
                                            OK
                                        </span>
                                    @elseif($task->status === 'in_progress')
                                        <span class="inline-block px-2 py-0.5 rounded font-black text-[11px] bg-blue-100 text-blue-900 border border-blue-300">
                                            Pending
                                        </span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded font-bold text-[11px] bg-amber-100 text-amber-900 border border-amber-300">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                {{-- Admin Remarks / Rating (Satisfactory / Unsatisfactory) --}}
                                <td class="py-2 px-3 text-left">
                                    @if($task->creator_rating === 'good' || $task->creator_rating === 'satisfactory')
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300 mb-0.5">
                                            Satisfactory
                                        </span>
                                    @elseif($task->creator_rating === 'bad' || $task->creator_rating === 'unsatisfactory')
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-black bg-red-100 text-red-800 border border-red-300 mb-0.5">
                                            Unsatisfactory
                                        </span>
                                    @endif

                                    @if($task->creator_remarks)
                                        <div class="{{ $task->creator_rating ? 'mt-1' : '' }} text-xs font-semibold text-gray-900 whitespace-pre-line leading-relaxed">
                                            {{ $task->creator_remarks }}
                                        </div>
                                    @elseif(!$task->creator_rating && !$task->admin_photo)
                                        <span class="text-gray-400 italic">—</span>
                                    @endif

                                    @if($task->admin_photo && $task->admin_photo_url)
                                        <div class="mt-1">
                                            <img src="{{ $task->admin_photo_url }}" alt="Attachment" class="h-10 w-10 object-cover rounded border border-gray-400 inline-block">
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-500 font-bold">
                                No tasks recorded for {{ $dateRangeLabel ?? (isset($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '') }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- FOOTER SIGNATURES & VERIFICATION (MATCHING REGISTER SHEET) -->
        <div class="mt-8 pt-4 border-t-2 border-gray-400 grid grid-cols-3 gap-8 text-xs text-gray-700">
            <div class="border-t border-gray-400 pt-2 text-center">
                <span class="font-bold uppercase text-gray-600 block text-[10px]">Prepared By</span>
                <span class="font-black text-gray-900 text-xs">{{ auth()->user()->name }}</span>
            </div>
            <div class="border-t border-gray-400 pt-2 text-center">
                <span class="font-bold uppercase text-gray-600 block text-[10px]">Supervisor / In-Charge</span>
                <span class="text-gray-400 italic text-xs">Signature & Date</span>
            </div>
            <div class="border-t border-gray-400 pt-2 text-center">
                <span class="font-bold uppercase text-gray-600 block text-[10px]">Admin / Management Approval</span>
                <span class="text-gray-400 italic text-xs">Signature & Stamp</span>
            </div>
        </div>

    </div>

</body>
</html>
