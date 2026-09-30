<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\GeneralReceivingVoucher;
use App\Models\Landlord;
use App\Models\Owner;
use App\Models\Party;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\PaymentVoucher;
use App\Models\ReceivingVoucher;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Voucher;
use Illuminate\Validation\ValidationException;

/**
 * Writes the lines of a multi-line Voucher into the existing single-entry tables
 * (receiving_vouchers, general_receiving_vouchers, payment_vouchers, expenses)
 * and reverses them. Ledgers read those tables, so no ledger code changes are needed.
 *
 * Callers must wrap post()/unpost() in a DB transaction.
 */
class VoucherPostingService
{
    /**
     * @param  array  $lines  validated lines: entry_type, amount, notes and the id field for the entry type
     */
    public function post(Voucher $voucher, array $lines): void
    {
        // The observer must not wrap/sync these rows: this voucher already owns them
        LegacyVoucherLinker::withoutSync(fn() => $this->postLines($voucher, $lines));
    }

    private function postLines(Voucher $voucher, array $lines): void
    {
        $account = PaymentAccount::findOrFail($voucher->payment_account_id);
        $multi = count($lines) > 1;

        if (!$voucher->isReceived()) {
            $total = array_sum(array_map(fn($l) => round((float) $l['amount']), $lines));
            $balance = $account->current_balance;
            if ($total > $balance + 0.01) {
                throw ValidationException::withMessages([
                    'payment_account_id' => 'The selected account (' . $account->name . ') does not have sufficient balance. Current balance: Rs. ' . number_format($balance, 2) . ', voucher total: Rs. ' . number_format($total, 2) . '.',
                ]);
            }
        }

        foreach (array_values($lines) as $i => $line) {
            $lineNo = $i + 1;
            $common = [
                'voucher_id'         => $voucher->id,
                'line_no'            => $lineNo,
                'voucher_no'         => $multi ? $voucher->voucher_no . '/' . $lineNo : $voucher->voucher_no,
                'date'               => $voucher->date->toDateString(),
                'amount'             => round((float) $line['amount']),
                'payment_method'     => $account->type,
                'payment_account_id' => $account->id,
                'reference'          => $voucher->reference,
                'notes'              => ($line['notes'] ?? null) ?: $voucher->narration,
                'user_id'            => $voucher->user_id ?? auth()->id(),
            ];
            $manualNo = $voucher->manual_voucher_no
                ? ($multi ? $voucher->manual_voucher_no . '/' . $lineNo : $voucher->manual_voucher_no)
                : null;

            if ($voucher->isReceived()) {
                $this->postReceivedLine($line, $common, $manualNo, $i, $account);
            } else {
                $this->postPaidLine($line, $common, $i);
            }
        }
    }

    /**
     * Reverse all lines of a voucher. RV allocations on tenant dues are rolled back.
     * $force = true hard-deletes the rows so their numbers can be reused on update.
     */
    public function unpost(Voucher $voucher, bool $force = false): void
    {
        LegacyVoucherLinker::withoutSync(fn() => $this->unpostLines($voucher, $force));
    }

    private function unpostLines(Voucher $voucher, bool $force): void
    {
        foreach ($voucher->receivingVouchers()->with('payments')->get() as $rv) {
            $this->revertAllocations($rv);
            $force ? $rv->forceDelete() : $rv->delete();
        }

        $others = [
            $voucher->generalReceivingVouchers()->get(),
            $voucher->paymentVouchers()->get(),
            $voucher->expenses()->get(),
        ];
        foreach ($others as $rows) {
            foreach ($rows as $row) {
                $force ? $row->forceDelete() : $row->delete();
            }
        }
    }

    // ── Received side ──────────────────────────────────────────────────────

    private function postReceivedLine(array $line, array $common, ?string $manualNo, int $i, PaymentAccount $account): void
    {
        $type = $line['entry_type'];

        if ($type === 'tenant') {
            $this->assertManualNoFree(ReceivingVoucher::class, $manualNo);
            $this->postTenantReceipt($line, $common + ['manual_voucher_no' => $manualNo], $i);
            return;
        }

        $this->assertManualNoFree(GeneralReceivingVoucher::class, $manualNo);

        $data = $common + [
            'manual_voucher_no'       => $manualNo,
            'received_from_type'      => $type,
            'party_id'                => null,
            'landlord_id'             => null,
            'from_payment_account_id' => null,
        ];

        if ($type === 'account') {
            $source = PaymentAccount::findOrFail($line['account_id']);
            if ($source->id === $account->id) {
                $this->fail($i, 'account_id', 'Source account must be different from the voucher account.');
            }
            $balance = $source->current_balance;
            if ($data['amount'] > $balance + 0.01) {
                $this->fail($i, 'amount', 'Source account (' . $source->name . ') does not have sufficient balance. Current balance: Rs. ' . number_format($balance, 2) . '.');
            }
            $data['from_payment_account_id'] = $source->id;
        } elseif ($type === 'landlord') {
            $data['landlord_id'] = $line['landlord_id'];
        } else {
            $data['received_from_type'] = 'party';
            $data['party_id'] = $line['party_id'];
        }

        GeneralReceivingVoucher::create($data);
    }

    /**
     * Same allocation rules as ReceivingVoucherController::store — oldest unpaid month first.
     */
    private function postTenantReceipt(array $line, array $data, int $i): void
    {
        $unit = Unit::with(['tenant', 'agreements.tenant'])->findOrFail($line['unit_id']);

        // Only the dues ticked on the form, when any were ticked (same as ReceivingVoucherController::store)
        $selectedIds = array_filter((array) ($line['payment_ids'] ?? []));

        $payments = Payment::where('unit_id', $unit->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->when($selectedIds, fn($q) => $q->whereIn('id', $selectedIds))
            ->orderBy('month', 'asc')
            ->lockForUpdate()
            ->get();

        $tenantId = $unit->tenant?->id
            ?? $payments->firstWhere('tenant_id', '!=', null)?->tenant_id
            ?? $unit->agreements->sortByDesc('id')->first()?->tenant_id;

        $totalBalance = $payments->sum(fn($p) => (float) $p->balanceDue());
        $amount = (float) $data['amount'];

        if ($amount > $totalBalance + 0.01) {
            $this->fail($i, 'amount', 'Amount exceeds the ' . ($selectedIds ? 'selected dues' : 'outstanding balance') . ' of unit ' . $unit->unit_number . ' (Rs. ' . number_format($totalBalance, 2) . ').');
        }

        $rv = ReceivingVoucher::create($data + [
            'received_from_type' => 'tenant',
            'tenant_id'          => $tenantId,
            'other_name'         => $unit->tenant?->name,
        ]);

        $remaining = round($amount, 2);
        foreach ($payments as $payment) {
            if ($remaining <= 0) {
                break;
            }
            $allocated = min($remaining, round((float) $payment->balanceDue(), 2));
            $newPaid = round((float) $payment->amount_paid + $allocated, 2);

            $payment->update([
                'amount_paid'        => $newPaid,
                'status'             => Payment::calculateStatus((float) $payment->amount, $newPaid),
                'paid_at'            => $payment->paid_at ?? $data['date'],
                'payment_account_id' => $data['payment_account_id'],
                'payment_method'     => $data['payment_method'],
                'reference'          => $data['reference'] ?? null,
            ]);

            $rv->payments()->attach($payment->id, ['amount_allocated' => $allocated]);
            $remaining = round($remaining - $allocated, 2);
        }
    }

    /**
     * Same rollback rules as ReceivingVoucherController::destroy.
     */
    private function revertAllocations(ReceivingVoucher $rv): void
    {
        foreach ($rv->payments as $payment) {
            $reverted = max(0.00, (float) $payment->amount_paid - (float) $payment->pivot->amount_allocated);

            if ($reverted <= 0) {
                $payment->update([
                    'amount_paid'        => 0.00,
                    'status'             => 'unpaid',
                    'paid_at'            => null,
                    'payment_account_id' => null,
                    'payment_method'     => null,
                ]);
            } else {
                $payment->update([
                    'amount_paid' => $reverted,
                    'status'      => Payment::calculateStatus((float) $payment->amount, $reverted),
                ]);
            }
        }

        $rv->payments()->detach();
    }

    // ── Paid side ──────────────────────────────────────────────────────────

    private function postPaidLine(array $line, array $common, int $i): void
    {
        $type = $line['entry_type'];

        if ($type === 'expense') {
            Expense::create($common + ['expense_head_id' => $line['expense_head_id']]);
            return;
        }

        $data = $common + [
            'paid_to_type'          => $type,
            'owner_id'              => null,
            'party_id'              => null,
            'tenant_id'             => null,
            'unit_id'               => null,
            'landlord_id'           => null,
            'to_payment_account_id' => null,
            'is_advance'            => false,
        ];

        switch ($type) {
            case 'tenant':
                $unit = Unit::with('tenant')->findOrFail($line['unit_id']);
                $tenant = !empty($line['tenant_id']) ? Tenant::findOrFail($line['tenant_id']) : $unit->tenant;
                if (!$tenant) {
                    $this->fail($i, 'tenant_id', 'Select the tenant to refund for unit ' . $unit->unit_number . '.');
                }
                $this->assertSecurityRefundLimit($tenant, $unit, (float) $data['amount'], $i);
                $data['tenant_id'] = $tenant->id;
                $data['unit_id'] = $unit->id;
                $data['other_name'] = $tenant->name;
                break;

            case 'landlord':
                $landlord = Landlord::findOrFail($line['landlord_id']);
                $available = -$landlord->currentBalance();
                if ($data['amount'] > $available + 0.01) {
                    $this->fail($i, 'amount', 'Amount exceeds the payable balance of ' . $landlord->name . ' (Rs. ' . number_format(max(0, $available), 2) . ').');
                }
                $data['landlord_id'] = $landlord->id;
                $data['other_name'] = $landlord->name;
                break;

            case 'owner':
                $owner = Owner::findOrFail($line['owner_id']);
                $data['owner_id'] = $owner->id;
                $data['other_name'] = $owner->name;
                break;

            case 'account':
                $to = PaymentAccount::findOrFail($line['account_id']);
                if ($to->id === (int) $common['payment_account_id']) {
                    $this->fail($i, 'account_id', 'Destination account must be different from the voucher account.');
                }
                $data['to_payment_account_id'] = $to->id;
                $data['other_name'] = $to->name;
                break;

            default:
                $party = Party::findOrFail($line['party_id']);
                $data['paid_to_type'] = 'other';
                $data['party_id'] = $party->id;
                $data['other_name'] = $party->name;
        }

        PaymentVoucher::create($data);
    }

    /**
     * Same rule as PaymentVoucherController::store — refunds can't exceed collected security deposit.
     */
    private function assertSecurityRefundLimit(Tenant $tenant, Unit $unit, float $amount, int $i): void
    {
        $pending = $this->securityRefundBalances($tenant->id)->get($unit->id)['pending'] ?? 0.0;

        if ($amount > $pending + 0.01) {
            $this->fail($i, 'amount', 'Amount exceeds the security deposit refund limit of Rs. ' . number_format($pending, 2) . ' for unit ' . $unit->unit_number . '.');
        }
    }

    /**
     * Security deposit payable back to a tenant, per unit: collected minus already refunded.
     * $excludeVoucherId leaves out refunds made by that voucher (used while editing it).
     *
     * @return \Illuminate\Support\Collection<int, array{unit_id:int, unit_number:string, collected:float, refunded:float, pending:float}>
     */
    public function securityRefundBalances(int $tenantId, ?int $excludeVoucherId = null)
    {
        $collected = Payment::where('tenant_id', $tenantId)
            ->where('type', 'security_deposit')
            ->groupBy('unit_id')
            ->selectRaw('unit_id, SUM(amount_paid) as total')
            ->pluck('total', 'unit_id');

        $refunded = PaymentVoucher::where('tenant_id', $tenantId)
            ->when($excludeVoucherId, fn($q) => $q->where(fn($w) => $w->whereNull('voucher_id')->orWhere('voucher_id', '!=', $excludeVoucherId)))
            ->whereNotNull('unit_id')
            ->groupBy('unit_id')
            ->selectRaw('unit_id, SUM(amount) as total')
            ->pluck('total', 'unit_id');

        $unitIds = $collected->keys()->merge($refunded->keys())->unique()->filter();
        $units = Unit::whereIn('id', $unitIds)->pluck('unit_number', 'id');

        return $unitIds->mapWithKeys(function ($unitId) use ($collected, $refunded, $units) {
            $in = (float) ($collected[$unitId] ?? 0);
            $out = (float) ($refunded[$unitId] ?? 0);

            return [(int) $unitId => [
                'unit_id'     => (int) $unitId,
                'unit_number' => (string) ($units[$unitId] ?? '—'),
                'collected'   => $in,
                'refunded'    => $out,
                'pending'     => round($in - $out, 2),
            ]];
        });
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function assertManualNoFree(string $modelClass, ?string $manualNo): void
    {
        if ($manualNo && $modelClass::withTrashed()->where('manual_voucher_no', $manualNo)->exists()) {
            throw ValidationException::withMessages([
                'manual_voucher_no' => 'Manual voucher number ' . $manualNo . ' is already used.',
            ]);
        }
    }

    private function fail(int $i, string $field, string $message): never
    {
        throw ValidationException::withMessages(["lines.$i.$field" => $message]);
    }
}
