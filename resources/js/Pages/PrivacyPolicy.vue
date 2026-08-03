<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ version: String, controllerName: String, controllerEmail: String, dpoEmail: String });
</script>

<template>
    <GuestLayout>
        <Head title="Privacy Notice" />
        <article class="policy">
            <h1>Privacy Notice</h1>
            <p><strong>Version:</strong> {{ version }}</p>
            <p>{{ controllerName }} is the controller responsible for the personal data described in this notice. Contact us at <a :href="`mailto:${controllerEmail}`">{{ controllerEmail }}</a> or contact our data-protection officer at <a :href="`mailto:${dpoEmail}`">{{ dpoEmail }}</a>.</p>

            <h2>Information we collect</h2>
            <p>We collect account and contact details, addresses, age, caregiver qualifications and licences, care requests, mobility and medical information, emergency-contact details, bookings, messages, reviews, invoices, payment references, device and security logs, and records of your privacy choices. Health information is special-category personal data.</p>

            <h2>Why and on what basis we use it</h2>
            <p>We use information to create and secure accounts, verify caregivers and agencies, arrange and administer home-care services, enable communication, process and evidence payments, provide support, meet legal obligations, prevent misuse, and establish or defend legal claims. We process necessary account and service information to perform our contract and comply with law. We process health information with explicit consent and, where applicable, for health or social-care purposes under the responsibility of persons bound by confidentiality. You may withdraw consent without affecting earlier lawful processing.</p>

            <h2>Who receives information</h2>
            <p>Access is limited to authorised Mauricare personnel, assigned caregivers or agencies where needed for care, and contracted providers supporting hosting, email, document storage, backup, security, professional advice, or payment administration. We may disclose information where law requires it. We do not sell personal data.</p>

            <h2>International transfers</h2>
            <p>Some contracted technology providers may process information outside Mauritius. Mauricare will assess those transfers, use appropriate contractual and security safeguards, and make any required filing with the Data Protection Commissioner.</p>

            <h2>Retention</h2>
            <p>We retain information only for the documented period necessary for care, safety, accounting, dispute resolution, and legal obligations. When no lawful reason remains, information is securely erased or irreversibly anonymised. Deleted data may remain in protected backups until the backup retention cycle expires.</p>

            <h2>Security</h2>
            <p>We use access controls, encryption, private document storage, monitoring, backups, and staff confidentiality measures appropriate to the sensitivity of health information. No system can guarantee absolute security.</p>

            <h2>Your rights</h2>
            <p>You may request access to a copy of your information, correction, erasure, restriction, or object to processing. You may withdraw consent and complain to the Mauritius Data Protection Commissioner. We may verify your identity and may retain information where another legal obligation or legal claim requires it.</p>

            <h2>Children and information about other people</h2>
            <p>An account holder who supplies information about another person must be authorised to do so and must make this notice available to that person. Personal data of a child under 16 may only be submitted by, or with verified authorisation from, a parent or guardian.</p>

            <h2>Contact and updates</h2>
            <p>Send privacy requests to <a :href="`mailto:${dpoEmail}`">{{ dpoEmail }}</a>. Material changes will be issued under a new notice version and, where required, presented for renewed acknowledgement.</p>

            <Link :href="route('register')">Return to registration</Link>
        </article>
    </GuestLayout>
</template>

<style scoped>
.policy { color: #334155; line-height: 1.7; }
.policy h1 { color: #0f172a; font-size: 2rem; font-weight: 800; }
.policy h2 { margin-top: 1.4rem; color: #0f172a; font-size: 1.1rem; font-weight: 800; }
.policy p { margin-top: .55rem; }
.policy a { color: #087ea4; font-weight: 700; text-decoration: underline; }
</style>
