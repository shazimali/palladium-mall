@php
    $search = request('search');
    $highlight = $highlight ?? function ($text) use ($search) {
        if (empty($text))
            return '';
        if (empty($search)) {
            return e($text);
        }
        $escapedSearch = preg_quote($search, '/');
        return preg_replace('/(' . $escapedSearch . ')/i', '<mark class="bg-amber-100 text-amber-900 rounded px-0.5 dark:bg-amber-950/70 dark:text-amber-300 font-medium">$1</mark>', e($text));
    };

    // Group payments by unit_id and month
    $groupedPayments = $payments->groupBy(function($payment) {
        return $payment->unit_id . '_' . ($payment->month ? $payment->month->format('Y-m') : 'no-month');
    });
@endphp

@isset($summary)
<div id="ajax-paginator-meta" class="hidden" 
     data-total="{{ $payments->total() }}"
     data-due="Rs. {{ number_format($summary['total_due']) }}"
     data-paid="Rs. {{ number_format($summary['total_paid']) }}"
     data-unpaid="{{ $summary['unpaid_count'] }}"
     data-overdue="{{ $summary['overdue_count'] }}"></div>
@else
<div id="ajax-paginator-meta" class="hidden" 
     data-total="{{ $payments->total() }}"></div>
@endisset

<div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
        <thead class="text-xs uppercase bg-brand-500 text-white dark:bg-brand-600 dark:text-white">
            <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">Flat/Shop Number</th>
                <th class="px-4 py-3">Tenant</th>
                <th class="px-4 py-3">Month</th>
                <th class="px-4 py-3">Payments Details</th>
                <th class="px-4 py-3">Total Amount</th>
                <th class="px-4 py-3">Total Paid</th>
                <th class="px-4 py-3">Consolidated Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @php
                $rowIndex = 0;
            @endphp
            @forelse($groupedPayments as $key => $group)
                @php
                    $firstPayment = $group->first();
                    $rowIndex++;
                    
                    // Consolidated statistics
                    $totalAmount = $group->sum('amount');
                    $totalPaid = $group->sum('amount_paid');
                    
                    $statuses = $group->pluck('status')->unique();
                    $consolidatedStatus = 'paid';
                    if ($statuses->contains('unpaid') && !$statuses->contains('paid') && !$statuses->contains('partial')) {
                        $consolidatedStatus = 'unpaid';
                    } elseif ($statuses->contains('unpaid') || $statuses->contains('partial') || $totalPaid < $totalAmount) {
                        $consolidatedStatus = $totalPaid == 0 ? 'unpaid' : 'partial';
                    }
                    
                    $consolidatedClass = match($consolidatedStatus) {
                        'paid' => 'bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 border border-green-200 dark:border-green-800/30',
                        'partial' => 'bg-orange-50 text-orange-700 dark:bg-orange-950/20 dark:text-orange-400 border border-orange-200 dark:border-orange-800/30',
                        'unpaid' => 'bg-red-50 text-red-700 dark:bg-red-950/20 dark:text-red-400 border border-red-200 dark:border-red-800/30',
                    };
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors {{ request('search') ? 'bg-amber-500/[0.03] dark:bg-amber-500/[0.02] border-l-2 border-l-amber-500/70' : '' }}">
                    <td class="px-4 py-3 text-gray-400 align-middle">{{ $payments->firstItem() + $rowIndex - 1 }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90 align-middle">
                        <span
                            class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-sm font-bold text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                            {!! $highlight($firstPayment->unit->unit_number) !!}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90 align-middle">
                        @if($firstPayment->tenant)
                            {!! $highlight($firstPayment->tenant->name) !!}
                        @elseif($firstPayment->otherTenant)
                            <div class="flex flex-col">
                                <span>{!! $highlight($firstPayment->otherTenant->name) !!}</span>
                                <span
                                    class="inline-flex items-center gap-0.5 mt-0.5 text-[10px] font-medium text-violet-600 dark:text-violet-400">
                                    Other-Owned
                                </span>
                            </div>
                        @else
                            <span
                                class="inline-flex items-center gap-1 text-xs font-medium text-violet-600 dark:text-violet-400">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Other-Owned
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs align-middle">{{ $firstPayment->month->format('M Y') }}</td>
                    <td class="px-4 py-3 align-middle max-w-xl">
                        <div class="flex flex-col gap-2">
                            @foreach($group as $payment)
                                <div class="flex flex-wrap items-center justify-between gap-3 p-2 rounded-xl border border-gray-100 bg-gray-50/50 dark:border-gray-800/40 dark:bg-white/[0.01]">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold {{ $payment->type_badge_class }}">
                                            {{ ucfirst($payment->type) }}
                                        </span>
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                            Rs. {{ number_format($payment->amount) }}
                                        </span>
                                        <span class="text-[10px] text-gray-500">
                                            Paid: <span class="{{ $payment->amount_paid > 0 ? 'text-green-600 font-medium' : 'text-gray-400' }}">Rs. {{ number_format($payment->amount_paid) }}</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $payment->status_badge_class }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </div>
                                    
                                    {{-- Actions for this specific payment --}}
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('payments.show', $payment) }}"
                                            class="inline-flex items-center rounded-lg p-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                            title="View">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        <a href="{{ route('payments.print', $payment) }}" target="_blank"
                                            class="inline-flex items-center rounded-lg p-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                            title="{{ $payment->type === 'rent' ? 'Print Rent Bill' : (in_array($payment->type, ['maintenance', 'electricity', 'water', 'gas']) ? 'Print Maintenance Bill' : 'Print Receipt') }}">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                                            </svg>
                                        </a>

                                        @if(auth()->user()->hasPermission('payments.whatsapp') || auth()->user()->isSuperAdmin())
                                            @php
                                                $phone = $payment->whatsapp_number ?: ($payment->tenant?->whatsapp_number ?: $payment->tenant?->phone);
                                            @endphp
                                            @if($phone)
                                                @php
                                                    $recipientName = $payment->tenant?->name
                                                        ?: ($payment->otherTenant?->name
                                                            ?: ($payment->unit?->landlord?->name ?: 'Other-Owned Unit'));

                                                    $phoneClean = preg_replace('/\D/', '', $phone);
                                                    if (strpos($phoneClean, '0') === 0 && strlen($phoneClean) === 11) {
                                                        $phoneClean = '92' . substr($phoneClean, 1);
                                                    }

                                                    $typeStr = ucfirst($payment->type);
                                                    $monthStr = $payment->month ? $payment->month->format('M Y') : '';
                                                    $amountStr = number_format($payment->amount);
                                                    $paidStr = number_format($payment->amount_paid);
                                                    $dueDateStr = $payment->due_date ? $payment->due_date->format('d M Y') : '';
                                                    $statusStr = ucfirst($payment->status);
                                                    $paymentUrl = $payment->public_url;

                                                    $message = "Dear {$recipientName},\n\nThis is a notification for your {$typeStr} payment towards Unit " . ($payment->unit?->unit_number ?? '') . " for {$monthStr}.\n\nBill Details:\n- Type: {$typeStr}\n- Month: {$monthStr}\n- Total Amount: Rs. {$amountStr}\n- Amount Paid: Rs. {$paidStr}\n- Due Date: {$dueDateStr}\n- Status: {$statusStr}\n\nYou can view/print your bill copy here: {$paymentUrl}\n\nRegards,\nPalladium Mall Management";
                                                    $whatsappUrl = "https://api.whatsapp.com/send?phone=" . urlencode($phoneClean) . "&text=" . urlencode($message);
                                                @endphp
                                                <a href="{{ $whatsappUrl }}" target="_blank"
                                                    class="inline-flex items-center rounded-lg p-1 text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors"
                                                    title="Share Bill on WhatsApp">
                                                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12.012 2c-5.506 0-9.988 4.482-9.988 9.988 0 1.76.46 3.413 1.258 4.868L2 22l5.29-1.387c1.405.766 3 1.205 4.722 1.205 5.506 0 9.988-4.482 9.988-9.988C22 6.482 17.518 2 12.012 2zm6.262 14.373c-.258.73-1.468 1.413-2.025 1.48-.48.06-1.106.1-3.23-.787-2.716-1.137-4.46-3.906-4.594-4.088-.135-.183-.996-1.328-.996-2.534s.623-1.802.846-2.052c.222-.25.48-.312.642-.312.163 0 .326.01.467.01.147.01.343-.06.538.41.196.48.674 1.638.73 1.75.056.113.093.243.017.393-.075.15-.112.24-.225.37-.113.13-.238.29-.338.39-.11.1-.225.21-.096.43.128.22.57 1.004 1.22 1.58.84.75 1.55.98 1.77 1.1.22.12.35.1.48-.05.13-.15.56-.65.71-.87.15-.22.3-.18.5-.1.21.08 1.32.62 1.55.73.23.11.38.16.44.27.06.1.06.59-.19 1.32z" />
                                                    </svg>
                                                </a>
                                            @endif
                                        @endif

                                        @if(auth()->user()->hasPermission('payments.edit') || auth()->user()->isSuperAdmin())
                                            <a href="{{ route('payments.edit', $payment) }}"
                                                class="inline-flex items-center rounded-lg p-1 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                                title="Edit">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                        @endif

                                        @if(auth()->user()->hasPermission('payments.delete') || auth()->user()->isSuperAdmin())
                                            <form action="{{ route('payments.destroy', $payment) }}" method="POST" x-data @submit.prevent="if(confirm('Delete this payment record?')) $el.submit()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-lg p-1 text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Delete">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90 align-middle">Rs. {{ number_format($totalAmount) }}</td>
                    <td class="px-4 py-3 align-middle">
                        <span class="{{ $totalPaid > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                            Rs. {{ number_format($totalPaid) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $consolidatedClass }}">
                            <span
                                class="h-1.5 w-1.5 rounded-full {{ $consolidatedStatus === 'paid' ? 'bg-green-600' : ($consolidatedStatus === 'partial' ? 'bg-orange-500' : 'bg-red-600') }}"></span>
                            {{ ucfirst($consolidatedStatus) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        No payment records found.
                        <a href="{{ route('payments.create') }}" class="text-brand-500 hover:underline">Add one.</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($payments->hasPages())
    <div class="mt-4 border-t border-gray-100 p-4 dark:border-gray-800">
        {{ $payments->links() }}
    </div>
@endif