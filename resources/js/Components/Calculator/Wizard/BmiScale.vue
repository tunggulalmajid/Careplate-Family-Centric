<script setup lang="ts">
import { computed } from 'vue';
import { idNumber } from '@/lib/foodUi';

const props = defineProps<{
    skala: {
        kode: string;
        label: string;
        dari: number | null;
        sampai: number | null;
        tone: string;
    }[];
    posisi: number | null;
}>();

// Visual widths only; the marker position itself comes from the server.
const WEIGHTS = [12, 10, 39, 14, 25];
const tones: Record<string, string> = {
    baik: 'bg-[#e4f6ea] dark:bg-[#17321f]',
    waspada: 'bg-[#fff4cc] dark:bg-amber-950/40',
    bahaya: 'bg-[#ffece5] dark:bg-[#3a1e14]',
};

// Boundary labels: "< 17,0", "18,5", "25,0", "> 27,0".
const labels = computed(() => {
    const edges = props.skala.slice(0, -1);

    return edges.map((b, i) => {
        if (b.sampai === null) return '';
        const prefix = i === 0 ? '< ' : i === edges.length - 1 ? '> ' : '';

        return `${prefix}${idNumber.format(b.sampai)}`;
    });
});
const clamped = computed(() => Math.min(100, Math.max(0, props.posisi ?? 0)));
</script>

<template>
    <div class="w-full">
        <div
            class="flex gap-1.5"
            role="img"
            :aria-label="`Skala BMI: ${skala.map((b) => b.label).join(', ')}`"
        >
            <div
                v-for="(b, i) in skala"
                :key="b.kode"
                class="h-2.5 rounded-full"
                :class="tones[b.tone] ?? 'bg-[#f4f3f0] dark:bg-[#262626]'"
                :style="{ flex: `${WEIGHTS[i] ?? 1} 1 0%` }"
            />
        </div>
        <div v-if="posisi !== null" class="relative mt-2 h-7">
            <span
                class="absolute top-0 -translate-x-1/2 rounded-full bg-[#191919] px-3 py-1 text-xs font-semibold text-white dark:bg-[#f0efea] dark:text-[#121212]"
                :style="{ left: `${clamped}%` }"
                >Kamu</span
            >
        </div>
        <div
            class="mt-2 flex justify-between text-xs text-[#5e5e5e] dark:text-[#a3a3a3]"
            aria-hidden="true"
        >
            <span v-for="(l, i) in labels" :key="i">{{ l }}</span>
        </div>
    </div>
</template>
