<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { idNumber } from '@/lib/foodUi';
import UserLayout from '@/Layouts/UserLayout.vue';
import SummaryCard from '@/Components/Calculator/Hub/SummaryCard.vue';
import HistoryTabs from '@/Components/Calculator/Hub/HistoryTabs.vue';
import type {
    BmiRow,
    Kategori,
    KaloriRow,
} from '@/Components/Calculator/Hub/CategoryPill.vue';

const props = defineProps<{
    terakhirBmi: {
        id: number;
        skor: number;
        kategori: Kategori | null;
        tanggal: string;
        tinggi: number;
        berat: number;
    } | null;
    terakhirKalori: {
        id: number;
        kkal: number;
        tanggal: string;
        aktivitas_label: string;
    } | null;
    riwayatBmi: BmiRow[];
    riwayatKalori: KaloriRow[];
}>();

const bmiNote = computed(() => {
    const b = props.terakhirBmi;
    if (!b) return 'Hitung BMI untuk melihat kategori berat badanmu.';

    return `Terakhir ${b.tanggal} • ${idNumber.format(b.tinggi)} cm, ${idNumber.format(b.berat)} kg`;
});
const kaloriNote = computed(() => {
    const k = props.terakhirKalori;
    if (!k) return 'Hitung kebutuhan kalori untuk menentukan target piringmu.';

    return `Terakhir ${k.tanggal} • ${k.aktivitas_label}`;
});
</script>

<template>
    <Head title="Kalkulator" />
    <UserLayout>
        <div class="flex flex-col gap-6">
            <header
                class="rounded-3xl bg-[#e4f6ea] px-7 py-6 dark:bg-[#17321f]"
            >
                <h1
                    class="font-display text-3xl font-extrabold text-[#191919] dark:text-[#f0efea]"
                >
                    Kalkulator
                </h1>
                <p class="mt-1 text-base text-[#5e5e5e] dark:text-[#a3a3a3]">
                    Hasil tersimpan otomatis dan menjadi target piringmu.
                </p>
            </header>

            <div class="grid gap-6 md:grid-cols-2">
                <SummaryCard
                    variant="bmi"
                    :nilai="terakhirBmi?.skor ?? null"
                    :kategori="terakhirBmi?.kategori"
                    :keterangan="bmiNote"
                    :href="route('user.calculator.bmi')"
                />
                <SummaryCard
                    variant="kalori"
                    :nilai="terakhirKalori?.kkal ?? null"
                    :keterangan="kaloriNote"
                    :href="route('user.calculator.kalori')"
                />
            </div>

            <HistoryTabs
                :riwayat-bmi="riwayatBmi"
                :riwayat-kalori="riwayatKalori"
            />
        </div>
    </UserLayout>
</template>
