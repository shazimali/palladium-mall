@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Landlord Detail — {{ $landlord->name }}" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left: Landlord info profile card --}}
        <div class="space-y-6 lg:col-span-1">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex flex-col items-center text-center">
                    @if($landlord->photo)
                        <div class="mb-4">
                            <img src="{{ $landlord->photo_url }}" alt="{{ $landlord->name }}" class="h-24 w-24 rounded-full object-cover border-2 border-brand-500 shadow-sm">
                        </div>
                    @else
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-xl font-bold text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                            {{ strtoupper(substr($landlord->name, 0, 2)) }}
                        </div>
                    @endif
                    <h3 class="mt-4 text-lg font-bold text-gray-950 dark:text-white">{{ $landlord->name }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Landlord</p>
                </div>

                <div class="mt-6 space-y-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">Phone</span>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $landlord->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">Email</span>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $landlord->email ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">CNIC</span>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $landlord->cnic ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">Mailing Address</span>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90 leading-relaxed">{{ $landlord->address ?? '—' }}</p>
                    </div>
                </div>

                @if($landlord->notes)
                    <div class="mt-6 border-t border-gray-100 pt-6 dark:border-gray-800">
                        <span class="text-xs text-gray-400 dark:text-gray-500">Notes / Remarks</span>
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 leading-relaxed">{{ $landlord->notes }}</p>
                    </div>
                @endif

                <div class="mt-6 flex items-center gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                    @if(auth()->user()->hasPermission('landlords.edit') || auth()->user()->isSuperAdmin())
                        <a href="{{ route('landlords.edit', $landlord) }}"
                            class="flex-1 text-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                            Edit Profile
                        </a>
                    @endif
                    <a href="{{ route('landlords.index') }}"
                        class="flex-1 text-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-colors">
                        Back to List
                    </a>
                </div>
            </div>
        </div>

        {{-- Right: Associated units and history --}}
        <div class="lg:col-span-2 space-y-6" x-data="{ activeTab: 'current' }">
            {{-- Tabs --}}
            <div class="flex border-b border-gray-200 dark:border-gray-800">
                <button @click="activeTab = 'current'"
                    :class="activeTab === 'current' ? 'border-brand-500 text-brand-600 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="border-b-2 px-6 py-3 text-sm font-medium transition-colors focus:outline-none">
                    Current Properties
                </button>
                <button @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'border-brand-500 text-brand-600 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                    class="border-b-2 px-6 py-3 text-sm font-medium transition-colors focus:outline-none">
                    Ownership History (All / Transferred)
                </button>
            </div>

            {{-- Tab 1: Current Properties --}}
            <div x-show="activeTab === 'current'" class="space-y-4">
                <x-common.component-card title="Owned Properties" desc="Flats and shops currently owned by {{ $landlord->name }}">
                    <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                        <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                            <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Flat No.</th>
                                    <th class="px-4 py-3">Type/Status</th>
                                    <th class="px-4 py-3">Location</th>
                                    <th class="px-4 py-3">File / Nominee</th>
                                    <th class="px-4 py-3">Financials</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($landlord->units as $unit)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90">
                                            {{ $unit->unit_number }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="block text-xs font-semibold capitalize text-gray-700 dark:text-gray-300">{{ $unit->type }}</span>
                                            <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-medium mt-1
                                                {{ $unit->status === 'rented'
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                    : ($unit->status === 'vacant'
                                                        ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'
                                                        : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400') }}">
                                                {{ ucfirst($unit->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                <p class="font-medium text-gray-700 dark:text-gray-300">{{ $unit->floor->name ?? '—' }}</p>
                                                <p class="text-[10px]">{{ $unit->block->name ?? '—' }} · {{ $unit->area->name ?? '—' }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php $o = $unit->currentOwnership; @endphp
                                            @if($o)
                                                <div class="text-xs">
                                                    <p class="font-medium text-gray-800 dark:text-white">File: <span class="font-bold text-brand-600 dark:text-brand-400">{{ $unit->file_no ?? '—' }}</span></p>
                                                    @if($o->nominee_name)
                                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5" title="Relation: {{ $o->relation_label }} {{ $o->nominee_relation_name }}">
                                                            Nominee: {{ $o->nominee_name }} ({{ $o->relation_label }})
                                                        </p>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">No active ownership</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($o)
                                                <div class="text-xs">
                                                    <p class="text-gray-700 dark:text-gray-300">Total: <span class="font-semibold text-gray-900 dark:text-white">Rs. {{ number_format((float)$o->total_amount) }}</span></p>
                                                    <p class="text-[10px] text-gray-500">Recv: Rs. {{ number_format((float)$o->received_amount) }}</p>
                                                    @if((float)$o->credit_amount > 0)
                                                        <p class="text-[10px] font-semibold text-red-600 dark:text-red-400 mt-0.5">Credit: Rs. {{ number_format((float)$o->credit_amount) }}</p>
                                                    @else
                                                        <p class="text-[10px] text-green-600 dark:text-green-400 mt-0.5 font-medium">Fully Paid</p>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('units.show', $unit) }}"
                                                class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white transition-colors"
                                                title="View Unit Details">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                            No properties currently assigned to this landlord.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-common.component-card>
            </div>

            {{-- Tab 2: Ownership History --}}
            <div x-show="activeTab === 'history'" class="space-y-4" style="display: none;">
                <x-common.component-card title="Ownership Records" desc="Complete history of all units owned by {{ $landlord->name }} (current and historical)">
                    <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                        <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                            <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Flat No.</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Ownership Period</th>
                                    <th class="px-4 py-3">File / Nominee</th>
                                    <th class="px-4 py-3">Financials</th>
                                    <th class="px-4 py-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($landlord->ownerships as $ownership)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90">
                                            {{ $ownership->unit->unit_number ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-medium
                                                {{ $ownership->is_current
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                    : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                                                {{ $ownership->is_current ? 'Active Owner' : 'Transferred' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-xs text-gray-700 dark:text-gray-300">
                                                <p><span class="font-medium text-gray-400">From:</span> {{ $ownership->start_date ? $ownership->start_date->format('d M Y') : '—' }}</p>
                                                <p class="mt-0.5"><span class="font-medium text-gray-400">To:</span> {{ $ownership->end_date ? $ownership->end_date->format('d M Y') : ($ownership->is_current ? 'Present' : '—') }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-xs">
                                                <p class="font-medium text-gray-800 dark:text-white">File: <span class="font-bold text-brand-600 dark:text-brand-400">{{ $ownership->unit->file_no ?? '—' }}</span></p>
                                                @if($ownership->nominee_name)
                                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5" title="Relation: {{ $ownership->relation_label }} {{ $ownership->nominee_relation_name }}">
                                                        Nominee: {{ $ownership->nominee_name }} ({{ $ownership->relation_label }})
                                                    </p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-xs">
                                                <p class="text-gray-700 dark:text-gray-300">Total: <span class="font-semibold text-gray-900 dark:text-white">Rs. {{ number_format((float)$ownership->total_amount) }}</span></p>
                                                <p class="text-[10px] text-gray-500">Recv: Rs. {{ number_format((float)$ownership->received_amount) }}</p>
                                                @if((float)$ownership->credit_amount > 0)
                                                    <p class="text-[10px] font-semibold text-red-600 dark:text-red-400 mt-0.5">Credit: Rs. {{ number_format((float)$ownership->credit_amount) }}</p>
                                                @else
                                                    <p class="text-[10px] text-green-600 dark:text-green-400 mt-0.5 font-medium">Fully Paid</p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if($ownership->unit)
                                                <a href="{{ route('units.show', $ownership->unit) }}"
                                                    class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white transition-colors"
                                                    title="View Unit Details">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                            No ownership records found for this landlord.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-common.component-card>
            </div>
        </div>
    </div>
@endsection
