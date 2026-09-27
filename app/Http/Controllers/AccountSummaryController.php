<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AccountSummaryService;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AccountSummaryController extends Controller
{
    protected $summaryService;

    private const ACCOUNT_TYPE_LABELS = [
        'all' => 'All Accounts',
        'asset' => 'Assets (Banks/Cash)',
        'liability' => 'Equity / Liabilities (Owners)',
        'receivable' => 'Security Deposits',
        'landlord_payable' => 'Landlord Payables',
        'party_due' => 'Party Dues',
        'jv_payable' => 'JV Payables',
    ];

    public function __construct(AccountSummaryService $summaryService)
    {
        $this->summaryService = $summaryService;
    }

    public function index(Request $request)
    {
        $this->authorizeReport('reports.account_summary');

        [$dateFrom, $dateTo, $accountType] = $this->filters($request);

        $summary = $this->summaryService->getSummary($dateFrom, $dateTo, $accountType);

        return view('reports.account_summary', [
            'title' => 'Balance Sheet (as per Accounts)',
            'summary' => $summary,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'accountType' => $accountType,
        ]);
    }

    public function detail(Request $request)
    {
        $this->authorizeReport('reports.account_summary_detail');

        [$dateFrom, $dateTo, $accountType] = $this->filters($request);

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

    // -----------------------------------------------------------------------
    // Print pages (PDF streamed in new window)
    // -----------------------------------------------------------------------

    public function print(Request $request)
    {
        $this->authorizeReport('reports.account_summary');

        [$dateFrom, $dateTo, $accountType] = $this->filters($request);

        $summary = $this->summaryService->getSummary($dateFrom, $dateTo, $accountType);

        $summaryCards = [
            ['label' => 'Total Payables', 'value' => 'Rs. ' . number_format($summary->sum('payable'), 2), 'color' => 's-red'],
            ['label' => 'Total Receivables', 'value' => 'Rs. ' . number_format($summary->sum('receivable'), 2), 'color' => 's-green'],
            ['label' => 'Net Balance', 'value' => 'Rs. ' . number_format($summary->sum('closing'), 2), 'color' => 's-purple'],
        ];

        return Pdf::loadView('reports.account_summary_print', [
            'pageTitle' => 'Balance Sheet (as per Accounts)',
            'filterChips' => $this->filterChips($dateFrom, $dateTo, $accountType),
            'summaryCards' => $summaryCards,
            'summary' => $summary,
        ])
            ->setPaper('a4', 'landscape')
            ->stream('account_summary_' . now()->format('Y_m_d') . '.pdf');
    }

    public function detailPrint(Request $request)
    {
        $this->authorizeReport('reports.account_summary_detail');

        [$dateFrom, $dateTo, $accountType] = $this->filters($request);

        $summary = $this->summaryService->getDetailedSummary($dateFrom, $dateTo, $accountType);

        $summaryCards = [
            ['label' => 'Opening Balance', 'value' => 'Rs. ' . number_format($summary->sum('opening'), 2), 'color' => 's-neutral'],
            ['label' => 'Total Debit', 'value' => 'Rs. ' . number_format($summary->sum('debit'), 2), 'color' => 's-red'],
            ['label' => 'Total Credit', 'value' => 'Rs. ' . number_format($summary->sum('credit'), 2), 'color' => 's-green'],
            ['label' => 'Closing Balance', 'value' => 'Rs. ' . number_format($summary->sum('closing'), 2), 'color' => 's-purple'],
        ];

        return Pdf::loadView('reports.account_summary_detail_print', [
            'pageTitle' => 'Account Summary',
            'filterChips' => $this->filterChips($dateFrom, $dateTo, $accountType),
            'summaryCards' => $summaryCards,
            'summary' => $summary->groupBy('group'),
        ])
            ->setPaper('a4', 'landscape')
            ->stream('account_summary_detail_' . now()->format('Y_m_d') . '.pdf');
    }

    private function authorizeReport(string $permission)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission($permission)) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function filters(Request $request): array
    {
        return [
            $request->input('date_from', Carbon::now()->startOfMonth()->toDateString()),
            $request->input('date_to', Carbon::now()->endOfMonth()->toDateString()),
            $request->input('account_type', 'all'),
        ];
    }

    private function filterChips($dateFrom, $dateTo, $accountType): array
    {
        $chips = [];
        if ($dateFrom)
            $chips[] = ['label' => 'Date From', 'value' => Carbon::parse($dateFrom)->format('d M Y')];
        if ($dateTo)
            $chips[] = ['label' => 'Date To', 'value' => Carbon::parse($dateTo)->format('d M Y')];
        if ($accountType && $accountType !== 'all')
            $chips[] = ['label' => 'Account Type', 'value' => self::ACCOUNT_TYPE_LABELS[$accountType] ?? ucfirst(str_replace('_', ' ', $accountType))];

        return $chips;
    }
}
