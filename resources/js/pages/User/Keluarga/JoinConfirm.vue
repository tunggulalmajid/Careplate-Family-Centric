<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import {
    Users,
    CheckCircle2,
    ArrowRight,
    AlertTriangle,
    Loader2,
} from 'lucide-vue-next';

const props = defineProps<{
    keluarga: {
        id: number;
        nama_keluarga: string;
        kode_keluarga: string;
        kepala_keluarga: string;
        jumlah_anggota: number;
    };
    userCurrentFamily: string | null;
}>();

const form = useForm({});

const submitJoin = () => {
    form.post(
        route('user.family.accept_join_link', {
            kode: props.keluarga.kode_keluarga,
        }),
    );
};
</script>

<template>
    <UserLayout title="Undangan Bergabung Keluarga">
        <Head :title="`Undangan ${keluarga.nama_keluarga} - CarePlate`" />

        <div class="mx-auto max-w-xl space-y-6 py-8 sm:py-12">
            <div
                class="space-y-6 rounded-3xl border border-gray-100 bg-white p-6 text-center shadow-sm sm:p-10 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
            >
                <!-- Icon -->
                <div
                    class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-emerald-50 text-[#0b8043] shadow-inner dark:bg-[#17321f] dark:text-[#3fcb7a]"
                >
                    <Users class="h-10 w-10" />
                </div>

                <div>
                    <span
                        class="text-xs font-bold tracking-wider text-[#0b8043] uppercase dark:text-[#3fcb7a]"
                    >
                        Undangan Grup Keluarga
                    </span>
                    <h1
                        class="mt-1 text-2xl font-extrabold text-gray-900 sm:text-3xl dark:text-[#f0efea]"
                    >
                        {{ keluarga.nama_keluarga }}
                    </h1>
                    <p
                        class="mt-1 text-xs text-gray-500 sm:text-sm dark:text-[#a3a3a3]"
                    >
                        Kepala Keluarga:
                        <span
                            class="font-semibold text-gray-800 dark:text-gray-200"
                            >{{ keluarga.kepala_keluarga }}</span
                        >
                        • {{ keluarga.jumlah_anggota }} anggota
                    </p>
                </div>

                <!-- Info Box -->
                <div
                    class="rounded-2xl border border-gray-100 bg-gray-50 p-4 text-left text-xs leading-relaxed text-gray-600 dark:border-[#2e2e2e] dark:bg-[#262626] dark:text-[#d4d4d4]"
                >
                    Dengan bergabung, kamu akan ikut serta dalam meja catatan
                    keluarga mingguan dan saling memotivasi kebiasaan makan
                    sehat. Catatan berat badan dan data medis pribadimu tetap
                    aman privat.
                </div>

                <!-- Warning if already in another family -->
                <div
                    v-if="userCurrentFamily"
                    class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-left text-xs text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-300"
                >
                    <AlertTriangle
                        class="mt-0.5 h-5 w-5 shrink-0 text-amber-600"
                    />
                    <div>
                        Kamu saat ini masih tergabung di grup
                        <strong>{{ userCurrentFamily }}</strong
                        >. Kamu harus keluar dari grup tersebut terlebih dahulu
                        sebelum bisa bergabung ke grup baru.
                    </div>
                </div>

                <!-- Action Buttons -->
                <div
                    class="flex flex-col items-center justify-center gap-3 pt-2 sm:flex-row"
                >
                    <Link
                        :href="route('user.family')"
                        class="w-full rounded-xl border border-gray-200 px-6 py-3 text-center text-sm font-semibold text-gray-600 transition hover:bg-gray-100 sm:w-auto dark:border-[#3d3d3d] dark:text-[#a3a3a3] dark:hover:bg-[#262626]"
                    >
                        Batal
                    </Link>

                    <button
                        v-if="!userCurrentFamily"
                        type="button"
                        @click="submitJoin"
                        :disabled="form.processing"
                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#0b8043] px-8 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#096836] disabled:opacity-40 sm:w-auto dark:bg-[#3fcb7a] dark:text-[#121212] dark:hover:bg-[#34aa66]"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="h-4 w-4 animate-spin"
                        />
                        <span>Ya, Bergabung</span>
                        <ArrowRight v-if="!form.processing" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
