<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import JoinCodeModal from '@/Components/Family/JoinCodeModal.vue';
import {
    Users,
    Calendar,
    Lock,
    ArrowRight,
    Sparkles,
    PlusCircle,
    KeyRound,
} from 'lucide-vue-next';

const isJoinModalOpen = ref(false);
</script>

<template>
    <UserLayout title="Keluarga">
        <Head title="Grup Keluarga - CarePlate" />

        <div class="space-y-8 pb-12">
            <!-- 1. Header Hero Banner (Figma 19:4307) -->
            <div
                class="relative overflow-hidden rounded-3xl border border-orange-100/70 bg-[#fff2eb] p-6 shadow-xs transition-colors sm:p-8 md:p-10 dark:border-[#3d2c26] dark:bg-[#261f1c]"
            >
                <div
                    class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center"
                >
                    <div class="max-w-xl">
                        <h1
                            class="font-display text-3xl font-extrabold tracking-tight text-[#191919] sm:text-4xl md:text-5xl dark:text-[#f0efea]"
                        >
                            Keluarga
                        </h1>
                        <p
                            class="mt-2 text-sm leading-relaxed text-gray-600 sm:text-base dark:text-[#a3a3a3]"
                        >
                            Catat bersama, lihat siapa saja yang sudah mencatat
                            minggu ini, dan saling menyemangati untuk hidup
                            lebih sehat.
                        </p>
                    </div>

                    <!-- Decorative Food/Meal Illustration -->
                    <div
                        class="flex shrink-0 items-center justify-center md:pr-4"
                    >
                        <div
                            class="flex h-24 w-24 items-center justify-center rounded-full bg-orange-100/70 text-4xl shadow-inner sm:h-28 sm:w-28 dark:bg-[#382319]"
                        >
                            🥗
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Two Big Action Cards: Buat vs Gabung (Figma 19:4311) -->
            <div class="grid grid-cols-1 gap-6 sm:gap-8 md:grid-cols-2">
                <!-- Option 1: Buat Grup Keluarga (Figma 19:4312) -->
                <div
                    class="group flex flex-col items-center justify-between rounded-3xl border border-gray-100 bg-white p-8 text-center shadow-xs transition-all hover:shadow-md sm:p-10 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
                >
                    <div class="flex flex-col items-center">
                        <div
                            class="mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-emerald-50 text-[#0b8043] transition-transform group-hover:scale-105 dark:bg-[#17321f] dark:text-[#3fcb7a]"
                        >
                            <PlusCircle class="h-12 w-12 stroke-[1.5]" />
                        </div>

                        <h2
                            class="mb-3 text-2xl font-bold text-gray-900 dark:text-[#f0efea]"
                        >
                            Buat grup keluarga
                        </h2>

                        <p
                            class="mb-8 max-w-sm text-sm leading-relaxed text-gray-600 dark:text-[#a3a3a3]"
                        >
                            Kamu otomatis menjadi
                            <span
                                class="font-semibold text-gray-800 dark:text-gray-200"
                                >Kepala Keluarga</span
                            >
                            dan mendapatkan kode 6 karakter serta tautan untuk
                            mengundang seluruh anggota.
                        </p>
                    </div>

                    <Link
                        :href="route('user.family.create')"
                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#0b8043] px-8 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-[#096836] sm:w-auto dark:bg-[#3fcb7a] dark:text-[#121212] dark:hover:bg-[#34aa66]"
                    >
                        <span>Buat Grup</span>
                        <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <!-- Option 2: Gabung dengan Kode (Figma 19:4336) -->
                <div
                    class="group flex flex-col items-center justify-between rounded-3xl border border-gray-100 bg-white p-8 text-center shadow-xs transition-all hover:shadow-md sm:p-10 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
                >
                    <div class="flex flex-col items-center">
                        <div
                            class="mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-amber-50 text-amber-600 transition-transform group-hover:scale-105 dark:bg-amber-950/30 dark:text-amber-400"
                        >
                            <KeyRound class="h-12 w-12 stroke-[1.5]" />
                        </div>

                        <h2
                            class="mb-3 text-2xl font-bold text-gray-900 dark:text-[#f0efea]"
                        >
                            Gabung dengan kode
                        </h2>

                        <p
                            class="mb-8 max-w-sm text-sm leading-relaxed text-gray-600 dark:text-[#a3a3a3]"
                        >
                            Sudah ada anggota keluarga yang membuat grup?
                            Masukkan 6 karakter kode undangan yang dibagikan
                            oleh Kepala Keluarga.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="isJoinModalOpen = true"
                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-gray-900 px-8 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-black sm:w-auto dark:bg-[#f0efea] dark:text-[#121212] dark:hover:bg-white"
                    >
                        <span>Gabung ke Grup</span>
                        <ArrowRight class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- 3. Bottom Information Banner: Yang bisa dilihat keluarga (Figma 19:4355) -->
            <div
                class="rounded-3xl border border-gray-100 bg-white p-6 shadow-xs sm:p-8 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
            >
                <h3
                    class="mb-6 text-base font-bold text-gray-900 dark:text-[#f0efea]"
                >
                    Yang bisa dilihat keluarga
                </h3>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div class="flex items-start gap-3.5">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-[#0b8043] dark:bg-[#17321f] dark:text-[#3fcb7a]"
                        >
                            <Users class="h-5 w-5" />
                        </div>
                        <div>
                            <h4
                                class="text-sm font-semibold text-gray-900 dark:text-[#f0efea]"
                            >
                                Siapa saja anggota grup
                            </h4>
                            <p
                                class="mt-1 text-xs leading-relaxed text-gray-500 dark:text-[#a3a3a3]"
                            >
                                Nama, status peran, serta kebersamaan dalam
                                memantau kebiasaan sehat keluarga.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-[#0b8043] dark:bg-[#17321f] dark:text-[#3fcb7a]"
                        >
                            <Calendar class="h-5 w-5" />
                        </div>
                        <div>
                            <h4
                                class="text-sm font-semibold text-gray-900 dark:text-[#f0efea]"
                            >
                                Hari yang sudah tercatat
                            </h4>
                            <p
                                class="mt-1 text-xs leading-relaxed text-gray-500 dark:text-[#a3a3a3]"
                            >
                                Keaktifan mingguan anggota dalam mencatat
                                makanan untuk mencapai target bersama 5 hari.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-[#0b8043] dark:bg-[#17321f] dark:text-[#3fcb7a]"
                        >
                            <Lock class="h-5 w-5" />
                        </div>
                        <div>
                            <h4
                                class="text-sm font-semibold text-gray-900 dark:text-[#f0efea]"
                            >
                                Data medis tetap privat
                            </h4>
                            <p
                                class="mt-1 text-xs leading-relaxed text-gray-500 dark:text-[#a3a3a3]"
                            >
                                Nilai BMI, kalkulasi berat badan, serta data
                                sensitif kesehatan lainnya tetap terjaga privat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Join with code Modal -->
        <JoinCodeModal
            :show="isJoinModalOpen"
            @close="isJoinModalOpen = false"
        />
    </UserLayout>
</template>
