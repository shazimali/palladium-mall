<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\UtilityReading;
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
        $occupiedUnits = Unit::where('status', 'occupied')->count();
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
        $soldUnits = Unit::where('status', 'sold')->count();

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
        $utilitiesDue = UtilityReading::where('month', $currentMonth)
            ->where('status', 'unpaid')
            ->sum('bill_amount');

        return view('dashboard.index', [
            'title' => 'Dashboard',
            // Stat cards
            'totalUnits' => $totalUnits,
            'occupiedUnits' => $occupiedUnits,
            'vacantUnits' => $vacantUnits,
            'rentDue' => $rentDue,
            'utilitiesDue' => $utilitiesDue,
            // Chart data
            'chartMonths' => $chartMonths,
            'chartDue' => $chartDue,
            'chartPaid' => $chartPaid,
            // Donut
            'occupiedUnits' => $occupiedUnits,
            'vacantUnits' => $vacantUnits,
            'soldUnits' => $soldUnits,
            // Tables
            'recentPayments' => $recentPayments,
            'expiringAgreements' => $expiringAgreements,
            'overduePayments' => $overduePayments,
            // Occupancy rate
            'occupancyRate' => $totalUnits > 0
                ? round(($occupiedUnits / $totalUnits) * 100, 1)
                : 0,
        ]);
    }
}