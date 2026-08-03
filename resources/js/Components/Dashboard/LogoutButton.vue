<script setup>
import { router } from '@inertiajs/vue3';
import { useDashboardTheme } from '@/composables/useDashboardTheme';
import { ref } from 'vue';

defineProps({
    buttonClass: {
        type: String,
        default: '',
    },
});

const showConfirmation = ref(false);
const isLoggingOut = ref(false);
const { isDark } = useDashboardTheme();

const close = () => {
    if (!isLoggingOut.value) showConfirmation.value = false;
};

const logout = () => {
    if (isLoggingOut.value) return;
    isLoggingOut.value = true;
    router.post(route('logout'), {}, {
        onFinish: () => {
            isLoggingOut.value = false;
        },
    });
};
</script>

<template>
    <button type="button" :class="buttonClass" @click="showConfirmation = true">
        <slot>
            <i class="fa-solid fa-right-from-bracket"></i>
            Log out
        </slot>
    </button>

    <Teleport to="body">
        <div
            v-if="showConfirmation"
            class="dashboard-theme fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/70 p-4"
            :class="{ 'dashboard-dark': isDark }"
            role="dialog"
            aria-modal="true"
            aria-labelledby="logout-confirmation-title"
            @click.self="close"
        >
            <div class="w-full max-w-md overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
            <div class="p-6 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-100 text-xl text-rose-700">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </span>
                <h2 id="logout-confirmation-title" class="mt-4 text-xl font-bold text-slate-950">Log out of Mauricare?</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">You will need to sign in again to access your dashboard.</p>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                <button type="button" class="rounded-md border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-white disabled:opacity-50" :disabled="isLoggingOut" @click="close">
                    Stay signed in
                </button>
                <button type="button" class="rounded-md bg-rose-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-rose-700 disabled:opacity-60" :disabled="isLoggingOut" @click="logout">
                    <i class="fa-solid mr-2" :class="isLoggingOut ? 'fa-spinner fa-spin' : 'fa-right-from-bracket'"></i>
                    {{ isLoggingOut ? 'Logging out…' : 'Yes, log out' }}
                </button>
            </div>
            </div>
        </div>
    </Teleport>
</template>
