<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    role: 'caregiver',
    name: '',
    email: '',
    phone: '',
    address: '',
    care_for: '',
    care_needs: '',
    experience_years: '',
    qualification: '',
    availability: '',
    password: '',
    password_confirmation: '',
});

const setRole = (role) => {
    form.role = role;
    form.clearErrors();
};

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
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
                :class="{ active: form.role === 'caregiver' }"
                @click="setRole('caregiver')"
            >
                Caregiver
            </button>
            <button
                type="button"
                :class="{ active: form.role === 'patient' }"
                @click="setRole('patient')"
            >
                Patient
            </button>
        </div>
        <InputError class="mt-2" :message="form.errors.role" />

        <form class="auth-form" @submit.prevent="submit">
            <div class="form-grid">
                <div class="form-field">
                    <label for="name">Full name</label>
                    <input id="name" v-model="form.name" type="text" required autofocus autocomplete="name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="form-field">
                    <label for="email">Email</label>
                    <input id="email" v-model="form.email" type="email" required autocomplete="username" />
                    <InputError :message="form.errors.email" />
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
            </div>

            <div v-if="form.role === 'caregiver'" class="role-fields">
                <div class="form-grid">
                    <div class="form-field">
                        <label for="experience_years">Years of experience</label>
                        <input
                            id="experience_years"
                            v-model="form.experience_years"
                            type="number"
                            min="0"
                            max="60"
                            required
                        />
                        <InputError :message="form.errors.experience_years" />
                    </div>

                    <div class="form-field">
                        <label for="qualification">Qualification</label>
                        <input
                            id="qualification"
                            v-model="form.qualification"
                            type="text"
                            required
                            placeholder="Nursing, caregiving, first aid..."
                        />
                        <InputError :message="form.errors.qualification" />
                    </div>
                </div>

                <div class="form-field">
                    <label for="availability">Availability</label>
                    <input
                        id="availability"
                        v-model="form.availability"
                        type="text"
                        required
                        placeholder="Weekdays, weekends, full-time, part-time..."
                    />
                    <InputError :message="form.errors.availability" />
                </div>
            </div>

            <div v-else class="role-fields">
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

            <button class="auth-button" :class="{ disabled: form.processing }" :disabled="form.processing">
                Register
            </button>

            <p class="auth-footer-text">
                Already registered?
                <Link :href="route('login')" class="auth-link">Log in</Link>
            </p>
        </form>
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
    grid-template-columns: repeat(2, minmax(0, 1fr));
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
.form-field textarea:focus {
    border-color: #119bd3;
    outline: 0;
    box-shadow: 0 0 0 3px rgba(17, 155, 211, 0.16);
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
