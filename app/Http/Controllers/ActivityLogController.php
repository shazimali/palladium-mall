<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::with('user')->latest();

        // 1. Filter by User
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 2. Filter by Action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // 3. Search Description / metadata
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('user_agent', 'like', "%{$search}%");
            });
        }

        // 4. Filter by Date range
        if ($request->filled('date_from')) {
            try {
                $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                $query->where('created_at', '>=', $dateFrom);
            } catch (\Exception $e) {}
        }

        if ($request->filled('date_to')) {
            try {
                $dateTo = Carbon::parse($request->date_to)->endOfDay();
                $query->where('created_at', '<=', $dateTo);
            } catch (\Exception $e) {}
        }

        $logs = $query->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get();

        // List of distinct actions for filter dropdown
        $actions = ActivityLog::distinct()->pluck('action')->toArray();

        return view('activity_logs.index', [
            'logs' => $logs,
            'users' => $users,
            'actions' => $actions,
            'filters' => $request->all(),
        ]);
    }
}
