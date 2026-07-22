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

        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', now()->endOfMonth()->toDateString());

        // 1. Calculate mall financials for the date range (excluding refundable security deposits & landlord-owned unit payments)
        $tenantIncomeAll = (float) ReceivingVoucher::where('received_from_type', 'tenant')
            ->when($dateFrom, fn($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('date', '<=', $dateTo))
            ->sum('amount');

        $excludedAmount = (float) \DB::table('receiving_voucher_payments')
            ->join('payments', 'receiving_voucher_payments.payment_id', '=', 'payments.id')
            ->join('receiving_vouchers', 'receiving_voucher_payments.receiving_voucher_id', '=', 'receiving_vouchers.id')
            ->whereNull('receiving_vouchers.deleted_at')
            ->whereNull('payments.deleted_at')
            ->where('receiving_vouchers.received_from_type', 'tenant')
            ->where(function ($q) {
                $q->where('payments.type', 'security_deposit')
                  ->orWhere('payments.landlord_id', '>', 0);
            })
            ->when($dateFrom && $dateTo, function ($q) use ($dateFrom, $dateTo) {
                $q->where(function ($sq) use ($dateFrom, $dateTo) {
                    $sq->whereBetween('payments.month', [$dateFrom, $dateTo])
                       ->orWhereBetween('payments.due_date', [$dateFrom, $dateTo]);
                });
            })
            ->sum('receiving_voucher_payments.amount_allocated');

        $tenantIncome = max(0.00, $tenantIncomeAll - $excludedAmount);

        $partyIncome = (float) GeneralReceivingVoucher::query()
            ->when($dateFrom, fn($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('date', '<=', $dateTo))
            ->sum('amount');

        $totalIncome = $tenantIncome + $partyIncome;

        $totalExpenses = (float) Expense::query()
            ->when($dateFrom, fn($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('date', '<=', $dateTo))
            ->sum('amount');

        $netProfit = max(0.00, $totalIncome - $totalExpenses);

        // 2. Fetch all owners with calculated shares for the selected date range
        $owners = Owner::orderBy('name')->get();

        $ownersData = $owners->map(function ($owner) use ($netProfit, $dateFrom, $dateTo) {
            $profitShare = round($netProfit * ((float) $owner->partnership_percentage / 100), 2);

            $totalPaid = (float) $owner->withdrawals()
                ->when($dateFrom, fn($q) => $q->whereDate('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->whereDate('date', '<=', $dateTo))
                ->sum('amount');

            $dueAmount = max(0.00, round($profitShare - $totalPaid, 2));

            return [
                'id' => $owner->id,
                'name' => $owner->name,
                'partnership_percentage' => $owner->partnership_percentage,
                'profit_share' => $profitShare,
                'total_paid' => $totalPaid,
                'due_amount' => $dueAmount,
            ];
        });

        return view('reports.owner_dues', [
            'title' => 'Managing Owner Dues Statement',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'ownersData' => $ownersData,
        ]);
    }
}
