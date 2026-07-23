<script setup>
import CareSeekerLayout from '@/Layouts/CareSeekerLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
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
});

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Profile Settings" />

    <CareSeekerLayout active="profile">
        <template #header>
            <h1 class="text-3xl font-bold leading-tight text-slate-950">Profile Settings</h1>
            <p class="mt-2 text-base text-slate-600">
                Manage your personal information, password and account.
            </p>
        </template>

        <div class="mt-8 grid gap-6 xl:grid-cols-[300px_minmax(0,1fr)]">
            <section class="h-fit rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
                <span class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-teal-100 text-3xl font-bold text-teal-800">
                    {{ user.name?.charAt(0) }}
                </span>
                <h2 class="mt-4 text-xl font-bold text-slate-950">{{ user.name }}</h2>
                <p class="mt-1 text-sm text-slate-600">{{ user.email }}</p>
                <span class="mt-4 inline-flex items-center gap-2 rounded-full bg-teal-50 px-4 py-1.5 text-xs font-bold text-teal-800">
                    <i class="fa-solid fa-heart"></i>
                    Care Seeker
                </span>
            </section>

            <div class="space-y-6">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <UpdateProfileInformationForm :profile="profile" />
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <UpdatePasswordForm />
                </div>

                <div class="rounded-xl border border-rose-100 bg-white p-6 shadow-sm sm:p-8">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </CareSeekerLayout>
</template>
