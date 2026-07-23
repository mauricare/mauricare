<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    active: {
        type: String,
        default: 'dashboard',
    },
    inPage: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['navigate', 'create']);

const page = usePage();
const mobileNavOpen = ref(false);

const navItems = [
    { key: 'dashboard', label: 'Dashboard', icon: 'fa-house' },
    { key: 'bookings', label: 'My Bookings', icon: 'fa-calendar-days' },
    { key: 'create', label: 'New Care Request', icon: 'fa-circle-plus' },
    { key: 'providers', label: 'My Care Providers', icon: 'fa-user-group' },
    { key: 'invoices', label: 'Invoices & Payments', icon: 'fa-file-invoice' },
    { key: 'messages', label: 'Messages', icon: 'fa-message' },
    { key: 'profile', label: 'Profile Settings', icon: 'fa-gear' },
    { key: 'help', label: 'Help & Support', icon: 'fa-circle-question' },
];

const firstName = computed(() => page.props.auth.user.name?.split(' ')[0] || 'there');

const dashboardUrl = (item) => {
    if (item.key === 'create') {
        return `${route('dashboard')}?create=1`;
    }

    if (item.key === 'dashboard') {
        return route('dashboard');
    }

    return `${route('dashboard')}?section=${item.key}`;
};

const itemIsButton = (item) => props.inPage && item.key !== 'profile';

const itemHref = (item) => {
    if (itemIsButton(item)) {
        return undefined;
    }

    if (item.key === 'profile') {
        return route('profile.edit');
    }

    return dashboardUrl(item);
};

const handleItemClick = (item) => {
    mobileNavOpen.value = false;

    if (item.key === 'profile' || !props.inPage) {
        return;
    }

    if (item.key === 'create') {
        emit('create');
        return;
    }

    emit('navigate', item.key);
};

</script>

<template>
    <div class="min-h-screen bg-[#f7fafc] text-slate-950">
        <div
            v-if="mobileNavOpen"
            class="fixed inset-0 z-20 bg-slate-950/40 lg:hidden"
            @click="mobileNavOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-30 flex w-72 flex-col border-r border-slate-100 bg-white px-4 py-8 transition-transform duration-200 lg:translate-x-0"
            :class="mobileNavOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <Link :href="route('dashboard')" class="flex items-center px-3">
                <img
                    src="/images/mauricare-home-care-services-logo.png"
                    alt="Mauricare"
                    class="h-14 w-auto object-contain"
                />
            </Link>

            <nav class="mt-12 space-y-2">
                <component
                    :is="itemIsButton(item) ? 'button' : Link"
                    v-for="item in navItems"
                    :key="item.key"
                    :href="itemHref(item)"
                    type="button"
                    class="flex w-full items-center rounded-xl px-5 py-4 text-left text-sm font-semibold transition"
                    :class="item.key === active
                        ? 'bg-teal-50 text-slate-950 shadow-sm shadow-teal-100'
                        : 'text-slate-700 hover:bg-slate-50'"
                    @click="handleItemClick(item)"
                >
                    <i class="fa-solid mr-4 w-5 text-center text-base" :class="[item.icon, item.key === active ? 'text-teal-700' : 'text-slate-500']"></i>
                    <span>{{ item.label }}</span>
                </component>
            </nav>

            <div class="mt-auto border-t border-slate-200 pt-5">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center rounded-xl px-5 py-4 text-left text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    <i class="fa-solid fa-arrow-right-from-bracket mr-4 w-5 text-center text-slate-500"></i>
                    Logout
                </Link>
            </div>
        </aside>

        <main class="lg:pl-72">
            <div class="mx-auto max-w-[1540px] px-5 py-6 sm:px-8 lg:px-12">
                <div class="mb-6 flex items-center justify-between lg:hidden">
                    <button
                        type="button"
                        class="flex h-11 w-11 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700"
                        aria-label="Open navigation"
                        @click="mobileNavOpen = true"
                    >
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <img
                        src="/images/mauricare-home-care-services-logo.png"
                        alt="Mauricare"
                        class="h-10 w-auto object-contain"
                    />
                    <Link :href="route('profile.edit')">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-teal-100 font-bold text-teal-800">
                            {{ firstName.charAt(0) }}
                        </span>
                    </Link>
                </div>

                <header class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <slot name="header" />
                    </div>

                    <Link :href="route('profile.edit')" class="hidden items-center gap-3 lg:flex">
                        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-teal-100 text-lg font-bold text-teal-800">
                            {{ firstName.charAt(0) }}
                        </span>
                        <i class="fa-solid fa-chevron-down text-sm text-slate-500"></i>
                    </Link>
                </header>

                <slot />
            </div>
        </main>
    </div>
</template>
