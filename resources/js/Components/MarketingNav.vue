<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

const navItems = [
    { label: 'Home', href: '#home' },
    { label: 'About', href: '#about' },
    { label: 'Medical Services', href: '#medical-services' },
    { label: 'Care Packages', href: '#care-packages' },
    { label: 'Contact Us', href: '#request-care' },
];

const isScrolled = ref(false);

function updateNavbarState() {
    isScrolled.value = window.scrollY > 20;
}

onMounted(() => {
    updateNavbarState();
    window.addEventListener('scroll', updateNavbarState, { passive: true });
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', updateNavbarState);
});
</script>

<template>
    <nav class="navbar navbar-expand-lg navbar-dark mauricare-nav" :class="{ 'is-scrolled': isScrolled }">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#home" aria-label="Mauricare home">
                <img
                    src="/images/mauricare-home-care-services-logo.png"
                    alt="Mauricare Home Care Services"
                    class="navbar-logo"
                />
            </a>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNavigation"
                aria-controls="mainNavigation"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div id="mainNavigation" class="collapse navbar-collapse">
                <ul class="navbar-nav mx-auto mb-3 mb-lg-0">
                    <li v-for="item in navItems" :key="item.label" class="nav-item">
                        <a class="nav-link" :href="item.href">{{ item.label }}</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <a class="btn btn-primary btn-sm fw-semibold px-3" href="tel:+23059199909">
                        Call +230 5919 9909
                    </a>
                    <a class="btn btn-outline-light btn-sm fw-semibold px-3" :href="route('login')">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </nav>
</template>
