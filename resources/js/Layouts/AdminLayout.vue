<script setup>
import AdminImpersonationMenu from '@/Components/Admin/AdminImpersonationMenu.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    activeSection: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['select']);
const page = usePage();
const mobileOpen = ref(false);

const menus = [
    { key: 'care_seekers', label: 'Care Seekers', icon: 'fa-users' },
    { key: 'care_givers', label: 'Care Givers', icon: 'fa-user-nurse' },
    { key: 'bookings', label: 'Bookings', icon: 'fa-calendar-check' },
    { key: 'messages', label: 'Messages', icon: 'fa-message' },
    { key: 'invoices', label: 'Invoices', icon: 'fa-file-invoice' },
];

const select = (key) => {
    mobileOpen.value = false;
    emit('select', key);
};
</script>

<template>
    <div class="min-h-screen bg-slate-100 text-slate-900">
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col border-r border-slate-200 bg-white transition-transform lg:translate-x-0"
            :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-32 items-center justify-center border-b border-slate-100 px-4">
                <Link :href="route('dashboard')" class="flex items-center justify-center">
                    <img
                        src="/images/mauricare-home-care-services-logo.png"
                        alt="Mauricare"
                        class="h-24 w-auto object-contain"
                    />
                </Link>
            </div>

            <nav class="flex-1 touch-pan-y space-y-2 overflow-y-auto overscroll-contain p-4">
                <button
                    v-for="menu in menus"
                    :key="menu.key"
                    type="button"
                    class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-semibold transition"
                    :class="activeSection === menu.key
                        ? 'bg-[#e8f7f4] text-[#117d73]'
                        : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                    @click="select(menu.key)"
                >
                    <i class="fa-solid w-5 text-center" :class="menu.icon"></i>
                    {{ menu.label }}
                </button>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-semibold text-slate-600 transition hover:bg-red-50 hover:text-red-700"
                >
                    <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                    Log out
                </Link>
            </nav>

            <div class="shrink-0 border-t border-slate-100 p-4">
                <div class="mb-3 flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#117d73] font-bold text-white">
                        {{ page.props.auth.user.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">{{ page.props.auth.user.name }}</p>
                        <p class="truncate text-xs text-slate-500">{{ page.props.auth.user.email }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <div v-if="mobileOpen" class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden" @click="mobileOpen = false"></div>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-20 flex h-20 items-center justify-between border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-8">
                <button type="button" class="rounded-lg p-2 text-slate-600 lg:hidden" @click="mobileOpen = true">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div class="ml-auto flex items-center gap-3">
                    <span class="hidden rounded-full bg-[#e8f7f4] px-3 py-1 text-xs font-bold uppercase tracking-wide text-[#117d73] sm:inline-flex">
                        Administrator
                    </span>
                    <AdminImpersonationMenu />
                    <div class="flex items-center gap-2">
                        <img
                            v-if="page.props.auth.user.avatar_url"
                            :src="page.props.auth.user.avatar_url"
                            :alt="`${page.props.auth.user.name} profile photo`"
                            class="h-10 w-10 rounded-full object-cover"
                        />
                        <span
                            v-else
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-[#117d73] font-bold text-white"
                        >
                            {{ page.props.auth.user.name?.charAt(0)?.toUpperCase() }}
                        </span>
                    </div>
                </div>
            </header>

            <main class="p-4 sm:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
