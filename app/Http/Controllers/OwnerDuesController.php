<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\ReceivingVoucher;
use App\Models\GeneralReceivingVoucher;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerDuesController extends Controller
{
    /**
     * Display a listing of managing owner dues.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        // 1. Calculate overall cumulative mall financials
        $tenantIncome = (float) ReceivingVoucher::where('received_from_type', 'tenant')->sum('amount');
        $partyIncome  = (float) GeneralReceivingVoucher::sum('amount');
        $totalIncome  = $tenantIncome + $partyIncome;

        $totalExpenses = (float) Expense::sum('amount');
        $netProfit     = max(0.00, $totalIncome - $totalExpenses);

        // 2. Fetch all owners with calculated shares
        $owners = Owner::orderBy('name')->get();

        $ownersData = $owners->map(function ($owner) use ($netProfit) {
            $profitShare = round($netProfit * ((float) $owner->partnership_percentage / 100), 2);
            $totalPaid   = $owner->totalPaid();
            $dueAmount   = $owner->pendingBalance();

            return [
                'id'                     => $owner->id,
                'name'                   => $owner->name,
                'partnership_percentage' => $owner->partnership_percentage,
                'profit_share'           => $profitShare,
                'total_paid'             => $totalPaid,
                'due_amount'             => $dueAmount,
            ];
        });

        return view('reports.owner_dues', [
            'title'         => 'Managing Owner Dues Statement',
            'totalIncome'  => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netProfit'     => $netProfit,
            'ownersData'    => $ownersData,
        ]);
    }
}
