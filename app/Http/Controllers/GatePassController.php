<?php

namespace App\Http\Controllers;

use App\Models\GatePass;
use App\Models\InventoryItem;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class GatePassController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeGatePass('gatepasses.view');

        $gatePasses = GatePass::with(['unit', 'user', 'items.inventoryItem'])
            ->when($request->search, function ($q) use ($request) {
                $q->where('gatepass_no', 'like', "%{$request->search}%")
                  ->orWhere('issued_to', 'like', "%{$request->search}%")
                  ->orWhere('purpose', 'like', "%{$request->search}%");
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.gate_passes.index', [
            'title'      => 'Gate Passes (Outflows)',
            'gatePasses' => $gatePasses,
        ]);
    }

    public function create(): View
    {
        $this->authorizeGatePass('gatepasses.manage');

        $items = InventoryItem::orderBy('name')->get();
        $units = Unit::orderBy('unit_number')->get();

        return view('inventory.gate_passes.create', [
            'title' => 'Create Gate Pass',
            'items' => $items,
            'units' => $units,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeGatePass('gatepasses.manage');

        $request->validate([
            'date'      => ['required', 'date'],
            'issued_to' => ['required', 'string', 'max:255'],
            'purpose'   => ['required', 'string', 'max:500'],
            'unit_id'   => ['nullable', 'exists:units,id'],
            'notes'     => ['nullable', 'string', 'max:1000'],
            'items'     => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'items.*.quantity'          => ['required', 'numeric', 'gt:0'],
            'items.*.notes'             => ['nullable', 'string', 'max:255'],
        ]);

        // Validate stock levels before saving to prevent negative stock
        foreach ($request->items as $itemData) {
            $invItem = InventoryItem::findOrFail($itemData['inventory_item_id']);
            if ($invItem->current_quantity < $itemData['quantity']) {
                return back()->withInput()->with('error', "Insufficient stock for '{$invItem->name}'. Current stock: {$invItem->current_quantity} {$invItem->unit_of_measure}, requested: {$itemData['quantity']}.");
            }
        }

        try {
            DB::beginTransaction();

            // Create Gate Pass parent record
            $gatePass = GatePass::create([
                'date'      => $request->date,
                'issued_to' => $request->issued_to,
                'purpose'   => $request->purpose,
                'unit_id'   => $request->unit_id,
                'status'    => 'Issued',
                'notes'     => $request->notes,
                'user_id'   => auth()->id(),
            ]);

            // Create Line Items (which triggers InventoryItem decrement events)
            foreach ($request->items as $itemData) {
                $gatePass->items()->create([
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'quantity'          => $itemData['quantity'],
                    'notes'             => $itemData['notes'],
                ]);
            }

            DB::commit();

            return redirect()->route('gate-passes.index')
                ->with('success', 'Gate Pass issued successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error issuing Gate Pass: ' . $e->getMessage());
        }
    }

    public function show(GatePass $gatePass): View
    {
        $this->authorizeGatePass('gatepasses.view');

        $gatePass->load(['items.inventoryItem', 'unit', 'user']);

        return view('inventory.gate_passes.show', [
            'title'    => 'Gate Pass Details — ' . $gatePass->gatepass_no,
            'gatePass' => $gatePass,
        ]);
    }

    public function cancel(GatePass $gatePass): RedirectResponse
    {
        $this->authorizeGatePass('gatepasses.manage');

        if ($gatePass->status === 'Cancelled') {
            return back()->with('error', 'Gate Pass is already cancelled.');
        }

        try {
            DB::beginTransaction();

            // Eloquent update will trigger static::updating event which restores stock
            $gatePass->update(['status' => 'Cancelled']);

            DB::commit();

            return redirect()->route('gate-passes.index')
                ->with('success', 'Gate Pass cancelled and item quantities restored to inventory.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error cancelling Gate Pass: ' . $e->getMessage());
        }
    }

    public function destroy(GatePass $gatePass): RedirectResponse
    {
        $this->authorizeGatePass('gatepasses.manage');

        try {
            DB::beginTransaction();

            // Eloquent delete will trigger static::deleting which:
            // 1. Restores the stock (if Issued)
            // 2. Deletes the child items
            $gatePass->delete();

            DB::commit();

            return redirect()->route('gate-passes.index')
                ->with('success', 'Gate Pass deleted and inventory levels rolled back.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('gate-passes.index')
                ->with('error', 'Error deleting Gate Pass: ' . $e->getMessage());
        }
    }

    public function print(GatePass $gatePass): View
    {
        $this->authorizeGatePass('gatepasses.view');

        $gatePass->load(['items.inventoryItem', 'unit', 'user']);

        return view('inventory.gate_passes.print', [
            'title'    => 'Print Gate Pass — ' . $gatePass->gatepass_no,
            'gatePass' => $gatePass,
        ]);
    }

    private function authorizeGatePass($permission)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->hasPermission($permission)) {
            abort(403, 'Unauthorized action.');
        }
    }
}
