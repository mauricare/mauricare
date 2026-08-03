<script setup>
import AdminBookingsTable from '@/Components/Admin/AdminBookingsTable.vue';
import AdminUserTable from '@/Components/Admin/AdminUserTable.vue';
import AdminInvoices from '@/Components/Admin/AdminInvoices.vue';
import AdminUninvoicedBookings from '@/Components/Admin/AdminUninvoicedBookings.vue';
import AdminStatistics from '@/Components/Admin/AdminStatistics.vue';
import MessagesSection from '@/Components/Dashboard/MessagesSection.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const validSections = ['statistics', 'care_seekers', 'care_givers', 'bookings', 'uninvoiced_bookings', 'messages', 'invoices'];
const initialSection = new URLSearchParams(window.location.search).get('section');
const activeSection = ref(validSections.includes(initialSection) ? initialSection : 'statistics');

const titles = {
    statistics: ['Statistics', 'Track bookings, invoice revenue and user growth by month or year.'],
    care_seekers: ['Care Seekers', 'Manage care seeker accounts and profile information.'],
    care_givers: ['Care Givers', 'Manage care giver accounts and availability.'],
    bookings: ['Bookings', 'Review and manage bookings across every status.'],
    uninvoiced_bookings: ['Uninvoiced Bookings', 'Review closed bookings that have not yet been included in an invoice.'],
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

        <AdminStatistics v-if="activeSection === 'statistics'" />
        <AdminUserTable v-else-if="activeSection === 'care_seekers'" type="care_seeker" />
        <AdminUserTable v-else-if="activeSection === 'care_givers'" type="care_giver" />
        <AdminBookingsTable v-else-if="activeSection === 'bookings'" />
        <AdminUninvoicedBookings v-else-if="activeSection === 'uninvoiced_bookings'" />
        <MessagesSection
            v-else-if="activeSection === 'messages'"
            group-contacts
            empty-title="No care users available"
            empty-message="Care seekers and care givers will appear here when they create an account."
        />
        <AdminInvoices v-else />
    </AdminLayout>
</template>
