<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

const processing = ref(false);
const showSuccessModal = ref(false);
const registeredEmail = ref('');

const loginUrl = computed(
    () => `${route('login')}?email=${encodeURIComponent(registeredEmail.value)}`,
);
const selectedRole = new URLSearchParams(window.location.search).get('role');

const form = useForm({
    role: ['care_giver', 'care_seeker', 'agency'].includes(selectedRole) ? selectedRole : 'care_giver',
    first_name: '',
    last_name: '',
    email: '',
    age: '',
    phone: '',
    address: '',
    city: '',
    care_giver_type: '',
    cv: null,
    care_for: '',
    care_needs: '',
    preferred_contact_method: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    mobility_level: '',
    medical_notes: '',
    agency_name: '',
    contact_person: '',
    agency_address: '',
    services_offered: '',
    agency_license: null,
    password: '',
    password_confirmation: '',
});

const setRole = (role) => {
    form.role = role;
    form.clearErrors();
};

const appendAttribute = (payload, key, value) => {
    if (value !== null && value !== undefined && value !== '') {
        payload.append(`mutate[0][attributes][${key}]`, value);
    }
};

const mapApiErrors = (errors) => Object.entries(errors).reduce((mappedErrors, [key, messages]) => {
    const field = key.replace(/^mutate\.0\.attributes\./, '');

    mappedErrors[field] = Array.isArray(messages) ? messages[0] : messages;

    return mappedErrors;
}, {});

const submit = async () => {
    processing.value = true;
    form.clearErrors();

    const payload = new FormData();

    payload.append('mutate[0][operation]', 'create');
    appendAttribute(payload, 'role', form.role);
    appendAttribute(payload, 'first_name', form.first_name);
    appendAttribute(payload, 'last_name', form.last_name);
    appendAttribute(payload, 'email', form.email);
    appendAttribute(payload, 'age', form.age);
    appendAttribute(payload, 'phone', form.phone);
    appendAttribute(payload, 'address', form.address);
    appendAttribute(payload, 'city', form.city);
    appendAttribute(payload, 'password', form.password);
    appendAttribute(payload, 'password_confirmation', form.password_confirmation);

    if (form.role === 'care_giver') {
        appendAttribute(payload, 'care_giver_type', form.care_giver_type);
        appendAttribute(payload, 'cv', form.cv);
    }

    if (form.role === 'care_seeker') {
        appendAttribute(payload, 'care_for', form.care_for);
        appendAttribute(payload, 'care_needs', form.care_needs);
        appendAttribute(payload, 'preferred_contact_method', form.preferred_contact_method);
        appendAttribute(payload, 'emergency_contact_name', form.emergency_contact_name);
        appendAttribute(payload, 'emergency_contact_phone', form.emergency_contact_phone);
        appendAttribute(payload, 'mobility_level', form.mobility_level);
        appendAttribute(payload, 'medical_notes', form.medical_notes);
    }

    if (form.role === 'agency') {
        appendAttribute(payload, 'agency_name', form.agency_name);
        appendAttribute(payload, 'contact_person', form.contact_person);
        appendAttribute(payload, 'agency_address', form.agency_address);
        appendAttribute(payload, 'services_offered', form.services_offered);
        appendAttribute(payload, 'agency_license', form.agency_license);
    }

    try {
        await axios.post('/api/users/mutate', payload);

        registeredEmail.value = form.email;
        form.reset();
        showSuccessModal.value = true;
    } catch (error) {
        if (error.response?.status === 422) {
            form.setError(mapApiErrors(error.response.data.errors ?? {}));
        }
    } finally {
        processing.value = false;
        form.reset('password', 'password_confirmation');
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="auth-card-heading">
            <span>Create account</span>
            <h1>Register with Mauricare</h1>
        </div>

        <div class="role-switch" aria-label="Account type">
            <button
                type="button"
                :class="{ active: form.role === 'care_giver' }"
                @click="setRole('care_giver')"
            >
                Care Giver
            </button>
            <button
                type="button"
                :class="{ active: form.role === 'care_seeker' }"
                @click="setRole('care_seeker')"
            >
                Care Seeker
            </button>
            <button
                type="button"
                :class="{ active: form.role === 'agency' }"
                @click="setRole('agency')"
            >
                Agency
            </button>
        </div>
        <InputError class="mt-2" :message="form.errors.role" />

        <form class="auth-form" @submit.prevent="submit">
            <div v-if="form.role !== 'agency'" class="form-grid">
                <div class="form-field">
                    <label for="first_name">First name</label>
                    <input id="first_name" v-model="form.first_name" type="text" required autofocus autocomplete="given-name" />
                    <InputError :message="form.errors.first_name" />
                </div>

                <div class="form-field">
                    <label for="last_name">Last name</label>
                    <input id="last_name" v-model="form.last_name" type="text" required autocomplete="family-name" />
                    <InputError :message="form.errors.last_name" />
                </div>

                <div class="form-field">
                    <label for="email">Email</label>
                    <input id="email" v-model="form.email" type="email" required autocomplete="username" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="form-field">
                    <label for="age">Age</label>
                    <input id="age" v-model="form.age" type="number" min="0" max="120" required autocomplete="bday" />
                    <InputError :message="form.errors.age" />
                </div>

                <div class="form-field">
                    <label for="phone">Phone number</label>
                    <input id="phone" v-model="form.phone" type="tel" required autocomplete="tel" />
                    <InputError :message="form.errors.phone" />
                </div>

                <div class="form-field">
                    <label for="address">Address</label>
                    <input id="address" v-model="form.address" type="text" autocomplete="street-address" />
                    <InputError :message="form.errors.address" />
                </div>

                <div class="form-field">
                    <label for="city">City</label>
                    <input id="city" v-model="form.city" type="text" required autocomplete="address-level2" />
                    <InputError :message="form.errors.city" />
                </div>
            </div>

            <div v-if="form.role === 'care_giver'" class="role-fields">
                <div class="form-grid">
                    <div class="form-field">
                        <label for="care_giver_type">Type</label>
                        <select id="care_giver_type" v-model="form.care_giver_type" required>
                            <option value="" disabled>Select a type</option>
                            <option value="doctor">Doctor</option>
                            <option value="nurse">Nurse</option>
                            <option value="carers">Carers</option>
                            <option value="physiotherapist">Physiotherapist</option>
                            <option value="other">Other</option>
                        </select>
                        <InputError :message="form.errors.care_giver_type" />
                    </div>

                    <div class="form-field">
                        <label for="cv">CV (optional)</label>
                        <input
                            id="cv"
                            class="file-input"
                            type="file"
                            accept=".pdf,.doc,.docx"
                            @input="form.cv = $event.target.files[0]"
                        />
                        <InputError :message="form.errors.cv" />
                    </div>
                </div>
            </div>

            <div v-else-if="form.role === 'care_seeker'" class="role-fields">
                <div class="form-grid">
                    <div class="form-field">
                        <label for="care_for">Who needs care?</label>
                        <input
                            id="care_for"
                            v-model="form.care_for"
                            type="text"
                            required
                            placeholder="Myself, parent, spouse, child..."
                        />
                        <InputError :message="form.errors.care_for" />
                    </div>

                    <div class="form-field">
                        <label for="preferred_contact_method">Preferred contact</label>
                        <select id="preferred_contact_method" v-model="form.preferred_contact_method">
                            <option value="">Any</option>
                            <option value="phone">Phone</option>
                            <option value="email">Email</option>
                        </select>
                        <InputError :message="form.errors.preferred_contact_method" />
                    </div>

                    <div class="form-field">
                        <label for="emergency_contact_name">Emergency contact name</label>
                        <input id="emergency_contact_name" v-model="form.emergency_contact_name" type="text" />
                        <InputError :message="form.errors.emergency_contact_name" />
                    </div>

                    <div class="form-field">
                        <label for="emergency_contact_phone">Emergency contact phone</label>
                        <input id="emergency_contact_phone" v-model="form.emergency_contact_phone" type="tel" />
                        <InputError :message="form.errors.emergency_contact_phone" />
                    </div>
                </div>

                <div class="form-field">
                    <label for="care_needs">Care needed</label>
                    <textarea
                        id="care_needs"
                        v-model="form.care_needs"
                        rows="4"
                        required
                        placeholder="Tell us about the support needed at home."
                    ></textarea>
                    <InputError :message="form.errors.care_needs" />
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="mobility_level">Mobility level</label>
                        <input
                            id="mobility_level"
                            v-model="form.mobility_level"
                            type="text"
                            placeholder="Independent, assisted, bedridden..."
                        />
                        <InputError :message="form.errors.mobility_level" />
                    </div>

                    <div class="form-field">
                        <label for="medical_notes">Medical notes</label>
                        <textarea
                            id="medical_notes"
                            v-model="form.medical_notes"
                            rows="3"
                            placeholder="Allergies, conditions, medication notes..."
                        ></textarea>
                        <InputError :message="form.errors.medical_notes" />
                    </div>
                </div>
            </div>

            <div v-else class="role-fields">
                <div class="form-grid">
                    <div class="form-field">
                        <label for="agency_name">Agency Name</label>
                        <input
                            id="agency_name"
                            v-model="form.agency_name"
                            type="text"
                            required
                            autofocus
                            autocomplete="organization"
                        />
                        <InputError :message="form.errors.agency_name" />
                    </div>

                    <div class="form-field">
                        <label for="contact_person">Contact Person</label>
                        <input id="contact_person" v-model="form.contact_person" type="text" required autocomplete="name" />
                        <InputError :message="form.errors.contact_person" />
                    </div>

                    <div class="form-field">
                        <label for="agency_email">Email</label>
                        <input id="agency_email" v-model="form.email" type="email" required autocomplete="username" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="form-field">
                        <label for="agency_phone">Phone</label>
                        <input id="agency_phone" v-model="form.phone" type="tel" required autocomplete="tel" />
                        <InputError :message="form.errors.phone" />
                    </div>
                </div>

                <div class="form-field">
                    <label for="agency_address">Agency Address</label>
                    <input
                        id="agency_address"
                        v-model="form.agency_address"
                        type="text"
                        required
                        autocomplete="street-address"
                    />
                    <InputError :message="form.errors.agency_address" />
                </div>

                <div class="form-field">
                    <label for="services_offered">Services Offered</label>
                    <textarea
                        id="services_offered"
                        v-model="form.services_offered"
                        rows="4"
                        required
                    ></textarea>
                    <InputError :message="form.errors.services_offered" />
                </div>

                <div class="form-field">
                    <label for="agency_license">Upload Agency License (optional)</label>
                    <input
                        id="agency_license"
                        class="file-input"
                        type="file"
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                        @input="form.agency_license = $event.target.files[0]"
                    />
                    <InputError :message="form.errors.agency_license" />
                </div>
            </div>

            <div class="form-grid">
                <div class="form-field">
                    <label for="password">Password</label>
                    <input id="password" v-model="form.password" type="password" required autocomplete="new-password" />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="form-field">
                    <label for="password_confirmation">Confirm password</label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        autocomplete="new-password"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>
            </div>

            <button class="auth-button" :class="{ disabled: processing }" :disabled="processing">
                Register
            </button>

            <p class="auth-footer-text">
                Already registered?
                <Link :href="route('login')" class="auth-link">Log in</Link>
            </p>
        </form>

        <Modal :show="showSuccessModal" max-width="md" @close="showSuccessModal = false">
            <div class="p-8 text-center">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-3xl text-emerald-600">
                    <i class="fa-solid fa-circle-check"></i>
                </span>
                <h3 class="mt-5 text-xl font-bold text-slate-950">Account created successfully!</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600">
                    Welcome to Mauricare. You can now log in with
                    <span class="font-semibold text-slate-900">{{ registeredEmail }}</span>.
                </p>
                <Link
                    :href="loginUrl"
                    class="mt-6 inline-flex w-full items-center justify-center rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800"
                >
                    Continue to Login
                    <i class="fa-solid fa-arrow-right ml-3"></i>
                </Link>
            </div>
        </Modal>
    </GuestLayout>
</template>

<style scoped>
.auth-card-heading {
    margin-bottom: 1.4rem;
}

.auth-card-heading span {
    color: #119bd3;
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
}

.auth-card-heading h1 {
    margin: 0.35rem 0 0;
    color: #41454f;
    font-size: 1.75rem;
    font-weight: 800;
    line-height: 1.2;
}

.role-switch {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.35rem;
    padding: 0.35rem;
    border-radius: 8px;
    background: #eef6ff;
}

.role-switch button {
    min-height: 42px;
    border: 0;
    border-radius: 6px;
    color: #59606b;
    background: transparent;
    font-weight: 800;
}

.role-switch button.active {
    color: #fff;
    background: #119bd3;
}

.auth-form {
    display: grid;
    gap: 1rem;
    margin-top: 1.4rem;
}

.status-message {
    border-radius: 6px;
    padding: 0.75rem 0.9rem;
    color: #12643a;
    background: #e9f8ef;
    font-size: 0.9rem;
    font-weight: 700;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.role-fields {
    display: grid;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #e1e8f0;
    border-radius: 8px;
    background: #f8fafc;
}

.form-field {
    display: grid;
    gap: 0.4rem;
}

.form-field label {
    color: #41454f;
    font-size: 0.88rem;
    font-weight: 800;
}

.form-field input,
.form-field select,
.form-field textarea {
    min-height: 46px;
    width: 100%;
    border: 1px solid #dfe5ec;
    border-radius: 6px;
    padding: 0.65rem 0.85rem;
    color: #41454f;
    resize: vertical;
}

.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus {
    border-color: #119bd3;
    outline: 0;
    box-shadow: 0 0 0 3px rgba(17, 155, 211, 0.16);
}

.form-field input.file-input {
    display: flex;
    align-items: center;
    padding: 0.35rem 0.5rem;
    color: #69707a;
    font-size: 0.9rem;
    cursor: pointer;
}

.form-field input.file-input::file-selector-button {
    margin-right: 0.85rem;
    padding: 0.55rem 1.1rem;
    border: 0;
    border-radius: 6px;
    background: #119bd3;
    color: #fff;
    font-size: 0.85rem;
    font-weight: 800;
    cursor: pointer;
    transition: background 0.15s ease;
}

.form-field input.file-input::file-selector-button:hover {
    background: #0e7faf;
}

.auth-button {
    min-height: 48px;
    border: 0;
    border-radius: 8px;
    color: #fff;
    background: #2586ff;
    font-weight: 800;
}

.auth-button:hover,
.auth-button:focus {
    background: #136fdc;
}

.auth-button.disabled {
    opacity: 0.55;
}

.auth-footer-text {
    margin: 0;
    color: #69707a;
    font-size: 0.9rem;
    text-align: center;
}

.auth-link {
    color: #119bd3;
    font-size: 0.9rem;
    font-weight: 800;
    text-decoration: none;
}

.auth-link:hover,
.auth-link:focus {
    color: #0e7faf;
    text-decoration: underline;
}

@media (max-width: 640px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
