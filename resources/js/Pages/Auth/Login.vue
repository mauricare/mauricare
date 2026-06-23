<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const accountType = ref('care_giver');

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="auth-card-heading">
            <span>Welcome back</span>
            <h1>Log in to Mauricare</h1>
        </div>

        <div class="role-switch" aria-label="Account type">
            <button
                type="button"
                :class="{ active: accountType === 'care_giver' }"
                @click="accountType = 'care_giver'"
            >
                Care Giver
            </button>
            <button
                type="button"
                :class="{ active: accountType === 'care_seeker' }"
                @click="accountType = 'care_seeker'"
            >
                Care Seeker
            </button>
        </div>

        <p class="auth-note">
            The login form is the same for care givers and care seekers.
        </p>

        <div v-if="status" class="status-message">
            {{ status }}
        </div>

        <form class="auth-form" @submit.prevent="submit">
            <div class="form-field">
                <label for="email">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="form-field">
                <label for="password">Password</label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="auth-row">
                <label class="remember-field">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span>Remember me</span>
                </label>

                <Link v-if="canResetPassword" :href="route('password.request')" class="auth-link">
                    Forgot password?
                </Link>
            </div>

            <button class="auth-button" :class="{ disabled: form.processing }" :disabled="form.processing">
                Log in
            </button>

            <p class="auth-footer-text">
                New to Mauricare?
                <Link :href="route('register')" class="auth-link">Create an account</Link>
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

.auth-note,
.auth-footer-text {
    margin: 1rem 0 0;
    color: #69707a;
    font-size: 0.9rem;
}

.status-message {
    margin-top: 1rem;
    color: #137a3d;
    font-size: 0.9rem;
    font-weight: 700;
}

.auth-form {
    display: grid;
    gap: 1rem;
    margin-top: 1.4rem;
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

.form-field input {
    min-height: 46px;
    width: 100%;
    border: 1px solid #dfe5ec;
    border-radius: 6px;
    padding: 0.65rem 0.85rem;
    color: #41454f;
}

.form-field input:focus {
    border-color: #119bd3;
    outline: 0;
    box-shadow: 0 0 0 3px rgba(17, 155, 211, 0.16);
}

.auth-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.remember-field {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    color: #69707a;
    font-size: 0.9rem;
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

@media (max-width: 480px) {
    .auth-row {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
