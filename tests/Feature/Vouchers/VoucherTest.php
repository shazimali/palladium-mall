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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'super-admin', 'display_name' => 'Super Admin']);
    $this->user->roles()->attach($role);
    $this->actingAs($this->user);

    $this->cash = PaymentAccount::forceCreate(['id' => Voucher::DEFAULT_CASH_ACCOUNT_ID, 'name' => 'CASH', 'type' => 'other', 'opening_balance' => 0]);
    $this->bank = PaymentAccount::create(['name' => 'HBL', 'type' => 'bank_transfer', 'opening_balance' => 100000]);
    $this->party = Party::create(['name' => 'ABC Traders']);
    $this->head = ExpenseHead::create(['name' => 'Electricity']);

    $this->unit = Unit::create(['unit_number' => 'S-01', 'type' => 'shop']);
    DB::table('payments')->insert([
        ['unit_id' => $this->unit->id, 'type' => 'rent', 'month' => '2026-07-01', 'due_date' => '2026-07-05', 'amount' => 5000, 'amount_paid' => 0, 'status' => 'unpaid', 'created_at' => now(), 'updated_at' => now()],
        ['unit_id' => $this->unit->id, 'type' => 'rent', 'month' => '2026-08-01', 'due_date' => '2026-08-05', 'amount' => 5000, 'amount_paid' => 0, 'status' => 'unpaid', 'created_at' => now(), 'updated_at' => now()],
    ]);
});

function receivedPayload(array $overrides = []): array
{
    return array_merge([
        'type' => 'cash_received',
        'date' => '2026-09-30',
        'payment_account_id' => test()->cash->id,
        'manual_voucher_no' => 'M-100',
        'narration' => 'Collection',
        'lines' => [
            ['entry_type' => 'tenant', 'unit_id' => test()->unit->id, 'amount' => 7000],
            ['entry_type' => 'party', 'party_id' => test()->party->id, 'amount' => 3000, 'notes' => 'Scrap sale'],
        ],
    ], $overrides);
}

it('posts a multi-line cash received voucher into the legacy tables', function () {
    $this->post(route('vouchers.store'), receivedPayload())->assertRedirect();

    $voucher = Voucher::firstOrFail();
    expect($voucher->voucher_no)->toBe('000001')
        ->and((float) $voucher->total_amount)->toBe(10000.0);

    $rv = ReceivingVoucher::where('voucher_id', $voucher->id)->firstOrFail();
    expect($rv->voucher_no)->toBe('000001/1')
        ->and($rv->manual_voucher_no)->toBe('M-100/1')
        ->and((float) $rv->amount)->toBe(7000.0)
        ->and($rv->payment_account_id)->toBe($this->cash->id);

    // Oldest month fully paid first, remainder on the next month
    $payments = Payment::orderBy('month')->get();
    expect($payments[0]->status)->toBe('paid')
        ->and((float) $payments[1]->amount_paid)->toBe(2000.0);

    $grv = GeneralReceivingVoucher::where('voucher_id', $voucher->id)->firstOrFail();
    expect($grv->voucher_no)->toBe('000001/2')
        ->and($grv->party_id)->toBe($this->party->id)
        ->and($grv->notes)->toBe('Scrap sale');

    // The account balance (used by ledgers/cash book) reflects both lines
    expect($this->cash->fresh()->current_balance)->toBe(10000.0);
});

it('posts a bank paid voucher with expense and party lines', function () {
    $this->post(route('vouchers.store'), [
        'type' => 'bank_paid',
        'date' => '2026-09-30',
        'payment_account_id' => $this->bank->id,
        'lines' => [
            ['entry_type' => 'expense', 'expense_head_id' => $this->head->id, 'amount' => 1500],
            ['entry_type' => 'party', 'party_id' => $this->party->id, 'amount' => 2500],
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $voucher = Voucher::firstOrFail();
    expect(Expense::where('voucher_id', $voucher->id)->value('expense_head_id'))->toBe($this->head->id);

    $pv = PaymentVoucher::where('voucher_id', $voucher->id)->firstOrFail();
    expect($pv->paid_to_type)->toBe('other')
        ->and($pv->party_id)->toBe($this->party->id);

    expect($this->bank->fresh()->current_balance)->toBe(96000.0);
});

it('uses a single number without a line suffix for one-line vouchers', function () {
    $this->post(route('vouchers.store'), receivedPayload([
        'manual_voucher_no' => null,
        'lines' => [['entry_type' => 'party', 'party_id' => $this->party->id, 'amount' => 500]],
    ]))->assertSessionHasNoErrors();

    expect(GeneralReceivingVoucher::firstOrFail()->voucher_no)->toBe('000001');
});

it('numbers vouchers as one shared series across types', function () {
    $this->post(route('vouchers.store'), receivedPayload(['manual_voucher_no' => null]));
    $this->post(route('vouchers.store'), [
        'type' => 'bank_paid', 'date' => '2026-09-30', 'payment_account_id' => $this->bank->id,
        'lines' => [['entry_type' => 'expense', 'expense_head_id' => $this->head->id, 'amount' => 100]],
    ]);

    expect(Voucher::orderBy('id')->pluck('voucher_no')->all())->toBe(['000001', '000002']);
});

it('rejects an overdraft on a paid voucher and writes nothing', function () {
    $this->post(route('vouchers.store'), [
        'type' => 'cash_paid', 'date' => '2026-09-30', 'payment_account_id' => $this->cash->id,
        'lines' => [['entry_type' => 'expense', 'expense_head_id' => $this->head->id, 'amount' => 100]],
    ])->assertSessionHasErrors('payment_account_id');

    expect(Voucher::count())->toBe(0)->and(Expense::count())->toBe(0);
});

it('rejects a cash account on a bank voucher', function () {
    $this->post(route('vouchers.store'), receivedPayload(['type' => 'bank_received']))
        ->assertSessionHasErrors('payment_account_id');
});

it('rejects a tenant receipt larger than the outstanding dues', function () {
    $this->post(route('vouchers.store'), receivedPayload([
        'lines' => [['entry_type' => 'tenant', 'unit_id' => $this->unit->id, 'amount' => 20000]],
    ]))->assertSessionHasErrors('lines.0.amount');

    expect(Voucher::count())->toBe(0)->and(Payment::sum('amount_paid'))->toEqual(0);
});

it('re-posts lines on update and keeps allocations consistent', function () {
    $this->post(route('vouchers.store'), receivedPayload());
    $voucher = Voucher::firstOrFail();

    $this->put(route('vouchers.update', $voucher), receivedPayload([
        'lines' => [['entry_type' => 'tenant', 'unit_id' => $this->unit->id, 'amount' => 4000]],
    ]))->assertSessionHasNoErrors();

    expect(ReceivingVoucher::withTrashed()->count())->toBe(1)
        ->and(GeneralReceivingVoucher::withTrashed()->count())->toBe(0)
        ->and(ReceivingVoucher::first()->voucher_no)->toBe('000001')
        ->and((float) Payment::sum('amount_paid'))->toBe(4000.0)
        ->and((float) $voucher->fresh()->total_amount)->toBe(4000.0);
});

it('reverses all lines and tenant allocations on delete', function () {
    $this->post(route('vouchers.store'), receivedPayload());
    $voucher = Voucher::firstOrFail();

    $this->delete(route('vouchers.destroy', $voucher))->assertRedirect(route('vouchers.index'));

    expect(Voucher::count())->toBe(0)
        ->and(ReceivingVoucher::count())->toBe(0)
        ->and(GeneralReceivingVoucher::count())->toBe(0)
        ->and((float) Payment::sum('amount_paid'))->toBe(0.0)
        ->and(Payment::where('status', 'unpaid')->count())->toBe(2)
        ->and($this->cash->fresh()->current_balance)->toBe(0.0);
});

it('renders the list, form, edit and print pages', function () {
    $this->post(route('vouchers.store'), receivedPayload());
    $voucher = Voucher::firstOrFail();

    $this->get(route('vouchers.index'))->assertOk()->assertSee('000001');
    $this->get(route('vouchers.create', ['type' => 'bank_paid']))->assertOk();
    $this->get(route('vouchers.edit', $voucher))->assertOk();
    $this->get(route('vouchers.print', $voucher))->assertOk()->assertSee('ABC Traders')->assertSee('S-01');
    $this->get(route('vouchers.print-list'))->assertOk();
});

it('forbids users without voucher permissions', function () {
    $this->actingAs(User::factory()->create());
    $this->get(route('vouchers.index'))->assertForbidden();
});

it('shows voucher lines in the existing account ledger without ledger changes', function () {
    $this->post(route('vouchers.store'), receivedPayload());
    $voucher = Voucher::firstOrFail();

    $this->get(route('ledgers.payment-account', ['payment_account_id' => $this->cash->id]))
        ->assertOk()
        ->assertSee('000001/1')
        ->assertSee('000001/2')
        ->assertSee('M-100/1');

    // The unified All Ledgers page links line numbers to the new voucher
    $this->get(route('ledgers.all', ['ledger_type' => 'payment_account', 'payment_account_id' => $this->cash->id]))
        ->assertOk()
        ->assertSee(route('vouchers.print', $voucher), false);
});

it('uses only account id 2 as the cash account and pre-selects it on cash vouchers', function () {
    $otherCash = PaymentAccount::create(['name' => 'CARE TAKER (CASH)', 'type' => 'other', 'opening_balance' => 50000]);

    $this->get(route('vouchers.create', ['type' => 'cash_received']))
        ->assertOk()
        ->assertSee("paymentAccountId: '{$this->cash->id}'", false)
        ->assertDontSee('CARE TAKER (CASH) (Bal', false);

    // Bank vouchers list every other account (whatever its type) and have no default
    $this->get(route('vouchers.create', ['type' => 'bank_received']))
        ->assertOk()
        ->assertSee("paymentAccountId: ''", false)
        ->assertSee('CARE TAKER (CASH) (Bal', false)
        ->assertDontSee('>CASH (Bal', false);

    $this->post(route('vouchers.store'), receivedPayload(['type' => 'bank_received', 'payment_account_id' => $this->cash->id]))
        ->assertSessionHasErrors('payment_account_id');

    $this->post(route('vouchers.store'), receivedPayload(['payment_account_id' => $otherCash->id]))
        ->assertSessionHasErrors('payment_account_id');

    $this->post(route('vouchers.store'), receivedPayload(['type' => 'bank_received', 'payment_account_id' => $otherCash->id]))
        ->assertSessionHasNoErrors();
});

it('allocates a tenant receipt only to the ticked outstanding dues', function () {
    [$july, $august] = Payment::orderBy('month')->get()->all();

    $this->get(route('ajax.tenant-pending-payments', ['unit_id' => $this->unit->id]))
        ->assertOk()
        ->assertJsonCount(2, 'payments');

    $this->post(route('vouchers.store'), receivedPayload([
        'manual_voucher_no' => null,
        'lines' => [['entry_type' => 'tenant', 'unit_id' => $this->unit->id, 'payment_ids' => [$august->id], 'amount' => 5000]],
    ]))->assertSessionHasNoErrors();

    expect($july->fresh()->status)->toBe('unpaid')
        ->and($august->fresh()->status)->toBe('paid');

    // Editing shows the ticked dues on the entry
    $voucher = Voucher::firstOrFail();
    $this->get(route('vouchers.edit', $voucher))->assertOk()->assertSee((string) $august->id);
});

it('rejects an amount larger than the ticked dues', function () {
    $july = Payment::orderBy('month')->first();

    $this->post(route('vouchers.store'), receivedPayload([
        'lines' => [['entry_type' => 'tenant', 'unit_id' => $this->unit->id, 'payment_ids' => [$july->id], 'amount' => 6000]],
    ]))->assertSessionHasErrors('lines.0.amount');

    expect(Voucher::count())->toBe(0);
});

it('returns saved lines as entered for the edit form', function () {
    $this->post(route('vouchers.store'), receivedPayload(['narration' => 'Monthly collection']));
    $lines = Voucher::firstOrFail()->lines();

    expect($lines[0]['entry_type'])->toBe('tenant')
        ->and($lines[0]['unit_id'])->toBe($this->unit->id)
        ->and($lines[0]['payment_ids'])->toHaveCount(2)
        ->and($lines[0]['notes'])->toBeNull()           // left blank on the form
        ->and($lines[1]['notes'])->toBe('Scrap sale');  // typed on the form
});

it('lists the security deposit payable to a tenant and refunds it', function () {
    $tenant = \App\Models\Tenant::create(['name' => 'Ali Khan', 'cnic' => '35202-1234567-1', 'phone' => '03001234567', 'unit_id' => $this->unit->id, 'status' => 'active']);
    Payment::create(['tenant_id' => $tenant->id, 'unit_id' => $this->unit->id, 'type' => 'security_deposit', 'month' => '2026-01-01', 'due_date' => '2026-01-05', 'amount' => 20000, 'amount_paid' => 20000, 'status' => 'paid']);

    $this->get(route('vouchers.tenant-payables', ['tenant_id' => $tenant->id]))
        ->assertOk()
        ->assertJsonPath('units.0.unit_number', 'S-01')
        ->assertJsonPath('units.0.pending', 20000);

    // The tenant list (not units) is offered on paid vouchers
    $this->get(route('vouchers.create', ['type' => 'bank_paid']))->assertOk()->assertSee('Ali Khan (S-01)');

    $this->post(route('vouchers.store'), [
        'type' => 'bank_paid', 'date' => '2026-09-30', 'payment_account_id' => $this->bank->id,
        'lines' => [['entry_type' => 'tenant', 'tenant_id' => $tenant->id, 'unit_id' => $this->unit->id, 'amount' => 15000]],
    ])->assertSessionHasNoErrors();

    $voucher = Voucher::firstOrFail();
    expect(PaymentVoucher::where('voucher_id', $voucher->id)->sole())
        ->tenant_id->toBe($tenant->id)
        ->unit_id->toBe($this->unit->id);

    $this->get(route('vouchers.tenant-payables', ['tenant_id' => $tenant->id]))->assertJsonPath('units.0.pending', 5000);
    // While editing this voucher its own refund still counts as payable
    $this->get(route('vouchers.tenant-payables', ['tenant_id' => $tenant->id, 'exclude_voucher_id' => $voucher->id]))->assertJsonPath('units.0.pending', 20000);

    expect($voucher->lines()[0]['refund_label'])->toBe('Unit S-01 security deposit refund');

    // Refunding more than is payable is rejected
    $this->post(route('vouchers.store'), [
        'type' => 'bank_paid', 'date' => '2026-09-30', 'payment_account_id' => $this->bank->id,
        'lines' => [['entry_type' => 'tenant', 'tenant_id' => $tenant->id, 'unit_id' => $this->unit->id, 'amount' => 6000]],
    ])->assertSessionHasErrors('lines.0.amount');
});
