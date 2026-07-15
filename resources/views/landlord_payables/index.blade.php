@extends('layouts.app')

@section('title', 'Landlord Payables')

@section('content')
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Landlord Payables</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <button
                    class="inline-flex items-center gap-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white px-4 py-2.5 text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md"
                    x-data @click="$dispatch('open-add-payable-modal')">
                    <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 16 16">
                        <path
                            d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span>Add Landlord Payable</span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
                <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div
                class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                <div class="flex items-center gap-3 mb-2 font-bold">
                    <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    Please correct the errors below:
                </div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filters Card --}}
        <div
            class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('landlord-payables.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-5">
                    {{-- Search --}}
                    <div class="relative">
                        <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search title, unit, notes..."
                            class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>

                    {{-- Landlord --}}
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Landlord</label>
                        <select name="landlord_id"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Landlords</option>
                            @foreach($landlords as $l)
                                <option value="{{ $l->id }}" {{ request('landlord_id') == $l->id ? 'selected' : '' }}>
                                    {{ $l->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Status</label>
                        <select name="status"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Statuses</option>
                            <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>

                    {{-- Date From --}}
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Due From</label>
                        <input type="text" id="date_from" name="date_from" value="{{ request('date_from') }}"
                            placeholder="Select Date" autocomplete="off"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>

                    {{-- Date To --}}
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Due To</label>
                        <input type="text" id="date_to" name="date_to" value="{{ request('date_to') }}"
                            placeholder="Select Date" autocomplete="off"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-gray-150 dark:border-gray-800">
                    @if(request()->anyFilled(['search', 'landlord_id', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('landlord-payables.index') }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                            Clear Filters
                        </a>
                    @endif
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl mb-8">
            <div class="overflow-x-auto">
                <table class="table-auto w-full dark:text-gray-300">
                    <thead
                        class="text-xs uppercase text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700/50 rounded-sm">
                        <tr>
                            <th class="p-4 whitespace-nowrap">
                                <div class="font-semibold text-left">Landlord</div>
                            </th>
                            <th class="p-4 whitespace-nowrap">
                                <div class="font-semibold text-left">Unit</div>
                            </th>
                            <th class="p-4 whitespace-nowrap">
                                <div class="font-semibold text-left">Title</div>
                            </th>
                            <th class="p-4 whitespace-nowrap">
                                <div class="font-semibold text-right">Amount</div>
                            </th>
                            <th class="p-4 whitespace-nowrap">
                                <div class="font-semibold text-left">Due Date</div>
                            </th>
                            <th class="p-4 whitespace-nowrap">
                                <div class="font-semibold text-center">Status</div>
                            </th>
                            <th class="p-4 whitespace-nowrap">
                                <div class="font-semibold text-center">Actions</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($payables as $payable)
                            <tr>
                                <td class="p-4 whitespace-nowrap">{{ $payable->landlord->name }}</td>
                                <td class="p-4 whitespace-nowrap">{{ $payable->unit ? $payable->unit->unit_number : '-' }}</td>
                                <td class="p-4 whitespace-nowrap">{{ $payable->title }}</td>
                                <td class="p-4 whitespace-nowrap text-right font-medium text-gray-900 dark:text-gray-100">Rs.
                                    {{ number_format($payable->amount) }}</td>
                                <td class="p-4 whitespace-nowrap">
                                    {{ $payable->due_date ? $payable->due_date->format('M d, Y') : '-' }}</td>
                                <td class="p-4 whitespace-nowrap text-center">
                                    @if($payable->status === 'paid')
                                        <span
                                            class="bg-emerald-100 text-emerald-600 px-2.5 py-0.5 rounded-full text-xs font-medium">Paid</span>
                                    @elseif($payable->status === 'partial')
                                        <span
                                            class="bg-amber-100 text-amber-600 px-2.5 py-0.5 rounded-full text-xs font-medium">Partial</span>
                                    @else
                                        <span
                                            class="bg-rose-100 text-rose-600 px-2.5 py-0.5 rounded-full text-xs font-medium">Unpaid</span>
                                    @endif
                                </td>
                                <td class="p-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="$dispatch('open-edit-payable-modal', {
                                            id: '{{ $payable->id }}',
                                            title: '{{ addslashes($payable->title) }}',
                                            amount: '{{ $payable->amount }}',
                                            due_date: '{{ $payable->due_date ? $payable->due_date->toDateString() : '' }}',
                                            notes: '{{ addslashes($payable->notes) }}',
                                            landlord_name: '{{ addslashes($payable->landlord->name) }}',
                                            landlord_balance: '{{ $payable->landlord->remaining_opening_balance }}'
                                        })"
                                            class="text-brand-500 hover:text-brand-600 font-semibold text-xs border border-brand-200 dark:border-brand-800 rounded-lg px-2.5 py-1.5 transition-colors bg-brand-50 hover:bg-brand-100 dark:bg-brand-950/20 dark:hover:bg-brand-950/40">
                                            Edit
                                        </button>
                                        <form action="{{ route('landlord-payables.destroy', $payable) }}" method="POST"
                                            class="inline"
                                            onsubmit="return confirm('Are you sure you want to delete this payable?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-rose-500 hover:text-rose-600 font-semibold text-xs border border-rose-200 dark:border-rose-900 rounded-lg px-2.5 py-1.5 transition-colors bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/10 dark:hover:bg-rose-950/20">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-4 text-center text-gray-500">No payables found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $payables->links() }}

        <x-ui.modal x-data="{ open: false, selectedBalance: null }"
            @open-add-payable-modal.window="open = true; selectedBalance = null" :isOpen="false" class="max-w-[500px] p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Add Landlord Payable</h3>
            <form action="{{ route('landlord-payables.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Landlord</label>
                        <select name="landlord_id"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            required
                            @change="selectedBalance = $event.target.selectedOptions[0].getAttribute('data-balance')">
                            <option value="">Select Landlord...</option>
                            @foreach($landlords as $l)
                                <option value="{{ $l->id }}" data-balance="{{ $l->remaining_opening_balance }}">{{ $l->name }}
                                </option>
                            @endforeach
                        </select>
                        <template x-if="selectedBalance !== null">
                            <div
                                class="mt-2 text-xs font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-850/30 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700/60 flex justify-between items-center">
                                <span>Remaining Opening Balance:</span>
                                <span class="font-bold text-sm text-brand-600 dark:text-brand-400"
                                    x-text="selectedBalance ? 'Rs. ' + Number(selectedBalance).toLocaleString() : 'Rs. 0'"></span>
                            </div>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title (e.g.
                            Installment 1)</label>
                        <input type="text" name="title"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                        <input type="number" name="amount"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                        <input type="text" id="modal_due_date" name="due_date" placeholder="Select Date" autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="open = false"
                            class="btn bg-white border-gray-200 hover:border-gray-300 text-gray-700">Cancel</button>
                        <button type="submit" class="btn bg-brand-500 hover:bg-brand-600 text-white">Save Payable</button>
                    </div>
                </div>
            </form>
        </x-ui.modal>

        {{-- Edit Modal --}}
        <x-ui.modal
            x-data="{ open: false, id: '', title: '', amount: '', due_date: '', notes: '', landlord_name: '', landlord_balance: 0 }"
            @open-edit-payable-modal.window="
                open = true;
                id = $event.detail.id;
                title = $event.detail.title;
                amount = $event.detail.amount;
                due_date = $event.detail.due_date;
                notes = $event.detail.notes;
                landlord_name = $event.detail.landlord_name;
                landlord_balance = parseFloat($event.detail.landlord_balance) + parseFloat($event.detail.amount);
                $nextTick(() => {
                    flatpickr('#edit_modal_due_date', {
                        dateFormat: 'Y-m-d',
                        altInput: true,
                        altFormat: 'd M Y',
                        allowInput: true,
                        disableMobile: true,
                        defaultDate: due_date
                    });
                });
            " :isOpen="false" class="max-w-[500px] p-6">

            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Edit Landlord Payable</h3>

            <form :action="`/landlord-payables/${id}`" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Landlord</label>
                        <input type="text" :value="landlord_name"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm text-gray-505 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                            readonly>
                        <div
                            class="mt-2 text-xs font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-850/30 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <span>Remaining Opening Balance:</span>
                            <span class="font-bold text-sm text-brand-600 dark:text-brand-400"
                                x-text="landlord_balance ? 'Rs. ' + Number(landlord_balance).toLocaleString() : 'Rs. 0'"></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title (e.g.
                            Installment 1)</label>
                        <input type="text" name="title" x-model="title"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                        <input type="number" name="amount" x-model="amount"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Due Date</label>
                        <input type="text" id="edit_modal_due_date" name="due_date" placeholder="Select Date"
                            autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea name="notes" x-model="notes" rows="2"
                            placeholder="Write any specific details or remarks..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="open = false"
                            class="btn bg-white border-gray-200 hover:border-gray-300 text-gray-700">Cancel</button>
                        <button type="submit" class="btn bg-brand-500 hover:bg-brand-600 text-white">Save Changes</button>
                    </div>
                </div>
            </form>
        </x-ui.modal>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#date_from', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true
                });
                flatpickr('#date_to', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true
                });
                flatpickr('#modal_due_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true
                });
            }
        });
    </script>
@endpush