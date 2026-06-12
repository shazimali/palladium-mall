@extends('layouts.app')

@section('content')
  <div class="space-y-6">

    {{-- ── Page Header ──────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-xl font-extrabold text-gray-800 dark:text-white/90">Dashboard</h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
          Welcome back! Here's what's happening at Palladium Mall.
        </p>
      </div>
      <span class="text-xs font-medium text-gray-400 dark:text-gray-500">
        {{ now()->format('l, d M Y') }}
      </span>
    </div>

    {{-- ── Row 1: Stat Cards ──────────────────────────────────────────── --}}
    <x-pmms.stat-cards
      :totalUnits="$totalUnits"
      :rentedUnits="$rentedUnits"
      :vacantUnits="$vacantUnits"
      :rentDue="$rentDue"
      :utilitiesDue="$utilitiesDue"
      :occupancyRate="$occupancyRate"
    />

    {{-- ── Widgets: All Full Width ──────────────────────────────────── --}}
    <div class="space-y-6">

      <x-pmms.rent-chart
        :chartMonths="$chartMonths"
        :chartDue="$chartDue"
        :chartPaid="$chartPaid"
      />

      <x-pmms.occupancy-chart
        :rentedUnits="$rentedUnits"
        :vacantUnits="$vacantUnits"
        :selfUnits="$selfUnits"
        :occupancyRate="$occupancyRate"
      />

      <x-pmms.recent-payments
        :payments="$recentPayments"
      />

      <x-pmms.overdue-payments
        :payments="$overduePayments"
      />

      <x-pmms.expiring-agreements
        :agreements="$expiringAgreements"
      />

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-pmms.landlords-summary
          :landlords="$landlords"
        />

        <x-pmms.recent-activities
          :activities="$recentActivities"
        />
      </div>

    </div>

  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {

      const isDark    = document.documentElement.classList.contains('dark');
      const textColor = isDark ? '#94A3B8' : '#64748B';
      const gridColor = isDark ? '#1E293B' : '#F1F5F9';
      const bgColor   = isDark ? 'transparent' : 'transparent';

      // ── Rent Collection Bar Chart ───────────────────────────────────
      const rentChartEl = document.querySelector('#pmmsRentChart');
      if (rentChartEl) {
        new ApexCharts(rentChartEl, {
          chart: {
            type: 'bar',
            height: 270,
            toolbar: { show: false },
            background: bgColor,
            fontFamily: 'Outfit, sans-serif',
            animations: { enabled: true, easing: 'easeinout', speed: 600 },
          },
          series: [
            { name: 'Rent Due',   data: @json($chartDue) },
            { name: 'Collected',  data: @json($chartPaid) },
          ],
          colors: ['#CBD5E1', '#465fff'],
          plotOptions: {
            bar: {
              borderRadius: 6,
              columnWidth: '52%',
              borderRadiusApplication: 'end',
            },
          },
          dataLabels: { enabled: false },
          xaxis: {
            categories: @json($chartMonths),
            labels: { style: { colors: textColor, fontSize: '12px', fontFamily: 'Outfit, sans-serif' } },
            axisBorder: { show: false },
            axisTicks:  { show: false },
          },
          yaxis: {
            labels: {
              style: { colors: textColor, fontSize: '11px', fontFamily: 'Outfit, sans-serif' },
              formatter: (val) => 'Rs. ' + (val / 1000).toFixed(0) + 'K',
            },
          },
          grid: {
            borderColor: gridColor,
            strokeDashArray: 5,
            xaxis: { lines: { show: false } },
          },
          legend: { show: false },
          tooltip: {
            theme: isDark ? 'dark' : 'light',
            y: { formatter: (val) => 'Rs. ' + val.toLocaleString('en-PK') },
          },
        }).render();
      }

      // ── Occupancy Donut Chart ───────────────────────────────────────
      const donutEl = document.querySelector('#pmmsOccupancyChart');
      if (donutEl) {
        new ApexCharts(donutEl, {
          chart: {
            type: 'donut',
            height: 280,
            background: bgColor,
            toolbar: { show: false },
            fontFamily: 'Outfit, sans-serif',
            animations: { enabled: true, easing: 'easeinout', speed: 600 },
          },
          series: [
            {{ $rentedUnits }},
            {{ $vacantUnits }},
            {{ $selfUnits }},
          ],
          labels: ['Rented', 'Vacant', 'Self'],
          colors: ['#12b76a', '#f79009', '#94a3b8'],
          plotOptions: {
            pie: {
              donut: {
                size: '76%',
                labels: {
                  show: true,
                  name: {
                    show: true,
                    fontSize: '12px',
                    fontFamily: 'Outfit, sans-serif',
                    color: textColor,
                    offsetY: -10
                  },
                  value: {
                    show: true,
                    fontSize: '32px',
                    fontFamily: 'Outfit, sans-serif',
                    fontWeight: '800',
                    color: isDark ? '#ffffff' : '#1e293b',
                    offsetY: 10,
                    formatter: (val) => val + ' Units'
                  },
                  total: {
                    show: true,
                    label: 'Occupancy',
                    color: textColor,
                    fontSize: '12px',
                    fontFamily: 'Outfit, sans-serif',
                    fontWeight: '600',
                    formatter: () => '{{ $occupancyRate }}%'
                  }
                }
              },
            },
          },
          dataLabels: { enabled: false },
          legend: { show: false },
          stroke: { width: 0 },
          tooltip: {
            theme: isDark ? 'dark' : 'light',
            y: { formatter: (val) => val + ' units' },
          },
        }).render();
      }

    });
  </script>
@endpush