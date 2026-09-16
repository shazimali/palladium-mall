<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AccountSummaryService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AccountSummaryExport;
use App\Exports\AccountSummaryDetailExport;

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

        return view('reports.account_summary', [
            'title' => 'Balance Sheet (as per Accounts)',
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

        $pdf = Pdf::loadView('reports.account_summary_pdf', [
            'title' => 'Balance Sheet (as per Accounts)',
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

    public function detail(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.account_summary_detail')) {
            abort(403, 'Unauthorized action.');
        }

        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->toDateString());
        $accountType = $request->input('account_type', 'all');

        $summary = $this->summaryService->getDetailedSummary($dateFrom, $dateTo, $accountType);
        $summary = $summary->groupBy('group');

        return view('reports.account_summary_detail', [
            'title' => 'Account Summary',
            'summary' => $summary,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'accountType' => $accountType,
        ]);
    }

    public function detailPdf(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.account_summary_detail')) {
            abort(403, 'Unauthorized action.');
        }

        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->toDateString());
        $accountType = $request->input('account_type', 'all');

        $summary = $this->summaryService->getDetailedSummary($dateFrom, $dateTo, $accountType);
        $summary = $summary->groupBy('group');

        $pdf = Pdf::loadView('reports.account_summary_detail_pdf', [
            'title' => 'Account Summary',
            'summary' => $summary,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'accountType' => $accountType,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('account_summary_detail_' . $dateFrom . '_to_' . $dateTo . '.pdf');
    }

    public function detailExcel(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.account_summary_detail')) {
            abort(403, 'Unauthorized action.');
        }

        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->toDateString());
        $accountType = $request->input('account_type', 'all');

        $summary = $this->summaryService->getDetailedSummary($dateFrom, $dateTo, $accountType);

        return Excel::download(new AccountSummaryDetailExport($summary, $dateFrom, $dateTo), 'account_summary_detail_' . $dateFrom . '_to_' . $dateTo . '.xlsx');
    }
}
