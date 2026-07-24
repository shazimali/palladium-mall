@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Agreement — {{ $agreement->tenant?->name ?? 'Deleted Tenant' }}" />

    {{-- ── Expiry Warning Banner ──────────────────────────────────────── --}}
    @if($agreement->isExpiringSoon())
        <div class="mb-5 flex items-center gap-3 rounded-xl border border-yellow-200 bg-yellow-50 px-5 py-4 text-base text-yellow-700 dark:border-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
            <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span>This agreement expires in <strong>{{ $agreement->daysRemaining() }} days</strong>.</span>
        </div>
    @endif

    {{-- ── KPI Summary Row ─────────────────────────────────────────────── --}}
    <div class="mb-6 grid grid-cols-2 gap-5 sm:grid-cols-4">
        {{-- Total Billed --}}
        <div class="rounded-xl border border-gray-150 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Billed</p>
            <p class="mt-2 text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">Rs. {{ number_format($totalBilled) }}</p>
            <p class="mt-1 text-sm font-medium text-gray-500 dark:text-gray-400">{{ $payments->count() }} {{ Str::plural('invoice', $payments->count()) }}</p>
        </div>
        {{-- Total Collected --}}
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-5 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-900/10">
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Collected</p>
            <p class="mt-2 text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($totalPaid) }}</p>
            <p class="mt-1 text-sm font-medium text-emerald-600/90 dark:text-emerald-400/90">{{ $payments->whereIn('status', ['paid','partial'])->count() }} paid</p>
        </div>
        {{-- Outstanding --}}
        <div class="rounded-xl border {{ $totalBalance > 0 ? 'border-red-200 bg-red-50/50 dark:border-red-900/40 dark:bg-red-900/10' : 'border-gray-150 bg-white dark:border-gray-800 dark:bg-white/[0.03]' }} p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wider {{ $totalBalance > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">Outstanding</p>
            <p class="mt-2 text-2xl sm:text-3xl font-extrabold {{ $totalBalance > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">Rs. {{ number_format($totalBalance) }}</p>
            <p class="mt-1 text-sm font-medium {{ $totalBalance > 0 ? 'text-red-600/90 dark:text-red-400/90' : 'text-gray-400' }}">
                {{ $payments->where('status','unpaid')->count() }} unpaid
            </p>
        </div>
        {{-- Monthly Rent --}}
        <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-5 shadow-sm dark:border-blue-900/40 dark:bg-blue-900/10">
            <p class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Monthly Rent</p>
            <p class="mt-2 text-2xl sm:text-3xl font-extrabold text-blue-600 dark:text-blue-400">Rs. {{ number_format($agreement->monthly_rent) }}</p>
            <p class="mt-1 text-sm font-medium text-blue-600/90 dark:text-blue-400/90">{{ $agreement->durationInMonths() }} months</p>
        </div>
    </div>

    {{-- ── Two-Column Layout ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Left — Agreement Details ──────────────────────────────────── --}}
        <div class="space-y-5">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.02] shadow-sm overflow-hidden">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Agreement Details</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Unit {{ $agreement->unit?->unit_number ?? '—' }} ·
                        {{ $agreement->start_date?->format('d M Y') ?? 'Draft' }}
                        to
                        {{ $agreement->end_date?->format('d M Y') ?? 'Draft' }}
                    </p>
                </div>

                {{-- Status pill row --}}
                <div class="px-6 py-3.5 flex items-center justify-between bg-gray-50/80 dark:bg-white/[0.01] border-b border-gray-200 dark:border-gray-800">
                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Status</span>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-bold
                        @if($agreement->status === 'active') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300
                        @elseif($agreement->status === 'expired') bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300
                        @elseif($agreement->status === 'terminated') bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300
                        @else bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 @endif">
                        {{ ucfirst($agreement->status) }}
                    </span>
                </div>

                {{-- Detail rows --}}
                <div class="divide-y divide-gray-150 dark:divide-gray-800">
                    @foreach([
                        ['Tenant',           $agreement->tenant?->name ?? 'Deleted Tenant'],
                        ['Unit',             $agreement->unit?->unit_number ?? '—'],
                        ['Start Date',       $agreement->start_date?->format('d M Y') ?? 'Draft'],
                        ['End Date',         $agreement->end_date?->format('d M Y') ?? 'Draft'],
                        ['Duration',         $agreement->durationInMonths().' months'],
                        ['Days Remaining',   $agreement->isActive() ? $agreement->daysRemaining().' days' : '—'],
                        ['Monthly Rent',     $agreement->monthly_rent ? 'Rs. '.number_format($agreement->monthly_rent) : '—'],
                        ['Security Deposit', $agreement->security_deposit ? 'Rs. '.number_format($agreement->security_deposit) : '—'],
                        ['Grace Period',     $agreement->grace_period_days.' days'],
                        ['Fine Per Day',     $agreement->fine_per_day ? 'Rs. '.number_format($agreement->fine_per_day) : '—'],
                    ] as [$label, $value])
                        <div class="flex items-center justify-between px-6 py-3">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</span>
                            <span class="text-base font-bold text-gray-900 dark:text-white text-right">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Terms --}}
                @if($agreement->terms)
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Terms</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $agreement->terms }}</p>
                    </div>
                @endif

                {{-- Document --}}
                @if($agreement->document)
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 flex items-center gap-2.5">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <a href="{{ $agreement->document_url }}" target="_blank" class="text-sm text-brand-600 dark:text-brand-400 hover:underline font-bold">
                            View Signed Document
                        </a>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex items-center gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-800">
                    @if(auth()->user()->hasPermission('agreements.edit') || auth()->user()->isSuperAdmin())
                        <a href="{{ route('agreements.edit', $agreement) }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-brand-600 transition-colors shadow-xs">
                            Edit Agreement
                        </a>
                    @endif
                    <a href="{{ route('agreements.index') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                        Back
                    </a>
                </div>
            </div>
        </div>

        {{-- Right — Payments ──────────────────────────────────────────── --}}
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.02] shadow-sm overflow-hidden">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Payment History</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">All billings linked to this agreement</p>
                    </div>
                    @if($payments->isNotEmpty())
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-bold text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                            {{ $payments->count() }} records
                        </span>
                    @endif
                </div>

                @if($payments->isEmpty())
                    <div class="py-20 text-center">
                        <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2" stroke="currentColor" stroke-width="1.5" fill="none"/>
                                <line x1="1" y1="10" x2="23" y2="10" stroke="currentColor" stroke-width="1.5"/>
                            </svg>
                        </div>
                        <p class="text-base font-bold text-gray-500 dark:text-gray-400">No payments found</p>
                        <p class="text-sm text-gray-400 mt-1">Payments will appear here once invoices are generated.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-base text-left">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-white/[0.02] text-xs uppercase tracking-wider font-bold text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                                    <th class="px-5 py-3.5">Month</th>
                                    <th class="px-5 py-3.5">Type</th>
                                    <th class="px-5 py-3.5">Due Date</th>
                                    <th class="px-5 py-3.5 text-right">Billed</th>
                                    <th class="px-5 py-3.5 text-right">Paid</th>
                                    <th class="px-5 py-3.5 text-right">Balance</th>
                                    <th class="px-5 py-3.5">Status</th>
                                    <th class="px-5 py-3.5">Paid At</th>
                                    <th class="px-5 py-3.5 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @foreach($payments as $payment)
                                    @php $balance = $payment->balanceDue(); @endphp
                                    <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02] transition-colors
                                        @if($payment->isOverdue()) bg-red-50/40 dark:bg-red-900/10 @endif">

                                        {{-- Month --}}
                                        <td class="px-5 py-4 font-bold text-gray-900 dark:text-white whitespace-nowrap text-base">
                                            {{ $payment->month->format('M Y') }}
                                        </td>

                                        {{-- Type --}}
                                        <td class="px-5 py-4">
                                            <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-bold {{ $payment->type_badge_class }}">
                                                {{ $payment->type_label }}
                                            </span>
                                        </td>

                                        {{-- Due Date --}}
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="text-sm font-semibold {{ $payment->isOverdue() ? 'text-red-600 dark:text-red-400 font-bold' : 'text-gray-600 dark:text-gray-300' }}">
                                                {{ $payment->due_date->format('d M Y') }}
                                                @if($payment->isOverdue())
                                                    <span class="ml-1.5 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-extrabold text-red-700 dark:bg-red-900/40 dark:text-red-300">OVERDUE</span>
                                                @endif
                                            </span>
                                        </td>

                                        {{-- Billed --}}
                                        <td class="px-5 py-4 text-right font-extrabold text-gray-900 dark:text-white whitespace-nowrap text-base">
                                            Rs. {{ number_format($payment->amount) }}
                                        </td>

                                        {{-- Paid --}}
                                        <td class="px-5 py-4 text-right whitespace-nowrap text-base">
                                            <span class="font-extrabold {{ $payment->amount_paid > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                                                Rs. {{ number_format($payment->amount_paid) }}
                                            </span>
                                        </td>

                                        {{-- Balance --}}
                                        <td class="px-5 py-4 text-right whitespace-nowrap text-base">
                                            @if($balance > 0)
                                                <span class="font-extrabold text-red-600 dark:text-red-400">Rs. {{ number_format($balance) }}</span>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">—</span>
                                            @endif
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-5 py-4">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold {{ $payment->status_badge_class }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>

                                        {{-- Paid At --}}
                                        <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                            @if($payment->paid_at)
                                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $payment->paid_at->format('d M Y') }}</span>
                                                @if($payment->paymentAccount)
                                                    <br><span class="text-xs text-gray-400 font-medium">via {{ $payment->paymentAccount->name }}</span>
                                                @endif
                                            @else
                                                —
                                            @endif
                                        </td>

                                        {{-- Action --}}
                                        <td class="px-5 py-4 text-right">
                                            <a href="{{ route('payments.show', $payment->id) }}"
                                               class="inline-flex items-center gap-1.5 text-sm font-bold text-brand-600 hover:text-brand-800 dark:text-brand-400 dark:hover:text-brand-300 transition-colors">
                                                View
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="9 18 15 12 9 6"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            {{-- Totals footer --}}
                            <tfoot class="border-t-2 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-white/[0.02]">
                                <tr class="font-extrabold text-gray-900 dark:text-white">
                                    <td class="px-5 py-4 text-sm uppercase tracking-wider text-gray-500 dark:text-gray-400" colspan="3">Totals</td>
                                    <td class="px-5 py-4 text-right text-base">Rs. {{ number_format($totalBilled) }}</td>
                                    <td class="px-5 py-4 text-right text-base text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($totalPaid) }}</td>
                                    <td class="px-5 py-4 text-right text-base {{ $totalBalance > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">
                                        {{ $totalBalance > 0 ? 'Rs. '.number_format($totalBalance) : '—' }}
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection