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
                if (document.body) document.body.classList.add('dark', 'bg-gray-900');
            } else {
                document.documentElement.classList.remove('dark');
                if (document.body) document.body.classList.remove('dark', 'bg-gray-900');
            }
        })();
    </script>

</head>

<body x-data="{ 'loaded': false}" x-init="$store.sidebar.isExpanded = false;
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

        <div class="flex-1 min-w-0 transition-all duration-300 ease-in-out" @if(!$hideSidebar) :class="{
             'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
             'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
             'ml-0': $store.sidebar.isMobileOpen
         }" @endif>
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
        window.confirmAction = function (formElement, message, title = 'Are you sure?', confirmText = 'Yes, proceed') {
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

    <!-- Single-Tab / Unique View Guard -->
    <script>
        (function () {
            // ─── Exempt paths (allow multiple tabs) ───────────────────────────────────
            const path = window.location.pathname;
            const exempt = ['/create', '/edit', '/login', '/register', '/password', '/bills/'];
            if (exempt.some(function (p) { return path.includes(p); })) return;

            // ─── Constants ────────────────────────────────────────────────────────────
            const KEY = 'pm_tab:' + path;                   // localStorage key for this page
            const PING_KEY = 'pm_ping:' + path;                  // used to trigger storage events
            const PONG_KEY = 'pm_pong:' + path;
            const tabId = Math.random().toString(36).slice(2) + Date.now();
            const HEARTBEAT = 1500;                               // ms between heartbeats
            const STALE_AFTER = 4000;                              // ms before a claim is considered stale

            // ─── Helpers ─────────────────────────────────────────────────────────────
            function now() { return Date.now(); }
            function esc(s) {
                return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }
            function claimOwnership() {
                localStorage.setItem(KEY, JSON.stringify({ id: tabId, t: now() }));
            }
            function readClaim() {
                try { return JSON.parse(localStorage.getItem(KEY)); } catch (e) { return null; }
            }
            function isStale(claim) {
                return !claim || (now() - claim.t) > STALE_AFTER;
            }
            function isMine(claim) {
                return claim && claim.id === tabId;
            }

            // ─── Show blocker overlay ─────────────────────────────────────────────────
            function block() {
                var html = '<div style="position:fixed;inset:0;z-index:2147483647;display:flex;align-items:center;justify-content:center;background:#0f172a;font-family:system-ui,sans-serif;padding:2rem">'
                    + '<div style="max-width:460px;background:#1e293b;padding:2.5rem;border-radius:1.25rem;border:2px solid #3b82f6;box-shadow:0 25px 50px rgba(0,0,0,.6);text-align:center">'
                    + '<div style="font-size:3rem;margin-bottom:.75rem">📑</div>'
                    + '<h2 style="margin:0 0 .75rem;font-size:1.4rem;font-weight:900;color:#fff">Already Open in Another Tab</h2>'
                    + '<p style="margin:0 0 1.5rem;color:#94a3b8;line-height:1.6;font-size:.9rem">The page <strong style="color:#60a5fa">' + esc(path) + '</strong> is already open. Switch to that tab or close this one.</p>'
                    + '<div style="display:flex;gap:.75rem;justify-content:center">'
                    + '<a href="/dashboard" style="background:#2563eb;color:#fff;padding:.8rem 1.4rem;border-radius:.75rem;font-weight:800;text-decoration:none;font-size:.9rem">Go to Dashboard</a>'
                    + '<button onclick="window.close()" style="background:#334155;color:#fff;padding:.8rem 1.2rem;border-radius:.75rem;font-weight:700;border:none;cursor:pointer;font-size:.9rem">Close Tab</button>'
                    + '</div></div></div>';

                // Wipe page content and show blocker
                clearInterval(heartbeat);
                localStorage.removeItem(KEY);
                if (document.body) {
                    document.body.innerHTML = html;
                } else {
                    document.addEventListener('DOMContentLoaded', function () { document.body.innerHTML = html; });
                }
            }

            // ─── STEP 1: Instant ping-pong check BEFORE claiming ─────────────────────
            // We write to PING_KEY — any existing tab on the same path sees the storage
            // event immediately and responds by writing to PONG_KEY.
            var pingResponded = false;

            window.addEventListener('storage', function onStorage(e) {
                // An existing tab hears our ping → respond
                if (e.key === PING_KEY && e.newValue) {
                    try {
                        var ping = JSON.parse(e.newValue);
                        if (ping.from !== tabId) {
                            // We are an existing tab → respond
                            var claim = readClaim();
                            if (claim && isMine(claim) && !isStale(claim)) {
                                window.focus();
                                localStorage.setItem(PONG_KEY, JSON.stringify({ to: ping.from, t: now() }));
                            }
                        }
                    } catch (err) { }
                }

                // We hear a pong meant for us → we are the duplicate
                if (e.key === PONG_KEY && e.newValue) {
                    try {
                        var pong = JSON.parse(e.newValue);
                        if (pong.to === tabId) {
                            pingResponded = true;
                            block();
                        }
                    } catch (err) { }
                }

                // Heartbeat from another tab on same path that is not us → block ourselves
                if (e.key === KEY && e.newValue) {
                    try {
                        var c = JSON.parse(e.newValue);
                        if (c.id !== tabId && !isStale(c)) {
                            block();
                        }
                    } catch (err) { }
                }
            });

            // Broadcast our ping
            localStorage.setItem(PING_KEY, JSON.stringify({ from: tabId, t: now() }));

            // Wait 120ms for any pong (existing tab responds almost instantly via storage event)
            setTimeout(function () {
                if (pingResponded) return; // already blocked

                // No existing tab responded → check stale heartbeat fallback
                var existing = readClaim();
                if (existing && !isMine(existing) && !isStale(existing)) {
                    block();
                    return;
                }

                // ─── We are the primary tab — claim ownership ─────────────────────
                claimOwnership();

                // Keep heartbeat alive
                var heartbeat = setInterval(claimOwnership, HEARTBEAT);

                // ─── Release on navigation / close ────────────────────────────────
                window.addEventListener('beforeunload', function () {
                    clearInterval(heartbeat);
                    var c = readClaim();
                    if (c && isMine(c)) localStorage.removeItem(KEY);
                });

                // ─── Reuse same tab for same-path links ───────────────────────────
                window.name = 'pm_tab_' + encodeURIComponent(path);

                document.addEventListener('click', function (e) {
                    var link = e.target.closest('a[href]');
                    if (!link) return;
                    try {
                        var url = new URL(link.href, location.origin);
                        if (url.origin !== location.origin) return;
                        var tp = url.pathname;
                        if (!exempt.some(function (p) { return tp.includes(p); })) {
                            link.target = 'pm_tab_' + encodeURIComponent(tp);
                        }
                    } catch (err) { }
                }, true);

            }, 120);

        })();
    </script>


</body>

@stack('scripts')

</html>