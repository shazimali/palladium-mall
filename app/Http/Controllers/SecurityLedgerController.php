<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Services\SecurityLedgerService;
use App\Exports\SecurityLedgerExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SecurityLedgerController extends Controller
{
    protected SecurityLedgerService $ledgerService;

    public function __construct(SecurityLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function index(Request $request)
    {
        $data = $this->ledgerService->buildLedgerData($request);
        $units = Unit::orderBy('unit_number')->get(['id', 'unit_number', 'is_self']);

        return view('ledgers.security.index', array_merge($data, [
            'units' => $units,
        ]));
    }

    public function print(Request $request)
    {
        $request->merge(['paginate' => false]);
        $data = $this->ledgerService->buildLedgerData($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('ledgers.security.pdf', array_merge($data, [
            'isPrint' => true,
        ]))->setPaper('a4', 'landscape');

        return $pdf->stream('security_ledger_' . now()->format('Y_m_d') . '.pdf');
    }

    public function export(Request $request)
    {
        $request->merge(['paginate' => false]);
        $data = $this->ledgerService->buildLedgerData($request);

        return Excel::download(new SecurityLedgerExport($data), 'security_ledger_' . now()->format('Y_m_d') . '.xlsx');
    }
}
