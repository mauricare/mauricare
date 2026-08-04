<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ version: String, contactEmail: String });
</script>

<template>
    <GuestLayout>
        <Head title="Terms of Use" />
        <article class="terms">
            <header>
                <span class="eyebrow">Mauricare platform agreement</span>
                <h1>Terms of Use</h1>
                <p><strong>Version and effective date:</strong> {{ version }}</p>
            </header>

            <div class="notice">
                <strong>Important:</strong> Mauricare provides a platform that connects people seeking care with independent care providers. Mauricare is not an emergency service and does not itself diagnose, prescribe, or deliver treatment. In an emergency, contact the appropriate emergency services immediately.
            </div>

            <section>
                <h2>1. Agreement and eligibility</h2>
                <p>These Terms govern registration for and use of the Mauricare website and application (the “Platform”). By checking the acceptance box and registering, you enter into an electronic agreement with Mauricare. You must provide accurate information, keep it current, protect your password, and promptly report suspected unauthorised access.</p>
                <p>You must be at least 18 and legally able to enter into this agreement. If you arrange care or provide information for another person, you confirm that you have their authority or are their parent or legal guardian. Our <Link :href="route('privacy-policy')">Privacy Notice</Link> separately explains how personal and health information is handled.</p>
            </section>

            <section>
                <h2>2. Mauricare’s role</h2>
                <p>The Platform lets care seekers publish care requests, lets eligible care givers review and accept available bookings, supports messages and notifications, records booking progress and direct payments, and enables reviews and platform-fee invoicing. Mauricare may verify information and moderate use, but does not guarantee that a request will be accepted, that a user is suitable for a particular need, or that services will achieve a particular outcome.</p>
                <p>Unless expressly agreed otherwise in writing, care givers are independent providers and are not employees, agents, partners, or representatives of Mauricare. The care seeker and care giver are responsible for agreeing the scope, timing, price, and delivery of each service.</p>
            </section>

            <section>
                <h2>3. Free registration and platform charges</h2>
                <ul>
                    <li>Registration is free for care seekers and care givers.</li>
                    <li>Care seekers pay no registration fee or Platform commission. They pay the care giver directly after treatment or care has been delivered.</li>
                    <li>Care givers do not owe a Platform fee merely for registering, maintaining a profile, or viewing bookings. A fee arises only from earnings on completed bookings obtained through the Platform.</li>
                    <li>At the end of each monthly billing period, Mauricare may invoice a care giver an amount between <strong>10% and 15%</strong> of the total income recorded for their completed Platform bookings. The applicable percentage, covered bookings, billing period, total earnings, and amount due will appear on the invoice.</li>
                </ul>
                <p>Care givers must pay valid invoices by the deadline and using the method stated on the invoice. They must promptly raise a genuine billing dispute with supporting details. Each user remains responsible for their own taxes, registrations, licences, insurance, and financial records.</p>
            </section>

            <section>
                <h2>4. Care-seeker responsibilities</h2>
                <p>A care seeker must submit truthful and sufficiently complete care needs, contact details, location, timing, mobility information, relevant medical notes, and known safety risks. The seeker must provide a safe environment, treat care givers respectfully, and not request unlawful, unsafe, or inappropriate work.</p>
                <p>After a visit is completed, the seeker pays the care giver directly using an agreed method currently supported by the Platform, such as cash or Juice. The seeker must then accurately record the amount paid, payment method, and any required transaction reference in the Platform. Recording payment does not cause Mauricare to receive, hold, or transfer that money.</p>
            </section>

            <section>
                <h2>5. Care-giver responsibilities</h2>
                <p>A care giver must provide truthful identity, contact, profession, experience, qualification, licence, and CV information and keep it current. The care giver must hold every registration, authorisation, qualification, insurance, and competence required for the services they offer, work only within their lawful professional scope, follow applicable clinical and safeguarding standards, preserve confidentiality, and act with care and professionalism.</p>
                <p>By accepting a booking, the care giver commits to attend as agreed or communicate promptly if circumstances change. After providing care, the care giver records the amount due; after the seeker confirms direct payment, the care giver closes the booking. Care givers must not conceal, understate, redirect, or misclassify Platform-sourced bookings or earnings to avoid fees.</p>
            </section>

            <section>
                <h2>6. Booking lifecycle, cancellations, and disputes</h2>
                <p>A request begins as open, may be accepted by one care giver, progresses through visit completion and payment confirmation, and is then closed. A request may also be cancelled. Users must keep booking records accurate and use messages responsibly. If the parties disagree about attendance, care, price, or payment, they should first communicate in good faith and preserve relevant records. Mauricare may review Platform records and assist with an administrative resolution but is not obliged to adjudicate private clinical or payment disputes.</p>
            </section>

            <section>
                <h2>7. Safety and medical responsibility</h2>
                <p>The Platform is not a substitute for emergency services, a medical diagnosis, or advice from an appropriately qualified professional. Care seekers decide whom to engage and should independently assess suitability. Care givers retain professional responsibility for their own decisions and services. No user may use the Platform to prescribe, obtain, supply, or administer anything unlawfully or to perform services beyond their authority or competence.</p>
            </section>

            <section>
                <h2>8. Acceptable use and communications</h2>
                <p>You must not misuse another person’s account or data; harass, discriminate against, threaten, exploit, or deceive anyone; upload unlawful or malicious material; scrape or disrupt the Platform; bypass safeguards; post false reviews; use confidential health information for an unrelated purpose; or use the Platform for fraud or illegal activity. Messages and reviews must be relevant, accurate, respectful, and must not unnecessarily reveal private or health information.</p>
            </section>

            <section>
                <h2>9. Verification and background information</h2>
                <p>Mauricare may request identity, qualification, licence, agency, CV, or other supporting documents and may refuse or restrict access while checks are incomplete. A displayed profile or accepted document is not a guarantee, endorsement, or continuing certification. Users should perform checks appropriate to the care being arranged, and care givers must notify Mauricare if a relevant licence, registration, insurance policy, or authority expires, is restricted, or is revoked.</p>
            </section>

            <section>
                <h2>10. Suspension and deactivation</h2>
                <p>Mauricare reserves the right to restrict, suspend, or deactivate an account where a user breaches these Terms, fails to pay a valid invoice, provides misleading information, creates a safety or legal risk, misuses personal data, interferes with the Platform, or otherwise fails to follow Platform rules. Where appropriate, Mauricare may investigate and allow the user to respond, but may act immediately to protect users or comply with law.</p>
                <p>A deactivated care-seeker account cannot create new care requests. A deactivated care-giver account cannot accept new bookings. Deactivation does not erase existing payment, invoice, confidentiality, dispute, or legal obligations, and Mauricare may retain restricted access needed to manage existing records or obligations.</p>
            </section>

            <section>
                <h2>11. Reviews, content, and Platform rights</h2>
                <p>You retain ownership of content you submit. You give Mauricare a non-exclusive licence to host, process, reproduce, and display it only as needed to operate, secure, improve, and lawfully promote the Platform. Public testimonials may use limited reviewer information as described in the Privacy Notice. Mauricare may remove content that is unlawful, irrelevant, misleading, unsafe, or contrary to these Terms. The Platform software, branding, and original materials remain Mauricare’s property.</p>
            </section>

            <section>
                <h2>12. Availability and liability</h2>
                <p>Mauricare aims to keep the Platform secure and available but cannot promise uninterrupted or error-free operation. To the extent permitted by Mauritian law, Mauricare is not responsible for independent care givers’ acts or omissions, clinical outcomes, off-Platform statements, direct payments, lost profits, or indirect or consequential loss. Nothing in these Terms excludes liability that cannot lawfully be excluded, including liability for Mauricare’s own fraud or other non-excludable responsibility.</p>
            </section>

            <section>
                <h2>13. Account closure and continuing terms</h2>
                <p>You may request account closure through the Platform. Closure may be delayed or data retained where necessary for open bookings, unpaid invoices, safety, accounting, legal claims, or other lawful obligations. Provisions concerning payment, fees, confidentiality, records, intellectual property, disputes, and liability continue where their nature requires it.</p>
            </section>

            <section>
                <h2>14. Changes to these Terms</h2>
                <p>Mauricare may update these Terms to reflect service, legal, safety, or commercial changes. A new version and effective date will be published. Where a change is material, Mauricare will provide appropriate notice and request renewed acceptance before continued use where required. Changes do not retroactively alter an already-issued invoice or completed transaction unless law requires otherwise.</p>
            </section>

            <section>
                <h2>15. Governing law and contact</h2>
                <p>These Terms are governed by the laws of the Republic of Mauritius. The parties should first attempt to resolve a dispute in good faith by contacting <a :href="`mailto:${contactEmail}`">{{ contactEmail }}</a>. If it cannot be resolved, the courts of Mauritius will have jurisdiction, subject to any mandatory rights or procedures that apply.</p>
            </section>

            <div class="actions">
                <Link :href="route('register')">Return to registration</Link>
                <Link :href="route('privacy-policy')">Read the Privacy Notice</Link>
            </div>
        </article>
    </GuestLayout>
</template>

<style scoped>
.terms { color: #334155; line-height: 1.72; }
.terms header { padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0; }
.eyebrow { color: #0f8f88; font-size: .75rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
.terms h1 { margin-top: .35rem; color: #0f172a; font-size: 2rem; font-weight: 800; }
.terms h2 { margin-top: 1.6rem; color: #0f172a; font-size: 1.08rem; font-weight: 800; }
.terms p, .terms ul { margin-top: .6rem; }
.terms ul { padding-left: 1.35rem; list-style: disc; }
.terms li + li { margin-top: .35rem; }
.terms a { color: #087ea4; font-weight: 700; text-decoration: underline; }
.notice { margin-top: 1.25rem; padding: 1rem 1.1rem; border: 1px solid #99f6e4; border-radius: .75rem; background: #f0fdfa; color: #134e4a; }
.actions { display: flex; flex-wrap: wrap; gap: 1rem 1.5rem; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; }
</style>
