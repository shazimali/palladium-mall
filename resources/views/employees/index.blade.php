@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Employees" />

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <x-common.component-card title="Registered Employees" desc="All users registered as employees with performance tracking">

        {{-- Top Bar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-5">
            <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                Total: {{ $employees->total() }}
            </span>

            <div class="flex items-center gap-2">
                @if($search)
                    <a href="{{ route('employees.index') }}"
                       class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                        Clear
                    </a>
                @endif
                @if(auth()->user()->hasPermission('employees.manage') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('employees.create') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Register Employee
                    </a>
                @endif
            </div>
        </div>

        {{-- Search --}}
        <div class="mb-5 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('employees.index') }}" method="GET" class="flex gap-3">
                <div class="relative flex-1 max-w-sm">
                    <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search employees..."
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"/>
                </div>
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">Search</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Employee</th>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Designation</th>
                        <th class="px-4 py-3">Basic Salary</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($employees as $i => $employee)
                        @php $p = $employee->employeeProfile; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 text-gray-400">{{ $employees->firstItem() + $i }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-900/30 dark:text-brand-400 text-sm font-bold">
                                        {{ strtoupper(substr($employee->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $employee->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $employee->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $p->employee_code ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($p->designation)
                                    <span class="text-gray-700 dark:text-gray-300">{{ $p->designation }}</span>
                                    @if($p->department)
                                        <span class="block text-xs text-gray-400">{{ $p->department }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                Rs. {{ number_format($p->basic_salary, 0) }}
                            </td>
                            <td class="px-4 py-3">
                                @if($p->is_active)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-600 dark:bg-red-900/30 dark:text-red-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('employees.show', $employee) }}"
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800 transition-colors">
                                        View
                                    </a>
                                    @if(auth()->user()->hasPermission('employees.manage') || auth()->user()->isSuperAdmin())
                                        <a href="{{ route('employees.edit', $employee) }}"
                                           class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800 transition-colors">
                                            Edit
                                        </a>
                                    @endif
                                    @if(auth()->user()->isSuperAdmin())
                                        <a href="{{ route('employees.tasks', $employee) }}"
                                           class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800 transition-colors">
                                            Tasks
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-400">
                                No employees registered yet.
                                @if(auth()->user()->hasPermission('employees.manage') || auth()->user()->isSuperAdmin())
                                    <a href="{{ route('employees.create') }}" class="text-brand-500 hover:underline ml-1">Register one now</a>.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($employees->hasPages())
            <div class="mt-4">{{ $employees->links() }}</div>
        @endif

    </x-common.component-card>
@endsection
