<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import MarketingFooter from '@/Components/MarketingFooter.vue';
import MarketingNav from '@/Components/MarketingNav.vue';

const benefits = [
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

const medicalServices = [
    { title: 'Injection & IV Therapy', icon: 'fa-solid fa-syringe' },
    { title: 'NGT & Catheter Care', icon: 'fa-solid fa-kit-medical' },
    { title: 'Wound Dressing', icon: 'fa-solid fa-bandage' },
    { title: 'Bedsore Treatment', icon: 'fa-solid fa-bed-pulse' },
    { title: 'Oxygen Therapy', icon: 'fa-solid fa-lungs' },
    { title: 'Monthly Doctor Visits', icon: 'fa-solid fa-user-doctor' },
    { title: 'Cashless Visits', icon: 'fa-solid fa-credit-card' },
    { title: 'Elderly Home Doctor Visits', icon: 'fa-solid fa-person-cane' },
    { title: 'Teleconsultation', icon: 'fa-solid fa-video' },
    { title: 'Blood Collection', icon: 'fa-solid fa-vial' },
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

const philosophyItems = [
    'Compassionate nursing care',
    'Personalized home visits',
    'Post-hospital recovery support',
    'Wound dressing and care',
    'Medication reminders',
    'Vital signs monitoring',
    'Bedridden patient support',
    'Elderly companionship',
    'Hygiene and comfort care',
    'Mobility assistance',
    'Family guidance',
    'Oxygen therapy support',
    'Bedsore prevention',
    'Catheter and NGT care',
    'Palliative care support',
    'Regular health check-ins',
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

const medicalServiceSlides = computed(() => {
    const slides = [];

    for (let index = 0; index < medicalServices.length; index += 5) {
        slides.push(medicalServices.slice(index, index + 5));
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
        <meta property="og:image" content="https://mauricare.mu/images/mauricare-home-care-nurse-elderly-home-visit.png" />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <div class="mauricare-page">
        <header id="home" class="hero-section">
            <MarketingNav />

            <div class="container hero-content text-center text-white">
                <h1 class="fw-bold mb-4 main-title">Professional Home<br />Nursing &amp; Care Services</h1>
                <p class="mx-auto mb-5">
                    We provide compassionate, reliable and professional care in the comfort of your home.
                </p>
                <a href="#appointment" class="btn btn-primary hero-button fw-semibold">Book a Home Visit</a>
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
                                    <span class="icon-circle flex-shrink-0">
                                        <i :class="benefit.icon" aria-hidden="true"></i>
                                    </span>
                                    <div>
                                        <h3>{{ benefit.title }}</h3>
                                        <p>{{ benefit.text }}</p>
                                    </div>
                                </article>
                            </div>
                        </div>

                        <div class="col-lg-6 text-center">
                            <img
                                src="/images/mauricare-home-care-nurse-professional-caregiver.png"
                                alt="Mauricare professional caregiver"
                                class="nurse-portrait mx-auto"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section id="request-care" class="request-band">
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
                                <input class="form-control" type="email" placeholder="Your Email" aria-label="Your Email" />
                                <input class="form-control" type="tel" placeholder="Your Phone Number *" aria-label="Your Phone Number" />
                                <textarea
                                    class="form-control"
                                    rows="4"
                                    placeholder="Description"
                                    aria-label="Description"
                                ></textarea>
                                <button class="btn btn-primary btn-sm fw-semibold" type="submit">Send Request</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-padding philosophy-section">
                <div class="container-fluid px-0">
                    <div class="philosophy-panel">
                        <div class="row align-items-center gy-5">
                            <div class="col-lg-6">
                                <h2>Our Philosophy</h2>
                                <p>
                                    At Mauricare Home Care Service, we believe that every individual deserves to be treated
                                    with dignity, respect, and kindness. Our philosophy is centered on delivering
                                    personalized, professional, and compassionate healthcare in the comfort of your home.
                                </p>
                                <a href="tel:+23058199009" class="btn philosophy-call fw-semibold">
                                    Call Now - 5819 9009
                                </a>
                            </div>

                            <div class="col-lg-6">
                                <div class="philosophy-list">
                                    <div
                                        v-for="item in philosophyItems"
                                        :key="item"
                                        class="philosophy-list-item"
                                    >
                                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                                        <span>{{ item }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="medical-services" class="medical-section section-padding">
                <div class="container-fluid px-3 px-xl-4">
                    <div class="text-center">
                        <h2 class="section-title">Medical Services &amp; Procedures</h2>
                        <p class="section-kicker mb-5">Expert Nursing Care at Home</p>
                    </div>

                    <div class="medical-carousel-wrap">
                        <button
                            class="medical-carousel-control"
                            type="button"
                            data-bs-target="#medicalServicesCarousel"
                            data-bs-slide="prev"
                            aria-label="Previous medical services"
                        >
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        </button>

                        <div id="medicalServicesCarousel" class="carousel slide medical-carousel" data-bs-ride="false">
                            <div class="carousel-inner">
                                <div
                                    v-for="(slide, slideIndex) in medicalServiceSlides"
                                    :key="slideIndex"
                                    class="carousel-item"
                                    :class="{ active: slideIndex === 0 }"
                                >
                                    <div class="row g-4 justify-content-center">
                                        <div v-for="service in slide" :key="service.title" class="col-sm-6 service-col">
                                    <article class="service-tile">
                                        <i :class="service.icon" aria-hidden="true"></i>
                                        <h3>{{ service.title }}</h3>
                                    </article>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            class="medical-carousel-control"
                            type="button"
                            data-bs-target="#medicalServicesCarousel"
                            data-bs-slide="next"
                            aria-label="Next medical services"
                        >
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </section>

            <section id="care-packages" class="section-padding">
                <div class="container-fluid section-wide-container">
                    <div class="section-heading text-center mx-auto mb-5">
                        <h2 class="section-title mb-0">Specialized Care Bundles</h2>
                        <p class="section-kicker mt-3 mb-0">Tailored Care for Faster Recovery</p>
                    </div>

                    <div class="row g-4">
                        <div v-for="bundle in careBundles" :key="bundle.title" class="col-md-6 col-xl-3">
                            <article class="care-bundle-card">
                                <img :src="bundle.image" :alt="`${bundle.title} home care service`" class="bundle-image" />

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

            <section class="caregiver-section section-padding">
                <div class="container-fluid section-wide-container">
                    <div class="caregiver-panel">
                        <div class="row align-items-center gy-4">
                            <div class="col-lg-8">
                                <span class="caregiver-kicker">Caregiver Opportunities</span>
                                <h2>Join Mauricare as a Caregiver</h2>
                                <p>
                                    We are looking for compassionate and reliable carers to join our team and help us
                                    provide dignified home care support to families across Mauritius.
                                </p>
                            </div>

                            <div class="col-lg-4 text-lg-end">
                                <a href="/login" class="btn caregiver-button fw-semibold">
                                    Click here to join our team of carers
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
    margin-top: 100px;
}

.caregiver-section {
    padding-top : 0px!important;
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
    padding-top: 160px;
}

.hero-content h1 {
    font-size: clamp(2.4rem, 5vw, 4.2rem);
    line-height: 1.12;
}

.hero-content p {
    max-width: 620px;
    color: rgba(255, 255, 255, 0.86);
}

.hero-button {
    min-width: 220px;
    padding: 0.9rem 2.1rem;
    border-radius: 6px;
    font-size: 1rem;
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
    transition: background-color 0.2s ease, box-shadow 0.2s ease, padding 0.2s ease;
}

:deep(.mauricare-nav.is-scrolled) {
    background: rgba(22, 24, 27, 0.92);
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.18);
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

.philosophy-panel {
    position: relative;
    overflow: hidden;
    padding: 5.5rem;
    color: #fff;
    border-top-left-radius: 120px;
    border-bottom-right-radius: 120px;
    background:
        linear-gradient(90deg, rgba(14, 77, 77, 0.92), rgba(25, 47, 56, 0.78)),
        url('/images/mauricare-home-care-blood-test-nurse.png') center / cover no-repeat;
}

.philosophy-panel h2 {
    max-width: 560px;
    margin-bottom: 1.5rem;
    color: #fff;
    font-size: clamp(2rem, 3vw, 2.85rem);
    font-weight: 800;
    line-height: 1.14;
}

.philosophy-panel p {
    max-width: 670px;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.96rem;
    font-weight: 500;
    line-height: 1.7;
}

.philosophy-call {
    margin-top: 1.8rem;
    padding: 0.9rem 1.8rem;
    color: #102b2b;
    background: #dff6c0;
    border: 0;
    border-radius: 8px;
}

.philosophy-call:hover,
.philosophy-call:focus {
    color: #102b2b;
    background: #cbec9f;
}

.philosophy-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem 1.25rem;
}

.philosophy-list-item {
    min-height: 54px;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.85rem 1rem;
    color: rgba(255, 255, 255, 0.94);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.08);
    font-size: 0.92rem;
    font-weight: 700;
}

.philosophy-list-item i {
    color: #dff6c0;
}

.medical-section,
.contact-section {
    background: #f3f8ff;
}

.service-tile {
    min-height: 245px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1.15rem;
    padding: 2rem 1.35rem;
    color: #fff;
    background: #119bd3;
    border-radius: 14px;
    text-align: center;
    box-shadow: 0 12px 28px rgba(43, 63, 95, 0.08);
}

.service-tile i {
    color: #dff6c0;
    font-size: 2.8rem;
}

.service-tile h3 {
    min-height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    color: #fff;
    font-size: 1.05rem;
    font-weight: 800;
    line-height: 1.15;
}

.medical-carousel-wrap {
    display: grid;
    grid-template-columns: 48px minmax(0, 1fr) 48px;
    align-items: center;
    gap: 0.9rem;
}

.medical-carousel-control {
    width: 48px;
    height: 48px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(17, 155, 211, 0.55);
    border-radius: 50%;
    color: #41454f;
    background: #fff;
    transition: 0.2s ease;
}

@media (min-width: 1200px) {
    .service-col {
        flex: 0 0 20%;
        max-width: 20%;
    }
}

.medical-carousel-control:hover,
.medical-carousel-control:focus {
    color: #fff;
    background: #119bd3;
    border-color: #119bd3;
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

.caregiver-panel {
    padding: 3rem;
    border-radius: 8px;
    color: #fff;
    background:
        linear-gradient(90deg, rgba(17, 155, 211, 0.95), rgba(236, 22, 150, 0.82)),
        url('/images/mauricare-home-care-nurse-professional-caregiver.png') center / cover no-repeat;
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
        padding-top: 150px;
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

    .medical-carousel-wrap {
        grid-template-columns: 44px minmax(0, 1fr) 44px;
        gap: 0.75rem;
    }

    .medical-carousel-control {
        width: 44px;
        height: 44px;
    }

    .service-tile {
        min-height: 230px;
    }

    .philosophy-panel {
        padding: 3rem;
        border-top-left-radius: 72px;
        border-bottom-right-radius: 72px;
    }

    .philosophy-list {
        grid-template-columns: 1fr;
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

    .philosophy-panel {
        padding: 2rem 1.25rem;
        border-top-left-radius: 42px;
        border-bottom-right-radius: 42px;
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
