<?php

namespace App\Http\Controllers;

use App\Models\PostSchedule;
use App\Models\PostScheduleHead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostScheduleController extends Controller
{
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('post_schedules.view')) {
            abort(403, 'Unauthorized action.');
        }

        $currentDay = strtolower($request->get('day', strtolower(now()->format('l'))));
        if (!in_array($currentDay, array_keys(PostSchedule::DAYS)) && $currentDay !== 'all') {
            $currentDay = 'monday';
        }

        $query = PostSchedule::with(['head', 'user'])
            ->when($currentDay !== 'all', fn($q) => $q->where('day_of_week', $currentDay))
            ->when($request->post_schedule_head_id, fn($q) => $q->where('post_schedule_head_id', $request->post_schedule_head_id))
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where(function ($sub) use ($term) {
                    $sub->where('employee_name', 'like', "%{$term}%")
                        ->orWhere('task_title', 'like', "%{$term}%")
                        ->orWhere('location', 'like', "%{$term}%")
                        ->orWhere('duties', 'like', "%{$term}%")
                        ->orWhereHas('head', fn($h) => $h->where('name', 'like', "%{$term}%"));
                });
            })
            ->orderBy('sort_order')
            ->orderBy('start_time');

        $schedules = $query->paginate(25)->withQueryString();
        $heads = PostScheduleHead::active()->ordered()->get();
        $users = User::orderBy('name')->get();

        // Calculate count per day for the day selector badges
        $dayCounts = [];
        foreach (array_keys(PostSchedule::DAYS) as $d) {
            $dayCounts[$d] = PostSchedule::where('day_of_week', $d)->count();
        }
        $dayCounts['all'] = PostSchedule::count();

        return view('post_schedules.index', [
            'title'      => 'Post Schedule',
            'schedules'  => $schedules,
            'heads'      => $heads,
            'users'      => $users,
            'currentDay' => $currentDay,
            'dayCounts'  => $dayCounts,
            'days'       => PostSchedule::DAYS,
        ]);
    }

    public function create(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('post_schedules.create')) {
            abort(403, 'Unauthorized action.');
        }

        $heads = PostScheduleHead::active()->ordered()->get();
        $users = User::orderBy('name')->get();
        $defaultDay = $request->get('day', strtolower(now()->format('l')));

        return view('post_schedules.create', [
            'title'      => 'Add Post Schedule Task',
            'heads'      => $heads,
            'users'      => $users,
            'defaultDay' => $defaultDay,
            'days'       => PostSchedule::DAYS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('post_schedules.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'post_schedule_head_id' => ['required', 'exists:post_schedule_heads,id'],
            'day_of_week'           => ['required', 'string', 'in:' . implode(',', array_keys(PostSchedule::DAYS))],
            'employee_name'         => ['required', 'string', 'max:255'],
            'user_id'               => ['nullable', 'exists:users,id'],
            'location'              => ['nullable', 'string', 'max:255'],
            'start_time'            => ['nullable', 'date_format:H:i'],
            'end_time'              => ['nullable', 'date_format:H:i'],
            'task_title'            => ['required', 'string', 'max:255'],
            'duties'                => ['nullable', 'string', 'max:5000'],
            'notes'                 => ['nullable', 'string', 'max:2000'],
            'sort_order'            => ['nullable', 'integer', 'min:0'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        PostSchedule::create($validated);

        return redirect()->route('post-schedules.index', ['day' => $validated['day_of_week']])
            ->with('success', 'Post Schedule created successfully.');
    }

    public function edit(PostSchedule $postSchedule): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('post_schedules.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $heads = PostScheduleHead::active()->ordered()->get();
        $users = User::orderBy('name')->get();

        return view('post_schedules.edit', [
            'title'        => 'Edit Post Schedule Task',
            'postSchedule' => $postSchedule,
            'heads'        => $heads,
            'users'        => $users,
            'days'         => PostSchedule::DAYS,
        ]);
    }

    public function update(Request $request, PostSchedule $postSchedule): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('post_schedules.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'post_schedule_head_id' => ['required', 'exists:post_schedule_heads,id'],
            'day_of_week'           => ['required', 'string', 'in:' . implode(',', array_keys(PostSchedule::DAYS))],
            'employee_name'         => ['required', 'string', 'max:255'],
            'user_id'               => ['nullable', 'exists:users,id'],
            'location'              => ['nullable', 'string', 'max:255'],
            'start_time'            => ['nullable', 'date_format:H:i'],
            'end_time'              => ['nullable', 'date_format:H:i'],
            'task_title'            => ['required', 'string', 'max:255'],
            'duties'                => ['nullable', 'string', 'max:5000'],
            'notes'                 => ['nullable', 'string', 'max:2000'],
            'sort_order'            => ['nullable', 'integer', 'min:0'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $postSchedule->update($validated);

        return redirect()->route('post-schedules.index', ['day' => $validated['day_of_week']])
            ->with('success', 'Post Schedule updated successfully.');
    }

    public function destroy(PostSchedule $postSchedule): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('post_schedules.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $day = $postSchedule->day_of_week;
        $postSchedule->delete();

        return redirect()->route('post-schedules.index', ['day' => $day])
            ->with('success', 'Post Schedule deleted successfully.');
    }

    /**
     * Copy schedule items from source day to multiple target days.
     */
    public function copyDays(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('post_schedules.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'source_day'  => ['required', 'string', 'in:' . implode(',', array_keys(PostSchedule::DAYS))],
            'target_days' => ['required', 'array', 'min:1'],
            'target_days.*' => ['string', 'in:' . implode(',', array_keys(PostSchedule::DAYS))],
            'replace_existing' => ['nullable', 'boolean'],
        ]);

        $sourceSchedules = PostSchedule::where('day_of_week', $validated['source_day'])->get();

        if ($sourceSchedules->isEmpty()) {
            return back()->with('error', "No schedule entries found on {$validated['source_day']} to copy.");
        }

        $copiedCount = 0;
        foreach ($validated['target_days'] as $targetDay) {
            if ($targetDay === $validated['source_day']) continue;

            if ($request->boolean('replace_existing')) {
                PostSchedule::where('day_of_week', $targetDay)->delete();
            }

            foreach ($sourceSchedules as $source) {
                PostSchedule::create([
                    'post_schedule_head_id' => $source->post_schedule_head_id,
                    'day_of_week'           => $targetDay,
                    'employee_name'         => $source->employee_name,
                    'user_id'               => $source->user_id,
                    'location'              => $source->location,
                    'start_time'            => $source->start_time,
                    'end_time'              => $source->end_time,
                    'task_title'            => $source->task_title,
                    'duties'                => $source->duties,
                    'notes'                 => $source->notes,
                    'sort_order'            => $source->sort_order,
                    'is_active'             => $source->is_active,
                ]);
                $copiedCount++;
            }
        }

        return redirect()->route('post-schedules.index', ['day' => $validated['source_day']])
            ->with('success', "Successfully copied {$copiedCount} schedule duties to selected days.");
    }

    /**
     * Daily printable duty assignment roster for office employees.
     */
    public function printDaily(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('post_schedules.print') && !auth()->user()->hasPermission('post_schedules.view')) {
            abort(403, 'Unauthorized action.');
        }

        $selectedDay = strtolower($request->get('day', strtolower(now()->format('l'))));
        if (!in_array($selectedDay, array_keys(PostSchedule::DAYS)) && $selectedDay !== 'all') {
            $selectedDay = strtolower(now()->format('l'));
        }

        $reportDate = $request->get('report_date', now()->format('d M Y'));

        $query = PostSchedule::with(['head', 'user'])
            ->active()
            ->when($selectedDay !== 'all', fn($q) => $q->where('day_of_week', $selectedDay))
            ->when($request->post_schedule_head_id, fn($q) => $q->where('post_schedule_head_id', $request->post_schedule_head_id))
            ->orderBy('post_schedule_head_id')
            ->orderBy('sort_order')
            ->orderBy('start_time');

        $schedules = $query->get();
        $groupedSchedules = $schedules->groupBy('post_schedule_head_id');
        $heads = PostScheduleHead::active()->ordered()->get()->keyBy('id');

        return view('post_schedules.print_daily', [
            'title'            => 'Daily Post Schedule & Task Sheet',
            'selectedDay'      => $selectedDay,
            'dayName'          => PostSchedule::DAYS[$selectedDay] ?? 'All Days',
            'reportDate'       => $reportDate,
            'groupedSchedules' => $groupedSchedules,
            'heads'            => $heads,
            'totalEntries'     => $schedules->count(),
            'filters'          => $request->all(),
        ]);
    }
}
