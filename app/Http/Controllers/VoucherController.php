<?php

namespace App\Http\Controllers;

use App\Models\ExpenseHead;
use App\Models\Landlord;
use App\Models\Owner;
use App\Models\Party;
use App\Models\PaymentAccount;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Voucher;
use App\Services\VoucherPostingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Cash/Bank Received/Paid vouchers with multiple lines. Lines are posted into the
 * existing voucher tables by VoucherPostingService, so all ledgers pick them up.
 */
class VoucherController extends Controller
{
    public function __construct(private VoucherPostingService $posting)
    {
    }

    public function index(Request $request): View
    {
        $this->authorizeAction('view');

        $query = $this->filteredQuery($request);

        $totals = [
            'received' => (float) (clone $query)->where('type', 'like', '%_received')->sum('total_amount'),
            'paid'     => (float) (clone $query)->where('type', 'like', '%_paid')->sum('total_amount'),
        ];

        $vouchers = $query->with(['paymentAccount', 'user'])
            ->latest('date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('vouchers.index', [
            'title'           => 'Vouchers',
            'vouchers'        => $vouchers,
            'totals'          => $totals,
            'paymentAccounts' => PaymentAccount::orderBy('name')->get(),
        ]);
    }

    public function printList(Request $request): View
    {
        $this->authorizeAction('view');

        $vouchers = $this->filteredQuery($request)
            ->with(['paymentAccount'])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        return view('vouchers.print_list', [
            'title'    => 'Vouchers List',
            'vouchers' => $vouchers,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeAction('create');

        $type = $request->query('type', 'cash_received');
        abort_unless(array_key_exists($type, Voucher::TYPES), 404);

        $formData = $this->formData($type);
        $defaultAccountId = Voucher::isCashType($type)
            && $formData['headerAccounts']->contains('value', Voucher::DEFAULT_CASH_ACCOUNT_ID)
                ? Voucher::DEFAULT_CASH_ACCOUNT_ID
                : null;

        return view('vouchers.form', $formData + [
            'title'            => 'New ' . Voucher::TYPES[$type] . ' Voucher',
            'voucher'          => null,
            'defaultAccountId' => $defaultAccountId,
            'nextVoucherNo' => Voucher::nextNumber(),
            'lines'         => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAction('create');

        [$header, $lines] = $this->validateVoucher($request);

        $voucher = DB::transaction(function () use ($header, $lines) {
            $voucher = Voucher::create($header + [
                'voucher_no'   => Voucher::nextNumber(true),
                'total_amount' => $this->total($lines),
                'user_id'      => auth()->id(),
            ]);
            $this->posting->post($voucher, $lines);

            return $voucher;
        });

        return redirect()->route('vouchers.print', $voucher)
            ->with('success', $voucher->type_label . ' voucher ' . $voucher->voucher_no . ' saved successfully.');
    }

    public function show(Voucher $voucher): RedirectResponse
    {
        return redirect()->route('vouchers.print', $voucher);
    }

    public function edit(Voucher $voucher): View|RedirectResponse
    {
        $this->authorizeAction('edit');
        if ($blocked = $this->blockIfLocked($voucher)) {
            return $blocked;
        }

        $lines = $voucher->lines()->map(fn($l) => collect($l)->except('model')->all())->all();

        return view('vouchers.form', $this->formData($voucher->type) + [
            'title'            => 'Edit ' . $voucher->type_label . ' Voucher ' . $voucher->voucher_no,
            'voucher'          => $voucher,
            'defaultAccountId' => null,
            'nextVoucherNo' => $voucher->voucher_no,
            'lines'         => $lines,
        ]);
    }

    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $this->authorizeAction('edit');
        if ($blocked = $this->blockIfLocked($voucher)) {
            return $blocked;
        }

        [$header, $lines] = $this->validateVoucher($request, $voucher);

        DB::transaction(function () use ($voucher, $header, $lines) {
            // Remove old lines (and roll back tenant allocations) before re-posting,
            // so balance guards see the account as it would be without this voucher.
            $this->posting->unpost($voucher, true);
            $voucher->update($header + ['total_amount' => $this->total($lines)]);
            $this->posting->post($voucher->fresh(), $lines);
        });

        return redirect()->route('vouchers.print', $voucher)
            ->with('success', 'Voucher ' . $voucher->voucher_no . ' updated successfully.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        $this->authorizeAction('delete');
        if ($blocked = $this->blockIfLocked($voucher)) {
            return $blocked;
        }

        DB::transaction(function () use ($voucher) {
            $this->posting->unpost($voucher);
            $voucher->delete();
        });

        return redirect()->route('vouchers.index')
            ->with('success', 'Voucher ' . $voucher->voucher_no . ' deleted and its entries reversed.');
    }

    public function print(Voucher $voucher): View
    {
        $this->authorizeAction('view');

        $voucher->load(['paymentAccount', 'user']);

        return view('vouchers.print', [
            'title'   => $voucher->type_label . ' Voucher ' . $voucher->voucher_no,
            'voucher' => $voucher,
            'lines'   => $voucher->lines(),
        ]);
    }

    /**
     * Security deposit still payable to a tenant, per unit (Paid vouchers → Tenant Security Refund).
     */
    public function tenantPayables(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id'          => ['required', 'integer'],
            'exclude_voucher_id' => ['nullable', 'integer'],
        ]);

        $units = $this->posting->securityRefundBalances((int) $request->tenant_id, $request->integer('exclude_voucher_id') ?: null)
            ->filter(fn($u) => $u['pending'] > 0)
            ->values();

        return response()->json(['units' => $units]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function authorizeAction(string $action): void
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->hasPermission('vouchers.' . $action)) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Vouchers created by Stock Entries / Move-out are edited in those modules,
     * so the form can't re-post (and break) their rows.
     */
    private function blockIfLocked(Voucher $voucher): ?RedirectResponse
    {
        if (!$voucher->isLocked()) {
            return null;
        }

        return redirect()->route('vouchers.print', $voucher)
            ->with('error', 'Voucher ' . $voucher->voucher_no . ' was created from ' . $voucher->source_label . '. Edit or delete it there.');
    }

    private function filteredQuery(Request $request): Builder
    {
        return Voucher::query()
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->source, fn($q) => $q->where('source', $request->source))
            ->when($request->payment_account_id, fn($q) => $q->where('payment_account_id', $request->payment_account_id))
            ->when($request->start_date, fn($q) => $q->whereDate('date', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('date', '<=', $request->end_date))
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where(function ($sub) use ($term) {
                    $sub->where('voucher_no', 'like', "%{$term}%")
                        ->orWhere('manual_voucher_no', 'like', "%{$term}%")
                        ->orWhere('reference', 'like', "%{$term}%")
                        ->orWhere('narration', 'like', "%{$term}%");
                });
            });
    }

    /**
     * @return array{0: array, 1: array} [header fields, lines]
     */
    private function validateVoucher(Request $request, ?Voucher $voucher = null): array
    {
        $type = $voucher?->type ?? $request->input('type');

        $data = $request->validate([
            'type'                    => [Rule::requiredIf(!$voucher), Rule::in(array_keys(Voucher::TYPES))],
            'date'                    => ['required', 'date'],
            'payment_account_id'      => ['required', 'exists:payment_accounts,id'],
            'manual_voucher_no'       => ['nullable', 'string', 'max:255', Rule::unique('vouchers', 'manual_voucher_no')->ignore($voucher?->id)],
            'reference'               => ['nullable', 'string', 'max:255'],
            'narration'               => ['nullable', 'string', 'max:1000'],
            'lines'                   => ['required', 'array', 'min:1'],
            'lines.*.entry_type'      => ['required', Rule::in(array_keys(Voucher::entryTypesFor((string) $type)))],
            'lines.*.amount'          => ['required', 'numeric', 'min:1'],
            'lines.*.notes'           => ['nullable', 'string', 'max:1000'],
            'lines.*.party_id'        => ['nullable', 'required_if:lines.*.entry_type,party', 'exists:parties,id'],
            'lines.*.landlord_id'     => ['nullable', 'required_if:lines.*.entry_type,landlord', 'exists:landlords,id'],
            'lines.*.owner_id'        => ['nullable', 'required_if:lines.*.entry_type,owner', 'exists:owners,id'],
            'lines.*.account_id'      => ['nullable', 'required_if:lines.*.entry_type,account', 'exists:payment_accounts,id'],
            'lines.*.expense_head_id' => ['nullable', 'required_if:lines.*.entry_type,expense', 'exists:expense_heads,id'],
            'lines.*.unit_id'         => ['nullable', 'required_if:lines.*.entry_type,tenant', 'exists:units,id'],
            'lines.*.tenant_id'       => ['nullable', 'exists:tenants,id'],
            'lines.*.payment_ids'     => ['nullable', 'array'],
            'lines.*.payment_ids.*'   => ['integer', 'exists:payments,id'],
        ], [
            'lines.required' => 'Add at least one entry.',
            'required_if'    => 'This field is required for the selected entry type.',
        ]);

        $account = PaymentAccount::findOrFail($data['payment_account_id']);
        if (Voucher::isCashType($type) !== Voucher::isCashAccount($account)) {
            throw ValidationException::withMessages([
                'payment_account_id' => Voucher::isCashType($type)
                    ? 'Select a cash account for a cash voucher.'
                    : 'Select a bank account for a bank voucher.',
            ]);
        }

        $header = [
            'type'               => $type,
            'date'               => $data['date'],
            'payment_account_id' => $account->id,
            'manual_voucher_no'  => $data['manual_voucher_no'] ?? null,
            'reference'          => $data['reference'] ?? null,
            'narration'          => $data['narration'] ?? null,
        ];

        return [$header, array_values($data['lines'])];
    }

    private function total(array $lines): float
    {
        return array_sum(array_map(fn($l) => round((float) $l['amount']), $lines));
    }

    private function formData(string $type): array
    {
        $isCash = Voucher::isCashType($type);

        $accounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();
        $headerAccounts = $accounts->filter(fn($a) => Voucher::isCashAccount($a) === $isCash)
            ->map(fn($a) => ['value' => $a->id, 'label' => $a->name . ' (Bal: Rs. ' . number_format($a->current_balance) . ')'])
            ->values();

        $units = Unit::with('tenant')->orderBy('unit_number')->get();

        return [
            'type'           => $type,
            'entryTypes'     => Voucher::entryTypesFor($type),
            'headerAccounts' => $headerAccounts,
            'options'        => [
                'party'    => Party::orderBy('name')->get()->map(fn($p) => ['value' => $p->id, 'label' => $p->name])->values(),
                'landlord' => Landlord::orderBy('name')->get()->map(fn($l) => ['value' => $l->id, 'label' => $l->name])->values(),
                'owner'    => Owner::orderBy('name')->get()->map(fn($o) => ['value' => $o->id, 'label' => $o->name])->values(),
                'account'  => $accounts->map(fn($a) => ['value' => $a->id, 'label' => $a->name . ' (' . ucfirst(str_replace('_', ' ', $a->type)) . ')'])->values(),
                'expense'  => ExpenseHead::orderBy('name')->get()->map(fn($h) => ['value' => $h->id, 'label' => $h->name])->values(),
                'unit'     => $units->map(fn($u) => ['value' => $u->id, 'label' => $u->unit_number . ($u->tenant ? ' - ' . $u->tenant->name : ''), 'tenant_id' => $u->tenant?->id])->values(),
                'tenant'   => Tenant::with('unit')->orderBy('name')->get()->map(fn($t) => ['value' => $t->id, 'label' => $t->name . ($t->unit ? ' (' . $t->unit->unit_number . ')' : '')])->values(),
            ],
        ];
    }
}
