<script setup>
import BookingList from '@/Components/Dashboard/BookingList.vue';
import BookingModal from '@/Components/Dashboard/BookingModal.vue';
import HelpSupportSection from '@/Components/Dashboard/HelpSupportSection.vue';
import SectionEmptyState from '@/Components/Dashboard/SectionEmptyState.vue';
import { bookingFilters, careTypes, statusClasses } from '@/constants/careBookings';
import { formatDateParts, formatOption, formatStatus, formatTime, providerName } from '@/utils/bookingFormat';
import CareSeekerLayout from '@/Layouts/CareSeekerLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const page = usePage();
const bookings = ref([]);
const isLoading = ref(false);
const loadError = ref(false);
const showBookingModal = ref(false);
const editingBooking = ref(null);
const activeBookingFilter = ref('all');
const activeSection = ref('dashboard');

const sections = ['dashboard', 'bookings', 'providers', 'invoices', 'messages', 'help'];

const quickLinks = [
    { label: 'View All Bookings', icon: 'fa-calendar-days', section: 'bookings' },
    { label: 'Invoices & Payments', icon: 'fa-file-invoice', section: 'invoices' },
    { label: 'My Care Providers', icon: 'fa-user-group', section: 'providers' },
    { label: 'Help & Support', icon: 'fa-circle-question', section: 'help' },
];

const sectionTitles = {
    bookings: 'My Bookings',
    providers: 'My Care Providers',
    invoices: 'Invoices & Payments',
    messages: 'Messages',
    help: 'Help & Support',
};

const filteredBookings = computed(() => {
    if (activeBookingFilter.value === 'all') {
        return bookings.value;
    }

    return bookings.value.filter((booking) => booking.status === activeBookingFilter.value);
});

const recentBookings = computed(() => bookings.value.slice(0, 4));

const bookingCounts = computed(() =>
    bookings.value.reduce(
        (counts, booking) => {
            counts.all += 1;
            counts[booking.status] = (counts[booking.status] || 0) + 1;

            return counts;
        },
        { all: 0 },
    ),
);

const upcomingBooking = computed(() => {
    const today = new Date().toISOString().slice(0, 10);

    return bookings.value
        .filter((booking) => ['pending', 'confirmed'].includes(booking.status) && booking.scheduled_date >= today)
        .sort((first, second) =>
            `${first.scheduled_date} ${first.start_time}`.localeCompare(`${second.scheduled_date} ${second.start_time}`),
        )[0] || null;
});

const firstName = computed(() => page.props.auth.user.name?.split(' ')[0] || 'there');

const loadBookings = async () => {
    isLoading.value = true;
    loadError.value = false;

    try {
        const response = await axios.post('/api/care-bookings/search', {
            search: {
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

const openCreateBooking = () => {
    editingBooking.value = null;
    showBookingModal.value = true;
};

const openBooking = (booking) => {
    editingBooking.value = booking;
    showBookingModal.value = true;
};

const closeBookingModal = () => {
    showBookingModal.value = false;
    editingBooking.value = null;
};

const handleBookingSaved = async () => {
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

    if (params.get('create') === '1') {
        openCreateBooking();
    }

    loadBookings();
});
</script>

<template>
    <Head title="Care Seeker Dashboard" />

    <CareSeekerLayout
        :active="activeSection"
        in-page
        @navigate="goToSection"
        @create="openCreateBooking"
    >
        <template #header>
            <div v-if="activeSection === 'dashboard'">
                <p class="text-base font-semibold text-slate-800">Welcome back,</p>
                <h1 class="mt-1 text-4xl font-bold leading-tight text-slate-950">
                    {{ firstName }} <span aria-hidden="true">👋</span>
                </h1>
                <p class="mt-3 text-base text-slate-600">
                    We're here to make care simple and reliable for you and your loved ones.
                </p>
            </div>
            <h1 v-else class="text-3xl font-bold leading-tight text-slate-950">
                {{ sectionTitles[activeSection] }}
            </h1>
        </template>

        <div v-if="activeSection === 'dashboard'" class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1fr)_350px]">
            <div class="space-y-6">
                <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(320px,0.95fr)]">
                    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="grid min-h-[300px] gap-4 p-8 md:grid-cols-[minmax(260px,0.9fr)_minmax(260px,1fr)]">
                            <div class="flex flex-col justify-center">
                                <button
                                    type="button"
                                    class="mb-6 flex h-12 w-12 items-center justify-center rounded-full bg-teal-700 text-white shadow-lg shadow-teal-100 transition hover:bg-teal-800"
                                    @click="openCreateBooking"
                                >
                                    <i class="fa-solid fa-plus text-xl"></i>
                                </button>
                                <h2 class="text-2xl font-bold leading-tight text-slate-950">
                                    Create a New<br />Care Request
                                </h2>
                                <p class="mt-4 max-w-xs text-base leading-7 text-slate-600">
                                    Tell us what care you or your loved one needs.
                                </p>
                                <button
                                    type="button"
                                    class="mt-6 inline-flex w-fit items-center rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800"
                                    @click="openCreateBooking"
                                >
                                    Create Request
                                    <i class="fa-solid fa-arrow-right ml-3"></i>
                                </button>
                            </div>

                            <div class="relative hidden items-end justify-center md:flex">
                                <div class="absolute inset-x-0 bottom-0 h-64 rounded-t-[5rem] bg-teal-50"></div>
                                <img
                                    src="/images/mauricare-family.png"
                                    alt=""
                                    class="relative z-10 max-h-72 w-full object-contain object-bottom"
                                />
                            </div>
                        </div>
                    </section>

                    <section class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
                        <h2 class="text-xl font-bold text-slate-950">How it works</h2>
                        <div class="mt-6 space-y-5">
                            <div class="flex gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700">
                                    <i class="fa-regular fa-calendar-plus"></i>
                                </span>
                                <div>
                                    <p class="font-bold text-slate-950">1. Create a care request</p>
                                    <p class="mt-1 text-sm text-slate-600">Tell us your needs</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700">
                                    <i class="fa-solid fa-user-nurse"></i>
                                </span>
                                <div>
                                    <p class="font-bold text-slate-950">2. Providers respond</p>
                                    <p class="mt-1 text-sm text-slate-600">We notify available providers</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700">
                                    <i class="fa-regular fa-circle-check"></i>
                                </span>
                                <div>
                                    <p class="font-bold text-slate-950">3. Choose & confirm</p>
                                    <p class="mt-1 text-sm text-slate-600">Select the best match</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700">
                                    <i class="fa-solid fa-house-medical"></i>
                                </span>
                                <div>
                                    <p class="font-bold text-slate-950">4. Care at home</p>
                                    <p class="mt-1 text-sm text-slate-600">Receive care with peace of mind</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-xl font-bold text-slate-950">My Bookings</h2>
                        <button
                            type="button"
                            class="text-sm font-semibold text-slate-900"
                            @click="goToSection('bookings')"
                        >
                            View all <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </div>

                    <div class="mt-5">
                        <BookingList
                            :bookings="recentBookings"
                            :is-loading="isLoading"
                            :load-error="loadError"
                            @select="openBooking"
                            @retry="loadBookings"
                            @create="openCreateBooking"
                        />
                    </div>
                </section>
            </div>

            <aside class="space-y-5">
                <section v-if="upcomingBooking" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-slate-950">Upcoming Booking</h2>
                    <div class="mt-5 rounded-3xl border border-slate-100 p-5">
                        <div class="flex items-start gap-4">
                            <span class="flex h-16 w-14 flex-col items-center justify-center rounded-lg bg-slate-50">
                                <span class="text-2xl font-bold leading-none text-slate-950">{{ formatDateParts(upcomingBooking.scheduled_date).day }}</span>
                                <span class="mt-1 text-xs font-semibold text-slate-600">{{ formatDateParts(upcomingBooking.scheduled_date).month }}</span>
                            </span>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-base font-bold leading-snug text-slate-950">
                                    {{ formatOption(careTypes, upcomingBooking.care_type) }}
                                </h3>
                                <p class="mt-1.5 text-sm text-slate-600">
                                    {{ formatDateParts(upcomingBooking.scheduled_date).full }}
                                    <span class="mx-1">•</span>
                                    {{ formatTime(upcomingBooking.start_time) }}
                                </p>
                                <span
                                    class="mt-2.5 inline-flex rounded-md px-2.5 py-1 text-xs font-semibold"
                                    :class="statusClasses[upcomingBooking.status] || statusClasses.pending"
                                >
                                    {{ formatStatus(upcomingBooking.status) }}
                                </span>
                                <p class="mt-4 flex items-center gap-3 text-sm text-slate-700">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-teal-100 font-bold text-teal-800">
                                        {{ providerName(upcomingBooking).charAt(0) }}
                                    </span>
                                    with {{ providerName(upcomingBooking) }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="mt-5 w-full rounded-md bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 transition hover:bg-slate-100"
                            @click="openBooking(upcomingBooking)"
                        >
                            View Booking Details <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-slate-950">Quick Links</h2>
                    <div class="mt-4 divide-y divide-slate-100">
                        <button
                            v-for="link in quickLinks"
                            :key="link.label"
                            type="button"
                            class="flex w-full items-center gap-4 py-4 text-left text-sm font-semibold text-slate-800"
                            @click="goToSection(link.section)"
                        >
                            <i class="fa-regular w-5 text-center text-slate-500" :class="link.icon"></i>
                            <span>{{ link.label }}</span>
                            <i class="fa-solid fa-chevron-right ml-auto text-xs text-slate-500"></i>
                        </button>
                    </div>
                </section>

                <section class="overflow-hidden rounded-xl bg-teal-700 p-6 text-white shadow-lg shadow-teal-100">
                    <h2 class="text-lg font-bold">Need help?</h2>
                    <p class="mt-2 text-sm leading-6 text-teal-50">Our support team is here to assist you.</p>
                    <button
                        type="button"
                        class="mt-4 rounded-md bg-white px-4 py-2 text-sm font-semibold text-teal-800 shadow-sm"
                        @click="goToSection('help')"
                    >
                        Contact Support
                    </button>
                </section>
            </aside>
        </div>

        <div v-else-if="activeSection === 'bookings'" class="mt-8">
            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-xl font-bold text-slate-950">All Bookings</h2>
                    <button
                        type="button"
                        class="rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800"
                        @click="openCreateBooking"
                    >
                        <i class="fa-solid fa-plus mr-2"></i> New Request
                    </button>
                </div>

                <div class="mt-5 flex flex-wrap gap-6 border-b border-slate-100">
                    <button
                        v-for="filter in bookingFilters"
                        :key="filter.value"
                        type="button"
                        class="border-b-2 px-2 pb-3 text-sm font-semibold transition"
                        :class="activeBookingFilter === filter.value
                            ? 'border-teal-700 text-teal-700'
                            : 'border-transparent text-slate-600 hover:text-slate-950'"
                        @click="activeBookingFilter = filter.value"
                    >
                        {{ filter.label }}
                        <span v-if="bookingCounts[filter.value]" class="ml-1 text-xs text-slate-500">
                            ({{ bookingCounts[filter.value] }})
                        </span>
                    </button>
                </div>

                <div class="mt-4">
                    <BookingList
                        :bookings="filteredBookings"
                        :is-loading="isLoading"
                        :load-error="loadError"
                        :empty-message="activeBookingFilter === 'all'
                            ? 'You have no bookings yet.'
                            : `No ${activeBookingFilter} bookings.`"
                        @select="openBooking"
                        @retry="loadBookings"
                        @create="openCreateBooking"
                    />
                </div>
            </section>
        </div>

        <div v-else-if="activeSection === 'providers'" class="mt-8">
            <SectionEmptyState
                icon="fa-user-group"
                title="No care providers yet"
                message="Once a provider is assigned to one of your bookings, they'll appear here so you can easily rebook with people you trust."
                action-label="Create a Care Request"
                @action="openCreateBooking"
            />
        </div>

        <div v-else-if="activeSection === 'invoices'" class="mt-8">
            <SectionEmptyState
                icon="fa-file-invoice"
                title="No invoices yet"
                message="Invoices and payment receipts will appear here after your bookings are completed. You currently have nothing due."
                action-label="View My Bookings"
                @action="goToSection('bookings')"
            />
        </div>

        <div v-else-if="activeSection === 'messages'" class="mt-8">
            <SectionEmptyState
                icon="fa-message"
                title="No messages yet"
                message="When you're connected with a care provider, your conversations will appear here. Need help in the meantime? Our support team is one click away."
                action-label="Contact Support"
                @action="goToSection('help')"
            />
        </div>

        <div v-else-if="activeSection === 'help'" class="mt-8">
            <HelpSupportSection />
        </div>

        <BookingModal
            :show="showBookingModal"
            :booking="editingBooking"
            @close="closeBookingModal"
            @saved="handleBookingSaved"
        />
    </CareSeekerLayout>
</template>
