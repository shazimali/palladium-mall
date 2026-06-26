<?php

namespace App\Http\Controllers;

use App\Models\StockEntry;
use App\Models\InventoryItem;
use App\Models\PaymentAccount;
use App\Models\Expense;
use App\Models\ExpenseHead;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class StockEntryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeInventory('inventory.view');

        $entries = StockEntry::with(['paymentAccount', 'expense', 'user', 'items.inventoryItem'])
            ->when($request->search, function ($q) use ($request) {
                $q->where('entry_no', 'like', "%{$request->search}%")
                  ->orWhere('notes', 'like', "%{$request->search}%");
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.stock_entries.index', [
            'title'   => 'Stock Inflows & Adjustments',
            'entries' => $entries,
        ]);
    }

    public function create(): View
    {
        $this->authorizeInventory('inventory.manage');

        $items = InventoryItem::orderBy('name')->get();
        $accounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('inventory.stock_entries.create', [
            'title'    => 'Record Stock Inflow',
            'items'    => $items,
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeInventory('inventory.manage');

        $request->validate([
            'date'               => ['required', 'date'],
            'type'               => ['required', 'in:IN,ADJUST'],
            'payment_account_id' => ['nullable', 'exists:payment_accounts,id'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'items.*.quantity'   => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            // Create Stock Entry
            $entry = StockEntry::create([
                'date'               => $request->date,
                'type'               => $request->type,
                'payment_account_id' => $request->payment_account_id,
                'notes'              => $request->notes,
                'user_id'            => auth()->id(),
            ]);

            $totalAmount = 0.00;

            // Create Line Items (which triggers InventoryItem increment events)
            foreach ($request->items as $itemData) {
                $entry->items()->create([
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'quantity'          => $itemData['quantity'],
                    'unit_price'        => $itemData['unit_price'],
                ]);
                $totalAmount += ($itemData['quantity'] * $itemData['unit_price']);
            }

            // Create Linked Expense Voucher if Payment Account is set
            if ($request->payment_account_id && $totalAmount > 0.01) {
                // Find or create "Repair & Maintenance" head
                $head = ExpenseHead::firstOrCreate(
                    ['name' => 'Repair & Maintenance'],
                    ['description' => 'HVAC maintenance, electrical repairs, plumbing work, etc.']
                );

                $account = PaymentAccount::find($request->payment_account_id);

                $expense = Expense::create([
                    'expense_head_id'    => $head->id,
                    'amount'             => $totalAmount,
                    'date'               => $request->date,
                    'payment_method'     => $account ? ucfirst($account->type) : 'Bank',
                    'payment_account_id' => $request->payment_account_id,
                    'reference'          => 'Stock In #' . $entry->entry_no,
                    'notes'              => 'Auto-generated from Stock Inflow: ' . $entry->entry_no . '. ' . ($request->notes ?? ''),
                    'user_id'            => auth()->id(),
                ]);

                // Link expense back to StockEntry
                $entry->update(['expense_id' => $expense->id]);
            }

            DB::commit();

            return redirect()->route('stock-entries.index')
                ->with('success', 'Stock inflow recorded successfully.' . ($entry->expense_id ? ' Associated expense voucher generated.' : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error saving stock inflow: ' . $e->getMessage());
        }
    }

    public function show(StockEntry $stockEntry): View
    {
        $this->authorizeInventory('inventory.view');

        $stockEntry->load(['items.inventoryItem', 'paymentAccount', 'expense', 'user']);

        return view('inventory.stock_entries.show', [
            'title' => 'Stock Inflow Details — ' . $stockEntry->entry_no,
            'entry' => $stockEntry,
        ]);
    }

    public function destroy(StockEntry $stockEntry): RedirectResponse
    {
        $this->authorizeInventory('inventory.manage');

        try {
            DB::beginTransaction();

            // Eloquent deletion will run booted static::deleting which:
            // 1. Deletes the associated expense voucher
            // 2. Deletes each child item, causing their deleted events to decrement/rollback stock quantities
            $stockEntry->delete();

            DB::commit();

            return redirect()->route('stock-entries.index')
                ->with('success', 'Stock entry deleted and inventory levels rolled back.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('stock-entries.index')
                ->with('error', 'Error deleting stock entry: ' . $e->getMessage());
        }
    }

    private function authorizeInventory($permission)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->hasPermission($permission)) {
            abort(403, 'Unauthorized action.');
        }
    }
}
