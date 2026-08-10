<?php

namespace App\Http\Controllers;

use App\Models\NotePad;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotePadController extends Controller
{
    /**
     * Display Google Keep style notes grid.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = NotePad::with('user');

        // Scoping: standard users see their own notes; super admins see all unless filtered
        if (!$user->isSuperAdmin()) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search filter
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('content', 'like', "%{$term}%");
            });
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        // Color filter
        if ($request->filled('color')) {
            $query->where('color', $request->color);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $allNotes = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->get();

        $pinnedNotes = $allNotes->where('is_pinned', true);
        $otherNotes  = $allNotes->where('is_pinned', false);

        return view('note_pads.index', [
            'title'       => 'Note Pad',
            'pinnedNotes' => $pinnedNotes,
            'otherNotes'  => $otherNotes,
            'totalCount'  => $allNotes->count(),
        ]);
    }

    /**
     * Store a newly created note/task.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => ['nullable', 'string', 'max:255'],
            'content'      => ['nullable', 'string'],
            'date'         => ['required', 'date'],
            'color'        => ['nullable', 'string', 'in:default,yellow,blue,green,pink,purple,orange'],
            'is_pinned'    => ['nullable', 'boolean'],
            'is_checklist' => ['nullable', 'boolean'],
            'checklist'    => ['nullable', 'array'],
            'checklist.*'  => ['nullable', 'string', 'max:500'],
        ]);

        $isChecklist = $request->boolean('is_checklist');
        $isPinned    = $request->boolean('is_pinned');
        $content     = $data['content'] ?? '';

        if ($isChecklist) {
            $rawItems = $request->input('checklist', []);
            $checklistItems = [];
            foreach ($rawItems as $itemText) {
                if (trim($itemText) !== '') {
                    $checklistItems[] = [
                        'text'      => trim($itemText),
                        'completed' => false,
                    ];
                }
            }
            $content = json_encode($checklistItems);
        }

        $note = NotePad::create([
            'user_id'      => auth()->id(),
            'title'        => $data['title'] ?? null,
            'content'      => $content,
            'date'         => $data['date'] ?? now()->toDateString(),
            'color'        => $data['color'] ?? 'default',
            'is_pinned'    => $isPinned,
            'is_checklist' => $isChecklist,
            'status'       => 'pending',
        ]);

        ActivityLog::log('create_note_pad', "Created Note Pad item #{$note->id}", $note);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Note created successfully.',
                'note'    => $note,
            ]);
        }

        return redirect()->route('note-pads.index')
            ->with('success', 'Note added successfully.');
    }

    /**
     * Update an existing note/task.
     */
    public function update(Request $request, NotePad $notePad)
    {
        $this->authorizeOwnerOrAdmin($notePad);

        $data = $request->validate([
            'title'        => ['nullable', 'string', 'max:255'],
            'content'      => ['nullable', 'string'],
            'date'         => ['required', 'date'],
            'color'        => ['nullable', 'string', 'in:default,yellow,blue,green,pink,purple,orange'],
            'is_pinned'    => ['nullable', 'boolean'],
            'is_checklist' => ['nullable', 'boolean'],
            'status'       => ['nullable', 'string', 'in:pending,completed'],
            'checklist'    => ['nullable', 'array'],
            'checklist.*'  => ['nullable', 'array'],
        ]);

        $isChecklist = $request->has('is_checklist') ? $request->boolean('is_checklist') : $notePad->is_checklist;
        $isPinned    = $request->has('is_pinned') ? $request->boolean('is_pinned') : $notePad->is_pinned;
        $content     = $data['content'] ?? $notePad->content;

        if ($isChecklist && $request->has('checklist')) {
            $checklistItems = [];
            foreach ($request->input('checklist', []) as $item) {
                if (is_array($item) && !empty($item['text'])) {
                    $checklistItems[] = [
                        'text'      => trim($item['text']),
                        'completed' => !empty($item['completed']),
                    ];
                }
            }
            $content = json_encode($checklistItems);
        }

        $notePad->update([
            'title'        => $data['title'] ?? $notePad->title,
            'content'      => $content,
            'date'         => $data['date'] ?? $notePad->date,
            'color'        => $data['color'] ?? $notePad->color,
            'is_pinned'    => $isPinned,
            'is_checklist' => $isChecklist,
            'status'       => $data['status'] ?? $notePad->status,
        ]);

        ActivityLog::log('update_note_pad', "Updated Note Pad item #{$notePad->id}", $notePad);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Note updated successfully.',
                'note'    => $notePad,
            ]);
        }

        return redirect()->route('note-pads.index')
            ->with('success', 'Note updated successfully.');
    }

    /**
     * Toggle pin status via AJAX.
     */
    public function togglePin(NotePad $notePad): JsonResponse
    {
        $this->authorizeOwnerOrAdmin($notePad);

        $notePad->is_pinned = !$notePad->is_pinned;
        $notePad->save();

        return response()->json([
            'success'   => true,
            'is_pinned' => $notePad->is_pinned,
            'message'   => $notePad->is_pinned ? 'Note pinned to top.' : 'Note unpinned.',
        ]);
    }

    /**
     * Toggle a single task checkbox within a checklist note via AJAX.
     */
    public function toggleTask(Request $request, NotePad $notePad): JsonResponse
    {
        $this->authorizeOwnerOrAdmin($notePad);

        $index = (int)$request->input('index');
        $items = $notePad->checklist_items;

        if (isset($items[$index])) {
            $items[$index]['completed'] = !$items[$index]['completed'];
            $notePad->checklist_items = $items;

            // Auto-mark note as completed if all checklist items are completed
            $total     = count($items);
            $completed = count(array_filter($items, fn($i) => !empty($i['completed'])));
            $notePad->status = ($total > 0 && $completed === $total) ? 'completed' : 'pending';

            $notePad->save();
        }

        return response()->json([
            'success'         => true,
            'completed_count' => $notePad->completed_tasks_count,
            'total_count'     => $notePad->total_tasks_count,
            'status'          => $notePad->status,
        ]);
    }

    /**
     * Soft-delete a note.
     */
    public function destroy(Request $request, NotePad $notePad)
    {
        $this->authorizeOwnerOrAdmin($notePad);

        $notePad->delete();

        ActivityLog::log('delete_note_pad', "Deleted Note Pad item #{$notePad->id}", null);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Note deleted.',
            ]);
        }

        return redirect()->route('note-pads.index')
            ->with('success', 'Note deleted.');
    }

    /**
     * Authorize user owner or admin access.
     */
    private function authorizeOwnerOrAdmin(NotePad $notePad): void
    {
        if (!auth()->user()->isSuperAdmin() && $notePad->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
