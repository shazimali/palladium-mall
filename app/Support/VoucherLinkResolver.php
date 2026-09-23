<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class VoucherLinkResolver
{
    /**
     * Maps the internal "model type" tags used across the various ledger row
     * builders to the route name of that model's show page. Types without a
     * working show route (e.g. withdrawals — edit is Super Admin only and
     * there's no dedicated show page; party dues — no show route at all) are
     * intentionally left out so those rows simply render unlinked.
     */
    private const ROUTES = [
        'payment' => 'payments.show',
        'receiving_voucher' => 'receiving-vouchers.show',
        'payment_voucher' => 'payment-vouchers.show',
        'general_receiving_voucher' => 'general-receiving-vouchers.show',
        'expense' => 'expenses.show',
        'jv_voucher' => 'jv-vouchers.show',
        'other_owned_rent_purchase_voucher' => 'other-owned-rent-purchase-vouchers.show',
    ];

    public static function resolve(?string $modelType, $id): ?string
    {
        if (!$modelType || !$id || !isset(self::ROUTES[$modelType])) {
            return null;
        }

        $routeName = self::ROUTES[$modelType];

        if (!Route::has($routeName)) {
            return null;
        }

        return route($routeName, $id);
    }
}
