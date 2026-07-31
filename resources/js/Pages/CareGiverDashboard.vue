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
const reviewSummary = ref({ average_rating: null, review_count: 0, reviews: [] });
const isLoadingReviews = ref(false);
const reviewsError = ref(false);

const sectionTitles = {
    open: 'Open Requests',
    mine: 'My Bookings',
    reviews: 'My Reviews',
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
const isActiveCareGiver = computed(() => Boolean(page.props.auth.user.care_giver_is_active));
const sections = computed(() => [
    'dashboard',
    ...(isActiveCareGiver.value ? ['open'] : []),
    'mine',
    'reviews',
    'messages',
    'help',
]);
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
    ...(isActiveCareGiver.value ? [
        { label: 'Open requests', value: openBookings.value.length, icon: 'fa-clipboard-list', section: 'open' },
    ] : []),
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

const loadReviews = async () => {
    isLoadingReviews.value = true;
    reviewsError.value = false;

    try {
        const response = await axios.get('/api/reviews/received');
        reviewSummary.value = response.data.data;
    } catch {
        reviewsError.value = true;
    } finally {
        isLoadingReviews.value = false;
    }
};

const formatReviewDate = (value) => {
    const date = new Date(value);

    return Number.isNaN(date.getTime())
        ? ''
        : new Intl.DateTimeFormat('en-GB', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(date);
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

    if (section === 'reviews') {
        loadReviews();
    }
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);

    if (sections.value.includes(params.get('section'))) {
        activeSection.value = params.get('section');
    }

    loadBookings();

    if (activeSection.value === 'reviews') {
        loadReviews();
    }
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
            <div
                v-if="!isActiveCareGiver"
                class="flex items-start gap-4 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-950"
            >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                    <i class="fa-solid fa-clock"></i>
                </span>
                <div>
                    <p class="font-bold">Your care giver account is inactive</p>
                    <p class="mt-1 text-sm text-amber-800">
                        You cannot view or accept new open care requests until your account is activated.
                        Your existing bookings, messages, and reviews remain available.
                    </p>
                </div>
            </div>

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

            <section v-if="isActiveCareGiver" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
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

        <div v-else-if="activeSection === 'reviews'" class="mt-8">
            <div v-if="isLoadingReviews" class="rounded-xl border border-slate-200 bg-white px-6 py-16 text-center text-sm text-slate-500 shadow-sm">
                Loading reviews...
            </div>

            <div v-else-if="reviewsError" class="rounded-xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
                <p class="text-sm text-slate-600">Your reviews could not be loaded.</p>
                <button
                    type="button"
                    class="mt-3 rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800"
                    @click="loadReviews"
                >
                    Try again
                </button>
            </div>

            <template v-else>
                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-slate-950">Rating summary</h2>
                            <p class="mt-1 text-sm text-slate-600">Feedback received from completed care bookings.</p>
                        </div>
                        <div class="flex items-center gap-4 rounded-xl bg-amber-50 px-5 py-4">
                            <span class="text-3xl font-bold text-slate-950">
                                {{ reviewSummary.average_rating ?? '—' }}
                            </span>
                            <div>
                                <div class="flex gap-0.5" :aria-label="reviewSummary.average_rating ? `${reviewSummary.average_rating} out of 5 stars` : 'No ratings yet'">
                                    <i
                                        v-for="star in 5"
                                        :key="star"
                                        class="fa-solid fa-star"
                                        :class="reviewSummary.average_rating && star <= Math.round(reviewSummary.average_rating)
                                            ? 'text-amber-400'
                                            : 'text-slate-300'"
                                    ></i>
                                </div>
                                <p class="mt-1 text-xs text-slate-600">
                                    {{ reviewSummary.review_count }} {{ reviewSummary.review_count === 1 ? 'review' : 'reviews' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-slate-950">Reviews received</h2>

                    <div
                        v-if="!reviewSummary.reviews.length"
                        class="mt-5 rounded-xl border border-slate-100 bg-slate-50 px-6 py-12 text-center"
                    >
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                            <i class="fa-regular fa-star text-xl"></i>
                        </span>
                        <p class="mt-4 font-semibold text-slate-950">No reviews yet</p>
                        <p class="mt-1 text-sm text-slate-600">Reviews will appear here after care seekers rate closed bookings.</p>
                    </div>

                    <div v-else class="mt-5 grid gap-4 lg:grid-cols-2">
                        <article
                            v-for="review in reviewSummary.reviews"
                            :key="review.id"
                            class="rounded-xl border border-slate-200 p-5"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <img
                                        v-if="review.reviewer_avatar_url"
                                        :src="review.reviewer_avatar_url"
                                        :alt="`${review.reviewer_name} profile photo`"
                                        class="h-11 w-11 shrink-0 rounded-full object-cover"
                                    />
                                    <span
                                        v-else
                                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-teal-100 font-bold text-teal-800"
                                    >
                                        {{ review.reviewer_name.charAt(0) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-950">{{ review.reviewer_name }}</p>
                                        <div class="mt-1 flex gap-0.5" :aria-label="`${review.rating} out of 5 stars`">
                                            <i
                                                v-for="star in 5"
                                                :key="star"
                                                class="fa-solid fa-star text-sm"
                                                :class="star <= review.rating ? 'text-amber-400' : 'text-slate-300'"
                                            ></i>
                                        </div>
                                    </div>
                                </div>
                                <time class="shrink-0 text-xs text-slate-500">{{ formatReviewDate(review.created_at) }}</time>
                            </div>
                            <p class="mt-4 whitespace-pre-line text-sm leading-6 text-slate-700">
                                {{ review.comment || 'No comment provided.' }}
                            </p>
                        </article>
                    </div>
                </section>
            </template>
        </div>

        <div v-else-if="activeSection === 'messages'" class="mt-8">
            <MessagesSection
                group-contacts
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
