<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import MarketingFooter from '@/Components/MarketingFooter.vue';
import MarketingNav from '@/Components/MarketingNav.vue';

const contactSuccess = ref('');

const contactForm = useForm({
    name: '',
    phone: '',
    email: '',
    message: '',
});

const submitContact = () => {
    contactSuccess.value = '';

    contactForm.post(route('contact.submit'), {
        preserveScroll: true,
        onSuccess: () => {
            contactSuccess.value = 'Thank you. Your message has been sent.';
            contactForm.reset();
        },
    });
};

/* const benefits = [
    {
        title: 'Professional Care at Home',
        icon: 'fa-solid fa-house-medical',
        text: 'Our experienced nurses provide personalized medical care in the comfort of your home, ensuring safety, dignity, and quality treatment without the need to travel.',
    },
    {
        title: 'Experienced Healthcare Professionals',
        icon: 'fa-solid fa-user-nurse',
        text: 'Our team includes qualified nurses and healthcare providers trained to deliver reliable and professional home care services tailored to each patient’s needs.',
    },
    {
        title: 'Personalized Care Plan',
        icon: 'fa-solid fa-clipboard-list',
        text: 'We tailor our healthcare services to meet each patient’s specific needs, ensuring the right level of medical attention, comfort, and support at every stage of care.',
    },
];

*/

const partnerGroups = [
    {
        title: 'For Families',
        icon: 'fa-solid fa-user-group',
        accent: 'teal',
        items: [
            {
                title: 'Find Trusted Care, Easily',
                icon: 'fa-solid fa-magnifying-glass',
                text: "Effortlessly discover qualified healthcare providers and agencies tailored to your family's needs.",
            },
            {
                title: 'Peace of Mind',
                icon: 'fa-solid fa-shield-heart',
                text: 'Secure, confidential, and reliable care services, ensuring your loved ones are in safe hands.',
            },
            {
                title: 'Seamless Experience',
                icon: 'fa-solid fa-calendar-days',
                text: 'Enjoy easy booking, streamlined communication, and islandwide access to quality care.',
            },
            {
                title: 'Personalized Support',
                icon: 'fa-solid fa-heart-pulse',
                text: 'Compassionate care that respects individuality, dignity, and well-being at home or in residential settings.',
            },
        ],
    },
    {
        title: 'For Healthcare Providers & Agencies',
        icon: 'fa-solid fa-user-doctor',
        accent: 'blue',
        items: [
            {
                title: 'Expand Your Reach',
                icon: 'fa-solid fa-globe',
                text: 'Connect with a wider network of families and clients across Mauritius.',
            },
            {
                title: 'Professional Showcase',
                icon: 'fa-solid fa-id-card',
                text: 'Build your online presence and highlight your expertise and compassionate services.',
            },
            {
                title: 'Efficient Practice Management',
                icon: 'fa-solid fa-gear',
                text: 'Utilize tools for streamlined appointments and communication.',
            },
            {
                title: 'Community Collaboration',
                icon: 'fa-solid fa-handshake',
                text: 'Become part of a trusted network committed to advancing healthcare standards in Mauritius.',
            },
        ],
    },
];

const partnerPrinciples = [
    {
        title: 'Our Mission',
        icon: 'fa-solid fa-bullseye',
        accent: 'teal',
        text: 'To build a healthier Mauritius by seamlessly connecting families with accessible, high-quality home and residential care, and by empowering healthcare professionals and agencies with the tools and reach to serve their communities effectively.',
    },
    {
        title: 'Our Vision',
        icon: 'fa-solid fa-eye',
        accent: 'blue',
        text: 'To be the cornerstone of accessible, compassionate healthcare and well-being in Mauritius, creating a future where every Mauritian family thrives with optimal health and dignity.',
    },
];

const comprehensiveCareServices = [
    {
        title: 'Home Care Nursing',
        icon: 'fa-solid fa-house-medical',
        text: 'Wound care, injections, IV therapy, catheter care and more, delivered with compassion.',
    },
    {
        title: 'Elderly & Residential Care',
        icon: 'fa-solid fa-hand-holding-heart',
        text: 'A warm, safe, and dignified environment for seniors.',
    },
    {
        title: 'Specialized Therapies',
        icon: 'fa-solid fa-person-walking',
        text: 'Physiotherapy and other rehabilitative services at home.',
    },
    {
        title: 'Medical Equipment Rental',
        icon: 'fa-solid fa-wheelchair',
        text: 'Convenient access to essential equipment when you need it.',
    },
    {
        title: 'Telehealth Consultations',
        icon: 'fa-solid fa-display',
        text: 'Remote access to healthcare professionals from the comfort of your home.',
    },
];

const careBundles = [
    {
        title: 'Wound Care Package',
        image: '/images/Wound%20Care%20Package.png',
        points: ['Sterile wound cleaning and dressing', 'Healing progress monitoring', 'Guidance for faster recovery'],
    },
    {
        title: 'Post-Hospital Recovery',
        image: '/images/Post-Hospital%20Recovery.jpg',
        points: ['Medication and vital sign support', 'Mobility and comfort assistance', 'Follow-up care coordination'],
    },
    {
        title: 'Bedridden Care Plan',
        image: '/images/Bedridden%20Care%20Plan.jpg',
        points: ['Daily hygiene and positioning', 'Bedsore prevention support', 'Compassionate routine care'],
    },
    {
        title: 'Home ICU Support',
        image: '/images/Home%20ICU%20Support.png',
        points: ['Advanced nursing observation', 'Oxygen and equipment assistance', 'Family support and reporting'],
    },
];

const goalItems = [
    {
        title: 'Provider Network Expansion',
        icon: 'fa-solid fa-people-arrows',
        accent: 'teal',
        text: 'To onboard a diverse and comprehensive network of healthcare providers and agencies, covering all major specializations and regions in Mauritius within two years.',
    },
    {
        title: 'Family Empowerment',
        icon: 'fa-solid fa-people-group',
        accent: 'green',
        text: 'To become the go-to platform for 70% of Mauritian families seeking home and residential care information and services within five years.',
    },
    {
        title: 'Quality Assurance',
        icon: 'fa-solid fa-shield-halved',
        accent: 'blue',
        text: 'To establish and maintain rigorous standards for all listed providers and agencies, ensuring consistent quality and client satisfaction.',
    },
    {
        title: 'Community Health Initiatives',
        icon: 'fa-solid fa-heart-pulse',
        accent: 'pink',
        text: 'To actively promote and facilitate community-based health and wellness programs, increasing preventative care adoption.',
    },
    {
        title: 'Technological Innovation',
        icon: 'fa-solid fa-lightbulb',
        accent: 'orange',
        text: 'To continuously enhance the platform with user-friendly technology that simplifies care navigation and improves outcomes for all.',
    },
];

const stories = [
    {
        name: 'Dorothy Palmer',
        role: 'Home Care Client',
        initials: 'DP',
        accent: 'blue',
        text: 'Mauricare gave our family peace of mind. The nurse was gentle, punctual, and made my mother feel safe at home.',
    },
    {
        name: 'Randy Tran',
        role: 'Patient Family',
        initials: 'RT',
        accent: 'orange',
        text: 'I was hesitant at first, but the home visit exceeded our expectations. The care plan was clear and professionally handled.',
    },
    {
        name: 'Amy Turner',
        role: 'Recovery Patient',
        initials: 'AT',
        accent: 'orange',
        text: 'The team was kind, attentive, and reliable after my procedure. Having care at home made recovery much easier.',
    },
    {
        name: 'Evelyn Green',
        role: 'Patient Family',
        initials: 'EG',
        accent: 'blue',
        text: 'Their support helped us care for our father with dignity. Communication was easy and the service felt genuinely personal.',
    },
    {
        name: 'Joe Alan',
        role: 'Long-term Client',
        initials: 'JA',
        accent: 'orange',
        text: 'Every visit was handled with patience and professionalism. We always knew exactly what care was being provided.',
    },
    {
        name: 'Nadia Bissessur',
        role: 'Home Care Client',
        initials: 'NB',
        accent: 'orange',
        text: 'A warm, dependable service. The nurse listened carefully and adjusted the care routine around our daily needs.',
    },
];

const testimonialSlides = computed(() => {
    const slides = [];

    for (let index = 0; index < stories.length; index += 3) {
        slides.push(stories.slice(index, index + 3));
    }

    return slides;
});

</script>

<template>
    <Head title="Mauricare Home Care Services in Mauritius">
        <meta
            name="description"
            content="Mauricare provides professional home nursing, elderly care, wound care, post-hospital recovery, and personalized healthcare services at home in Mauritius."
        />
        <meta
            name="keywords"
            content="home care Mauritius, home nursing Mauritius, elderly care Mauritius, caregiver Mauritius, wound care at home, post hospital recovery care"
        />
        <meta name="robots" content="index, follow" />
        <meta property="og:title" content="Mauricare Home Care Services in Mauritius" />
        <meta
            property="og:description"
            content="Professional and compassionate nursing care at home for patients, elderly people, and families across Mauritius."
        />
        <meta property="og:type" content="website" />
        <meta property="og:image" content="https://mauricare.mu/images/mauricare-family.png" />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <div class="mauricare-page">
        <header id="home" class="hero-section">
            <MarketingNav />

            <div class="container hero-content text-white">
                <h1 class="fw-bold mb-4 main-title">Empowering<br />Healthier Lives,<br />Together.</h1>
                <p class="mb-4 hero-copy">
                    MauriCare: Connecting Mauritian Families with Compassionate, Exceptional Home and Residential Care.
                </p>

                <div class="hero-actions">
                    <a href="/login?role=care_seeker" class="btn hero-button hero-button-primary fw-semibold">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <span>Find Care</span>
                    </a>
                    <a href="/register?role=care_giver" class="btn hero-button hero-button-secondary fw-semibold">
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                        <span>Join as Provider</span>
                    </a>
                </div>

                <div class="hero-trust-strip" aria-label="Mauricare service highlights">
                    <div class="hero-trust-item">
                        <i class="fa-solid fa-award" aria-hidden="true"></i>
                        <span>Trusted<br />Professionals</span>
                    </div>
                    <div class="hero-trust-item">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        <span>Secure &amp;<br />Confidential</span>
                    </div>
                    <div class="hero-trust-item">
                        <i class="fa-solid fa-calendar-plus" aria-hidden="true"></i>
                        <span>Easy<br />Booking</span>
                    </div>
                    <div class="hero-trust-item">
                        <i class="fa-solid fa-headset" aria-hidden="true"></i>
                        <span>Islandwide<br />Access</span>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <section id="about" class="section-padding about-section">
                <div class="container">
                    <div class="section-heading text-center mx-auto">
                        <h2 class="section-title">Why Partner with MauriCare?</h2>
                        <p class="section-copy">
                            MauriCare is more than just a platform; it's a community dedicated to fostering a healthier
                            Mauritius. We empower families by providing seamless access to a comprehensive network of
                            trusted healthcare providers and agencies, while offering professionals a dedicated space to
                            grow their practice and reach those in need.
                        </p>
                    </div>

                    <div class="partner-grid">
                        <article
                            v-for="group in partnerGroups"
                            :key="group.title"
                            class="partner-card"
                            :class="`partner-card-${group.accent}`"
                        >
                            <header class="partner-card-header">
                                <h3>{{ group.title }}</h3>
                            </header>

                            <div class="partner-feature-list">
                                <div v-for="item in group.items" :key="item.title" class="partner-feature">
                                    <i :class="item.icon" aria-hidden="true"></i>
                                    <div>
                                        <h4>{{ item.title }}</h4>
                                        <p>{{ item.text }}</p>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="principle-grid">
                        <article
                            v-for="principle in partnerPrinciples"
                            :key="principle.title"
                            class="principle-card"
                            :class="`principle-card-${principle.accent}`"
                        >
                            <i :class="principle.icon" aria-hidden="true"></i>
                            <div>
                                <h3>{{ principle.title }}</h3>
                                <p>{{ principle.text }}</p>
                            </div>
                        </article>
                    </div>
                </div>
            </section>

            <section class="section-padding goals-section">
                <div class="container-fluid px-3 px-xl-4">
                    <div class="goals-heading">
                        <h2>Our Goals</h2>
                    </div>

                    <div class="goals-grid">
                        <article
                            v-for="goal in goalItems"
                            :key="goal.title"
                            class="goal-item"
                            :class="`goal-item-${goal.accent}`"
                        >
                            <div class="goal-icon">
                                <i :class="goal.icon" aria-hidden="true"></i>
                            </div>
                            <h3>{{ goal.title }}</h3>
                            <p>{{ goal.text }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="medical-services" class="medical-section section-padding">
                <div class="container-fluid px-3 px-xl-4">
                    <div class="text-center">
                        <h2 class="section-title">Comprehensive Care Services</h2>
                        <p class="section-kicker mb-5">Expert Care at Home and in Residential Settings</p>
                    </div>

                    <div class="care-services-grid">
                        <article
                            v-for="service in comprehensiveCareServices"
                            :key="service.title"
                            class="care-service-card"
                        >
                            <i :class="service.icon" aria-hidden="true"></i>
                            <h3>{{ service.title }}</h3>
                            <p>{{ service.text }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="caregiver-section section-padding">
                <div class="container-fluid section-wide-container">
                    <div class="caregiver-panel">
                        <div class="row align-items-center gy-4">
                            <div class="col-lg-8">
                                <span class="caregiver-kicker">Caregiver Opportunities</span>
                                <h2>Join Mauricare as a Healthcare Provider</h2>
                                <p>
                                    We are looking for compassionate and reliable healthcare providers to join our team and help us
                                    provide dignified home care support to families across Mauritius.
                                </p>
                            </div>

                            <div class="col-lg-4 text-lg-end">
                                <a href="/register?role=care_giver" class="btn caregiver-button fw-semibold">
                                    Click here to join our team
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="false" class="section-padding d-none">
                <div class="container">
                    <div class="row align-items-center gy-4">
                        <div class="col-lg-4">
                            <h2 class="section-title">Dentistry for Today’s<br />Lifestyle</h2>
                            <p class="section-kicker">We Have All Your Dental Needs Covered</p>
                        </div>

                        <div class="col-lg-8">
                            <div class="dental-grid">
                                <article v-for="(service, index) in dentalServices" :key="service" class="dental-card">
                                    <span>{{ String(index + 1).padStart(2, '0') }}.</span>
                                    <h3>{{ service }}</h3>
                                    <p>This is a short description detailing the service you have mentioned above.</p>
                                </article>
                                <article class="insurance-card">
                                    <span>For insurance</span>
                                    <a href="tel:0075678976">(007) 567-8976</a>
                                </article>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="false" class="stories-section section-padding d-none">
                <div class="container">
                    <div class="row gy-4">
                        <div class="col-lg-4">
                            <h2 class="section-title">Happy Stories</h2>
                            <p class="section-kicker">Creating Vibrant Smiles for Healthy Lifestyles!</p>
                            <div class="dot-pattern"></div>
                        </div>
                        <div class="col-lg-8">
                            <div class="row g-4">
                                <div v-for="person in stories" :key="person" class="col-md-6 col-xl-4">
                                    <article class="story-card">
                                        <span class="quote-mark">“</span>
                                        <p>
                                            Voluptatem aspernatur ullamcorper hymenaeos. Saepe pretium voluptas vel
                                            velit augue, eget. Recusandae rerum, mus commodo tempore deserunt.
                                        </p>
                                        <strong>{{ person }}</strong>
                                    </article>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="request-care" class="contact-section section-padding">
                <div class="container-fluid contact-section-container">
                    <div class="contact-panel mx-auto overflow-hidden rounded-3">
                        <div class="row g-0">
                            <div class="col-lg-5 contact-strip">
                                <div class="contact-intro">
                                    <span class="contact-kicker">Contact Us</span>
                                    <h2>Speak with Mauricare</h2>
                                    <p>
                                        Tell us what kind of care you need and our team will get back to you shortly.
                                    </p>
                                </div>

                                <div class="contact-method-list">
                                    <a class="contact-method" href="tel:+23058199909">
                                        <span class="contact-icon">
                                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                                        </span>
                                        <span>
                                            <strong>Call us</strong>
                                            +230 5819 9909
                                        </span>
                                    </a>

                                    <a class="contact-method" href="mailto:info@mauricare.mu">
                                        <span class="contact-icon">
                                            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                                        </span>
                                        <span>
                                            <strong>Email us</strong>
                                            info@mauricare.mu
                                        </span>
                                    </a>

                                    <div class="contact-method">
                                        <span class="contact-icon">
                                            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                                        </span>
                                        <span>
                                            <strong>Service area</strong>
                                            Islandwide care across Mauritius
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-7">
                                <form class="appointment-form" @submit.prevent="submitContact">
                                    <div v-if="contactSuccess" class="contact-success">
                                        {{ contactSuccess }}
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="contact-name">Name</label>
                                            <input id="contact-name" v-model="contactForm.name" class="form-control" type="text" required />
                                            <small v-if="contactForm.errors.name" class="contact-error">{{ contactForm.errors.name }}</small>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label" for="contact-phone">Phone</label>
                                            <input id="contact-phone" v-model="contactForm.phone" class="form-control" type="tel" required />
                                            <small v-if="contactForm.errors.phone" class="contact-error">{{ contactForm.errors.phone }}</small>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label" for="contact-email">Email</label>
                                            <input id="contact-email" v-model="contactForm.email" class="form-control" type="email" />
                                            <small v-if="contactForm.errors.email" class="contact-error">{{ contactForm.errors.email }}</small>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label" for="contact-message">Message</label>
                                            <textarea id="contact-message" v-model="contactForm.message" class="form-control" rows="5" required></textarea>
                                            <small v-if="contactForm.errors.message" class="contact-error">{{ contactForm.errors.message }}</small>
                                        </div>

                                        <div class="col-12">
                                            <button class="btn btn-primary fw-semibold px-4" type="submit" :disabled="contactForm.processing">
                                                {{ contactForm.processing ? 'Sending...' : 'Send Message' }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="stories-section section-padding">
                <div class="container-fluid section-wide-container">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-5">
                        <h2 class="testimonials-title mb-0">Testimonials</h2>

                        <div class="testimonial-controls d-flex gap-3">
                            <button
                                class="testimonial-control"
                                type="button"
                                data-bs-target="#testimonialCarousel"
                                data-bs-slide="prev"
                                aria-label="Previous testimonials"
                            >
                                <span aria-hidden="true">&larr;</span>
                            </button>
                            <button
                                class="testimonial-control"
                                type="button"
                                data-bs-target="#testimonialCarousel"
                                data-bs-slide="next"
                                aria-label="Next testimonials"
                            >
                                <span aria-hidden="true">&rarr;</span>
                            </button>
                        </div>
                    </div>

                    <div id="testimonialCarousel" class="carousel slide testimonial-carousel" data-bs-ride="false">
                        <div class="carousel-inner">
                            <div
                                v-for="(slide, slideIndex) in testimonialSlides"
                                :key="slideIndex"
                                class="carousel-item"
                                :class="{ active: slideIndex === 0 }"
                            >
                                <div class="row g-4">
                                    <div v-for="story in slide" :key="story.name" class="col-lg-4">
                                        <article class="story-card" :class="`story-card-${story.accent}`">
                                            <div class="stars" aria-label="5 star rating">
                                                <span v-for="star in 5" :key="star"></span>
                                            </div>

                                            <p>{{ story.text }}</p>

                                            <div class="story-person d-flex align-items-center gap-3">
                                                <span class="story-avatar">{{ story.initials }}</span>
                                                <div>
                                                    <strong>{{ story.name }}</strong>
                                                    <small>{{ story.role }}</small>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-mobile-list">
                        <div class="row g-4">
                            <div v-for="story in stories" :key="story.name" class="col-md-6">
                                <article class="story-card" :class="`story-card-${story.accent}`">
                                    <div class="stars" aria-label="5 star rating">
                                        <span v-for="star in 5" :key="star"></span>
                                    </div>

                                    <p>{{ story.text }}</p>

                                    <div class="story-person d-flex align-items-center gap-3">
                                        <span class="story-avatar">{{ story.initials }}</span>
                                        <div>
                                            <strong>{{ story.name }}</strong>
                                            <small>{{ story.role }}</small>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <MarketingFooter />
    </div>
</template>

<style scoped>
.mauricare-page {
    --primary-blue: #2586ff;
    --care-blue: #119bd3;
    --accent-pink: #ec1696;
    --ink: #3f434d;
    color: var(--ink);
    font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    background: #fff;
}


.main-title {
    margin-top: 55px;
}

.hero-section {
    position: relative;
    min-height: 800px;
    overflow: hidden;
    background:
        linear-gradient(90deg, rgba(255, 255, 255, 0.92) 0%, rgba(255, 255, 255, 0.72) 38%, rgba(255, 255, 255, 0.12) 72%),
        url('/images/mauricare-family.png') center / cover no-repeat;
}

.hero-section::after {
    content: "";
    position: absolute;
    left: 50%;
    bottom: -338px;
    width: 135vw;
    height: 340px;
    transform: translateX(-50%);
    border-radius: 50%;
    background: #fff;
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 1;
    padding-top: 105px;
    text-align: left;
}

@media (min-width: 1400px) {
    .hero-content.container {
        margin-left: calc((100vw - 1320px) / 4);
        margin-right: auto;
    }
}

.hero-content h1 {
    max-width: 560px;
    color: #071f48;
    font-size: clamp(2.7rem, 5.4vw, 5rem);
    line-height: 1.05;
}

.hero-content p {
    max-width: 520px;
    color: #071f48;
}

.hero-copy {
    font-size: clamp(1.05rem, 1.6vw, 1.55rem);
    font-weight: 700;
    line-height: 1.45;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem 1.4rem;
    margin-bottom: 3.1rem;
}

.hero-button {
    min-width: 188px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.65rem;
    padding: 0.9rem 2.1rem;
    border: 0;
    border-radius: 999px;
    font-size: 1rem;
    color: #fff;
    box-shadow: 0 14px 28px rgba(18, 34, 60, 0.16);
}

.hero-button-primary {
    background: #df1d7a;
}

.hero-button-primary:hover,
.hero-button-primary:focus {
    color: #fff;
    background: #c91369;
}

.hero-button-secondary {
    background: #0aa6bd;
}

.hero-button-secondary:hover,
.hero-button-secondary:focus {
    color: #fff;
    background: #078da1;
}

.hero-trust-strip {
    width: fit-content;
    max-width: 100%;
    display: grid;
    grid-template-columns: repeat(4, minmax(132px, 1fr));
    gap: 0;
    margin-bottom: 3rem;
    overflow: hidden;
    color: #071f48;
    border: 1px solid rgba(10, 95, 121, 0.14);
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.92);
    box-shadow: 0 18px 42px rgba(17, 51, 78, 0.12);
}

.hero-trust-item {
    min-height: 82px;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 1rem 1.25rem;
    font-size: 0.9rem;
    font-weight: 800;
    line-height: 1.2;
}

.hero-trust-item + .hero-trust-item {
    border-left: 1px solid rgba(10, 95, 121, 0.12);
}

.hero-trust-item i {
    color: #0aa6bd;
    font-size: 1.8rem;
}

:deep(.mauricare-nav) {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1030;
    padding-top: 10px;
    padding-bottom: 10px;
    background: transparent;
    box-shadow: none;
    transition: background-color 0.2s ease, box-shadow 0.2s ease, padding 0.2s ease;
}

:deep(.mauricare-nav.is-scrolled) {
    background: rgba(22, 24, 27, 0.92);
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.18);
}

:deep(.mauricare-nav .nav-link) {
    color: #071f48;
    font-size: 0.9rem;
    font-weight: 600;
    padding-right: 1rem;
    padding-left: 1rem;
}

:deep(.mauricare-nav.is-scrolled .nav-link) {
    color: rgba(255, 255, 255, 0.86);
}

:deep(.mauricare-nav .btn-outline-light) {
    color: #071f48;
    border-color: rgba(7, 31, 72, 0.5);
}

:deep(.mauricare-nav.is-scrolled .btn-outline-light) {
    color: #fff;
    border-color: rgba(255, 255, 255, 0.8);
}

:deep(.mauricare-nav .navbar-collapse) {
    visibility: visible;
}

:deep(.mauricare-nav .navbar-brand) {
    max-width: 290px;
}

:deep(.navbar-logo) {
    display: block;
    max-width: 100%;
    width: auto;
    height: 78px;
    object-fit: contain;
    transform: scale(1.50);
    transform-origin: left center;
    transition: height 0.2s ease, transform 0.2s ease;
}

:deep(.mauricare-nav.is-scrolled .navbar-logo) {
    height: 58px;
    transform: scale(1.40);
}

:deep(.brand-mark),
.brand-mark {
    width: 34px;
    height: 34px;
    display: inline-block;
    border-radius: 50%;
    background: linear-gradient(135deg, #179bd7 0 48%, #fff 48% 56%, #eb168e 56% 100%);
}

:deep(.brand-text) {
    font-size: 0.8rem;
    font-weight: 700;
}

.section-padding {
    padding: 100px 0;
}

.section-wide-container {
    max-width: 1560px;
    padding-right: clamp(1rem, 3vw, 2.5rem);
    padding-left: clamp(1rem, 3vw, 2.5rem);
}

.about-section {
    padding-top: 48px;
    background: linear-gradient(180deg, #f8fbff 0%, #eef8fb 100%);
}

.about-section .section-heading {
    max-width: 900px;
    margin-bottom: 2.4rem;
}

.about-section .section-title {
    color: #071f48;
}

.about-section .section-copy {
    max-width: 860px;
    margin-right: auto;
    margin-left: auto;
    color: #1d3154;
    font-weight: 700;
}

.partner-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 2rem;
}

.partner-card {
    padding: 1.8rem;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.72);
    box-shadow: 0 18px 42px rgba(34, 80, 118, 0.1);
}

.partner-card-header {
    width: fit-content;
    max-width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-right: auto;
    margin-bottom: 1.2rem;
    margin-left: auto;
}

.partner-card-header i {
    font-size: 2.1rem;
}

.partner-card-header h3 {
    max-width: 520px;
    margin: 0;
    font-size: clamp(1.35rem, 2vw, 1.8rem);
    font-weight: 800;
    line-height: 1.2;
    text-align: center;
}

.partner-feature-list {
    display: grid;
    gap: 2rem;
    padding: 1.8rem;
    border-radius: 8px;
    background: #fff;
}

.partner-feature {
    display: grid;
    grid-template-columns: 72px minmax(0, 1fr);
    gap: 1.3rem;
    align-items: start;
}

.partner-feature > i {
    font-size: 3.4rem;
    line-height: 1;
    text-align: center;
}

.partner-feature h4 {
    margin-bottom: 0.45rem;
    font-size: 1.02rem;
    font-weight: 800;
    line-height: 1.25;
}

.partner-feature p {
    margin: 0;
    color: #1d3154;
    font-size: 0.94rem;
    font-weight: 700;
    line-height: 1.55;
}

.partner-card-teal,
.principle-card-teal {
    --partner-accent: #0999a8;
}

.partner-card-blue,
.principle-card-blue {
    --partner-accent: #1168c8;
}

.partner-card-header,
.partner-feature > i,
.partner-feature h4,
.principle-card > i,
.principle-card h3 {
    color: var(--partner-accent);
}

.principle-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    margin-top: 2rem;
    overflow: hidden;
    border-radius: 0 0 8px 8px;
    background: #eaf6f9;
}

.principle-card {
    min-height: 230px;
    display: grid;
    grid-template-columns: 82px minmax(0, 1fr);
    gap: 1.4rem;
    align-items: start;
    padding: 2rem 3rem;
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.64), rgba(255, 255, 255, 0.18)),
        linear-gradient(135deg, rgba(9, 153, 168, 0.12), rgba(17, 104, 200, 0.1));
}

.principle-card + .principle-card {
    border-left: 1px solid rgba(17, 104, 200, 0.12);
}

.principle-card > i {
    font-size: 4rem;
    line-height: 1;
    text-align: center;
}

.principle-card h3 {
    margin-bottom: 0.55rem;
    font-size: 1.45rem;
    font-weight: 800;
}

.principle-card p {
    max-width: 470px;
    margin: 0;
    color: #1d3154;
    font-size: 0.94rem;
    font-weight: 700;
    line-height: 1.6;
}

.section-title {
    color: #41454f;
    font-size: clamp(2rem, 3vw, 2.85rem);
    font-weight: 800;
    line-height: 1.14;
    margin-bottom: 1rem;
}

.section-copy,
.section-kicker,
.benefit-list p,
.story-card p {
    color: #69707a;
    font-size: 0.96rem;
    line-height: 1.7;
}

.sub-title {
    color: #4c515c;
    font-size: 1rem;
    font-weight: 800;
    margin-bottom: 1.4rem;
}

.benefit-list {
    display: grid;
    gap: 1.8rem;
    margin-top: 2rem;
}

.benefit-list h3 {
    color: #424752;
    font-size: 1.05rem;
    font-weight: 800;
    margin-bottom: 0.35rem;
}

.icon-circle,
.contact-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eef6ff;
    color: var(--primary-blue);
    font-weight: 800;
}

.icon-circle i {
    font-size: 0.95rem;
}

.blank-image {
    min-height: 260px;
    display: grid;
    place-items: center;
    color: #8c97a4;
    border: 1px dashed #cbd5e1;
    background: linear-gradient(135deg, #f7fafc, #e9eef4);
}

.nurse-portrait {
    width: min(100%, 430px);
    min-height: 600px;
    border: 0;
    display: block;
    object-fit: contain;
}

.request-band {
    position: relative;
    padding: 72px 0;
    background: linear-gradient(90deg, var(--care-blue) 0 81%, var(--accent-pink) 81% 100%);
}

.request-form {
    display: grid;
    gap: 1.25rem;
    padding: 3.5rem;
    background: #f4f6fb;
}

.form-control {
    min-height: 42px;
    border: 1px solid #dfe5ec;
    border-radius: 2px;
    font-size: 0.9rem;
}

.btn-primary {
    --bs-btn-bg: var(--primary-blue);
    --bs-btn-border-color: var(--primary-blue);
    --bs-btn-hover-bg: #136fdc;
    --bs-btn-hover-border-color: #136fdc;
}

.goals-section {
    padding-bottom: 7.5rem;
    background: #fff;
}

.goals-heading {
    margin-bottom: 2rem;
    text-align: center;
}

.goals-heading h2 {
    display: inline-block;
    position: relative;
    margin: 0;
    color: #41454f;
    font-size: clamp(2rem, 3vw, 2.85rem);
    font-weight: 800;
    line-height: 1.14;
}

.goals-heading h2::after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: -0.5rem;
    width: 72px;
    height: 3px;
    border-radius: 999px;
    background: #119bd3;
    transform: translateX(-50%);
}

.goals-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 1.55rem;
    max-width: 1400px;
    margin: 0 auto;
}

.goal-item {
    min-height: 285px;
    display: grid;
    align-content: start;
    justify-items: center;
    gap: 1.15rem;
    padding: 1.9rem 1.15rem 1.55rem;
    text-align: center;
    border: 1px solid #e5edf5;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 16px 34px rgba(17, 75, 112, 0.08);
}

.goal-icon {
    min-height: 84px;
    display: grid;
    place-items: center;
    color: var(--goal-color);
    font-size: 3.4rem;
    line-height: 1;
    text-align: center;
}

.goal-item h3 {
    min-height: 52px;
    display: grid;
    place-items: center;
    max-width: 190px;
    margin: 0 0 0.25rem;
    color: var(--goal-color);
    font-size: 1.02rem;
    font-weight: 800;
    line-height: 1.25;
}

.goal-item p {
    max-width: 210px;
    margin: 0;
    color: #1d3154;
    font-size: 0.94rem;
    font-weight: 700;
    line-height: 1.55;
}

.goal-item-teal {
    --goal-color: #17a9bd;
}

.goal-item-green {
    --goal-color: #239642;
}

.goal-item-blue {
    --goal-color: #2468c9;
}

.goal-item-pink {
    --goal-color: #e22c79;
}

.goal-item-orange {
    --goal-color: #f39a21;
}

.medical-section,
.contact-section {
    background: #f3f8ff;
}

.care-services-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 1.55rem;
    max-width: 1400px;
    margin: 0 auto;
}

.care-service-card {
    min-height: 285px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding: 1.9rem 1.15rem 1.55rem;
    border-radius: 8px;
    color: #fff;
    background: #119bd3;
    text-align: center;
    box-shadow: 0 16px 34px rgba(17, 75, 112, 0.16);
}

.care-service-card i {
    min-height: 74px;
    display: grid;
    place-items: center;
    color: #dff6c0;
    font-size: 3.75rem;
    line-height: 1;
}

.care-service-card h3 {
    min-height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 1.05rem 0 0.75rem;
    color: #fff;
    font-size: 1rem;
    font-weight: 800;
    line-height: 1.2;
}

.care-service-card p {
    margin: 0;
    color: rgba(255, 255, 255, 0.88);
    font-size: 0.9rem;
    font-weight: 700;
    line-height: 1.55;
}

.section-heading {
    max-width: 860px;
}

.care-bundle-card {
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #e6ebf1;
    border-radius: 6px;
    background: #fff;
    box-shadow: 0 12px 28px rgba(43, 63, 95, 0.08);
}

.bundle-image {
    width: calc(100% - 2.2rem);
    height: 200px;
    margin: 1.1rem 1.1rem 0;
    display: block;
    object-fit: cover;
    border: 0;
    background: #f7fafc;
}

.bundle-body {
    flex: 1;
    padding: 1.35rem 1.35rem 1rem;
}

.bundle-body h3 {
    color: #414752;
    font-size: 1.05rem;
    font-weight: 800;
    line-height: 1.35;
    margin-bottom: 1rem;
}

.bundle-body ul {
    display: grid;
    gap: 0.55rem;
    margin: 0;
    padding-left: 1.1rem;
}

.bundle-body li {
    color: #69707a;
    font-size: 0.9rem;
    line-height: 1.55;
}

.caregiver-section {
    background: #fff;
}

.caregiver-section .section-wide-container {
    max-width: 1400px;
    padding-right: 0;
    padding-left: 0;
}

.caregiver-panel {
    padding: 3rem;
    border-radius: 8px;
    color: #fff;
    background:
        linear-gradient(90deg, rgba(17, 155, 211, 0.95), rgba(236, 22, 150, 0.82)),
        url('/images/mauricare-home-care-nurse-professional-caregiver.png') center 20% / cover no-repeat;
}

.caregiver-kicker {
    display: block;
    margin-bottom: 0.7rem;
    color: rgba(255, 255, 255, 0.82);
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0;
    text-transform: uppercase;
}

.caregiver-panel h2 {
    margin-bottom: 1rem;
    color: #fff;
    font-size: clamp(1.9rem, 3vw, 2.6rem);
    font-weight: 800;
    line-height: 1.14;
}

.caregiver-panel p {
    max-width: 760px;
    margin: 0;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.98rem;
    line-height: 1.7;
}

.caregiver-button {
    max-width: 100%;
    padding: 0.9rem 1.4rem;
    color: #17404a;
    white-space: normal;
    background: #dff6c0;
    border: 0;
    border-radius: 8px;
}

.caregiver-button:hover,
.caregiver-button:focus {
    color: #17404a;
    background: #cbec9f;
}

.dental-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    overflow: hidden;
    border-bottom-right-radius: 120px;
    background: var(--primary-blue);
}

.dental-card {
    min-height: 165px;
    padding: 1.4rem;
    color: #fff;
    background:
        linear-gradient(rgba(35, 39, 44, 0.68), rgba(35, 39, 44, 0.68)),
        linear-gradient(135deg, #737b85, #333940);
}

.dental-card span,
.insurance-card span {
    display: block;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
}

.dental-card h3 {
    font-size: 1rem;
    font-weight: 800;
    margin: 0.5rem 0;
}

.dental-card p {
    margin: 0;
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.78rem;
}

.insurance-card {
    grid-column: span 2;
    min-height: 120px;
    padding: 1.6rem;
    color: #fff;
    background: var(--primary-blue);
}

.insurance-card a {
    display: inline-block;
    margin-top: 0.5rem;
    color: #fff;
    font-size: 1.45rem;
    font-weight: 800;
    text-decoration: none;
}

.offer-band {
    padding: 80px 0;
    background: linear-gradient(90deg, rgba(37, 134, 255, 0.72), var(--primary-blue));
}

.offer-image {
    min-height: 280px;
    border: 0;
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
}

.stories-section {
    background: #f8fafc;
}

.story-card {
    min-height: 360px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 2.1rem;
    background: #fff;
    border: 1px solid #d4dbe5;
    border-radius: 18px;
    box-shadow: none;
}

.story-card-blue {
    border-color: #4b46ff;
}

.testimonials-title {
    color: #41454f;
    font-size: clamp(2rem, 3vw, 2.85rem);
    font-weight: 800;
    line-height: 1.14;
}

.testimonial-control {
    width: 52px;
    height: 52px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #4b46ff;
    border-radius: 50%;
    color: #4b46ff;
    background: transparent;
    font-size: 1.55rem;
    line-height: 1;
    transition: 0.2s ease;
}

.testimonial-control:hover,
.testimonial-control:focus {
    color: #fff;
    background: #4b46ff;
}

.testimonial-mobile-list {
    display: none;
}

.stars {
    display: flex;
    gap: 0.45rem;
    margin-bottom: 2rem;
}

.stars span::before {
    content: "\2605";
    color: #f59a00;
    font-size: 1.45rem;
    line-height: 1;
}

.story-card-blue .stars span::before {
    color: #4b46ff;
}

.story-card p {
    color: #69707a;
    font-size: 0.96rem;
    line-height: 1.7;
    margin-bottom: 2.25rem;
}

.story-person strong {
    display: block;
    color: #414752;
    font-size: 0.96rem;
    font-weight: 800;
}

.story-person small {
    display: block;
    color: #69707a;
    font-size: 0.86rem;
    margin-top: 0.3rem;
}

.story-card-blue .story-person strong {
    color: #4b46ff;
}

.story-avatar {
    width: 54px;
    height: 54px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 50%;
    color: #fff;
    font-size: 0.9rem;
    font-weight: 800;
    background: linear-gradient(135deg, #119bd3, #ec1696);
}

.story-card-orange .story-avatar {
    background: linear-gradient(135deg, #f6b33b, #f07f1a);
}

.contact-section {
    padding: 100px 0;
}

.contact-section-container {
    max-width: 1400px;
    padding-right: 0;
    padding-left: 0;
}

.contact-panel {
    max-width: 1400px;
    background: #fff;
    box-shadow: 0 22px 54px rgba(42, 66, 98, 0.12);
}

.contact-strip {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 2rem;
    padding: 3rem 2rem;
    color: #fff;
    background:
        linear-gradient(145deg, rgba(17, 155, 211, 0.98), rgba(37, 134, 255, 0.94)),
        url('/images/mauricare-home-care-blood-test-nurse.png') center / cover no-repeat;
}

.contact-intro h2 {
    margin: 0.55rem 0 1rem;
    color: #fff;
    font-size: clamp(2rem, 4vw, 2.9rem);
    font-weight: 800;
    line-height: 1.08;
}

.contact-intro p {
    margin: 0;
    color: rgba(255, 255, 255, 0.88);
    font-size: 0.98rem;
    line-height: 1.7;
}

.contact-kicker {
    color: #dff6c0;
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: uppercase;
}

.contact-method-list {
    display: grid;
    gap: 0.75rem;
}

.contact-method {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: 8px;
    color: rgba(255, 255, 255, 0.86);
    background: rgba(255, 255, 255, 0.08);
    text-decoration: none;
}

.contact-method strong {
    display: block;
    color: #fff;
    font-size: 0.9rem;
}

.contact-strip .contact-icon {
    flex: 0 0 auto;
    color: #119bd3;
    background: #dff6c0;
}

.appointment-form {
    padding: 3rem 2rem;
}

.appointment-form .form-label {
    color: #59606b;
    font-size: 0.82rem;
    font-weight: 700;
}

.contact-success {
    margin-bottom: 1rem;
    border-radius: 8px;
    padding: 0.8rem 1rem;
    color: #12643a;
    background: #e9f8ef;
    font-size: 0.92rem;
    font-weight: 800;
}

.contact-error {
    display: block;
    margin-top: 0.35rem;
    color: #c52828;
    font-size: 0.8rem;
    font-weight: 700;
}

:deep(.site-footer) {
    background: #181818;
    color: #fff;
}

:deep(.footer-brand) {
    color: #fff;
    font-size: 0.9rem;
    font-weight: 700;
}

:deep(.footer-socials a) {
    color: #fff;
    font-weight: 700;
    text-decoration: none;
}

:deep(.footer-bottom) {
    margin-top: 0px;
    padding: 26px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    color: rgba(255, 255, 255, 0.72);
    font-size: 0.82rem;
}

@media (max-width: 991.98px) {
    .hero-section {
        min-height: 680px;
    }

    :deep(.mauricare-nav .navbar-brand) {
        max-width: 220px;
    }

    :deep(.navbar-logo) {
        height: 62px;
        transform: scale(1.18);
    }

    :deep(.mauricare-nav.is-scrolled .navbar-logo) {
        height: 50px;
        transform: scale(1.12);
    }

    .hero-content {
        padding-top: 115px;
    }

    .hero-trust-strip {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .hero-trust-item:nth-child(3) {
        border-left: 0;
    }

    .hero-trust-item:nth-child(n + 3) {
        border-top: 1px solid rgba(10, 95, 121, 0.12);
    }

    .section-padding {
        padding: 72px 0;
    }

    .about-section {
        padding-top: 40px;
    }

    .partner-grid,
    .principle-grid {
        grid-template-columns: 1fr;
    }

    .principle-card + .principle-card {
        border-top: 1px solid rgba(17, 104, 200, 0.12);
        border-left: 0;
    }

    .request-band {
        background: var(--care-blue);
    }

    .dental-grid {
        grid-template-columns: repeat(2, 1fr);
        border-bottom-right-radius: 64px;
    }

    .testimonial-controls,
    .testimonial-carousel {
        display: none;
    }

    .testimonial-mobile-list {
        display: block;
    }

    .story-card {
        min-height: 320px;
    }

    .care-services-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .care-service-card {
        min-height: 250px;
    }

    .goals-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }
}

@media (max-width: 575.98px) {
    .hero-content h1 {
        max-width: 100%;
    }

    .hero-actions {
        gap: 0.8rem;
        margin-bottom: 2rem;
    }

    .hero-button {
        width: 100%;
    }

    .hero-trust-strip {
        width: 100%;
        grid-template-columns: 1fr;
    }

    .hero-trust-item {
        min-height: 70px;
        padding: 0.85rem 1rem;
    }

    .hero-trust-item + .hero-trust-item,
    .hero-trust-item:nth-child(3) {
        border-left: 0;
        border-top: 1px solid rgba(10, 95, 121, 0.12);
    }

    .hero-section::after {
        bottom: -178px;
        width: 150vw;
        height: 180px;
    }

    .nurse-portrait {
        min-height: 360px;
    }

    .goals-grid {
        grid-template-columns: 1fr;
    }

    .goal-item,
    .goal-item:last-child {
        min-height: auto;
        padding: 1.25rem;
    }

    .goal-icon {
        min-height: 64px;
        font-size: 2.65rem;
    }

    .request-form,
    .appointment-form {
        padding: 1.5rem;
    }

    .partner-card {
        padding: 1rem;
    }

    .caregiver-section .section-wide-container {
        padding-right: 1rem;
        padding-left: 1rem;
    }

    .contact-section-container {
        padding-right: 1rem;
        padding-left: 1rem;
    }

    .partner-feature-list {
        gap: 1.45rem;
        padding: 1.25rem;
    }

    .partner-feature {
        grid-template-columns: 48px minmax(0, 1fr);
        gap: 1rem;
    }

    .partner-feature > i {
        font-size: 2.55rem;
    }

    .principle-card {
        grid-template-columns: 54px minmax(0, 1fr);
        gap: 1rem;
        padding: 1.5rem 1.25rem;
    }

    .principle-card > i {
        font-size: 3rem;
    }

    .dental-grid {
        grid-template-columns: 1fr;
        border-bottom-right-radius: 42px;
    }

    .insurance-card {
        grid-column: span 1;
    }

    .care-services-grid {
        grid-template-columns: 1fr;
    }
}
</style>
