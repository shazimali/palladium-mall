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

        // 2. Calculate Flat/Shop Status Grids (3 Rows)
        // Row 1: Overall
        $overallTotal = Unit::count();
        $overallRented = Unit::where('status', 'rented')->count();
        $overallVacant = Unit::where('status', 'vacant')->count();

        // Row 2: PM Mall Managed (is_self = false)
        $pmMallTotal = Unit::where('is_self', false)->count();
        $pmMallRented = Unit::where('is_self', false)->where('status', 'rented')->count();
        $pmMallVacant = Unit::where('is_self', false)->where('status', 'vacant')->count();

        // Row 3: Other Owned Units (is_self = true)
        $otherOwnedTotal = Unit::where('is_self', true)->count();
        $otherOwnedRented = Unit::where('is_self', true)->where('status', 'rented')->count();
        $otherOwnedVacant = Unit::where('is_self', true)->where('status', 'vacant')->count();

        return view('dashboard.index', [
            'title' => 'Dashboard',
            'financialWidgets' => $financialWidgets,
            'currentMonthLabel' => $currentMonthLabel,
            'selectedMonth' => $selectedMonthVal,
            
            'overall' => [
                'total' => $overallTotal,
                'rented' => $overallRented,
                'vacant' => $overallVacant,
            ],
            'pmMall' => [
                'total' => $pmMallTotal,
                'rented' => $pmMallRented,
                'vacant' => $pmMallVacant,
            ],
            'otherOwned' => [
                'total' => $otherOwnedTotal,
                'rented' => $otherOwnedRented,
                'vacant' => $otherOwnedVacant,
            ],
        ]);
    }

    /**
     * Show the detailed view of flats and shops grouped by floor and block.
     */
    public function unitsDetail(Request $request): View
    {
        $type = $request->input('type', 'pm_mall'); // 'pm_mall' or 'other_owned'
        $isSelf = $type === 'other_owned';

        // Fetch all units of this ownership type with floor, block, and area preloaded
        $units = Unit::where('is_self', $isSelf)
            ->with(['floor', 'block', 'area'])
            ->get();

        $stats = [
            'total' => $units->count(),
            'rented' => $units->where('status', 'rented')->count(),
            'vacant' => $units->where('status', 'vacant')->count(),
        ];

        // Retrieve floors and blocks in order to populate combos
        $floors = Floor::orderBy('id')->get();
        $blocks = Block::orderBy('id')->get();

        $structuredGrouped = [];
        foreach ($floors as $floor) {
            foreach ($blocks as $block) {
                $filtered = $units->filter(fn($u) => $u->floor_id == $floor->id && $u->block_id == $block->id);
                if ($filtered->isNotEmpty()) {
                    $structuredGrouped[$floor->name][$block->name] = $filtered->sortBy('unit_number');
                }
            }
        }

        // Add fallback for units without floor or block
        $noFloorOrBlock = $units->filter(fn($u) => is_null($u->floor_id) || is_null($u->block_id));
        if ($noFloorOrBlock->isNotEmpty()) {
            $structuredGrouped['Other']['Other'] = $noFloorOrBlock->sortBy('unit_number');
        }

        $typeLabel = $type === 'pm_mall' ? 'Palladium Mall Managed' : 'Other-Owned';

        return view('dashboard.units_detail', [
            'title' => $typeLabel . ' — Detail List',
            'typeLabel' => $typeLabel,
            'type' => $type,
            'grouped' => $structuredGrouped,
            'stats' => $stats,
        ]);
    }
}