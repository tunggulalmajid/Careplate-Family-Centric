<script setup lang="ts">
// Three-bar activity signal; `level` is how many bars are filled (1 to 3).
defineProps<{ level: number }>();
</script>

<template>
    <span class="flex h-5 items-end gap-0.5" aria-hidden="true">
        <span
            v-for="n in 3"
            :key="n"
            class="w-1.5 rounded-sm"
            :class="[
                n === 1 ? 'h-2' : n === 2 ? 'h-3.5' : 'h-5',
                n <= level
                    ? 'bg-[#0b8043] dark:bg-[#3fcb7a]'
                    : 'bg-[#d6d3cc] dark:bg-[#3d3d3d]',
            ]"
        />
    </span>
</template>
