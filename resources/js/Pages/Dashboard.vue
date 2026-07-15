<script setup>
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, reactive, ref } from 'vue';

const page = usePage();
const bookings = ref([]);
const isLoading = ref(false);
const isSaving = ref(false);
const showBookingModal = ref(false);
const editingBooking = ref(null);
const formErrors = ref({});
const activeBookingFilter = ref('all');

const bookingForm = reactive({
    scheduled_date: '',
    start_time: '09:00',
    duration_hours: 1,
    care_type: '',
    preferred_carer_type: '',
    description: '',
});

const careTypes = [
    { value: 'personal_care', label: 'Personal Care Assistance' },
    { value: 'nursing_care', label: 'Nursing Care' },
    { value: 'physiotherapy', label: 'Physiotherapy Session' },
    { value: 'post_hospital_recovery', label: 'Post-Hospital Recovery' },
    { value: 'respite_care', label: 'Respite Care' },
    { value: 'companionship', label: 'Companionship' },
    { value: 'wound_care', label: 'Wound Care' },
    { value: 'home_icu_support', label: 'Home ICU Support' },
    { value: 'other', label: 'Other Care' },
];

const carerTypes = [
    { value: 'doctor', label: 'Doctor' },
    { value: 'nurse', label: 'Nurse' },
    { value: 'carers', label: 'Carer' },
    { value: 'physiotherapist', label: 'Physiotherapist' },
    { value: 'other', label: 'Other' },
];

const bookingFilters = [
    { value: 'all', label: 'All' },
    { value: 'pending', label: 'Upcoming' },
    { value: 'confirmed', label: 'In Progress' },
    { value: 'completed', label: 'Completed' },
    { value: 'cancelled', label: 'Cancelled' },
];

const navItems = [
    { label: 'Dashboard', icon: 'fa-house', active: true, action: null },
    { label: 'My Bookings', icon: 'fa-calendar-days', active: false, action: null },
    { label: 'New Care Request', icon: 'fa-circle-plus', active: false, action: 'create' },
    { label: 'My Care Providers', icon: 'fa-user-group', active: false, action: null },
    { label: 'Invoices & Payments', icon: 'fa-file-invoice', active: false, action: null },
    { label: 'Messages', icon: 'fa-message', active: false, badge: '2', action: null },
    { label: 'Profile Settings', icon: 'fa-gear', active: false, href: route('profile.edit') },
    { label: 'Help & Support', icon: 'fa-circle-question', active: false, action: null },
];

const quickLinks = [
    { label: 'View All Bookings', icon: 'fa-calendar-days' },
    { label: 'Invoices & Payments', icon: 'fa-file-invoice' },
    { label: 'My Care Providers', icon: 'fa-user-group' },
    { label: 'Help & Support', icon: 'fa-circle-question' },
];

const invoices = [
    { id: 'INV-2025-0062', date: '31 May 2025', amount: 'Rs 2,160' },
    { id: 'INV-2025-0051', date: '30 Apr 2025', amount: 'Rs 1,944' },
];

const statusClasses = {
    pending: 'bg-amber-50 text-amber-700',
    confirmed: 'bg-teal-50 text-teal-700',
    cancelled: 'bg-rose-50 text-rose-700',
    completed: 'bg-emerald-50 text-emerald-700',
};

const fallbackBookings = [
    {
        id: 'sample-1',
        scheduled_date: '2025-06-18',
        start_time: '10:00',
        duration_hours: 1,
        care_type: 'physiotherapy',
        preferred_carer_type: 'physiotherapist',
        status: 'confirmed',
        provider: 'Kavishnee R.',
    },
    {
        id: 'sample-2',
        scheduled_date: '2025-06-21',
        start_time: '09:00',
        duration_hours: 1,
        care_type: 'companionship',
        preferred_carer_type: 'carers',
        status: 'pending',
        provider: 'Shabana B.',
    },
    {
        id: 'sample-3',
        scheduled_date: '2025-06-14',
        start_time: '11:00',
        duration_hours: 1,
        care_type: 'nursing_care',
        preferred_carer_type: 'nurse',
        status: 'completed',
        provider: 'Completed',
    },
    {
        id: 'sample-4',
        scheduled_date: '2025-06-10',
        start_time: '14:00',
        duration_hours: 1,
        care_type: 'personal_care',
        preferred_carer_type: 'carers',
        status: 'completed',
        provider: 'Completed',
    },
];

const displayBookings = computed(() => (bookings.value.length ? bookings.value : fallbackBookings));

const filteredBookings = computed(() => {
    if (activeBookingFilter.value === 'all') {
        return displayBookings.value;
    }

    return displayBookings.value.filter((booking) => booking.status === activeBookingFilter.value);
});

const upcomingBooking = computed(() =>
    displayBookings.value.find((booking) => !['cancelled', 'completed'].includes(booking.status))
        || displayBookings.value[0],
);

const firstName = computed(() => page.props.auth.user.name?.split(' ')[0] || 'there');

const formatOption = (options, value) =>
    options.find((option) => option.value === value)?.label || value;

const formatStatus = (status) =>
    status
        ? status.replace('_', ' ').replace(/^\w/, (character) => character.toUpperCase())
        : 'Pending';

const formatDateParts = (dateValue) => {
    const date = new Date(`${dateValue}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        return { day: '--', month: '---', full: dateValue };
    }

    return {
        day: new Intl.DateTimeFormat('en-GB', { day: '2-digit' }).format(date),
        month: new Intl.DateTimeFormat('en-GB', { month: 'short' }).format(date).toUpperCase(),
        full: new Intl.DateTimeFormat('en-GB', {
            weekday: 'short',
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }).format(date),
    };
};

const formatTime = (timeValue) => {
    if (!timeValue) {
        return '';
    }

    const [hours, minutes] = timeValue.split(':');
    const date = new Date();
    date.setHours(Number(hours), Number(minutes), 0, 0);

    return new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
    }).format(date);
};

const providerName = (booking) => booking.provider || 'Provider pending';

const resetForm = () => {
    editingBooking.value = null;
    formErrors.value = {};
    Object.assign(bookingForm, {
        scheduled_date: '',
        start_time: '09:00',
        duration_hours: 1,
        care_type: '',
        preferred_carer_type: '',
        description: '',
    });
};

const openCreateBooking = () => {
    resetForm();
    showBookingModal.value = true;
};

const openEditBooking = (booking) => {
    if (String(booking.id).startsWith('sample-')) {
        openCreateBooking();
        return;
    }

    editingBooking.value = booking;
    formErrors.value = {};
    Object.assign(bookingForm, {
        scheduled_date: booking.scheduled_date,
        start_time: booking.start_time?.slice(0, 5) || '09:00',
        duration_hours: booking.duration_hours || 1,
        care_type: booking.care_type,
        preferred_carer_type: booking.preferred_carer_type,
        description: booking.description,
    });
    showBookingModal.value = true;
};

const closeBookingModal = () => {
    showBookingModal.value = false;
    resetForm();
};

const loadBookings = async () => {
    isLoading.value = true;

    try {
        const response = await axios.post('/api/care-bookings/search', {
            search: {
                sorts: [
                    { field: 'scheduled_date', direction: 'desc' },
                    { field: 'start_time', direction: 'desc' },
                ],
                limit: 25,
            },
        });

        bookings.value = response.data.data || [];
    } finally {
        isLoading.value = false;
    }
};

const normalizeErrors = (errors) => {
    return Object.entries(errors || {}).reduce((mappedErrors, [key, messages]) => {
        const field = key.split('.').pop();
        mappedErrors[field] = Array.isArray(messages) ? messages[0] : messages;

        return mappedErrors;
    }, {});
};

const saveBooking = async () => {
    isSaving.value = true;
    formErrors.value = {};

    const payload = {
        operation: editingBooking.value ? 'update' : 'create',
        attributes: { ...bookingForm },
    };

    if (editingBooking.value) {
        payload.key = editingBooking.value.id;
    }

    try {
        await axios.post('/api/care-bookings/mutate', {
            mutate: [payload],
        });

        closeBookingModal();
        await loadBookings();
    } catch (error) {
        if (error.response?.status === 422) {
            formErrors.value = normalizeErrors(error.response.data.errors);
        }
    } finally {
        isSaving.value = false;
    }
};

const handleNavAction = (item) => {
    if (item.action === 'create') {
        openCreateBooking();
    }
};

onMounted(loadBookings);
</script>

<template>
    <Head title="Care Seeker Dashboard" />

    <div class="min-h-screen bg-[#f7fafc] text-slate-950">
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 border-r border-slate-100 bg-white px-4 py-8 lg:flex lg:flex-col">
            <Link :href="route('dashboard')" class="flex items-center px-3">
                <img
                    src="/images/mauricare-home-care-services-logo.png"
                    alt="Mauricare"
                    class="h-14 w-auto object-contain"
                />
            </Link>

            <nav class="mt-12 space-y-2">
                <component
                    :is="item.href ? Link : 'button'"
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    type="button"
                    class="flex w-full items-center rounded-xl px-5 py-4 text-left text-sm font-semibold transition"
                    :class="item.active
                        ? 'bg-teal-50 text-slate-950 shadow-sm shadow-teal-100'
                        : 'text-slate-700 hover:bg-slate-50'"
                    @click="handleNavAction(item)"
                >
                    <i class="fa-solid mr-4 w-5 text-center text-base" :class="[item.icon, item.active ? 'text-teal-700' : 'text-slate-500']"></i>
                    <span>{{ item.label }}</span>
                    <span
                        v-if="item.badge"
                        class="ml-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-bold text-white"
                    >
                        {{ item.badge }}
                    </span>
                </component>
            </nav>

            <div class="mt-auto">
                <div class="mb-6 border-t border-slate-200 pt-5">
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

                <div class="overflow-hidden rounded-xl bg-teal-700 p-5 text-white shadow-lg shadow-teal-100">
                    <p class="text-base font-semibold">Need help?</p>
                    <p class="mt-2 text-sm leading-6 text-teal-50">Our support team is here to assist you.</p>
                    <button
                        type="button"
                        class="mt-4 rounded-md bg-white px-4 py-2 text-sm font-semibold text-teal-800 shadow-sm"
                    >
                        Contact Support
                    </button>
                </div>
            </div>
        </aside>

        <main class="lg:pl-72">
            <div class="mx-auto max-w-[1540px] px-5 py-6 sm:px-8 lg:px-12">
                <header class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-base font-semibold text-slate-800">Welcome back,</p>
                        <h1 class="mt-1 text-4xl font-bold leading-tight text-slate-950">
                            {{ firstName }} <span aria-hidden="true">👋</span>
                        </h1>
                        <p class="mt-3 text-base text-slate-600">
                            We're here to make care simple and reliable for you and your loved ones.
                        </p>
                    </div>

                    <div class="flex items-center gap-5">
                        <button
                            type="button"
                            class="hidden h-11 w-11 items-center justify-center rounded-full text-slate-600 hover:bg-white sm:inline-flex"
                        >
                            <i class="fa-regular fa-bell text-xl"></i>
                        </button>
                        <Link :href="route('profile.edit')" class="flex items-center gap-3">
                            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-teal-100 text-lg font-bold text-teal-800">
                                {{ firstName.charAt(0) }}
                            </span>
                            <i class="fa-solid fa-chevron-down text-sm text-slate-500"></i>
                        </Link>
                    </div>
                </header>

                <div class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,1fr)_350px]">
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
                                <button type="button" class="text-sm font-semibold text-slate-900">
                                    View all <i class="fa-solid fa-arrow-right ml-2"></i>
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
                                </button>
                            </div>

                            <div class="mt-0 overflow-hidden rounded-xl border border-slate-100">
                                <div v-if="isLoading" class="px-6 py-10 text-center text-sm text-slate-500">
                                    Loading bookings...
                                </div>
                                <button
                                    v-for="booking in filteredBookings"
                                    v-else
                                    :key="booking.id"
                                    type="button"
                                    class="grid w-full grid-cols-[72px_minmax(0,1fr)_auto_24px] items-center gap-4 border-b border-slate-100 px-4 py-4 text-left last:border-b-0 transition hover:bg-slate-50"
                                    @click="openEditBooking(booking)"
                                >
                                    <span class="flex h-16 w-16 flex-col items-center justify-center rounded-lg bg-slate-50">
                                        <span class="text-2xl font-bold leading-none text-slate-950">{{ formatDateParts(booking.scheduled_date).day }}</span>
                                        <span class="mt-1 text-xs font-semibold text-slate-600">{{ formatDateParts(booking.scheduled_date).month }}</span>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-base font-bold text-slate-950">
                                            {{ formatOption(careTypes, booking.care_type) }}
                                        </span>
                                        <span class="mt-1 block text-sm text-slate-600">
                                            {{ formatDateParts(booking.scheduled_date).full }}
                                            <span class="mx-2">•</span>
                                            {{ formatTime(booking.start_time) }}
                                        </span>
                                        <span class="mt-1 flex items-center gap-2 text-sm text-slate-700">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-teal-100 text-xs font-bold text-teal-800">
                                                {{ providerName(booking).charAt(0) }}
                                            </span>
                                            {{ providerName(booking) }}
                                        </span>
                                    </span>
                                    <span
                                        class="hidden rounded-md px-3 py-1.5 text-sm font-semibold sm:inline-flex"
                                        :class="statusClasses[booking.status] || statusClasses.pending"
                                    >
                                        {{ formatStatus(booking.status) }}
                                    </span>
                                    <i class="fa-solid fa-chevron-right text-slate-500"></i>
                                </button>
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
                                        <div class="flex items-start justify-between gap-2">
                                            <h3 class="truncate font-bold text-slate-950">
                                                {{ formatOption(careTypes, upcomingBooking.care_type) }}
                                            </h3>
                                            <span
                                                class="rounded-md px-2 py-1 text-xs font-semibold"
                                                :class="statusClasses[upcomingBooking.status] || statusClasses.pending"
                                            >
                                                {{ formatStatus(upcomingBooking.status) }}
                                            </span>
                                        </div>
                                        <p class="mt-2 text-sm text-slate-600">
                                            {{ formatDateParts(upcomingBooking.scheduled_date).full }}
                                            <span class="mx-1">•</span>
                                            {{ formatTime(upcomingBooking.start_time) }}
                                        </p>
                                        <p class="mt-5 flex items-center gap-3 text-sm text-slate-700">
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
                                    @click="openEditBooking(upcomingBooking)"
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
                                >
                                    <i class="fa-regular w-5 text-center text-slate-500" :class="link.icon"></i>
                                    <span>{{ link.label }}</span>
                                    <i class="fa-solid fa-chevron-right ml-auto text-xs text-slate-500"></i>
                                </button>
                            </div>
                        </section>

                        <section class="relative overflow-hidden rounded-xl bg-teal-50 p-6 shadow-sm">
                            <div class="max-w-[220px]">
                                <h2 class="text-lg font-bold text-slate-950">Refer & Earn</h2>
                                <p class="mt-2 text-sm leading-6 text-slate-700">
                                    Refer a friend and get up to Rs 300 off on your next booking!
                                </p>
                                <button type="button" class="mt-4 text-sm font-bold text-teal-700">Refer Now</button>
                            </div>
                            <div class="absolute bottom-5 right-5 flex h-24 w-24 items-center justify-center rounded-full bg-white/60 text-5xl">
                                🎁
                            </div>
                        </section>

                        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold text-slate-950">Recent Invoices</h2>
                                <button type="button" class="text-sm font-semibold text-slate-900">
                                    View all <i class="fa-solid fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                            <div class="mt-5 divide-y divide-slate-100">
                                <div v-for="invoice in invoices" :key="invoice.id" class="flex items-center justify-between py-4">
                                    <div>
                                        <p class="font-bold text-slate-950">{{ invoice.id }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ invoice.date }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-slate-950">{{ invoice.amount }}</p>
                                        <span class="mt-2 inline-flex rounded-md bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                            Paid
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </main>

        <Modal :show="showBookingModal" max-width="2xl" @close="closeBookingModal">
            <form class="p-6" @submit.prevent="saveBooking">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">
                            {{ editingBooking ? 'Edit booking' : 'Create booking' }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-600">
                            Describe the care needed, including health state and allergies if any.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-md p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        @click="closeBookingModal"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Date</span>
                        <input
                            v-model="bookingForm.scheduled_date"
                            type="date"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            required
                        />
                        <span v-if="formErrors.scheduled_date" class="mt-1 block text-sm text-rose-600">
                            {{ formErrors.scheduled_date }}
                        </span>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Time period</span>
                        <input
                            v-model="bookingForm.start_time"
                            type="time"
                            step="3600"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            required
                        />
                        <span class="mt-1 block text-xs text-slate-500">Each booking is for 1 hour.</span>
                        <span v-if="formErrors.start_time" class="mt-1 block text-sm text-rose-600">
                            {{ formErrors.start_time }}
                        </span>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Type of care</span>
                        <select
                            v-model="bookingForm.care_type"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            required
                        >
                            <option value="" disabled>Select care type</option>
                            <option v-for="type in careTypes" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                        <span v-if="formErrors.care_type" class="mt-1 block text-sm text-rose-600">
                            {{ formErrors.care_type }}
                        </span>
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Type of carer</span>
                        <select
                            v-model="bookingForm.preferred_carer_type"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            required
                        >
                            <option value="" disabled>Select carer type</option>
                            <option v-for="type in carerTypes" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                        <span v-if="formErrors.preferred_carer_type" class="mt-1 block text-sm text-rose-600">
                            {{ formErrors.preferred_carer_type }}
                        </span>
                    </label>
                </div>

                <label class="mt-4 block">
                    <span class="text-sm font-medium text-slate-700">Description of care</span>
                    <textarea
                        v-model="bookingForm.description"
                        rows="5"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                        placeholder="Include health state, current needs, allergies, mobility concerns, or any special instructions."
                        required
                    ></textarea>
                    <span v-if="formErrors.description" class="mt-1 block text-sm text-rose-600">
                        {{ formErrors.description }}
                    </span>
                </label>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="closeBookingModal">
                        Close
                    </SecondaryButton>
                    <PrimaryButton type="submit" :disabled="isSaving" :class="{ 'opacity-75': isSaving }">
                        {{ isSaving ? 'Saving...' : 'Save booking' }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    </div>
</template>
