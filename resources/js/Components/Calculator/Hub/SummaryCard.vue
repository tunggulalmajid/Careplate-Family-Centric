<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Scale, Target } from 'lucide-vue-next';
import { idNumber } from '@/lib/foodUi';
import { bmiNumber } from '@/lib/calculatorUi';
import CategoryPill, { type Kategori } from './CategoryPill.vue';

const props = defineProps<{
    variant: 'bmi' | 'kalori';
    /** BMI score or kcal; null when never calculated. */
    nilai: number | null;
    kategori?: Kategori | null;
    keterangan?: string;
    href: string;
}>();

const isBmi = computed(() => props.variant === 'bmi');
const title = computed(() => (isBmi.value ? 'BMI' : 'Kebutuhan Kalori'));
const cta = computed(() =>
    isBmi.value ? 'Hitung BMI' : 'Hitung Kebutuhan Kalori',
);
const id = computed(() => `ringkasan-${props.variant}`);
</script>

<template>
    <section
        :aria-labelledby="id"
        class="flex flex-col gap-4 rounded-3xl bg-white p-6 shadow-[0_1px_2px_rgba(30,35,33,0.06),0_4px_12px_rgba(30,35,33,0.06)] dark:bg-[#1c1c1c]"
    >
        <div class="flex items-center gap-3">
            <span
                class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#e4f6ea] text-[#0b8043] dark:bg-[#17321f] dark:text-[#3fcb7a]"
            >
                <Scale v-if="isBmi" class="h-6 w-6" aria-hidden="true" />
                <Target v-else class="h-6 w-6" aria-hidden="true" />
            </span>
            <h2
                :id="id"
                class="font-display text-2xl font-extrabold text-[#191919] dark:text-[#f0efea]"
            >
                {{ title }}
            </h2>
        </div>

        <div class="flex min-h-14 flex-wrap items-center gap-x-4 gap-y-2">
            <template v-if="nilai !== null">
                <p
                    class="font-display text-5xl font-extrabold tracking-tight text-[#191919] dark:text-[#f0efea]"
                >
                    {{
                        isBmi ? bmiNumber.format(nilai) : idNumber.format(nilai)
                    }}
                </p>
                <CategoryPill v-if="isBmi && kategori" :kategori="kategori" />
                <p
                    v-else-if="!isBmi"
                    class="text-base text-[#5e5e5e] dark:text-[#a3a3a3]"
                >
                    kkal per hari
                </p>
            </template>
            <p
                v-else
                class="font-display text-3xl font-extrabold text-[#5e5e5e] dark:text-[#a3a3a3]"
            >
                Belum dihitung
            </p>
        </div>

        <p
            v-if="keterangan"
            class="text-base text-[#5e5e5e] dark:text-[#a3a3a3]"
        >
            {{ keterangan }}
        </p>

        <div>
            <Link
                :href="href"
                class="inline-flex h-11 items-center rounded-full bg-[#0b8043] px-5 text-sm font-semibold text-white hover:bg-[#096b38] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#191919] dark:bg-[#3fcb7a] dark:text-[#0f1f15] dark:hover:bg-[#5ad88f] dark:focus-visible:outline-[#f0efea]"
            >
                {{ cta }}
            </Link>
        </div>
    </section>
</template>
