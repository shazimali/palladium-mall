<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Owner;
use App\Models\PaymentAccount;
use App\Models\ExpenseHead;
use App\Models\Landlord;
use App\Models\Party;
use App\Services\SecurityLedgerService;
use App\Services\FlatShopLedgerService;
use App\Support\LedgerTypeRegistry;
use App\Support\VoucherLinkResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Pagination\LengthAwarePaginator;

class AllLedgersController extends Controller
{
    public function __construct(
        protected LedgerController $ledgerController,
        protected LandlordLedgerController $landlordController,
        protected PartyLedgerController $partyController,
        protected SecurityLedgerService $securityService,
        protected FlatShopLedgerService $flatShopService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorizeLedger();

        $canViewLandlord = auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('landlords.view');

        $ledgerType = $request->query('ledger_type', 'tenant');
        if (!LedgerTypeRegistry::isValid($ledgerType)) {
            $ledgerType = 'tenant';
        }
        if ($ledgerType === 'landlord' && !$canViewLandlord) {
            abort(403, 'Unauthorized action.');
        }

        $base = [
            'hasSelection' => false,
            'rows' => collect(),
            'allRows' => collect(),
            'columns' => [],
            'summaryCards' => [],
            'filterChips' => [],
            'paginator' => null,
            'printRoute' => null,
            'pdfRoute' => null,
            'excelRoute' => null,
            'manageRoute' => null,
            'footerBalanceOverride' => null,
        ];

        $result = match ($ledgerType) {
            'tenant' => $this->buildTenantLedgerView($request),
            'owner' => $this->buildOwnerLedgerView($request),
            'payment_account' => $this->buildAccountLedgerView($request),
            'expense' => $this->buildExpenseLedgerView($request),
            'landlord' => $this->buildLandlordLedgerView($request),
            'security' => $this->buildSecurityLedgerView($request),
            'flat_shop' => $this->buildFlatShopLedgerView($request),
            'party' => $this->buildPartyLedgerView($request),
        };

        return view('ledgers.all.index', array_merge($base, $result, [
            'ledgerType' => $ledgerType,
            'canViewLandlord' => $canViewLandlord,
            'units' => Unit::with(['tenant', 'otherTenant'])->orderBy('unit_number')->get(),
            'owners' => Owner::orderBy('name')->get(),
            'accounts' => PaymentAccount::where('is_active', true)->orderBy('name')->get(),
            'expenseHeads' => ExpenseHead::orderBy('name')->get(),
            'landlords' => $canViewLandlord ? Landlord::orderBy('name')->get() : collect(),
            'parties' => Party::orderBy('name')->get(),
        ]));
    }

    /**
     * Redirects to the source ledger's own print/pdf/excel route rather than
     * duplicating export logic — keeps output byte-for-byte identical to the
     * original standalone pages.
     */
    public function print(Request $request)
    {
        return $this->redirectToSourceRoute($request, 'print');
    }

    public function pdf(Request $request)
    {
        return $this->redirectToSourceRoute($request, 'pdf');
    }

    public function excel(Request $request)
    {
        return $this->redirectToSourceRoute($request, 'excel');
    }

    private function redirectToSourceRoute(Request $request, string $action)
    {
        $ledgerType = $request->query('ledger_type');
        if (!LedgerTypeRegistry::isValid((string) $ledgerType)) {
            abort(404);
        }

        if ($ledgerType === 'landlord' && !auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('landlords.view')) {
            abort(403, 'Unauthorized action.');
        }

        $sourceRoutes = [
            'tenant' => ['print' => 'ledgers.tenant.print', 'pdf' => 'ledgers.tenant.pdf', 'excel' => 'ledgers.tenant.excel'],
            'owner' => ['print' => 'ledgers.owner.print', 'pdf' => 'ledgers.owner.pdf', 'excel' => 'ledgers.owner.excel'],
            'payment_account' => ['print' => 'ledgers.payment-account.print', 'pdf' => 'ledgers.payment-account.pdf', 'excel' => 'ledgers.payment-account.excel'],
            'expense' => ['print' => 'ledgers.expense.print', 'pdf' => 'ledgers.expense.pdf', 'excel' => 'ledgers.expense.excel'],
            'landlord' => ['print' => 'landlord_ledgers.print', 'pdf' => 'landlord_ledgers.pdf', 'excel' => 'landlord_ledgers.excel'],
            // Security/Flat-Shop only have a PDF-streaming print route and an Excel export route.
            'security' => ['print' => 'ledgers.security.print', 'pdf' => 'ledgers.security.print', 'excel' => 'ledgers.security.export'],
            'flat_shop' => ['print' => 'ledgers.flat_shop.print', 'pdf' => 'ledgers.flat_shop.print', 'excel' => 'ledgers.flat_shop.export'],
            // Party Ledger only has a print route — no PDF/Excel export exists for it.
            'party' => ['print' => 'ledgers.party.print', 'pdf' => null, 'excel' => null],
        ];

        $routeName = $sourceRoutes[$ledgerType][$action] ?? null;

        if (!$routeName) {
            abort(404);
        }

        $query = $request->query();
        unset($query['ledger_type']);

        return redirect()->route($routeName, $query);
    }

    private function authorizeLedger(): void
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('ledgers.view')) {
            abort(403, 'Unauthorized action.');
        }
    }

    // -------------------------------------------------------------------------
    // Per-type adapters — each copies the normalization already proven in the
    // source controller's print*()/print() method, just returning an array
    // instead of a View.
    // -------------------------------------------------------------------------

    private function buildTenantLedgerView(Request $request): array
    {
        $unitId = $request->query('unit_id');
        if (!$unitId) {
            return ['hasSelection' => false];
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $includeSecurityDeposit = $request->boolean('include_security_deposit', false);

        $ledgerData = $this->ledgerController->getTenantLedgerData($unitId, $dateFrom, $dateTo, $includeSecurityDeposit);
        $unit = $ledgerData['unit'];
        $tenant = $unit->tenant ?? $unit->otherTenant;

        $filterChips = [
            ['label' => 'Flat / Shop', 'value' => $unit->unit_number . ($tenant ? ' — ' . $tenant->name : '')],
        ];
        if ($dateFrom) {
            $filterChips[] = ['label' => 'Date From', 'value' => Carbon::parse($dateFrom)->format('d M Y')];
        }
        if ($dateTo) {
            $filterChips[] = ['label' => 'Date To', 'value' => Carbon::parse($dateTo)->format('d M Y')];
        }
        if ($includeSecurityDeposit) {
            $filterChips[] = ['label' => 'Security Deposit', 'value' => 'Included'];
        }

        $s = $ledgerData['summary'];
        $summaryCards = [
            ['label' => 'Total Billed / Charges', 'value' => 'Rs. ' . number_format($s['total_invoiced'], 2), 'color' => 's-blue'],
            ['label' => 'Total Paid / Credits', 'value' => 'Rs. ' . number_format($s['total_paid'], 2), 'color' => 's-green'],
            ['label' => 'Balance Outstanding', 'value' => 'Rs. ' . number_format($s['balance_due'], 2), 'color' => $s['balance_due'] > 0 ? 's-orange' : 's-neutral'],
        ];

        $columns = [
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'unit_number', 'label' => 'Flat/Shop'],
            ['key' => 'description', 'label' => 'Description'],
            ['key' => 'reference', 'label' => 'Ref / Voucher #'],
            ['key' => 'manual_voucher_no', 'label' => 'Manual Voucher #'],
            ['key' => 'debit', 'label' => 'Debit (Charged)', 'type' => 'debit', 'class' => 'text-right'],
            ['key' => 'credit', 'label' => 'Credit (Paid)', 'type' => 'credit', 'class' => 'text-right'],
            ['key' => 'running_balance', 'label' => 'Running Balance', 'type' => 'balance', 'class' => 'text-right'],
        ];

        $entries = $ledgerData['entries']->map(function ($e) {
            $modelType = match ($e['type'] ?? null) {
                'bill', 'legacy_payment' => 'payment',
                'voucher' => 'receiving_voucher',
                'voucher_payout' => 'payment_voucher',
                default => null,
            };
            $e['link'] = VoucherLinkResolver::resolve($modelType, $e['id'] ?? null);
            return $e;
        });

        return [
            'hasSelection' => true,
            'rows' => $entries,
            'allRows' => $entries,
            'columns' => $columns,
            'summaryCards' => $summaryCards,
            'filterChips' => $filterChips,
            'printRoute' => route('ledgers.all.print', array_merge($request->query(), ['ledger_type' => 'tenant'])),
            'pdfRoute' => route('ledgers.all.pdf', array_merge($request->query(), ['ledger_type' => 'tenant'])),
            'excelRoute' => route('ledgers.all.excel', array_merge($request->query(), ['ledger_type' => 'tenant'])),
        ];
    }

    private function buildOwnerLedgerView(Request $request): array
    {
        $ownerId = $request->query('owner_id');

        $year = (int) $request->query('year', Carbon::now()->year);
        $months = collect($request->query('months', []))
            ->map(fn($m) => (int) $m)
            ->filter(fn($m) => $m >= 1 && $m <= 12)
            ->unique()
            ->sort()
            ->values()
            ->all();
        if (empty($months)) {
            $months = [Carbon::now()->month];
        }

        if (!$ownerId) {
            return ['hasSelection' => false, 'year' => $year, 'months' => $months];
        }

        $ledgerData = $this->ledgerController->getOwnerLedgerData($ownerId, $year, $months);
        $owner = $ledgerData['owner'];

        $monthsLabel = collect($months)->map(fn($m) => Carbon::create($year, $m, 1)->format('F Y'))->implode(', ');

        $filterChips = [
            ['label' => 'Owner', 'value' => $owner->name . ($owner->email ? ' (' . $owner->email . ')' : '')],
            ['label' => 'Months', 'value' => $monthsLabel],
        ];

        $s = $ledgerData['summary'];
        $summaryCards = [
            ['label' => 'Total Payouts (Debits)', 'value' => 'Rs. ' . number_format($s['total_debit'], 2), 'color' => 's-blue'],
            ['label' => 'Total Deposits (Credits)', 'value' => 'Rs. ' . number_format($s['total_credit'], 2), 'color' => 's-green'],
            ['label' => 'Net Business Balance', 'value' => 'Rs. ' . number_format($s['net_balance'], 2), 'color' => 's-neutral'],
        ];

        $columns = [
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'voucher_no', 'label' => 'Voucher #'],
            ['key' => 'manual_voucher_no', 'label' => 'Manual Voucher #'],
            ['key' => 'account', 'label' => 'Account'],
            ['key' => 'reference', 'label' => 'Reference'],
            ['key' => 'notes', 'label' => 'Notes'],
            ['key' => 'debit', 'label' => 'Debit (Payout)', 'type' => 'debit', 'class' => 'text-right'],
            ['key' => 'credit', 'label' => 'Credit (Deposit)', 'type' => 'credit', 'class' => 'text-right'],
            ['key' => 'running_balance', 'label' => 'Running Balance', 'type' => 'balance', 'class' => 'text-right'],
        ];

        $entries = $ledgerData['entries']->map(function ($e) {
            $modelType = match ($e['type'] ?? null) {
                'payment_voucher', 'withdrawal', 'receiving_voucher' => $e['type'],
                default => null,
            };
            $e['link'] = VoucherLinkResolver::resolve($modelType, $e['id'] ?? null);
            return $e;
        });

        return [
            'hasSelection' => true,
            'year' => $year,
            'months' => $months,
            'rows' => $entries,
            'allRows' => $entries,
            'columns' => $columns,
            'summaryCards' => $summaryCards,
            'filterChips' => $filterChips,
            'printRoute' => route('ledgers.all.print', array_merge($request->query(), ['ledger_type' => 'owner'])),
            'pdfRoute' => route('ledgers.all.pdf', array_merge($request->query(), ['ledger_type' => 'owner'])),
            'excelRoute' => route('ledgers.all.excel', array_merge($request->query(), ['ledger_type' => 'owner'])),
        ];
    }

    private function buildAccountLedgerView(Request $request): array
    {
        $accountId = $request->query('payment_account_id');
        if (!$accountId) {
            return ['hasSelection' => false];
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $ledgerData = $this->ledgerController->getAccountLedgerData($accountId, $dateFrom, $dateTo);
        $account = $ledgerData['account'];

        $filterChips = [
            ['label' => 'Account', 'value' => $account->name . ' (' . ucfirst($account->type) . ')'],
        ];
        if ($dateFrom) {
            $filterChips[] = ['label' => 'Date From', 'value' => Carbon::parse($dateFrom)->format('d M Y')];
        }
        if ($dateTo) {
            $filterChips[] = ['label' => 'Date To', 'value' => Carbon::parse($dateTo)->format('d M Y')];
        }

        $s = $ledgerData['summary'];
        $summaryCards = [
            ['label' => 'Total Inflows (Debits)', 'value' => 'Rs. ' . number_format($s['total_inflow'], 2), 'color' => 's-green'],
            ['label' => 'Total Outflows (Credits)', 'value' => 'Rs. ' . number_format($s['total_outflow'], 2), 'color' => 's-blue'],
            ['label' => 'Account Running Balance', 'value' => 'Rs. ' . number_format($s['net_balance'], 2), 'color' => 's-neutral'],
        ];

        $columns = [
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'voucher_no', 'label' => 'Voucher #'],
            ['key' => 'manual_voucher_no', 'label' => 'Manual Voucher #'],
            ['key' => 'type', 'label' => 'Type', 'type' => 'badge'],
            ['key' => 'description', 'label' => 'Description / Ref'],
            ['key' => 'debit', 'label' => 'Debit', 'type' => 'debit', 'class' => 'text-right'],
            ['key' => 'credit', 'label' => 'Credit', 'type' => 'credit', 'class' => 'text-right'],
            ['key' => 'running_balance', 'label' => 'Running Balance', 'type' => 'balance', 'class' => 'text-right'],
        ];

        $entries = $ledgerData['entries']->map(function ($e) {
            $e['link'] = VoucherLinkResolver::resolve($e['model_type'] ?? null, $e['model_id'] ?? null);
            return $e;
        });

        return [
            'hasSelection' => true,
            'rows' => $entries,
            'allRows' => $entries,
            'columns' => $columns,
            'summaryCards' => $summaryCards,
            'filterChips' => $filterChips,
            'printRoute' => route('ledgers.all.print', array_merge($request->query(), ['ledger_type' => 'payment_account'])),
            'pdfRoute' => route('ledgers.all.pdf', array_merge($request->query(), ['ledger_type' => 'payment_account'])),
            'excelRoute' => route('ledgers.all.excel', array_merge($request->query(), ['ledger_type' => 'payment_account'])),
        ];
    }

    private function buildExpenseLedgerView(Request $request): array
    {
        $expenseHeadId = $request->query('expense_head_id');
        if (!$expenseHeadId) {
            return ['hasSelection' => false];
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $ledgerData = $this->ledgerController->getExpenseLedgerData($expenseHeadId, $dateFrom, $dateTo);
        $head = $ledgerData['head'];

        $filterChips = [
            ['label' => 'Expense Category', 'value' => $head ? ($head->name . ($head->code ? ' (Code: ' . $head->code . ')' : '')) : 'All Expenses'],
        ];
        if ($dateFrom) {
            $filterChips[] = ['label' => 'Date From', 'value' => Carbon::parse($dateFrom)->format('d M Y')];
        }
        if ($dateTo) {
            $filterChips[] = ['label' => 'Date To', 'value' => Carbon::parse($dateTo)->format('d M Y')];
        }

        $s = $ledgerData['summary'];
        $summaryCards = [
            ['label' => 'Total Spent Under Head', 'value' => 'Rs. ' . number_format($s['total_amount'], 2), 'color' => 's-amber'],
        ];

        $columns = [
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'voucher_no', 'label' => 'Voucher #'],
            ['key' => 'notes', 'label' => 'Spent On / Notes'],
        ];
        if ($ledgerData['is_all']) {
            $columns[] = ['key' => 'expense_head', 'label' => 'Expense Category'];
        }
        $columns[] = ['key' => 'payment_account', 'label' => 'Payment Account'];
        $columns[] = ['key' => 'reference', 'label' => 'Reference'];
        $columns[] = ['key' => 'amount', 'label' => 'Amount', 'type' => 'amount', 'class' => 'text-right'];
        $columns[] = ['key' => 'status', 'label' => 'Status', 'type' => 'status'];

        $entries = $ledgerData['entries']->map(function ($e) {
            $modelType = match ($e['type'] ?? null) {
                'Expense' => 'expense',
                'JV Voucher' => 'jv_voucher',
                default => null,
            };
            $e['link'] = VoucherLinkResolver::resolve($modelType, $e['id'] ?? null);
            return $e;
        });

        return [
            'hasSelection' => true,
            'rows' => $entries,
            'allRows' => $entries,
            'columns' => $columns,
            'summaryCards' => $summaryCards,
            'filterChips' => $filterChips,
            'printRoute' => route('ledgers.all.print', array_merge($request->query(), ['ledger_type' => 'expense'])),
            'pdfRoute' => route('ledgers.all.pdf', array_merge($request->query(), ['ledger_type' => 'expense'])),
            'excelRoute' => route('ledgers.all.excel', array_merge($request->query(), ['ledger_type' => 'expense'])),
        ];
    }

    private function buildLandlordLedgerView(Request $request): array
    {
        $landlordId = $request->query('landlord_id');
        if (!$landlordId) {
            return ['hasSelection' => false];
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $ledgerData = $this->landlordController->getLandlordLedgerData($landlordId, $dateFrom, $dateTo);
        $landlord = $ledgerData['landlord'];

        $summaryCards = [
            ['label' => 'Total Unit Value Owed', 'value' => 'Rs. ' . number_format($ledgerData['openingBalance'], 2), 'color' => 's-blue'],
            ['label' => 'Total Payments Received', 'value' => 'Rs. ' . number_format($ledgerData['totalPaid'], 2), 'color' => 's-green'],
            ['label' => 'Outstanding Balance', 'value' => 'Rs. ' . number_format($ledgerData['pendingBalance'], 2), 'color' => $ledgerData['pendingBalance'] > 0 ? 's-orange' : 's-neutral'],
        ];

        $columns = [
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'unit_number', 'label' => 'Flat/Shop'],
            ['key' => 'description', 'label' => 'Description'],
            ['key' => 'voucher_no', 'label' => 'Voucher / Ref #'],
            ['key' => 'manual_voucher_no', 'label' => 'Manual Voucher #'],
            ['key' => 'debit', 'label' => 'Debit (Payable)', 'type' => 'debit', 'class' => 'text-right'],
            ['key' => 'credit', 'label' => 'Credit (Paid)', 'type' => 'credit', 'class' => 'text-right'],
            ['key' => 'running_balance', 'label' => 'Running Balance', 'type' => 'balance', 'class' => 'text-right'],
        ];

        $entries = $ledgerData['entries']->map(function ($e) {
            $model = $e['model'] ?? null;
            $modelType = match (true) {
                $model instanceof \App\Models\GeneralReceivingVoucher => 'general_receiving_voucher',
                $model instanceof \App\Models\PaymentVoucher => 'payment_voucher',
                $model instanceof \App\Models\OtherOwnedRentPurchaseVoucher => 'other_owned_rent_purchase_voucher',
                default => null,
            };
            $e['link'] = $model ? VoucherLinkResolver::resolve($modelType, $model->id) : null;
            return $e;
        });

        return [
            'hasSelection' => true,
            'rows' => $entries,
            'allRows' => $entries,
            'columns' => $columns,
            'summaryCards' => $summaryCards,
            'filterChips' => [
                ['label' => 'Landlord', 'value' => $landlord->name],
                ['label' => 'Date From', 'value' => $dateFrom ?? 'All Time'],
                ['label' => 'Date To', 'value' => $dateTo ?? 'All Time'],
            ],
            'printRoute' => route('ledgers.all.print', array_merge($request->query(), ['ledger_type' => 'landlord'])),
            'pdfRoute' => route('ledgers.all.pdf', array_merge($request->query(), ['ledger_type' => 'landlord'])),
            'excelRoute' => route('ledgers.all.excel', array_merge($request->query(), ['ledger_type' => 'landlord'])),
        ];
    }

    private function buildSecurityLedgerView(Request $request): array
    {
        $data = $this->securityService->buildLedgerData($request);
        $s = $data['summary'];

        $attachLink = function ($row) {
            $row['link'] = VoucherLinkResolver::resolve($row['reference_type'] ?? null, $row['reference_id'] ?? null);
            return $row;
        };

        if ($data['rows'] instanceof LengthAwarePaginator) {
            $data['rows']->setCollection($data['rows']->getCollection()->map($attachLink));
        } else {
            $data['rows'] = $data['rows']->map($attachLink);
        }
        $data['all_rows'] = $data['all_rows']->map($attachLink);

        $summaryCards = [
            ['label' => 'Deposit Received', 'value' => 'Rs. ' . number_format($s['total_received'] ?? 0, 2), 'color' => 's-green'],
            ['label' => 'Deducted / Damage', 'value' => 'Rs. ' . number_format($s['total_deducted'] ?? 0, 2), 'color' => 's-amber'],
            ['label' => 'Deposit Refunded', 'value' => 'Rs. ' . number_format($s['total_refunded'] ?? 0, 2), 'color' => 's-orange'],
            ['label' => 'Currently Held', 'value' => 'Rs. ' . number_format($s['total_balance'] ?? 0, 2), 'color' => 's-neutral'],
        ];

        $columns = [
            ['key' => 'sr', 'label' => 'Sr #'],
            ['key' => 'date', 'label' => 'Date'],
            ['key' => 'unit_number', 'label' => 'Flat/Shop'],
            ['key' => 'tenant_name', 'label' => 'Tenant'],
            ['key' => 'type', 'label' => 'Transaction', 'type' => 'badge'],
            ['key' => 'reference', 'label' => 'Reference'],
            ['key' => 'manual_voucher_no', 'label' => 'Manual Voucher #'],
            ['key' => 'debit', 'label' => 'Debit', 'type' => 'debit', 'class' => 'text-right'],
            ['key' => 'credit', 'label' => 'Credit', 'type' => 'credit', 'class' => 'text-right'],
            ['key' => 'balance', 'label' => 'Balance', 'type' => 'balance', 'class' => 'text-right'],
        ];

        return [
            'hasSelection' => true,
            'rows' => $data['rows'],
            'allRows' => $data['all_rows'],
            'columns' => $columns,
            'summaryCards' => $summaryCards,
            'filterChips' => $data['filter_tags'],
            'paginator' => $data['rows'] instanceof LengthAwarePaginator ? $data['rows'] : null,
            // 'balance' is one running balance across all units; the footer shows the
            // combined total held by all ledgers (equal to the last row's balance).
            'footerBalanceOverride' => $s['total_balance'] ?? 0,
            'printRoute' => route('ledgers.all.print', array_merge($request->query(), ['ledger_type' => 'security'])),
            'pdfRoute' => null,
            'excelRoute' => route('ledgers.all.excel', array_merge($request->query(), ['ledger_type' => 'security'])),
        ];
    }

    private function buildFlatShopLedgerView(Request $request): array
    {
        $data = $this->flatShopService->buildLedgerData($request);
        $s = $data['summary'];
        $isSecurityDeposit = $data['is_security_deposit'];

        // Only the non-deposit rows carry a per-transaction Payment id; the
        // security-deposit mode rows are per-unit aggregates with no single
        // voucher to link to.
        if (!$isSecurityDeposit) {
            $attachLink = function ($row) {
                $row['link'] = !empty($row['payment_id']) ? VoucherLinkResolver::resolve('payment', $row['payment_id']) : null;
                return $row;
            };

            if ($data['rows'] instanceof LengthAwarePaginator) {
                $data['rows']->setCollection($data['rows']->getCollection()->map($attachLink));
            } else {
                $data['rows'] = $data['rows']->map($attachLink);
            }
            $data['all_rows'] = $data['all_rows']->map($attachLink);
        }

        if ($isSecurityDeposit) {
            $summaryCards = [
                ['label' => 'Required Deposit', 'value' => 'Rs. ' . number_format($s['total_required'] ?? 0, 2), 'color' => 's-blue'],
                ['label' => 'Collected Deposit', 'value' => 'Rs. ' . number_format($s['total_collected'] ?? 0, 2), 'color' => 's-green'],
                ['label' => 'Pending Deposit', 'value' => 'Rs. ' . number_format($s['total_pending'] ?? 0, 2), 'color' => 's-orange'],
                ['label' => 'Deductions / Damage', 'value' => 'Rs. ' . number_format($s['total_deductions'] ?? 0, 2), 'color' => 's-amber'],
                ['label' => 'Refunded Deposit', 'value' => 'Rs. ' . number_format($s['total_refunded'] ?? 0, 2), 'color' => 's-teal'],
                ['label' => 'Net Refundable', 'value' => 'Rs. ' . number_format($s['total_net_refundable'] ?? 0, 2), 'color' => 's-purple'],
            ];

            $columns = [
                ['key' => 'sr', 'label' => 'Sr'],
                ['key' => 'unit_number', 'label' => 'Flat / Shop'],
                ['key' => 'owner', 'label' => 'Owner'],
                ['key' => 'tenant_name', 'label' => 'Tenant'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'badge'],
                ['key' => 'required_deposit', 'label' => 'Required Deposit', 'type' => 'amount', 'class' => 'text-right', 'color' => 'blue'],
                ['key' => 'collected_deposit', 'label' => 'Collected Deposit', 'type' => 'amount', 'class' => 'text-right', 'color' => 'emerald'],
                ['key' => 'pending_deposit', 'label' => 'Pending Deposit', 'type' => 'amount', 'class' => 'text-right', 'color' => 'orange'],
                ['key' => 'deduction_deposit', 'label' => 'Deductions / Damage', 'type' => 'amount', 'class' => 'text-right', 'color' => 'amber'],
                ['key' => 'refunded_deposit', 'label' => 'Refunded Deposit', 'type' => 'amount', 'class' => 'text-right', 'color' => 'teal'],
                ['key' => 'net_refundable', 'label' => 'Net Refundable', 'type' => 'amount', 'class' => 'text-right', 'color' => 'purple'],
            ];
        } else {
            $summaryCards = [
                ['label' => 'Total Prev. Unpaid', 'value' => 'Rs. ' . number_format($s['total_prev_unpaid'] ?? 0, 2), 'color' => 's-amber'],
                ['label' => 'Total Amount Due', 'value' => 'Rs. ' . number_format($s['total_amount_due'] ?? 0, 2), 'color' => 's-blue'],
                ['label' => 'Total Amount Paid', 'value' => 'Rs. ' . number_format($s['total_amount_paid'] ?? 0, 2), 'color' => 's-green'],
                ['label' => 'Total Balance', 'value' => 'Rs. ' . number_format($s['total_balance'] ?? 0, 2), 'color' => 's-neutral'],
            ];

            $columns = [
                ['key' => 'sr', 'label' => 'Sr #'],
                ['key' => 'unit_number', 'label' => 'Flat/Shop'],
                ['key' => 'tenant_name', 'label' => 'Tenant'],
                ['key' => 'type_label', 'label' => 'Billing Type', 'type' => 'badge'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ['key' => 'prev_unpaid', 'label' => 'Prev. Unpaid', 'type' => 'amount', 'class' => 'text-right'],
                ['key' => 'amount_due', 'label' => 'Amount Due', 'type' => 'amount', 'class' => 'text-right'],
                ['key' => 'amount_paid', 'label' => 'Amount Paid', 'type' => 'amount', 'class' => 'text-right'],
                ['key' => 'payment_method', 'label' => 'Payment Method'],
                ['key' => 'payment_account', 'label' => 'Payment Account'],
                ['key' => 'paid_at', 'label' => 'Paid At'],
                ['key' => 'balance', 'label' => 'Balance', 'type' => 'balance', 'class' => 'text-right'],
            ];
        }

        return [
            'hasSelection' => true,
            'isSecurityDeposit' => $isSecurityDeposit,
            'rows' => $data['rows'],
            'allRows' => $data['all_rows'],
            'columns' => $columns,
            'summaryCards' => $summaryCards,
            'filterChips' => $data['filter_tags'],
            'paginator' => $data['rows'] instanceof LengthAwarePaginator ? $data['rows'] : null,
            // 'balance' (non-deposit mode) is per-unit, not one continuous ledger —
            // use the pre-aggregated total rather than sum/last-row logic.
            'footerBalanceOverride' => $isSecurityDeposit ? null : ($s['total_balance'] ?? 0),
            'printRoute' => route('ledgers.all.print', array_merge($request->query(), ['ledger_type' => 'flat_shop'])),
            'pdfRoute' => null,
            'excelRoute' => route('ledgers.all.excel', array_merge($request->query(), ['ledger_type' => 'flat_shop'])),
        ];
    }

    private function buildPartyLedgerView(Request $request): array
    {
        $partyId = $request->query('party_id');
        if (!$partyId) {
            return ['hasSelection' => false];
        }

        $ledgerData = $this->partyController->getPartyLedgerData($partyId);
        $party = $ledgerData['party'];

        $s = $ledgerData['summary'];
        $summaryCards = [
            ['label' => 'Net Receivable', 'value' => 'Rs. ' . number_format($s['net_receivable'], 2), 'color' => 's-green'],
            ['label' => 'Net Payable', 'value' => 'Rs. ' . number_format($s['net_payable'], 2), 'color' => 's-orange'],
            ['label' => 'Total Received', 'value' => 'Rs. ' . number_format($s['total_received'], 2), 'color' => 's-blue'],
            ['label' => 'Total Paid', 'value' => 'Rs. ' . number_format($s['total_paid'], 2), 'color' => 's-neutral'],
        ];

        $columns = [
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'ref', 'label' => 'Ref / Voucher #'],
            ['key' => 'manual_voucher_no', 'label' => 'Manual Voucher #'],
            ['key' => 'type', 'label' => 'Transaction Type', 'type' => 'badge'],
            ['key' => 'description', 'label' => 'Details / Description'],
            ['key' => 'debit', 'label' => 'Debit (Dr)', 'type' => 'debit', 'class' => 'text-right'],
            ['key' => 'credit', 'label' => 'Credit (Cr)', 'type' => 'credit', 'class' => 'text-right'],
            ['key' => 'balance', 'label' => 'Balance', 'type' => 'balance', 'class' => 'text-right'],
        ];

        $entries = $ledgerData['entries']->map(function ($e) {
            $modelType = match ($e['type'] ?? null) {
                'Receipt (General)' => 'general_receiving_voucher',
                'Payment', 'Payment (Advance)' => 'payment_voucher',
                default => null,
            };
            $e['link'] = VoucherLinkResolver::resolve($modelType, $e['id'] ?? null);
            return $e;
        });

        return [
            'hasSelection' => true,
            'rows' => $entries,
            'allRows' => $entries,
            'columns' => $columns,
            'summaryCards' => $summaryCards,
            'filterChips' => [
                ['label' => 'Party', 'value' => $party->name],
            ],
            'printRoute' => route('ledgers.all.print', array_merge($request->query(), ['ledger_type' => 'party'])),
            'pdfRoute' => null,
            'excelRoute' => null,
            'manageRoute' => route('ledgers.party', ['party_id' => $party->id]),
        ];
    }
}
