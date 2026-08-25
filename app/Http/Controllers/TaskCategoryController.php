<?php

namespace App\Http\Controllers;

use App\Models\TaskCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskCategoryController extends Controller
{
    /**
     * List all categories (used in the manage-categories modal via AJAX).
     */
    public function index(): JsonResponse
    {
        $this->authorizeSuperAdminOrPermission();

        $categories = TaskCategory::orderBy('name')->get(['id', 'name', 'is_active']);

        return response()->json(['categories' => $categories]);
    }

    /**
     * Create a new category.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeSuperAdminOrPermission();

        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:task_categories,name',
        ]);

        $category = TaskCategory::create([
            'name'      => $validated['name'],
            'is_active' => true,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Category created successfully.',
            'category' => $category,
        ]);
    }

    /**
     * Toggle active / inactive status.
     */
    public function toggleStatus(TaskCategory $taskCategory): JsonResponse
    {
        $this->authorizeSuperAdminOrPermission();

        $taskCategory->update(['is_active' => !$taskCategory->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $taskCategory->is_active,
            'message'   => $taskCategory->is_active ? 'Category activated.' : 'Category deactivated.',
        ]);
    }

    /**
     * Only super-admins or users with tasks.create permission can manage categories.
     */
    protected function authorizeSuperAdminOrPermission(): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (!$user) {
            abort(401);
        }

        if ($user->isSuperAdmin() || $user->hasPermission('tasks.create')) {
            return;
        }

        abort(403, 'Unauthorized.');
    }
}
