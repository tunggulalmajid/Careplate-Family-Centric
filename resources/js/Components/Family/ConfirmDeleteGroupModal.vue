<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { X, Trash2, Loader2, AlertTriangle } from 'lucide-vue-next';

const props = defineProps<{
    show: boolean;
    familyName: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const form = useForm({});

const submitDelete = () => {
    form.delete(route('user.family.delete_group'), {
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <div
        v-if="show"
        class="animate-in fade-in fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs duration-150"
    >
        <div
            class="relative w-full max-w-md rounded-3xl border border-gray-100 bg-white p-6 shadow-2xl sm:p-8 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
        >
            <button
                type="button"
                @click="emit('close')"
                class="absolute top-6 right-6 cursor-pointer rounded-full p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-[#262626] dark:hover:text-[#f0efea]"
            >
                <X class="h-5 w-5" />
            </button>

            <div class="mb-5">
                <div
                    class="mb-3 flex h-10 w-10 items-center justify-center rounded-2xl bg-red-100 text-red-600 dark:bg-red-950/60 dark:text-red-400"
                >
                    <AlertTriangle class="h-5 w-5" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-[#f0efea]">
                    Hapus grup keluarga?
                </h3>
                <p
                    class="mt-2 text-xs leading-relaxed text-gray-500 sm:text-sm dark:text-[#a3a3a3]"
                >
                    Semua anggota akan otomatis keluar dari grup
                    <span class="font-bold text-gray-900 dark:text-[#f0efea]">{{
                        familyName
                    }}</span>
                    dan kode grup tidak lagi aktif. Seluruh catatan makan
                    masing-masing anggota tetap aman tersimpan.
                </p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3">
                <button
                    type="button"
                    @click="emit('close')"
                    class="cursor-pointer rounded-xl px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-100 dark:text-[#a3a3a3] dark:hover:bg-[#262626]"
                >
                    Batal
                </button>
                <button
                    type="button"
                    @click="submitDelete"
                    :disabled="form.processing"
                    class="flex cursor-pointer items-center gap-2 rounded-xl bg-red-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-700 disabled:opacity-40"
                >
                    <Loader2
                        v-if="form.processing"
                        class="h-4 w-4 animate-spin"
                    />
                    <span>Ya, Hapus Grup</span>
                </button>
            </div>
        </div>
    </div>
</template>
