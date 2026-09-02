<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Services\FlatShopLedgerService;
use App\Exports\FlatShopLedgerExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barrier\DomPDF\Facade\Pdf;

class FlatShopLedgerController extends Controller
{
    protected FlatShopLedgerService $ledgerService;

    public function __construct(FlatShopLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function index(Request $request)
    {
        $data = $this->ledgerService->buildLedgerData($request);
        $units = Unit::orderBy('unit_number')->get(['id', 'unit_number', 'is_self']);

        return view('ledgers.flat_shop.index', array_merge($data, [
            'units' => $units,
        ]));
    }

    public function statement(Unit $unit, Request $request)
    {
        $data = $this->ledgerService->buildUnitStatement($unit, $request);

        return view('ledgers.flat_shop.statement', $data);
    }

    public function print(Request $request)
    {
        $request->merge(['paginate' => false]);
        $data = $this->ledgerService->buildLedgerData($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('ledgers.flat_shop.pdf', array_merge($data, [
            'isPrint' => true,
        ]))->setPaper('a4', 'landscape');

        return $pdf->stream('flat_shop_ledger_' . now()->format('Y_m_d') . '.pdf');
    }

    public function export(Request $request)
    {
        $request->merge(['paginate' => false]);
        $data = $this->ledgerService->buildLedgerData($request);

        return Excel::download(new FlatShopLedgerExport($data), 'flat_shop_ledger_' . now()->format('Y_m_d') . '.xlsx');
    }
}
