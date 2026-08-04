<script setup>
import { computed, onMounted, ref, watch } from 'vue';

const now = new Date();
const selectedYear = ref(now.getFullYear());
const selectedMonth = ref(now.getMonth() + 1);
const statistics = ref(null);
const monthly = ref([]);
const isLoading = ref(false);
const error = ref('');
const monthlyView = ref('table');

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

const compactNumber = (value) => new Intl.NumberFormat('en', { notation: 'compact', maximumFractionDigits: 1 }).format(Number(value || 0));
const compactMoney = (value) => `Rs ${compactNumber(value)}`;
const chartMax = (key) => Math.max(...monthly.value.map((row) => Number(row[key] || 0)), 0);
const combinedChartMax = (...keys) => Math.max(...keys.map((key) => chartMax(key)), 0);
const barHeight = (value, max) => {
    const numericValue = Number(value || 0);
    if (!numericValue || !max) return 0;

    return Math.max((numericValue / max) * 150, 4);
};

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
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="font-bold text-slate-950">Monthly breakdown — {{ selectedYear }}</h2>
                    <div class="inline-flex w-fit rounded-lg bg-slate-100 p-1" role="group" aria-label="Monthly breakdown view">
                        <button type="button" class="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-xs font-bold transition" :class="monthlyView === 'table' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'" :aria-pressed="monthlyView === 'table'" @click="monthlyView = 'table'"><i class="fa-solid fa-table-list"></i> Table</button>
                        <button type="button" class="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-xs font-bold transition" :class="monthlyView === 'chart' ? 'bg-[#117d73] text-white shadow-sm' : 'text-slate-500 hover:text-slate-800'" :aria-pressed="monthlyView === 'chart'" @click="monthlyView = 'chart'"><i class="fa-solid fa-chart-column"></i> Charts</button>
                    </div>
                </div>
                <div v-if="monthlyView === 'table'" class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Month</th><th class="px-5 py-3">Bookings created</th><th class="px-5 py-3">Care seekers joined</th><th class="px-5 py-3">Care givers joined</th><th class="px-5 py-3 text-right">Paid invoices</th></tr></thead>
                        <tbody class="divide-y divide-slate-100"><tr v-for="row in monthly" :key="row.month"><td class="px-5 py-3 font-semibold">{{ months[row.month - 1].label }}</td><td class="px-5 py-3">{{ row.bookings_created }}</td><td class="px-5 py-3">{{ row.care_seekers_joined }}</td><td class="px-5 py-3">{{ row.care_givers_joined }}</td><td class="px-5 py-3 text-right font-semibold">{{ money(row.paid_invoice_total) }}</td></tr></tbody>
                    </table>
                </div>
                <div v-else class="grid gap-5 bg-slate-50/60 p-4 sm:p-5 xl:grid-cols-2">
                    <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mb-5 flex items-center justify-between gap-3">
                            <div><h3 class="font-bold text-slate-900">Bookings created</h3><p class="mt-1 text-xs text-slate-500">New bookings created each month</p></div>
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-50 text-[#117d73]"><i class="fa-solid fa-calendar-check"></i></span>
                        </div>
                        <div class="overflow-x-auto pb-1">
                            <div class="flex h-52 min-w-[660px] items-end gap-2 border-b border-slate-200 px-1">
                                <div v-for="row in monthly" :key="`booking-${row.month}`" class="flex h-full min-w-0 flex-1 flex-col items-center justify-end">
                                    <span class="mb-1 text-[10px] font-bold text-slate-600">{{ compactNumber(row.bookings_created) }}</span>
                                    <div class="w-full max-w-8 rounded-t bg-[#117d73] transition-all" :style="{ height: `${barHeight(row.bookings_created, chartMax('bookings_created'))}px` }" :title="`${months[row.month - 1].label}: ${row.bookings_created} bookings`"></div>
                                    <span class="mt-2 pb-2 text-[10px] font-semibold uppercase text-slate-500">{{ months[row.month - 1].label.slice(0, 3) }}</span>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                            <div><h3 class="font-bold text-slate-900">Care users joined</h3><p class="mt-1 text-xs text-slate-500">Care seekers compared with care givers</p></div>
                            <div class="flex gap-3 text-[11px] font-semibold"><span class="flex items-center gap-1.5"><i class="h-2.5 w-2.5 rounded-sm bg-sky-500"></i>Seekers</span><span class="flex items-center gap-1.5"><i class="h-2.5 w-2.5 rounded-sm bg-violet-500"></i>Givers</span></div>
                        </div>
                        <div class="overflow-x-auto pb-1">
                            <div class="flex h-52 min-w-[660px] items-end gap-2 border-b border-slate-200 px-1">
                                <div v-for="row in monthly" :key="`users-${row.month}`" class="flex h-full min-w-0 flex-1 flex-col items-center justify-end">
                                    <span class="mb-1 text-[9px] font-bold text-slate-600">{{ row.care_seekers_joined }}/{{ row.care_givers_joined }}</span>
                                    <div class="flex w-full items-end justify-center gap-1">
                                        <div class="w-3 rounded-t bg-sky-500 transition-all" :style="{ height: `${barHeight(row.care_seekers_joined, combinedChartMax('care_seekers_joined', 'care_givers_joined'))}px` }" :title="`${months[row.month - 1].label}: ${row.care_seekers_joined} care seekers`"></div>
                                        <div class="w-3 rounded-t bg-violet-500 transition-all" :style="{ height: `${barHeight(row.care_givers_joined, combinedChartMax('care_seekers_joined', 'care_givers_joined'))}px` }" :title="`${months[row.month - 1].label}: ${row.care_givers_joined} care givers`"></div>
                                    </div>
                                    <span class="mt-2 pb-2 text-[10px] font-semibold uppercase text-slate-500">{{ months[row.month - 1].label.slice(0, 3) }}</span>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 xl:col-span-2">
                        <div class="mb-5 flex items-center justify-between gap-3">
                            <div><h3 class="font-bold text-slate-900">Paid invoices</h3><p class="mt-1 text-xs text-slate-500">Total invoice revenue paid each month</p></div>
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><i class="fa-solid fa-coins"></i></span>
                        </div>
                        <div class="overflow-x-auto pb-1">
                            <div class="flex h-52 min-w-[660px] items-end gap-2 border-b border-slate-200 px-1">
                                <div v-for="row in monthly" :key="`invoice-${row.month}`" class="flex h-full min-w-0 flex-1 flex-col items-center justify-end">
                                    <span class="mb-1 text-[9px] font-bold text-emerald-700">{{ compactMoney(row.paid_invoice_total) }}</span>
                                    <div class="w-full max-w-8 rounded-t bg-emerald-500 transition-all" :style="{ height: `${barHeight(row.paid_invoice_total, chartMax('paid_invoice_total'))}px` }" :title="`${months[row.month - 1].label}: ${money(row.paid_invoice_total)}`"></div>
                                    <span class="mt-2 pb-2 text-[10px] font-semibold uppercase text-slate-500">{{ months[row.month - 1].label.slice(0, 3) }}</span>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </template>
    </div>
</template>
