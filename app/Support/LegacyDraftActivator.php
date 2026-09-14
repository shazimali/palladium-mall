<?php

namespace App\Support;

use App\Models\Agreement;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

/**
 * Shared activation logic used by both the tenant wizard (TenantController)
 * and the one-time backfill command for pre-existing draft agreements that
 * already carry all the data the merged Step 1 now requires.
 */
class LegacyDraftActivator
{
    public static function isEligible(Agreement $agreement): bool
    {
        return $agreement->status === 'draft'
            && $agreement->unit_id
            && $agreement->start_date !== null
            && $agreement->end_date !== null
            && $agreement->monthly_rent !== null
            && $agreement->security_deposit !== null
            && $agreement->payment_due_day !== null
            && $agreement->fine_per_day !== null;
    }

    public static function hasOtherActiveAgreement(Agreement $agreement): bool
    {
        return Agreement::where('tenant_id', $agreement->tenant_id)
            ->where('status', 'active')
            ->where('id', '!=', $agreement->id)
            ->exists();
    }

    public static function runActivationTransaction(Tenant $tenant, Agreement $agreement): void
    {
        DB::transaction(function () use ($tenant, $agreement) {
            $tenant->update(['status' => 'active']);

            // Expire any previous active agreements for this tenant on the same unit
            $tenant->agreements()
                ->where('unit_id', $agreement->unit_id)
                ->where('status', 'active')
                ->where('id', '!=', $agreement->id)
                ->update(['status' => 'expired']);

            $agreement->update(['status' => 'active']);
        });
    }

    /**
     * Activate a pre-existing draft agreement if it already has everything the
     * merged Step 1 requires. Leaves ineligible or conflicting agreements untouched.
     */
    public static function activate(Agreement $agreement): Agreement
    {
        if (!static::isEligible($agreement)) {
            return $agreement;
        }

        $tenant = $agreement->tenant;
        if (!$tenant || static::hasOtherActiveAgreement($agreement)) {
            return $agreement;
        }

        static::runActivationTransaction($tenant, $agreement);

        if ($agreement->unit_id) {
            Unit::find($agreement->unit_id)?->update(['status' => 'rented']);
        }

        return $agreement->fresh();
    }
}
