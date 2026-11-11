<script setup lang="ts">
import { ref } from 'vue';
import { habitCards } from '../../data/landingData';
import { useSnapCarousel } from '../../composables/useSnapCarousel';
import { ChevronLeft, ChevronRight, Info } from 'lucide-vue-next';

const infoItems = [
    'Nilai gizi diverifikasi dari Tabel Komposisi Pangan Indonesia.',
    'Pantau kecukupan makronutrisi harian: karbohidrat, protein, lemak, dan serat.',
    'Panduan porsi "Isi Piringku" disesuaikan otomatis untuk anak dan orang dewasa.',
];
const currentInfoIndex = ref(0);

const nextInfo = () => {
    currentInfoIndex.value = (currentInfoIndex.value + 1) % infoItems.length;
};

const prevInfo = () => {
    currentInfoIndex.value =
        (currentInfoIndex.value - 1 + infoItems.length) % infoItems.length;
};

const track = ref<HTMLElement | null>(null);
const { active, engaged, go, next, prev, onKeydown } = useSnapCarousel(
    track,
    habitCards.length,
);

const getFeatureImage = (illustrationType: string) => {
    switch (illustrationType) {
        case 'scan-label':
            return '/assets/habit-pindai.webp';
        case 'cook-together':
            return '/assets/habit-masak.webp';
        default:
            return '/assets/habit-catat.webp';
    }
};

const arrowClass =
    'flex shrink-0 items-center justify-center rounded-full border border-line bg-white text-ink shadow-[0_4px_12px_rgba(30,35,33,0.06),0_1px_2px_rgba(30,35,33,0.06)] transition-[background-color,scale] duration-200 hover:bg-tile active:scale-95';
</script>

<template>
    <section
        id="kebiasaan"
        data-habits
        class="relative overflow-hidden rounded-b-[72px] bg-cream py-16 sm:py-24 lg:rounded-b-[160px]"
    >
        <div
            class="mx-auto flex max-w-[1440px] flex-col items-center gap-10 px-4 sm:gap-12 sm:px-6"
        >
            <h2
                data-habit-title
                class="max-w-[1200px] text-center font-display text-[34px] leading-[1.1] font-semibold tracking-[-0.02em] text-balance text-ink sm:text-5xl lg:text-[56px] lg:leading-[60px]"
            >
                Kebiasaan kecil yang mengisi piring keluarga
            </h2>

            <!-- Carousel: arrows and dots work at every width; see useSnapCarousel for the wide-screen behaviour. -->
            <div
                role="region"
                aria-roledescription="carousel"
                aria-label="Tiga kebiasaan kecil"
                class="flex w-full flex-col items-center gap-10 sm:gap-12"
                @keydown="onKeydown"
            >
                <div class="flex w-full items-center justify-center gap-6">
                    <div data-habit-ui class="hidden lg:block">
                        <button
                            type="button"
                            class="size-14"
                            :class="arrowClass"
                            aria-label="Kebiasaan sebelumnya"
                            @click="prev"
                        >
                            <ChevronLeft
                                class="size-[22px]"
                                aria-hidden="true"
                            />
                        </button>
                    </div>

                    <div
                        ref="track"
                        class="-mt-6 -mb-10 flex min-w-0 snap-x snap-mandatory [scrollbar-width:none] gap-6 overflow-x-auto overscroll-x-contain pt-6 pb-10 lg:max-w-[1224px] lg:flex-1 [&::-webkit-scrollbar]:hidden"
                    >
                        <div
                            v-for="(card, index) in habitCards"
                            :key="card.id"
                            data-habit-card
                            role="group"
                            aria-roledescription="slide"
                            :aria-label="`${index + 1} dari ${habitCards.length}`"
                            class="flex w-[min(392px,calc(100vw-4rem))] shrink-0 snap-center lg:w-auto lg:max-w-[392px] lg:min-w-[320px] lg:flex-[1_1_392px]"
                        >
                            <article
                                class="group relative flex min-h-[540px] w-full flex-col gap-3 overflow-hidden rounded-[40px] px-8 pt-10 text-white shadow-[0_1px_2px_rgba(30,35,33,0.06),0_8px_24px_rgba(30,35,33,0.08)] transition-[translate,scale,box-shadow] duration-300 ease-[cubic-bezier(0.23,1,0.32,1)] motion-reduce:transition-none"
                                :class="[
                                    card.bgColor,
                                    engaged && active === index
                                        ? '-translate-y-2 shadow-[0_28px_48px_-18px_rgba(30,35,33,0.45)]'
                                        : '',
                                    engaged && active !== index
                                        ? 'scale-[0.97]'
                                        : '',
                                ]"
                            >
                                <h3
                                    data-habit-copy
                                    class="font-display text-[32px] leading-[1.17] font-semibold tracking-[-0.01em] sm:text-4xl"
                                >
                                    {{ card.title }}
                                </h3>
                                <p
                                    data-habit-copy
                                    class="text-lg leading-7 font-medium"
                                >
                                    {{ card.description }}
                                </p>
                                <a
                                    data-habit-copy
                                    :href="card.actionHref"
                                    class="group/link inline-flex h-11 w-fit items-center gap-2.5 text-sm font-bold"
                                >
                                    <span class="group-hover/link:underline">{{
                                        card.actionText
                                    }}</span>
                                    <span
                                        class="flex size-7 items-center justify-center rounded-full bg-white text-ink transition-transform duration-200 group-hover/link:translate-x-0.5"
                                    >
                                        <ChevronRight
                                            class="size-4"
                                            aria-hidden="true"
                                        />
                                    </span>
                                </a>

                                <!-- Arch window: reaches the bottom edge of the card, as drawn. -->
                                <div
                                    class="mt-auto h-[280px] w-full shrink-0 overflow-hidden rounded-t-full"
                                >
                                    <div data-habit-art class="size-full">
                                        <img
                                            :src="
                                                getFeatureImage(
                                                    card.illustrationType,
                                                )
                                            "
                                            alt=""
                                            width="656"
                                            height="560"
                                            class="pointer-events-none size-full object-cover transition-transform duration-500 ease-[cubic-bezier(0.23,1,0.32,1)] select-none group-hover:scale-105 motion-reduce:transition-none"
                                            loading="lazy"
                                            draggable="false"
                                        />
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>

                    <div data-habit-ui class="hidden lg:block">
                        <button
                            type="button"
                            class="size-14"
                            :class="arrowClass"
                            aria-label="Kebiasaan berikutnya"
                            @click="next"
                        >
                            <ChevronRight
                                class="size-[22px]"
                                aria-hidden="true"
                            />
                        </button>
                    </div>
                </div>

                <!-- Dots are real buttons (44px tall hit area); below lg the arrows sit beside them. -->
                <div data-habit-ui class="-my-4 flex items-center gap-2">
                    <button
                        type="button"
                        class="size-11 lg:hidden"
                        :class="arrowClass"
                        aria-label="Kebiasaan sebelumnya"
                        @click="prev"
                    >
                        <ChevronLeft class="size-5" aria-hidden="true" />
                    </button>
                    <button
                        v-for="(card, index) in habitCards"
                        :key="card.id"
                        type="button"
                        class="group/dot flex h-11 items-center px-1"
                        :aria-label="`Lihat kebiasaan ${index + 1}: ${card.title}`"
                        :aria-current="active === index ? 'true' : undefined"
                        @click="go(index)"
                    >
                        <span
                            class="block h-2.5 rounded-full transition-[width,background-color] duration-300 ease-[cubic-bezier(0.23,1,0.32,1)] motion-reduce:transition-none"
                            :class="
                                active === index
                                    ? 'w-7 bg-primary'
                                    : 'w-2.5 bg-line-strong group-hover/dot:bg-muted'
                            "
                        ></span>
                    </button>
                    <button
                        type="button"
                        class="size-11 lg:hidden"
                        :class="arrowClass"
                        aria-label="Kebiasaan berikutnya"
                        @click="next"
                    >
                        <ChevronRight class="size-5" aria-hidden="true" />
                    </button>
                </div>
                <p class="sr-only" aria-live="polite" aria-atomic="true">
                    {{
                        engaged
                            ? `Kebiasaan ${active + 1} dari ${habitCards.length}: ${habitCards[active].title}`
                            : ''
                    }}
                </p>
            </div>

            <!-- Info bar: its own small ticker, independent of the cards. -->
            <div
                data-habit-ui
                class="flex w-full max-w-[1000px] items-center gap-4 rounded-[28px] border border-line bg-white p-2 sm:rounded-full"
            >
                <span
                    class="inline-flex shrink-0 items-center gap-2 rounded-full bg-terong py-2 pr-[18px] pl-3.5 text-sm font-bold text-white"
                >
                    <Info class="size-[18px]" aria-hidden="true" />
                    <span>Info</span>
                </span>
                <div
                    class="min-w-0 flex-1"
                    aria-live="polite"
                    aria-atomic="true"
                >
                    <Transition name="landing-info" mode="out-in">
                        <p
                            :key="currentInfoIndex"
                            class="text-sm leading-5 font-medium text-ink sm:text-base sm:leading-6"
                        >
                            {{ infoItems[currentInfoIndex] }}
                        </p>
                    </Transition>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <button
                        type="button"
                        class="size-11"
                        :class="arrowClass"
                        aria-label="Info sebelumnya"
                        @click="prevInfo"
                    >
                        <ChevronLeft class="size-[22px]" aria-hidden="true" />
                    </button>
                    <button
                        type="button"
                        class="size-11"
                        :class="arrowClass"
                        aria-label="Info berikutnya"
                        @click="nextInfo"
                    >
                        <ChevronRight class="size-[22px]" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>
