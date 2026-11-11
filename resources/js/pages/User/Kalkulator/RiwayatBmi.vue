<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, ChartLine, Table } from 'lucide-vue-next';
import { bmiNumber } from '@/lib/calculatorUi';
import UserLayout from '@/Layouts/UserLayout.vue';
import BmiChart, {
    type BmiPoint,
} from '@/Components/Calculator/Hub/BmiChart.vue';
import BmiTable from '@/Components/Calculator/Hub/BmiTable.vue';
import type { BmiRow } from '@/Components/Calculator/Hub/CategoryPill.vue';

type Rentang = '7' | '30' | '90' | 'semua';

const props = defineProps<{
    rentang: Rentang;
    terakhir: { skor: number; tanggal: string } | null;
    titik: BmiPoint[];
    normal: { min: number; max: number };
    tabel: BmiRow[];
}>();

const options: { value: Rentang; label: string }[] = [
    { value: '7', label: '7 hari' },
    { value: '30', label: '30 hari' },
    { value: '90', label: '3 bulan' },
    { value: 'semua', label: 'Semua' },
];

const asTable = ref(false);

function pick(value: Rentang) {
    if (value === props.rentang) return;
    router.get(
        route('user.calculator.bmi.history'),
        { rentang: value },
        { preserveScroll: true, preserveState: true },
    );
}

const rentangLabel = computed(
    () => options.find((o) => o.value === props.rentang)?.label ?? '',
);

const ringkasan = computed(() => {
    if (!props.titik.length) return 'Belum ada data BMI pada rentang ini.';
    const first = props.titik[0];
    const last = props.titik[props.titik.length - 1];
    const lo = Math.min(...props.titik.map((p) => p.skor));
    const hi = Math.max(...props.titik.map((p) => p.skor));

    return `${props.titik.length} pengukuran dalam rentang ${rentangLabel.value}. Dari ${bmiNumber.format(first.skor)} pada ${first.label} menjadi ${bmiNumber.format(last.skor)} pada ${last.label}. Terendah ${bmiNumber.format(lo)}, tertinggi ${bmiNumber.format(hi)}. Rentang normal ${bmiNumber.format(props.normal.min)} sampai ${bmiNumber.format(props.normal.max)}.`;
});
</script>

<template>
    <Head title="Riwayat BMI" />
    <UserLayout>
        <div class="flex flex-col gap-6">
            <header
                class="flex flex-wrap items-center justify-between gap-3 rounded-3xl bg-[#e4f6ea] px-7 py-6 dark:bg-[#17321f]"
            >
                <h1
                    class="font-display text-3xl font-extrabold text-[#191919] dark:text-[#f0efea]"
                >
                    Riwayat BMI
                </h1>
                <Link
                    :href="route('user.calculator')"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0b8043] underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#191919] dark:text-[#3fcb7a] dark:focus-visible:outline-[#f0efea]"
                >
                    <ArrowLeft class="h-4 w-4" aria-hidden="true" /> Kembali ke
                    Kalkulator
                </Link>
            </header>

            <section
                aria-label="Grafik riwayat BMI"
                class="rounded-3xl bg-white p-6 shadow-[0_1px_2px_rgba(30,35,33,0.06),0_4px_12px_rgba(30,35,33,0.06)] dark:bg-[#1c1c1c]"
            >
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p
                            v-if="terakhir"
                            class="font-display text-4xl font-extrabold text-[#191919] dark:text-[#f0efea]"
                        >
                            {{ bmiNumber.format(terakhir.skor) }}
                        </p>
                        <p
                            v-else
                            class="font-display text-2xl font-extrabold text-[#5e5e5e] dark:text-[#a3a3a3]"
                        >
                            Belum dihitung
                        </p>
                        <p
                            v-if="terakhir"
                            class="mt-1 text-sm text-[#5e5e5e] dark:text-[#a3a3a3]"
                        >
                            BMI terakhir, {{ terakhir.tanggal }}
                        </p>
                    </div>

                    <div
                        role="radiogroup"
                        aria-label="Rentang waktu"
                        class="inline-flex overflow-hidden rounded-xl border-2 border-[#191919] dark:border-[#f0efea]"
                    >
                        <label
                            v-for="o in options"
                            :key="o.value"
                            class="cursor-pointer border-r-2 border-[#191919] px-3.5 py-2 text-sm font-semibold last:border-r-0 has-[:focus-visible]:outline-2 has-[:focus-visible]:-outline-offset-4 has-[:focus-visible]:outline-[#0b8043] dark:border-[#f0efea]"
                            :class="
                                rentang === o.value
                                    ? 'bg-[#191919] text-white dark:bg-[#f0efea] dark:text-[#191919]'
                                    : 'bg-white text-[#191919] hover:bg-[#f4f3f0] dark:bg-[#1c1c1c] dark:text-[#f0efea] dark:hover:bg-[#262626]'
                            "
                        >
                            <input
                                type="radio"
                                name="rentang"
                                class="sr-only"
                                :value="o.value"
                                :checked="rentang === o.value"
                                @change="pick(o.value)"
                            />
                            {{ o.label }}
                        </label>
                    </div>
                </div>

                <p class="sr-only">{{ ringkasan }}</p>

                <div class="mt-6">
                    <p
                        v-if="!titik.length"
                        class="rounded-2xl bg-[#f4f3f0] px-4 py-10 text-center text-base text-[#5e5e5e] dark:bg-[#262626] dark:text-[#a3a3a3]"
                    >
                        Belum ada data BMI pada rentang ini.
                    </p>
                    <BmiTable
                        v-else-if="asTable"
                        :rows="tabel"
                        :caption="`Riwayat BMI, ${rentangLabel}`"
                    />
                    <BmiChart
                        v-else
                        :titik="titik"
                        :normal="normal"
                        :ringkasan="ringkasan"
                    />
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2">
                    <p
                        class="flex items-center gap-2 text-sm text-[#5e5e5e] dark:text-[#a3a3a3]"
                    >
                        <span
                            class="h-3 w-5 rounded-sm border border-[#0b8043] bg-[#e4f6ea] dark:border-[#3fcb7a] dark:bg-[#17321f]"
                            aria-hidden="true"
                        />
                        Rentang Normal ({{
                            bmiNumber.format(normal.min)
                        }}
                        sampai {{ bmiNumber.format(normal.max) }})
                    </p>
                    <button
                        v-if="titik.length"
                        type="button"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#0b8043] underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#191919] dark:text-[#3fcb7a] dark:focus-visible:outline-[#f0efea]"
                        :aria-pressed="asTable"
                        @click="asTable = !asTable"
                    >
                        <ChartLine
                            v-if="asTable"
                            class="h-4 w-4"
                            aria-hidden="true"
                        />
                        <Table v-else class="h-4 w-4" aria-hidden="true" />
                        {{
                            asTable
                                ? 'Lihat sebagai grafik'
                                : 'Lihat sebagai tabel'
                        }}
                    </button>
                </div>
            </section>
        </div>
    </UserLayout>
</template>
