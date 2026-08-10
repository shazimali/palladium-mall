<?php

namespace App\Http\Controllers;

use App\Models\OtherOwnedRentPurchaseVoucher;
use App\Models\Landlord;
use App\Models\Unit;
use App\Models\OtherTenant;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OtherOwnedRentPurchaseVoucherController extends Controller
{
    /**
     * Display a listing of ORP Vouchers.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_owned_rent_purchase_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = OtherOwnedRentPurchaseVoucher::with(['landlord', 'unit', 'otherTenant', 'user']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('voucher_no', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%")
                    ->orWhereHas('landlord', fn($l) => $l->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('unit', fn($u) => $u->where('unit_number', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('landlord_id')) {
            $query->where('landlord_id', $request->landlord_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        if ($request->filled('month')) {
            $query->whereYear('month', Carbon::parse($request->month)->year)
                  ->whereMonth('month', Carbon::parse($request->month)->month);
        }

        $totalAmount = (float) (clone $query)->sum('amount');

        $vouchers  = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $landlords = Landlord::orderBy('name')->get();

        return view('other_owned_rent_purchase_vouchers.index', [
            'title'       => 'Other Owned Rent Purchase Vouchers',
            'vouchers'    => $vouchers,
            'landlords'   => $landlords,
            'totalAmount' => $totalAmount,
        ]);
    }

    /**
     * Show form to create a new ORP Voucher.
     */
    public function create(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_owned_rent_purchase_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        $landlords     = Landlord::orderBy('name')->get();
        $nextVoucherNo = OtherOwnedRentPurchaseVoucher::getNextVoucherNo();

        return view('other_owned_rent_purchase_vouchers.create', [
            'title'         => 'New Other Owned Rent Purchase Voucher',
            'landlords'     => $landlords,
            'nextVoucherNo' => $nextVoucherNo,
        ]);
    }

    /**
     * Store a newly created ORP Voucher.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_owned_rent_purchase_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        // Strip commas from amount
        if ($request->has('amount') && $request->input('amount') !== null) {
            $request->merge(['amount' => str_replace(',', '', $request->input('amount'))]);
        }

        $data = $request->validate([
            'landlord_id'     => ['required', 'exists:landlords,id'],
            'unit_id'         => ['nullable', 'exists:units,id'],
            'other_tenant_id' => ['nullable', 'exists:other_tenants,id'],
            'month'           => ['required', 'date'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'date'            => ['required', 'date'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $data['user_id'] = auth()->id();

        $voucher = OtherOwnedRentPurchaseVoucher::create($data);

        ActivityLog::log('create_orp_voucher', "Created ORP Voucher {$voucher->voucher_no}", $voucher);

        return redirect()->route('other-owned-rent-purchase-vouchers.show', $voucher->id)
            ->with('success', 'ORP Voucher created successfully.');
    }

    /**
     * Display a specific ORP Voucher.
     */
    public function show(OtherOwnedRentPurchaseVoucher $otherOwnedRentPurchaseVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_owned_rent_purchase_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $otherOwnedRentPurchaseVoucher->load(['landlord', 'unit', 'otherTenant', 'user']);

        return view('other_owned_rent_purchase_vouchers.show', [
            'title'   => 'ORP Voucher — ' . $otherOwnedRentPurchaseVoucher->voucher_no,
            'voucher' => $otherOwnedRentPurchaseVoucher,
        ]);
    }

    /**
     * Show edit form for an ORP Voucher.
     */
    public function edit(OtherOwnedRentPurchaseVoucher $otherOwnedRentPurchaseVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_owned_rent_purchase_vouchers.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $otherOwnedRentPurchaseVoucher->load(['landlord', 'unit', 'otherTenant']);
        $landlords = Landlord::orderBy('name')->get();

        // Get units belonging to the voucher's landlord that are self-owned
        $units = Unit::where('is_self', true)
            ->where('landlord_id', $otherOwnedRentPurchaseVoucher->landlord_id)
            ->with(['otherTenant'])
            ->orderBy('unit_number')
            ->get();

        return view('other_owned_rent_purchase_vouchers.edit', [
            'title'   => 'Edit ORP Voucher — ' . $otherOwnedRentPurchaseVoucher->voucher_no,
            'voucher' => $otherOwnedRentPurchaseVoucher,
            'landlords' => $landlords,
            'units'   => $units,
        ]);
    }

    /**
     * Update an ORP Voucher.
     */
    public function update(Request $request, OtherOwnedRentPurchaseVoucher $otherOwnedRentPurchaseVoucher): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_owned_rent_purchase_vouchers.edit')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->has('amount') && $request->input('amount') !== null) {
            $request->merge(['amount' => str_replace(',', '', $request->input('amount'))]);
        }

        $data = $request->validate([
            'landlord_id'     => ['required', 'exists:landlords,id'],
            'unit_id'         => ['nullable', 'exists:units,id'],
            'other_tenant_id' => ['nullable', 'exists:other_tenants,id'],
            'month'           => ['required', 'date'],
            'amount'          => ['required', 'numeric', 'min:0.01'],
            'date'            => ['required', 'date'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ]);

        $otherOwnedRentPurchaseVoucher->update($data);

        ActivityLog::log('update_orp_voucher', "Updated ORP Voucher {$otherOwnedRentPurchaseVoucher->voucher_no}", $otherOwnedRentPurchaseVoucher);

        return redirect()->route('other-owned-rent-purchase-vouchers.show', $otherOwnedRentPurchaseVoucher->id)
            ->with('success', 'ORP Voucher updated successfully.');
    }

    /**
     * Soft-delete an ORP Voucher.
     */
    public function destroy(OtherOwnedRentPurchaseVoucher $otherOwnedRentPurchaseVoucher): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_owned_rent_purchase_vouchers.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $voucherNo = $otherOwnedRentPurchaseVoucher->voucher_no;
        $otherOwnedRentPurchaseVoucher->delete();

        ActivityLog::log('delete_orp_voucher', "Deleted ORP Voucher {$voucherNo}", null);

        return redirect()->route('other-owned-rent-purchase-vouchers.index')
            ->with('success', "ORP Voucher {$voucherNo} deleted successfully.");
    }

    /**
     * Print a single ORP Voucher.
     */
    public function print(OtherOwnedRentPurchaseVoucher $otherOwnedRentPurchaseVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_owned_rent_purchase_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $otherOwnedRentPurchaseVoucher->load(['landlord', 'unit', 'otherTenant', 'user']);

        return view('other_owned_rent_purchase_vouchers.print', [
            'title'   => 'Print ORP Voucher — ' . $otherOwnedRentPurchaseVoucher->voucher_no,
            'voucher' => $otherOwnedRentPurchaseVoucher,
        ]);
    }

    /**
     * AJAX: Get self-owned units and other tenants for a given landlord.
     */
    public function getLandlordUnits(Request $request)
    {
        $landlordId = $request->landlord_id;
        $units = Unit::where('is_self', true)
            ->where('landlord_id', $landlordId)
            ->with(['otherTenant', 'floor', 'block'])
            ->orderBy('unit_number')
            ->get()
            ->map(fn($u) => [
                'id'             => $u->id,
                'unit_number'    => $u->unit_number,
                'other_tenant'   => $u->otherTenant ? [
                    'id'           => $u->otherTenant->id,
                    'name'         => $u->otherTenant->name,
                    'monthly_rent' => (float) $u->otherTenant->monthly_rent,
                ] : null,
            ]);

        return response()->json(['units' => $units]);
    }
}
