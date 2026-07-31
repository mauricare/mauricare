<script setup>
import { useDashboardTheme } from '@/composables/useDashboardTheme';

const { isDark, toggleTheme } = useDashboardTheme();
</script>

<template>
    <button
        type="button"
        class="dashboard-theme-toggle flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-teal-300 hover:text-teal-700"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        :title="isDark ? 'Light mode' : 'Dark mode'"
        @click="toggleTheme"
    >
        <i class="fa-solid" :class="isDark ? 'fa-sun' : 'fa-moon'"></i>
    </button>
</template>
