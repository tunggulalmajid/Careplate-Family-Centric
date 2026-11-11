<script setup lang="ts">
defineProps<{ title: string }>();
</script>

<template>
    <aside
        class="h-fit rounded-3xl border border-[#eceae5] p-6 dark:border-[#2e2e2e]"
        :aria-label="title"
    >
        <h2 class="font-display text-lg font-bold">{{ title }}</h2>
        <div class="mt-3" aria-live="polite">
            <slot />
        </div>
    </aside>
</template>
