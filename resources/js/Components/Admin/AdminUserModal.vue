<script setup>
import Modal from '@/Components/Modal.vue';
import { statusClasses, statusLabels } from '@/constants/careBookings';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    userId: Number,
    mode: {
        type: String,
        default: 'view',
    },
});

const emit = defineEmits(['close', 'saved']);
const user = ref(null);
const loading = ref(false);
const saving = ref(false);
const error = ref('');
const errors = ref({});
const form = reactive({});

const isCareGiver = computed(() => user.value?.role === 'care_giver');
const title = computed(() => props.mode === 'edit' ? 'Edit user' : 'User profile');

const populateForm = () => {
    if (!user.value) return;
    Object.assign(form, {
        first_name: user.value.profile?.first_name || '',
        last_name: user.value.profile?.last_name || '',
        email: user.value.email || '',
        age: user.value.profile?.age ?? '',
        phone: user.value.profile?.phone || '',
        address: user.value.profile?.address || '',
        city: user.value.profile?.city || '',
        care_giver_type: user.value.role_profile?.type || '',
        care_for: user.value.role_profile?.care_for || '',
        care_needs: user.value.role_profile?.care_needs || '',
        preferred_contact_method: user.value.role_profile?.preferred_contact_method || '',
        emergency_contact_name: user.value.role_profile?.emergency_contact_name || '',
        emergency_contact_phone: user.value.role_profile?.emergency_contact_phone || '',
        mobility_level: user.value.role_profile?.mobility_level || '',
        medical_notes: user.value.role_profile?.medical_notes || '',
    });
};

const loadUser = async () => {
    if (!props.userId) return;
    loading.value = true;
    error.value = '';
    try {
        const response = await window.axios.get(`/api/admin/users/${props.userId}`);
        user.value = response.data.data;
        populateForm();
    } catch (exception) {
        error.value = exception.response?.data?.message || 'Unable to load this profile.';
    } finally {
        loading.value = false;
    }
};

watch(() => [props.show, props.userId], ([show]) => {
    if (show) loadUser();
}, { immediate: true });

const save = async () => {
    saving.value = true;
    errors.value = {};
    error.value = '';
    try {
        const response = await window.axios.patch(`/api/admin/users/${props.userId}`, form);
        user.value = response.data.data;
        emit('saved', user.value);
        emit('close');
    } catch (exception) {
        errors.value = exception.response?.data?.errors || {};
        error.value = exception.response?.data?.message || 'Unable to save these changes.';
    } finally {
        saving.value = false;
    }
};

const fieldClass = 'mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-[#117d73] focus:outline-none focus:ring-2 focus:ring-[#117d73]/20';
const labelClass = 'block text-sm font-medium text-slate-700';
const formatDate = (date) => date
    ? new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium' }).format(new Date(date))
    : '';
const formatFileSize = (bytes) => {
    if (!bytes) return '';
    if (bytes < 1024 * 1024) return `${Math.ceil(bytes / 1024)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};
const documentTypeLabel = (type) => ({
    cv: 'CV',
    agency_license: 'Agency licence',
}[type] || 'Document');
const detailRows = computed(() => {
    if (!user.value) return [];
    const common = [
        ['Email', user.value.email],
        ['Phone', user.value.profile?.phone],
        ['Age', user.value.profile?.age],
        ['Address', user.value.profile?.address],
        ['City', user.value.profile?.city],
    ];
    if (isCareGiver.value) {
        return [...common, ['Care giver type', user.value.role_profile?.type]];
    }
    return [
        ...common,
        ['Care for', user.value.role_profile?.care_for],
        ['Care needs', user.value.role_profile?.care_needs],
        ['Preferred contact', user.value.role_profile?.preferred_contact_method],
        ['Emergency contact', user.value.role_profile?.emergency_contact_name],
        ['Emergency phone', user.value.role_profile?.emergency_contact_phone],
        ['Mobility level', user.value.role_profile?.mobility_level],
        ['Medical notes', user.value.role_profile?.medical_notes],
    ];
});
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="emit('close')">
        <div class="max-h-[85vh] overflow-y-auto p-6">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-xl font-bold text-slate-900">{{ title }}</h2>
                <button type="button" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700" @click="emit('close')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div v-if="loading" class="py-16 text-center text-slate-500">
                <i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading profile...
            </div>
            <div v-else-if="error && !user" class="rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ error }}</div>
            <template v-else-if="user">
                <div class="mb-6 flex items-center gap-4 rounded-xl bg-slate-50 p-4">
                    <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" class="h-20 w-20 rounded-full object-cover" />
                    <div v-else class="flex h-20 w-20 items-center justify-center rounded-full bg-[#dff5f1] text-2xl font-bold text-[#117d73]">
                        {{ user.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div>
                        <h3 class="text-lg font-bold">{{ user.name }}</h3>
                        <p class="text-sm text-slate-500">{{ isCareGiver ? 'Care Giver' : 'Care Seeker' }}</p>
                        <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="user.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'">
                            {{ user.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <div v-if="mode === 'view'" class="grid gap-4 sm:grid-cols-2">
                    <section class="sm:col-span-2">
                        <div class="mb-3 flex items-center justify-between">
                            <h4 class="text-sm font-bold text-slate-900">Bookings by status</h4>
                            <span class="text-xs font-semibold text-slate-500">
                                {{ user.booking_total || 0 }} total
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            <div
                                v-for="(label, status) in statusLabels"
                                :key="status"
                                class="rounded-xl border border-slate-200 p-3"
                            >
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    :class="statusClasses[status]"
                                >
                                    {{ label }}
                                </span>
                                <p class="mt-3 text-2xl font-bold text-slate-900">
                                    {{ user.booking_counts?.[status] || 0 }}
                                </p>
                            </div>
                        </div>
                    </section>
                    <section v-if="isCareGiver" class="sm:col-span-2">
                        <h4 class="mb-3 text-sm font-bold text-slate-900">Invoice summary</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-slate-200 p-4">
                                <span class="inline-flex rounded-full bg-teal-100 px-2 py-1 text-xs font-semibold text-teal-700">
                                    Invoices generated
                                </span>
                                <p class="mt-3 text-2xl font-bold text-slate-900">{{ user.invoice_count || 0 }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-4">
                                <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700">
                                    Invoices paid
                                </span>
                                <p class="mt-3 text-2xl font-bold text-slate-900">{{ user.paid_invoice_count || 0 }}</p>
                            </div>
                        </div>
                    </section>
                    <section class="sm:col-span-2">
                        <h4 class="mb-3 text-sm font-bold text-slate-900">Documents</h4>
                        <div
                            v-if="!user.documents?.length"
                            class="rounded-xl border border-slate-200 bg-slate-50 px-5 py-6 text-center text-sm text-slate-500"
                        >
                            No documents uploaded.
                        </div>
                        <div v-else class="space-y-3">
                            <article
                                v-for="document in user.documents"
                                :key="document.id"
                                class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-4"
                            >
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-[#117d73]">
                                        <i class="fa-solid fa-file-shield"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ document.name }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ documentTypeLabel(document.type) }}
                                            <span v-if="document.size"> · {{ formatFileSize(document.size) }}</span>
                                        </p>
                                    </div>
                                </div>
                                <a
                                    :href="document.download_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 rounded-lg bg-[#117d73] px-3 py-2 text-sm font-semibold text-white hover:bg-[#0d6c63]"
                                >
                                    <i class="fa-solid fa-download"></i>
                                    View
                                </a>
                            </article>
                        </div>
                    </section>
                    <div v-for="[label, value] in detailRows" :key="label" class="rounded-lg border border-slate-200 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ label }}</p>
                        <p class="mt-1 whitespace-pre-wrap text-sm text-slate-800">{{ value || '—' }}</p>
                    </div>
                    <section v-if="isCareGiver" class="sm:col-span-2">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                            <h4 class="text-sm font-bold text-slate-900">Reviews received</h4>
                            <div class="flex items-center gap-2 text-sm">
                                <div class="flex gap-0.5">
                                    <i
                                        v-for="star in 5"
                                        :key="star"
                                        class="fa-solid fa-star"
                                        :class="user.average_rating && star <= Math.round(user.average_rating)
                                            ? 'text-amber-400'
                                            : 'text-slate-300'"
                                    ></i>
                                </div>
                                <span class="font-bold text-slate-800">{{ user.average_rating ?? 'No rating' }}</span>
                                <span class="text-slate-500">
                                    ({{ user.review_count || 0 }} {{ user.review_count === 1 ? 'review' : 'reviews' }})
                                </span>
                            </div>
                        </div>

                        <div
                            v-if="!user.reviews?.length"
                            class="rounded-xl border border-slate-200 bg-slate-50 px-5 py-8 text-center text-sm text-slate-500"
                        >
                            This care giver has not received any reviews yet.
                        </div>
                        <div v-else class="space-y-3">
                            <article
                                v-for="review in user.reviews"
                                :key="review.id"
                                class="rounded-xl border border-slate-200 p-4"
                            >
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <img
                                            v-if="review.reviewer_avatar_url"
                                            :src="review.reviewer_avatar_url"
                                            :alt="`${review.reviewer_name} profile photo`"
                                            class="h-10 w-10 rounded-full object-cover"
                                        />
                                        <span
                                            v-else
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-[#dff5f1] text-sm font-bold text-[#117d73]"
                                        >
                                            {{ review.reviewer_name?.charAt(0)?.toUpperCase() }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">{{ review.reviewer_name }}</p>
                                            <div class="mt-1 flex gap-0.5">
                                                <i
                                                    v-for="star in 5"
                                                    :key="star"
                                                    class="fa-solid fa-star text-xs"
                                                    :class="star <= review.rating ? 'text-amber-400' : 'text-slate-300'"
                                                ></i>
                                            </div>
                                        </div>
                                    </div>
                                    <time class="text-xs text-slate-500">{{ formatDate(review.created_at) }}</time>
                                </div>
                                <p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-slate-700">
                                    {{ review.comment || 'No comment provided.' }}
                                </p>
                            </article>
                        </div>
                    </section>
                </div>

                <form v-else class="space-y-5" @submit.prevent="save">
                    <div v-if="error" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ error }}</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label :class="labelClass">First name
                            <input v-model="form.first_name" :class="fieldClass" />
                            <span v-if="errors.first_name" class="mt-1 block text-xs text-red-600">{{ errors.first_name[0] }}</span>
                        </label>
                        <label :class="labelClass">Last name
                            <input v-model="form.last_name" :class="fieldClass" />
                            <span v-if="errors.last_name" class="mt-1 block text-xs text-red-600">{{ errors.last_name[0] }}</span>
                        </label>
                        <label :class="labelClass">Email
                            <input v-model="form.email" type="email" :class="fieldClass" />
                            <span v-if="errors.email" class="mt-1 block text-xs text-red-600">{{ errors.email[0] }}</span>
                        </label>
                        <label :class="labelClass">Phone
                            <input v-model="form.phone" :class="fieldClass" />
                        </label>
                        <label :class="labelClass">Age
                            <input v-model="form.age" type="number" min="0" max="120" :class="fieldClass" />
                        </label>
                        <label :class="labelClass">City
                            <input v-model="form.city" :class="fieldClass" />
                        </label>
                    </div>
                    <template v-if="isCareGiver">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label :class="labelClass">Address
                                <input v-model="form.address" :class="fieldClass" />
                            </label>
                            <label :class="labelClass">Care giver type
                                <select v-model="form.care_giver_type" :class="fieldClass">
                                    <option value="">Select a type</option>
                                    <option value="doctor">Doctor</option>
                                    <option value="nurse">Nurse</option>
                                    <option value="carers">Carer</option>
                                    <option value="physiotherapist">Physiotherapist</option>
                                    <option value="other">Other</option>
                                </select>
                            </label>
                        </div>
                    </template>
                    <template v-else>
                        <label :class="labelClass">Address
                            <input v-model="form.address" :class="fieldClass" />
                        </label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label :class="labelClass">Who needs care?
                                <input v-model="form.care_for" :class="fieldClass" />
                            </label>
                            <label :class="labelClass">Preferred contact
                                <select v-model="form.preferred_contact_method" :class="fieldClass">
                                    <option value="">Not specified</option>
                                    <option value="phone">Phone</option>
                                    <option value="email">Email</option>
                                </select>
                            </label>
                            <label :class="labelClass">Emergency contact
                                <input v-model="form.emergency_contact_name" :class="fieldClass" />
                            </label>
                            <label :class="labelClass">Emergency phone
                                <input v-model="form.emergency_contact_phone" :class="fieldClass" />
                            </label>
                            <label :class="labelClass">Mobility level
                                <input v-model="form.mobility_level" :class="fieldClass" />
                            </label>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label :class="labelClass">Care needs
                                <textarea v-model="form.care_needs" rows="3" :class="fieldClass"></textarea>
                            </label>
                            <label :class="labelClass">Medical notes
                                <textarea v-model="form.medical_notes" rows="3" :class="fieldClass"></textarea>
                            </label>
                        </div>
                    </template>

                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-5">
                        <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="emit('close')">Cancel</button>
                        <button type="submit" :disabled="saving" class="rounded-lg bg-[#117d73] px-5 py-2 text-sm font-semibold text-white hover:bg-[#0d6c63] disabled:opacity-60">
                            <i v-if="saving" class="fa-solid fa-spinner fa-spin mr-2"></i>
                            Save changes
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </Modal>
</template>
