<script setup>
import { computed, onMounted, reactive, ref } from 'vue';

const today = new Date();
const localDateValue = (value) => [
    value.getFullYear(),
    String(value.getMonth() + 1).padStart(2, '0'),
    String(value.getDate()).padStart(2, '0'),
].join('-');

const period = reactive({
    start: localDateValue(new Date(today.getFullYear(), today.getMonth(), 1)),
    end: localDateValue(new Date(today.getFullYear(), today.getMonth() + 1, 0)),
});
const groups = ref([]);
const summary = ref({ care_givers_count: 0, bookings_count: 0, booking_total: '0.00' });
const search = ref('');
const isLoading = ref(false);
const error = ref('');

const money = (value) => new Intl.NumberFormat('en-MU', {
    style: 'currency',
    currency: 'MUR',
    minimumFractionDigits: 2,
}).format(Number(value || 0));

const date = (value) => new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium' })
    .format(new Date(`${value}T00:00:00`));

const filteredGroups = computed(() => {
    const query = search.value.trim().toLocaleLowerCase();
    if (!query) return groups.value;

    return groups.value.filter((group) =>
        group.care_giver.name.toLocaleLowerCase().includes(query)
        || group.care_giver.email.toLocaleLowerCase().includes(query)
        || group.bookings.some((booking) =>
            String(booking.id).includes(query)
            || booking.care_seeker.name.toLocaleLowerCase().includes(query),
        ),
    );
});

const loadBookings = async () => {
    error.value = '';
    isLoading.value = true;

    try {
        const response = await axios.get('/api/admin/uninvoiced-bookings', {
            params: {
                period_start: period.start,
                period_end: period.end,
            },
        });
        groups.value = response.data.data || [];
        summary.value = response.data.summary;
    } catch (requestError) {
        error.value = requestError.response?.data?.message || 'Uninvoiced bookings could not be loaded.';
    } finally {
        isLoading.value = false;
    }
};

onMounted(loadBookings);
</script>

<template>
    <div class="space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <form class="flex flex-col gap-4 md:flex-row md:items-end" @submit.prevent="loadBookings">
                <label class="block flex-1 text-sm font-semibold text-slate-700">
                    Start date
                    <input v-model="period.start" type="date" class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500" required />
                </label>
                <label class="block flex-1 text-sm font-semibold text-slate-700">
                    End date
                    <input v-model="period.end" type="date" :min="period.start" class="mt-1 block w-full rounded-md border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500" required />
                </label>
                <button type="submit" :disabled="isLoading" class="rounded-md bg-[#117d73] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#0d665e] disabled:opacity-60">
                    <i class="fa-solid mr-2" :class="isLoading ? 'fa-spinner fa-spin' : 'fa-filter'"></i>
                    {{ isLoading ? 'Loading…' : 'Apply period' }}
                </button>
            </form>
            <p v-if="error" class="mt-4 rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ error }}</p>
        </section>

        <section class="grid gap-4 sm:grid-cols-3">
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Care givers</p>
                <p class="mt-2 text-3xl font-black text-slate-950">{{ summary.care_givers_count }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Closed bookings</p>
                <p class="mt-2 text-3xl font-black text-slate-950">{{ summary.bookings_count }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Combined paid amount</p>
                <p class="mt-2 text-3xl font-black text-teal-700">{{ money(summary.booking_total) }}</p>
            </article>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="font-bold text-slate-950">Bookings by care giver</h2>
                <label class="relative block sm:w-80">
                    <span class="sr-only">Search care givers or bookings</span>
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input v-model="search" type="search" placeholder="Care giver, care seeker or booking" class="block w-full rounded-md border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-teal-500 focus:ring-teal-500" />
                </label>
            </div>

            <p v-if="isLoading && !groups.length" class="px-6 py-12 text-center text-sm text-slate-500">Loading uninvoiced bookings…</p>
            <p v-else-if="!filteredGroups.length" class="px-6 py-12 text-center text-sm text-slate-500">
                {{ search ? 'No care givers or bookings match your search.' : 'No closed uninvoiced bookings exist for this period.' }}
            </p>

            <div v-else class="divide-y divide-slate-200">
                <article v-for="group in filteredGroups" :key="group.care_giver.id">
                    <div class="flex flex-col gap-2 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="font-bold text-slate-950">{{ group.care_giver.name }}</h3>
                            <p class="text-sm text-slate-500">{{ group.care_giver.email }}</p>
                        </div>
                        <div class="text-sm sm:text-right">
                            <p class="font-semibold text-slate-700">{{ group.bookings_count }} booking{{ group.bookings_count === 1 ? '' : 's' }}</p>
                            <p class="font-bold text-teal-700">{{ money(group.booking_total) }}</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Booking</th><th class="px-5 py-3">Date</th><th class="px-5 py-3">Care type</th><th class="px-5 py-3">Care seeker</th><th class="px-5 py-3 text-right">Paid amount</th></tr></thead>
                            <tbody class="divide-y divide-slate-100"><tr v-for="booking in group.bookings" :key="booking.id"><td class="px-5 py-3 font-semibold">#{{ booking.id }}</td><td class="px-5 py-3">{{ date(booking.scheduled_date) }}</td><td class="px-5 py-3 capitalize">{{ booking.care_type.replaceAll('_', ' ') }}</td><td class="px-5 py-3"><span class="block font-semibold">{{ booking.care_seeker.name }}</span><span class="block text-xs text-slate-500">{{ booking.care_seeker.email }}</span></td><td class="px-5 py-3 text-right font-semibold">{{ money(booking.amount) }}</td></tr></tbody>
                        </table>
                    </div>
                </article>
            </div>
        </section>
    </div>
</template>
