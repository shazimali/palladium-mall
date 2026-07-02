@forelse($monthlySummaries as $monthStr => $summary)
    <div class="mb-8 p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs transition-all duration-300 hover:shadow-md">
        <h3 class="text-base font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
            <span>📅</span> {{ $summary['display_month'] }}
        </h3>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $historyWidgets = [
                    'grand_total' => [
                        'label' => 'Grand Total Summary',
                        'gradient' => 'linear-gradient(135deg, #465fff 0%, #2a31d8 100%)',
                        'icon' => '📊',
                    ],
                    'rent' => [
                        'label' => 'Rent Summary',
                        'gradient' => 'linear-gradient(135deg, #f04438 0%, #912018 100%)',
                        'icon' => '🔑',
                    ],
                    'services' => [
                        'label' => 'Services Summary',
                        'gradient' => 'linear-gradient(135deg, #7a5af8 0%, #2a31d8 100%)',
                        'icon' => '🛠️',
                    ],
                    'security_deposit' => [
                        'label' => 'Security Deposit',
                        'gradient' => 'linear-gradient(135deg, #a855f7 0%, #701a75 100%)',
                        'icon' => '🛡️',
                    ],
                ];
            @endphp

            @foreach($historyWidgets as $wKey => $cfg)
                @php
                    $data = $summary['widgets'][$wKey] ?? ['due' => 0, 'paid' => 0, 'unpaid' => 0];
                @endphp
                <div class="group relative overflow-hidden rounded-2xl p-4 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                    style="background: {{ $cfg['gradient'] }}; min-height: 140px;">
                    <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                    <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                    <div class="relative flex justify-between items-center mb-2">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-white/75">{{ $cfg['label'] }}</p>
                        <span class="text-sm">{{ $cfg['icon'] }}</span>
                    </div>
                    <div class="relative mt-2 space-y-1">
                        <div class="flex justify-between items-baseline">
                            <span class="text-[10px] uppercase text-white/70">Expected Total</span>
                            <span class="font-bold text-white text-sm sm:text-base">
                                Rs. {{ number_format($data['due']) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <span class="text-[10px] uppercase text-white/70">Received</span>
                            <span class="font-bold text-emerald-300 text-sm sm:text-base">
                                Rs. {{ number_format($data['paid']) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                            <span class="text-[10px] uppercase text-white/70">Pending</span>
                            <span class="font-bold text-rose-300 text-sm sm:text-base">
                                Rs. {{ number_format($data['unpaid']) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="p-12 text-center bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
        <svg class="mx-auto mb-4 h-12 w-12 text-gray-400 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
        <p class="text-sm text-gray-500">No billing history found for the selected filters.</p>
    </div>
@endforelse
