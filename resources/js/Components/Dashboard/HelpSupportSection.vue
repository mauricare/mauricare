<script setup>
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const messageSent = ref(false);

const form = useForm({
    name: page.props.auth.user?.name || '',
    phone: '',
    email: page.props.auth.user?.email || '',
    message: '',
});

const submit = () => {
    messageSent.value = false;

    form.post(route('contact.submit'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('phone', 'message');
            messageSent.value = true;
        },
    });
};
</script>

<template>
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        <section class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="text-xl font-bold text-slate-950">Contact Support</h2>
            <p class="mt-2 text-sm text-slate-600">
                Send us a message and our team will get back to you as soon as possible.
            </p>

            <p
                v-if="messageSent"
                class="mt-5 rounded-md bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
            >
                Thank you. Your message has been sent — we'll be in touch shortly.
            </p>

            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Name</span>
                        <input
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            required
                        />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Phone</span>
                        <input
                            v-model="form.phone"
                            type="tel"
                            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            required
                        />
                        <InputError :message="form.errors.phone" class="mt-1" />
                    </label>
                </div>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Email</span>
                    <input
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    />
                    <InputError :message="form.errors.email" class="mt-1" />
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">How can we help?</span>
                    <textarea
                        v-model="form.message"
                        rows="5"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                        placeholder="Tell us about your question or issue."
                        required
                    ></textarea>
                    <InputError :message="form.errors.message" class="mt-1" />
                </label>

                <div class="flex justify-end">
                    <PrimaryButton type="submit" :disabled="form.processing" :class="{ 'opacity-75': form.processing }">
                        {{ form.processing ? 'Sending...' : 'Send message' }}
                    </PrimaryButton>
                </div>
            </form>
        </section>

        <section class="h-fit rounded-xl bg-teal-700 p-6 text-white shadow-lg shadow-teal-100">
            <h2 class="text-lg font-bold">We're here for you</h2>
            <p class="mt-2 text-sm leading-6 text-teal-50">
                Questions about bookings, providers, or payments — our support team is happy to assist.
            </p>
            <div class="mt-6 space-y-4 text-sm">
                <p class="flex items-center gap-3">
                    <i class="fa-solid fa-envelope w-5 text-center text-teal-200"></i>
                    <a href="mailto:contact@mauricare.mu" class="font-semibold underline-offset-2 hover:underline">
                        contact@mauricare.mu
                    </a>
                </p>
                <p class="flex items-center gap-3">
                    <i class="fa-solid fa-clock w-5 text-center text-teal-200"></i>
                    Monday – Saturday, 8:00 – 18:00
                </p>
            </div>
        </section>
    </div>
</template>
