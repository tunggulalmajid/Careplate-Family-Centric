<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useLandingMotion } from '../composables/useLandingMotion';
import type { PageProps } from '../types';

import HeroSection from '../Components/Landing/HeroSection.vue';
import HabitsSection from '../Components/Landing/HabitsSection.vue';
import FeaturesGridSection from '../Components/Landing/FeaturesGridSection.vue';
import FromMarketToTableSection from '../Components/Landing/FromMarketToTableSection.vue';
import FamilyTableSection from '../Components/Landing/FamilyTableSection.vue';
import ArticlesSection from '../Components/Landing/ArticlesSection.vue';
import CtaBannerSection from '../Components/Landing/CtaBannerSection.vue';
import FooterSection from '../Components/Landing/FooterSection.vue';

interface Props {
    canLogin?: boolean;
    canRegister?: boolean;
    laravelVersion?: string;
    phpVersion?: string;
}

defineProps<Props>();

const page = usePage<PageProps>();
const currentUser = computed(() => page.props.auth?.user);
const landingRoot = ref<HTMLElement | null>(null);
useLandingMotion(landingRoot);
</script>

<template>
    <Head title="CarePlate - Makan Sehat Sekeluarga, Mulai dari Satu Meja" />

    <div
        ref="landingRoot"
        class="careplate-landing min-h-screen bg-white font-sans text-[#191919] antialiased selection:bg-[#0b8043] selection:text-white"
    >
        <!-- 1. Hero Section (Integrated Floating Navbar, Headline, Avatars, Outlined Pill, Floating Stickers, Scenic Blend) -->
        <HeroSection
            :can-login="canLogin"
            :can-register="canRegister"
            :user="currentUser"
        />

        <!-- 2. Tiga Kebiasaan Kecil yang Mengisi Piring Keluarga (3 Colorful Cards & Info Ticker) -->
        <HabitsSection />

        <!-- 3. Semua Kebutuhan Gizi, Satu Aplikasi (8 Feature Tiles) -->
        <FeaturesGridSection :user="currentUser" />

        <!-- 4. Dari Pasar Sampai Meja Makan (3 Scene Cards) -->
        <FromMarketToTableSection />

        <!-- 5. Meja Keluarga (Weekly Table Matrix Mockup & Privacy Value Props) -->
        <FamilyTableSection :user="currentUser" />

        <!-- 6. Bacaan untuk Keluarga (Category Photo Circles) -->
        <ArticlesSection />

        <!-- 7. CTA Bukit: Siap Mengisi Piring Bareng Keluarga? -->
        <CtaBannerSection :user="currentUser" />

        <!-- 8. Deep Forest Green Footer -->
        <FooterSection />
    </div>
</template>

<style>
.careplate-landing {
    --landing-ease: cubic-bezier(0.23, 1, 0.32, 1);
}

.careplate-landing :is(a, button):focus-visible {
    outline: 3px solid #0b8043;
    outline-offset: 5px;
}

@media (prefers-reduced-motion: no-preference) {
    .careplate-landing :is(a, button) {
        transition-timing-function: var(--landing-ease);
    }

    .careplate-landing :is(a, button):active {
        scale: 0.97;
    }

    .careplate-landing #fitur a:hover svg:first-child {
        rotate: -8deg;
        transition: rotate 200ms var(--landing-ease);
    }

    /* Idle life: stickers hover, sparkles twinkle, family avatars flip to a check in turn. */
    @keyframes landing-float {
        0%,
        100% {
            translate: 0 0;
        }
        50% {
            translate: 0 -8px;
        }
    }

    @keyframes landing-twinkle {
        0%,
        100% {
            opacity: 0.55;
            scale: 0.85;
        }
        50% {
            opacity: 1;
            scale: 1.1;
        }
    }

    @keyframes landing-flip {
        0%,
        62% {
            transform: rotateY(0deg);
        }
        70%,
        88% {
            transform: rotateY(180deg);
        }
        96%,
        100% {
            transform: rotateY(360deg);
        }
    }

    /* The delay lets the entrance animation finish before the idle loop starts. */
    .careplate-landing [data-hero-sticker] {
        animation: landing-float 6s ease-in-out 2.2s infinite;
    }

    .careplate-landing [data-hero-sticker]:last-child {
        animation-duration: 7s;
        animation-delay: 4.2s;
    }

    .careplate-landing [data-hero-sparkle] > svg:first-child,
    .careplate-landing .landing-twinkle {
        animation: landing-twinkle 3.4s ease-in-out infinite;
    }

    .careplate-landing .landing-flip {
        animation: landing-flip 9s var(--landing-ease) var(--flip-delay, 0s)
            infinite;
    }

    .landing-menu-enter-active {
        transition:
            opacity 200ms var(--landing-ease),
            transform 200ms var(--landing-ease);
    }

    .landing-menu-leave-active {
        transition:
            opacity 150ms var(--landing-ease),
            transform 150ms var(--landing-ease);
    }

    .landing-menu-enter-from,
    .landing-menu-leave-to {
        opacity: 0;
        transform: translateY(-8px);
    }

    .landing-info-enter-active {
        transition:
            opacity 180ms var(--landing-ease),
            transform 180ms var(--landing-ease);
    }

    .landing-info-leave-active {
        transition: opacity 100ms var(--landing-ease);
    }

    .landing-info-enter-from {
        opacity: 0;
        transform: translateY(5px);
    }

    .landing-info-leave-to {
        opacity: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .careplate-landing *,
    .careplate-landing *::before,
    .careplate-landing *::after {
        animation: none !important;
        transition-duration: 0s !important;
    }

    .careplate-landing :is(a, button):active {
        scale: none;
    }
}
</style>
