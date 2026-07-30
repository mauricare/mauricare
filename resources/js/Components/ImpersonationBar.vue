<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const isStopping = ref(false);

const impersonation = computed(() => page.props.auth?.impersonation);
const currentUser = computed(() => page.props.auth?.user);

const stopImpersonation = () => {
    isStopping.value = true;

    router.post(route('impersonation.stop'), {}, {
        preserveScroll: false,
        onFinish: () => {
            isStopping.value = false;
        },
    });
};
</script>

<template>
    <aside
        v-if="impersonation?.active"
        class="fixed bottom-4 right-4 z-[100] flex max-w-[calc(100vw-2rem)] items-center gap-3 rounded-xl border border-amber-300 bg-amber-50 p-2 pl-4 text-slate-900 shadow-2xl"
        aria-label="Impersonation controls"
    >
        <i class="fa-solid fa-user-secret shrink-0 text-amber-700"></i>
        <p class="min-w-0 text-sm">
            <span class="hidden sm:inline">Viewing as </span>
            <strong class="truncate">{{ currentUser?.name }}</strong>
        </p>
        <button
            type="button"
            class="shrink-0 rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white transition hover:bg-slate-700 disabled:cursor-wait disabled:opacity-60 sm:text-sm"
            :disabled="isStopping"
            @click="stopImpersonation"
        >
            <i
                class="fa-solid mr-1.5"
                :class="isStopping ? 'fa-spinner fa-spin' : 'fa-arrow-right-from-bracket'"
            ></i>
            End impersonation
        </button>
    </aside>
</template>
