<script lang="ts">
export interface BmiPoint {
    tanggal: string;
    label: string;
    skor: number;
}
</script>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { bmiNumber } from '@/lib/calculatorUi';

const props = defineProps<{
    titik: BmiPoint[];
    normal: { min: number; max: number };
    ringkasan: string;
}>();

const H = 280;
const M = { top: 16, right: 20, bottom: 36, left: 48 };

const box = ref<HTMLElement | null>(null);
const W = ref(720);
let ro: ResizeObserver | undefined;

onMounted(() => {
    if (!box.value) return;
    W.value = Math.max(280, box.value.clientWidth);
    ro = new ResizeObserver(([e]) => {
        W.value = Math.max(280, Math.round(e.contentRect.width));
    });
    ro.observe(box.value);
});
onBeforeUnmount(() => ro?.disconnect());

const domain = computed(() => {
    const vals = props.titik.map((p) => p.skor);
    const lo = Math.floor(Math.min(...vals, props.normal.min) - 1);
    const hi = Math.ceil(Math.max(...vals, props.normal.max) + 1);
    return { lo, hi };
});

const innerW = computed(() => W.value - M.left - M.right);
const innerH = H - M.top - M.bottom;

const y = (v: number) =>
    M.top +
    innerH * (1 - (v - domain.value.lo) / (domain.value.hi - domain.value.lo));

const pts = computed(() => {
    const t = props.titik.map((p) => new Date(p.tanggal).getTime());
    const t0 = Math.min(...t);
    const span = Math.max(...t) - t0;
    return props.titik.map((p, i) => ({
        ...p,
        x:
            M.left +
            (span === 0 || props.titik.length === 1
                ? innerW.value / 2
                : ((t[i] - t0) / span) * innerW.value),
        y: y(p.skor),
    }));
});

const path = computed(() =>
    pts.value
        .map((p, i) => `${i ? 'L' : 'M'}${p.x.toFixed(1)} ${p.y.toFixed(1)}`)
        .join(' '),
);

const yTicks = computed(() => {
    const { lo, hi } = domain.value;
    const step = hi - lo > 12 ? 4 : 2;
    const out: number[] = [];
    for (let v = Math.ceil(lo / step) * step; v <= hi; v += step) out.push(v);
    return out;
});

/** Roughly one label per 80 px, so labels never collide on a phone. */
const xLabels = computed(() => {
    const n = pts.value.length;
    const max = Math.max(2, Math.floor(innerW.value / 80));
    if (n <= max) return pts.value;
    const picked = new Set<number>();
    for (let k = 0; k < max; k++)
        picked.add(Math.round((k * (n - 1)) / (max - 1)));
    return pts.value.filter((_, i) => picked.has(i));
});

const bandTop = computed(() => y(props.normal.max));
const bandH = computed(() => y(props.normal.min) - y(props.normal.max));
const last = computed(() => pts.value[pts.value.length - 1]);
</script>

<template>
    <div ref="box" class="w-full">
        <svg
            :width="W"
            :height="H"
            :viewBox="`0 0 ${W} ${H}`"
            role="img"
            aria-labelledby="bmi-chart-title bmi-chart-desc"
            class="block max-w-full"
        >
            <title id="bmi-chart-title">Grafik garis BMI</title>
            <desc id="bmi-chart-desc">{{ ringkasan }}</desc>

            <rect
                :x="M.left"
                :y="bandTop"
                :width="innerW"
                :height="bandH"
                class="fill-[#e4f6ea] dark:fill-[#17321f]"
            />
            <g aria-hidden="true">
                <g v-for="v in yTicks" :key="v">
                    <line
                        :x1="M.left"
                        :x2="M.left + innerW"
                        :y1="y(v)"
                        :y2="y(v)"
                        class="stroke-[#eceae5] dark:stroke-[#2e2e2e]"
                    />
                    <text
                        :x="M.left - 8"
                        :y="y(v) + 4"
                        text-anchor="end"
                        class="fill-[#5e5e5e] text-[11px] dark:fill-[#a3a3a3]"
                    >
                        {{ bmiNumber.format(v) }}
                    </text>
                </g>
                <text
                    v-for="p in xLabels"
                    :key="p.tanggal"
                    :x="p.x"
                    :y="H - 12"
                    text-anchor="middle"
                    class="fill-[#5e5e5e] text-[11px] dark:fill-[#a3a3a3]"
                >
                    {{ p.label }}
                </text>
                <path
                    v-if="pts.length > 1"
                    :d="path"
                    fill="none"
                    stroke-width="2.5"
                    stroke-linejoin="round"
                    class="stroke-[#0b8043] dark:stroke-[#3fcb7a]"
                />
                <circle
                    v-for="(p, i) in pts"
                    :key="p.tanggal"
                    :cx="p.x"
                    :cy="p.y"
                    r="5"
                    stroke-width="2.5"
                    class="stroke-[#0b8043] dark:stroke-[#3fcb7a]"
                    :class="
                        i === pts.length - 1
                            ? 'fill-[#0b8043] dark:fill-[#3fcb7a]'
                            : 'fill-white dark:fill-[#1c1c1c]'
                    "
                >
                    <title>{{ p.label }}: {{ bmiNumber.format(p.skor) }}</title>
                </circle>
                <text
                    v-if="last"
                    :x="Math.min(last.x, W - M.right - 4)"
                    :y="last.y - 12"
                    text-anchor="end"
                    class="fill-[#191919] text-[12px] font-bold dark:fill-[#f0efea]"
                >
                    {{ bmiNumber.format(last.skor) }}
                </text>
            </g>
        </svg>
    </div>
</template>
