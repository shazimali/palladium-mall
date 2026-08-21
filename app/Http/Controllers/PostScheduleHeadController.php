<?php

namespace App\Http\Controllers;

use App\Models\PostScheduleHead;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostScheduleHeadController extends Controller
{
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('post_schedule_heads.view')) {
            abort(403, 'Unauthorized action.');
        }

        $heads = PostScheduleHead::withCount('schedules')
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return view('post_schedule_heads.index', [
            'title' => 'Post Schedule Heads',
            'heads' => $heads,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('post_schedule_heads.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'color'       => ['required', 'string', 'in:blue,emerald,amber,purple,rose,indigo,cyan,gray'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        PostScheduleHead::create($validated);

        return redirect()->route('post-schedule-heads.index')->with('success', 'Post Schedule Head created successfully.');
    }

    public function update(Request $request, PostScheduleHead $postScheduleHead): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('post_schedule_heads.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'color'       => ['required', 'string', 'in:blue,emerald,amber,purple,rose,indigo,cyan,gray'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $postScheduleHead->update($validated);

        return redirect()->route('post-schedule-heads.index')->with('success', 'Post Schedule Head updated successfully.');
    }

    public function destroy(PostScheduleHead $postScheduleHead): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('post_schedule_heads.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $postScheduleHead->delete();

        return redirect()->route('post-schedule-heads.index')->with('success', 'Post Schedule Head deleted successfully.');
    }
}
