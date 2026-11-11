<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    X,
    ShieldAlert,
    Loader2,
    ChevronDown,
    UserCheck,
} from 'lucide-vue-next';

interface MemberOption {
    id: number;
    name: string;
    is_kepala: boolean;
    role_label: string;
}

const props = defineProps<{
    show: boolean;
    members: MemberOption[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const form = useForm({
    new_head_id: '',
});

// Hanya anggota yang bukan kepala keluarga saat ini
const availableMembers = computed(() => {
    return props.members.filter((m) => !m.is_kepala);
});

const selectedMemberName = computed(() => {
    const found = availableMembers.value.find(
        (m) => String(m.id) === String(form.new_head_id),
    );
    return found ? found.name : '';
});

const submitTransfer = () => {
    if (!form.new_head_id) return;

    form.post(route('user.family.transfer_head'), {
        onSuccess: () => {
            emit('close');
            form.reset();
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
            class="relative w-full max-w-lg rounded-3xl border border-gray-100 bg-white p-6 shadow-2xl sm:p-8 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
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
                    class="mb-3 flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400"
                >
                    <ShieldAlert class="h-5 w-5" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-[#f0efea]">
                    Alihkan Kepala Keluarga?
                </h3>
                <p
                    class="mt-1 text-xs text-gray-500 sm:text-sm dark:text-[#a3a3a3]"
                >
                    Pilih anggota keluarga yang akan mengelola grup. Setelah
                    dialihkan, kamu akan tetap berada di dalam grup sebagai
                    Anggota.
                </p>
            </div>

            <form @submit.prevent="submitTransfer" class="space-y-5">
                <div>
                    <label
                        class="mb-2 block text-xs font-bold tracking-wider text-gray-700 uppercase dark:text-[#a3a3a3]"
                    >
                        Pilih Kepala Keluarga Baru
                    </label>

                    <div class="relative">
                        <select
                            v-model="form.new_head_id"
                            class="w-full cursor-pointer appearance-none rounded-2xl border border-gray-200 bg-white px-4 py-3 pr-10 text-sm text-gray-900 transition focus:border-[#0b8043] focus:ring-2 focus:ring-[#0b8043]/15 focus:outline-none dark:border-[#3d3d3d] dark:bg-[#262626] dark:text-[#f0efea] dark:focus:border-[#3fcb7a]"
                        >
                            <option value="" disabled>
                                -- Pilih dari anggota keluarga --
                            </option>
                            <option
                                v-for="member in availableMembers"
                                :key="member.id"
                                :value="member.id"
                            >
                                {{ member.name }}
                            </option>
                        </select>
                        <ChevronDown
                            class="pointer-events-none absolute top-1/2 right-4 h-4 w-4 -translate-y-1/2 text-gray-400"
                        />
                    </div>

                    <p
                        v-if="form.errors.new_head_id"
                        class="mt-1.5 text-xs font-medium text-red-500"
                    >
                        {{ form.errors.new_head_id }}
                    </p>
                </div>

                <!-- Confirmation Notice Preview -->
                <div
                    v-if="selectedMemberName"
                    class="flex items-start gap-3 rounded-2xl border border-amber-200/60 bg-amber-50/70 p-4 text-xs leading-relaxed text-amber-800 dark:border-amber-900/40 dark:bg-amber-950/20 dark:text-amber-300"
                >
                    <UserCheck
                        class="mt-0.5 h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400"
                    />
                    <div>
                        <span class="font-bold">{{ selectedMemberName }}</span>
                        akan menjadi Kepala Keluarga dan memiliki hak kelola
                        grup. Statusmu akan beralih menjadi Anggota.
                    </div>
                </div>

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
                        :disabled="!form.new_head_id || form.processing"
                        class="flex cursor-pointer items-center gap-2 rounded-xl bg-amber-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-40"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="h-4 w-4 animate-spin"
                        />
                        <span>Alihkan Peran</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
