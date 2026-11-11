<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import {
    Users,
    Copy,
    Check,
    Share2,
    Settings,
    LogOut,
    CheckCircle2,
    Circle,
    Flame,
    Utensils,
    Calendar,
    ChevronDown,
    ChevronUp,
    AlertCircle,
    Info,
} from 'lucide-vue-next';

interface WeeklyDot {
    label: string;
    date: string;
    logged: boolean;
    is_today: boolean;
    is_future: boolean;
}

interface TodayMealItem {
    id: number;
    nama_makanan: string;
    kategori_makanan: string;
    waktu_konsumsi: string;
    jumlah_porsi: number;
    total_kalori: number;
}

interface MemberDetail {
    id: number;
    user_id: number;
    name: string;
    email: string;
    initials: string;
    is_kepala: boolean;
    role_label: string;
    usia: number | null;
    total_hari_minggu_ini: number;
    target_tercapai: boolean;
    weekly_dots: WeeklyDot[];
    total_kalori_hari_ini: number;
    target_kalori_harian: number;
    catatan_hari_ini: TodayMealItem[];
}

const props = defineProps<{
    keluarga: {
        id: number;
        nama_keluarga: string;
        kode_keluarga: string;
        kepala_keluarga_nama: string;
        total_anggota: number;
        link_undangan: string;
    };
    isKepalaKeluarga: boolean;
    currentUserId: number;
    periodeMinggu: string;
    targetTercapaiCount: number;
    totalAnggota: number;
    anggotaList: MemberDetail[];
}>();

const copiedCode = ref(false);
const copiedLink = ref(false);
const expandedMemberId = ref<number | null>(null);

const copyCode = async () => {
    try {
        await navigator.clipboard.writeText(props.keluarga.kode_keluarga);
        copiedCode.value = true;
        setTimeout(() => (copiedCode.value = false), 2000);
    } catch {
        // Fallback
    }
};

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(props.keluarga.link_undangan);
        copiedLink.value = true;
        setTimeout(() => (copiedLink.value = false), 2000);
    } catch {
        // Fallback
    }
};

const toggleMemberAccordion = (id: number) => {
    expandedMemberId.value = expandedMemberId.value === id ? null : id;
};

const confirmLeaveGroup = () => {
    if (confirm('Apakah kamu yakin ingin keluar dari grup keluarga ini?')) {
        router.post(route('user.family.leave'));
    }
};

const formatWaktu = (waktu: string) => {
    const map: Record<string, string> = {
        sarapan: 'Sarapan',
        makan_siang: 'Makan Siang',
        makan_malam: 'Makan Malam',
        camilan: 'Camilan',
    };
    return map[waktu] || waktu;
};
</script>

<template>
    <UserLayout :title="keluarga.nama_keluarga">
        <Head :title="`${keluarga.nama_keluarga} - Meja Keluarga`" />

        <div class="space-y-8 pb-16">
            <!-- 1. Header Meja Keluarga (Figma 19:4004) -->
            <div
                class="relative overflow-hidden rounded-3xl border border-orange-100/70 bg-[#fff2eb] p-6 shadow-xs transition-colors sm:p-8 md:p-10 dark:border-[#3d2c26] dark:bg-[#261f1c]"
            >
                <div
                    class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center"
                >
                    <div class="space-y-2">
                        <div
                            class="inline-flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-[#333] dark:text-gray-300"
                        >
                            <span
                                class="h-2 w-2 rounded-full bg-emerald-500"
                            ></span>
                            <span>{{
                                isKepalaKeluarga
                                    ? 'Kamu Kepala Keluarga'
                                    : `Kepala: ${keluarga.kepala_keluarga_nama}`
                            }}</span>
                            <span>•</span>
                            <span>{{ totalAnggota }} Anggota</span>
                        </div>

                        <h1
                            class="font-display text-3xl font-extrabold tracking-tight text-[#191919] sm:text-4xl md:text-5xl dark:text-[#f0efea]"
                        >
                            {{ keluarga.nama_keluarga }}
                        </h1>
                        <p
                            class="text-xs text-gray-600 sm:text-sm dark:text-[#a3a3a3]"
                        >
                            Saling memantau kebiasaan sehat dan aktivitas gizi
                            keluarga setiap hari.
                        </p>
                    </div>

                    <!-- Right Controls: Kode, Link, Kelola -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Kode Grup Badge -->
                        <div
                            class="flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-xs shadow-2xs dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
                        >
                            <span class="text-gray-400 dark:text-[#737373]"
                                >Kode:</span
                            >
                            <span
                                class="font-mono text-sm font-bold tracking-wider text-gray-900 dark:text-[#f0efea]"
                            >
                                {{ keluarga.kode_keluarga }}
                            </span>
                            <button
                                type="button"
                                @click="copyCode"
                                class="cursor-pointer rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-[#262626] dark:hover:text-[#f0efea]"
                                title="Salin kode keluarga"
                            >
                                <Check
                                    v-if="copiedCode"
                                    class="h-4 w-4 text-emerald-500"
                                />
                                <Copy v-else class="h-4 w-4" />
                            </button>
                        </div>

                        <!-- Button Salin Link Undangan -->
                        <button
                            type="button"
                            @click="copyLink"
                            class="flex cursor-pointer items-center gap-1.5 rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-700 shadow-2xs transition hover:bg-gray-50 dark:border-[#2e2e2e] dark:bg-[#1c1c1c] dark:text-[#f0efea] dark:hover:bg-[#262626]"
                        >
                            <Check
                                v-if="copiedLink"
                                class="h-4 w-4 text-emerald-500"
                            />
                            <Share2
                                v-else
                                class="h-4 w-4 text-[#0b8043] dark:text-[#3fcb7a]"
                            />
                            <span>{{
                                copiedLink
                                    ? 'Tautan Disalin!'
                                    : 'Salin Link Undangan'
                            }}</span>
                        </button>

                        <!-- Tombol Kelola Grup (Khusus Kepala Keluarga) -->
                        <Link
                            v-if="isKepalaKeluarga"
                            :href="route('user.family.manage')"
                            class="flex cursor-pointer items-center gap-1.5 rounded-2xl bg-[#0b8043] px-4 py-2.5 text-xs font-bold text-white shadow-xs transition hover:bg-[#096836] dark:bg-[#3fcb7a] dark:text-[#121212] dark:hover:bg-[#34aa66]"
                        >
                            <Settings class="h-4 w-4" />
                            <span>Kelola Grup</span>
                        </Link>

                        <!-- Tombol Keluar dari Grup (Jika Anggota Biasa) -->
                        <button
                            v-else
                            type="button"
                            @click="confirmLeaveGroup"
                            class="flex cursor-pointer items-center gap-1.5 rounded-2xl border border-red-200 bg-white px-4 py-2.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-[#1c1c1c] dark:text-red-400 dark:hover:bg-red-950/30"
                        >
                            <LogOut class="h-4 w-4" />
                            <span>Keluar Grup</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
                <!-- 2. Left Column: Tabel Meja Keluarga Mingguan (Figma 19:4009) -->
                <div class="space-y-6 lg:col-span-7">
                    <div
                        class="space-y-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-xs sm:p-8 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
                    >
                        <!-- Card Header & Progress -->
                        <div
                            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                        >
                            <div>
                                <h2
                                    class="text-lg font-bold text-gray-900 sm:text-xl dark:text-[#f0efea]"
                                >
                                    Meja Keluarga • {{ periodeMinggu }}
                                </h2>
                                <p
                                    class="mt-0.5 text-xs text-gray-500 sm:text-sm dark:text-[#a3a3a3]"
                                >
                                    Target bersama: masing-masing mencatat 5
                                    hari dalam seminggu.
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <span
                                    class="text-2xl font-black text-[#0b8043] dark:text-[#3fcb7a]"
                                >
                                    {{ targetTercapaiCount }} dari
                                    {{ totalAnggota }}
                                </span>
                                <span
                                    class="block text-[11px] text-gray-400 dark:text-[#737373]"
                                >
                                    mencapai 5 hari
                                </span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div
                            class="h-3 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-[#262626]"
                        >
                            <div
                                class="h-full rounded-full bg-[#0b8043] transition-all duration-500 dark:bg-[#3fcb7a]"
                                :style="{
                                    width: `${totalAnggota > 0 ? (targetTercapaiCount / totalAnggota) * 100 : 0}%`,
                                }"
                            ></div>
                        </div>

                        <!-- Weekly Grid Table -->
                        <div class="no-scrollbar overflow-x-auto pt-2">
                            <table
                                class="w-full min-w-[540px] border-collapse text-left"
                            >
                                <thead>
                                    <tr
                                        class="border-b border-gray-100 text-xs font-semibold text-gray-400 dark:border-[#262626] dark:text-[#737373]"
                                    >
                                        <th class="px-2 py-3">Anggota</th>
                                        <th class="w-10 px-2 py-3 text-center">
                                            Sn
                                        </th>
                                        <th class="w-10 px-2 py-3 text-center">
                                            Sl
                                        </th>
                                        <th class="w-10 px-2 py-3 text-center">
                                            Rb
                                        </th>
                                        <th class="w-10 px-2 py-3 text-center">
                                            Km
                                        </th>
                                        <th class="w-10 px-2 py-3 text-center">
                                            Jm
                                        </th>
                                        <th class="w-10 px-2 py-3 text-center">
                                            Sb
                                        </th>
                                        <th class="w-10 px-2 py-3 text-center">
                                            Mg
                                        </th>
                                        <th class="px-2 py-3 text-right">
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-50 dark:divide-[#262626]"
                                >
                                    <tr
                                        v-for="member in anggotaList"
                                        :key="member.id"
                                        class="transition-colors hover:bg-gray-50/50 dark:hover:bg-[#222]"
                                    >
                                        <!-- Member Profile Info -->
                                        <td class="px-2 py-3.5">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-emerald-200/50 bg-emerald-100 text-xs font-bold text-[#0b8043] dark:border-[#2e2e2e] dark:bg-[#17321f] dark:text-[#3fcb7a]"
                                                >
                                                    {{ member.initials }}
                                                </div>
                                                <div class="min-w-0">
                                                    <div
                                                        class="truncate text-sm font-bold text-gray-900 dark:text-[#f0efea]"
                                                    >
                                                        {{ member.name }}
                                                    </div>
                                                    <div
                                                        class="text-[11px] text-gray-400 dark:text-[#737373]"
                                                    >
                                                        {{ member.role_label }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- 7 Days Dots (Sn - Mg) -->
                                        <td
                                            v-for="dot in member.weekly_dots"
                                            :key="dot.date"
                                            class="px-2 py-3.5 text-center"
                                        >
                                            <div
                                                class="flex items-center justify-center"
                                            >
                                                <!-- Logged dot -->
                                                <div
                                                    v-if="dot.logged"
                                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-[#0b8043] text-white shadow-2xs dark:bg-[#3fcb7a] dark:text-[#121212]"
                                                    title="Mencatat makanan"
                                                >
                                                    <Check
                                                        class="h-3.5 w-3.5 stroke-[3]"
                                                    />
                                                </div>

                                                <!-- Unlogged dot for today -->
                                                <div
                                                    v-else-if="dot.is_today"
                                                    class="h-6 w-6 rounded-full border-2 border-dashed border-[#0b8043] bg-emerald-50/40 dark:border-[#3fcb7a] dark:bg-[#17321f]/20"
                                                    title="Belum mencatat hari ini"
                                                ></div>

                                                <!-- Future / past empty dot -->
                                                <div
                                                    v-else
                                                    class="h-5 w-5 rounded-full border border-gray-200/60 bg-gray-100 dark:border-[#333] dark:bg-[#262626]"
                                                ></div>
                                            </div>
                                        </td>

                                        <!-- Total Days Badge -->
                                        <td class="px-2 py-3.5 text-right">
                                            <span
                                                class="inline-block rounded-full px-2.5 py-1 text-xs font-bold"
                                                :class="
                                                    member.target_tercapai
                                                        ? 'bg-emerald-100 text-[#0b8043] dark:bg-[#17321f] dark:text-[#3fcb7a]'
                                                        : 'bg-gray-100 text-gray-600 dark:bg-[#262626] dark:text-[#a3a3a3]'
                                                "
                                            >
                                                {{
                                                    member.total_hari_minggu_ini
                                                }}/5 hari
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Reset notice note -->
                        <div
                            class="flex items-center gap-2.5 rounded-2xl border border-gray-100 bg-gray-50 p-3.5 text-xs text-gray-500 dark:border-[#2e2e2e] dark:bg-[#262626] dark:text-[#a3a3a3]"
                        >
                            <Info
                                class="h-4 w-4 shrink-0 text-[#0b8043] dark:text-[#3fcb7a]"
                            />
                            <span
                                >Aktivitas meja keluarga dihitung per minggu
                                berjalan dan otomatis ter-reset setiap hari
                                Senin.</span
                            >
                        </div>
                    </div>
                </div>

                <!-- 3. Right Column: Aktivitas Makanan & Kalori Hari Ini (Permintaan Spesifik User) -->
                <div class="space-y-6 lg:col-span-5">
                    <div
                        class="space-y-5 rounded-3xl border border-gray-100 bg-white p-6 shadow-xs sm:p-8 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-xl bg-orange-100/70 text-[#eb4416] dark:bg-[#3a261c] dark:text-[#ff7a4d]"
                                >
                                    <Utensils class="h-4 w-4" />
                                </div>
                                <h3
                                    class="text-base font-bold text-gray-900 dark:text-[#f0efea]"
                                >
                                    Aktivitas Makan Hari Ini
                                </h3>
                            </div>
                            <span
                                class="text-xs font-medium text-gray-400 dark:text-[#737373]"
                            >
                                Hari ini
                            </span>
                        </div>

                        <!-- List Anggota & Detail Makanan Hari Ini -->
                        <div class="space-y-4">
                            <div
                                v-for="member in anggotaList"
                                :key="member.id"
                                class="overflow-hidden rounded-2xl border border-gray-100 transition-all dark:border-[#2e2e2e]"
                            >
                                <!-- Accordion Header -->
                                <button
                                    type="button"
                                    @click="toggleMemberAccordion(member.id)"
                                    class="flex w-full cursor-pointer items-center justify-between p-4 text-left transition-colors hover:bg-gray-50/80 dark:hover:bg-[#262626]"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-xs font-bold text-[#0b8043] dark:bg-[#17321f] dark:text-[#3fcb7a]"
                                        >
                                            {{ member.initials }}
                                        </div>
                                        <div>
                                            <div
                                                class="text-sm font-bold text-gray-900 dark:text-[#f0efea]"
                                            >
                                                {{ member.name }}
                                            </div>
                                            <div
                                                class="text-[11px] text-gray-400 dark:text-[#737373]"
                                            >
                                                {{
                                                    member.catatan_hari_ini
                                                        .length
                                                }}
                                                makanan tercatat
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div class="text-right">
                                            <div
                                                class="text-xs font-extrabold text-[#0b8043] dark:text-[#3fcb7a]"
                                            >
                                                {{
                                                    Math.round(
                                                        member.total_kalori_hari_ini,
                                                    )
                                                }}
                                                kkal
                                            </div>
                                            <div
                                                class="text-[10px] text-gray-400 dark:text-[#737373]"
                                            >
                                                dari
                                                {{
                                                    Math.round(
                                                        member.target_kalori_harian,
                                                    )
                                                }}
                                                kkal
                                            </div>
                                        </div>
                                        <component
                                            :is="
                                                expandedMemberId === member.id
                                                    ? ChevronUp
                                                    : ChevronDown
                                            "
                                            class="h-4 w-4 text-gray-400"
                                        />
                                    </div>
                                </button>

                                <!-- Accordion Body: Rincian Makanan -->
                                <div
                                    v-if="expandedMemberId === member.id"
                                    class="space-y-3 border-t border-gray-100 bg-gray-50/50 p-4 dark:border-[#2e2e2e] dark:bg-[#171717]"
                                >
                                    <div
                                        v-if="
                                            member.catatan_hari_ini.length === 0
                                        "
                                        class="py-2 text-center text-xs text-gray-400 dark:text-[#737373]"
                                    >
                                        Belum ada makanan yang dicatat hari ini.
                                    </div>

                                    <div
                                        v-for="item in member.catatan_hari_ini"
                                        :key="item.id"
                                        class="flex items-center justify-between border-b border-gray-100 py-1.5 text-xs last:border-0 dark:border-[#262626]"
                                    >
                                        <div class="min-w-0 pr-2">
                                            <span
                                                class="mr-1.5 inline-block rounded bg-gray-200 px-1.5 py-0.5 text-[10px] font-semibold text-gray-700 dark:bg-[#262626] dark:text-gray-300"
                                            >
                                                {{
                                                    formatWaktu(
                                                        item.waktu_konsumsi,
                                                    )
                                                }}
                                            </span>
                                            <span
                                                class="font-medium text-gray-800 dark:text-[#f0efea]"
                                            >
                                                {{ item.nama_makanan }}
                                            </span>
                                        </div>
                                        <div
                                            class="shrink-0 font-bold text-[#0b8043] dark:text-[#3fcb7a]"
                                        >
                                            {{ Math.round(item.total_kalori) }}
                                            kkal
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
