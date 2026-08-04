<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const page = usePage();
const root = ref(null);
const searchInput = ref(null);
const reasonInput = ref(null);
const isOpen = ref(false);
const isConfirming = ref(false);
const isLoading = ref(false);
const isStarting = ref(false);
const search = ref('');
const users = ref([]);
const error = ref('');
const modalError = ref('');
const selectedUser = ref(null);
const reason = ref('');
const password = ref('');
let searchTimer = null;
let requestNumber = 0;

const isImpersonating = computed(() => Boolean(page.props.auth.impersonation?.active));

const loadUsers = async () => {
    const currentRequest = ++requestNumber;
    isLoading.value = true;
    error.value = '';

    try {
        const response = await window.axios.get(route('impersonation.users'), {
            params: {
                search: search.value.trim() || undefined,
            },
        });

        if (currentRequest === requestNumber) {
            users.value = response.data.data || [];
        }
    } catch (exception) {
        if (currentRequest === requestNumber) {
            users.value = [];
            error.value = exception.response?.data?.message || 'Unable to load users.';
        }
    } finally {
        if (currentRequest === requestNumber) {
            isLoading.value = false;
        }
    }
};

const toggle = async () => {
    isOpen.value = !isOpen.value;

    if (isOpen.value) {
        await nextTick();
        searchInput.value?.focus();
        loadUsers();
    }
};

const close = () => {
    isOpen.value = false;
};

const openConfirmation = async (user) => {
    selectedUser.value = user;
    reason.value = '';
    password.value = '';
    modalError.value = '';
    isOpen.value = false;
    isConfirming.value = true;
    await nextTick();
    reasonInput.value?.focus();
};

const closeConfirmation = () => {
    if (isStarting.value) return;

    isConfirming.value = false;
    selectedUser.value = null;
    reason.value = '';
    password.value = '';
    modalError.value = '';
};

const startImpersonation = () => {
    if (!reason.value.trim() || !password.value) {
        modalError.value = 'Enter a reason and your administrator password.';
        return;
    }

    isStarting.value = true;
    modalError.value = '';

    router.post(route('impersonation.start', selectedUser.value.id), {
        reason: reason.value.trim(),
        password: password.value,
    }, {
        preserveScroll: false,
        onError: (errors) => {
            modalError.value = errors.reason || errors.password || 'Unable to impersonate this user.';
        },
        onFinish: () => {
            isStarting.value = false;
        },
    });
};

const roleLabel = (user) => {
    if (!user.roles?.length) {
        return 'User';
    }

    return user.roles
        .map((role) => role.replaceAll('_', ' '))
        .join(', ');
};

const handleDocumentClick = (event) => {
    if (isOpen.value && !root.value?.contains(event.target)) {
        close();
    }
};

const handleKeydown = (event) => {
    if (event.key === 'Escape') {
        if (isConfirming.value) {
            closeConfirmation();
        } else {
            close();
        }
    }
};

watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadUsers, 250);
});

onMounted(() => {
    document.addEventListener('click', handleDocumentClick);
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    clearTimeout(searchTimer);
    document.removeEventListener('click', handleDocumentClick);
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div v-if="!isImpersonating" ref="root" class="relative">
        <button
            type="button"
            class="inline-flex h-10 items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-[#117d73] hover:text-[#117d73]"
            :aria-expanded="isOpen"
            aria-haspopup="dialog"
            @click="toggle"
        >
            <i class="fa-solid fa-user-secret"></i>
            <span class="hidden sm:inline">Impersonate</span>
            <i class="fa-solid fa-chevron-down text-[10px]"></i>
        </button>

        <div
            v-if="isOpen"
            class="absolute right-0 top-full z-50 mt-2 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl"
            role="dialog"
            aria-label="Choose a user to impersonate"
        >
            <div class="border-b border-slate-100 p-4">
                <p class="text-sm font-bold text-slate-950">Impersonate a user</p>
                <p class="mt-1 text-xs leading-5 text-slate-500">
                    You will see the application exactly as the selected account.
                </p>
                <label class="relative mt-3 block">
                    <span class="sr-only">Search users</span>
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input
                        ref="searchInput"
                        v-model="search"
                        type="search"
                        autocomplete="off"
                        placeholder="Search by name or email"
                        class="block w-full rounded-lg border-slate-300 py-2.5 pl-9 pr-3 text-sm focus:border-[#117d73] focus:ring-[#117d73]"
                    />
                </label>
            </div>

            <div class="max-h-80 overflow-y-auto overscroll-contain p-2">
                <div v-if="isLoading" class="px-3 py-8 text-center text-sm text-slate-500">
                    <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                    Loading users...
                </div>
                <p v-else-if="error" class="m-2 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">
                    {{ error }}
                </p>
                <p v-else-if="!users.length" class="px-3 py-8 text-center text-sm text-slate-500">
                    No users found.
                </p>
                <button
                    v-for="user in users"
                    v-else
                    :key="user.id"
                    type="button"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-3 text-left transition hover:bg-slate-50 disabled:cursor-wait disabled:opacity-60"
                    :disabled="isStarting"
                    @click="openConfirmation(user)"
                >
                    <img
                        v-if="user.avatar_url"
                        :src="user.avatar_url"
                        :alt="`${user.name} profile photo`"
                        class="h-10 w-10 shrink-0 rounded-full object-cover"
                    />
                    <span
                        v-else
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#e8f7f4] font-bold text-[#117d73]"
                    >
                        {{ user.name?.charAt(0)?.toUpperCase() }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-slate-900">{{ user.name }}</span>
                        <span class="block truncate text-xs text-slate-500">{{ user.email }}</span>
                    </span>
                    <span class="shrink-0 capitalize text-[11px] font-semibold text-slate-400">
                        {{ roleLabel(user) }}
                    </span>
                </button>
            </div>
        </div>

        <Teleport to="body">
            <div
                v-if="isConfirming"
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/55 p-4 backdrop-blur-[2px]"
                role="dialog"
                aria-modal="true"
                aria-labelledby="impersonation-modal-title"
                @click.self="closeConfirmation"
            >
                <form
                    class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl"
                    @submit.prevent="startImpersonation"
                >
                    <div class="flex items-start gap-4 border-b border-slate-100 px-6 py-5">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#e8f7f4] text-[#117d73]">
                            <i class="fa-solid fa-user-secret text-lg"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h2 id="impersonation-modal-title" class="text-lg font-bold text-slate-950">
                                Impersonate {{ selectedUser?.name }}
                            </h2>
                            <p class="mt-1 text-sm leading-5 text-slate-500">
                                This access is audited. Provide a support or care reason before continuing.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:opacity-50"
                            aria-label="Close"
                            :disabled="isStarting"
                            @click="closeConfirmation"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        <label class="block">
                            <span class="mb-1.5 block text-sm font-semibold text-slate-700">Reason for access</span>
                            <textarea
                                ref="reasonInput"
                                v-model="reason"
                                rows="3"
                                required
                                maxlength="500"
                                placeholder="Describe the support or care task..."
                                class="block w-full resize-none rounded-xl border-slate-300 text-sm focus:border-[#117d73] focus:ring-[#117d73]"
                            ></textarea>
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-sm font-semibold text-slate-700">Administrator password</span>
                            <input
                                v-model="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="block w-full rounded-xl border-slate-300 text-sm focus:border-[#117d73] focus:ring-[#117d73]"
                            />
                        </label>

                        <p v-if="modalError" class="rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700" role="alert">
                            {{ modalError }}
                        </p>
                    </div>

                    <div class="flex flex-col-reverse gap-3 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 disabled:opacity-50"
                            :disabled="isStarting"
                            @click="closeConfirmation"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#117d73] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0d665e] disabled:cursor-wait disabled:opacity-60"
                            :disabled="isStarting"
                        >
                            <i v-if="isStarting" class="fa-solid fa-spinner fa-spin"></i>
                            <i v-else class="fa-solid fa-user-secret"></i>
                            {{ isStarting ? 'Starting...' : 'Start impersonation' }}
                        </button>
                    </div>
                </form>
            </div>
        </Teleport>
    </div>
</template>
