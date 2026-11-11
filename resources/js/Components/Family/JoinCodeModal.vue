<script setup lang="ts">
import { ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { X, CheckCircle2, Users, Loader2, AlertCircle } from 'lucide-vue-next';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const form = useForm({
    kode_keluarga: '',
});

const previewData = ref<{
    id: number;
    nama_keluarga: string;
    kepala_keluarga: string;
    jumlah_anggota: number;
} | null>(null);

const isLoadingPreview = ref(false);
const previewError = ref('');
let previewTimeout: ReturnType<typeof setTimeout> | null = null;

const onCodeInput = () => {
    // Otomatis ubah ke huruf kapital dan batasi 6 karakter
    form.kode_keluarga = form.kode_keluarga
        .toUpperCase()
        .replace(/[^A-Z0-9]/g, '')
        .slice(0, 6);
    previewData.value = null;
    previewError.value = '';

    if (form.kode_keluarga.length === 6) {
        checkPreview(form.kode_keluarga);
    }
};

const checkPreview = async (code: string) => {
    try {
        isLoadingPreview.value = true;
        previewError.value = '';
        const response = await fetch(
            route('user.family.preview_code', { kode: code }),
            {
                headers: {
                    Accept: 'application/json',
                },
            },
        );

        if (!response.ok) {
            throw new Error('Kode keluarga tidak ditemukan.');
        }

        const data = await response.json();
        if (data.found) {
            previewData.value = data;
        } else {
            previewError.value = 'Kode tidak ditemukan.';
        }
    } catch {
        previewError.value = 'Kode keluarga tidak ditemukan atau tidak valid.';
    } finally {
        isLoadingPreview.value = false;
    }
};

const submitJoin = () => {
    if (form.kode_keluarga.length !== 6) return;

    form.post(route('user.family.join_code'), {
        onSuccess: () => {
            emit('close');
            form.reset();
            previewData.value = null;
        },
    });
};

watch(
    () => props.show,
    (val) => {
        if (!val) {
            form.reset();
            previewData.value = null;
            previewError.value = '';
        }
    },
);
</script>

<template>
    <div
        v-if="show"
        class="animate-in fade-in fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs duration-150"
    >
        <div
            class="relative w-full max-w-lg rounded-3xl border border-gray-100 bg-white p-6 shadow-2xl sm:p-8 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
        >
            <!-- Close button -->
            <button
                type="button"
                @click="emit('close')"
                class="absolute top-6 right-6 cursor-pointer rounded-full p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-[#262626] dark:hover:text-[#f0efea]"
            >
                <X class="h-5 w-5" />
            </button>

            <div class="mb-6">
                <h3
                    class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl dark:text-[#f0efea]"
                >
                    Gabung dengan kode
                </h3>
                <p
                    class="mt-1 text-xs text-gray-500 sm:text-sm dark:text-[#a3a3a3]"
                >
                    Minta 6 karakter kode grup ke Kepala Keluarga kamu.
                </p>
            </div>

            <form @submit.prevent="submitJoin" class="space-y-6">
                <!-- 6 Character Input Box -->
                <div>
                    <div class="relative flex items-center justify-center">
                        <input
                            v-model="form.kode_keluarga"
                            @input="onCodeInput"
                            type="text"
                            maxlength="6"
                            placeholder="KS7R2M"
                            autofocus
                            class="w-full rounded-2xl border-2 border-gray-200 bg-[#f8f9fa] px-4 py-3.5 text-center font-mono text-2xl font-extrabold tracking-[0.5em] text-gray-900 uppercase placeholder-gray-300 transition focus:border-[#0b8043] focus:ring-4 focus:ring-[#0b8043]/15 focus:outline-none sm:text-3xl dark:border-[#3d3d3d] dark:bg-[#262626] dark:text-[#f0efea] dark:placeholder-[#555] dark:focus:border-[#3fcb7a]"
                        />
                    </div>
                    <p
                        v-if="form.errors.kode_keluarga"
                        class="mt-2 text-center text-xs font-medium text-red-500"
                    >
                        {{ form.errors.kode_keluarga }}
                    </p>
                </div>

                <!-- Preview Area -->
                <div
                    v-if="isLoadingPreview"
                    class="flex items-center justify-center gap-2 py-4 text-xs text-gray-500 dark:text-[#a3a3a3]"
                >
                    <Loader2
                        class="h-4 w-4 animate-spin text-[#0b8043] dark:text-[#3fcb7a]"
                    />
                    <span>Mencari informasi grup keluarga...</span>
                </div>

                <div
                    v-else-if="previewData"
                    class="animate-in fade-in flex items-center gap-3.5 rounded-2xl border border-emerald-200 bg-[#e7f5ec] p-4 duration-200 dark:border-[#204a2c] dark:bg-[#17321f]"
                >
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#0b8043] text-white dark:bg-[#3fcb7a] dark:text-[#121212]"
                    >
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4
                            class="truncate text-sm font-bold text-gray-900 dark:text-[#f0efea]"
                        >
                            {{ previewData.nama_keluarga }}
                        </h4>
                        <p
                            class="mt-0.5 text-xs text-gray-600 dark:text-[#a3a3a3]"
                        >
                            Kepala Keluarga:
                            <span
                                class="font-medium text-gray-800 dark:text-gray-200"
                                >{{ previewData.kepala_keluarga }}</span
                            >
                            • {{ previewData.jumlah_anggota }} anggota
                        </p>
                    </div>
                </div>

                <div
                    v-else-if="previewError"
                    class="flex items-center gap-2.5 rounded-2xl border border-red-200 bg-red-50 p-3.5 text-xs text-red-600 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-400"
                >
                    <AlertCircle class="h-4 w-4 shrink-0" />
                    <span>{{ previewError }}</span>
                </div>

                <!-- Action buttons -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        @click="emit('close')"
                        class="cursor-pointer rounded-xl px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:text-[#a3a3a3] dark:hover:bg-[#262626]"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="
                            form.kode_keluarga.length !== 6 || form.processing
                        "
                        class="flex cursor-pointer items-center gap-2 rounded-xl bg-[#0b8043] px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-[#096836] disabled:opacity-40 dark:bg-[#3fcb7a] dark:text-[#121212] dark:hover:bg-[#34aa66]"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="h-4 w-4 animate-spin"
                        />
                        <span>Gabung ke Grup</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
