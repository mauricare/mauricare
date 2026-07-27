<script setup>
import BookingList from '@/Components/Dashboard/BookingList.vue';
import CareGiverBookingModal from '@/Components/Dashboard/CareGiverBookingModal.vue';
import HelpSupportSection from '@/Components/Dashboard/HelpSupportSection.vue';
import MessagesSection from '@/Components/Dashboard/MessagesSection.vue';
import { statusLabels } from '@/constants/careBookings';
import CareGiverLayout from '@/Layouts/CareGiverLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const page = usePage();
const bookings = ref([]);
const isLoading = ref(false);
const loadError = ref(false);
const showBookingModal = ref(false);
const selectedBooking = ref(null);
const activeSection = ref('dashboard');
const activeBookingFilter = ref('all');

const sections = ['dashboard', 'open', 'mine', 'messages', 'help'];

const sectionTitles = {
    open: 'Open Requests',
    mine: 'My Bookings',
    messages: 'Messages',
    help: 'Help & Support',
};

const myBookingFilters = [
    { value: 'all', label: 'All' },
    { value: 'assigned', label: statusLabels.assigned },
    { value: 'awaiting_payment', label: statusLabels.awaiting_payment },
    { value: 'paid', label: statusLabels.paid },
    { value: 'closed', label: statusLabels.closed },
];

const userId = computed(() => page.props.auth.user.id);
const firstName = computed(() => page.props.auth.user.name?.split(' ')[0] || 'there');

const openBookings = computed(() =>
    bookings.value.filter((booking) => booking.status === 'open' && !booking.care_giver_id),
);

const myBookings = computed(() => bookings.value.filter((booking) => booking.care_giver_id === userId.value));

const filteredMyBookings = computed(() => {
    if (activeBookingFilter.value === 'all') {
        return myBookings.value;
    }

    return myBookings.value.filter((booking) => booking.status === activeBookingFilter.value);
});

const myBookingCounts = computed(() =>
    myBookings.value.reduce(
        (counts, booking) => {
            counts.all += 1;
            counts[booking.status] = (counts[booking.status] || 0) + 1;

            return counts;
        },
        { all: 0 },
    ),
);

const stats = computed(() => [
    { label: 'Open requests', value: openBookings.value.length, icon: 'fa-clipboard-list', section: 'open' },
    {
        label: 'Upcoming visits',
        value: myBookings.value.filter((booking) => booking.status === 'assigned').length,
        icon: 'fa-calendar-check',
        section: 'mine',
    },
    {
        label: 'Awaiting payment',
        value: myBookings.value.filter((booking) => ['awaiting_payment', 'paid'].includes(booking.status)).length,
        icon: 'fa-file-invoice-dollar',
        section: 'mine',
    },
    {
        label: 'Completed',
        value: myBookings.value.filter((booking) => booking.status === 'closed').length,
        icon: 'fa-circle-check',
        section: 'mine',
    },
]);

const loadBookings = async () => {
    isLoading.value = true;
    loadError.value = false;

    try {
        const response = await axios.post('/api/care-bookings/search', {
            search: {
                includes: [{ relation: 'user' }],
                sorts: [
                    { field: 'scheduled_date', direction: 'desc' },
                    { field: 'start_time', direction: 'desc' },
                ],
                limit: 50,
            },
        });

        bookings.value = response.data.data || [];
    } catch {
        loadError.value = true;
    } finally {
        isLoading.value = false;
    }
};

const openBooking = (booking) => {
    selectedBooking.value = booking;
    showBookingModal.value = true;
};

const closeBookingModal = () => {
    showBookingModal.value = false;
    selectedBooking.value = null;
};

const handleBookingUpdated = async () => {
    closeBookingModal();
    await loadBookings();
};

const goToSection = (section) => {
    activeSection.value = section;
    window.scrollTo({ top: 0 });
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);

    if (sections.includes(params.get('section'))) {
        activeSection.value = params.get('section');
    }

    loadBookings();
});
</script>

<template>
    <Head title="Care Giver Dashboard" />

    <CareGiverLayout
        :active="activeSection"
        in-page
        @navigate="goToSection"
    >
        <template #header>
            <div v-if="activeSection === 'dashboard'">
                <p class="text-base font-semibold text-slate-800">Welcome back,</p>
                <h1 class="mt-1 text-3xl font-bold leading-tight text-slate-950 sm:text-4xl">
                    {{ firstName }} <span aria-hidden="true">👋</span>
                </h1>
                <p class="mt-3 text-base text-slate-600">
                    Here are the care requests waiting for you and your upcoming visits.
                </p>
            </div>
            <h1 v-else class="text-2xl font-bold leading-tight text-slate-950 sm:text-3xl">
                {{ sectionTitles[activeSection] }}
            </h1>
        </template>

        <div v-if="activeSection === 'dashboard'" class="mt-8 space-y-6">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <button
                    v-for="stat in stats"
                    :key="stat.label"
                    type="button"
                    class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:border-teal-200 hover:shadow"
                    @click="goToSection(stat.section)"
                >
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700">
                        <i class="fa-solid" :class="stat.icon"></i>
                    </span>
                    <span>
                        <span class="block text-2xl font-bold text-slate-950">{{ stat.value }}</span>
                        <span class="mt-0.5 block text-sm font-semibold text-slate-600">{{ stat.label }}</span>
                    </span>
                </button>
            </div>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-xl font-bold text-slate-950">Open Care Requests</h2>
                    <button
                        type="button"
                        class="text-sm font-semibold text-slate-900"
                        @click="goToSection('open')"
                    >
                        View all <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>
                </div>
                <div class="mt-5">
                    <BookingList
                        :bookings="openBookings.slice(0, 4)"
                        :is-loading="isLoading"
                        :load-error="loadError"
                        show-seeker
                        :show-create="false"
                        empty-message="No open care requests right now."
                        empty-hint="New requests from care seekers will appear here."
                        @select="openBooking"
                        @retry="loadBookings"
                    />
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-xl font-bold text-slate-950">My Bookings</h2>
                    <button
                        type="button"
                        class="text-sm font-semibold text-slate-900"
                        @click="goToSection('mine')"
                    >
                        View all <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>
                </div>
                <div class="mt-5">
                    <BookingList
                        :bookings="myBookings.slice(0, 4)"
                        :is-loading="isLoading"
                        :load-error="loadError"
                        show-seeker
                        :show-create="false"
                        empty-message="You have no bookings yet."
                        empty-hint="Accept an open care request to get started."
                        @select="openBooking"
                        @retry="loadBookings"
                    />
                </div>
            </section>
        </div>

        <div v-else-if="activeSection === 'open'" class="mt-8">
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-950">Open Care Requests</h2>
                <p class="mt-1 text-sm text-slate-600">
                    These requests are not assigned yet. Open one to see the details and accept it.
                </p>
                <div class="mt-5">
                    <BookingList
                        :bookings="openBookings"
                        :is-loading="isLoading"
                        :load-error="loadError"
                        show-seeker
                        :show-create="false"
                        empty-message="No open care requests right now."
                        empty-hint="New requests from care seekers will appear here."
                        @select="openBooking"
                        @retry="loadBookings"
                    />
                </div>
            </section>
        </div>

        <div v-else-if="activeSection === 'mine'" class="mt-8">
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-slate-950">My Bookings</h2>

                <div class="mt-5 flex gap-4 overflow-x-auto border-b border-slate-100 sm:gap-6">
                    <button
                        v-for="filter in myBookingFilters"
                        :key="filter.value"
                        type="button"
                        class="shrink-0 whitespace-nowrap border-b-2 px-2 pb-3 text-sm font-semibold transition"
                        :class="activeBookingFilter === filter.value
                            ? 'border-teal-700 text-teal-700'
                            : 'border-transparent text-slate-600 hover:text-slate-950'"
                        @click="activeBookingFilter = filter.value"
                    >
                        {{ filter.label }}
                        <span v-if="myBookingCounts[filter.value]" class="ml-1 text-xs text-slate-500">
                            ({{ myBookingCounts[filter.value] }})
                        </span>
                    </button>
                </div>

                <div class="mt-4">
                    <BookingList
                        :bookings="filteredMyBookings"
                        :is-loading="isLoading"
                        :load-error="loadError"
                        show-seeker
                        :show-create="false"
                        empty-message="No bookings here yet."
                        empty-hint="Accept an open care request to get started."
                        @select="openBooking"
                        @retry="loadBookings"
                    />
                </div>
            </section>
        </div>

        <div v-else-if="activeSection === 'messages'" class="mt-8">
            <MessagesSection
                empty-title="No conversations yet"
                empty-message="Once you accept a care request, you can message the care seeker here."
            />
        </div>

        <div v-else-if="activeSection === 'help'" class="mt-8">
            <HelpSupportSection />
        </div>

        <CareGiverBookingModal
            :show="showBookingModal"
            :booking="selectedBooking"
            @close="closeBookingModal"
            @updated="handleBookingUpdated"
        />
    </CareGiverLayout>
</template>
