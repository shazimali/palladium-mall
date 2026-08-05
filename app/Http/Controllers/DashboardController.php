<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Landlord;
use App\Models\ActivityLog;
use App\Models\Floor;
use App\Models\Block;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $fromDateInput = $request->input('from_date');
        $toDateInput = $request->input('to_date');
        $monthInput = $request->input('month');

        if ($fromDateInput && $toDateInput) {
            try {
                $fromDate = Carbon::parse($fromDateInput)->startOfDay();
                $toDate = Carbon::parse($toDateInput)->endOfDay();
            } catch (\Exception $e) {
                $fromDate = Carbon::now()->startOfMonth()->startOfDay();
                $toDate = Carbon::now()->endOfMonth()->endOfDay();
            }
        } elseif ($monthInput) {
            try {
                $parsedDate = Carbon::parse($monthInput)->startOfMonth();
                $fromDate = $parsedDate->copy()->startOfMonth()->startOfDay();
                $toDate = $parsedDate->copy()->endOfMonth()->endOfDay();
            } catch (\Exception $e) {
                $fromDate = Carbon::now()->startOfMonth()->startOfDay();
                $toDate = Carbon::now()->endOfMonth()->endOfDay();
            }
        } else {
            $fromDate = Carbon::now()->startOfMonth()->startOfDay();
            $toDate = Carbon::now()->endOfMonth()->endOfDay();
        }

        $fromDateStr = $fromDate->toDateString();
        $toDateStr = $toDate->toDateString();

        if ($fromDate->format('Y-m-d') === $fromDate->copy()->startOfMonth()->format('Y-m-d') &&
            $toDate->format('Y-m-d') === $fromDate->copy()->endOfMonth()->format('Y-m-d') &&
            $fromDate->format('Y-m') === $toDate->format('Y-m')) {
            $dateLabel = $fromDate->format('F Y');
        } else {
            $dateLabel = $fromDate->format('d M Y') . ' — ' . $toDate->format('d M Y');
        }

        // 1. Calculate Financial Widgets (Filtered by Date Range)
        $payments = Payment::whereBetween('month', [$fromDateStr, $toDateStr])->get();

        $rentPayments = $payments->where('type', 'rent');
        $depositPayments = $payments->where('type', 'security_deposit');
        $servicePayments = $payments->whereNotIn('type', ['rent', 'security_deposit']);

        // Rent sums
        $rentDue = (float) $rentPayments->sum('amount');
        $rentPaid = (float) $rentPayments->sum('amount_paid');

        // Security Deposit sums
        $depositDue = (float) $depositPayments->sum('amount');
        $depositPaid = (float) $depositPayments->sum('amount_paid');

        // Services sums
        $servicesDue = (float) $servicePayments->sum('amount');
        $servicesPaid = (float) $servicePayments->sum('amount_paid');

        // Grand Total sums
        $grandDue = (float) $payments->sum('amount');
        $grandPaid = (float) $payments->sum('amount_paid');

        $financialWidgets = [
            'grand_total' => [
                'label' => 'Grand Total Summary',
                'due' => $grandDue,
                'paid' => $grandPaid,
                'unpaid' => $grandDue - $grandPaid,
                'gradient' => 'linear-gradient(135deg, #465fff 0%, #2a31d8 100%)',
                'icon' => '📊',
            ],
            'rent' => [
                'label' => 'Rent Summary',
                'due' => $rentDue,
                'paid' => $rentPaid,
                'unpaid' => $rentDue - $rentPaid,
                'gradient' => 'linear-gradient(135deg, #f04438 0%, #912018 100%)',
                'icon' => '🔑',
            ],
            'services' => [
                'label' => 'Services Summary',
                'due' => $servicesDue,
                'paid' => $servicesPaid,
                'unpaid' => $servicesDue - $servicesPaid,
                'gradient' => 'linear-gradient(135deg, #7a5af8 0%, #2a31d8 100%)',
                'icon' => '🛠️',
            ],
            'security_deposit' => [
                'label' => 'Security Deposit',
                'due' => $depositDue,
                'paid' => $depositPaid,
                'unpaid' => $depositDue - $depositPaid,
                'gradient' => 'linear-gradient(135deg, #a855f7 0%, #701a75 100%)',
                'icon' => '🛡️',
            ],
        ];

        // 2. Calculate Flat/Shop/Office Status Grids as per Date Filter Range
        $allUnits = Unit::with(['otherTenant', 'agreements', 'payments', 'otherTenantHistory'])->get();
        $pmMallUnits = $allUnits->filter(fn($u) => !$u->is_self);
        $otherOwnedUnits = $allUnits->filter(fn($u) => $u->is_self);

        $expiringAgreements = Agreement::expiringSoon(60)
            ->with(['tenant', 'unit'])
            ->orderBy('end_date', 'asc')
            ->get();

        return view('dashboard.index', [
            'title' => 'Dashboard',
            'financialWidgets' => $financialWidgets,
            'currentMonthLabel' => $dateLabel,
            'fromDate' => $fromDateStr,
            'toDate' => $toDateStr,
            'expiringAgreements' => $expiringAgreements,
            'overall' => $this->buildUnitGroupStats($allUnits, $fromDateStr, $toDateStr),
            'pmMall' => $this->buildUnitGroupStats($pmMallUnits, $fromDateStr, $toDateStr),
            'otherOwned' => $this->buildUnitGroupStats($otherOwnedUnits, $fromDateStr, $toDateStr),
        ]);
    }

    /**
     * Build detailed type breakdown (total, flat, shop, office) for rented, vacant, and total units.
     */
    private function buildUnitGroupStats($unitsCollection, string $fromDateStr, string $toDateStr): array
    {
        $rentedUnits = $unitsCollection->filter(fn($u) => $this->isUnitRentedInRange($u, $fromDateStr, $toDateStr));
        $vacantUnits = $unitsCollection->filter(fn($u) => !$this->isUnitRentedInRange($u, $fromDateStr, $toDateStr));

        return [
            'total' => $unitsCollection->count(),
            'flat' => $unitsCollection->where('type', 'flat')->count(),
            'shop' => $unitsCollection->where('type', 'shop')->count(),
            'office' => $unitsCollection->where('type', 'office')->count(),

            'rented' => $rentedUnits->count(),
            'rented_flat' => $rentedUnits->where('type', 'flat')->count(),
            'rented_shop' => $rentedUnits->where('type', 'shop')->count(),
            'rented_office' => $rentedUnits->where('type', 'office')->count(),

            'vacant' => $vacantUnits->count(),
            'vacant_flat' => $vacantUnits->where('type', 'flat')->count(),
            'vacant_shop' => $vacantUnits->where('type', 'shop')->count(),
            'vacant_office' => $vacantUnits->where('type', 'office')->count(),
        ];
    }

    /**
     * Helper to evaluate whether a unit was Rented during a given date range.
     */
    private function isUnitRentedInRange($u, string $fromDateStr, string $toDateStr): bool
    {
        // 1. For Other-Owned units (is_self = true): Strictly based on currently attached Other Tenant
        if ($u->is_self) {
            return $u->otherTenant !== null;
        }

        // 2. For PM Mall managed units (is_self = false)
        if ($u->agreements && $u->agreements->isNotEmpty()) {
            $hasAgreement = $u->agreements->contains(function ($a) use ($fromDateStr, $toDateStr) {
                // Draft or cancelled agreements do NOT represent an active tenancy
                if (in_array($a->status, ['draft', 'cancelled'])) {
                    return false;
                }

                if (!$a->start_date) return false;
                $startDate = $a->start_date instanceof Carbon ? $a->start_date->format('Y-m-d') : substr((string) $a->start_date, 0, 10);
                $endDate = $a->end_date ? ($a->end_date instanceof Carbon ? $a->end_date->format('Y-m-d') : substr((string) $a->end_date, 0, 10)) : null;

                $overlapsStart = $startDate <= $toDateStr;
                $overlapsEnd = is_null($endDate) || $endDate >= $fromDateStr;

                return $overlapsStart && $overlapsEnd;
            });

            if ($hasAgreement) {
                return true;
            }
        }

        if ($u->payments && $u->payments->isNotEmpty()) {
            $hasPayment = $u->payments->contains(function ($p) use ($fromDateStr, $toDateStr) {
                if (!$p->month) return false;
                $pMonth = $p->month instanceof Carbon ? $p->month->format('Y-m-d') : substr((string) $p->month, 0, 10);
                return $pMonth >= $fromDateStr && $pMonth <= $toDateStr;
            });

            if ($hasPayment) {
                return true;
            }
        }

        return $u->status === 'rented';
    }

    /**
     * Show the detailed view of flats and shops grouped by floor and block.
     */
    public function unitsDetail(Request $request): View
    {
        $type = $request->input('type', 'all'); // 'all', 'pm_mall', or 'other_owned'
        $status = $request->input('status'); // 'rented', 'vacant', or null

        $fromDateInput = $request->input('from_date');
        $toDateInput = $request->input('to_date');
        if ($fromDateInput && $toDateInput) {
            try {
                $fromDateStr = Carbon::parse($fromDateInput)->startOfDay()->toDateString();
                $toDateStr = Carbon::parse($toDateInput)->endOfDay()->toDateString();
            } catch (\Exception $e) {
                $fromDateStr = Carbon::now()->startOfMonth()->toDateString();
                $toDateStr = Carbon::now()->endOfMonth()->toDateString();
            }
        } else {
            $fromDateStr = Carbon::now()->startOfMonth()->toDateString();
            $toDateStr = Carbon::now()->endOfMonth()->toDateString();
        }

        // Fetch units based on type filter ('all', 'pm_mall', or 'other_owned')
        $query = Unit::with(['floor', 'block', 'area', 'otherTenant', 'agreements', 'payments', 'otherTenantHistory']);

        if ($type === 'other_owned') {
            $query->where('is_self', true);
            $baseLabel = 'Other-Owned';
        } elseif ($type === 'pm_mall') {
            $query->where('is_self', false);
            $baseLabel = 'Palladium Mall Managed';
        } else {
            $type = 'all';
            $baseLabel = 'All Units';
        }

        $allUnits = $query->get();

        $rentedUnits = $allUnits->filter(fn($u) => $this->isUnitRentedInRange($u, $fromDateStr, $toDateStr));
        $vacantUnits = $allUnits->filter(fn($u) => !$this->isUnitRentedInRange($u, $fromDateStr, $toDateStr));
        $rentedUnitIds = $rentedUnits->pluck('id')->toArray();

        $stats = [
            'total' => $allUnits->count(),
            'rented' => $rentedUnits->count(),
            'vacant' => $vacantUnits->count(),
        ];

        // Filter units displayed in the grid if a specific status was selected
        $displayUnits = $allUnits;
        if ($status === 'rented') {
            $displayUnits = $rentedUnits;
        } elseif ($status === 'vacant') {
            $displayUnits = $vacantUnits;
        }

        // Retrieve floors and blocks in order to populate combos
        $floors = Floor::orderBy('id')->get();
        $blocks = Block::orderBy('id')->get();

        $structuredGrouped = [];
        foreach ($floors as $floor) {
            foreach ($blocks as $block) {
                $filtered = $displayUnits->filter(fn($u) => $u->floor_id == $floor->id && $u->block_id == $block->id);
                if ($filtered->isNotEmpty()) {
                    $structuredGrouped[$floor->name][$block->name] = $filtered->sortBy('unit_number');
                }
            }
        }

        // Add fallback for units without floor or block
        $noFloorOrBlock = $displayUnits->filter(fn($u) => is_null($u->floor_id) || is_null($u->block_id));
        if ($noFloorOrBlock->isNotEmpty()) {
            $structuredGrouped['Other']['Other'] = $noFloorOrBlock->sortBy('unit_number');
        }

        $typeLabel = $status ? ucfirst($status) . ' — ' . $baseLabel : $baseLabel;

        $counts = [
            'total' => $allUnits->count(),
            'flats' => $allUnits->where('type', 'flat')->count(),
            'shops' => $allUnits->where('type', 'shop')->count(),
            'offices' => $allUnits->where('type', 'office')->count(),
            'rented_flats' => $rentedUnits->where('type', 'flat')->count(),
            'rented_shops' => $rentedUnits->where('type', 'shop')->count(),
            'rented_offices' => $rentedUnits->where('type', 'office')->count(),
            'vacant_flats' => $vacantUnits->where('type', 'flat')->count(),
            'vacant_shops' => $vacantUnits->where('type', 'shop')->count(),
            'vacant_offices' => $vacantUnits->where('type', 'office')->count(),
        ];

        return view('dashboard.units_detail', [
            'title' => $typeLabel . ' — Detail List',
            'typeLabel' => $typeLabel,
            'type' => $type,
            'status' => $status,
            'fromDate' => $fromDateStr,
            'toDate' => $toDateStr,
            'rentedUnitIds' => $rentedUnitIds,
            'grouped' => $structuredGrouped,
            'stats' => $stats,
            'counts' => $counts,
        ]);
    }
}