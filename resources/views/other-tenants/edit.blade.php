@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="" />

    <x-common.component-card title="Edit Other Flat/Shop Tenant" desc="Update tenant profile details">

        <form action="{{ route('other-tenants.update', $otherTenant) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('other-tenants._form', [
                'submitLabel' => 'Save Changes',
                'selfUnits'   => $selfUnits,
            ])
        </form>

    </x-common.component-card>
@endsection
