<script lang="ts">
export interface Kategori {
    kode: string;
    label: string;
    tone: 'baik' | 'waspada' | 'bahaya' | 'info';
}
export interface BmiRow {
    id: number;
    tanggal: string;
    skor: number;
    kategori: Kategori | null;
    tinggi: number;
    berat: number;
}
export interface KaloriRow {
    id: number;
    tanggal: string;
    kkal: number;
    aktivitas_label: string;
    metode: string;
    berat: number;
}
</script>

<script setup lang="ts">
import {
    AlertTriangle,
    CheckCircle2,
    Info,
    OctagonAlert,
} from 'lucide-vue-next';

defineProps<{ kategori: Kategori }>();

const tones = {
    baik: {
        icon: CheckCircle2,
        cls: 'bg-[#e4f6ea] text-[#0b8043] dark:bg-[#17321f] dark:text-[#3fcb7a]',
    },
    waspada: {
        icon: AlertTriangle,
        cls: 'bg-[#fff4cc] text-[#7a4b00] dark:bg-amber-950/40 dark:text-amber-300',
    },
    bahaya: {
        icon: OctagonAlert,
        cls: 'bg-[#ffece5] text-[#a52f08] dark:bg-[#3a1e14] dark:text-red-300',
    },
    info: {
        icon: Info,
        cls: 'bg-teal-50 text-teal-800 dark:bg-teal-950/40 dark:text-teal-300',
    },
} as const;
</script>

<template>
    <span
        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold"
        :class="tones[kategori.tone].cls"
    >
        <component
            :is="tones[kategori.tone].icon"
            class="h-3.5 w-3.5"
            aria-hidden="true"
        />
        {{ kategori.label }}
    </span>
</template>
