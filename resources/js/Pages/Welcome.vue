<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import MarketingFooter from '@/Components/MarketingFooter.vue';
import MarketingNav from '@/Components/MarketingNav.vue';

const benefits = [
    {
        title: 'Professional Care at Home',
        text: 'Our experienced nurses provide personalized medical care in the comfort of your home, ensuring safety, dignity, and quality treatment without the need to travel.',
    },
    {
        title: 'Experienced Healthcare Professionals',
        text: 'Our team includes qualified nurses and healthcare providers trained to deliver reliable and professional home care services tailored to each patient’s needs.',
    },
    {
        title: 'Personalized Care Plan',
        text: 'We tailor our healthcare services to meet each patient’s specific needs, ensuring the right level of medical attention, comfort, and support at every stage of care.',
    },
];

const medicalServices = [
    'Injection & IV Therapy',
    'NGT & Catheter Care',
    'Wound Dressing',
    'Bedsore Treatment',
    'Oxygen Therapy',
];

const careBundles = [
    {
        title: 'Wound Care Package',
        points: ['Sterile wound cleaning and dressing', 'Healing progress monitoring', 'Guidance for faster recovery'],
    },
    {
        title: 'Post-Hospital Recovery',
        points: ['Medication and vital sign support', 'Mobility and comfort assistance', 'Follow-up care coordination'],
    },
    {
        title: 'Bedridden Care Plan',
        points: ['Daily hygiene and positioning', 'Bedsore prevention support', 'Compassionate routine care'],
    },
    {
        title: 'Home ICU Support',
        points: ['Advanced nursing observation', 'Oxygen and equipment assistance', 'Family support and reporting'],
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
    <Head title="Home Care Services" />

    <div class="mauricare-page">
        <header id="home" class="hero-section">
            <MarketingNav />

            <div class="container hero-content text-center text-white">
                <h1 class="fw-bold mb-3">Professional Home<br />Nursing &amp; Care Services</h1>
                <p class="mx-auto mb-4">
                    We provide compassionate, reliable and professional care in the comfort of your home.
                </p>
                <a href="#appointment" class="btn btn-primary btn-sm fw-semibold px-4">Book a Home Visit</a>
            </div>
        </header>

        <main>
            <section id="about" class="section-padding about-section">
                <div class="container">
                    <div class="row align-items-center gy-5">
                        <div class="col-lg-6">
                            <h2 class="section-title">Why Choose Mauricare<br />Home Care Services</h2>
                            <p class="section-copy">
                                We provide professional and compassionate healthcare services directly at your home.
                                Our qualified nurses and healthcare professionals ensure high-quality medical care,
                                comfort, and peace of mind for patients and families.
                            </p>

                            <div class="benefit-list">
                                <article v-for="benefit in benefits" :key="benefit.title" class="d-flex gap-3">
                                    <span class="icon-circle flex-shrink-0">+</span>
                                    <div>
                                        <h3>{{ benefit.title }}</h3>
                                        <p>{{ benefit.text }}</p>
                                    </div>
                                </article>
                            </div>
                        </div>

                        <div class="col-lg-6 text-center">
                            <div class="blank-image nurse-portrait mx-auto">
                                <span>Image placeholder</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="request-band">
                <div class="container">
                    <div class="row align-items-center gy-4">
                        <div class="col-lg-5 text-white">
                            <h2 class="fw-bold mb-3">Request Home Care<br />Services</h2>
                            <p class="mb-4">
                                Fill in your details and our team will contact you shortly to discuss your needs and
                                arrange professional home healthcare services.
                            </p>
                            <span class="small text-uppercase fw-bold">Need assistance?</span>
                            <a class="d-block text-white fw-bold text-decoration-none mt-1" href="tel:+23058199009">
                                +(230) 5819-9009
                            </a>
                        </div>

                        <div class="col-lg-5 offset-lg-1">
                            <form class="request-form">
                                <input class="form-control" type="text" placeholder="Your Name *" aria-label="Your Name" />
                                <input class="form-control" type="email" placeholder="Your Email *" aria-label="Your Email" />
                                <input class="form-control" type="tel" placeholder="Your Phone Number *" aria-label="Your Phone Number" />
                                <button class="btn btn-primary btn-sm fw-semibold" type="submit">Send Request</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-padding philosophy-section">
                <div class="container">
                    <div class="row align-items-center gy-5">
                        <div class="col-lg-5">
                            <div class="care-card">
                                <div class="blank-image care-image">
                                    <span>Image placeholder</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 offset-lg-1">
                            <h2 class="section-title">Our Philosophy</h2>
                            <h3 class="sub-title">Care with Compassion, Service with Heart</h3>
                            <p>
                                At Mauricare Home Care Service, we believe that every individual deserves to be treated
                                with dignity, respect, and kindness. Our philosophy is centered on delivering
                                personalized, professional, and compassionate healthcare in the comfort of your home.
                            </p>
                            <p>
                                We are committed to supporting patients and their families by providing reliable medical
                                care, emotional support, and a sense of security.
                            </p>
                            <p>Your well-being is our priority, and your trust is our greatest reward.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="medical-services" class="medical-section section-padding">
                <div class="container text-center">
                    <h2 class="section-title">Medical Services &amp; Procedures</h2>
                    <p class="section-kicker mb-5">Expert Nursing Care at Home</p>

                    <div class="row g-3 justify-content-center">
                        <div v-for="service in medicalServices" :key="service" class="col-6 col-md">
                            <article class="service-tile">{{ service }}</article>
                        </div>
                    </div>
                </div>
            </section>

            <section id="care-packages" class="section-padding">
                <div class="container">
                    <div class="section-heading text-center mx-auto mb-5">
                        <div class="heading-with-lines">
                            <span></span>
                            <h2 class="section-title mb-0">Specialized Care Bundles</h2>
                            <span></span>
                        </div>
                        <p class="section-kicker mt-3 mb-0">Tailored Care for Faster Recovery</p>
                    </div>

                    <div class="row g-4">
                        <div v-for="bundle in careBundles" :key="bundle.title" class="col-md-6 col-xl-3">
                            <article class="care-bundle-card">
                                <div class="bundle-image">
                                    <span>Image placeholder</span>
                                </div>

                                <div class="bundle-body">
                                    <h3>{{ bundle.title }}</h3>
                                    <ul>
                                        <li v-for="point in bundle.points" :key="point">{{ point }}</li>
                                    </ul>
                                </div>
                            </article>
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

            <section class="stories-section section-padding">
                <div class="container">
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

            <section class="stories-section section-padding d-none">
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

.hero-section {
    position: relative;
    min-height: 800px;
    overflow: hidden;
    background:
        linear-gradient(rgba(22, 24, 27, 0.66), rgba(22, 24, 27, 0.66)),
        url('/images/mauricare-home-care-nurse-elderly-home-visit.png') center / cover no-repeat;
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
    padding-top: 90px;
}

.hero-content h1 {
    font-size: clamp(2.4rem, 5vw, 4.2rem);
    line-height: 1.12;
}

.hero-content p {
    max-width: 620px;
    color: rgba(255, 255, 255, 0.86);
}

:deep(.mauricare-nav) {
    position: relative;
    z-index: 2;
    padding-top: 28px;
}

:deep(.mauricare-nav .nav-link) {
    color: rgba(255, 255, 255, 0.86);
    font-size: 0.9rem;
    font-weight: 600;
    padding-right: 1rem;
    padding-left: 1rem;
}

:deep(.mauricare-nav .navbar-collapse) {
    visibility: visible;
}

:deep(.mauricare-nav .navbar-brand) {
    max-width: 540px;
}

:deep(.navbar-logo) {
    display: block;
    max-width: 100%;
    width: auto;
    height: 138px;
    object-fit: contain;
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

.about-section {
    padding-top: 48px;
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
.philosophy-section p,
.story-card p {
    color: #69707a;
    font-size: 0.96rem;
    line-height: 1.7;
}

.philosophy-section {
    padding-bottom: 20px;
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
    background: #f8fbff;
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

.care-card {
    position: relative;
    max-width: 420px;
    padding: 0 0 72px 38px;
}

.care-card::after {
    content: "";
    position: absolute;
    right: 0;
    bottom: 0;
    width: 190px;
    height: 255px;
    border-radius: 0 0 95px 95px;
    background: #fff;
    box-shadow: 0 25px 55px rgba(41, 51, 72, 0.12);
    z-index: -1;
}

.care-image {
    min-height: 470px;
}

.medical-section,
.contact-section {
    background: #f3f8ff;
}

.service-tile {
    min-height: 150px;
    display: grid;
    place-items: end center;
    padding: 1.5rem 0.75rem;
    background: #fff;
    color: #6b7280;
    font-size: 0.86rem;
    box-shadow: 0 8px 25px rgba(56, 75, 105, 0.04);
}

.section-heading {
    max-width: 860px;
}

.heading-with-lines {
    display: grid;
    grid-template-columns: minmax(40px, 1fr) auto minmax(40px, 1fr);
    align-items: center;
    gap: 1.5rem;
}

.heading-with-lines span {
    height: 1px;
    background: #dde5ee;
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
    min-height: 132px;
    margin: 1.1rem 1.1rem 0;
    display: grid;
    place-items: center;
    color: #8c97a4;
    border: 1px dashed #cbd5e1;
    background: linear-gradient(135deg, #f7fafc, #e9eef4);
    font-size: 0.82rem;
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
    padding: 0 0 88px;
}

.contact-panel {
    max-width: 990px;
    background: #fff;
    box-shadow: 0 22px 54px rgba(42, 66, 98, 0.12);
}

.contact-strip {
    background: var(--primary-blue);
}

.contact-method {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.6rem 2rem;
}

.contact-method a {
    display: block;
    color: rgba(255, 255, 255, 0.84);
    font-size: 0.78rem;
    text-decoration: none;
}

.appointment-form {
    padding: 3rem 2rem;
}

.appointment-form .form-label {
    color: #59606b;
    font-size: 0.82rem;
    font-weight: 700;
}

:deep(.site-footer) {
    background: #181818;
    color: #fff;
    padding-top: 90px;
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
    margin-top: 90px;
    padding: 26px 0;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    color: rgba(255, 255, 255, 0.72);
    font-size: 0.82rem;
}

@media (max-width: 991.98px) {
    .hero-section {
        min-height: 680px;
    }

    .hero-content {
        padding-top: 80px;
    }

    .section-padding {
        padding: 72px 0;
    }

    .about-section {
        padding-top: 40px;
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
}

@media (max-width: 575.98px) {
    .hero-section::after {
        bottom: -178px;
        width: 150vw;
        height: 180px;
    }

    .nurse-portrait {
        min-height: 360px;
    }

    .request-form,
    .appointment-form {
        padding: 1.5rem;
    }

    .dental-grid {
        grid-template-columns: 1fr;
        border-bottom-right-radius: 42px;
    }

    .insurance-card {
        grid-column: span 1;
    }
}
</style>
