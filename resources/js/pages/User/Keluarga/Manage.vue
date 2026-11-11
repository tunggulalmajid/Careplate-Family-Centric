<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import TransferHeadModal from '@/Components/Family/TransferHeadModal.vue';
import ConfirmRemoveMemberModal from '@/Components/Family/ConfirmRemoveMemberModal.vue';
import ConfirmDeleteGroupModal from '@/Components/Family/ConfirmDeleteGroupModal.vue';
import {
    ArrowLeft,
    Copy,
    Check,
    RefreshCw,
    UserMinus,
    Shield,
    Trash2,
    Loader2,
    Save,
} from 'lucide-vue-next';

interface MemberItem {
    id: number;
    user_id: number;
    name: string;
    email: string;
    initials: string;
    is_kepala: boolean;
    role_label: string;
    usia: number | null;
}

const props = defineProps<{
    keluarga: {
        id: number;
        nama_keluarga: string;
        kode_keluarga: string;
        link_undangan: string;
    };
    anggota: MemberItem[];
}>();

const nameForm = useForm({
    nama_keluarga: props.keluarga.nama_keluarga,
});

const copiedCode = ref(false);
const isRegeneratingCode = ref(false);

// Modals state
const isTransferModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const memberToRemove = ref<MemberItem | null>(null);

const copyCode = async () => {
    try {
        await navigator.clipboard.writeText(props.keluarga.kode_keluarga);
        copiedCode.value = true;
        setTimeout(() => (copiedCode.value = false), 2000);
    } catch {
        // Fallback
    }
};

const handleUpdateName = () => {
    nameForm.put(route('user.family.update_name'), {
        preserveScroll: true,
    });
};

const handleRegenerateCode = () => {
    if (
        confirm(
            'Apakah kamu yakin ingin membuat kode grup baru? Kode lama tidak akan bisa digunakan lagi.',
        )
    ) {
        isRegeneratingCode.value = true;
        router.post(
            route('user.family.regenerate_code'),
            {},
            {
                onFinish: () => (isRegeneratingCode.value = false),
            },
        );
    }
};

const openRemoveModal = (member: MemberItem) => {
    memberToRemove.value = member;
};
</script>

<template>
    <UserLayout title="Kelola Grup Keluarga">
        <Head :title="`Kelola Grup ${keluarga.nama_keluarga} - CarePlate`" />

        <div class="mx-auto max-w-5xl space-y-8 pb-16">
            <!-- Back Navigation (Figma 35:9787) -->
            <div>
                <Link
                    :href="route('user.family')"
                    class="group inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-gray-600 transition-colors hover:text-[#0b8043] dark:text-[#a3a3a3] dark:hover:text-[#3fcb7a]"
                >
                    <ArrowLeft
                        class="h-4 w-4 transition-transform group-hover:-translate-x-1"
                    />
                    <span>{{ keluarga.nama_keluarga }}</span>
                </Link>
            </div>

            <!-- Page Title -->
            <div>
                <h1
                    class="font-display text-3xl font-extrabold text-gray-900 sm:text-4xl dark:text-[#f0efea]"
                >
                    Kelola grup
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-[#a3a3a3]">
                    Atur nama grup, kode undangan, serta peran anggota keluarga
                    kamu.
                </p>
            </div>

            <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-12">
                <!-- Left Column (Figma 35:9790) -->
                <div class="space-y-6 lg:col-span-7">
                    <!-- Card 1: Ubah Nama Grup (Figma 35:9791) -->
                    <div
                        class="space-y-5 rounded-3xl border border-gray-100 bg-white p-6 shadow-xs sm:p-8 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
                    >
                        <div>
                            <h3
                                class="text-base font-bold text-gray-900 dark:text-[#f0efea]"
                            >
                                Nama grup
                            </h3>
                            <p
                                class="mt-0.5 text-xs text-gray-500 dark:text-[#a3a3a3]"
                            >
                                Ganti nama keluarga yang tampil di dashboard
                                meja bersama.
                            </p>
                        </div>

                        <form
                            @submit.prevent="handleUpdateName"
                            class="space-y-4"
                        >
                            <div>
                                <div
                                    class="mb-1.5 flex items-center justify-between"
                                >
                                    <label
                                        class="block text-xs font-semibold text-gray-600 dark:text-[#a3a3a3]"
                                    >
                                        Nama keluarga
                                    </label>
                                    <span
                                        class="font-mono text-xs text-gray-400"
                                    >
                                        {{ nameForm.nama_keluarga.length }}/40
                                    </span>
                                </div>
                                <input
                                    v-model="nameForm.nama_keluarga"
                                    type="text"
                                    maxlength="40"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 transition focus:border-[#0b8043] focus:ring-2 focus:ring-[#0b8043]/15 focus:outline-none dark:border-[#3d3d3d] dark:bg-[#262626] dark:text-[#f0efea] dark:focus:border-[#3fcb7a]"
                                />
                                <p
                                    v-if="nameForm.errors.nama_keluarga"
                                    class="mt-1 text-xs font-medium text-red-500"
                                >
                                    {{ nameForm.errors.nama_keluarga }}
                                </p>
                            </div>

                            <button
                                type="submit"
                                :disabled="
                                    nameForm.processing ||
                                    nameForm.nama_keluarga ===
                                        keluarga.nama_keluarga
                                "
                                class="flex cursor-pointer items-center gap-1.5 rounded-xl bg-[#0b8043] px-5 py-2.5 text-xs font-bold text-white transition hover:bg-[#096836] disabled:opacity-40 dark:bg-[#3fcb7a] dark:text-[#121212] dark:hover:bg-[#34aa66]"
                            >
                                <Loader2
                                    v-if="nameForm.processing"
                                    class="h-3.5 w-3.5 animate-spin"
                                />
                                <Save v-else class="h-3.5 w-3.5" />
                                <span>Simpan Nama</span>
                            </button>
                        </form>
                    </div>

                    <!-- Card 2: Daftar Anggota (Figma 35:9800) -->
                    <div
                        class="space-y-5 rounded-3xl border border-gray-100 bg-white p-6 shadow-xs sm:p-8 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h3
                                    class="text-base font-bold text-gray-900 dark:text-[#f0efea]"
                                >
                                    Anggota ({{ anggota.length }})
                                </h3>
                                <p
                                    class="mt-0.5 text-xs text-gray-500 dark:text-[#a3a3a3]"
                                >
                                    Daftar orang yang saat ini terhubung di grup
                                    keluarga kamu.
                                </p>
                            </div>
                        </div>

                        <div
                            class="divide-y divide-gray-100 dark:divide-[#262626]"
                        >
                            <div
                                v-for="member in anggota"
                                :key="member.id"
                                class="flex items-center justify-between gap-4 py-3.5"
                            >
                                <div class="flex min-w-0 items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-emerald-200/50 bg-emerald-100 text-xs font-bold text-[#0b8043] dark:border-[#2e2e2e] dark:bg-[#17321f] dark:text-[#3fcb7a]"
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
                                            class="text-xs text-gray-400 dark:text-[#737373]"
                                        >
                                            {{ member.role_label }}
                                            <span v-if="member.usia"
                                                >• {{ member.usia }} tahun</span
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Action: Keluarkan jika bukan Kepala Keluarga -->
                                <button
                                    v-if="!member.is_kepala"
                                    type="button"
                                    @click="openRemoveModal(member)"
                                    class="shrink-0 cursor-pointer rounded-xl border border-red-200 px-3.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:text-red-400 dark:hover:bg-red-950/30"
                                >
                                    Keluarkan
                                </button>
                                <span
                                    v-else
                                    class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-bold text-[#0b8043] dark:bg-[#17321f] dark:text-[#3fcb7a]"
                                >
                                    Kepala Keluarga
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Figma 35:9840) -->
                <div class="space-y-6 lg:col-span-5">
                    <!-- Card 1: Kode Grup (Figma 35:9841) -->
                    <div
                        class="space-y-4 rounded-3xl border border-gray-100 bg-white p-6 shadow-xs sm:p-8 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
                    >
                        <h3
                            class="text-base font-bold text-gray-900 dark:text-[#f0efea]"
                        >
                            Kode grup
                        </h3>

                        <div
                            class="flex items-center justify-between rounded-2xl border border-gray-100 bg-gray-50 p-4 dark:border-[#2e2e2e] dark:bg-[#262626]"
                        >
                            <span
                                class="font-mono text-2xl font-black tracking-widest text-gray-900 dark:text-[#f0efea]"
                            >
                                {{ keluarga.kode_keluarga }}
                            </span>
                            <button
                                type="button"
                                @click="copyCode"
                                class="cursor-pointer rounded-xl p-2 text-gray-500 transition hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-[#333] dark:hover:text-[#f0efea]"
                                title="Salin kode"
                            >
                                <Check
                                    v-if="copiedCode"
                                    class="h-5 w-5 text-emerald-500"
                                />
                                <Copy v-else class="h-5 w-5" />
                            </button>
                        </div>

                        <p
                            class="text-xs leading-relaxed text-gray-500 dark:text-[#a3a3a3]"
                        >
                            Membuat kode baru akan membuat kode lama tidak
                            berlaku lagi bagi anggota baru.
                        </p>

                        <button
                            type="button"
                            @click="handleRegenerateCode"
                            :disabled="isRegeneratingCode"
                            class="flex cursor-pointer items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-[#2e2e2e] dark:text-[#f0efea] dark:hover:bg-[#262626]"
                        >
                            <Loader2
                                v-if="isRegeneratingCode"
                                class="h-3.5 w-3.5 animate-spin"
                            />
                            <RefreshCw
                                v-else
                                class="h-3.5 w-3.5 text-[#0b8043] dark:text-[#3fcb7a]"
                            />
                            <span>Buat kode baru</span>
                        </button>
                    </div>

                    <!-- Card 2: Alihkan Kepala Keluarga (Figma 57:11117) -->
                    <div
                        class="space-y-3.5 rounded-3xl border border-gray-100 bg-white p-6 shadow-xs sm:p-8 dark:border-[#2e2e2e] dark:bg-[#1c1c1c]"
                    >
                        <h3
                            class="text-base font-bold text-gray-900 dark:text-[#f0efea]"
                        >
                            Kepala keluarga
                        </h3>

                        <p
                            class="text-xs leading-relaxed text-gray-500 dark:text-[#a3a3a3]"
                        >
                            Alihkan peran Kepala Keluarga ke anggota keluarga
                            lain jika kamu ingin melepaskan hak kelola grup.
                        </p>

                        <button
                            type="button"
                            @click="isTransferModalOpen = true"
                            :disabled="anggota.length <= 1"
                            class="flex cursor-pointer items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs font-bold text-amber-700 transition hover:bg-amber-100 disabled:opacity-40 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-300 dark:hover:bg-amber-900/50"
                        >
                            <Shield class="h-4 w-4" />
                            <span>Alihkan Kepala Keluarga</span>
                        </button>
                        <p
                            v-if="anggota.length <= 1"
                            class="text-[11px] text-gray-400"
                        >
                            Undang anggota lain terlebih dahulu untuk
                            mengalihkan peran.
                        </p>
                    </div>

                    <!-- Card 3: Hapus Grup (Figma 35:9852) -->
                    <div
                        class="space-y-3.5 rounded-3xl border border-red-100 bg-white p-6 shadow-xs sm:p-8 dark:border-red-900/30 dark:bg-[#1c1c1c]"
                    >
                        <h3
                            class="text-base font-bold text-red-600 dark:text-red-400"
                        >
                            Hapus grup
                        </h3>

                        <p
                            class="text-xs leading-relaxed text-gray-500 dark:text-[#a3a3a3]"
                        >
                            Semua anggota akan keluar dari grup. Catatan makan
                            harian masing-masing tetap tersimpan.
                        </p>

                        <button
                            type="button"
                            @click="isDeleteModalOpen = true"
                            class="flex cursor-pointer items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-red-700"
                        >
                            <Trash2 class="h-4 w-4" />
                            <span>Hapus Grup</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <TransferHeadModal
            :show="isTransferModalOpen"
            :members="anggota"
            @close="isTransferModalOpen = false"
        />

        <ConfirmRemoveMemberModal
            :show="!!memberToRemove"
            :member="memberToRemove"
            @close="memberToRemove = null"
        />

        <ConfirmDeleteGroupModal
            :show="isDeleteModalOpen"
            :family-name="keluarga.nama_keluarga"
            @close="isDeleteModalOpen = false"
        />
    </UserLayout>
</template>
