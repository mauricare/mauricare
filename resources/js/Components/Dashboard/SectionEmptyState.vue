<script setup>
defineProps({
    icon: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        required: true,
    },
    actionLabel: {
        type: String,
        default: '',
    },
});

defineEmits(['action']);
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
        <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-teal-50 text-2xl text-teal-700">
            <i class="fa-solid" :class="icon"></i>
        </span>
        <h2 class="mt-5 text-xl font-bold text-slate-950">{{ title }}</h2>
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-600">{{ message }}</p>
        <button
            v-if="actionLabel"
            type="button"
            class="mt-6 rounded-md bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800"
            @click="$emit('action')"
        >
            {{ actionLabel }}
        </button>
    </div>
</template>
