<script setup>
import { computed, onMounted, ref, watch } from 'vue';

const now = new Date();
const selectedYear = ref(now.getFullYear());
const selectedMonth = ref(now.getMonth() + 1);
const statistics = ref(null);
const monthly = ref([]);
const isLoading = ref(false);
const error = ref('');

const months = Array.from({ length: 12 }, (_, index) => ({
    value: index + 1,
    label: new Intl.DateTimeFormat('en-GB', { month: 'long' }).format(new Date(2026, index, 1)),
}));
const years = computed(() => Array.from({ length: 7 }, (_, index) => now.getFullYear() - index));
const selectedPeriodLabel = computed(() => selectedMonth.value
    ? `${months[selectedMonth.value - 1].label} ${selectedYear.value}`
    : `Year ${selectedYear.value}`,
);

const money = (value) => new Intl.NumberFormat('en-MU', {
    style: 'currency',
    currency: 'MUR',
    minimumFractionDigits: 2,
}).format(Number(value || 0));

const loadStatistics = async () => {
    isLoading.value = true;
    error.value = '';

    try {
        const response = await axios.get('/api/admin/statistics', {
            params: {
                year: selectedYear.value,
                month: selectedMonth.value || undefined,
            },
        });
        statistics.value = response.data.data;
        monthly.value = response.data.monthly || [];
    } catch (requestError) {
        error.value = requestError.response?.data?.message || 'Statistics could not be loaded.';
    } finally {
        isLoading.value = false;
    }
};

const showCurrentMonth = () => {
    selectedYear.value = now.getFullYear();
    selectedMonth.value = now.getMonth() + 1;
};

const showCurrentYear = () => {
    selectedYear.value = now.getFullYear();
    selectedMonth.value = '';
};

watch([selectedYear, selectedMonth], loadStatistics);
onMounted(loadStatistics);
</script>

<template>
    <div class="space-y-6">
        <section class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm lg:flex-row lg:items-end lg:justify-between">
            <div class="flex flex-wrap gap-3">
                <button type="button" class="rounded-md px-4 py-2 text-sm font-bold" :class="selectedYear === now.getFullYear() && selectedMonth === now.getMonth() + 1 ? 'bg-[#117d73] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" @click="showCurrentMonth">
                    Current month
                </button>
                <button type="button" class="rounded-md px-4 py-2 text-sm font-bold" :class="selectedYear === now.getFullYear() && !selectedMonth ? 'bg-[#117d73] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" @click="showCurrentYear">
                    Current year
                </button>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <label class="text-sm font-semibold text-slate-700">
                    Year
                    <select v-model="selectedYear" class="mt-1 block w-full rounded-md border-slate-300 py-2 text-sm focus:border-teal-500 focus:ring-teal-500 sm:w-32">
                        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                    </select>
                </label>
                <label class="text-sm font-semibold text-slate-700">
                    Month
                    <select v-model="selectedMonth" class="mt-1 block w-full rounded-md border-slate-300 py-2 text-sm focus:border-teal-500 focus:ring-teal-500 sm:w-48">
                        <option value="">All year</option>
                        <option v-for="month in months" :key="month.value" :value="month.value">{{ month.label }}</option>
                    </select>
                </label>
            </div>
        </section>

        <p v-if="error" class="rounded-md bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ error }}</p>
        <div v-if="isLoading && !statistics" class="rounded-xl border border-slate-200 bg-white px-6 py-16 text-center text-sm text-slate-500 shadow-sm">
            <i class="fa-solid fa-spinner fa-spin mr-2"></i>Loading statistics…
        </div>

        <template v-else-if="statistics">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-950">{{ selectedPeriodLabel }}</h2>
                <span v-if="isLoading" class="text-sm text-slate-500"><i class="fa-solid fa-spinner fa-spin mr-1"></i>Updating…</span>
            </div>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-xl border border-emerald-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Paid invoice total</p><i class="fa-solid fa-coins text-emerald-600"></i></div>
                    <p class="mt-3 text-2xl font-black text-emerald-700">{{ money(statistics.paid_invoice_total) }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ statistics.paid_invoice_count }} paid invoice{{ statistics.paid_invoice_count === 1 ? '' : 's' }}</p>
                </article>
                <article class="rounded-xl border border-amber-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Outstanding invoices</p><i class="fa-solid fa-clock text-amber-600"></i></div>
                    <p class="mt-3 text-2xl font-black text-amber-700">{{ money(statistics.unpaid_invoice_total) }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ statistics.unpaid_invoice_count }} unpaid of {{ statistics.invoices_generated }} generated</p>
                </article>
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Bookings created</p><i class="fa-solid fa-calendar-plus text-teal-600"></i></div>
                    <p class="mt-3 text-3xl font-black text-slate-950">{{ statistics.bookings_created }}</p>
                </article>
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Closure rate</p><i class="fa-solid fa-chart-line text-blue-600"></i></div>
                    <p class="mt-3 text-3xl font-black text-slate-950">{{ statistics.booking_closure_rate }}%</p>
                    <p class="mt-1 text-xs text-slate-500">Of closed and cancelled bookings</p>
                </article>
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Bookings closed</p>
                    <p class="mt-3 text-3xl font-black text-emerald-700">{{ statistics.bookings_closed }}</p>
                </article>
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Bookings cancelled</p>
                    <p class="mt-3 text-3xl font-black text-rose-700">{{ statistics.bookings_cancelled }}</p>
                </article>
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Care seekers joined</p>
                    <p class="mt-3 text-3xl font-black text-slate-950">{{ statistics.care_seekers_joined }}</p>
                </article>
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Care givers joined</p>
                    <p class="mt-3 text-3xl font-black text-slate-950">{{ statistics.care_givers_joined }}</p>
                </article>
            </section>

            <section class="grid gap-4 sm:grid-cols-2">
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Average paid invoice</p>
                    <p class="mt-2 text-2xl font-black text-slate-950">{{ money(statistics.average_paid_invoice) }}</p>
                </article>
                <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Invoices generated</p>
                    <p class="mt-2 text-2xl font-black text-slate-950">{{ statistics.invoices_generated }}</p>
                </article>
            </section>

            <section v-if="!selectedMonth" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <h2 class="border-b border-slate-100 px-5 py-4 font-bold text-slate-950">Monthly breakdown — {{ selectedYear }}</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Month</th><th class="px-5 py-3">Bookings</th><th class="px-5 py-3">Care seekers</th><th class="px-5 py-3">Care givers</th><th class="px-5 py-3 text-right">Paid invoices</th></tr></thead>
                        <tbody class="divide-y divide-slate-100"><tr v-for="row in monthly" :key="row.month"><td class="px-5 py-3 font-semibold">{{ months[row.month - 1].label }}</td><td class="px-5 py-3">{{ row.bookings_created }}</td><td class="px-5 py-3">{{ row.care_seekers_joined }}</td><td class="px-5 py-3">{{ row.care_givers_joined }}</td><td class="px-5 py-3 text-right font-semibold">{{ money(row.paid_invoice_total) }}</td></tr></tbody>
                    </table>
                </div>
            </section>
        </template>
    </div>
</template>
