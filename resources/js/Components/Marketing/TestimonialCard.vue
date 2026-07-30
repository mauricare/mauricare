<script setup>
defineProps({
    story: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <article class="testimonial-card" :class="`testimonial-card-${story.accent}`">
        <div class="testimonial-card-top">
            <div class="testimonial-stars" :aria-label="`${story.rating} out of 5 stars`">
                <span
                    v-for="star in 5"
                    :key="star"
                    :class="{ 'is-empty': star > story.rating }"
                    aria-hidden="true"
                >
                    &#9733;
                </span>
            </div>

            <span class="testimonial-verified">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                Verified review
            </span>
        </div>

        <div class="testimonial-quote" aria-hidden="true">&ldquo;</div>

        <blockquote>
            <p>{{ story.text }}</p>
        </blockquote>

        <footer class="testimonial-person">
            <span class="testimonial-avatar">{{ story.initials }}</span>
            <span class="testimonial-identity">
                <strong>{{ story.name }}</strong>
                <small>{{ story.role }}</small>
            </span>
        </footer>
    </article>
</template>

<style scoped>
.testimonial-card {
    --card-accent: #119bd3;
    --card-accent-dark: #0d7ca9;
    position: relative;
    min-height: 310px;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding: 1.75rem;
    border: 1px solid #e1e8f0;
    border-radius: 24px;
    background:
        radial-gradient(circle at 100% 0, color-mix(in srgb, var(--card-accent) 10%, transparent), transparent 34%),
        #fff;
    box-shadow: 0 18px 45px rgba(34, 53, 82, 0.09);
    transition:
        transform 0.25s ease,
        border-color 0.25s ease,
        box-shadow 0.25s ease;
}

.testimonial-card::before {
    position: absolute;
    inset: 0 auto 0 0;
    width: 5px;
    background: linear-gradient(180deg, var(--card-accent), #ec1696);
    content: "";
}

.testimonial-card:hover {
    transform: translateY(-5px);
    border-color: color-mix(in srgb, var(--card-accent) 32%, #e1e8f0);
    box-shadow: 0 24px 55px rgba(34, 53, 82, 0.14);
}

.testimonial-card-orange {
    --card-accent: #f59a00;
    --card-accent-dark: #b86600;
}

.testimonial-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.testimonial-stars {
    display: flex;
    gap: 0.22rem;
    color: #f5a000;
    font-size: 1rem;
    line-height: 1;
}

.testimonial-stars .is-empty {
    color: #dce2e9;
}

.testimonial-verified {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.35rem 0.6rem;
    color: #087a68;
    background: #e9f8f4;
    font-size: 0.69rem;
    font-weight: 800;
    white-space: nowrap;
}

.testimonial-quote {
    height: 2.8rem;
    margin-top: 1.15rem;
    color: color-mix(in srgb, var(--card-accent) 22%, transparent);
    font-family: Georgia, "Times New Roman", serif;
    font-size: 5rem;
    font-weight: 700;
    line-height: 0.9;
}

blockquote {
    margin: 0;
}

blockquote p {
    margin: 0;
    color: #344054;
    font-size: 1.04rem;
    font-weight: 500;
    line-height: 1.72;
}

.testimonial-person {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-top: auto;
    border-top: 1px solid #edf1f5;
    padding-top: 1.25rem;
}

.testimonial-avatar {
    width: 50px;
    height: 50px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 3px solid #fff;
    border-radius: 50%;
    color: #fff;
    background: linear-gradient(135deg, var(--card-accent), #ec1696);
    box-shadow: 0 7px 18px color-mix(in srgb, var(--card-accent) 24%, transparent);
    font-size: 0.8rem;
    font-weight: 900;
}

.testimonial-identity {
    min-width: 0;
}

.testimonial-identity strong,
.testimonial-identity small {
    display: block;
}

.testimonial-identity strong {
    overflow: hidden;
    color: #222b3a;
    font-size: 0.98rem;
    font-weight: 850;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.testimonial-identity small {
    margin-top: 0.2rem;
    color: #697586;
    font-size: 0.8rem;
}

@media (max-width: 575.98px) {
    .testimonial-card {
        min-height: 285px;
        padding: 1.4rem;
        border-radius: 20px;
    }

    .testimonial-card-top {
        align-items: flex-start;
        flex-direction: column;
        gap: 0.7rem;
    }
}
</style>
