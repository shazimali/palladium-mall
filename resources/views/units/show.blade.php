@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Unit — {{ $unit->unit_number }}" />

    <x-common.component-card
        title="Unit — {{ $unit->unit_number }}"
        desc="{{ ucfirst($unit->type) }} · {{ $unit->floor->name ?? '' }} {{ $unit->block->name ?? '' }}">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['Unit Number',   $unit->unit_number, null],
                ['Type',          ucfirst($unit->type), null],
                ['Floor',         $unit->floor->name ?? '—', null],
                ['Block',         $unit->block->name ?? '—', null],
                ['Area / Zone',   $unit->area->name  ?? '—', null],
                ['Area (sq.ft.)', $unit->area_sqft ? $unit->area_sqft.' sq.ft.' : '—', null],
                ['Status',        ucfirst($unit->status), null],
                ['Landlord',      $unit->landlord->name ?? '—', $unit->landlord_id ? route('landlords.show', $unit->landlord_id) : null],
                ['Elec. Meter',   $unit->elec_meter_id  ?? '—', null],
                ['Water Meter',   $unit->water_meter_id ?? '—', null],
                ['Gas Meter',     $unit->gas_meter_id   ?? '—', null],
            ] as [$label, $value, $url])
                <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $label }}</p>
                    @if($url)
                        <p class="mt-0.5 text-sm font-medium text-brand-500 hover:underline">
                            <a href="{{ $url }}">{{ $value }}</a>
                        </p>
                    @else
                        <p class="mt-0.5 text-sm font-medium text-gray-800 dark:text-white/90">{{ $value }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        @if($unit->notes)
            <div class="rounded-lg bg-gray-50 px-4 py-3 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-400 dark:text-gray-500">Notes</p>
                <p class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $unit->notes }}</p>
            </div>
        @endif

        <div class="flex items-center gap-3 pt-2">
            @if(auth()->user()->hasPermission('units.edit') || auth()->user()->isSuperAdmin())
                <a href="{{ route('units.edit', $unit) }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    Edit Unit
                </a>
            @endif
            <a href="{{ route('units.index') }}"
                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                Back to Units
            </a>
        </div>
    </x-common.component-card>
@endsection