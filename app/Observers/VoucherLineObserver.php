<?php

namespace App\Observers;

use App\Models\Voucher;
use App\Services\LegacyVoucherLinker;
use Illuminate\Database\Eloquent\Model;

/**
 * Keeps the unified Vouchers list complete when rows are written outside the new
 * voucher form (Stock Entries, Move-out, Billing page, old screens).
 */
class VoucherLineObserver
{
    public function __construct(private LegacyVoucherLinker $linker)
    {
    }

    public function created(Model $row): void
    {
        if (!LegacyVoucherLinker::syncPaused() && !$row->voucher_id) {
            $this->linker->wrap($row);
        }
    }

    public function updated(Model $row): void
    {
        $this->syncHeader($row);
    }

    public function deleted(Model $row): void
    {
        $this->syncHeader($row);
    }

    public function restored(Model $row): void
    {
        $this->syncHeader($row);
    }

    private function syncHeader(Model $row): void
    {
        if (LegacyVoucherLinker::syncPaused() || !$row->voucher_id) {
            return;
        }

        if ($voucher = Voucher::withTrashed()->find($row->voucher_id)) {
            $this->linker->sync($voucher);
        }
    }
}
