<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Landlord;
use App\Models\ActivityLog;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        $currentMonth = Carbon::now()->startOfMonth()->toDateString();
        $today = Carbon::today();

        // ── Stat cards ────────────────────────────────────────────────
        $totalUnits = Unit::count();
        $rentedUnits = Unit::where('status', 'rented')->count();
        $vacantUnits = Unit::where('status', 'vacant')->count();
        $rentDue = Payment::where('month', $currentMonth)
            ->where('type', 'rent')
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum(\DB::raw('amount - amount_paid'));

        // ── Monthly rent chart — last 6 months ────────────────────────
        $chartMonths = [];
        $chartDue = [];
        $chartPaid = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->startOfMonth();
            $monthKey = $month->toDateString();
            $label = $month->format('M Y');

            $chartMonths[] = $label;
            $chartDue[] = (float) Payment::where('month', $monthKey)
                ->where('type', 'rent')
                ->sum('amount');
            $chartPaid[] = (float) Payment::where('month', $monthKey)
                ->where('type', 'rent')
                ->sum('amount_paid');
        }

        // ── Occupancy donut ───────────────────────────────────────────
        $selfUnits = Unit::where('status', 'self')->count();

        // ── Recent payments ───────────────────────────────────────────
        $recentPayments = Payment::with(['tenant', 'unit'])
            ->latest('updated_at')
            ->take(8)
            ->get();

        // ── Expiring agreements (next 30 days) ────────────────────────
        $expiringAgreements = Agreement::with(['tenant', 'unit'])
            ->where('status', 'active')
            ->whereBetween('end_date', [$today, $today->copy()->addDays(30)])
            ->orderBy('end_date')
            ->take(5)
            ->get();

        // ── Overdue payments ──────────────────────────────────────────
        $overduePayments = Payment::with(['tenant', 'unit'])
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', $today)
            ->orderBy('due_date')
            ->take(6)
            ->get();

        // ── Utility summary this month ────────────────────────────────
        $utilitiesDue = Payment::where('month', $currentMonth)
            ->whereIn('type', ['electricity', 'water', 'gas'])
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum(\DB::raw('amount - amount_paid'));

        // ── Landlord portfolios ───────────────────────────────────────
        $landlords = Landlord::with('units')
            ->withCount('units')
            ->get()
            ->map(function ($landlord) {
                $unitIds = $landlord->units->pluck('id')->toArray();
                $earnings = Payment::whereIn('unit_id', $unitIds)
                    ->whereIn('status', ['paid', 'partial'])
                    ->sum('amount_paid');
                
                return [
                    'id' => $landlord->id,
                    'name' => $landlord->name,
                    'units_count' => $landlord->units_count,
                    'earnings' => (float) $earnings,
                ];
            })
            ->sortByDesc('units_count')
            ->take(5)
            ->values();

        // ── Recent activity logs ──────────────────────────────────────
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', [
            'title' => 'Dashboard',
            // Stat cards
            'totalUnits' => $totalUnits,
            'rentedUnits' => $rentedUnits,
            'vacantUnits' => $vacantUnits,
            'rentDue' => $rentDue,
            'utilitiesDue' => $utilitiesDue,
            // Chart data
            'chartMonths' => $chartMonths,
            'chartDue' => $chartDue,
            'chartPaid' => $chartPaid,
            // Donut
            'rentedUnits' => $rentedUnits,
            'vacantUnits' => $vacantUnits,
            'selfUnits' => $selfUnits,
            // Tables
            'recentPayments' => $recentPayments,
            'expiringAgreements' => $expiringAgreements,
            'overduePayments' => $overduePayments,
            'landlords' => $landlords,
            'recentActivities' => $recentActivities,
            // Occupancy rate
            'occupancyRate' => $totalUnits > 0
                ? round(($rentedUnits / $totalUnits) * 100, 1)
                : 0,
        ]);
    }
}