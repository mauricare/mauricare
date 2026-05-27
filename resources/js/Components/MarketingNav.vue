<script setup>
const navItems = [
    { label: 'Home', href: '#home' },
    { label: 'About', href: '#about' },
    { label: 'Care Packages', href: '#care-packages' },
    { label: 'Medical Services', href: '#medical-services' },
    { label: 'Contact Us', href: '#contact' },
];
</script>

<template>
    <nav class="navbar navbar-expand-lg navbar-dark mauricare-nav">
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

                <a class="btn btn-primary btn-sm fw-semibold px-3" href="tel:+23058199009">
                    Call +230 5819 9009
                </a>
            </div>
        </div>
    </nav>
</template>
