<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Scale } from 'lucide-vue-next';
import CategoryPill from '@/Components/Calculator/Wizard/CategoryPill.vue';
import NumberField from '@/Components/Calculator/Wizard/NumberField.vue';
import PreviewCard from '@/Components/Calculator/Wizard/PreviewCard.vue';
import WizardActions from '@/Components/Calculator/Wizard/WizardActions.vue';
import WizardShell from '@/Components/Calculator/Wizard/WizardShell.vue';
import {
    parseDecimal,
    usePreview,
} from '@/Components/Calculator/Wizard/usePreview';
import { bmiNumber } from '@/lib/calculatorUi';

interface Kategori {
    kode: string;
    label: string;
    tone: 'baik' | 'waspada' | 'bahaya' | 'info';
}

const props = defineProps<{
    prefill: { tinggi: number | null; berat: number | null };
    usia: number | null;
}>();

const TOTAL = 2;
const step = ref(1);
const text = (n: number | null) =>
    n === null ? '' : String(n).replace('.', ',');

const form = useForm({
    tinggi_badan: text(props.prefill.tinggi),
    berat_badan: text(props.prefill.berat),
});
const touched = reactive({ tinggi_badan: false, berat_badan: false });

const tinggi = computed(() => parseDecimal(form.tinggi_badan));
const berat = computed(() => parseDecimal(form.berat_badan));

const { data: preview, errors: previewErrors } = usePreview<{
    skor: number;
    kategori: Kategori | null;
    anak: boolean;
}>(route('user.calculator.bmi.preview'), () =>
    tinggi.value && berat.value
        ? { tinggi_badan: tinggi.value, berat_badan: berat.value }
        : null,
);

const fieldError = (k: 'tinggi_badan' | 'berat_badan') =>
    form.errors[k] ||
    (touched[k] ? previewErrors.value[k] : undefined) ||
    (touched[k] && form[k].trim() !== '' && parseDecimal(form[k]) === null
        ? 'Tulis angka saja, misalnya 54,5.'
        : undefined);

const stepOk = computed(() =>
    step.value === 1 ? (tinggi.value ?? 0) > 0 : (berat.value ?? 0) > 0,
);

const bubble = computed(() =>
    step.value === 1
        ? 'Berapa tinggi badanmu sekarang?'
        : 'Masukkan berat badan terbarumu.',
);

function submit() {
    if (!stepOk.value) return;
    if (step.value < TOTAL) {
        step.value++;

        return;
    }
    form.transform((d) => ({
        tinggi_badan: parseDecimal(d.tinggi_badan),
        berat_badan: parseDecimal(d.berat_badan),
    })).post(route('user.calculator.bmi.store'), {
        onError: (errors) => {
            step.value = errors.tinggi_badan ? 1 : 2;
        },
    });
}
</script>

<template>
    <Head title="Hitung BMI" />
    <WizardShell
        :exit-href="route('user.calculator')"
        progress-label="Kemajuan hitung BMI"
        :step="step"
        :total="TOTAL"
        :bubble="bubble"
        :icon="Scale"
    >
        <form
            id="bmi-form"
            class="flex flex-col gap-4"
            @submit.prevent="submit"
        >
            <NumberField
                v-if="step === 1"
                id="bmi-tinggi"
                v-model="form.tinggi_badan"
                label="Tinggi badan"
                unit="cm"
                :error="fieldError('tinggi_badan')"
                @blur="touched.tinggi_badan = true"
            />
            <template v-else>
                <NumberField
                    id="bmi-berat"
                    v-model="form.berat_badan"
                    label="Berat badan"
                    unit="kg"
                    :error="fieldError('berat_badan')"
                    hint="Timbang pagi hari sebelum makan agar hasil konsisten dari bulan ke bulan."
                    @blur="touched.berat_badan = true"
                />
            </template>
        </form>

        <template #aside>
            <PreviewCard title="Pratinjau">
                <p v-if="preview" class="font-display text-3xl font-bold">
                    {{ bmiNumber.format(preview.skor) }}
                    <span
                        class="text-sm font-semibold text-[#5e5e5e] dark:text-[#a3a3a3]"
                        >BMI</span
                    >
                </p>
                <p v-else class="text-base text-[#5e5e5e] dark:text-[#a3a3a3]">
                    Belum bisa dihitung
                </p>
                <div v-if="preview" class="mt-3">
                    <CategoryPill
                        v-if="preview.anak || !preview.kategori"
                        label="Dinilai menurut usia"
                        tone="info"
                    />
                    <CategoryPill
                        v-else
                        :label="preview.kategori.label"
                        :tone="preview.kategori.tone"
                    />
                </div>
                <p class="mt-3 text-xs text-[#5e5e5e] dark:text-[#a3a3a3]">
                    {{
                        preview?.anak
                            ? 'Usia di bawah 18 tahun dinilai menurut usia, bukan kategori Kemenkes.'
                            : 'Klasifikasi Kemenkes untuk usia 18 tahun ke atas.'
                    }}
                </p>
            </PreviewCard>
        </template>

        <template #actions>
            <WizardActions
                form-id="bmi-form"
                :show-back="step > 1"
                :next-label="
                    step < TOTAL
                        ? 'Lanjut'
                        : form.processing
                          ? 'Menghitung…'
                          : 'Hitung BMI'
                "
                :disabled="!stepOk || form.processing"
                @back="step--"
            />
        </template>
    </WizardShell>
</template>
