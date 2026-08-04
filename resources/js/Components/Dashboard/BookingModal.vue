<script setup>
import BookingPaymentPanel from '@/Components/Dashboard/BookingPaymentPanel.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { editableStatuses, statusClasses } from '@/constants/careBookings';
import { useCareOptions } from '@/composables/useCareOptions';
import { formatStatus, providerName } from '@/utils/bookingFormat';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    booking: {
        type: Object,
        default: null,
    },
});
const { careTypes, carerTypes } = useCareOptions();

const emit = defineEmits(['close', 'saved', 'view-care-giver']);

const isSaving = ref(false);
const isCancelling = ref(false);
const confirmingCancel = ref(false);
const showReviewForm = ref(false);
const isSubmittingReview = ref(false);
const isDeletingReview = ref(false);
const reviewRating = ref(0);
const reviewComment = ref('');
const reviewError = ref('');
const formErrors = ref({});
const submitError = ref('');
const repeatEnabled = ref(false);
const repeatFrequency = ref('daily');
const repeatUntil = ref('');

const bookingForm = reactive({
    scheduled_date: '',
    start_time: '09:00',
    duration_hours: 1,
    care_type: '',
    preferred_carer_type: '',
    address: '',
    contact_phone: '',
    description: '',
});

const isEditing = computed(() => Boolean(props.booking));
const isReadOnly = computed(() => isEditing.value && !editableStatuses.includes(props.booking.status));
const canCancel = computed(() => isEditing.value && editableStatuses.includes(props.booking.status));
const showPayment = computed(() =>
    isEditing.value && ['awaiting_payment', 'paid', 'closed'].includes(props.booking.status),
);
const hasCareGiver = computed(() => Boolean(props.booking?.care_giver));
const isClosed = computed(() => props.booking?.status === 'closed');
const existingReview = computed(() => props.booking?.review);
const maximumRepeatedBookings = 10;

const localDate = (date = new Date()) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const localTime = (date = new Date()) =>
    `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;

const minimumDate = () => isEditing.value ? undefined : localDate();
const minimumTime = () =>
    !isEditing.value && bookingForm.scheduled_date === localDate() ? localTime() : undefined;

const parseLocalDate = (value) => {
    const [year, month, day] = (value || '').split('-').map(Number);

    if (!year || !month || !day) {
        return null;
    }

    const date = new Date(year, month - 1, day);

    return Number.isNaN(date.getTime()) ? null : date;
};

const bookingDates = computed(() => {
    const startDate = parseLocalDate(bookingForm.scheduled_date);

    if (!startDate) {
        return [];
    }

    const dates = [localDate(startDate)];

    if (!repeatEnabled.value) {
        return dates;
    }

    const endDate = parseLocalDate(repeatUntil.value);

    if (!endDate || endDate <= startDate) {
        return dates;
    }

    const cursor = new Date(startDate);
    const step = repeatFrequency.value === 'weekly' ? 7 : 1;

    while (dates.length <= maximumRepeatedBookings && cursor < endDate) {
        cursor.setDate(cursor.getDate() + step);

        if (cursor <= endDate) {
            dates.push(localDate(cursor));
        }
    }

    return dates;
});

const repeatLimitExceeded = computed(() =>
    repeatEnabled.value && bookingDates.value.length > maximumRepeatedBookings,
);

const saveButtonLabel = computed(() => {
    if (isSaving.value) {
        return 'Saving...';
    }

    if (repeatLimitExceeded.value) {
        return `Maximum ${maximumRepeatedBookings} bookings`;
    }

    if (!isEditing.value && repeatEnabled.value && bookingDates.value.length > 1) {
        return `Create ${bookingDates.value.length} bookings`;
    }

    return 'Save booking';
});

const modalSubtitle = computed(() => {
    if (!isReadOnly.value) {
        return 'Describe the care needed, including health state and allergies if any.';
    }

    if (props.booking.status === 'awaiting_payment') {
        return 'The visit is completed. Please confirm the payment below.';
    }

    return 'This booking can no longer be changed.';
});

watch(
    () => props.show,
    (show) => {
        if (!show) {
            return;
        }

        formErrors.value = {};
        submitError.value = '';
        reviewError.value = '';
        confirmingCancel.value = false;
        showReviewForm.value = false;
        reviewRating.value = props.booking?.review?.rating || 0;
        reviewComment.value = props.booking?.review?.comment || '';
        repeatEnabled.value = false;
        repeatFrequency.value = 'daily';
        repeatUntil.value = '';
        Object.assign(bookingForm, {
            scheduled_date: props.booking?.scheduled_date || '',
            start_time: props.booking?.start_time?.slice(0, 5) || '09:00',
            duration_hours: props.booking?.duration_hours || 1,
            care_type: props.booking?.care_type || '',
            preferred_carer_type: props.booking?.preferred_carer_type || '',
            address: props.booking?.address || '',
            contact_phone: props.booking?.contact_phone || '',
            description: props.booking?.description || '',
        });
    },
);

const normalizeErrors = (errors) => {
    return Object.entries(errors || {}).reduce((mappedErrors, [key, messages]) => {
        const field = key.split('.').pop();
        mappedErrors[field] = Array.isArray(messages) ? messages[0] : messages;

        return mappedErrors;
    }, {});
};

const mutate = async (payload) => {
    await axios.post('/api/care-bookings/mutate', {
        mutate: Array.isArray(payload) ? payload : [payload],
    });
};

const saveBooking = async () => {
    if (isReadOnly.value) {
        return;
    }

    formErrors.value = {};
    submitError.value = '';

    if (!isEditing.value && repeatEnabled.value) {
        if (!repeatUntil.value) {
            formErrors.value = {
                repeat_until: 'Choose when the repeated bookings should end.',
            };
            return;
        }

        if (repeatUntil.value <= bookingForm.scheduled_date) {
            formErrors.value = {
                repeat_until: 'The repeat end date must be after the first booking date.',
            };
            return;
        }

        if (bookingDates.value.length > maximumRepeatedBookings) {
            formErrors.value = {
                repeat_until: `You can create up to ${maximumRepeatedBookings} bookings at once.`,
            };
            return;
        }
    }

    const hasPastDate = !isEditing.value && bookingDates.value.some((date) => {
        const selectedAt = new Date(`${date}T${bookingForm.start_time}`);

        return Number.isNaN(selectedAt.getTime()) || selectedAt < new Date();
    });

    if (hasPastDate) {
        formErrors.value = {
            start_time: 'The selected booking date and time must not be in the past.',
        };
        return;
    }

    isSaving.value = true;

    const payload = isEditing.value
        ? {
            operation: 'update',
            key: props.booking.id,
            attributes: { ...bookingForm },
        }
        : bookingDates.value.map((scheduledDate) => ({
            operation: 'create',
            attributes: {
                ...bookingForm,
                scheduled_date: scheduledDate,
            },
        }));

    try {
        await mutate(payload);
        emit('saved');
    } catch (error) {
        if (error.response?.status === 422) {
            formErrors.value = normalizeErrors(error.response.data.errors);
        } else {
            submitError.value = 'Something went wrong while saving. Please try again.';
        }
    } finally {
        isSaving.value = false;
    }
};

const cancelBooking = async () => {
    if (!confirmingCancel.value) {
        confirmingCancel.value = true;
        return;
    }

    isCancelling.value = true;
    submitError.value = '';

    try {
        await mutate({
            operation: 'update',
            key: props.booking.id,
            attributes: { status: 'cancelled' },
        });
        emit('saved');
    } catch {
        submitError.value = 'Something went wrong while cancelling. Please try again.';
    } finally {
        isCancelling.value = false;
        confirmingCancel.value = false;
    }
};

const submitReview = async () => {
    if (!reviewRating.value || isSubmittingReview.value) {
        reviewError.value = 'Please select a rating from 1 to 5 stars.';
        return;
    }

    isSubmittingReview.value = true;
    reviewError.value = '';

    try {
        const payload = {
            rating: reviewRating.value,
            comment: reviewComment.value.trim() || null,
        };

        if (existingReview.value) {
            await axios.patch(`/api/reviews/${existingReview.value.id}`, payload);
        } else {
            await axios.post(`/api/care-bookings/${props.booking.id}/review`, payload);
        }

        emit('saved');
    } catch (error) {
        if (error.response?.status === 422) {
            reviewError.value = Object.values(error.response.data.errors || {}).flat()[0]
                || 'Please check your review and try again.';
        } else if (error.response?.status === 403) {
            reviewError.value = 'This booking cannot be reviewed.';
        } else {
            reviewError.value = 'Your review could not be saved. Please try again.';
        }
    } finally {
        isSubmittingReview.value = false;
    }
};

const editReview = () => {
    reviewRating.value = existingReview.value.rating;
    reviewComment.value = existingReview.value.comment || '';
    reviewError.value = '';
    showReviewForm.value = true;
};

const cancelReviewForm = () => {
    showReviewForm.value = false;
    reviewError.value = '';
    reviewRating.value = existingReview.value?.rating || 0;
    reviewComment.value = existingReview.value?.comment || '';
};

const deleteReview = async () => {
    const result = await Swal.fire({
        title: 'Delete your review?',
        text: 'Your rating and comment will be permanently removed.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete review',
        cancelButtonText: 'Keep review',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-lg px-5 py-2.5 text-sm font-semibold',
            cancelButton: 'rounded-lg px-5 py-2.5 text-sm font-semibold',
        },
    });

    if (!result.isConfirmed) {
        return;
    }

    isDeletingReview.value = true;
    reviewError.value = '';

    try {
        await axios.delete(`/api/reviews/${existingReview.value.id}`);
        emit('saved');
    } catch {
        reviewError.value = 'Your review could not be deleted. Please try again.';
    } finally {
        isDeletingReview.value = false;
    }
};
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="$emit('close')">
        <form class="p-5 sm:p-6" @submit.prevent="saveBooking">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-semibold text-slate-950">
                            {{ isReadOnly ? 'Booking details' : isEditing ? 'Edit booking' : 'Create booking' }}
                        </h3>
                        <span
                            v-if="isEditing"
                            class="rounded-md px-2 py-1 text-xs font-semibold"
                            :class="statusClasses[booking.status] || statusClasses.open"
                        >
                            {{ formatStatus(booking.status) }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ modalSubtitle }}
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-md p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    @click="$emit('close')"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div
                v-if="hasCareGiver"
                class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-teal-100 bg-teal-50/60 px-4 py-3 text-sm text-slate-700"
            >
                <img
                    v-if="booking.care_giver?.avatar_url"
                    :src="booking.care_giver.avatar_url"
                    :alt="`${providerName(booking)} profile photo`"
                    class="h-9 w-9 rounded-full object-cover"
                />
                <span v-else class="flex h-9 w-9 items-center justify-center rounded-full bg-teal-100 font-bold text-teal-800">
                    {{ providerName(booking).charAt(0) }}
                </span>
                <span class="min-w-0 flex-1">
                    Your care giver is <strong class="text-slate-950">{{ providerName(booking) }}</strong>
                </span>
                <button
                    type="button"
                    class="rounded-md border border-teal-700 px-3 py-1.5 text-xs font-semibold text-teal-800 transition hover:bg-teal-700 hover:text-white"
                    @click="emit('view-care-giver', booking.care_giver)"
                >
                    View profile
                </button>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Date</span>
                    <input
                        v-model="bookingForm.scheduled_date"
                        type="date"
                        :min="minimumDate()"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-50 disabled:text-slate-500"
                        :disabled="isReadOnly"
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
                        :min="minimumTime()"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-50 disabled:text-slate-500"
                        :disabled="isReadOnly"
                        required
                    />
                    <span class="mt-1 block text-xs text-slate-500">Each booking is for 1 hour.</span>
                    <span v-if="formErrors.start_time" class="mt-1 block text-sm text-rose-600">
                        {{ formErrors.start_time }}
                    </span>
                </label>

                <section
                    v-if="!isEditing"
                    class="sm:col-span-2 rounded-xl border border-teal-100 bg-teal-50/50 p-4"
                >
                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            v-model="repeatEnabled"
                            type="checkbox"
                            class="mt-0.5 rounded border-slate-300 text-teal-700 focus:ring-teal-500"
                        />
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">Repeat booking</span>
                            <span class="mt-0.5 block text-xs text-slate-600">
                                Create a separate booking with the same details for each repeated date.
                            </span>
                        </span>
                    </label>

                    <div v-if="repeatEnabled" class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">Repeat</span>
                            <select
                                v-model="repeatFrequency"
                                class="mt-1 block w-full rounded-md border-slate-300 bg-white shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            >
                                <option value="daily">Every day</option>
                                <option value="weekly">Every week</option>
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">Repeat until</span>
                            <input
                                v-model="repeatUntil"
                                type="date"
                                :min="bookingForm.scheduled_date || minimumDate()"
                                class="mt-1 block w-full rounded-md border-slate-300 bg-white shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                required
                            />
                            <span v-if="formErrors.repeat_until" class="mt-1 block text-sm text-rose-600">
                                {{ formErrors.repeat_until }}
                            </span>
                        </label>
                    </div>

                    <p v-if="repeatLimitExceeded" class="mt-3 text-sm font-semibold text-rose-700">
                        You can create a maximum of {{ maximumRepeatedBookings }} bookings at once. Choose an earlier end date.
                    </p>
                    <p v-else-if="repeatEnabled && bookingDates.length > 1" class="mt-3 text-sm font-semibold text-teal-800">
                        {{ bookingDates.length }} separate bookings will be created.
                    </p>
                </section>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Type of care</span>
                    <select
                        v-model="bookingForm.care_type"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-50 disabled:text-slate-500"
                        :disabled="isReadOnly"
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
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-50 disabled:text-slate-500"
                        :disabled="isReadOnly"
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

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Address of the visit</span>
                    <input
                        v-model="bookingForm.address"
                        type="text"
                        maxlength="255"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-50 disabled:text-slate-500"
                        placeholder="Street, city"
                        :disabled="isReadOnly"
                        required
                    />
                    <span v-if="formErrors.address" class="mt-1 block text-sm text-rose-600">
                        {{ formErrors.address }}
                    </span>
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Contact phone <span class="text-slate-400">(optional)</span></span>
                    <input
                        v-model="bookingForm.contact_phone"
                        type="tel"
                        maxlength="30"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-50 disabled:text-slate-500"
                        placeholder="e.g. 5xxx xxxx"
                        :disabled="isReadOnly"
                    />
                    <span v-if="formErrors.contact_phone" class="mt-1 block text-sm text-rose-600">
                        {{ formErrors.contact_phone }}
                    </span>
                </label>
            </div>

            <label class="mt-4 block">
                <span class="text-sm font-medium text-slate-700">Description of care</span>
                <textarea
                    v-model="bookingForm.description"
                    rows="5"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-50 disabled:text-slate-500"
                    placeholder="Include health state, current needs, allergies, mobility concerns, or any special instructions."
                    :disabled="isReadOnly"
                    required
                ></textarea>
                <span v-if="formErrors.description" class="mt-1 block text-sm text-rose-600">
                    {{ formErrors.description }}
                </span>
            </label>

            <BookingPaymentPanel
                v-if="showPayment"
                :booking="booking"
                @confirmed="$emit('saved')"
            />

            <section v-if="isClosed" class="mt-5 rounded-xl border border-amber-200 bg-amber-50/50 p-5">
                <template v-if="existingReview && !showReviewForm">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h4 class="text-base font-semibold text-slate-950">Your review</h4>
                        <span class="text-sm font-medium text-slate-600">Review submitted</span>
                    </div>
                    <div class="mt-3 flex gap-1" :aria-label="`${existingReview.rating} out of 5 stars`">
                        <i
                            v-for="star in 5"
                            :key="star"
                            class="fa-solid fa-star text-xl"
                            :class="star <= existingReview.rating ? 'text-amber-400' : 'text-slate-300'"
                        ></i>
                    </div>
                    <p v-if="existingReview.comment" class="mt-3 whitespace-pre-line text-sm text-slate-700">
                        {{ existingReview.comment }}
                    </p>
                    <p v-if="reviewError" class="mt-3 text-sm font-medium text-rose-700">{{ reviewError }}</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <SecondaryButton type="button" @click="editReview">
                            <i class="fa-solid fa-pen mr-2"></i>
                            Edit review
                        </SecondaryButton>
                        <DangerButton
                            type="button"
                            :disabled="isDeletingReview"
                            :class="{ 'opacity-75': isDeletingReview }"
                            @click="deleteReview"
                        >
                            <i class="fa-solid fa-trash mr-2"></i>
                            {{ isDeletingReview ? 'Deleting...' : 'Delete review' }}
                        </DangerButton>
                    </div>
                </template>

                <template v-else-if="showReviewForm">
                    <h4 class="text-base font-semibold text-slate-950">Review {{ providerName(booking) }}</h4>
                    <p class="mt-1 text-sm text-slate-600">How was the care you received?</p>

                    <div class="mt-3">
                        <span class="sr-only">Rating</span>
                        <div class="flex gap-1">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                class="rounded p-1 text-2xl transition hover:scale-110 focus:outline-none focus:ring-2 focus:ring-amber-400"
                                :class="star <= reviewRating ? 'text-amber-400' : 'text-slate-300'"
                                :aria-label="`${star} star${star === 1 ? '' : 's'}`"
                                @click="reviewRating = star"
                            >
                                <i class="fa-solid fa-star"></i>
                            </button>
                        </div>
                    </div>

                    <label class="mt-3 block">
                        <span class="text-sm font-medium text-slate-700">Comment <span class="text-slate-400">(optional)</span></span>
                        <textarea
                            v-model="reviewComment"
                            rows="4"
                            maxlength="2000"
                            class="mt-1 block w-full rounded-md border-slate-300 bg-white shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            placeholder="Share your experience with this care giver."
                        ></textarea>
                    </label>

                    <p v-if="reviewError" class="mt-2 text-sm font-medium text-rose-700">{{ reviewError }}</p>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <PrimaryButton
                            type="button"
                            :disabled="isSubmittingReview"
                            :class="{ 'opacity-75': isSubmittingReview }"
                            @click="submitReview"
                        >
                            {{ isSubmittingReview ? 'Saving...' : existingReview ? 'Save changes' : 'Submit review' }}
                        </PrimaryButton>
                        <SecondaryButton type="button" @click="cancelReviewForm">
                            Cancel
                        </SecondaryButton>
                    </div>
                </template>

                <template v-else>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h4 class="text-base font-semibold text-slate-950">How was your care?</h4>
                            <p class="mt-1 text-sm text-slate-600">Rate your experience with {{ providerName(booking) }}.</p>
                        </div>
                        <PrimaryButton type="button" class="shrink-0" @click="showReviewForm = true">
                            Leave a review
                        </PrimaryButton>
                    </div>
                </template>
            </section>

            <p v-if="submitError" class="mt-4 rounded-md bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                {{ submitError }}
            </p>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                <DangerButton
                    v-if="canCancel"
                    type="button"
                    class="w-full justify-center sm:mr-auto sm:w-auto"
                    :disabled="isCancelling"
                    @click="cancelBooking"
                >
                    {{ isCancelling ? 'Cancelling...' : confirmingCancel ? 'Confirm cancellation' : 'Cancel booking' }}
                </DangerButton>
                <SecondaryButton type="button" class="w-full justify-center sm:w-auto" @click="$emit('close')">
                    Close
                </SecondaryButton>
                <PrimaryButton
                    v-if="!isReadOnly"
                    type="submit"
                    class="w-full justify-center sm:w-auto"
                    :disabled="isSaving || repeatLimitExceeded"
                    :class="{ 'opacity-75': isSaving || repeatLimitExceeded }"
                >
                    {{ saveButtonLabel }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
