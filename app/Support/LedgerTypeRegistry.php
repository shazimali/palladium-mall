<?php

namespace App\Support;

class LedgerTypeRegistry
{
    /**
     * The 7 ledger types selectable on the unified "All Ledgers" page, keyed by
     * the `ledger_type` query param. `permission` is only set for types that
     * need an additional permission check beyond the page's own `ledgers.view` gate.
     */
    public static function types(): array
    {
        return [
            'tenant' => ['label' => 'Tenant / Unit Ledger'],
            'owner' => ['label' => 'Managing Owner Ledger'],
            'payment_account' => ['label' => 'Payment Account (Cash & Bank) Ledger'],
            'expense' => ['label' => 'Expense Head Ledger'],
            'landlord' => ['label' => 'Landlord Ledger', 'permission' => 'landlords.view'],
            'security' => ['label' => 'Security Deposit Ledger'],
            'flat_shop' => ['label' => 'Flat / Shop Recovery Ledger'],
            'party' => ['label' => 'Party Ledger'],
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::types());
    }

    public static function isValid(string $key): bool
    {
        return array_key_exists($key, self::types());
    }
}
