<script setup>
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    careGiver: {
        type: Object,
        default: null,
    },
});

defineEmits(['close', 'message']);

const profile = ref(null);
const isLoading = ref(false);
const loadError = ref('');

const typeLabels = {
    doctor: 'Doctor',
    nurse: 'Nurse',
    carers: 'Carer',
    physiotherapist: 'Physiotherapist',
    other: 'Other',
};

const detailRows = computed(() => {
    if (!profile.value) {
        return [];
    }

    return [
        { label: 'Professional type', value: typeLabels[profile.value.type] || 'Not provided' },
        { label: 'Age', value: profile.value.age ? `${profile.value.age} years` : 'Not provided' },
        { label: 'City', value: profile.value.city || 'Not provided' },
        { label: 'Phone', value: profile.value.phone || 'Not provided' },
    ];
});

const formatDate = (value) => {
    const date = new Date(value);

    return Number.isNaN(date.getTime())
        ? ''
        : new Intl.DateTimeFormat('en-GB', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(date);
};

const loadProfile = async () => {
    if (!props.careGiver?.id) {
        return;
    }

    isLoading.value = true;
    loadError.value = '';
    profile.value = null;

    try {
        const response = await axios.get(`/api/care-givers/${props.careGiver.id}/profile`);
        profile.value = response.data.data;
    } catch {
        loadError.value = 'The care giver profile could not be loaded. Please try again.';
    } finally {
        isLoading.value = false;
    }
};

watch(
    [() => props.show, () => props.careGiver?.id],
    ([show]) => {
        if (show) {
            loadProfile();
        }
    },
);
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="$emit('close')">
        <div class="p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950">Care giver profile</h3>
                    <p class="mt-1 text-sm text-slate-600">Profile information and feedback from care seekers.</p>
                </div>
                <button
                    type="button"
                    class="rounded-md p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Close profile"
                    @click="$emit('close')"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div v-if="isLoading" class="py-16 text-center text-sm text-slate-500">
                Loading profile...
            </div>

            <div v-else-if="loadError" class="py-12 text-center">
                <p class="text-sm text-rose-700">{{ loadError }}</p>
                <button
                    type="button"
                    class="mt-3 rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800"
                    @click="loadProfile"
                >
                    Try again
                </button>
            </div>

            <template v-else-if="profile">
                <section class="mt-5 rounded-xl border border-teal-100 bg-teal-50/50 p-5">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                        <img
                            v-if="profile.avatar_url"
                            :src="profile.avatar_url"
                            :alt="`${profile.name} profile photo`"
                            class="h-24 w-24 shrink-0 rounded-full object-cover ring-4 ring-white"
                        />
                        <span
                            v-else
                            class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-teal-100 text-3xl font-bold text-teal-800 ring-4 ring-white"
                        >
                            {{ profile.name.charAt(0) }}
                        </span>

                        <div class="min-w-0">
                            <h4 class="truncate text-xl font-bold text-slate-950">{{ profile.name }}</h4>
                            <p class="mt-1 text-sm font-medium text-teal-800">
                                {{ typeLabels[profile.type] || 'Care giver' }}
                            </p>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <div class="flex gap-0.5" :aria-label="profile.average_rating ? `${profile.average_rating} out of 5 stars` : 'No ratings yet'">
                                    <i
                                        v-for="star in 5"
                                        :key="star"
                                        class="fa-solid fa-star"
                                        :class="profile.average_rating && star <= Math.round(profile.average_rating)
                                            ? 'text-amber-400'
                                            : 'text-slate-300'"
                                    ></i>
                                </div>
                                <span class="text-sm font-semibold text-slate-800">
                                    {{ profile.average_rating ?? 'No rating' }}
                                </span>
                                <span class="text-sm text-slate-500">
                                    ({{ profile.review_count }} {{ profile.review_count === 1 ? 'review' : 'reviews' }})
                                </span>
                            </div>
                        </div>
                    </div>

                    <dl class="mt-5 grid gap-4 border-t border-teal-100 pt-5 sm:grid-cols-2">
                        <div v-for="row in detailRows" :key="row.label">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ row.label }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ row.value }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="mt-6">
                    <h4 class="text-base font-bold text-slate-950">Reviews</h4>

                    <div
                        v-if="!profile.reviews.length"
                        class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-5 py-8 text-center text-sm text-slate-500"
                    >
                        This care giver has no reviews yet.
                    </div>

                    <div v-else class="mt-3 max-h-80 space-y-3 overflow-y-auto pr-1">
                        <article
                            v-for="review in profile.reviews"
                            :key="review.id"
                            class="rounded-xl border border-slate-200 p-4"
                        >
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div class="flex items-center gap-3">
                                    <img
                                        v-if="review.reviewer_avatar_url"
                                        :src="review.reviewer_avatar_url"
                                        :alt="`${review.reviewer_name} profile photo`"
                                        class="h-10 w-10 shrink-0 rounded-full object-cover"
                                    />
                                    <span
                                        v-else
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-100 text-sm font-bold text-teal-800"
                                    >
                                        {{ review.reviewer_name.charAt(0) }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-950">{{ review.reviewer_name }}</p>
                                        <div class="mt-1 flex gap-0.5" :aria-label="`${review.rating} out of 5 stars`">
                                            <i
                                                v-for="star in 5"
                                                :key="star"
                                                class="fa-solid fa-star text-sm"
                                                :class="star <= review.rating ? 'text-amber-400' : 'text-slate-300'"
                                            ></i>
                                        </div>
                                    </div>
                                </div>
                                <time class="text-xs text-slate-500">{{ formatDate(review.created_at) }}</time>
                            </div>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">
                                {{ review.comment || 'No comment provided.' }}
                            </p>
                        </article>
                    </div>
                </section>
            </template>

            <div class="mt-6 flex justify-end gap-3">
                <PrimaryButton
                    v-if="profile"
                    type="button"
                    @click="$emit('message', careGiver)"
                >
                    <i class="fa-regular fa-message mr-2"></i>
                    Send message
                </PrimaryButton>
                <SecondaryButton type="button" @click="$emit('close')">Close</SecondaryButton>
            </div>
        </div>
    </Modal>
</template>
