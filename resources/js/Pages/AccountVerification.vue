<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const user = computed(() => page.props.auth?.user ?? {});
</script>

<template>
    <Head title="Account verification" />

    <main class="verification-shell">
        <section class="verification-panel" aria-labelledby="verification-title">
            <div class="brand-bar">
                <img
                    class="brand-logo"
                    src="/images/mauricare-home-care-services-logo.png"
                    alt="Mauricare Home Care Services"
                />

                <Link
                    class="logout-link"
                    :href="route('logout')"
                    method="post"
                    as="button"
                >
                    Log out
                </Link>
            </div>

            <div class="status-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" role="img">
                    <path
                        d="M12 2.75 4.25 6.1v5.45c0 4.92 3.3 9.52 7.75 10.7 4.45-1.18 7.75-5.78 7.75-10.7V6.1L12 2.75Zm3.62 7.95-4.23 4.23a.95.95 0 0 1-1.34 0l-1.67-1.67a.95.95 0 1 1 1.34-1.34l1 1 3.56-3.56a.95.95 0 0 1 1.34 1.34Z"
                    />
                </svg>
            </div>

            <p class="eyebrow">Application received</p>

            <h1 id="verification-title">
                Account verification in progress
            </h1>

            <p class="message">
                Our team is reviewing your account. We will email you as soon as your portal access is approved.
            </p>

            <div class="account-summary">
                <span>Signed in as</span>
                <strong>{{ user.name }}</strong>
                <small>{{ user.email }}</small>
            </div>

            <div class="progress-steps" aria-label="Verification progress">
                <div class="step complete">
                    <span></span>
                    <p>Account created</p>
                </div>
                <div class="step active">
                    <span></span>
                    <p>Review in progress</p>
                </div>
                <div class="step">
                    <span></span>
                    <p>Portal access</p>
                </div>
            </div>
        </section>
    </main>
</template>

<style scoped>
.verification-shell {
    display: grid;
    min-height: 100vh;
    place-items: center;
    padding: 2rem 1rem;
    background:
        radial-gradient(circle at 14% 18%, rgba(255, 255, 255, 0.28), transparent 29%),
        linear-gradient(135deg, #119bd3 0%, #2586ff 46%, #ec1696 100%);
    color: #41454f;
    font-family: Figtree, ui-sans-serif, system-ui, sans-serif;
}

.verification-panel {
    width: min(100%, 760px);
    border: 1px solid rgba(255, 255, 255, 0.48);
    border-radius: 8px;
    padding: clamp(1.25rem, 4vw, 2.5rem);
    background: rgba(255, 255, 255, 0.96);
    box-shadow: 0 24px 70px rgba(29, 45, 68, 0.22);
}

.brand-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.brand-logo {
    width: 124px;
    height: auto;
}

.logout-link {
    min-height: 40px;
    border: 1px solid #dfe5ec;
    border-radius: 8px;
    padding: 0 1rem;
    color: #59606b;
    background: #fff;
    font-weight: 800;
}

.logout-link:hover,
.logout-link:focus {
    border-color: #119bd3;
    color: #0e7faf;
}

.status-icon {
    display: grid;
    width: 76px;
    height: 76px;
    place-items: center;
    margin: 3rem auto 1.35rem;
    border-radius: 50%;
    background: #eef6ff;
    color: #119bd3;
}

.status-icon svg {
    width: 42px;
    height: 42px;
    fill: currentColor;
}

.eyebrow {
    margin: 0;
    color: #119bd3;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0;
    text-align: center;
    text-transform: uppercase;
}

h1 {
    max-width: 560px;
    margin: 0.45rem auto 0;
    color: #41454f;
    font-size: clamp(2rem, 5vw, 3.4rem);
    font-weight: 800;
    line-height: 1.05;
    text-align: center;
}

.message {
    max-width: 520px;
    margin: 1.1rem auto 0;
    color: #69707a;
    font-size: 1.08rem;
    line-height: 1.7;
    text-align: center;
}

.account-summary {
    display: grid;
    gap: 0.25rem;
    max-width: 420px;
    margin: 1.75rem auto 0;
    border: 1px solid #dfe5ec;
    border-radius: 8px;
    padding: 1rem;
    background: #f8fbff;
    text-align: center;
}

.account-summary span,
.account-summary small {
    color: #69707a;
    font-size: 0.88rem;
}

.account-summary strong {
    color: #41454f;
    font-size: 1rem;
}

.progress-steps {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.75rem;
    margin-top: 2rem;
}

.step {
    display: grid;
    gap: 0.55rem;
    justify-items: center;
    color: #8a929d;
    font-size: 0.88rem;
    font-weight: 800;
    text-align: center;
}

.step span {
    width: 100%;
    height: 6px;
    border-radius: 999px;
    background: #dfe5ec;
}

.step.complete {
    color: #137a3d;
}

.step.complete span {
    background: #29b263;
}

.step.active {
    color: #119bd3;
}

.step.active span {
    background: linear-gradient(90deg, #119bd3, #2586ff);
}

.step p {
    margin: 0;
}

@media (max-width: 560px) {
    .verification-panel {
        padding: 1.25rem;
    }

    .brand-bar {
        align-items: flex-start;
        flex-direction: column;
    }

    .logout-link {
        width: 100%;
    }

    .status-icon {
        margin-top: 2rem;
    }

    .progress-steps {
        grid-template-columns: 1fr;
    }
}
</style>
