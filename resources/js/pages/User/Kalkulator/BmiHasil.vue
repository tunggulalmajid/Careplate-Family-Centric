<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BmiScale from '@/Components/Calculator/Wizard/BmiScale.vue';
import CategoryPill from '@/Components/Calculator/Wizard/CategoryPill.vue';
import WizardShell from '@/Components/Calculator/Wizard/WizardShell.vue';
import { idNumber } from '@/lib/foodUi';
import { bmiNumber } from '@/lib/calculatorUi';

defineProps<{
    hasil: {
        id: number;
        skor: number;
        kategori: {
            kode: string;
            label: string;
            tone: 'baik' | 'waspada' | 'bahaya' | 'info';
        } | null;
        tinggi: number;
        berat: number;
        usia: number | null;
        anak: boolean;
        tanggal: string;
        skala: {
            kode: string;
            label: string;
            dari: number | null;
            sampai: number | null;
            tone: string;
        }[];
        posisi_persen: number | null;
    };
}>();

const outline =
    'inline-flex h-11 items-center rounded-full border-2 border-[#191919] px-6 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#191919] dark:border-[#f0efea] dark:focus-visible:outline-[#f0efea]';
const textLink =
    'text-sm font-semibold text-[#0b8043] underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#191919] dark:text-[#3fcb7a] dark:focus-visible:outline-[#f0efea]';
</script>

<template>
    <Head title="Hasil BMI" />
    <WizardShell
        :exit-href="route('user.calculator')"
        progress-label="Hitung BMI selesai"
    >
        <h1
            class="mt-8 text-xl font-semibold text-[#5e5e5e] dark:text-[#a3a3a3]"
        >
            BMI kamu
        </h1>
        <p class="relative mt-6 px-3 font-display text-7xl font-extrabold">
            <span
                class="absolute inset-x-0 bottom-1 -z-0 h-3 rounded-full bg-[#ffc933]"
                aria-hidden="true"
            />
            <span class="relative">{{ bmiNumber.format(hasil.skor) }}</span>
        </p>

        <div class="mt-8">
            <CategoryPill
                v-if="hasil.anak || !hasil.kategori"
                label="Dinilai menurut usia"
                tone="info"
            />
            <CategoryPill
                v-else
                :label="hasil.kategori.label"
                :tone="hasil.kategori.tone"
            />
        </div>

        <div v-if="!hasil.anak && hasil.skala.length" class="mt-6 w-full">
            <BmiScale :skala="hasil.skala" :posisi="hasil.posisi_persen" />
        </div>

        <p class="mt-6 text-lg">
            <template v-if="hasil.anak">
                Untuk usia di bawah 18 tahun, kategori BMI tidak ditampilkan
                karena penilaiannya bergantung pada usia. Tanyakan hasil ini
                lewat konsultasi gizi.
            </template>
            <template v-else
                >BMI kamu masuk kategori {{ hasil.kategori?.label }} menurut
                klasifikasi Kemenkes.</template
            >
            Tinggi {{ idNumber.format(hasil.tinggi) }} cm, berat
            {{ idNumber.format(hasil.berat) }} kg.
        </p>

        <div class="mt-6 flex flex-wrap items-center justify-center gap-5">
            <Link :href="route('user.calculator.bmi.history')" :class="outline"
                >Lihat Riwayat BMI</Link
            >
            <Link
                v-if="hasil.anak"
                :href="route('user.consultation')"
                :class="textLink"
                >Ajukan Konsultasi Gizi Anak</Link
            >
            <Link
                v-else
                :href="route('user.calculator.kalori')"
                :class="textLink"
                >Hitung Kebutuhan Kalori</Link
            >
        </div>

        <template #actions>
            <Link
                :href="route('user.dashboard')"
                class="inline-flex h-11 items-center rounded-full bg-[#0b8043] px-6 text-sm font-semibold text-white hover:bg-[#086637] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#191919] dark:focus-visible:outline-[#f0efea]"
                >Kembali ke Beranda</Link
            >
        </template>
    </WizardShell>
</template>
