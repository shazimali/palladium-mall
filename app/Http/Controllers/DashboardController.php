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
        $monthInput = $request->input('month');
        if ($monthInput) {
            try {
                $parsedDate = Carbon::parse($monthInput)->startOfMonth();
            } catch (\Exception $e) {
                $parsedDate = Carbon::now()->startOfMonth();
            }
        } else {
            $parsedDate = Carbon::now()->startOfMonth();
        }
        $currentMonth = $parsedDate->toDateString();
        $currentMonthLabel = $parsedDate->format('F Y');
        $selectedMonthVal = $parsedDate->format('Y-m');

        $today = Carbon::today();

        // 1. Calculate Financial Widgets (Current Month)
        $currentMonthPayments = Payment::where('month', $currentMonth)->get();

        $rentPayments = $currentMonthPayments->where('type', 'rent');
        $depositPayments = $currentMonthPayments->where('type', 'security_deposit');
        $servicePayments = $currentMonthPayments->whereNotIn('type', ['rent', 'security_deposit']);

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
        $grandDue = (float) $currentMonthPayments->sum('amount');
        $grandPaid = (float) $currentMonthPayments->sum('amount_paid');

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

        // 2. Calculate Flat/Shop/Office Status Grids (3 Rows) matching /units logic
        $allUnits = Unit::with('otherTenant')->get();
        $pmMallUnits = $allUnits->filter(fn($u) => !$u->is_self);
        $otherOwnedUnits = $allUnits->filter(fn($u) => $u->is_self);

        return view('dashboard.index', [
            'title' => 'Dashboard',
            'financialWidgets' => $financialWidgets,
            'currentMonthLabel' => $currentMonthLabel,
            'selectedMonth' => $selectedMonthVal,
            'overall' => $this->buildUnitGroupStats($allUnits),
            'pmMall' => $this->buildUnitGroupStats($pmMallUnits),
            'otherOwned' => $this->buildUnitGroupStats($otherOwnedUnits),
        ]);
    }

    /**
     * Build detailed type breakdown (total, flat, shop, office) for rented, vacant, and total units.
     */
    private function buildUnitGroupStats($unitsCollection): array
    {
        $rentedUnits = $unitsCollection->filter(fn($u) => $u->status === 'rented' || ($u->is_self && $u->otherTenant));
        $vacantUnits = $unitsCollection->filter(fn($u) => $u->status === 'vacant' && !($u->is_self && $u->otherTenant));

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
     * Show the detailed view of flats and shops grouped by floor and block.
     */
    public function unitsDetail(Request $request): View
    {
        $type = $request->input('type', 'pm_mall'); // 'pm_mall' or 'other_owned'
        $status = $request->input('status'); // 'rented', 'vacant', or null
        $isSelf = $type === 'other_owned';

        // Fetch all units of this ownership type with floor, block, area, and otherTenant preloaded
        $allUnits = Unit::where('is_self', $isSelf)
            ->with(['floor', 'block', 'area', 'otherTenant'])
            ->get();

        $stats = [
            'total' => $allUnits->count(),
            'rented' => $allUnits->filter(fn($u) => $u->status === 'rented' || ($u->is_self && $u->otherTenant))->count(),
            'vacant' => $allUnits->filter(fn($u) => $u->status === 'vacant' && !($u->is_self && $u->otherTenant))->count(),
        ];

        // Filter units displayed in the grid if a specific status was selected
        $displayUnits = $allUnits;
        if ($status === 'rented') {
            $displayUnits = $allUnits->filter(fn($u) => $u->status === 'rented' || ($u->is_self && $u->otherTenant));
        } elseif ($status === 'vacant') {
            $displayUnits = $allUnits->filter(fn($u) => $u->status === 'vacant' && !($u->is_self && $u->otherTenant));
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

        $baseLabel = $type === 'pm_mall' ? 'Palladium Mall Managed' : 'Other-Owned';
        $typeLabel = $status ? ucfirst($status) . ' — ' . $baseLabel : $baseLabel;

        $rentedUnits = $allUnits->filter(fn($u) => $u->status === 'rented' || ($u->is_self && $u->otherTenant));
        $vacantUnits = $allUnits->filter(fn($u) => $u->status === 'vacant' && !($u->is_self && $u->otherTenant));

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
            'grouped' => $structuredGrouped,
            'stats' => $stats,
            'counts' => $counts,
        ]);
    }
}