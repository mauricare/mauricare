<script setup>
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { careTypes, carerTypes, paymentMethods, statusClasses } from '@/constants/careBookings';
import { formatAmount, formatDateParts, formatOption, formatStatus, formatTime, seekerName } from '@/utils/bookingFormat';
import { computed, ref, watch } from 'vue';

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

const emit = defineEmits(['close', 'updated']);

const isSubmitting = ref(false);
const amountDue = ref('');
const formErrors = ref({});
const submitError = ref('');

watch(
    () => props.show,
    (show) => {
        if (!show) {
            return;
        }

        amountDue.value = props.booking?.amount_due || '';
        formErrors.value = {};
        submitError.value = '';
    },
);

const status = computed(() => props.booking?.status);

const detailRows = computed(() => {
    if (!props.booking) {
        return [];
    }

    return [
        { label: 'Care seeker', value: seekerName(props.booking) },
        { label: 'Date', value: formatDateParts(props.booking.scheduled_date).full },
        { label: 'Time', value: `${formatTime(props.booking.start_time)} (${props.booking.duration_hours}h)` },
        { label: 'Type of care', value: formatOption(careTypes, props.booking.care_type) },
        { label: 'Carer requested', value: formatOption(carerTypes, props.booking.preferred_carer_type) },
        { label: 'Address', value: props.booking.address || '—' },
        { label: 'Contact phone', value: props.booking.contact_phone || '—' },
    ];
});

const postAction = async (action, payload = {}) => {
    isSubmitting.value = true;
    formErrors.value = {};
    submitError.value = '';

    try {
        await axios.post(`/api/care-bookings/${props.booking.id}/${action}`, payload);
        emit('updated');
    } catch (error) {
        if (error.response?.status === 422) {
            formErrors.value = Object.fromEntries(
                Object.entries(error.response.data.errors || {}).map(([field, messages]) => [
                    field,
                    Array.isArray(messages) ? messages[0] : messages,
                ]),
            );
        } else if (error.response?.status === 409) {
            submitError.value = 'This booking has already been taken by another care giver.';
        } else {
            submitError.value = 'Something went wrong. Please try again.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const acceptBooking = () => postAction('assign');
const completeVisit = () => postAction('complete-visit', { amount_due: amountDue.value });
const confirmPaymentReceived = () => postAction('close');
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="$emit('close')">
        <div v-if="booking" class="p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-semibold text-slate-950">Booking details</h3>
                        <span
                            class="rounded-md px-2 py-1 text-xs font-semibold"
                            :class="statusClasses[booking.status] || statusClasses.open"
                        >
                            {{ formatStatus(booking.status) }}
                        </span>
                    </div>
                </div>
                <button
                    type="button"
                    class="rounded-md p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    @click="$emit('close')"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <dl class="mt-6 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                <div v-for="row in detailRows" :key="row.label">
                    <dt class="text-sm font-medium text-slate-500">{{ row.label }}</dt>
                    <dd class="mt-0.5 text-sm font-semibold text-slate-950">{{ row.value }}</dd>
                </div>
            </dl>

            <div class="mt-5">
                <h4 class="text-sm font-medium text-slate-500">Description of care</h4>
                <p class="mt-1 whitespace-pre-line rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-700">
                    {{ booking.description }}
                </p>
            </div>

            <section v-if="status === 'assigned'" class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-5">
                <h4 class="text-base font-semibold text-slate-950">Visit completed?</h4>
                <p class="mt-1 text-sm text-slate-600">
                    Once the home visit is done, enter the amount the care seeker has to pay.
                </p>
                <label class="mt-4 block max-w-xs">
                    <span class="text-sm font-medium text-slate-700">Amount to be paid (Rs)</span>
                    <input
                        v-model="amountDue"
                        type="number"
                        min="0"
                        step="0.01"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    />
                    <span v-if="formErrors.amount_due" class="mt-1 block text-sm text-rose-600">
                        {{ formErrors.amount_due }}
                    </span>
                </label>
            </section>

            <section
                v-else-if="['awaiting_payment', 'paid', 'closed'].includes(status)"
                class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-5"
            >
                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                    <h4 class="text-base font-semibold text-slate-950">Payment</h4>
                    <p class="text-base font-bold text-slate-950">Amount due: {{ formatAmount(booking.amount_due) }}</p>
                </div>

                <p v-if="status === 'awaiting_payment'" class="mt-3 rounded-md bg-violet-50 px-4 py-3 text-sm font-medium text-violet-700">
                    Waiting for the care seeker to record the payment.
                </p>

                <template v-else>
                    <dl class="mt-3 grid gap-2 text-sm text-slate-700 sm:grid-cols-3">
                        <div>
                            <dt class="font-medium text-slate-500">Amount paid</dt>
                            <dd class="mt-0.5 font-semibold text-slate-950">{{ formatAmount(booking.amount_paid) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Method</dt>
                            <dd class="mt-0.5 font-semibold text-slate-950">
                                {{ formatOption(paymentMethods, booking.payment_method) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Reference</dt>
                            <dd class="mt-0.5 font-semibold text-slate-950">{{ booking.payment_reference || '—' }}</dd>
                        </div>
                    </dl>

                    <p v-if="status === 'paid'" class="mt-3 rounded-md bg-sky-50 px-4 py-3 text-sm font-medium text-sky-700">
                        The care seeker recorded this payment. Check you received it, then confirm below.
                    </p>
                    <p v-else class="mt-3 rounded-md bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        Payment received. This booking is closed.
                    </p>
                </template>
            </section>

            <p v-if="submitError" class="mt-4 rounded-md bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                {{ submitError }}
            </p>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                <SecondaryButton type="button" class="w-full justify-center sm:w-auto" @click="$emit('close')">
                    Close
                </SecondaryButton>
                <PrimaryButton
                    v-if="status === 'open'"
                    type="button"
                    class="w-full justify-center sm:w-auto"
                    :disabled="isSubmitting"
                    :class="{ 'opacity-75': isSubmitting }"
                    @click="acceptBooking"
                >
                    {{ isSubmitting ? 'Accepting...' : 'Accept this booking' }}
                </PrimaryButton>
                <PrimaryButton
                    v-else-if="status === 'assigned'"
                    type="button"
                    class="w-full justify-center sm:w-auto"
                    :disabled="isSubmitting"
                    :class="{ 'opacity-75': isSubmitting }"
                    @click="completeVisit"
                >
                    {{ isSubmitting ? 'Saving...' : 'Visit completed' }}
                </PrimaryButton>
                <PrimaryButton
                    v-else-if="status === 'paid'"
                    type="button"
                    class="w-full justify-center sm:w-auto"
                    :disabled="isSubmitting"
                    :class="{ 'opacity-75': isSubmitting }"
                    @click="confirmPaymentReceived"
                >
                    {{ isSubmitting ? 'Confirming...' : 'Payment received' }}
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
