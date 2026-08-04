<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useCareOptions } from '@/composables/useCareOptions';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    profile: {
        type: Object,
        default: null,
    },
    role: {
        type: String,
        required: true,
    },
    roleProfile: {
        type: Object,
        default: null,
    },
    document: {
        type: Object,
        default: null,
    },
});
const { carerTypes } = useCareOptions();

const user = usePage().props.auth.user;

const form = useForm({
    first_name: props.profile?.first_name || user.name?.split(' ')[0] || '',
    last_name: props.profile?.last_name || user.name?.split(' ').slice(1).join(' ') || '',
    email: user.email,
    age: props.profile?.age === null || props.profile?.age === undefined ? '' : String(props.profile.age),
    phone: props.profile?.phone || '',
    address: props.profile?.address || '',
    city: props.profile?.city || '',
    care_giver_type: props.roleProfile?.type || '',
    cv: null,
    care_for: props.roleProfile?.care_for || '',
    care_needs: props.roleProfile?.care_needs || '',
    preferred_contact_method: props.roleProfile?.preferred_contact_method || '',
    emergency_contact_name: props.roleProfile?.emergency_contact_name || '',
    emergency_contact_phone: props.roleProfile?.emergency_contact_phone || '',
    mobility_level: props.roleProfile?.mobility_level || '',
    medical_notes: props.roleProfile?.medical_notes || '',
    agency_name: props.roleProfile?.agency_name || user.name || '',
    contact_person: props.roleProfile?.contact_person || props.profile?.first_name || '',
    agency_address: props.roleProfile?.agency_address || props.profile?.address || '',
    services_offered: props.roleProfile?.services_offered || '',
    agency_license: null,
    _method: 'patch',
});

const submit = () => {
    form.post(route('profile.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => form.reset('cv', 'agency_license'),
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-xl font-bold text-slate-950">
                {{ role === 'agency' ? 'Agency Information' : 'Personal Information' }}
            </h2>
            <p class="mt-1 text-sm text-slate-600">
                Keep your account and {{ role.replace('_', ' ') }} details up to date.
            </p>
        </header>

        <form class="mt-6 space-y-5" @submit.prevent="submit">
            <div class="grid gap-5 sm:grid-cols-2">
                <div v-if="role !== 'agency'">
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

                <div v-if="role !== 'agency'">
                    <InputLabel for="last_name" value="Last name" />
                    <TextInput
                        id="last_name"
                        v-model="form.last_name"
                        type="text"
                        class="mt-1 block w-full"
                        required
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

                <div v-if="role !== 'agency'">
                    <InputLabel for="age" value="Age" />
                    <TextInput
                        id="age"
                        v-model="form.age"
                        type="number"
                        min="0"
                        max="120"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.age" />
                </div>

                <div v-if="role !== 'agency'">
                    <InputLabel for="city" value="City" />
                    <TextInput
                        id="city"
                        v-model="form.city"
                        type="text"
                        class="mt-1 block w-full"
                        required
                        autocomplete="address-level2"
                    />
                    <InputError class="mt-2" :message="form.errors.city" />
                </div>

                <div v-if="role !== 'agency'" class="sm:col-span-2">
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

            <div v-if="role === 'care_giver'" class="rounded-xl border border-teal-100 bg-teal-50/40 p-5">
                <h3 class="font-bold text-slate-950">Care giver details</h3>
                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    <div>
                        <InputLabel for="care_giver_type" value="Type" />
                        <select
                            id="care_giver_type"
                            v-model="form.care_giver_type"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            required
                        >
                            <option value="" disabled>Select a type</option>
                            <option v-for="type in carerTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.care_giver_type" />
                    </div>
                    <div>
                        <InputLabel for="cv" value="CV (PDF, DOC, or DOCX)" />
                        <input
                            id="cv"
                            type="file"
                            accept=".pdf,.doc,.docx"
                            class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:font-semibold file:text-teal-800"
                            @input="form.cv = $event.target.files[0]"
                        />
                        <a v-if="document" :href="document.url" target="_blank" class="mt-2 block text-sm font-medium text-teal-700 underline">
                            Current: {{ document.name }}
                        </a>
                        <InputError class="mt-2" :message="form.errors.cv" />
                    </div>
                </div>
            </div>

            <div v-if="role === 'care_seeker'" class="rounded-xl border border-teal-100 bg-teal-50/40 p-5">
                <h3 class="font-bold text-slate-950">Care requirements</h3>
                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    <div>
                        <InputLabel for="care_for" value="Who needs care?" />
                        <TextInput id="care_for" v-model="form.care_for" class="mt-1 block w-full" required />
                        <InputError class="mt-2" :message="form.errors.care_for" />
                    </div>
                    <div>
                        <InputLabel for="preferred_contact_method" value="Preferred contact" />
                        <select id="preferred_contact_method" v-model="form.preferred_contact_method" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                            <option value="">Any</option>
                            <option value="phone">Phone</option>
                            <option value="email">Email</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.preferred_contact_method" />
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel for="care_needs" value="Care needed" />
                        <textarea id="care_needs" v-model="form.care_needs" rows="4" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500" required></textarea>
                        <InputError class="mt-2" :message="form.errors.care_needs" />
                    </div>
                    <div>
                        <InputLabel for="emergency_contact_name" value="Emergency contact name" />
                        <TextInput id="emergency_contact_name" v-model="form.emergency_contact_name" class="mt-1 block w-full" />
                        <InputError class="mt-2" :message="form.errors.emergency_contact_name" />
                    </div>
                    <div>
                        <InputLabel for="emergency_contact_phone" value="Emergency contact phone" />
                        <TextInput id="emergency_contact_phone" v-model="form.emergency_contact_phone" type="tel" class="mt-1 block w-full" />
                        <InputError class="mt-2" :message="form.errors.emergency_contact_phone" />
                    </div>
                    <div>
                        <InputLabel for="mobility_level" value="Mobility level" />
                        <TextInput id="mobility_level" v-model="form.mobility_level" class="mt-1 block w-full" />
                        <InputError class="mt-2" :message="form.errors.mobility_level" />
                    </div>
                    <div>
                        <InputLabel for="medical_notes" value="Medical notes" />
                        <textarea id="medical_notes" v-model="form.medical_notes" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                        <InputError class="mt-2" :message="form.errors.medical_notes" />
                    </div>
                </div>
            </div>

            <div v-if="role === 'agency'" class="grid gap-5 sm:grid-cols-2">
                <div>
                    <InputLabel for="agency_name" value="Agency name" />
                    <TextInput id="agency_name" v-model="form.agency_name" class="mt-1 block w-full" required />
                    <InputError class="mt-2" :message="form.errors.agency_name" />
                </div>
                <div>
                    <InputLabel for="contact_person" value="Contact person" />
                    <TextInput id="contact_person" v-model="form.contact_person" class="mt-1 block w-full" required />
                    <InputError class="mt-2" :message="form.errors.contact_person" />
                </div>
                <div class="sm:col-span-2">
                    <InputLabel for="agency_address" value="Agency address" />
                    <TextInput id="agency_address" v-model="form.agency_address" class="mt-1 block w-full" required />
                    <InputError class="mt-2" :message="form.errors.agency_address" />
                </div>
                <div class="sm:col-span-2">
                    <InputLabel for="services_offered" value="Services offered" />
                    <textarea id="services_offered" v-model="form.services_offered" rows="4" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500" required></textarea>
                    <InputError class="mt-2" :message="form.errors.services_offered" />
                </div>
                <div class="sm:col-span-2">
                    <InputLabel for="agency_license" value="Agency license (PDF, image, DOC, or DOCX)" />
                    <input
                        id="agency_license"
                        type="file"
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                        class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:font-semibold file:text-teal-800"
                        @input="form.agency_license = $event.target.files[0]"
                    />
                    <a v-if="document" :href="document.url" target="_blank" class="mt-2 block text-sm font-medium text-teal-700 underline">
                        Current: {{ document.name }}
                    </a>
                    <InputError class="mt-2" :message="form.errors.agency_license" />
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
