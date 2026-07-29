<script setup>
import AdminUserModal from '@/Components/Admin/AdminUserModal.vue';
import { confirmAdminAction, showAdminError, showAdminSuccess } from '@/utils/adminAlerts';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
});

const users = ref([]);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const loading = ref(false);
const error = ref('');
const selectedUserId = ref(null);
const modalMode = ref('view');
let searchTimer;

const endpoint = computed(() => props.type === 'care_giver' ? '/api/admin/care-givers' : '/api/admin/care-seekers');
const singularLabel = computed(() => props.type === 'care_giver' ? 'care giver' : 'care seeker');

const loadUsers = async (page = 1) => {
    loading.value = true;
    error.value = '';
    try {
        const response = await window.axios.get(endpoint.value, {
            params: { search: search.value || undefined, page, per_page: 10 },
        });
        users.value = response.data.data;
        meta.value = response.data.meta;
    } catch (exception) {
        error.value = exception.response?.data?.message || 'Unable to load users.';
    } finally {
        loading.value = false;
    }
};

watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadUsers(1), 350);
});
watch(() => props.type, () => {
    search.value = '';
    loadUsers(1);
});
onMounted(() => loadUsers());

const openModal = (user, mode) => {
    selectedUserId.value = user.id;
    modalMode.value = mode;
};

const toggleStatus = async (user) => {
    const action = user.is_active ? 'deactivate' : 'activate';
    const confirmed = await confirmAdminAction({
        title: `${user.is_active ? 'Deactivate' : 'Activate'} user?`,
        text: user.is_active
            ? `${user.name} will no longer be able to use active care features.`
            : `${user.name} will be allowed to use active care features.`,
        confirmText: user.is_active ? 'Yes, deactivate' : 'Yes, activate',
        icon: user.is_active ? 'warning' : 'question',
    });

    if (!confirmed) return;

    try {
        const response = await window.axios.patch(`/api/admin/users/${user.id}/status`, {
            is_active: !user.is_active,
        });
        user.is_active = response.data.is_active;
        showAdminSuccess(`${user.name} has been ${user.is_active ? 'activated' : 'deactivated'}.`);
    } catch (exception) {
        showAdminError(exception.response?.data?.message || `Unable to ${action} this user.`);
    }
};

const deleteUser = async (user) => {
    const confirmed = await confirmAdminAction({
        title: 'Delete this user?',
        text: `${user.name} and their related data will be permanently deleted. This cannot be undone.`,
        confirmText: 'Yes, delete user',
        destructive: true,
    });

    if (!confirmed) return;

    try {
        await window.axios.delete(`/api/admin/users/${user.id}`);
        await loadUsers(users.value.length === 1 && meta.value.current_page > 1 ? meta.value.current_page - 1 : meta.value.current_page);
        showAdminSuccess(`${user.name} has been deleted.`);
    } catch (exception) {
        showAdminError(exception.response?.data?.message || 'Unable to delete this user.');
    }
};

const initials = (name) => name?.split(' ').slice(0, 2).map((part) => part[0]).join('').toUpperCase();
const formatDate = (date) => date ? new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium' }).format(new Date(date)) : '—';
</script>

<template>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold capitalize">{{ type === 'care_giver' ? 'Care Givers' : 'Care Seekers' }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ meta.total }} registered {{ meta.total === 1 ? singularLabel : `${singularLabel}s` }}</p>
            </div>
            <div class="relative w-full sm:w-80">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                <input v-model="search" type="search" :placeholder="`Search ${singularLabel}s...`" class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm focus:border-[#117d73] focus:outline-none focus:ring-2 focus:ring-[#117d73]/20" />
            </div>
        </div>

        <div v-if="error" class="m-5 rounded-lg bg-red-50 p-4 text-sm text-red-700">{{ error }}</div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[920px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Phone</th>
                        <th class="px-5 py-3">City</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Joined</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="loading">
                        <td colspan="6" class="px-5 py-14 text-center text-slate-500"><i class="fa-solid fa-spinner fa-spin mr-2"></i> Loading users...</td>
                    </tr>
                    <tr v-else-if="!users.length">
                        <td colspan="6" class="px-5 py-14 text-center text-slate-500">No {{ singularLabel }}s found.</td>
                    </tr>
                    <tr v-for="user in users" v-else :key="user.id" class="hover:bg-slate-50/70">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" class="h-10 w-10 rounded-full object-cover" />
                                <div v-else class="flex h-10 w-10 items-center justify-center rounded-full bg-[#dff5f1] text-xs font-bold text-[#117d73]">{{ initials(user.name) }}</div>
                                <div><p class="font-semibold text-slate-900">{{ user.name }}</p><p class="text-xs text-slate-500">{{ user.email }}</p></div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ user.profile?.phone || '—' }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ user.profile?.city || '—' }}</td>
                        <td class="px-5 py-4">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" :class="user.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'">{{ user.is_active ? 'Active' : 'Inactive' }}</span>
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ formatDate(user.created_at) }}</td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-1">
                                <button type="button" title="View profile" class="rounded-lg p-2 text-slate-500 hover:bg-sky-50 hover:text-sky-700" @click="openModal(user, 'view')"><i class="fa-solid fa-eye"></i></button>
                                <button type="button" title="Edit" class="rounded-lg p-2 text-slate-500 hover:bg-amber-50 hover:text-amber-700" @click="openModal(user, 'edit')"><i class="fa-solid fa-pen"></i></button>
                                <button type="button" :title="user.is_active ? 'Deactivate' : 'Activate'" class="rounded-lg p-2" :class="user.is_active ? 'text-emerald-600 hover:bg-slate-100 hover:text-slate-600' : 'text-slate-400 hover:bg-emerald-50 hover:text-emerald-700'" @click="toggleStatus(user)"><i class="fa-solid fa-power-off"></i></button>
                                <button type="button" title="Delete" class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-red-700" @click="deleteUser(user)"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="meta.last_page > 1" class="flex items-center justify-between border-t border-slate-200 px-5 py-4">
            <p class="text-sm text-slate-500">Page {{ meta.current_page }} of {{ meta.last_page }}</p>
            <div class="flex gap-2">
                <button type="button" :disabled="meta.current_page <= 1" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold disabled:opacity-40" @click="loadUsers(meta.current_page - 1)">Previous</button>
                <button type="button" :disabled="meta.current_page >= meta.last_page" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold disabled:opacity-40" @click="loadUsers(meta.current_page + 1)">Next</button>
            </div>
        </div>
    </section>

    <AdminUserModal
        :show="selectedUserId !== null"
        :user-id="selectedUserId"
        :mode="modalMode"
        @close="selectedUserId = null"
        @saved="loadUsers(meta.current_page)"
    />
</template>
