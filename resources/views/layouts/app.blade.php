<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | Palladium Mall</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    {{--
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

    <!-- Theme Store -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                init() {
                    const savedTheme = localStorage.getItem('theme');
                    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
                    this.theme = savedTheme || systemTheme;
                    this.updateTheme();
                },
                theme: 'light',
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                    this.updateTheme();
                },
                updateTheme() {
                    const html = document.documentElement;
                    const body = document.body;
                    if (this.theme === 'dark') {
                        html.classList.add('dark');
                        body.classList.add('dark', 'bg-gray-900');
                    } else {
                        html.classList.remove('dark');
                        body.classList.remove('dark', 'bg-gray-900');
                    }
                }
            });

            Alpine.store('sidebar', {
                // Initialize to always closed/collapsed by default
                isExpanded: false,
                isMobileOpen: false,
                isHovered: false,

                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    // When toggling desktop sidebar, ensure mobile menu is closed
                    this.isMobileOpen = false;
                },

                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                    // Don't modify isExpanded when toggling mobile menu
                },

                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },

                setHovered(val) {
                    // Only allow hover effects on desktop when sidebar is collapsed
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>

    <!-- Apply dark mode immediately to prevent flash -->
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = savedTheme || systemTheme;
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark', 'bg-gray-900');
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark', 'bg-gray-900');
            }
        })();
    </script>

</head>

<body x-data="{ 'loaded': true}" x-init="$store.sidebar.isExpanded = false;
    const checkMobile = () => {
        if (window.innerWidth < 1280) {
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = false;
        }
    };
    window.addEventListener('resize', checkMobile);">

    {{-- preloader --}}
    <x-common.preloader />
    {{-- preloader end --}}

    @php
        $hideSidebar = request()->has('no_sidebar') || request()->routeIs('reports.index');
    @endphp

    <div class="min-h-screen xl:flex">
        @if(!$hideSidebar)
            @include('layouts.backdrop')
            @include('layouts.sidebar')
        @endif

        <div class="flex-1 min-w-0 transition-all duration-300 ease-in-out"
             @if(!$hideSidebar)
             :class="{
                 'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                 'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                 'ml-0': $store.sidebar.isMobileOpen
             }"
             @endif>
            <!-- app header start -->
            @if(!$hideSidebar)
                @include('layouts.app-header')
            @endif
            <!-- app header end -->
            <div class="p-4 mx-auto @yield('containerClass', 'max-w-(--breakpoint-2xl)') md:p-6">
                @yield('content')
            </div>
        </div>

    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.confirmAction = function(formElement, message, title = 'Are you sure?', confirmText = 'Yes, proceed') {
            Swal.fire({
                title: title,
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors mx-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2',
                    cancelButton: 'inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors mx-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    formElement.submit();
                }
            });
        };
    </script>

    <!-- Single-Tab / Unique View Guard Script -->
    <script>
        (function () {
            const currentPath = window.location.pathname;

            // Exempt voucher creation, edit, and entry pages from single-tab locking
            const isExemptFromSingleTab = currentPath.includes('/create') || 
                                          currentPath.includes('/edit') || 
                                          currentPath.includes('vouchers');

            if (!isExemptFromSingleTab) {
                // Set deterministic window name so named target links natively reuse this tab
                window.name = 'pm_tab_' + encodeURIComponent(currentPath);
            }

            // Automatically enforce target names on _blank links (excluding exempt paths)
            document.addEventListener('click', function (e) {
                const link = e.target.closest('a[target="_blank"]');
                if (link && link.href) {
                    try {
                        const url = new URL(link.href, window.location.origin);
                        if (url.origin === window.location.origin) {
                            const linkPath = url.pathname;
                            const isLinkExempt = linkPath.includes('/create') || 
                                                 linkPath.includes('/edit') || 
                                                 linkPath.includes('vouchers');
                            if (!isLinkExempt) {
                                link.target = 'pm_tab_' + encodeURIComponent(linkPath);
                            }
                        }
                    } catch (err) {}
                }
            }, true);

            // Intercept window.open calls without explicit target name
            const originalOpen = window.open;
            window.open = function (url, target, features) {
                if (!target || target === '_blank') {
                    try {
                        const parsed = new URL(url, window.location.origin);
                        if (parsed.origin === window.location.origin) {
                            const linkPath = parsed.pathname;
                            const isLinkExempt = linkPath.includes('/create') || 
                                                 linkPath.includes('/edit') || 
                                                 linkPath.includes('vouchers');
                            if (!isLinkExempt) {
                                target = 'pm_tab_' + encodeURIComponent(linkPath);
                            }
                        }
                    } catch (err) {}
                }
                return originalOpen.call(window, url, target, features);
            };

            // BroadcastChannel check for tabs (skipped for create/edit/voucher paths)
            if ('BroadcastChannel' in window && !isExemptFromSingleTab) {
                const tabId = 'tab_' + Math.random().toString(36).substring(2, 11) + '_' + Date.now();
                const channel = new BroadcastChannel('pm_single_view_channel');

                // Ask if any tab is already on this pathname
                channel.postMessage({
                    type: 'CHECK_VIEW_OPEN',
                    pathname: currentPath,
                    tabId: tabId
                });

                channel.onmessage = function (event) {
                    const data = event.data;
                    if (!data) return;

                    // Existing tab receiving check request
                    if (data.type === 'CHECK_VIEW_OPEN' && data.pathname === currentPath && data.tabId !== tabId) {
                        // Focus current window
                        window.focus();
                        // Reply to new tab that this view is already open
                        channel.postMessage({
                            type: 'VIEW_ALREADY_OPEN',
                            pathname: currentPath,
                            requestingTabId: data.tabId,
                            tabId: tabId
                        });
                    }

                    // New tab receiving response that view is already open in another tab
                    if (data.type === 'VIEW_ALREADY_OPEN' && data.requestingTabId === tabId && data.pathname === currentPath) {
                        // Attempt to focus existing tab and inform user
                        if (window.Swal) {
                            Swal.fire({
                                title: 'View Already Open',
                                text: 'This view is already open in another tab. Switching to active tab...',
                                icon: 'info',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        }
                        // Close or redirect duplicate tab
                        setTimeout(() => {
                            try {
                                window.close();
                            } catch (e) {}
                            if (!window.closed) {
                                // If browser prevented closing, go back or to home
                                if (document.referrer && document.referrer !== window.location.href) {
                                    window.location.href = document.referrer;
                                }
                            }
                        }, 800);
                    }
                };
            }
        })();
    </script>
</body>

@stack('scripts')

</html>