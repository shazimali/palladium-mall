@extends('layouts.app')

@section('content')
  <div class="space-y-6">

    {{-- ── Row 1: Stat Cards ──────────────────────────────────────── --}}
    <x-pmms.stat-cards 
      :totalUnits="$totalUnits" 
      :occupiedUnits="$occupiedUnits" 
      :vacantUnits="$vacantUnits"
      :rentDue="$rentDue" 
      :utilitiesDue="$utilitiesDue" 
      :occupancyRate="$occupancyRate" 
    />

    {{-- ── Row 2: 2-Column Dashboard Layout ──────────────────────── --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
      
      {{-- Left Side: Main Data & Charts (Col Span 8) --}}
      <div class="space-y-6 lg:col-span-8">
        {{-- Rent Collection Chart --}}
        <x-pmms.rent-chart 
          :chartMonths="$chartMonths" 
          :chartDue="$chartDue" 
          :chartPaid="$chartPaid" 
        />

        {{-- Recent Payments --}}
        <x-pmms.recent-payments 
          :payments="$recentPayments" 
        />
      </div>

      {{-- Right Side: Quick Widgets & Status (Col Span 4) --}}
      <div class="space-y-6 lg:col-span-4">
        {{-- Occupancy breakdown donut --}}
        <x-pmms.occupancy-chart 
          :occupiedUnits="$occupiedUnits" 
          :vacantUnits="$vacantUnits" 
          :soldUnits="$soldUnits"
          :occupancyRate="$occupancyRate" 
        />

        {{-- Overdue Payments --}}
        <x-pmms.overdue-payments 
          :payments="$overduePayments" 
        />

        {{-- Expiring Agreements --}}
        <x-pmms.expiring-agreements 
          :agreements="$expiringAgreements" 
        />
      </div>

    </div>

  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {

      const isDark = document.documentElement.classList.contains('dark');
      const textColor = isDark ? '#94A3B8' : '#64748B';
      const gridColor = isDark ? '#1E293B' : '#F1F5F9';

      // ── Rent Collection Bar Chart ──────────────────────────────────────
      const rentChartEl = document.querySelector('#pmmsRentChart');
      if (rentChartEl) {
        new ApexCharts(rentChartEl, {
          chart: {
            type: 'bar',
            height: 280,
            toolbar: { show: false },
            background: 'transparent',
          },
          series: [
            { name: 'Rent Due', data: @json($chartDue) },
            { name: 'Collected', data: @json($chartPaid) },
          ],
          colors: ['#E2E8F0', '#1A56DB'],
          plotOptions: {
            bar: {
              borderRadius: 4,
              columnWidth: '55%',
            },
          },
          dataLabels: { enabled: false },
          xaxis: {
            categories: @json($chartMonths),
            labels: { style: { colors: textColor, fontSize: '12px' } },
            axisBorder: { show: false },
            axisTicks: { show: false },
          },
          yaxis: {
            labels: {
              style: { colors: textColor, fontSize: '12px' },
              formatter: (val) => 'Rs. ' + (val / 1000).toFixed(0) + 'K',
            },
          },
          grid: { borderColor: gridColor, strokeDashArray: 4 },
          legend: {
            position: 'top',
            horizontalAlign: 'right',
            labels: { colors: textColor },
          },
          tooltip: {
            y: { formatter: (val) => 'Rs. ' + val.toLocaleString('en-PK') },
          },
        }).render();
      }

      // ── Occupancy Donut Chart ──────────────────────────────────────────
      const donutEl = document.querySelector('#pmmsOccupancyChart');
      if (donutEl) {
        new ApexCharts(donutEl, {
          chart: {
            type: 'donut',
            height: 200,
            background: 'transparent',
            toolbar: { show: false },
          },
          series: [
            {{ $occupiedUnits }},
            {{ $vacantUnits }},
            {{ $soldUnits }},
          ],
          labels: ['Occupied', 'Vacant', 'Sold'],
          colors: ['#059669', '#F59E0B', '#94A3B8'],
          plotOptions: {
            pie: {
              donut: {
                size: '72%',
                labels: { show: false },
              },
            },
          },
          dataLabels: { enabled: false },
          legend: { show: false },
          tooltip: {
            y: { formatter: (val) => val + ' units' },
          },
        }).render();
      }

    });
  </script>
@endpush