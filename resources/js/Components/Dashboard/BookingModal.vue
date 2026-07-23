<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { careTypes, carerTypes, readOnlyStatuses, statusClasses } from '@/constants/careBookings';
import { formatStatus } from '@/utils/bookingFormat';
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

const emit = defineEmits(['close', 'saved']);

const isSaving = ref(false);
const isCancelling = ref(false);
const confirmingCancel = ref(false);
const formErrors = ref({});
const submitError = ref('');

const bookingForm = reactive({
    scheduled_date: '',
    start_time: '09:00',
    duration_hours: 1,
    care_type: '',
    preferred_carer_type: '',
    description: '',
});

const isEditing = computed(() => Boolean(props.booking));
const isReadOnly = computed(() => isEditing.value && readOnlyStatuses.includes(props.booking.status));
const canCancel = computed(() => isEditing.value && !isReadOnly.value);

watch(
    () => props.show,
    (show) => {
        if (!show) {
            return;
        }

        formErrors.value = {};
        submitError.value = '';
        confirmingCancel.value = false;
        Object.assign(bookingForm, {
            scheduled_date: props.booking?.scheduled_date || '',
            start_time: props.booking?.start_time?.slice(0, 5) || '09:00',
            duration_hours: props.booking?.duration_hours || 1,
            care_type: props.booking?.care_type || '',
            preferred_carer_type: props.booking?.preferred_carer_type || '',
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
        mutate: [payload],
    });
};

const saveBooking = async () => {
    if (isReadOnly.value) {
        return;
    }

    isSaving.value = true;
    formErrors.value = {};
    submitError.value = '';

    const payload = {
        operation: isEditing.value ? 'update' : 'create',
        attributes: { ...bookingForm },
    };

    if (isEditing.value) {
        payload.key = props.booking.id;
    }

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
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="$emit('close')">
        <form class="p-6" @submit.prevent="saveBooking">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-semibold text-slate-950">
                            {{ isReadOnly ? 'Booking details' : isEditing ? 'Edit booking' : 'Create booking' }}
                        </h3>
                        <span
                            v-if="isEditing"
                            class="rounded-md px-2 py-1 text-xs font-semibold"
                            :class="statusClasses[booking.status] || statusClasses.pending"
                        >
                            {{ formatStatus(booking.status) }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ isReadOnly
                            ? 'This booking can no longer be changed.'
                            : 'Describe the care needed, including health state and allergies if any.' }}
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

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Date</span>
                    <input
                        v-model="bookingForm.scheduled_date"
                        type="date"
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
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-50 disabled:text-slate-500"
                        :disabled="isReadOnly"
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

            <p v-if="submitError" class="mt-4 rounded-md bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                {{ submitError }}
            </p>

            <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
                <DangerButton
                    v-if="canCancel"
                    type="button"
                    class="mr-auto"
                    :disabled="isCancelling"
                    @click="cancelBooking"
                >
                    {{ isCancelling ? 'Cancelling...' : confirmingCancel ? 'Confirm cancellation' : 'Cancel booking' }}
                </DangerButton>
                <SecondaryButton type="button" @click="$emit('close')">
                    Close
                </SecondaryButton>
                <PrimaryButton
                    v-if="!isReadOnly"
                    type="submit"
                    :disabled="isSaving"
                    :class="{ 'opacity-75': isSaving }"
                >
                    {{ isSaving ? 'Saving...' : 'Save booking' }}
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>
