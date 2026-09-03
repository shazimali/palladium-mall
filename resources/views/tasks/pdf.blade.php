<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Tasks Register - {{ $dateRangeLabel ?? (isset($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '') }} - PALLADIUM MALL</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, ui-sans-serif, system-ui, -apple-system, sans-serif;
            font-size: 10px;
            color: #111827;
            background: #ffffff;
            line-height: 1.3;
        }

        .header-bar {
            border-bottom: 2px solid #ef4444;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #111827;
        }

        .header-subtitle {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .date-badge {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 4px;
            padding: 4px 8px;
            text-align: right;
            display: inline-block;
        }

        .date-label {
            font-size: 9px;
            font-weight: bold;
            color: #dc2626;
            text-transform: uppercase;
        }

        .date-val {
            font-size: 11px;
            font-weight: bold;
            color: #111827;
        }

        /* Filter Box */
        .filter-box {
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 6px 8px;
            margin-bottom: 8px;
        }

        .filter-table {
            width: 100%;
            border-collapse: collapse;
        }

        .filter-table td {
            width: 20%;
            vertical-align: top;
        }

        .filter-label {
            font-size: 8px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            display: block;
        }

        .filter-val {
            font-size: 9px;
            font-weight: bold;
            color: #111827;
        }

        /* Stats Bar */
        .stats-bar {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 6px 8px;
            margin-bottom: 8px;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
        }

        .stats-item {
            font-size: 9px;
            font-weight: bold;
        }

        .stats-printed {
            font-size: 8px;
            color: #6b7280;
            text-align: right;
        }

        /* Main Table */
        .task-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #374151;
        }

        .task-table th {
            background-color: #e5e7eb;
            color: #111827;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 6px;
            border: 1px solid #4b5563;
            text-align: left;
        }

        .task-table td {
            padding: 5px 6px;
            border: 1px solid #d1d5db;
            vertical-align: top;
            font-size: 9px;
        }

        .category-header {
            background-color: #fef3c7;
            border-top: 1px solid #f59e0b;
            border-bottom: 1px solid #f59e0b;
            padding: 4px 6px;
            font-weight: bold;
            color: #78350f;
            font-size: 10px;
        }

        /* Status & Priority Badges */
        .badge-ok {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #34d399;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
            display: inline-block;
        }

        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
            display: inline-block;
        }

        .badge-progress {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
            display: inline-block;
        }

        .priority-urgent { color: #dc2626; font-weight: bold; }
        .priority-high { color: #c2410c; font-weight: bold; }
        .priority-med { color: #1d4ed8; font-weight: bold; }
        .priority-low { color: #4b5563; font-weight: bold; }

        .rating-good {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 2px;
        }

        .rating-bad {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 2px;
        }

        /* Footer signatures */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            border-top: 1px solid #9ca3af;
            padding-top: 8px;
        }

        .footer-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 4px 8px;
        }

        .sig-label {
            font-size: 8px;
            font-weight: bold;
            color: #4b5563;
            text-transform: uppercase;
            display: block;
        }

        .sig-val {
            font-size: 10px;
            font-weight: bold;
            color: #111827;
            margin-top: 2px;
        }

        .sig-placeholder {
            font-size: 9px;
            color: #9ca3af;
            font-style: italic;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <!-- Header Bar -->
    <div class="header-bar">
        <table class="header-table">
            <tr>
                <td>
                    <div class="header-title">PALLADIUM MALL</div>
                    <div class="header-subtitle">Daily Operations & Maintenance Register</div>
                </td>
                <td style="text-align: right;">
                    <div class="date-badge">
                        <span class="date-label">Date: </span>
                        <span class="date-val">{{ $dateRangeLabel ?? (isset($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '') }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Filter Criteria Bar -->
    <div class="filter-box">
        <table class="filter-table">
            <tr>
                <td>
                    <span class="filter-label">Register Date:</span>
                    <span class="filter-val">{{ $dateRangeLabel ?? (isset($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '') }}</span>
                </td>
                <td>
                    <span class="filter-label">Category Filter:</span>
                    <span class="filter-val">{{ $selectedCategory }}</span>
                </td>
                <td>
                    <span class="filter-label">Assigned To:</span>
                    <span class="filter-val">{{ $selectedAssignee }}</span>
                </td>
                <td>
                    <span class="filter-label">Status Filter:</span>
                    <span class="filter-val">
                        @if(!empty($filters['status']))
                            @if($filters['status'] === 'todo') To Do
                            @elseif($filters['status'] === 'in_progress') In Progress
                            @elseif($filters['status'] === 'completed') Completed
                            @else {{ ucfirst($filters['status']) }}
                            @endif
                        @else
                            All Statuses
                        @endif
                    </span>
                </td>
                <td>
                    <span class="filter-label">Priority Filter:</span>
                    <span class="filter-val">
                        @if(!empty($filters['priority']))
                            {{ ucfirst($filters['priority']) }}
                        @else
                            All Priorities
                        @endif
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Stats Bar -->
    <div class="stats-bar">
        <table class="stats-table">
            <tr>
                <td class="stats-item">
                    Total: <strong>{{ $counts['total'] }}</strong> &nbsp;|&nbsp;
                    To Do: <strong style="color: #b45309;">{{ $counts['todo'] }}</strong> &nbsp;|&nbsp;
                    In Progress: <strong style="color: #1d4ed8;">{{ $counts['in_progress'] }}</strong> &nbsp;|&nbsp;
                    Completed / OK: <strong style="color: #047857;">{{ $counts['completed'] }}</strong>
                </td>
                <td class="stats-printed">
                    Printed: <strong>{{ now()->format('d/m/Y h:i A') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    @php
        $groupedTasks = $tasks->groupBy(function($task) {
            return $task->category?->name ?? 'General Tasks';
        });
    @endphp

    <!-- Register Table -->
    <table class="task-table">
        <thead>
            <tr>
                <th style="width: 4%; text-align: center;">#</th>
                <th style="width: 32%;">Description / Instructions</th>
                <th style="width: 10%; text-align: center;">Priority</th>
                <th style="width: 12%; text-align: center;">Due Date & Time</th>
                <th style="width: 18%;">Remarks (Assignee)</th>
                <th style="width: 9%; text-align: center;">OK / Pending</th>
                <th style="width: 15%;">Admin Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groupedTasks as $categoryName => $categoryTasks)
                <tr>
                    <td colspan="7" class="category-header">
                        {{ $categoryName }} ({{ $categoryTasks->count() }} {{ \Illuminate\Support\Str::plural('task', $categoryTasks->count()) }})
                    </td>
                </tr>
                @foreach($categoryTasks as $taskIndex => $task)
                    @php
                        $isCompleted = ($task->status === 'completed');
                    @endphp
                    <tr style="background-color: {{ $taskIndex % 2 === 0 ? '#ffffff' : '#f9fafb' }};">
                        <td style="text-align: center; font-weight: bold; color: #4b5563;">
                            {{ $taskIndex + 1 }}
                        </td>
                        <td>
                            {{ $task->description ?: '—' }}
                        </td>
                        <td style="text-align: center;">
                            @if($task->priority === 'urgent')
                                <span class="priority-urgent">🔴 Urgent</span>
                            @elseif($task->priority === 'high')
                                <span class="priority-high">🟠 High</span>
                            @elseif($task->priority === 'low')
                                <span class="priority-low">⚪ Low</span>
                            @else
                                <span class="priority-med">🔵 Med</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($task->due_at)
                                <div style="font-weight: bold;">{{ $task->due_at->format('h:i A') }}</div>
                                <div style="font-size: 8px; color: #6b7280;">{{ $task->due_at->format('d/m/Y') }}</div>
                            @else
                                <span style="color: #9ca3af;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($task->assignee_remarks)
                                {{ $task->assignee_remarks }}
                            @elseif($isCompleted)
                                <span style="color: #047857; font-weight: bold; font-style: italic;">Completed</span>
                            @else
                                <span style="color: #9ca3af; font-style: italic;">—</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($isCompleted)
                                <span class="badge-ok">OK</span>
                            @elseif($task->status === 'in_progress')
                                <span class="badge-progress">Pending</span>
                            @else
                                <span class="badge-pending">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if($task->creator_rating === 'good' || $task->creator_rating === 'satisfactory')
                                <span class="rating-good">Satisfactory</span>
                            @elseif($task->creator_rating === 'bad' || $task->creator_rating === 'unsatisfactory')
                                <span class="rating-bad">Unsatisfactory</span>
                            @endif

                            @if($task->creator_remarks)
                                <div>{{ $task->creator_remarks }}</div>
                            @elseif(!$task->creator_rating)
                                <span style="color: #9ca3af; font-style: italic;">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #6b7280; font-weight: bold;">
                        No tasks recorded for {{ $dateRangeLabel ?? (isset($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '') }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer Signatures -->
    <table class="footer-table">
        <tr>
            <td>
                <span class="sig-label">Prepared By</span>
                <div class="sig-val">{{ auth()->user()->name }}</div>
            </td>
            <td>
                <span class="sig-label">Supervisor / In-Charge</span>
                <div class="sig-placeholder">Signature & Date</div>
            </td>
            <td>
                <span class="sig-label">Admin / Management Approval</span>
                <div class="sig-placeholder">Signature & Stamp</div>
            </td>
        </tr>
    </table>

</body>
</html>
