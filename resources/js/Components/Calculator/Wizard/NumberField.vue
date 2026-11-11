<script setup lang="ts">
const props = defineProps<{
    id: string;
    label: string;
    unit: string;
    error?: string;
    hint?: string;
}>();

const model = defineModel<string>({ required: true });
const emit = defineEmits<{ blur: [] }>();
</script>

<template>
    <div>
        <label :for="props.id" class="mb-1.5 block text-sm font-semibold">{{
            props.label
        }}</label>
        <div class="relative">
            <input
                :id="props.id"
                v-model="model"
                type="text"
                inputmode="decimal"
                autocomplete="off"
                :aria-invalid="props.error ? 'true' : undefined"
                :aria-describedby="
                    props.error
                        ? `${props.id}-error`
                        : props.hint
                          ? `${props.id}-hint`
                          : undefined
                "
                class="h-14 w-full rounded-2xl border-2 border-[#191919] bg-white px-4 pr-14 text-xl font-semibold text-[#191919] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b8043] aria-[invalid=true]:border-[#b42318] dark:border-[#f0efea] dark:bg-[#1c1c1c] dark:text-[#f0efea] dark:aria-[invalid=true]:border-red-300"
                @blur="emit('blur')"
            />
            <span
                class="pointer-events-none absolute top-1/2 right-4 -translate-y-1/2 text-sm font-semibold text-[#5e5e5e] dark:text-[#a3a3a3]"
                >{{ props.unit }}</span
            >
        </div>
        <p
            v-if="props.error"
            :id="`${props.id}-error`"
            class="mt-1.5 text-sm text-[#a52f08] dark:text-red-300"
            role="alert"
        >
            {{ props.error }}
        </p>
        <p
            v-else-if="props.hint"
            :id="`${props.id}-hint`"
            class="mt-1.5 text-sm text-[#5e5e5e] dark:text-[#a3a3a3]"
        >
            {{ props.hint }}
        </p>
    </div>
</template>
