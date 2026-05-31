@props(['payments' => collect()])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 transition-all hover:shadow-md">

    {{-- Header with red accent bar --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800"
         style="border-left: 4px solid #f04438;">
        <div>
            <h3 class="text-base font-bold text-gray-800 dark:text-white/90">Overdue Payments</h3>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Past due date</p>
        </div>
        <a href="{{ route('payments.index', ['status' => 'unpaid']) }}"
            class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold transition-all"
            style="color: #f04438; background: #fef3f2;"
            onmouseover="this.style.background='#fee4e2'" onmouseout="this.style.background='#fef3f2'">
            View All →
        </a>
    </div>

    <div class="divide-y divide-gray-50 dark:divide-gray-800 px-4 py-2">
        @forelse($payments as $payment)
            <div class="flex items-center justify-between py-3 transition-all">
                <div class="flex items-center gap-3">
                    {{-- Red icon badge --}}
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                         style="background: #fef3f2;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f04438" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800 dark:text-white/90 leading-tight">
                            {{ $payment->tenant->name }}
                        </p>
                        <span class="text-xs text-gray-400 dark:text-gray-500 font-medium">
                            {{ $payment->unit->unit_number }} · {{ ucfirst($payment->type) }}
                        </span>
                    </div>
                </div>
                <div class="text-right shrink-0 ml-2">
                    <p class="text-sm font-extrabold" style="color: #f04438;">
                        Rs. {{ number_format($payment->balanceDue()) }}
                    </p>
                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        Due {{ $payment->due_date->format('d M') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="py-8 text-center">
                <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full" style="background: #ecfdf3;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#12b76a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-400 dark:text-gray-600">All clear! No overdue payments.</p>
            </div>
        @endforelse
    </div>
</div>