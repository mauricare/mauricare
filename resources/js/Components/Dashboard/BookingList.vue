<script setup>
import { careTypes, statusClasses } from '@/constants/careBookings';
import { formatDateParts, formatOption, formatStatus, formatTime, providerName, seekerName } from '@/utils/bookingFormat';

const props = defineProps({
    bookings: {
        type: Array,
        required: true,
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
    loadError: {
        type: Boolean,
        default: false,
    },
    emptyMessage: {
        type: String,
        default: 'You have no bookings yet.',
    },
    emptyHint: {
        type: String,
        default: "Create a care request and we'll match you with a provider.",
    },
    createLabel: {
        type: String,
        default: 'Create Request',
    },
    showCreate: {
        type: Boolean,
        default: true,
    },
    showSeeker: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['select', 'retry', 'create']);

const personName = (booking) => (props.showSeeker ? seekerName(booking) : providerName(booking));
const person = (booking) => (props.showSeeker ? booking.user : booking.care_giver);
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-slate-100">
        <div v-if="isLoading" class="px-6 py-10 text-center text-sm text-slate-500">
            Loading bookings...
        </div>

        <div v-else-if="loadError" class="px-6 py-10 text-center">
            <p class="text-sm text-slate-600">We couldn't load your bookings.</p>
            <button
                type="button"
                class="mt-3 rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800"
                @click="$emit('retry')"
            >
                Try again
            </button>
        </div>

        <div v-else-if="!bookings.length" class="px-6 py-12 text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-teal-50 text-teal-700">
                <i class="fa-regular fa-calendar-plus text-xl"></i>
            </span>
            <p class="mt-4 font-semibold text-slate-950">{{ emptyMessage }}</p>
            <p class="mt-1 text-sm text-slate-600">{{ emptyHint }}</p>
            <button
                v-if="showCreate"
                type="button"
                class="mt-4 rounded-md bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800"
                @click="$emit('create')"
            >
                {{ createLabel }}
            </button>
        </div>

        <button
            v-for="booking in bookings"
            v-else
            :key="booking.id"
            type="button"
            class="grid w-full grid-cols-[56px_minmax(0,1fr)_16px] items-center gap-3 border-b border-slate-100 px-3 py-4 text-left last:border-b-0 transition hover:bg-slate-50 sm:grid-cols-[72px_minmax(0,1fr)_auto_24px] sm:gap-4 sm:px-4"
            @click="$emit('select', booking)"
        >
            <span class="flex h-14 w-14 flex-col items-center justify-center rounded-lg bg-slate-50 sm:h-16 sm:w-16">
                <span class="text-xl font-bold leading-none text-slate-950 sm:text-2xl">{{ formatDateParts(booking.scheduled_date).day }}</span>
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
                    <img
                        v-if="person(booking)?.avatar_url"
                        :src="person(booking).avatar_url"
                        :alt="`${personName(booking)} profile photo`"
                        class="h-6 w-6 rounded-full object-cover"
                    />
                    <span v-else class="flex h-6 w-6 items-center justify-center rounded-full bg-teal-100 text-xs font-bold text-teal-800">
                        {{ personName(booking).charAt(0) }}
                    </span>
                    {{ personName(booking) }}
                </span>
                <span
                    class="mt-1.5 inline-flex rounded-md px-2 py-0.5 text-xs font-semibold sm:hidden"
                    :class="statusClasses[booking.status] || statusClasses.open"
                >
                    {{ formatStatus(booking.status) }}
                </span>
            </span>
            <span
                class="hidden rounded-md px-3 py-1.5 text-sm font-semibold sm:inline-flex"
                :class="statusClasses[booking.status] || statusClasses.open"
            >
                {{ formatStatus(booking.status) }}
            </span>
            <i class="fa-solid fa-chevron-right text-slate-500"></i>
        </button>
    </div>
</template>
