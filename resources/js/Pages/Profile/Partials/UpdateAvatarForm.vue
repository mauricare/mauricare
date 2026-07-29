<script setup>
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';

const props = defineProps({
    avatarUrl: {
        type: String,
        default: null,
    },
    userName: {
        type: String,
        default: '',
    },
});

const input = ref(null);
const previewUrl = ref(null);
const uploadSuccessful = ref(false);
const form = useForm({ avatar: null });
const removeForm = useForm({});

const selectAvatar = () => input.value?.click();

const avatarSelected = (event) => {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    form.avatar = file;
    form.clearErrors();
    uploadSuccessful.value = false;
    previewUrl.value = URL.createObjectURL(file);
};

const submit = () => {
    if (!form.avatar || form.processing) {
        return;
    }

    uploadSuccessful.value = false;

    form.post(route('profile.avatar.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            uploadSuccessful.value = true;
            form.reset();
            previewUrl.value = null;
            if (input.value) {
                input.value.value = '';
            }
        },
    });
};

const removeAvatar = () => {
    removeForm.delete(route('profile.avatar.destroy'), {
        preserveScroll: true,
    });
};

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-xl font-bold text-slate-950">Profile photo</h2>
            <p class="mt-1 text-sm text-slate-600">Upload a JPG, PNG, or WebP image up to 5 MB.</p>
        </header>

        <form class="mt-6 flex flex-col gap-5 sm:flex-row sm:items-center" @submit.prevent="submit">
            <img
                v-if="previewUrl || avatarUrl"
                :src="previewUrl || avatarUrl"
                :alt="`${userName} profile photo`"
                class="h-24 w-24 rounded-full object-cover ring-4 ring-teal-50"
            />
            <span
                v-else
                class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-teal-100 text-3xl font-bold text-teal-800"
            >
                {{ userName?.charAt(0) }}
            </span>

            <div>
                <input
                    ref="input"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="hidden"
                    @change="avatarSelected"
                />
                <div class="flex flex-wrap gap-3">
                    <SecondaryButton type="button" @click="selectAvatar">
                        Choose image
                    </SecondaryButton>
                    <PrimaryButton
                        v-if="form.avatar"
                        type="button"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        {{ form.processing ? 'Uploading...' : 'Upload photo' }}
                    </PrimaryButton>
                    <SecondaryButton
                        v-if="avatarUrl && !form.avatar"
                        type="button"
                        :disabled="removeForm.processing"
                        @click="removeAvatar"
                    >
                        Remove
                    </SecondaryButton>
                </div>
                <p v-if="form.avatar" class="mt-2 max-w-xs truncate text-sm text-slate-600">
                    {{ form.avatar.name }}
                </p>
                <InputError class="mt-2" :message="form.errors.avatar" />
                <p v-if="uploadSuccessful" class="mt-2 text-sm font-medium text-emerald-600">
                    Profile photo updated.
                </p>
            </div>
        </form>
    </section>
</template>
