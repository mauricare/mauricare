<script setup>
import CareGiverLayout from '@/Layouts/CareGiverLayout.vue';
import CareSeekerLayout from '@/Layouts/CareSeekerLayout.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateAvatarForm from './Partials/UpdateAvatarForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    profile: {
        type: Object,
        default: null,
    },
    role: {
        type: String,
        default: null,
    },
    roleProfile: {
        type: Object,
        default: null,
    },
    document: {
        type: Object,
        default: null,
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const accountRole = computed(() => props.role || page.props.auth.roles?.[0] || 'user');
const isCareGiver = computed(() => (page.props.auth.roles || []).includes('care_giver'));
const isAgency = computed(() => (page.props.auth.roles || []).includes('agency'));
const layoutComponent = computed(() => {
    if (isAgency.value) {
        return AuthenticatedLayout;
    }

    return isCareGiver.value ? CareGiverLayout : CareSeekerLayout;
});
</script>

<template>
    <Head title="Profile Settings" />

    <component :is="layoutComponent" active="profile">
        <template #header>
            <h1 class="text-2xl font-bold leading-tight text-slate-950 sm:text-3xl">Profile Settings</h1>
            <p class="mt-2 text-base text-slate-600">
                Manage your personal information, password and account.
            </p>
        </template>

        <div class="mt-8 grid gap-6 xl:grid-cols-[300px_minmax(0,1fr)]">
            <section class="h-fit rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                <img
                    v-if="user.avatar_url"
                    :src="user.avatar_url"
                    :alt="`${user.name} profile photo`"
                    class="mx-auto h-24 w-24 rounded-full object-cover ring-4 ring-teal-50"
                />
                <span v-else class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-teal-100 text-3xl font-bold text-teal-800">
                    {{ user.name?.charAt(0) }}
                </span>
                <h2 class="mt-4 text-xl font-bold text-slate-950">{{ user.name }}</h2>
                <p class="mt-1 text-sm text-slate-600">{{ user.email }}</p>
                <span class="mt-4 inline-flex items-center gap-2 rounded-full bg-teal-50 px-4 py-1.5 text-xs font-bold text-teal-800">
                    <i class="fa-solid" :class="isCareGiver ? 'fa-user-nurse' : 'fa-heart'"></i>
                    {{ isAgency ? 'Agency' : isCareGiver ? 'Care Giver' : 'Care Seeker' }}
                </span>
            </section>

            <div class="space-y-6">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <UpdateAvatarForm :avatar-url="user.avatar_url" :user-name="user.name" />
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <UpdateProfileInformationForm
                        :profile="profile"
                        :role="accountRole"
                        :role-profile="roleProfile"
                        :document="document"
                    />
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <UpdatePasswordForm />
                </div>

                <div class="rounded-xl border border-rose-100 bg-white p-6 shadow-sm sm:p-8">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </component>
</template>
