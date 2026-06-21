@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Add Other Tenant" />

    <x-common.component-card title="Add Other Tenant" desc="Create a new tenant profile for an other-owned unit">
        <form action="{{ route('other-tenants.store') }}" method="POST">
            @csrf
            @include('other-tenants._form', ['submitLabel' => 'Add Other Tenant', 'selfUnits' => $selfUnits])
        </form>
    </x-common.component-card>
@endsection
