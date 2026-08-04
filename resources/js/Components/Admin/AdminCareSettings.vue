<script setup>
import { onMounted, reactive, ref } from 'vue';
import { showAdminError, showAdminSuccess } from '@/utils/adminAlerts';
import { router } from '@inertiajs/vue3';

const groups = ref({ care_types: [], carer_types: [] });
const loading = ref(false);
const newLabels = reactive({ care_type: '', carer_type: '' });

const loadOptions = async () => {
    loading.value = true;
    try {
        groups.value = (await axios.get('/api/admin/care-options')).data.data;
    } catch (error) {
        showAdminError(error.response?.data?.message || 'Unable to load care settings.');
    } finally {
        loading.value = false;
    }
};

const addOption = async (category) => {
    const label = newLabels[category].trim();
    if (!label) return;
    try {
        await axios.post('/api/admin/care-options', { category, label });
        newLabels[category] = '';
        await loadOptions();
        router.reload({ only: ['care_options'], preserveScroll: true });
        showAdminSuccess('Option added.');
    } catch (error) {
        showAdminError(error.response?.data?.message || 'Unable to add this option.');
    }
};

const saveOption = async (option) => {
    try {
        await axios.patch(`/api/admin/care-options/${option.id}`, {
            label: option.label,
            sort_order: option.sort_order,
            is_active: option.is_active,
        });
        await loadOptions();
        router.reload({ only: ['care_options'], preserveScroll: true });
        showAdminSuccess('Option updated.');
    } catch (error) {
        showAdminError(error.response?.data?.message || 'Unable to update this option.');
    }
};

onMounted(loadOptions);
</script>

<template>
    <div class="grid gap-6 xl:grid-cols-2">
        <section v-for="section in [
            { key: 'care_types', category: 'care_type', title: 'Types of care', addPlaceholder: 'Add a type of care', description: 'Options shown when creating or editing a booking.' },
            { key: 'carer_types', category: 'carer_type', title: 'Types of carer', addPlaceholder: 'Add a type of carer', description: 'Options shown for caregiver registration, profiles and bookings.' },
        ]" :key="section.key" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-200 p-5">
                <h2 class="text-lg font-bold text-slate-950">{{ section.title }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ section.description }}</p>
                <form class="mt-4 flex gap-2" @submit.prevent="addOption(section.category)">
                    <input v-model="newLabels[section.category]" maxlength="255" required :placeholder="section.addPlaceholder" class="min-w-0 flex-1 rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600" />
                    <button class="rounded-xl bg-[#117d73] px-4 py-2 text-sm font-bold text-white hover:bg-[#0d665e]">Add</button>
                </form>
            </header>
            <p v-if="loading" class="p-8 text-center text-sm text-slate-500">Loading settings…</p>
            <div v-else class="divide-y divide-slate-100">
                <form v-for="option in groups[section.key]" :key="option.id" class="grid gap-3 p-4 sm:grid-cols-[minmax(0,1fr)_88px_110px_44px] sm:items-end" @submit.prevent="saveOption(option)">
                    <label class="min-w-0 text-xs font-semibold text-slate-500">
                        Label
                        <input v-model="option.label" required maxlength="255" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600" />
                        <code class="mt-1 block truncate font-normal text-slate-400" :title="option.value">{{ option.value }}</code>
                    </label>
                    <label class="text-xs font-semibold text-slate-500">
                        Order
                        <input v-model.number="option.sort_order" type="number" min="0" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600" />
                        <span class="mt-1 block text-xs font-normal text-transparent" aria-hidden="true">value</span>
                    </label>
                    <div>
                        <span class="block text-xs font-semibold text-slate-500">Status</span>
                        <button type="button" class="mt-1 flex w-full items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold" :class="option.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600'" @click="option.is_active = !option.is_active; saveOption(option)">
                            {{ option.is_active ? 'Active' : 'Inactive' }}
                        </button>
                        <span class="mt-1 block text-xs text-transparent" aria-hidden="true">value</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-transparent" aria-hidden="true">Save</span>
                        <button type="submit" title="Save changes" aria-label="Save changes" class="mt-1 flex h-[42px] w-11 items-center justify-center rounded-lg border border-teal-200 bg-teal-50 text-teal-700 transition hover:bg-teal-100"><i class="fa-solid fa-floppy-disk"></i></button>
                        <span class="mt-1 block text-xs text-transparent" aria-hidden="true">value</span>
                    </div>
                </form>
            </div>
        </section>
    </div>
</template>
