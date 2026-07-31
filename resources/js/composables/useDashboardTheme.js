import { computed, ref } from 'vue';

const storageKey = 'mauricare-dashboard-theme';
const storedTheme = typeof window !== 'undefined' ? window.localStorage.getItem(storageKey) : null;
const prefersDark = typeof window !== 'undefined'
    && window.matchMedia('(prefers-color-scheme: dark)').matches;
const theme = ref(storedTheme || (prefersDark ? 'dark' : 'light'));

export const useDashboardTheme = () => {
    const isDark = computed(() => theme.value === 'dark');

    const toggleTheme = () => {
        theme.value = isDark.value ? 'light' : 'dark';
        window.localStorage.setItem(storageKey, theme.value);
    };

    return {
        isDark,
        toggleTheme,
    };
};
