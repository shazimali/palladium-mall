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

    /**
     * Legacy line tables that can belong to a multi-line Voucher header.
     */
    private const LINE_MODELS = [
        'receiving_voucher' => \App\Models\ReceivingVoucher::class,
        'general_receiving_voucher' => \App\Models\GeneralReceivingVoucher::class,
        'payment_voucher' => \App\Models\PaymentVoucher::class,
        'expense' => \App\Models\Expense::class,
    ];

    /** @var array<string, array<int, int>> line id => voucher id, loaded once per type per request */
    private static array $voucherMaps = [];

    public static function resolve(?string $modelType, $id): ?string
    {
        if (!$modelType || !$id || !isset(self::ROUTES[$modelType])) {
            return null;
        }

        if ($voucherId = self::headerVoucherId($modelType, (int) $id)) {
            return Route::has('vouchers.print') ? route('vouchers.print', $voucherId) : null;
        }

        $routeName = self::ROUTES[$modelType];

        if (!Route::has($routeName)) {
            return null;
        }

        return route($routeName, $id);
    }

    private static function headerVoucherId(string $modelType, int $id): ?int
    {
        if (!isset(self::LINE_MODELS[$modelType])) {
            return null;
        }

        self::$voucherMaps[$modelType] ??= self::LINE_MODELS[$modelType]::withTrashed()
            ->whereNotNull('voucher_id')
            ->pluck('voucher_id', 'id')
            ->all();

        return self::$voucherMaps[$modelType][$id] ?? null;
    }
}
