@extends('layouts.app')
 
@section('content')
    <x-common.page-breadcrumb pageTitle="Add New Payment Account" />
 
    <div class="mx-auto max-w-3xl">
        <x-common.component-card title="Payment Account Details" desc="Provide information to register a new collection account">
            <form action="{{ route('payment-accounts.store') }}" method="POST">
                @csrf
 
                @include('payment_accounts._form')
 
                {{-- Form Actions --}}
                <div class="mt-6 flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('payment-accounts.index') }}"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        Create Account
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
