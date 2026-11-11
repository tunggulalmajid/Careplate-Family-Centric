<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import {
    ArrowLeft,
    Users,
    Copy,
    Lock,
    CheckCircle2,
    AlertCircle,
    Loader2,
    Sparkles,
} from 'lucide-vue-next';

const form = useForm({
    nama_keluarga: '',
});

const isChecking = ref(false);
const isAvailable = ref<boolean | null>(null);
const checkMessage = ref('');
let checkTimeout: ReturnType<typeof setTimeout> | null = null;

const characterCount = computed(() => form.nama_keluarga.length);

const handleNameInput = () => {
    isAvailable.value = null;
    checkMessage.value = '';

    if (form.nama_keluarga.trim().length < 3) {
        return;
    }

    if (checkTimeout) clearTimeout(checkTimeout);
    checkTimeout = setTimeout(() => {
        checkAvailability();
    }, 400);
};

const checkAvailability = async () => {
    try {
        isChecking.value = true;
        const response = await fetch(route('user.family.check_name'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    (
                        document.querySelector(
                            'meta[name="csrf-token"]',
                        ) as HTMLMetaElement
                    )?.content || '',
                Accept: 'application/json',
            },
            body: JSON.stringify({ nama_keluarga: form.nama_keluarga.trim() }),
        });

        const data = await response.json();
        isAvailable.value = data.available;
        checkMessage.value = data.message;
    } catch {
        // Fallback
    } finally {
        isChecking.value = false;
    }
};

const submitCreate = () => {
    if (form.nama_keluarga.trim().length < 3 || form.processing) return;

    form.post(route('user.family.store'));
};
</script>

<template>
    <UserLayout title="Buat Grup Keluarga">
        <Head title="Buat Grup Keluarga - CarePlate" />

        <div class="mx-auto max-w-5xl space-y-8 pb-12">
            <!-- Back navigation -->
            <div>
                <Link
                    :href="route('user.family')"
                    class="group inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-gray-600 transition-colors hover:text-[#0b8043] dark:text-[#a3a3a3] dark:hover:text-[#3fcb7a]"
                >
                    <ArrowLeft
                        class="h-4 w-4 transition-transform group-hover:-translate-x-1"
                    />
                    <span>Kembali ke Keluarga</span>
                </Link>
            </div>

            <!-- Page Title Header -->
            <div>
                <h1
                    class="font-display text-3xl font-extrabold text-gray-900 sm:text-4xl dark:text-[#f0efea]"
                >
                    Buat grup keluarga
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-[#a3a3a3]">
                    Beri nama untuk grup keluargamu dan mulailah melacak
                    kebiasaan gizi bersama.
                </p>
            </div>

            <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
                <!-- Left Form Column (Figma 26:6540) -->
                <div
                    class="space-y-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-xs sm:p-8 lg:col-span-7 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
                >
                    <form @submit.prevent="submitCreate" class="space-y-6">
                        <!-- Input Nama Grup -->
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label
                                    class="block text-xs font-bold tracking-wider text-gray-700 uppercase dark:text-[#a3a3a3]"
                                >
                                    Nama grup keluarga
                                    <span class="text-red-500">*</span>
                                </label>
                                <span
                                    class="font-mono text-xs"
                                    :class="
                                        characterCount > 40
                                            ? 'font-bold text-red-500'
                                            : 'text-gray-400 dark:text-[#737373]'
                                    "
                                >
                                    {{ characterCount }}/40
                                </span>
                            </div>

                            <div class="relative">
                                <input
                                    v-model="form.nama_keluarga"
                                    @input="handleNameInput"
                                    type="text"
                                    maxlength="40"
                                    placeholder="Contoh: Keluarga Santoso"
                                    autofocus
                                    class="w-full rounded-2xl border bg-white px-4 py-3.5 text-base font-semibold text-gray-900 placeholder-gray-400 transition focus:ring-2 focus:outline-none sm:text-lg dark:bg-[#262626] dark:text-[#f0efea] dark:placeholder-[#737373]"
                                    :class="[
                                        isAvailable === true
                                            ? 'border-emerald-500 focus:border-emerald-500 focus:ring-emerald-500/15'
                                            : isAvailable === false
                                              ? 'border-red-500 focus:border-red-500 focus:ring-red-500/15'
                                              : 'border-gray-200 focus:border-[#0b8043] focus:ring-[#0b8043]/15 dark:border-[#3d3d3d] dark:focus:border-[#3fcb7a]',
                                    ]"
                                />

                                <div
                                    class="absolute top-1/2 right-4 flex -translate-y-1/2 items-center"
                                >
                                    <Loader2
                                        v-if="isChecking"
                                        class="h-4 w-4 animate-spin text-gray-400"
                                    />
                                    <CheckCircle2
                                        v-else-if="isAvailable === true"
                                        class="h-5 w-5 text-emerald-500"
                                    />
                                    <AlertCircle
                                        v-else-if="isAvailable === false"
                                        class="h-5 w-5 text-red-500"
                                    />
                                </div>
                            </div>

                            <p
                                v-if="checkMessage"
                                class="mt-1.5 text-xs font-medium"
                                :class="
                                    isAvailable
                                        ? 'text-emerald-600 dark:text-[#3fcb7a]'
                                        : 'text-red-500'
                                "
                            >
                                {{ checkMessage }}
                            </p>
                            <p
                                v-if="form.errors.nama_keluarga"
                                class="mt-1.5 text-xs font-medium text-red-500"
                            >
                                {{ form.errors.nama_keluarga }}
                            </p>
                        </div>

                        <!-- Hak & Keuntungan Kepala Keluarga (Figma 26:6563) -->
                        <div
                            class="space-y-3.5 rounded-2xl border border-gray-100 bg-gray-50/80 p-5 dark:border-[#2e2e2e] dark:bg-[#262626]"
                        >
                            <div
                                class="flex items-center gap-3 text-xs text-gray-700 sm:text-sm dark:text-[#d4d4d4]"
                            >
                                <div
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-100/70 text-[#0b8043] dark:bg-[#17321f] dark:text-[#3fcb7a]"
                                >
                                    <Users class="h-4 w-4" />
                                </div>
                                <span
                                    >Kamu otomatis menjadi
                                    <strong>Kepala Keluarga</strong></span
                                >
                            </div>

                            <div
                                class="flex items-center gap-3 text-xs text-gray-700 sm:text-sm dark:text-[#d4d4d4]"
                            >
                                <div
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-100/70 text-[#0b8043] dark:bg-[#17321f] dark:text-[#3fcb7a]"
                                >
                                    <Copy class="h-4 w-4" />
                                </div>
                                <span
                                    >Mendapat kode 6 karakter & tautan untuk
                                    dibagikan</span
                                >
                            </div>

                            <div
                                class="flex items-center gap-3 text-xs text-gray-700 sm:text-sm dark:text-[#d4d4d4]"
                            >
                                <div
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-100/70 text-[#0b8043] dark:bg-[#17321f] dark:text-[#3fcb7a]"
                                >
                                    <Lock class="h-4 w-4" />
                                </div>
                                <span
                                    >Data medis, BMI, dan berat badan anggota
                                    tetap privat</span
                                >
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="
                                form.nama_keluarga.trim().length < 3 ||
                                isAvailable === false ||
                                form.processing
                            "
                            class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-[#0b8043] px-6 py-3.5 text-base font-bold text-white shadow-sm transition-all hover:bg-[#096836] disabled:opacity-40 dark:bg-[#3fcb7a] dark:text-[#121212] dark:hover:bg-[#34aa66]"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="h-5 w-5 animate-spin"
                            />
                            <span>Buat Grup Keluarga</span>
                        </button>
                    </form>
                </div>

                <!-- Right Preview Column (Figma 26:6576) -->
                <div
                    class="space-y-4 rounded-3xl border border-gray-100 bg-white p-6 shadow-xs sm:p-8 lg:col-span-5 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
                >
                    <span
                        class="text-xs font-bold tracking-wider text-gray-400 uppercase dark:text-[#737373]"
                    >
                        Pratinjau Kartu Grup
                    </span>

                    <div
                        class="space-y-3 rounded-2xl border border-orange-100 bg-[#fff2eb] p-6 dark:border-[#3d2c26] dark:bg-[#261f1c]"
                    >
                        <h3
                            class="truncate text-xl font-bold text-gray-900 dark:text-[#f0efea]"
                        >
                            {{
                                form.nama_keluarga.trim() ||
                                'Nama Keluarga Anda'
                            }}
                        </h3>

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <span
                                class="rounded-full bg-[#0b8043] px-2.5 py-1 text-[11px] font-bold text-white dark:bg-[#3fcb7a] dark:text-[#121212]"
                            >
                                Kepala Keluarga
                            </span>
                            <span
                                class="rounded-full bg-white/80 px-2.5 py-1 text-[11px] font-medium text-gray-700 dark:bg-[#333] dark:text-gray-300"
                            >
                                1 Anggota
                            </span>
                        </div>

                        <p
                            class="border-t border-orange-200/50 pt-2 text-xs text-gray-500 dark:border-[#3d2c26] dark:text-[#a3a3a3]"
                        >
                            Kode grup 6 karakter dan tautan undangan akan
                            otomatis dibuat setelah kamu menekan tombol Buat
                            Grup.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
