<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\GeneralReceivingVoucher;
use App\Models\PaymentVoucher;
use App\Models\ReceivingVoucher;
use App\Models\Voucher;
use App\Services\LegacyVoucherLinker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * The old Receiving / General Receiving / Paid / Expense voucher screens are read-only.
 * Their create/edit/store/update/destroy routes (same URLs and names as before, so old
 * buttons keep working) land here and are sent to the unified voucher form.
 */
class LegacyVoucherRedirectController extends Controller
{
    private const KINDS = [
        'rv'  => ['model' => ReceivingVoucher::class, 'type' => 'cash_received', 'index' => 'receiving-vouchers.index'],
        'grv' => ['model' => GeneralReceivingVoucher::class, 'type' => 'cash_received', 'index' => 'general-receiving-vouchers.index'],
        'pv'  => ['model' => PaymentVoucher::class, 'type' => 'cash_paid', 'index' => 'payment-vouchers.index'],
        'ev'  => ['model' => Expense::class, 'type' => 'cash_paid', 'index' => 'expenses.index'],
    ];

    /**
     * Register the write routes of an old voucher resource (same URIs and names as
     * Route::resource) so they redirect here. Call before the read-only resource routes.
     */
    public static function routes(string $uri, string $param, string $namePrefix, string $kind): void
    {
        Route::get("$uri/create", [self::class, 'create'])->name("$namePrefix.create")->defaults('kind', $kind);
        Route::post($uri, [self::class, 'store'])->name("$namePrefix.store")->defaults('kind', $kind);
        Route::get("$uri/{{$param}}/edit", [self::class, 'edit'])->name("$namePrefix.edit")->defaults('kind', $kind);
        Route::match(['put', 'patch'], "$uri/{{$param}}", [self::class, 'update'])->name("$namePrefix.update")->defaults('kind', $kind);
        Route::delete("$uri/{{$param}}", [self::class, 'destroy'])->name("$namePrefix.destroy")->defaults('kind', $kind);
    }

    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('vouchers.create', ['type' => $this->kind($request)['type']]);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('vouchers.create', ['type' => $this->kind($request)['type']])
            ->with('error', 'The old voucher screens are read-only. Please create the voucher here.');
    }

    public function edit(Request $request, LegacyVoucherLinker $linker): RedirectResponse
    {
        return $this->toHeader($request, $linker, null);
    }

    public function update(Request $request, LegacyVoucherLinker $linker): RedirectResponse
    {
        return $this->toHeader($request, $linker, 'The old voucher screens are read-only. Please make your changes here.');
    }

    public function destroy(Request $request, LegacyVoucherLinker $linker): RedirectResponse
    {
        return $this->toHeader($request, $linker, 'The old voucher screens are read-only. Delete the voucher from the Vouchers list.');
    }

    private function toHeader(Request $request, LegacyVoucherLinker $linker, ?string $message): RedirectResponse
    {
        $kind = $this->kind($request);
        $row = $kind['model']::findOrFail($this->rowId($request));

        $voucher = $row->voucher_id ? Voucher::find($row->voucher_id) : $linker->wrap($row);
        if (!$voucher) {
            return redirect()->route($kind['index'])->with('error', 'This voucher has no payment account and cannot be opened in the voucher form.');
        }

        if ($voucher->isLocked()) {
            return redirect()->route('vouchers.print', $voucher)
                ->with('error', 'Voucher ' . $voucher->voucher_no . ' was created from ' . $voucher->source_label . '. Edit or delete it there.');
        }

        $redirect = redirect()->route('vouchers.edit', $voucher);

        return $message ? $redirect->with('error', $message) : $redirect;
    }

    private function kind(Request $request): array
    {
        return self::KINDS[$request->route('kind')] ?? abort(404);
    }

    /** The row id is the only route parameter besides the "kind" default. */
    private function rowId(Request $request): int
    {
        $params = collect($request->route()->parameters())->except('kind');

        return (int) $params->first();
    }
}
