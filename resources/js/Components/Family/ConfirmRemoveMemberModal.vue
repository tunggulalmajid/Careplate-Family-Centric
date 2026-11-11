<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { X, UserMinus, Loader2 } from 'lucide-vue-next';

interface TargetMember {
    id: number;
    name: string;
}

const props = defineProps<{
    show: boolean;
    member: TargetMember | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const form = useForm({});

const submitRemove = () => {
    if (!props.member) return;

    form.delete(
        route('user.family.remove_member', { member: props.member.id }),
        {
            onSuccess: () => {
                emit('close');
            },
        },
    );
};
</script>

<template>
    <div
        v-if="show && member"
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
                    class="mb-3 flex h-10 w-10 items-center justify-center rounded-2xl bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400"
                >
                    <UserMinus class="h-5 w-5" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-[#f0efea]">
                    Keluarkan anggota?
                </h3>
                <p
                    class="mt-2 text-xs leading-relaxed text-gray-500 sm:text-sm dark:text-[#a3a3a3]"
                >
                    Apakah kamu yakin ingin mengeluarkan
                    <span class="font-bold text-gray-900 dark:text-[#f0efea]">{{
                        member.name
                    }}</span>
                    dari grup keluarga? Riwayat catatan makannya akan tetap
                    tersimpan di akun pribadinya.
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
                    @click="submitRemove"
                    :disabled="form.processing"
                    class="flex cursor-pointer items-center gap-2 rounded-xl bg-red-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-700 disabled:opacity-40"
                >
                    <Loader2
                        v-if="form.processing"
                        class="h-4 w-4 animate-spin"
                    />
                    <span>Keluarkan</span>
                </button>
            </div>
        </div>
    </div>
</template>
