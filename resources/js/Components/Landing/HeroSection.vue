<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import Navbar from './Navbar.vue';

interface Props {
    canLogin?: boolean;
    canRegister?: boolean;
    user?: any;
}

defineProps<Props>();
</script>

<template>
    <section class="relative overflow-hidden bg-[#bae7cf]">
        <!-- 1. Integrated Navbar inside the Hero's Sky Frame -->
        <Navbar
            :can-login="canLogin"
            :can-register="canRegister"
            :user="user"
        />

        <!-- 2. Decorative Sparkles & Dashed Paths matching Figma -->
        <!-- Green Sparkle Left -->
        <div
            data-hero-sparkle
            class="pointer-events-none absolute top-[190px] left-[10.4%] hidden text-[#0b8043] select-none lg:block"
        >
            <svg
                class="h-[18px] w-[18px]"
                viewBox="0 0 24 24"
                fill="currentColor"
            >
                <path
                    d="M12 0L14.5 9.5L24 12L14.5 14.5L12 24L9.5 14.5L0 12L9.5 9.5L12 0Z"
                />
            </svg>
        </div>

        <!-- White Sparkle Top-Right -->
        <div
            data-hero-sparkle
            class="pointer-events-none absolute top-[160px] right-[7.9%] hidden text-white drop-shadow-xs select-none lg:block"
        >
            <svg class="h-9 w-9" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M12 0L14.5 9.5L24 12L14.5 14.5L12 24L9.5 14.5L0 12L9.5 9.5L12 0Z"
                />
            </svg>
        </div>

        <!-- Yellow Sparkle Left-Center with Dashed Path -->
        <div
            data-hero-sparkle
            class="pointer-events-none absolute top-[310px] left-[20.8%] hidden select-none lg:block"
        >
            <svg
                class="mb-1 h-6 w-6 text-[#ffc933]"
                viewBox="0 0 24 24"
                fill="currentColor"
            >
                <path
                    d="M12 0L14.5 9.5L24 12L14.5 14.5L12 24L9.5 14.5L0 12L9.5 9.5L12 0Z"
                />
            </svg>
            <!-- Dashed trajectory leading down toward Left Sticker -->
            <svg
                class="h-16 w-28 text-[#0b8043]/30"
                viewBox="0 0 110 60"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-dasharray="4 4"
            >
                <path d="M90 0 C70 35, 30 45, 0 55" />
            </svg>
        </div>

        <!-- 3. Hero Copy (Figma 14.1 Hero Copy: 1100px column, 80px headline, family stack on the headline row) -->
        <div
            class="relative z-20 mx-auto max-w-[1440px] px-4 pt-8 pb-4 text-center sm:px-6 sm:pt-12 lg:px-8"
        >
            <div
                class="mx-auto flex max-w-[1100px] flex-col items-center gap-4 sm:gap-6"
            >
                <!-- Headline row 1: text, then the family stack on the same row (wraps below only when the screen is too narrow) -->
                <div
                    data-hero-line
                    class="flex flex-wrap items-center justify-center gap-x-5 gap-y-3"
                >
                    <span
                        class="font-display text-[clamp(2.25rem,5.6vw,5rem)] leading-[1.1] font-semibold tracking-[-0.02em] text-ink"
                    >
                        Makan sehat sekeluarga,
                    </span>

                    <div
                        class="flex items-center rounded-full bg-white p-1.5 sm:p-2"
                        aria-hidden="true"
                    >
                        <!-- Each avatar flips to a check in turn: the "who has logged" idea of Meja Keluarga. -->
                        <span
                            v-for="(initials, index) in [
                                'BS',
                                'RS',
                                'RK',
                                'DT',
                            ]"
                            :key="initials"
                            class="relative size-11 [perspective:600px] sm:size-16"
                            :class="index < 3 ? '-mr-3 sm:-mr-4' : ''"
                        >
                            <span
                                class="landing-flip absolute inset-0 [transform-style:preserve-3d]"
                                :style="{ '--flip-delay': `${index * 0.7}s` }"
                            >
                                <span
                                    class="absolute inset-0 flex items-center justify-center rounded-full border-[3px] border-white bg-primary-tint font-display text-base font-semibold tracking-[-0.01em] text-ink [backface-visibility:hidden] sm:text-2xl"
                                >
                                    {{ initials }}
                                </span>
                                <span
                                    class="absolute inset-0 flex [transform:rotateY(180deg)] items-center justify-center rounded-full border-[3px] border-white bg-primary text-white [backface-visibility:hidden]"
                                >
                                    <Check
                                        class="size-5 stroke-[3] sm:size-7"
                                    />
                                </span>
                            </span>
                        </span>
                    </div>
                </div>

                <!-- Headline row 2: mulai dari [ satu meja. ] -->
                <div
                    data-hero-line
                    class="flex flex-wrap items-center justify-center gap-4"
                >
                    <span
                        class="font-display text-[clamp(2.25rem,5.6vw,5rem)] leading-[1.1] font-semibold tracking-[-0.02em] text-ink"
                    >
                        mulai dari
                    </span>

                    <div
                        data-hero-pill
                        class="inline-flex items-center rounded-full border-[3px] border-ink px-6 pt-1 pb-2 sm:px-8"
                    >
                        <span
                            class="font-display text-[clamp(2.25rem,5.6vw,5rem)] leading-[1.1] font-semibold tracking-[-0.02em] text-primary-deep"
                        >
                            satu meja.
                        </span>
                    </div>

                    <!-- Orange Sparkle Right -->
                    <span
                        class="pointer-events-none hidden text-[#eb4416] select-none md:block"
                    >
                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                        >
                            <path
                                d="M12 0L14.5 9.5L24 12L14.5 14.5L12 24L9.5 14.5L0 12L9.5 9.5L12 0Z"
                            />
                        </svg>
                    </span>
                </div>

                <!-- Subtitle -->
                <p
                    data-hero-support
                    class="max-w-[640px] text-base leading-7 font-medium text-ink sm:text-lg"
                >
                    Untuk orang tua yang mengatur menu rumah. Catat makan,
                    pindai label, dan lihat siapa yang sudah mencatat minggu
                    ini.
                </p>

                <!-- Action Buttons -->
                <div
                    data-hero-support
                    class="flex flex-wrap items-center justify-center gap-3"
                >
                    <template v-if="user">
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex h-11 items-center justify-center rounded-full bg-primary px-5 text-sm font-bold text-white transition-colors hover:bg-primary-deep"
                        >
                            Buka Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('register')"
                            class="inline-flex h-11 items-center justify-center rounded-full bg-primary px-5 text-sm font-bold text-white transition-colors hover:bg-primary-deep"
                        >
                            Buat Akun Keluarga
                        </Link>
                        <a
                            href="#kebiasaan"
                            class="inline-flex h-11 items-center justify-center rounded-full border-[1.5px] border-ink bg-white px-5 text-sm font-bold text-ink transition-colors hover:bg-tile"
                        >
                            Lihat cara kerja
                        </a>
                    </template>
                </div>
            </div>
        </div>

        <!-- 4. Scenic Illustration Area with Seamless Sky Blend & Floating Stickers (mentok kiri dan kanan) -->
        <div class="relative mt-4 w-full sm:mt-6">
            <!-- Floating Stickers Container (positioned harmoniously over illustration) -->
            <div
                class="pointer-events-none absolute inset-x-0 top-0 z-20 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
            >
                <div class="relative h-24 sm:h-32">
                    <!-- Floating Sticker Left: Sarapan Tercatat -->
                    <div
                        data-hero-sticker
                        class="pointer-events-auto absolute top-2 left-0 hidden max-w-[260px] -rotate-1 transform items-center gap-3.5 rounded-2xl border border-white bg-white/95 px-4 py-3 shadow-xl backdrop-blur-sm transition-transform hover:rotate-0 md:flex lg:top-6"
                    >
                        <!-- Donut Progress 25% (1 dari 4) -->
                        <div class="relative h-12 w-12 shrink-0">
                            <svg
                                class="h-12 w-12 -rotate-90"
                                viewBox="0 0 36 36"
                            >
                                <!-- Background track -->
                                <path
                                    class="text-[#eceae5]"
                                    stroke="currentColor"
                                    stroke-width="4.5"
                                    fill="none"
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                />
                                <!-- 25% Green Stroke -->
                                <path
                                    class="text-[#0b8043]"
                                    stroke="currentColor"
                                    stroke-width="4.5"
                                    stroke-dasharray="25, 100"
                                    stroke-linecap="round"
                                    fill="none"
                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                />
                            </svg>
                        </div>
                        <div>
                            <p
                                class="font-display text-sm leading-tight font-bold text-[#191919]"
                            >
                                Sarapan tercatat
                            </p>
                            <p class="mt-0.5 text-xs text-[#5e5e5e]">
                                1 dari 4 waktu makan
                            </p>
                        </div>
                    </div>

                    <!-- Floating Sticker Right: 4 dari 4 sudah mencatat -->
                    <div
                        data-hero-sticker
                        class="pointer-events-auto absolute top-1 right-0 hidden min-w-[240px] rotate-1 transform flex-col gap-2 rounded-2xl border border-white bg-white/95 p-4 shadow-xl backdrop-blur-sm transition-transform hover:rotate-0 md:flex lg:top-4"
                    >
                        <!-- Avatars Top Row -->
                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-[#f4f3f0] text-[10px] font-bold text-[#191919]"
                                >BS</span
                            >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-[#f4f3f0] text-[10px] font-bold text-[#191919]"
                                >RS</span
                            >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-[#f4f3f0] text-[10px] font-bold text-[#191919]"
                                >RK</span
                            >
                            <span
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-[#f4f3f0] text-[10px] font-bold text-[#191919]"
                                >DT</span
                            >
                        </div>
                        <!-- Title -->
                        <p
                            class="font-display text-sm font-bold text-[#191919]"
                        >
                            4 dari 4 sudah mencatat
                        </p>
                        <!-- Badge Pill -->
                        <div
                            class="inline-flex w-fit items-center gap-1.5 rounded-full border border-[#0b8043]/20 bg-[#e6f6ec] px-2.5 py-1 text-xs font-semibold text-[#0b7a46]"
                        >
                            <Check class="h-3.5 w-3.5 stroke-[3]" />
                            <span>Minggu yang kompak</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full-width Scenic Image with Seamless Sky Gradient Blend (mentok kiri kanan) -->
            <div class="relative w-full overflow-hidden leading-none">
                <!-- Sky Blend Gradient Over the Top 140px of Illustration -->
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 z-10 h-32 bg-gradient-to-b from-[#bae7cf] via-[#bae7cf]/75 to-transparent sm:h-44"
                ></div>

                <img
                    src="/assets/Ilustrasi Meja Keluarga.png"
                    alt="Ilustrasi Makan Sehat Sekeluarga di Meja - CarePlate"
                    data-hero-art
                    class="pointer-events-none block h-auto w-full object-cover select-none"
                    loading="eager"
                />
            </div>
        </div>

        <!-- 5. Wave Vector Divider at the Base of Hero, Transitioning into Tiga Kebiasaan (#fdf8e6) -->
        <div
            class="relative z-20 -mt-8 w-full overflow-hidden leading-none sm:-mt-14"
        >
            <svg
                class="relative block h-12 w-full text-[#fdf8e6] sm:h-20"
                viewBox="0 0 1440 84"
                fill="none"
                preserveAspectRatio="none"
            >
                <path
                    d="M0,42 C320,84 640,0 960,42 C1200,74 1360,20 1440,42 L1440,84 L0,84 Z"
                    fill="currentColor"
                ></path>
            </svg>
        </div>
    </section>
</template>
