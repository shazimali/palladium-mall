{{-- Real-Time Notification Dropdown Component --}}
<div class="relative" x-data="{
    dropdownOpen: false,
    unreadCount: 0,
    notifications: [],
    loading: false,

    init() {
        this.fetchNotifications();
        // Poll every 15 seconds as fallback
        setInterval(() => this.fetchNotifications(), 15000);

        // Listen via Echo if initialized
        if (window.Echo && {{ auth()->check() ? auth()->id() : 'null' }}) {
            window.Echo.private('users.{{ auth()->id() }}')
                .notification((notification) => {
                    this.unreadCount++;
                    this.notifications.unshift({
                        id: notification.id || Date.now(),
                        data: notification,
                        created_at: 'Just now',
                        read_at: null
                    });
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: notification.message || 'New notification',
                            showConfirmButton: false,
                            timer: 4000
                        });
                    }
                });
        }
    },

    async fetchNotifications() {
        try {
            const res = await fetch('/notifications', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (res.ok) {
                const data = await res.json();
                this.unreadCount = data.unread_count || 0;
                this.notifications = data.notifications || [];
            }
        } catch (e) {
            console.error('Error fetching notifications:', e);
        }
    },

    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
        if (this.dropdownOpen) {
            this.fetchNotifications();
        }
    },

    closeDropdown() {
        this.dropdownOpen = false;
    },

    async markRead(notification) {
        try {
            await fetch('/notifications/' + notification.id + '/read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            this.fetchNotifications();
            if (notification.data && notification.data.url) {
                window.location.href = notification.data.url;
            }
        } catch(e) {}
    },

    async markAllRead() {
        try {
            await fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            this.fetchNotifications();
        } catch(e) {}
    }
}" @click.away="closeDropdown()">
    <!-- Notification Button -->
    <button
        class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-dark-900 h-11 w-11 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
        @click="toggleDropdown()"
        type="button"
        title="Notifications"
    >
        <!-- Notification Badge Count -->
        <span
            x-show="unreadCount > 0"
            class="absolute -top-1 -right-1 z-10 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white shadow-sm"
            x-text="unreadCount > 99 ? '99+' : unreadCount"
        ></span>

        <!-- Bell Icon -->
        <svg
            class="fill-current"
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z"
                fill=""
            />
        </svg>
    </button>

    <!-- Dropdown Start -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute -right-[240px] z-50 mt-[17px] flex max-h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-xl dark:border-gray-800 dark:bg-gray-900 sm:w-[360px] lg:right-0"
        style="display: none;"
    >
        <!-- Dropdown Header -->
        <div class="flex items-center justify-between pb-3 mb-2 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2">
                <h5 class="text-base font-semibold text-gray-800 dark:text-white/90">Notifications</h5>
                <span x-show="unreadCount > 0" class="px-2 py-0.5 text-xs font-bold text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400 rounded-full" x-text="unreadCount + ' unread'"></span>
            </div>

            <button
                x-show="unreadCount > 0"
                @click="markAllRead()"
                type="button"
                class="text-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400"
            >
                Mark all read
            </button>
        </div>

        <!-- Notification List -->
        <ul class="flex flex-col overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800 custom-scrollbar max-h-[360px]">
            <template x-if="notifications.length === 0">
                <li class="py-8 text-center text-sm text-gray-400">
                    No new notifications
                </li>
            </template>

            <template x-for="item in notifications" :key="item.id">
                <li>
                    <button
                        type="button"
                        @click="markRead(item)"
                        class="w-full text-left flex gap-3 p-3 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors rounded-lg"
                        :class="{ 'bg-brand-50/50 dark:bg-brand-950/20 font-medium': !item.read_at }"
                    >
                        <div class="flex-shrink-0 mt-0.5">
                            <span class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-600 dark:text-brand-400 flex items-center justify-center text-xs font-bold">
                                🔔
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-800 dark:text-gray-200 leading-snug" x-text="item.data.message || 'Notification update'"></p>
                            <span class="mt-1 block text-[10px] text-gray-400" x-text="item.data.created_at || 'Recently'"></span>
                        </div>
                    </button>
                </li>
            </template>
        </ul>
    </div>
    <!-- Dropdown End -->
</div>
