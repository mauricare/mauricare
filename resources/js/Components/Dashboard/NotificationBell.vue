<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const isOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const isLoading = ref(false);
const rootEl = ref(null);
let pollTimer = null;

const loadNotifications = async () => {
    try {
        const response = await axios.get('/api/notifications');
        notifications.value = response.data.data || [];
        unreadCount.value = response.data.unread_count || 0;
    } catch {
        // Keep the last known state when the request fails.
    }
};

const toggleOpen = async () => {
    isOpen.value = !isOpen.value;

    if (!isOpen.value) {
        return;
    }

    isLoading.value = true;
    await loadNotifications();
    isLoading.value = false;

    if (unreadCount.value > 0) {
        try {
            await axios.post('/api/notifications/mark-all-read');
            unreadCount.value = 0;
        } catch {
            // The badge will correct itself on the next refresh.
        }
    }
};

const closeOnOutsideClick = (event) => {
    if (isOpen.value && rootEl.value && !rootEl.value.contains(event.target)) {
        isOpen.value = false;
    }
};

const relativeTime = (value) => {
    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    const seconds = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));

    if (seconds < 60) {
        return 'Just now';
    }

    if (seconds < 3600) {
        return `${Math.floor(seconds / 60)}m ago`;
    }

    if (seconds < 86400) {
        return `${Math.floor(seconds / 3600)}h ago`;
    }

    return new Intl.DateTimeFormat('en-GB', { day: '2-digit', month: 'short' }).format(date);
};

onMounted(() => {
    loadNotifications();
    pollTimer = setInterval(loadNotifications, 30000);
    document.addEventListener('click', closeOnOutsideClick);
});

onUnmounted(() => {
    if (pollTimer) {
        clearInterval(pollTimer);
    }

    document.removeEventListener('click', closeOnOutsideClick);
});
</script>

<template>
    <div ref="rootEl" class="relative">
        <button
            type="button"
            class="relative flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 lg:h-12 lg:w-12"
            aria-label="Notifications"
            @click="toggleOpen"
        >
            <i class="fa-regular fa-bell text-lg"></i>
            <span
                v-if="unreadCount"
                class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-600 px-1 text-[11px] font-bold text-white"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <div
            v-if="isOpen"
            class="absolute right-0 top-full z-40 mt-2 w-96 max-w-[calc(100vw-2.5rem)] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
        >
            <h3 class="border-b border-slate-100 px-5 py-3 text-sm font-bold text-slate-950">Notifications</h3>

            <div class="max-h-96 overflow-y-auto">
                <p v-if="isLoading" class="px-5 py-8 text-center text-sm text-slate-500">
                    Loading...
                </p>
                <p v-else-if="!notifications.length" class="px-5 py-8 text-center text-sm text-slate-500">
                    No notifications yet.
                </p>
                <div
                    v-for="notification in notifications"
                    v-else
                    :key="notification.id"
                    class="flex gap-3 border-b border-slate-50 px-5 py-4 last:border-b-0"
                    :class="{ 'bg-teal-50/50': !notification.read_at }"
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-100 text-teal-700">
                        <i class="fa-regular fa-calendar-check text-sm"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm leading-5 text-slate-800">{{ notification.message }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ relativeTime(notification.created_at) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
