<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { paymentMethods } from '@/constants/careBookings';
import { formatAmount, formatOption } from '@/utils/bookingFormat';
import { reactive, ref, watch } from 'vue';

const props = defineProps({
    booking: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['confirmed']);

const isSubmitting = ref(false);
const formErrors = ref({});
const submitError = ref('');

const paymentForm = reactive({
    amount_paid: '',
    payment_method: 'cash',
    payment_reference: '',
});

watch(
    () => props.booking,
    (booking) => {
        formErrors.value = {};
        submitError.value = '';
        Object.assign(paymentForm, {
            amount_paid: booking?.amount_due || '',
            payment_method: 'cash',
            payment_reference: '',
        });
    },
    { immediate: true },
);

const confirmPayment = async () => {
    isSubmitting.value = true;
    formErrors.value = {};
    submitError.value = '';

    try {
        await axios.post(`/api/care-bookings/${props.booking.id}/confirm-payment`, paymentForm);
        emit('confirmed');
    } catch (error) {
        if (error.response?.status === 422) {
            formErrors.value = Object.fromEntries(
                Object.entries(error.response.data.errors || {}).map(([field, messages]) => [
                    field,
                    Array.isArray(messages) ? messages[0] : messages,
                ]),
            );
        } else {
            submitError.value = 'Something went wrong while confirming the payment. Please try again.';
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <section class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-5">
        <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
            <h4 class="text-base font-semibold text-slate-950">Payment</h4>
            <p v-if="booking.amount_due" class="text-base font-bold text-slate-950">
                Amount due: {{ formatAmount(booking.amount_due) }}
            </p>
        </div>

        <template v-if="booking.status === 'awaiting_payment'">
            <p class="mt-2 text-sm text-slate-600">
                The visit is done. Pay your care giver in cash or via Juice, then record the payment below.
            </p>

            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Amount paid (Rs)</span>
                    <input
                        v-model="paymentForm.amount_paid"
                        type="number"
                        min="0"
                        step="0.01"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                        required
                    />
                    <span v-if="formErrors.amount_paid" class="mt-1 block text-sm text-rose-600">
                        {{ formErrors.amount_paid }}
                    </span>
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Payment method</span>
                    <select
                        v-model="paymentForm.payment_method"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    >
                        <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                            {{ method.label }}
                        </option>
                    </select>
                    <span v-if="formErrors.payment_method" class="mt-1 block text-sm text-rose-600">
                        {{ formErrors.payment_method }}
                    </span>
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">
                        Transaction reference
                        <span v-if="paymentForm.payment_method !== 'juice'" class="text-slate-400">(optional)</span>
                    </span>
                    <input
                        v-model="paymentForm.payment_reference"
                        type="text"
                        maxlength="100"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                        placeholder="e.g. Juice transaction ID"
                    />
                    <span v-if="formErrors.payment_reference" class="mt-1 block text-sm text-rose-600">
                        {{ formErrors.payment_reference }}
                    </span>
                </label>
            </div>

            <p v-if="submitError" class="mt-3 rounded-md bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                {{ submitError }}
            </p>

            <PrimaryButton
                type="button"
                class="mt-4 w-full justify-center sm:w-auto"
                :disabled="isSubmitting"
                :class="{ 'opacity-75': isSubmitting }"
                @click="confirmPayment"
            >
                {{ isSubmitting ? 'Confirming...' : 'Confirm Payment' }}
            </PrimaryButton>
        </template>

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

            <p v-if="booking.status === 'paid'" class="mt-3 rounded-md bg-sky-50 px-4 py-3 text-sm font-medium text-sky-700">
                Payment recorded. Waiting for your care giver to confirm they received it.
            </p>
            <p v-else-if="booking.status === 'closed'" class="mt-3 rounded-md bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                Payment confirmed by your care giver. This booking is closed.
            </p>
        </template>
    </section>
</template>
