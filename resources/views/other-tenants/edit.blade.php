@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Other Tenant" />

    <x-common.component-card title="Edit Other Tenant" desc="Update tenant profile details">

        <form action="{{ route('other-tenants.update', $otherTenant) }}" method="POST">
            @csrf
            @method('PUT')
            @include('other-tenants._form', [
                'submitLabel' => 'Save Changes',
                'selfUnits'   => $selfUnits,
            ])
        </form>

    </x-common.component-card>
@endsection
