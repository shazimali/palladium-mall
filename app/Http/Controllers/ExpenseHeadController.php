<?php

namespace App\Http\Controllers;

use App\Models\ExpenseHead;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExpenseHeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expense_heads.view')) {
            abort(403, 'Unauthorized action.');
        }

        $expenseHeads = ExpenseHead::query()
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('expense_heads.index', [
            'title'        => 'Expense Heads',
            'expenseHeads' => $expenseHeads,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expense_heads.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('expense_heads.create', [
            'title' => 'Create Expense Head',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expense_heads.create')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:expense_heads,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        ExpenseHead::create($data);

        return redirect()->route('expense-heads.index')
            ->with('success', 'Expense Head created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExpenseHead $expenseHead): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expense_heads.edit')) {
            abort(403, 'Unauthorized action.');
        }

        return view('expense_heads.edit', [
            'title'       => 'Edit Expense Head — ' . $expenseHead->name,
            'expenseHead' => $expenseHead,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExpenseHead $expenseHead): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expense_heads.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:expense_heads,name,' . $expenseHead->id],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $expenseHead->update($data);

        return redirect()->route('expense-heads.index')
            ->with('success', 'Expense Head updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseHead $expenseHead): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expense_heads.delete')) {
            abort(403, 'Unauthorized action.');
        }

        // Check if there are any associated expenses
        if ($expenseHead->expenses()->exists()) {
            return redirect()->route('expense-heads.index')
                ->with('error', 'Cannot delete Expense Head because it has associated recorded expenses. Please re-assign or delete those expenses first.');
        }

        $expenseHead->delete();

        return redirect()->route('expense-heads.index')
            ->with('success', 'Expense Head deleted successfully.');
    }
}
