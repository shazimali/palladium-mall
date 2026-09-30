<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Header for a multi-line Cash/Bank Received/Paid voucher. The lines themselves
 * are rows in receiving_vouchers, general_receiving_vouchers, payment_vouchers
 * and expenses (linked by voucher_id), so every existing ledger picks them up.
 */
class Voucher extends Model
{
    use SoftDeletes, LogsActivity;

    public const TYPES = [
        'cash_received' => 'Cash Received',
        'cash_paid'     => 'Cash Paid',
        'bank_received' => 'Bank Received',
        'bank_paid'     => 'Bank Paid',
    ];

    public const RECEIVED_ENTRY_TYPES = [
        'tenant'   => 'Tenant (Unit)',
        'party'    => 'Party',
        'landlord' => 'Landlord',
        'account'  => 'Cash / Bank Account',
    ];

    public const PAID_ENTRY_TYPES = [
        'expense'  => 'Expense Head',
        'party'    => 'Party',
        'tenant'   => 'Tenant (Security Refund)',
        'landlord' => 'Landlord',
        'owner'    => 'Owner',
        'account'  => 'Cash / Bank Account',
    ];

    /**
     * The mall's only cash account ("CASH", same account the Cash Book uses).
     * Every other payment account is treated as a bank account.
     */
    public const DEFAULT_CASH_ACCOUNT_ID = 2;

    /** Where the header came from; see the add_source_to_vouchers migration. */
    public const SOURCES = [
        'form'        => 'New Voucher',
        'legacy'      => 'Imported',
        'stock_entry' => 'Stock Entry',
        'move_out'    => 'Move-out',
    ];

    /** Headers wrapping rows owned by another module; they are edited in that module. */
    public const LOCKED_SOURCES = ['stock_entry', 'move_out'];

    protected $fillable = [
        'voucher_no',
        'type',
        'source',
        'manual_voucher_no',
        'date',
        'payment_account_id',
        'total_amount',
        'reference',
        'narration',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Next number in the shared, prefix-less 6-digit series (000001, 000002, ...).
     * Must be called inside a transaction; the lock stops two users getting the same number.
     */
    public static function nextNumber(bool $lock = false): string
    {
        // Only form vouchers use the 6-digit series; imported ones keep numbers like PM-RV-00012
        $query = static::withTrashed()->where('source', 'form');
        if ($lock) {
            $query->lockForUpdate();
        }
        $max = (int) $query->max('voucher_no');

        return str_pad((string) ($max + 1), 6, '0', STR_PAD_LEFT);
    }

    public static function isCashType(string $type): bool
    {
        return str_starts_with($type, 'cash_');
    }

    public static function isCashAccount(PaymentAccount $account): bool
    {
        return (int) $account->id === self::DEFAULT_CASH_ACCOUNT_ID;
    }

    public static function isReceivedType(string $type): bool
    {
        return str_ends_with($type, '_received');
    }

    public static function entryTypesFor(string $type): array
    {
        return static::isReceivedType($type) ? static::RECEIVED_ENTRY_TYPES : static::PAID_ENTRY_TYPES;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }

    public function isLocked(): bool
    {
        return in_array($this->source, self::LOCKED_SOURCES, true);
    }

    public function isReceived(): bool
    {
        return static::isReceivedType($this->type);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function receivingVouchers(): HasMany
    {
        return $this->hasMany(ReceivingVoucher::class);
    }

    public function generalReceivingVouchers(): HasMany
    {
        return $this->hasMany(GeneralReceivingVoucher::class);
    }

    public function paymentVouchers(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * All line rows normalised to one shape, ordered by line_no.
     * Each item: line_no, entry_type, label (account/party name), amount, notes, model.
     */
    public function lines(): Collection
    {
        $lines = collect();

        foreach ($this->receivingVouchers()->with(['tenant', 'payments'])->get() as $row) {
            $unitId = $row->payments->first()?->unit_id;
            $unit = $unitId ? Unit::find($unitId) : null;
            $lines->push($this->makeLine($row, 'tenant', trim(($unit?->unit_number ? $unit->unit_number . ' - ' : '') . ($row->tenant?->name ?? '')), [
                'unit_id' => $unitId,
                'rv_id' => $row->id,
                'payment_ids' => $row->payments->pluck('id')->map(fn($id) => (string) $id)->all(),
                'payment_labels' => $row->payments->map(fn($p) => ($p->month?->format('M Y') ?? '') . ' ' . $p->type_label)->all(),
            ]));
        }

        foreach ($this->generalReceivingVouchers()->with(['party', 'landlord', 'fromPaymentAccount'])->get() as $row) {
            $type = $row->received_from_type ?: 'party';
            $label = match ($type) {
                'landlord' => $row->landlord?->name,
                'account'  => $row->fromPaymentAccount?->name,
                default    => $row->party?->name,
            };
            $lines->push($this->makeLine($row, $type, (string) $label, [
                'party_id' => $row->party_id,
                'landlord_id' => $row->landlord_id,
                'account_id' => $row->from_payment_account_id,
            ]));
        }

        foreach ($this->paymentVouchers()->with(['toPaymentAccount', 'unit'])->get() as $row) {
            // Legacy PV stores party payments as paid_to_type = 'other'
            $type = in_array($row->paid_to_type, [null, '', 'other'], true) ? 'party' : $row->paid_to_type;
            $label = $type === 'account' ? $row->toPaymentAccount?->name : $row->other_name;
            $lines->push($this->makeLine($row, $type, (string) $label, [
                'party_id' => $row->party_id,
                'tenant_id' => $row->tenant_id,
                'unit_id' => $row->unit_id,
                'refund_label' => $type === 'tenant' && $row->unit ? 'Unit ' . $row->unit->unit_number . ' security deposit refund' : null,
                'landlord_id' => $row->landlord_id,
                'owner_id' => $row->owner_id,
                'account_id' => $row->to_payment_account_id,
            ]));
        }

        foreach ($this->expenses()->with('expenseHead')->get() as $row) {
            $lines->push($this->makeLine($row, 'expense', (string) $row->expenseHead?->name, [
                'expense_head_id' => $row->expense_head_id,
            ]));
        }

        return $lines->sortBy('line_no')->values();
    }

    private function makeLine(Model $row, string $entryType, string $label, array $ids): array
    {
        return array_merge([
            'line_no' => (int) $row->line_no,
            'entry_type' => $entryType,
            'label' => $label,
            'amount' => (float) $row->amount,
            // Blank line remarks are saved as the voucher narration (so ledgers show a description);
            // report them as blank so the edit form shows the entry as it was entered.
            'notes' => $row->notes === $this->narration ? null : $row->notes,
            'voucher_no' => $row->voucher_no,
            'model' => $row,
        ], $ids);
    }
}
