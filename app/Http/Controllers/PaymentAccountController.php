<?php
 
namespace App\Http\Controllers;
 
use App\Models\PaymentAccount;
use App\Http\Requests\StorePaymentAccountRequest;
use App\Http\Requests\UpdatePaymentAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
 
class PaymentAccountController extends Controller
{
    public function index(Request $request): View
    {
        $paymentAccounts = PaymentAccount::query()
            ->withSum(['payments as total_received' => function ($q) {
                $q->whereIn('status', ['paid', 'partial']);
            }], 'amount_paid')
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('bank_name', 'like', "%{$request->search}%")
                    ->orWhere('account_number', 'like', "%{$request->search}%")
                    ->orWhere('account_holder', 'like', "%{$request->search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
 
        return view('payment_accounts.index', [
            'title' => 'Payment Accounts',
            'paymentAccounts' => $paymentAccounts,
        ]);
    }
 
    public function create(): View
    {
        return view('payment_accounts.create', [
            'title' => 'Add New Payment Account',
        ]);
    }
 
    public function store(StorePaymentAccountRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;
 
        PaymentAccount::create($data);
 
        return redirect()->route('payment-accounts.index')
            ->with('success', 'Payment account created successfully.');
    }
 
    public function show(PaymentAccount $paymentAccount): View
    {
        $paymentAccount->loadSum(['payments as total_received' => function ($q) {
            $q->whereIn('status', ['paid', 'partial']);
        }], 'amount_paid');
 
        $payments = $paymentAccount->payments()
            ->with(['tenant', 'unit'])
            ->latest('paid_at')
            ->paginate(20);
 
        return view('payment_accounts.show', [
            'title' => 'Payment Account — ' . $paymentAccount->name,
            'paymentAccount' => $paymentAccount,
            'payments' => $payments,
        ]);
    }
 
    public function edit(PaymentAccount $paymentAccount): View
    {
        return view('payment_accounts.edit', [
            'title' => 'Edit Payment Account — ' . $paymentAccount->name,
            'paymentAccount' => $paymentAccount,
        ]);
    }
 
    public function update(UpdatePaymentAccountRequest $request, PaymentAccount $paymentAccount): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->has('is_active') ? true : false;
 
        $paymentAccount->update($data);
 
        return redirect()->route('payment-accounts.index')
            ->with('success', 'Payment account updated successfully.');
    }
 
    public function destroy(PaymentAccount $paymentAccount): RedirectResponse
    {
        $paymentAccount->delete();
 
        return redirect()->route('payment-accounts.index')
            ->with('success', 'Payment account deleted successfully.');
    }
}
