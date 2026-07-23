<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    profile: {
        type: Object,
        default: null,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    first_name: props.profile?.first_name || user.name?.split(' ')[0] || '',
    last_name: props.profile?.last_name || user.name?.split(' ').slice(1).join(' ') || '',
    email: user.email,
    age: props.profile?.age === null || props.profile?.age === undefined ? '' : String(props.profile.age),
    phone: props.profile?.phone || '',
    address: props.profile?.address || '',
    city: props.profile?.city || '',
});

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-xl font-bold text-slate-950">Personal Information</h2>
            <p class="mt-1 text-sm text-slate-600">
                Keep your contact details up to date so care providers can reach you.
            </p>
        </header>

        <form class="mt-6 space-y-5" @submit.prevent="submit">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <InputLabel for="first_name" value="First name" />
                    <TextInput
                        id="first_name"
                        v-model="form.first_name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autocomplete="given-name"
                    />
                    <InputError class="mt-2" :message="form.errors.first_name" />
                </div>

                <div>
                    <InputLabel for="last_name" value="Last name" />
                    <TextInput
                        id="last_name"
                        v-model="form.last_name"
                        type="text"
                        class="mt-1 block w-full"
                        autocomplete="family-name"
                    />
                    <InputError class="mt-2" :message="form.errors.last_name" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full"
                        required
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="phone" value="Phone" />
                    <TextInput
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        class="mt-1 block w-full"
                        required
                        autocomplete="tel"
                    />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>

                <div>
                    <InputLabel for="age" value="Age" />
                    <TextInput
                        id="age"
                        v-model="form.age"
                        type="number"
                        min="0"
                        max="120"
                        class="mt-1 block w-full"
                    />
                    <InputError class="mt-2" :message="form.errors.age" />
                </div>

                <div>
                    <InputLabel for="city" value="City" />
                    <TextInput
                        id="city"
                        v-model="form.city"
                        type="text"
                        class="mt-1 block w-full"
                        autocomplete="address-level2"
                    />
                    <InputError class="mt-2" :message="form.errors.city" />
                </div>

                <div class="sm:col-span-2">
                    <InputLabel for="address" value="Address" />
                    <TextInput
                        id="address"
                        v-model="form.address"
                        type="text"
                        class="mt-1 block w-full"
                        autocomplete="street-address"
                    />
                    <InputError class="mt-2" :message="form.errors.address" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save changes</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm font-medium text-emerald-600">
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
