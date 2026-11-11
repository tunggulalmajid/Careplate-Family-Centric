<script setup lang="ts">
import { nextTick, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { idNumber } from '@/lib/foodUi';
import BmiTable from './BmiTable.vue';
import type { BmiRow, KaloriRow } from './CategoryPill.vue';

defineProps<{ riwayatBmi: BmiRow[]; riwayatKalori: KaloriRow[] }>();

const tabs = [
    { key: 'bmi', label: 'BMI' },
    { key: 'kalori', label: 'Kebutuhan Kalori' },
] as const;
type TabKey = (typeof tabs)[number]['key'];

const active = ref<TabKey>('bmi');
const tabEls = ref<HTMLButtonElement[]>([]);

async function move(to: number) {
    const i = (to + tabs.length) % tabs.length;
    active.value = tabs[i].key;
    await nextTick();
    tabEls.value[i]?.focus();
}

function onKey(e: KeyboardEvent, i: number) {
    if (e.key === 'ArrowRight') move(i + 1);
    else if (e.key === 'ArrowLeft') move(i - 1);
    else if (e.key === 'Home') move(0);
    else if (e.key === 'End') move(tabs.length - 1);
    else return;
    e.preventDefault();
}

const th =
    'whitespace-nowrap px-4 py-3 text-left text-sm font-semibold text-[#5e5e5e] first:pl-6 last:pr-6 dark:text-[#a3a3a3]';
const td =
    'whitespace-nowrap px-4 py-4 text-sm text-[#191919] first:pl-6 last:pr-6 dark:text-[#f0efea]';
</script>

<template>
    <section
        aria-labelledby="riwayat-terbaru"
        class="overflow-hidden rounded-3xl bg-white shadow-[0_1px_2px_rgba(30,35,33,0.06),0_4px_12px_rgba(30,35,33,0.06)] dark:bg-[#1c1c1c]"
    >
        <div
            class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2 px-6 pt-6"
        >
            <h2
                id="riwayat-terbaru"
                class="font-display text-2xl font-extrabold text-[#191919] dark:text-[#f0efea]"
            >
                Riwayat terbaru
            </h2>
            <div role="tablist" aria-label="Jenis riwayat" class="flex gap-6">
                <button
                    v-for="(t, i) in tabs"
                    :id="`tab-${t.key}`"
                    :key="t.key"
                    :ref="(el) => (tabEls[i] = el as HTMLButtonElement)"
                    type="button"
                    role="tab"
                    :aria-selected="active === t.key"
                    :aria-controls="`panel-${t.key}`"
                    :tabindex="active === t.key ? 0 : -1"
                    class="border-b-2 py-3 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#191919] dark:focus-visible:outline-[#f0efea]"
                    :class="
                        active === t.key
                            ? 'border-[#0b8043] text-[#191919] dark:border-[#3fcb7a] dark:text-[#f0efea]'
                            : 'border-transparent text-[#5e5e5e] hover:text-[#191919] dark:text-[#a3a3a3] dark:hover:text-[#f0efea]'
                    "
                    @click="active = t.key"
                    @keydown="onKey($event, i)"
                >
                    {{ t.label }}
                </button>
            </div>
        </div>

        <div
            id="panel-bmi"
            role="tabpanel"
            aria-labelledby="tab-bmi"
            :hidden="active !== 'bmi'"
            class="mt-2"
        >
            <BmiTable
                v-if="riwayatBmi.length"
                :rows="riwayatBmi"
                caption="Riwayat BMI terbaru"
            />
            <p
                v-else
                class="px-6 py-8 text-base text-[#5e5e5e] dark:text-[#a3a3a3]"
            >
                Belum ada riwayat.
            </p>
            <div
                class="border-t border-[#eceae5] px-6 py-4 dark:border-[#2e2e2e]"
            >
                <Link
                    :href="route('user.calculator.bmi.history')"
                    class="text-sm font-semibold text-[#0b8043] underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-[#191919] dark:text-[#3fcb7a] dark:focus-visible:outline-[#f0efea]"
                    >Lihat semua riwayat BMI</Link
                >
            </div>
        </div>

        <div
            id="panel-kalori"
            role="tabpanel"
            aria-labelledby="tab-kalori"
            :hidden="active !== 'kalori'"
            class="mt-2"
        >
            <div v-if="riwayatKalori.length" class="overflow-x-auto">
                <table class="w-full min-w-[34rem] border-collapse">
                    <caption class="sr-only">
                        Riwayat kebutuhan kalori terbaru
                    </caption>
                    <thead class="bg-[#f4f3f0] dark:bg-[#262626]">
                        <tr>
                            <th scope="col" :class="th">Tanggal</th>
                            <th scope="col" :class="th">Kebutuhan</th>
                            <th scope="col" :class="th">Aktivitas</th>
                            <th scope="col" :class="th">Metode</th>
                            <th scope="col" :class="th">Berat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="r in riwayatKalori"
                            :key="r.id"
                            class="border-b border-[#eceae5] last:border-b-0 dark:border-[#2e2e2e]"
                        >
                            <td :class="td">{{ r.tanggal }}</td>
                            <td :class="[td, 'font-bold']">
                                {{ idNumber.format(r.kkal) }} kkal
                            </td>
                            <td :class="td">{{ r.aktivitas_label }}</td>
                            <td :class="td">{{ r.metode }}</td>
                            <td :class="td">
                                {{ idNumber.format(r.berat) }} kg
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p
                v-else
                class="px-6 py-8 text-base text-[#5e5e5e] dark:text-[#a3a3a3]"
            >
                Belum ada riwayat.
            </p>
        </div>
    </section>
</template>
