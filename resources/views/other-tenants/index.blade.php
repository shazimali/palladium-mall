@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="" />

    @php
        $search = request('search');
        $highlight = function($text) use ($search) {
            if (empty($text)) return '';
            if (empty($search)) {
                return e($text);
            }
            $escapedSearch = preg_quote($search, '/');
            return preg_replace('/(' . $escapedSearch . ')/i', '<mark class="bg-amber-100 text-amber-900 rounded px-0.5 dark:bg-amber-950/70 dark:text-amber-300 font-medium">$1</mark>', e($text));
        };
    @endphp

    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <x-common.component-card title="Other Flat/Shop Tenants" desc="Manage Other Flat/Shop Tenants">

        {{-- ── Top bar: Badges & Actions ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            {{-- Status Badges (Bigger, size of add button) --}}
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" onclick="setStatusFilter('')"
                   class="status-badge-btn inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold transition-all duration-200"
                   data-status="">
                   <span>All Tenants</span>
                   <span class="badge-count rounded-full px-2 py-0.5 text-xs bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $counts['total'] }}</span>
                </button>

                <button type="button" onclick="setStatusFilter('attached')"
                   class="status-badge-btn inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold transition-all duration-200"
                   data-status="attached">
                   <span>Attached</span>
                   <span class="badge-count rounded-full px-2 py-0.5 text-xs bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $counts['attached'] }}</span>
                </button>

                <button type="button" onclick="setStatusFilter('detached')"
                   class="status-badge-btn inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold transition-all duration-200"
                   data-status="detached">
                   <span>Detached</span>
                   <span class="badge-count rounded-full px-2 py-0.5 text-xs bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $counts['detached'] }}</span>
                </button>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'status', 'filter_month']))
                    <a href="{{ route('other-tenants.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Clear
                    </a>
                @endif
                @if(auth()->user()->hasPermission('other_tenants.create') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('other-tenants.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Other Tenant
                    </a>
                @endif
            </div>
        </div>

        {{-- ── Search bar ── --}}
        <div class="mb-6 mt-4 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <form id="filter-form" action="{{ route('other-tenants.index') }}" method="GET"
                class="flex flex-col gap-4 sm:flex-row sm:items-center" onsubmit="event.preventDefault();">
                <input type="hidden" name="status" id="status-filter" value="{{ request('status') }}">
                <div class="relative flex-1 max-w-md">
                    <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                        </svg>
                    </span>
                    <input type="text" name="search" id="search-input" value="{{ request('search') }}"
                        placeholder="Search by name, CNIC, phone..." autocomplete="off"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <!-- Month/Year Filter -->
                <div class="relative">
                    <input type="text" id="filter_month" name="filter_month" value="{{ request('filter_month') }}" placeholder="Select Month/Year" autocomplete="off"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-48 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
            </form>
        </div>

        <div id="table-container" class="transition-opacity duration-200">
            @include('other-tenants._table')
        </div>

    </x-common.component-card>

    @push('scripts')
    <script>
        let ajaxTimeout = null;

        function fetchResults() {
            const form = document.getElementById('filter-form');
            if (!form) return;
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({ path: newUrl }, '', newUrl);

            const container = document.getElementById('table-container');
            if (container) container.classList.add('opacity-50');

            params.append('ajax', '1');

            fetch(`${window.location.pathname}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                if (container) {
                    container.classList.remove('opacity-50');
                    container.innerHTML = html;
                }
                updateBadgeActiveState();
            })
            .catch(err => {
                if (container) container.classList.remove('opacity-50');
                console.error('Error fetching search results:', err);
            });
        }

        function setStatusFilter(status) {
            const input = document.getElementById('status-filter');
            if (input) input.value = status;
            fetchResults();
        }

        function updateBadgeActiveState() {
            const statusInput = document.getElementById('status-filter');
            const currentStatus = statusInput ? statusInput.value : '';
            const buttons = document.querySelectorAll('.status-badge-btn');
            
            buttons.forEach(btn => {
                const status = btn.getAttribute('data-status');
                const badge = btn.querySelector('.badge-count');
                if (status === currentStatus) {
                    let activeBg = 'bg-brand-500 border-brand-500 text-white';
                    if (status === 'attached') activeBg = 'bg-emerald-600 border-emerald-600 text-white';
                    if (status === 'detached') activeBg = 'bg-amber-600 border-amber-600 text-white';

                    btn.className = `status-badge-btn inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold transition-all duration-200 shadow-xs ${activeBg}`;
                    if (badge) {
                        badge.className = 'badge-count rounded-full px-2 py-0.5 text-xs bg-white/20 text-white';
                    }
                } else {
                    btn.className = 'status-badge-btn inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold transition-all duration-200 border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800';
                    if (badge) {
                        badge.className = 'badge-count rounded-full px-2 py-0.5 text-xs bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            flatpickr('#filter_month', {
                dateFormat: 'Y-m-01',
                altInput: true,
                altFormat: 'F Y',
                allowInput: false,
                disableMobile: true,
                plugins: [
                    new monthSelectPlugin({
                        shorthand: false,
                        dateFormat: 'Y-m-01',
                        altFormat: 'F Y',
                        theme: 'light',
                    })
                ],
                onChange: function(selectedDates, dateStr, instance) {
                    fetchResults();
                }
            });

            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(ajaxTimeout);
                    ajaxTimeout = setTimeout(fetchResults, 250);
                });
            }

            // Pagination Link Delegation
            const container = document.getElementById('table-container');
            if (container) {
                container.addEventListener('click', function(e) {
                    const link = e.target.closest('.pagination a, a.page-link');
                    if (link) {
                        e.preventDefault();
                        const url = new URL(link.href);
                        
                        const currentSearch = document.getElementById('search-input');
                        const currentStatus = document.getElementById('status-filter');
                        const filterMonth = document.getElementById('filter_month');

                        if (url.searchParams.has('search') && currentSearch) {
                            currentSearch.value = url.searchParams.get('search');
                        }
                        if (url.searchParams.has('status') && currentStatus) {
                            currentStatus.value = url.searchParams.get('status');
                        }
                        if (url.searchParams.has('filter_month') && filterMonth) {
                            filterMonth.value = url.searchParams.get('filter_month');
                        }

                        container.classList.add('opacity-50');
                        url.searchParams.append('ajax', '1');

                        fetch(url.toString(), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.text())
                        .then(html => {
                            container.classList.remove('opacity-50');
                            container.innerHTML = html;
                            window.history.pushState({ path: url.toString() }, '', url.toString());
                            updateBadgeActiveState();
                        })
                        .catch(err => {
                            container.classList.remove('opacity-50');
                            console.error('Error fetching paginated data:', err);
                        });
                    }
                });
            }

            // AJAX Filter Clear
            document.addEventListener('click', function(e) {
                const clearBtn = e.target.closest('a[href$="/other-tenants"]');
                if (clearBtn && !clearBtn.closest('.pagination')) {
                    e.preventDefault();
                    const sInput = document.getElementById('search-input');
                    const stInput = document.getElementById('status-filter');
                    const mInput = document.getElementById('filter_month');
                    if (sInput) sInput.value = '';
                    if (stInput) stInput.value = '';
                    if (mInput && mInput._flatpickr) mInput._flatpickr.clear();
                    fetchResults();
                }
            });

            updateBadgeActiveState();
        });

        function attachModal() {
            return {
                showModal: false,
                tenantId: null,
                tenantName: '',
                unitSearch: '',
                openAttach(id, name) {
                    this.tenantId   = id;
                    this.tenantName = name;
                    this.unitSearch = '';
                    this.showModal  = true;
                },
            };
        }
    </script>
    @endpush
@endsection
