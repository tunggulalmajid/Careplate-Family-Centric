<script setup lang="ts">
import { idNumber } from '@/lib/foodUi';
import { bmiNumber } from '@/lib/calculatorUi';
import CategoryPill, { type BmiRow } from './CategoryPill.vue';

defineProps<{ rows: BmiRow[]; caption: string }>();

const th =
    'whitespace-nowrap px-4 py-3 text-left text-sm font-semibold text-[#5e5e5e] first:pl-6 last:pr-6 dark:text-[#a3a3a3]';
const td =
    'whitespace-nowrap px-4 py-4 text-sm text-[#191919] first:pl-6 last:pr-6 dark:text-[#f0efea]';
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[34rem] border-collapse">
            <caption class="sr-only">
                {{
                    caption
                }}
            </caption>
            <thead class="bg-[#f4f3f0] dark:bg-[#262626]">
                <tr>
                    <th scope="col" :class="th">Tanggal</th>
                    <th scope="col" :class="th">BMI</th>
                    <th scope="col" :class="th">Kategori</th>
                    <th scope="col" :class="th">Tinggi</th>
                    <th scope="col" :class="th">Berat</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="r in rows"
                    :key="r.id"
                    class="border-b border-[#eceae5] last:border-b-0 dark:border-[#2e2e2e]"
                >
                    <td :class="td">{{ r.tanggal }}</td>
                    <td :class="[td, 'font-bold']">
                        {{ bmiNumber.format(r.skor) }}
                    </td>
                    <td :class="td">
                        <CategoryPill
                            v-if="r.kategori"
                            :kategori="r.kategori"
                        />
                        <span v-else class="text-[#5e5e5e] dark:text-[#a3a3a3]"
                            >Anak, lihat kurva pertumbuhan</span
                        >
                    </td>
                    <td :class="td">{{ idNumber.format(r.tinggi) }} cm</td>
                    <td :class="td">{{ idNumber.format(r.berat) }} kg</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
