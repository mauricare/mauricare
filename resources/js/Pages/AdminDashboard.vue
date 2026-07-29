<script setup>
import AdminBookingsTable from '@/Components/Admin/AdminBookingsTable.vue';
import AdminUserTable from '@/Components/Admin/AdminUserTable.vue';
import MessagesSection from '@/Components/Dashboard/MessagesSection.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const validSections = ['care_seekers', 'care_givers', 'bookings', 'messages', 'invoices'];
const initialSection = new URLSearchParams(window.location.search).get('section');
const activeSection = ref(validSections.includes(initialSection) ? initialSection : 'care_seekers');

const titles = {
    care_seekers: ['Care Seekers', 'Manage care seeker accounts and profile information.'],
    care_givers: ['Care Givers', 'Manage care giver accounts and availability.'],
    bookings: ['Bookings', 'Review and manage bookings across every status.'],
    messages: ['Messages', 'Communicate directly with care seekers and care givers.'],
    invoices: ['Invoices', 'Invoice management will be available here.'],
};
const heading = computed(() => titles[activeSection.value]);

const selectSection = (section) => {
    activeSection.value = section;
    const url = new URL(window.location.href);
    url.searchParams.set('section', section);
    window.history.replaceState({}, '', url);
};
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout :active-section="activeSection" @select="selectSection">
        <div class="mb-7">
            <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">{{ heading[0] }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ heading[1] }}</p>
        </div>

        <AdminUserTable v-if="activeSection === 'care_seekers'" type="care_seeker" />
        <AdminUserTable v-else-if="activeSection === 'care_givers'" type="care_giver" />
        <AdminBookingsTable v-else-if="activeSection === 'bookings'" />
        <MessagesSection
            v-else-if="activeSection === 'messages'"
            empty-title="No care users available"
            empty-message="Care seekers and care givers will appear here when they create an account."
        />
        <section v-else class="rounded-2xl border border-slate-200 bg-white px-6 py-20 text-center shadow-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#e8f7f4] text-2xl text-[#117d73]">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
            <h2 class="mt-5 text-xl font-bold">Invoice management</h2>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">This menu is ready. Its invoice features will be added later.</p>
        </section>
    </AdminLayout>
</template>
