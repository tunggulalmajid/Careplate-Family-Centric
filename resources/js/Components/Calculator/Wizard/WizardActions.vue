<script setup lang="ts">
defineProps<{
    formId: string;
    showBack: boolean;
    nextLabel: string;
    disabled: boolean;
}>();

defineEmits<{ back: [] }>();
</script>

<template>
    <button
        v-if="showBack"
        type="button"
        class="h-11 rounded-full border-2 border-[#191919] px-6 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#191919] dark:border-[#f0efea] dark:focus-visible:outline-[#f0efea]"
        @click="$emit('back')"
    >
        Kembali
    </button>
    <button
        type="submit"
        :form="formId"
        :disabled="disabled"
        class="h-11 rounded-full bg-[#0b8043] px-6 text-sm font-semibold text-white hover:bg-[#086637] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#191919] disabled:cursor-not-allowed disabled:opacity-50 dark:focus-visible:outline-[#f0efea]"
    >
        {{ nextLabel }}
    </button>
</template>
