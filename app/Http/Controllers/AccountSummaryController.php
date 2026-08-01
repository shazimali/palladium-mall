<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AccountSummaryService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AccountSummaryExport;

class AccountSummaryController extends Controller
{
    protected $summaryService;

    public function __construct(AccountSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.account_summary')) {
            abort(403, 'Unauthorized action.');
        }

        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->toDateString());
        $accountType = $request->input('account_type', 'all');

        $summary = $this->summaryService->getSummary($dateFrom, $dateTo, $accountType);
        $summary = $summary->groupBy('group');

        return view('reports.account_summary', [
            'title' => 'Account Summary Report',
            'summary' => $summary,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'accountType' => $accountType,
        ]);
    }

    public function exportPdf(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.account_summary')) {
            abort(403, 'Unauthorized action.');
        }

        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->toDateString());
        $accountType = $request->input('account_type', 'all');

        $summary = $this->summaryService->getSummary($dateFrom, $dateTo, $accountType);
        $summary = $summary->groupBy('group');

        $pdf = Pdf::loadView('reports.account_summary_pdf', [
            'title' => 'Account Summary Report',
            'summary' => $summary,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'accountType' => $accountType,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('account_summary_' . $dateFrom . '_to_' . $dateTo . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.account_summary')) {
            abort(403, 'Unauthorized action.');
        }

        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->toDateString());
        $accountType = $request->input('account_type', 'all');

        $summary = $this->summaryService->getSummary($dateFrom, $dateTo, $accountType);

        return Excel::download(new AccountSummaryExport($summary, $dateFrom, $dateTo), 'account_summary_' . $dateFrom . '_to_' . $dateTo . '.xlsx');
    }
}
