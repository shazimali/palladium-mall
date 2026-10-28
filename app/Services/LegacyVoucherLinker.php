<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\GeneralReceivingVoucher;
use App\Models\PaymentVoucher;
use App\Models\ReceivingVoucher;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Gives existing single-entry voucher rows (and rows other modules keep creating,
 * e.g. Stock Entry expenses or Billing-page receipts) a one-line Voucher header so
 * they appear in the unified Vouchers list. The row itself is never changed apart
 * from voucher_id / line_no, so ledgers are unaffected.
 */
class LegacyVoucherLinker
{
    /** Line models that can carry a voucher_id. */
    public const LINE_MODELS = [
        ReceivingVoucher::class,
        GeneralReceivingVoucher::class,
        PaymentVoucher::class,
        Expense::class,
    ];

    private static int $paused = 0;

    /**
     * Run $callback without the observer creating/syncing headers
     * (used while VoucherPostingService writes a form voucher's own lines).
     */
    public static function withoutSync(callable $callback): mixed
    {
        self::$paused++;
        try {
            return $callback();
        } finally {
            self::$paused--;
        }
    }

    public static function syncPaused(): bool
    {
        return self::$paused > 0;
    }

    /**
     * Wrap one unlinked row in a one-line header. Returns null when the row can't be
     * wrapped (already linked, or it has no payment account).
     */
    public function wrap(Model $row): ?Voucher
    {
        if ($row->voucher_id || !$row->payment_account_id) {
            return null;
        }

        return DB::transaction(function () use ($row) {
            $voucher = Voucher::withoutEvents(fn() => Voucher::forceCreate([
                'voucher_no'         => $row->voucher_no,
                'type'               => $this->typeFor($row),
                'source'             => $this->sourceFor($row),
                'manual_voucher_no'  => $this->headerManualNo($row),
                'date'               => $row->date,
                'payment_account_id' => $row->payment_account_id,
                'total_amount'       => $row->amount,
                'narration'          => $row->notes,
                'user_id'            => $row->user_id,
                'created_at'         => $row->created_at ?? now(),
                'updated_at'         => $row->updated_at ?? now(),
            ]));

            // Query-builder update: no model events, no activity log, updated_at untouched
            DB::table($row->getTable())->where('id', $row->id)->update(['voucher_id' => $voucher->id, 'line_no' => 1]);
            $row->setRawAttributes(array_merge($row->getAttributes(), ['voucher_id' => $voucher->id, 'line_no' => 1]), true);

            return $voucher;
        });
    }

    /**
     * Keep a non-form header in step with its row after the row was changed or deleted
     * by an old screen or another module. Form vouchers are managed by VoucherPostingService.
     */
    public function sync(Voucher $voucher): void
    {
        if ($voucher->source === 'form') {
            return;
        }

        $rows = collect(self::LINE_MODELS)
            ->flatMap(fn($class) => $class::where('voucher_id', $voucher->id)->get());

        Voucher::withoutEvents(function () use ($voucher, $rows) {
            if ($rows->isEmpty()) {
                if (!$voucher->trashed()) {
                    $voucher->delete();
                }
                return;
            }

            if ($voucher->trashed()) {
                $voucher->restore();
            }

            $first = $rows->sortBy('line_no')->first();
            $voucher->update([
                'type'               => $this->typeFor($first),
                'date'               => $first->date,
                'payment_account_id' => $first->payment_account_id ?? $voucher->payment_account_id,
                'total_amount'       => $rows->sum(fn($r) => (float) $r->amount),
                'narration'          => $rows->count() === 1 ? $first->notes : $voucher->narration,
            ]);
        });
    }

    public function typeFor(Model $row): string
    {
        $received = $row instanceof ReceivingVoucher || $row instanceof GeneralReceivingVoucher;
        $cash = (int) $row->payment_account_id === Voucher::DEFAULT_CASH_ACCOUNT_ID;

        return ($cash ? 'cash' : 'bank') . ($received ? '_received' : '_paid');
    }

    public function sourceFor(Model $row): string
    {
        if ($row instanceof Expense) {
            $linkedToStock = str_starts_with((string) $row->reference, 'Stock In #')
                || DB::table('stock_entries')->where('expense_id', $row->id)->exists();
            if ($linkedToStock) {
                return 'stock_entry';
            }
        }

        if ($row instanceof ReceivingVoucher && str_starts_with((string) $row->reference, 'MOVE-OUT-DED-')) {
            return 'move_out';
        }

        return 'legacy';
    }

    /**
     * The row's manual number, unless it's a placeholder (all zeros) or another
     * header already uses it (the header column is unique). The row keeps its own value.
     */
    public function headerManualNo(Model $row): ?string
    {
        $manual = trim((string) ($row->manual_voucher_no ?? ''));

        if ($manual === '' || preg_match('/^0+$/', $manual)) {
            return null;
        }

        return Voucher::withTrashed()->where('manual_voucher_no', $manual)->exists() ? null : $manual;
    }
}
