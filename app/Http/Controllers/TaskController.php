<?php

namespace App\Http\Controllers;

use App\Models\Task;
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
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Display the Task Table view.
     */
    public function index(Request $request): View
    {
        $this->authorizeTaskAccess('view');

        /** @var User $currentUser */
        $currentUser = auth()->user();

        $query = Task::with(['creator', 'assignees', 'comments.user']);

        // Role-based visibility: Super admin sees all, others only see their created or assigned tasks
        if (!$currentUser->isSuperAdmin()) {
            $query->where(function ($q) use ($currentUser) {
                $q->where('created_by', $currentUser->id)
                  ->orWhereHas('assignees', fn($aq) => $aq->where('users.id', $currentUser->id));
            });
        }

        // Filters
        if ($request->filled('assigned_to')) {
            $assignedTo = $request->query('assigned_to');
            if ($assignedTo === 'me') {
                $query->whereHas('assignees', fn($q) => $q->where('users.id', $currentUser->id));
            } else {
                $query->whereHas('assignees', fn($q) => $q->where('users.id', $assignedTo));
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $allTasks = $query->orderBy('order_column')->orderByDesc('created_at')->get();

        // Kanban / status summary counts based on user-scoped tasks
        $countQuery = Task::query();
        if (!$currentUser->isSuperAdmin()) {
            $countQuery->where(function ($q) use ($currentUser) {
                $q->where('created_by', $currentUser->id)
                  ->orWhereHas('assignees', fn($aq) => $aq->where('users.id', $currentUser->id));
            });
        }
        $allForCount = $countQuery->select('status')->get();
        $kanban = [
            'todo'        => $allForCount->where('status', 'todo')->values(),
            'in_progress' => $allForCount->where('status', 'in_progress')->values(),
            'completed'   => $allForCount->where('status', 'completed')->values(),
        ];

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('tasks.index', [
            'title'        => 'Task Management',
            'allTasks'     => $allTasks,
            'kanban'       => $kanban,
            'users'        => $users,
            'filters'      => $request->only(['assigned_to', 'priority', 'search', 'status']),
            'activeTaskId' => $request->query('task_id'),
        ]);
    }

    /**
     * Show form to create a new task.
     */
    public function create(): View
    {
        $this->authorizeTaskAccess('create');

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('tasks.create', [
            'title' => 'Create New Task',
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request)
    {
        $this->authorizeTaskAccess('create');

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'priority'     => 'required|in:low,medium,high,urgent',
            'due_at'       => 'nullable|date',
            'assignee_ids' => 'required|array|min:1',
            'assignee_ids.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $task = Task::create([
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'priority'    => $validated['priority'],
                'status'      => 'todo',
                'due_at'      => $validated['due_at'] ? Carbon::parse($validated['due_at']) : null,
                'created_by'  => auth()->id(),
            ]);

            $task->assignees()->sync($validated['assignee_ids']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
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
                'task'    => $task->load(['creator', 'assignees', 'comments.user']),
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task created and assigned successfully!');
    }

    /**
     * Show form to edit an existing task.
     */
    public function edit(Task $task): View
    {
        $this->authorizeTaskAccess('edit', $task);

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('tasks.edit', [
            'title' => 'Edit Task: ' . $task->title,
            'task'  => $task->load('assignees'),
            'users' => $users,
        ]);
    }

    /**
     * Update task details.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorizeTaskAccess('edit', $task);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'status'       => 'nullable|in:todo,in_progress,completed',
            'priority'     => 'required|in:low,medium,high,urgent',
            'due_at'       => 'nullable|date',
            'assignee_ids' => 'required|array|min:1',
            'assignee_ids.*' => 'exists:users,id',
        ]);

        $existingAssigneeIds = $task->assignees->pluck('id')->toArray();
        $newStatus = $validated['status'] ?? $task->status;

        $task->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status'      => $newStatus,
            'priority'    => $validated['priority'],
            'due_at'      => $validated['due_at'] ? Carbon::parse($validated['due_at']) : null,
            'completed_at' => ($newStatus === 'completed' && !$task->completed_at) ? now() : ($newStatus !== 'completed' ? null : $task->completed_at),
        ]);

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
                'task'    => $task->load(['creator', 'assignees', 'comments.user']),
            ]);
        }

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully!');
    }

    /**
     * Update task status & Kanban order position via AJAX.
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $this->authorizeTaskAccess('edit', $task);

        $validated = $request->validate([
            'status'       => 'required|in:todo,in_progress,completed',
            'order_column' => 'nullable|integer',
        ]);

        $oldStatus = $task->status;
        $newStatus = $validated['status'];

        $task->status = $newStatus;

        if (isset($validated['order_column'])) {
            $task->order_column = $validated['order_column'];
        }

        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $task->completed_at = now();
        } elseif ($newStatus !== 'completed') {
            $task->completed_at = null;
        }

        $task->save();

        // Notify involved users if status changed
        if ($oldStatus !== $newStatus) {
            $involvedUserIds = array_unique(array_merge(
                [$task->created_by],
                $task->assignees->pluck('id')->toArray()
            ));

            $usersToNotify = User::whereIn('id', $involvedUserIds)
                ->where('id', '!=', auth()->id())
                ->get();

            if ($usersToNotify->isNotEmpty()) {
                Notification::send($usersToNotify, new TaskStatusUpdatedNotification($task, auth()->user(), $oldStatus, $newStatus));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Task status updated!',
            'task'    => $task->fresh(['creator', 'assignees', 'comments.user']),
        ]);
    }

    /**
     * Add a comment to a task.
     */
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

        // Send notification to involved users (creator + assignees)
        $involvedUserIds = array_unique(array_merge(
            [$task->created_by],
            $task->assignees->pluck('id')->toArray()
        ));

        $usersToNotify = User::whereIn('id', $involvedUserIds)
            ->where('id', '!=', auth()->id())
            ->get();

        if ($usersToNotify->isNotEmpty()) {
            Notification::send($usersToNotify, new TaskCommentAddedNotification($task, $comment, auth()->user()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment posted!',
            'comment' => $comment->load('user'),
        ]);
    }

    /**
     * Delete a task.
     */
    public function destroy(Request $request, Task $task)
    {
        $this->authorizeTaskAccess('delete', $task);

        $task->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Task deleted!']);
        }

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully!');
    }

    /**
     * Permission and task ownership/assignment authorization helper.
     */
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

        // For non-super-admins, ensure they are either the creator or an assignee of the task
        if ($task) {
            $isCreator = (int) $task->created_by === (int) $user->id;
            $isAssignee = $task->assignees->contains('id', $user->id);

            if (!$isCreator && !$isAssignee) {
                abort(403, "You do not have access to this task.");
            }
        }
    }
}
