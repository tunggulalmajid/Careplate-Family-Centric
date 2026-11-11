<script setup lang="ts">
import { computed } from 'vue';
import {
    AlertOctagon,
    AlertTriangle,
    CheckCircle2,
    Info,
} from 'lucide-vue-next';

const props = defineProps<{
    label: string;
    tone: 'baik' | 'waspada' | 'bahaya' | 'info' | string;
}>();

const style = computed(() => {
    switch (props.tone) {
        case 'baik':
            return {
                icon: CheckCircle2,
                cls: 'bg-[#e4f6ea] text-[#0b7a46] dark:bg-[#17321f] dark:text-[#3fcb7a]',
            };
        case 'waspada':
            return {
                icon: AlertTriangle,
                cls: 'bg-[#fff4cc] text-[#7a4b00] dark:bg-amber-950/40 dark:text-amber-300',
            };
        case 'bahaya':
            return {
                icon: AlertOctagon,
                cls: 'bg-[#ffece5] text-[#a52f08] dark:bg-[#3a1e14] dark:text-red-300',
            };
        default:
            return {
                icon: Info,
                cls: 'bg-[#e0f2f1] text-[#0b5f63] dark:bg-teal-950/40 dark:text-teal-300',
            };
    }
});
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
        :class="style.cls"
        ><component :is="style.icon" class="h-3.5 w-3.5" aria-hidden="true" />{{
            label
        }}</span
    >
</template>
