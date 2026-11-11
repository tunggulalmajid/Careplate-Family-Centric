<script setup lang="ts">
import type { Component } from 'vue';
import { Link } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';

const props = defineProps<{
    exitHref: string;
    progressLabel: string;
    /** Wizard step; omit on result pages (shows "Selesai", full bar). */
    step?: number;
    total?: number;
    /** Speech bubble beside the round icon (wizard steps only). */
    bubble?: string;
    icon?: Component;
}>();

const isDone = () => props.step === undefined || props.total === undefined;
const percent = () =>
    isDone() ? 100 : ((props.step ?? 0) / (props.total ?? 1)) * 100;
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-white text-[#191919] dark:bg-[#1c1c1c] dark:text-[#f0efea]"
    >
        <header class="flex items-center gap-4 px-6 py-5">
            <Link
                :href="exitHref"
                class="inline-flex items-center gap-1 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-[#191919] dark:focus-visible:outline-[#f0efea]"
                ><X class="h-4 w-4" aria-hidden="true" /> Keluar</Link
            >
            <div
                class="h-2 flex-1 overflow-hidden rounded-full bg-[#f4f3f0] dark:bg-[#262626]"
                role="progressbar"
                :aria-label="progressLabel"
                aria-valuemin="1"
                :aria-valuemax="isDone() ? 1 : total"
                :aria-valuenow="isDone() ? 1 : step"
            >
                <div
                    class="h-full rounded-full bg-[#0b8043] transition-all"
                    :style="{ width: `${percent()}%` }"
                />
            </div>
            <span class="text-sm font-semibold">{{
                isDone() ? 'Selesai' : `Langkah ${step} dari ${total}`
            }}</span>
        </header>

        <main
            v-if="$slots.aside"
            class="mx-auto grid w-full max-w-5xl flex-1 content-start gap-10 px-6 py-8 lg:grid-cols-[1fr_20rem]"
        >
            <div class="flex min-w-0 flex-col gap-6">
                <div v-if="bubble" class="flex items-start gap-4">
                    <span
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[#e4f6ea] dark:bg-[#17321f]"
                        ><component
                            :is="icon"
                            v-if="icon"
                            class="h-6 w-6 text-[#0b8043] dark:text-[#3fcb7a]"
                            aria-hidden="true"
                    /></span>
                    <p
                        class="min-w-0 rounded-2xl border-2 border-[#eceae5] px-5 py-3 text-lg dark:border-[#2e2e2e]"
                        role="status"
                    >
                        {{ bubble }}
                    </p>
                </div>
                <slot />
            </div>
            <slot name="aside" />
        </main>
        <main
            v-else
            class="mx-auto flex w-full max-w-2xl flex-1 flex-col items-center px-6 py-8 text-center"
        >
            <slot />
        </main>

        <footer
            class="flex justify-end gap-3 border-t border-[#eceae5] px-6 py-5 dark:border-[#2e2e2e]"
        >
            <slot name="actions" />
        </footer>
    </div>
</template>
