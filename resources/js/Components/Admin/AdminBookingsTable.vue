<script setup>
import { careTypes, statusClasses, statusLabels } from '@/constants/careBookings';
import { confirmAdminAction, showAdminError, showAdminSuccess } from '@/utils/adminAlerts';
import { formatOption } from '@/utils/bookingFormat';
import { computed, onMounted, ref, watch } from 'vue';

const bookings = ref([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });
const statusCounts = ref({});
const activeStatus = ref('');
const search = ref('');
const loading = ref(false);
const error = ref('');
let searchTimer;

const statuses = computed(() => [
    { value: '', label: 'All', count: Object.values(statusCounts.value).reduce((sum, value) => sum + Number(value), 0) },
    ...Object.entries(statusLabels).map(([value, label]) => ({
        value,
        label,
        count: statusCounts.value[value] || 0,
    })),
]);

const loadBookings = async (page = 1) => {
    loading.value = true;
    error.value = '';
    try {
        const response = await window.axios.get('/api/admin/bookings', {
            params: {
                status: activeStatus.value || undefined,
                search: search.value || undefined,
                page,
                per_page: 10,
            },
        });
        bookings.value = response.data.data;
        meta.value = response.data.meta;
        statusCounts.value = response.data.status_counts;
    } catch (exception) {
        error.value = exception.response?.data?.message || 'Unable to load bookings.';
    } finally {
        loading.value = false;
    }
};

watch(activeStatus, () => loadBookings(1));
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadBookings(1), 350);
});
onMounted(() => loadBookings());

const cancelBooking = async (booking) => {
    const confirmed = await confirmAdminAction({
        title: `Cancel booking #${booking.id}?`,
        text: 'The care seeker and care giver will no longer be able to act on this booking.',
        confirmText: 'Yes, cancel booking',
        destructive: true,
    });

    if (!confirmed) return;

    try {
        await window.axios.patch(`/api/admin/bookings/${booking.id}/cancel`);
        await loadBookings(meta.value.current_page);
        showAdminSuccess(`Booking #${booking.id} has been cancelled.`);
    } catch (exception) {
        showAdminError(exception.response?.data?.message || 'Unable to cancel this booking.');
    }
};

const canCancel = (booking) => !['closed', 'cancelled'].includes(booking.status);
const formatDate = (date) => date ? new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium' }).format(new Date(`${date}T00:00:00`)) : '—';
const formatTime = (time) => time ? time.slice(0, 5) : '—';
const formatMoney = (amount) => `Rs ${Number(amount || 0).toFixed(2)}`;
</script>

<template>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5">
            <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold">Bookings</h2>
                    <p class="mt-1 text-sm text-slate-500">Manage all care bookings and cancel them when required.</p>
                </div>
                <div class="relative w-full sm:w-80">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input v-model="search" type="search" placeholder="Search by ID, user, or care type..." class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm focus:border-[#117d73] focus:outline-none focus:ring-2 focus:ring-[#117d73]/20" />
                </div>
            </div>
            <div class="flex gap-2 overflow-x-auto pb-1">
                <button
                    v-for="status in statuses"
                    :key="status.value || 'all'"
                    type="button"
                    class="whitespace-nowrap rounded-full px-3.5 py-2 text-xs font-semibold transition"
                    :class="activeStatus === status.value ? 'bg-[#117d73] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    @click="activeStatus = status.value"
                >
                    {{ status.label }} <span class="ml-1 opacity-75">{{ status.count }}</span>
                </button>
            </div>
        </div>

        <div v-if="error" class="m-5 rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ error }}</div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Booking</th>
                        <th class="px-5 py-3">Care seeker</th>
                        <th class="px-5 py-3">Care giver</th>
                        <th class="px-5 py-3">Schedule</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Amount</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="loading"><td colspan="8" class="px-5 py-14 text-center text-slate-500"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading bookings...</td></tr>
                    <tr v-else-if="!bookings.length"><td colspan="8" class="px-5 py-14 text-center text-slate-500">No bookings found in this status.</td></tr>
                    <tr v-for="booking in bookings" v-else :key="booking.id" class="hover:bg-slate-50/70">
                        <td class="px-5 py-4 font-bold text-slate-900">#{{ booking.id }}</td>
                        <td class="px-5 py-4"><p class="font-semibold text-slate-800">{{ booking.care_seeker.name }}</p><p class="text-xs text-slate-500">{{ booking.care_seeker.email }}</p></td>
                        <td class="px-5 py-4"><template v-if="booking.care_giver"><p class="font-semibold text-slate-800">{{ booking.care_giver.name }}</p><p class="text-xs text-slate-500">{{ booking.care_giver.email }}</p></template><span v-else class="text-slate-400">Unassigned</span></td>
                        <td class="px-5 py-4 text-slate-600"><p>{{ formatDate(booking.scheduled_date) }}</p><p class="text-xs">{{ formatTime(booking.start_time) }} · {{ booking.duration_hours }}h</p></td>
                        <td class="px-5 py-4 text-slate-600">{{ formatOption(careTypes, booking.care_type) }}</td>
                        <td class="px-5 py-4"><p class="font-semibold text-slate-800">{{ formatMoney(booking.amount_due) }}</p><p class="text-xs text-slate-500">Paid {{ formatMoney(booking.amount_paid) }}</p></td>
                        <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses[booking.status]">{{ statusLabels[booking.status] || booking.status }}</span></td>
                        <td class="px-5 py-4 text-right">
                            <button v-if="canCancel(booking)" type="button" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50" @click="cancelBooking(booking)">Cancel</button>
                            <span v-else class="text-xs text-slate-400">No action</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="meta.last_page > 1" class="flex items-center justify-between border-t border-slate-200 px-5 py-4">
            <p class="text-sm text-slate-500">Page {{ meta.current_page }} of {{ meta.last_page }} · {{ meta.total }} bookings</p>
            <div class="flex gap-2">
                <button type="button" :disabled="meta.current_page <= 1" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold disabled:opacity-40" @click="loadBookings(meta.current_page - 1)">Previous</button>
                <button type="button" :disabled="meta.current_page >= meta.last_page" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold disabled:opacity-40" @click="loadBookings(meta.current_page + 1)">Next</button>
            </div>
        </div>
    </section>
</template>
