<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskComment;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskCommentAddedNotification;
use App\Notifications\TaskStatusUpdatedNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TaskController extends Controller
{
    // -----------------------------------------------------------------------
    // Index — Daily Register Table
    // -----------------------------------------------------------------------

    public function index(Request $request): View
    {
        $this->authorizeTaskAccess('view');

        /** @var User $currentUser */
        $currentUser = auth()->user();

        // Date-range filter (both optional — no date = show all tasks)
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : null;
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : null;

        $query = Task::with(['category', 'creator', 'assignees']);

        if ($dateFrom && $dateTo) {
            $query->whereBetween('due_at', [$dateFrom, $dateTo]);
        } elseif ($dateFrom) {
            $query->where('due_at', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->where('due_at', '<=', $dateTo);
        }

        // Role-based visibility
        if (!$currentUser->isSuperAdmin()) {
            $query->where(function ($q) use ($currentUser) {
                $q->where('created_by', $currentUser->id)
                  ->orWhereHas('assignees', fn($aq) => $aq->where('users.id', $currentUser->id));
            });
        }

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('assigned_to')) {
            $assignedTo = $request->input('assigned_to');
            if ($assignedTo === 'me') {
                $query->whereHas('assignees', fn($q) => $q->where('users.id', $currentUser->id));
            } else {
                $query->whereHas('assignees', fn($q) => $q->where('users.id', $assignedTo));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        $tasks = $query->orderBy('order_column')->orderByDesc('created_at')->get();

        $counts = [
            'total'       => $tasks->count(),
            'todo'        => $tasks->where('status', 'todo')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed'   => $tasks->where('status', 'completed')->count(),
        ];

        $users      = User::where('is_active', true)->orderBy('name')->get();
        $categories = TaskCategory::orderBy('name')->get();

        return view('tasks.index', [
            'title'      => 'Daily Tasks',
            'tasks'      => $tasks,
            'counts'     => $counts,
            'users'      => $users,
            'categories' => $categories,
            'dateFrom'   => $request->input('date_from', ''),
            'dateTo'     => $request->input('date_to', ''),
            'filters'    => $request->only(['category_id', 'assigned_to', 'status', 'priority', 'date_from', 'date_to']),
        ]);
    }

    // -----------------------------------------------------------------------
    // Print View — Daily Tasks Register
    // -----------------------------------------------------------------------

    public function print(Request $request): View
    {
        $this->authorizeTaskAccess('view');

        /** @var User $currentUser */
        $currentUser = auth()->user();

        // Date-range filter (both optional)
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : null;
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : null;

        $query = Task::with(['category', 'creator', 'assignees']);

        if ($dateFrom && $dateTo) {
            $query->whereBetween('due_at', [$dateFrom, $dateTo]);
        } elseif ($dateFrom) {
            $query->where('due_at', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->where('due_at', '<=', $dateTo);
        }

        // Role-based visibility
        if (!$currentUser->isSuperAdmin()) {
            $query->where(function ($q) use ($currentUser) {
                $q->where('created_by', $currentUser->id)
                  ->orWhereHas('assignees', fn($aq) => $aq->where('users.id', $currentUser->id));
            });
        }

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('assigned_to')) {
            $assignedTo = $request->input('assigned_to');
            if ($assignedTo === 'me') {
                $query->whereHas('assignees', fn($q) => $q->where('users.id', $currentUser->id));
            } else {
                $query->whereHas('assignees', fn($q) => $q->where('users.id', $assignedTo));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        $tasks = $query->orderBy('order_column')->orderByDesc('created_at')->get();

        $counts = [
            'total'       => $tasks->count(),
            'todo'        => $tasks->where('status', 'todo')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed'   => $tasks->where('status', 'completed')->count(),
        ];

        $selectedCategory = $request->filled('category_id')
            ? TaskCategory::find($request->input('category_id'))?->name
            : 'All Categories';

        $selectedAssignee = 'All Assignees';
        if ($request->filled('assigned_to')) {
            if ($request->input('assigned_to') === 'me') {
                $selectedAssignee = auth()->user()->name . ' (My Tasks)';
            } else {
                $selectedAssignee = User::find($request->input('assigned_to'))?->name ?? 'All Assignees';
            }
        }

        $dateRangeLabel = 'All Dates';
        if ($dateFrom && $dateTo) {
            if ($dateFrom->toDateString() === $dateTo->toDateString()) {
                $dateRangeLabel = $dateFrom->format('d M Y');
            } else {
                $dateRangeLabel = $dateFrom->format('d M Y') . ' – ' . $dateTo->format('d M Y');
            }
        } elseif ($dateFrom) {
            $dateRangeLabel = 'From ' . $dateFrom->format('d M Y');
        } elseif ($dateTo) {
            $dateRangeLabel = 'Until ' . $dateTo->format('d M Y');
        }

        $date = $request->input('date')
            ?? $request->input('date_from')
            ?? $request->input('date_to')
            ?? now()->toDateString();

        return view('tasks.print', [
            'title'            => 'Daily Tasks Register Print',
            'tasks'            => $tasks,
            'counts'           => $counts,
            'date'             => $date,
            'dateFrom'         => $request->input('date_from', ''),
            'dateTo'           => $request->input('date_to', ''),
            'dateRangeLabel'   => $dateRangeLabel,
            'filters'          => $request->only(['category_id', 'assigned_to', 'status', 'priority', 'date_from', 'date_to']),
            'selectedCategory' => $selectedCategory,
            'selectedAssignee' => $selectedAssignee,
        ]);
    }

    // -----------------------------------------------------------------------
    // Table Rows — AJAX (returns tbody HTML + counts after create/update)
    // -----------------------------------------------------------------------

    public function tableRows(Request $request): JsonResponse
    {
        $this->authorizeTaskAccess('view');

        /** @var User $currentUser */
        $currentUser = auth()->user();

        // Date-range filter (both optional)
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : null;
        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : null;

        $query = Task::with(['category', 'creator', 'assignees']);

        if ($dateFrom && $dateTo) {
            $query->whereBetween('due_at', [$dateFrom, $dateTo]);
        } elseif ($dateFrom) {
            $query->where('due_at', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->where('due_at', '<=', $dateTo);
        }

        if (!$currentUser->isSuperAdmin()) {
            $query->where(function ($q) use ($currentUser) {
                $q->where('created_by', $currentUser->id)
                  ->orWhereHas('assignees', fn($aq) => $aq->where('users.id', $currentUser->id));
            });
        }

        if ($request->filled('category_id')) $query->where('category_id', $request->input('category_id'));
        if ($request->filled('status'))      $query->where('status', $request->input('status'));
        if ($request->filled('priority'))    $query->where('priority', $request->input('priority'));
        if ($request->filled('assigned_to')) {
            $at = $request->input('assigned_to');
            $at === 'me'
                ? $query->whereHas('assignees', fn($q) => $q->where('users.id', $currentUser->id))
                : $query->whereHas('assignees', fn($q) => $q->where('users.id', $at));
        }

        $tasks  = $query->orderBy('order_column')->orderByDesc('created_at')->get();
        $counts = [
            'total'       => $tasks->count(),
            'todo'        => $tasks->where('status', 'todo')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed'   => $tasks->where('status', 'completed')->count(),
        ];

        return response()->json([
            'html'   => view('tasks.partials._table_rows', ['tasks' => $tasks, 'date' => $dateFrom?->toDateString() ?? ''])->render(),
            'counts' => $counts,
        ]);
    }

    // -----------------------------------------------------------------------
    // Create
    // -----------------------------------------------------------------------

    public function create(): View
    {
        $this->authorizeTaskAccess('create');

        $users      = User::where('is_active', true)->orderBy('name')->get();
        $categories = TaskCategory::active()->orderBy('name')->get();

        return view('tasks.create', [
            'title'      => 'Assign Daily Task',
            'users'      => $users,
            'categories' => $categories,
        ]);
    }

    // -----------------------------------------------------------------------
    // Store
    // -----------------------------------------------------------------------

    public function store(Request $request)
    {
        $this->authorizeTaskAccess('create');

        $validated = $request->validate([
            'category_id'      => 'required|exists:task_categories,id',
            'description'      => 'nullable|string',
            'creator_remarks'  => 'nullable|string',
            'creator_rating'   => 'nullable|in:good,bad',
            'admin_photo'      => ['nullable', 'image', 'max:200'], // Max 200 KB
            'assignee_remarks' => 'nullable|string',
            'priority'         => 'required|in:low,medium,high,urgent',
            'due_at'           => 'required|date',
            'assignee_ids'     => 'required|array|min:1',
            'assignee_ids.*'   => 'exists:users,id',
        ], [
            'admin_photo.max' => 'Photo size must not exceed 200 KB.',
        ]);

        // Auto-fill title from category name
        $category = TaskCategory::findOrFail($validated['category_id']);

        $photoPath = null;
        if ($request->hasFile('admin_photo')) {
            $photoPath = $request->file('admin_photo')->store('task_photos', 'public');
        }

        DB::beginTransaction();
        try {
            $task = Task::create([
                'title'            => $category->name,
                'category_id'      => $validated['category_id'],
                'description'      => $validated['description'] ?? null,
                'creator_remarks'  => $validated['creator_remarks'] ?? null,
                'creator_rating'   => $validated['creator_rating'] ?? null,
                'admin_photo'      => $photoPath,
                'assignee_remarks' => $validated['assignee_remarks'] ?? null,
                'priority'         => $validated['priority'],
                'status'           => 'todo',
                'due_at'           => Carbon::parse($validated['due_at']),
                'created_by'       => auth()->id(),
            ]);

            $task->assignees()->sync($validated['assignee_ids']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Failed to create task: ' . $e->getMessage());
        }

        // Send notifications to assignees
        $assignees = User::whereIn('id', $validated['assignee_ids'])
            ->where('id', '!=', auth()->id())
            ->get();

        if ($assignees->isNotEmpty()) {
            Notification::send($assignees, new TaskAssignedNotification($task, auth()->user()));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully!',
                'task'    => $task->load(['category', 'creator', 'assignees']),
            ]);
        }

        return redirect()->route('tasks.index', ['date' => $task->due_at->toDateString()])
            ->with('success', 'Task created and assigned successfully!');
    }

    // -----------------------------------------------------------------------
    // Edit
    // -----------------------------------------------------------------------

    public function edit(Task $task): View
    {
        $this->authorizeTaskAccess('edit', $task);

        $currentUser    = auth()->user();
        $isSuperAdmin   = $currentUser->isSuperAdmin();
        $isCreator      = (int) $currentUser->id === (int) $task->created_by;
        $isAssignee     = $task->assignees->contains('id', $currentUser->id);
        $canEditAll     = $isSuperAdmin || $isCreator;
        $isAssigneeOnly = $isAssignee && !$canEditAll;

        $users      = User::where('is_active', true)->orderBy('name')->get();
        $categories = TaskCategory::active()->orderBy('name')->get();

        return view('tasks.edit', [
            'title'          => 'Edit Task',
            'task'           => $task->load('assignees'),
            'users'          => $users,
            'categories'     => $categories,
            'isAssigneeOnly' => $isAssigneeOnly,
            'canEditAll'     => $canEditAll,
        ]);
    }

    // -----------------------------------------------------------------------
    // Update
    // -----------------------------------------------------------------------

    public function update(Request $request, Task $task)
    {
        $this->authorizeTaskAccess('edit', $task);

        $currentUser  = auth()->user();
        $isSuperAdmin = $currentUser->isSuperAdmin();
        $isCreator    = (int) $currentUser->id === (int) $task->created_by;
        $isAssignee   = $task->assignees->contains('id', $currentUser->id);

        // Assignee-only mode: only working on task, not creator or super admin
        if ($isAssignee && !$isCreator && !$isSuperAdmin) {
            $validated = $request->validate([
                'assignee_remarks' => 'nullable|string',
                'status'           => 'nullable|in:todo,in_progress,completed',
            ]);

            $newStatus = $validated['status'] ?? $task->status;

            // Restriction: Once completed, only Super Admin can change status back
            if ($task->status === 'completed' && $newStatus !== 'completed') {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only Super Admin can change the status of a completed task.',
                    ], 403);
                }
                return back()->with('error', 'Only Super Admin can change the status of a completed task.');
            }

            $task->update([
                'assignee_remarks' => $validated['assignee_remarks'] ?? $task->assignee_remarks,
                'status'           => $newStatus,
                'completed_at'     => ($newStatus === 'completed' && !$task->completed_at)
                    ? now()
                    : ($newStatus !== 'completed' ? null : $task->completed_at),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Task remarks and status updated successfully!',
                    'task'    => $task->load(['category', 'creator', 'assignees']),
                ]);
            }

            return redirect()->route('tasks.index', ['date' => $task->due_at?->toDateString()])
                ->with('success', 'Task remarks and status updated successfully!');
        }

        // Full edit mode for Creator or Super Admin:
        $validated = $request->validate([
            'category_id'        => 'required|exists:task_categories,id',
            'description'        => 'nullable|string',
            'creator_remarks'    => 'nullable|string',
            'creator_rating'     => 'nullable|in:good,bad',
            'admin_photo'        => ['nullable', 'image', 'max:200'], // Max 200 KB
            'remove_admin_photo' => 'nullable|boolean',
            'assignee_remarks'   => 'nullable|string',
            'status'             => 'nullable|in:todo,in_progress,completed',
            'priority'           => 'required|in:low,medium,high,urgent',
            'due_at'             => 'required|date',
            'assignee_ids'       => 'required|array|min:1',
            'assignee_ids.*'     => 'exists:users,id',
        ], [
            'admin_photo.max' => 'Photo size must not exceed 200 KB.',
        ]);

        $category            = TaskCategory::findOrFail($validated['category_id']);
        $existingAssigneeIds = $task->assignees->pluck('id')->toArray();
        $newStatus           = $validated['status'] ?? $task->status;

        // Restriction: Once completed, only Super Admin can change status back
        if ($task->status === 'completed' && $newStatus !== 'completed') {
            if (!$isSuperAdmin) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only Super Admin can change the status of a completed task.',
                    ], 403);
                }
                return back()->with('error', 'Only Super Admin can change the status of a completed task.');
            }
        }

        $updateData = [
            'title'            => $category->name,
            'category_id'      => $validated['category_id'],
            'description'      => $validated['description'] ?? null,
            'creator_remarks'  => $validated['creator_remarks'] ?? null,
            'creator_rating'   => $validated['creator_rating'] ?? null,
            'assignee_remarks' => $validated['assignee_remarks'] ?? null,
            'status'           => $newStatus,
            'priority'         => $validated['priority'],
            'due_at'           => Carbon::parse($validated['due_at']),
            'completed_at'     => ($newStatus === 'completed' && !$task->completed_at)
                ? now()
                : ($newStatus !== 'completed' ? null : $task->completed_at),
        ];

        // Handle Admin Photo
        if ($request->boolean('remove_admin_photo')) {
            if ($task->admin_photo && Storage::disk('public')->exists($task->admin_photo)) {
                Storage::disk('public')->delete($task->admin_photo);
            }
            $updateData['admin_photo'] = null;
        } elseif ($request->hasFile('admin_photo')) {
            if ($task->admin_photo && Storage::disk('public')->exists($task->admin_photo)) {
                Storage::disk('public')->delete($task->admin_photo);
            }
            $updateData['admin_photo'] = $request->file('admin_photo')->store('task_photos', 'public');
        }

        $task->update($updateData);

        $task->assignees()->sync($validated['assignee_ids']);

        // Notify newly added assignees
        $newAssigneeIds = array_diff($validated['assignee_ids'], $existingAssigneeIds);
        if (!empty($newAssigneeIds)) {
            $newAssignees = User::whereIn('id', $newAssigneeIds)
                ->where('id', '!=', auth()->id())
                ->get();
            if ($newAssignees->isNotEmpty()) {
                Notification::send($newAssignees, new TaskAssignedNotification($task, auth()->user()));
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully!',
                'task'    => $task->load(['category', 'creator', 'assignees']),
            ]);
        }

        return redirect()->route('tasks.index', ['date' => $task->due_at?->toDateString()])
            ->with('success', 'Task updated successfully!');
    }

    // -----------------------------------------------------------------------
    // Update Status (AJAX)
    // -----------------------------------------------------------------------

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTaskAccess('edit', $task);

        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,completed',
        ]);

        $newStatus    = $validated['status'];
        $isSuperAdmin = auth()->user()->isSuperAdmin();

        // Restriction: Once completed, only Super Admin can revert status
        if ($task->status === 'completed' && $newStatus !== 'completed') {
            if (!$isSuperAdmin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Super Admin can change the status of a completed task.',
                ], 403);
            }
        }

        $oldStatus = $task->status;
        $task->update([
            'status'       => $newStatus,
            'completed_at' => ($newStatus === 'completed' && !$task->completed_at)
                ? now()
                : ($newStatus !== 'completed' ? null : $task->completed_at),
        ]);

        // Notify creator if status changed by someone else
        if ($task->created_by && (int)$task->created_by !== (int)auth()->id()) {
            $creator = User::find($task->created_by);
            if ($creator) {
                $creator->notify(new TaskStatusUpdatedNotification($task, auth()->user(), $oldStatus, $newStatus));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated to ' . ucfirst(str_replace('_', ' ', $newStatus)),
            'status'  => $newStatus,
        ]);
    }

    // -----------------------------------------------------------------------
    // Get Task Data — for edit modal (AJAX)
    // -----------------------------------------------------------------------

    public function getData(Task $task): JsonResponse
    {
        $this->authorizeTaskAccess('view', $task);
        $task->load('assignees');

        $currentUser    = auth()->user();
        $isSuperAdmin   = $currentUser->isSuperAdmin();
        $isCreator      = (int) $currentUser->id === (int) $task->created_by;
        $isAssignee     = $task->assignees->contains('id', $currentUser->id);
        $canEditAll     = $isSuperAdmin || $isCreator;
        $isAssigneeOnly = $isAssignee && !$canEditAll;

        return response()->json([
            'task' => [
                'id'               => $task->id,
                'category_id'      => $task->category_id,
                'category_name'    => $task->category?->name ?? $task->title,
                'description'      => $task->description,
                'creator_remarks'  => $task->creator_remarks,
                'creator_rating'   => $task->creator_rating,
                'admin_photo'      => $task->admin_photo,
                'admin_photo_url'  => $task->admin_photo_url,
                'assignee_remarks' => $task->assignee_remarks,
                'status'           => $task->status,
                'priority'         => $task->priority,
                'priority_label'   => ucfirst($task->priority),
                'due_at'           => $task->due_at?->format('Y-m-d H:i:s'),
                'formatted_due_at' => $task->due_at?->format('d M Y, h:i A'),
                'creator_name'     => $task->creator?->name ?? 'Admin',
                'created_by'       => $task->created_by,
                'assignee_ids'     => $task->assignees->pluck('id')->toArray(),
                'is_super_admin'   => $isSuperAdmin,
                'is_creator'       => $isCreator,
                'is_assignee_only' => $isAssigneeOnly,
                'can_edit_all'     => $canEditAll,
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // Add Comment
    // -----------------------------------------------------------------------

    public function storeComment(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTaskAccess('view', $task);

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'comment' => $validated['comment'],
        ]);

        $commenterIsCreator = (int) auth()->id() === (int) $task->created_by;

        if ($commenterIsCreator) {
            // Creator commenting → notify all assignees
            $usersToNotify = $task->assignees->where('id', '!=', auth()->id())->values();
        } else {
            // Assignee commenting → notify creator + all other assignees
            $involvedUserIds = array_unique(array_merge(
                [$task->created_by],
                $task->assignees->pluck('id')->toArray()
            ));
            $usersToNotify = User::whereIn('id', $involvedUserIds)
                ->where('id', '!=', auth()->id())
                ->get();
        }

        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new TaskCommentAddedNotification($task, $comment, auth()->user()));
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Comment posted!',
            'comment'  => [
                'id'         => $comment->id,
                'comment'    => $comment->comment,
                'created_at' => $comment->created_at->diffForHumans(),
                'user'       => ['name' => auth()->user()->name, 'id' => auth()->id()],
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // Delete
    // -----------------------------------------------------------------------

    public function destroy(Request $request, Task $task)
    {
        $this->authorizeTaskAccess('delete', $task);

        $task->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Task deleted!']);
        }

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully!');
    }

    // -----------------------------------------------------------------------
    // Authorization Helper
    // -----------------------------------------------------------------------

    protected function authorizeTaskAccess(string $action, ?Task $task = null): void
    {
        /** @var User $user */
        $user = auth()->user();

        if (!$user) {
            abort(401);
        }

        if ($user->isSuperAdmin()) {
            return;
        }

        $permission = "tasks.{$action}";
        if (!$user->hasPermission($permission)) {
            abort(403, "You do not have permission to {$action} tasks.");
        }

        if ($task) {
            $isCreator  = (int) $task->created_by === (int) $user->id;
            $isAssignee = $task->assignees->contains('id', $user->id);

            if (!$isCreator && !$isAssignee) {
                abort(403, 'You do not have access to this task.');
            }
        }
    }
}
