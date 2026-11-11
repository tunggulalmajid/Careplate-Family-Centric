<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, Target } from 'lucide-vue-next';
import WizardShell from '@/Components/Calculator/Wizard/WizardShell.vue';
import { idNumber } from '@/lib/foodUi';

interface Range {
    min: number;
    max: number;
}

const props = defineProps<{
    hasil: {
        id: number;
        kkal: number;
        aktivitas_label: string;
        tanggal: string;
        makro: { protein: Range; karbohidrat: Range; lemak: Range };
    };
}>();

const macros = [
    ['Protein', props.hasil.makro.protein],
    ['Karbohidrat', props.hasil.makro.karbohidrat],
    ['Lemak', props.hasil.makro.lemak],
] as const;
</script>

<template>
    <Head title="Hasil Kebutuhan Kalori" />
    <WizardShell
        :exit-href="route('user.calculator')"
        progress-label="Hitung kalori selesai"
    >
        <span
            class="flex h-20 w-20 items-center justify-center rounded-full bg-[#e4f6ea] dark:bg-[#17321f]"
            ><Target
                class="h-9 w-9 text-[#0b8043] dark:text-[#3fcb7a]"
                aria-hidden="true"
        /></span>
        <h1
            class="mt-6 text-xl font-semibold text-[#5e5e5e] dark:text-[#a3a3a3]"
        >
            Kebutuhan kalorimu sekitar
        </h1>
        <p class="relative mt-6 px-3 font-display text-6xl font-extrabold">
            <span
                class="absolute inset-x-0 bottom-1 h-3 rounded-full bg-[#ffc933]"
                aria-hidden="true"
            />
            <span class="relative">{{ idNumber.format(hasil.kkal) }} kkal</span>
        </p>
        <p class="mt-6 text-lg text-[#5e5e5e] dark:text-[#a3a3a3]">
            per hari, untuk aktivitas {{ hasil.aktivitas_label.toLowerCase() }}
        </p>

        <dl
            class="mt-8 grid w-full grid-cols-1 gap-4 rounded-2xl border border-[#eceae5] p-6 text-left sm:grid-cols-3 dark:border-[#2e2e2e]"
        >
            <div v-for="m in macros" :key="m[0]">
                <dt class="text-xs text-[#5e5e5e] dark:text-[#a3a3a3]">
                    {{ m[0] }}
                </dt>
                <dd class="mt-1 text-base font-semibold">
                    {{ idNumber.format(m[1].min) }} sampai
                    {{ idNumber.format(m[1].max) }} g
                </dd>
            </div>
        </dl>

        <p
            class="mt-6 flex w-full items-start gap-3 rounded-xl bg-[#e4f6ea] px-4 py-3 text-left text-sm text-[#0b7a46] dark:bg-[#17321f] dark:text-[#3fcb7a]"
            role="status"
        >
            <CheckCircle2 class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />
            Tersimpan ke riwayat dan menjadi target Piring hari ini.
        </p>

        <template #actions>
            <Link
                :href="route('user.dashboard')"
                class="inline-flex h-11 items-center rounded-full bg-[#0b8043] px-6 text-sm font-semibold text-white hover:bg-[#086637] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#191919] dark:focus-visible:outline-[#f0efea]"
                >Kembali ke Beranda</Link
            >
        </template>
    </WizardShell>
</template>
