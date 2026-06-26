<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeInventory('inventory.view');

        $items = InventoryItem::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('category', 'like', "%{$request->search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.items.index', [
            'title' => 'Inventory Items (Stock)',
            'items' => $items,
        ]);
    }

    public function create(): View
    {
        $this->authorizeInventory('inventory.manage');

        return view('inventory.items.create', [
            'title' => 'Register Inventory Item',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeInventory('inventory.manage');

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'category'        => ['nullable', 'string', 'max:255'],
            'unit_of_measure' => ['required', 'string', 'max:100'],
            'min_stock_level' => ['required', 'numeric', 'min:0'],
        ]);

        InventoryItem::create($data);

        return redirect()->route('items.index')
            ->with('success', 'Inventory item registered successfully.');
    }

    public function edit(InventoryItem $item): View
    {
        $this->authorizeInventory('inventory.manage');

        return view('inventory.items.edit', [
            'title' => 'Edit Item — ' . $item->name,
            'item'  => $item,
        ]);
    }

    public function update(Request $request, InventoryItem $item): RedirectResponse
    {
        $this->authorizeInventory('inventory.manage');

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'category'        => ['nullable', 'string', 'max:255'],
            'unit_of_measure' => ['required', 'string', 'max:100'],
            'min_stock_level' => ['required', 'numeric', 'min:0'],
        ]);

        $item->update($data);

        return redirect()->route('items.index')
            ->with('success', 'Inventory item updated successfully.');
    }

    public function destroy(InventoryItem $item): RedirectResponse
    {
        $this->authorizeInventory('inventory.manage');

        // Check if there are any recorded stock entries or gate passes referencing this item
        if (\DB::table('stock_entry_items')->where('inventory_item_id', $item->id)->exists() ||
            \DB::table('gate_pass_items')->where('inventory_item_id', $item->id)->exists()) {
            return redirect()->route('items.index')
                ->with('error', 'Cannot delete item because it has recorded transactions (Stock-In or Gate Pass).');
        }

        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Inventory item deleted successfully.');
    }

    private function authorizeInventory($permission)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->hasPermission($permission)) {
            abort(403, 'Unauthorized action.');
        }
    }
}
