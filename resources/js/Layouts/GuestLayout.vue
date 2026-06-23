<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="auth-shell flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0"
    >
        <div
            class="auth-card w-full overflow-hidden bg-white px-6 py-5 shadow-md sm:max-w-3xl sm:rounded-lg"
        >
            <div class="auth-brand">
                <Link href="/">
                    <img
                        class="auth-logo"
                        src="/images/mauricare-home-care-services-logo.png"
                        alt="Mauricare Home Care Services"
                    />
                </Link>
            </div>

            <slot />
        </div>
    </div>
</template>

<style scoped>
.auth-shell {
    position: relative;
    overflow: hidden;
    background:
        radial-gradient(circle at 16% 18%, rgba(255, 255, 255, 0.28), transparent 28%),
        linear-gradient(135deg, #119bd3 0%, #2586ff 42%, #ec1696 100%);
}

.auth-shell::before {
    position: absolute;
    inset: 0;
    content: "";
    background:
        linear-gradient(rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.08)),
        radial-gradient(circle at 82% 78%, rgba(255, 255, 255, 0.22), transparent 32%);
    pointer-events: none;
}

.auth-logo,
.auth-card {
    position: relative;
    z-index: 1;
}

.auth-brand {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.35rem;
}

.auth-logo {
    width: 124px;
    height: auto;
    display: block;
}

.auth-card {
    border: 1px solid rgba(255, 255, 255, 0.42);
    box-shadow: 0 24px 70px rgba(29, 45, 68, 0.22);
}
</style>
