<?php

use App\Models\Expense;
use App\Models\ExpenseHead;
use App\Models\GeneralReceivingVoucher;
use App\Models\Party;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\PaymentVoucher;
use App\Models\ReceivingVoucher;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Models\Voucher;
use App\Services\LegacyVoucherLinker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->roles()->attach(Role::create(['name' => 'super-admin', 'display_name' => 'Super Admin']));
    $this->actingAs($this->user);

    $this->cash = PaymentAccount::forceCreate(['id' => Voucher::DEFAULT_CASH_ACCOUNT_ID, 'name' => 'CASH', 'type' => 'other', 'opening_balance' => 0]);
    $this->bank = PaymentAccount::create(['name' => 'Meezan', 'type' => 'bank_transfer', 'opening_balance' => 100000]);
    $this->party = Party::create(['name' => 'ABC Traders']);
    $this->head = ExpenseHead::create(['name' => 'Electricity']);
    $this->unit = Unit::create(['unit_number' => 'S-01', 'type' => 'shop']);
    $this->payment = Payment::create(['unit_id' => $this->unit->id, 'type' => 'rent', 'month' => '2026-07-01', 'due_date' => '2026-07-05', 'amount' => 5000, 'amount_paid' => 0, 'status' => 'unpaid']);

    // Existing rows as the old screens created them (no header yet)
    LegacyVoucherLinker::withoutSync(function () {
        $this->rv = ReceivingVoucher::create(['date' => '2026-07-10', 'manual_voucher_no' => 'M-77', 'amount' => 5000, 'received_from_type' => 'tenant', 'payment_method' => 'other', 'payment_account_id' => $this->cash->id, 'notes' => 'July rent', 'user_id' => $this->user->id]);
        $this->rv->payments()->attach($this->payment->id, ['amount_allocated' => 5000]);
        $this->payment->update(['amount_paid' => 5000, 'status' => 'paid']);

        $this->grv = GeneralReceivingVoucher::create(['date' => '2026-07-11', 'manual_voucher_no' => '000', 'amount' => 2000, 'received_from_type' => 'party', 'party_id' => $this->party->id, 'payment_method' => 'bank_transfer', 'payment_account_id' => $this->bank->id, 'user_id' => $this->user->id]);
        $this->pv = PaymentVoucher::create(['date' => '2026-07-12', 'amount' => 1500, 'paid_to_type' => 'other', 'party_id' => $this->party->id, 'other_name' => 'ABC Traders', 'payment_method' => 'other', 'payment_account_id' => $this->cash->id, 'notes' => 'Supplies', 'user_id' => $this->user->id]);
        $this->ev = Expense::create(['expense_head_id' => $this->head->id, 'amount' => 800, 'date' => '2026-07-13', 'payment_method' => 'bank_transfer', 'payment_account_id' => $this->bank->id, 'user_id' => $this->user->id]);
        $this->stockEv = Expense::create(['expense_head_id' => $this->head->id, 'amount' => 300, 'date' => '2026-07-14', 'payment_method' => 'other', 'payment_account_id' => $this->cash->id, 'reference' => 'Stock In #SE-1', 'user_id' => $this->user->id]);
        $trashed = Expense::create(['expense_head_id' => $this->head->id, 'amount' => 999, 'date' => '2026-07-15', 'payment_method' => 'other', 'payment_account_id' => $this->cash->id, 'user_id' => $this->user->id]);
        $trashed->delete();
    });
});

it('imports existing rows with their old numbers, types and sources, without touching ledger data', function () {
    $before = DB::table('payment_vouchers')->where('id', $this->pv->id)->first();

    $this->artisan('vouchers:import-legacy')->assertSuccessful();

    expect(Voucher::count())->toBe(5); // trashed expense skipped

    $rvHeader = Voucher::where('voucher_no', $this->rv->fresh()->voucher_no)->firstOrFail();
    expect($rvHeader->type)->toBe('cash_received')
        ->and($rvHeader->source)->toBe('legacy')
        ->and($rvHeader->manual_voucher_no)->toBe('M-77')
        ->and((float) $rvHeader->total_amount)->toBe(5000.0)
        ->and($this->rv->fresh()->voucher_id)->toBe($rvHeader->id);

    expect(Voucher::find($this->grv->fresh()->voucher_id))
        ->type->toBe('bank_received')
        ->manual_voucher_no->toBeNull(); // "000" placeholder not copied
    expect($this->grv->fresh()->manual_voucher_no)->toBe('000'); // row keeps it

    expect(Voucher::find($this->pv->fresh()->voucher_id)->type)->toBe('cash_paid')
        ->and(Voucher::find($this->ev->fresh()->voucher_id)->type)->toBe('bank_paid')
        ->and(Voucher::find($this->stockEv->fresh()->voucher_id)->source)->toBe('stock_entry');

    // Only voucher_id / line_no changed on the row
    $after = DB::table('payment_vouchers')->where('id', $this->pv->id)->first();
    expect(collect((array) $after)->except(['voucher_id', 'line_no'])->all())
        ->toEqual(collect((array) $before)->except(['voucher_id', 'line_no'])->all());
});

it('is safe to run twice and can be undone', function () {
    $this->artisan('vouchers:import-legacy')->assertSuccessful();
    $this->artisan('vouchers:import-legacy')->assertSuccessful();
    expect(Voucher::count())->toBe(5);

    $this->artisan('vouchers:import-legacy --undo')->expectsConfirmation('Remove all imported voucher headers and unlink their rows?', 'yes')->assertSuccessful();
    expect(Voucher::withTrashed()->count())->toBe(0)
        ->and(ReceivingVoucher::whereNotNull('voucher_id')->count())->toBe(0);

    $this->artisan('vouchers:import-legacy')->assertSuccessful();
    expect(Voucher::count())->toBe(5);
});

it('writes nothing on a dry run', function () {
    $this->artisan('vouchers:import-legacy --dry-run')->assertSuccessful();
    expect(Voucher::count())->toBe(0);
});

it('keeps the 000001 series separate from imported numbers', function () {
    $this->artisan('vouchers:import-legacy');
    expect(Voucher::nextNumber())->toBe('000001');
});

it('edits an imported voucher in the new form and keeps its old number', function () {
    $this->artisan('vouchers:import-legacy');
    $header = Voucher::find($this->pv->fresh()->voucher_id);
    $oldNo = $header->voucher_no;

    $this->get(route('vouchers.edit', $header))->assertOk()->assertSee($oldNo);

    $this->put(route('vouchers.update', $header), [
        'date' => '2026-07-12',
        'payment_account_id' => $this->cash->id,
        'narration' => 'Supplies',
        'lines' => [['entry_type' => 'party', 'party_id' => $this->party->id, 'amount' => 1200]],
    ])->assertSessionHasNoErrors();

    $row = PaymentVoucher::where('voucher_id', $header->id)->sole();
    expect($row->voucher_no)->toBe($oldNo)
        ->and((float) $row->amount)->toBe(1200.0)
        ->and($header->fresh()->source)->toBe('legacy')
        ->and(Voucher::count())->toBe(5);
});

it('blocks editing vouchers owned by another module', function () {
    $this->artisan('vouchers:import-legacy');
    $header = Voucher::find($this->stockEv->fresh()->voucher_id);

    $this->get(route('vouchers.edit', $header))->assertRedirect(route('vouchers.print', $header));
    $this->delete(route('vouchers.destroy', $header))->assertRedirect(route('vouchers.print', $header));
    expect($header->fresh()->trashed())->toBeFalse();
});

it('gives rows created by other modules a header and keeps it in sync', function () {
    // e.g. the Billing page creating a receipt
    $rv = ReceivingVoucher::create(['date' => '2026-08-01', 'amount' => 700, 'received_from_type' => 'tenant', 'payment_method' => 'bank_transfer', 'payment_account_id' => $this->bank->id, 'notes' => 'Auto-generated from Billings page.', 'user_id' => $this->user->id]);

    $header = Voucher::find($rv->fresh()->voucher_id);
    expect($header)->not->toBeNull()
        ->and($header->voucher_no)->toBe($rv->fresh()->voucher_no)
        ->and($header->voucher_no)->toStartWith('PM-RV-')
        ->and($header->type)->toBe('bank_received')
        ->and($header->source)->toBe('legacy');

    $rv->fresh()->update(['amount' => 900, 'payment_account_id' => $this->cash->id]);
    expect((float) $header->fresh()->total_amount)->toBe(900.0)
        ->and($header->fresh()->type)->toBe('cash_received');

    $rv->fresh()->delete();
    expect($header->fresh()->trashed())->toBeTrue();
});

it('does not create extra headers for vouchers saved in the new form', function () {
    $this->post(route('vouchers.store'), [
        'type' => 'bank_paid', 'date' => '2026-09-30', 'payment_account_id' => $this->bank->id,
        'lines' => [['entry_type' => 'expense', 'expense_head_id' => $this->head->id, 'amount' => 100]],
    ])->assertSessionHasNoErrors();

    expect(Voucher::count())->toBe(1)->and(Voucher::first()->source)->toBe('form');
});

it('sends the old screens create/edit/delete to the new form', function () {
    $this->artisan('vouchers:import-legacy');
    $header = Voucher::find($this->pv->fresh()->voucher_id);

    $this->get(route('payment-vouchers.create'))->assertRedirect(route('vouchers.create', ['type' => 'cash_paid']));
    $this->get(route('receiving-vouchers.create'))->assertRedirect(route('vouchers.create', ['type' => 'cash_received']));
    $this->get(route('payment-vouchers.edit', $this->pv))->assertRedirect(route('vouchers.edit', $header));
    $this->delete(route('payment-vouchers.destroy', $this->pv))->assertRedirect(route('vouchers.edit', $header));
    expect($this->pv->fresh())->not->toBeNull();

    $this->get(route('expenses.edit', $this->stockEv))->assertRedirect(route('vouchers.print', Voucher::find($this->stockEv->fresh()->voucher_id)));

    // Lists and prints still work
    $this->get(route('payment-vouchers.index'))->assertOk();
    $this->get(route('payment-vouchers.print', $this->pv))->assertOk();
    $this->get(route('vouchers.index', ['source' => 'legacy']))->assertOk()->assertSee('Imported');
});
