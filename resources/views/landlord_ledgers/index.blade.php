@extends('layouts.app')

@section('title', 'Landlord Ledgers')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Landlord Ledgers</h1>
        </div>
    </div>

    {{-- Search Filter Card --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <form action="{{ route('landlord_ledgers.index') }}" method="GET" class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <div class="relative flex-1 max-w-md">
                <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                    🔍
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search landlord by name, phone, CNIC..."
                    class="h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('landlord_ledgers.index') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl mb-8">
        <div class="overflow-x-auto">
            <table class="table-auto w-full dark:text-gray-300">
                <thead class="text-xs uppercase text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700/50 rounded-sm">
                    <tr>
                        <th class="p-4 whitespace-nowrap"><div class="font-semibold text-left">Landlord</div></th>
                        <th class="p-4 whitespace-nowrap"><div class="font-semibold text-left">Phone</div></th>
                        <th class="p-4 whitespace-nowrap"><div class="font-semibold text-left">Units Owned</div></th>
                        <th class="p-4 whitespace-nowrap"><div class="font-semibold text-center">Action</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach($landlords as $landlord)
                    <tr>
                        <td class="p-4 whitespace-nowrap">
                            <div class="font-medium text-gray-800 dark:text-gray-100">{{ $landlord->name }}</div>
                        </td>
                        <td class="p-4 whitespace-nowrap">{{ $landlord->phone ?? '-' }}</td>
                        <td class="p-4 whitespace-nowrap">{{ $landlord->ownerships->count() }}</td>
                        <td class="p-4 whitespace-nowrap text-center">
                            <a href="{{ route('landlord_ledgers.show', $landlord) }}" class="btn-sm bg-brand-500 hover:bg-brand-600 text-white">View Ledger</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    {{ $landlords->links() }}
</div>
@endsection
