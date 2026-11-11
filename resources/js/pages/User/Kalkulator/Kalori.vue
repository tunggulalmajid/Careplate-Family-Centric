<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Calculator } from 'lucide-vue-next';
import NumberField from '@/Components/Calculator/Wizard/NumberField.vue';
import PreviewCard from '@/Components/Calculator/Wizard/PreviewCard.vue';
import SignalBars from '@/Components/Calculator/Wizard/SignalBars.vue';
import WizardActions from '@/Components/Calculator/Wizard/WizardActions.vue';
import WizardShell from '@/Components/Calculator/Wizard/WizardShell.vue';
import {
    parseDecimal,
    usePreview,
} from '@/Components/Calculator/Wizard/usePreview';
import { idNumber } from '@/lib/foodUi';

const props = defineProps<{
    prefill: {
        jenis_kelamin: 'Laki-laki' | 'Perempuan' | null;
        usia: number | null;
        tinggi: number | null;
        berat: number | null;
        aktivitas: string | null;
    };
    aktivitasOpsi: {
        kode: 'Sedentary' | 'Light' | 'Moderate' | 'Active' | 'Very Active';
        label: string;
        deskripsi: string;
    }[];
}>();

const TOTAL = 5;
const step = ref(1);
const text = (n: number | null) =>
    n === null ? '' : String(n).replace('.', ',');

const form = useForm({
    jenis_kelamin: props.prefill.jenis_kelamin ?? '',
    usia: text(props.prefill.usia),
    tinggi_badan: text(props.prefill.tinggi),
    berat_badan: text(props.prefill.berat),
    aktivitas: props.prefill.aktivitas ?? '',
});
const touched = reactive({
    usia: false,
    tinggi_badan: false,
    berat_badan: false,
});

const FIELD_STEP: Record<string, number> = {
    jenis_kelamin: 1,
    usia: 2,
    tinggi_badan: 3,
    berat_badan: 4,
    aktivitas: 5,
};

const activity = computed(() =>
    props.aktivitasOpsi.find((o) => o.kode === form.aktivitas),
);

// Whatever is valid so far goes to the server; it decides what is computable.
const { data: preview, errors: previewErrors } = usePreview<{
    kkal: number | null;
    alasan: string | null;
}>(route('user.calculator.kalori.preview'), () => {
    const body: Record<string, unknown> = {};
    if (form.jenis_kelamin) body.jenis_kelamin = form.jenis_kelamin;
    if (form.aktivitas) body.aktivitas = form.aktivitas;
    for (const [key, raw] of [
        ['usia', form.usia],
        ['tinggi_badan', form.tinggi_badan],
        ['berat_badan', form.berat_badan],
    ] as const) {
        const n = parseDecimal(raw);
        if (n !== null) body[key] = n;
    }

    return Object.keys(body).length ? body : null;
});

type NumKey = 'usia' | 'tinggi_badan' | 'berat_badan';
const fieldError = (k: NumKey) =>
    form.errors[k] ||
    (touched[k] ? previewErrors.value[k] : undefined) ||
    (touched[k] && form[k].trim() !== '' && parseDecimal(form[k]) === null
        ? 'Tulis angka saja, misalnya 54,5.'
        : undefined);

const stepOk = computed(() => {
    switch (step.value) {
        case 1:
            return form.jenis_kelamin !== '';
        case 2:
            return (parseDecimal(form.usia) ?? 0) > 0;
        case 3:
            return (parseDecimal(form.tinggi_badan) ?? 0) > 0;
        case 4:
            return (parseDecimal(form.berat_badan) ?? 0) > 0;
        default:
            return form.aktivitas !== '';
    }
});

const bubble = computed(
    () =>
        [
            '',
            'Pilih jenis kelamin yang dipakai untuk rumus kalori.',
            'Berapa usiamu sekarang?',
            'Berapa tinggi badanmu?',
            'Berapa berat badanmu sekarang?',
            'Seberapa aktif kamu sehari-hari?',
        ][step.value],
);

const summary = computed(() =>
    [
        ['Jenis kelamin', form.jenis_kelamin],
        ['Usia', parseDecimal(form.usia) !== null ? `${form.usia} tahun` : ''],
        [
            'Tinggi',
            parseDecimal(form.tinggi_badan) !== null
                ? `${form.tinggi_badan} cm`
                : '',
        ],
        [
            'Berat',
            parseDecimal(form.berat_badan) !== null
                ? `${form.berat_badan} kg`
                : '',
        ],
        ['Aktivitas', activity.value?.label ?? ''],
    ].filter((r) => r[1] !== ''),
);

function submit() {
    if (!stepOk.value) return;
    if (step.value < TOTAL) {
        step.value++;

        return;
    }
    form.transform((d) => ({
        jenis_kelamin: d.jenis_kelamin,
        usia: parseDecimal(d.usia),
        tinggi_badan: parseDecimal(d.tinggi_badan),
        berat_badan: parseDecimal(d.berat_badan),
        aktivitas: d.aktivitas,
    })).post(route('user.calculator.kalori.store'), {
        onError: (errors) => {
            step.value = Math.min(
                ...Object.keys(errors).map((k) => FIELD_STEP[k] ?? TOTAL),
            );
        },
    });
}

const focus =
    'has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-[#191919] dark:has-[:focus-visible]:outline-[#f0efea]';
const chosen =
    'border-[#0b8043] bg-[#e4f6ea] dark:border-[#3fcb7a] dark:bg-[#17321f]';
const idle =
    'border-[#eceae5] hover:bg-[#f4f3f0] dark:border-[#2e2e2e] dark:hover:bg-[#262626]';
</script>

<template>
    <Head title="Hitung Kebutuhan Kalori" />
    <WizardShell
        :exit-href="route('user.calculator')"
        progress-label="Kemajuan hitung kalori"
        :step="step"
        :total="TOTAL"
        :bubble="bubble"
        :icon="Calculator"
    >
        <form
            id="kalori-form"
            class="flex flex-col gap-4"
            @submit.prevent="submit"
        >
            <fieldset v-if="step === 1">
                <legend class="mb-1.5 text-sm font-semibold">
                    Jenis kelamin
                </legend>
                <div class="grid grid-cols-2 gap-3">
                    <label
                        v-for="o in ['Laki-laki', 'Perempuan']"
                        :key="o"
                        class="flex cursor-pointer items-center justify-center rounded-2xl border-2 px-4 py-5 text-lg font-semibold"
                        :class="[
                            focus,
                            form.jenis_kelamin === o ? chosen : idle,
                        ]"
                        ><input
                            v-model="form.jenis_kelamin"
                            type="radio"
                            name="jenis_kelamin"
                            :value="o"
                            class="sr-only"
                        />{{ o }}</label
                    >
                </div>
                <p
                    v-if="form.errors.jenis_kelamin"
                    class="mt-1.5 text-sm text-[#a52f08] dark:text-red-300"
                    role="alert"
                >
                    {{ form.errors.jenis_kelamin }}
                </p>
            </fieldset>

            <NumberField
                v-else-if="step === 2"
                id="kal-usia"
                v-model="form.usia"
                label="Usia"
                unit="tahun"
                hint="Hasil kebutuhan kalori untuk usia 18 tahun ke atas."
                :error="fieldError('usia')"
                @blur="touched.usia = true"
            />
            <NumberField
                v-else-if="step === 3"
                id="kal-tinggi"
                v-model="form.tinggi_badan"
                label="Tinggi badan"
                unit="cm"
                :error="fieldError('tinggi_badan')"
                @blur="touched.tinggi_badan = true"
            />
            <NumberField
                v-else-if="step === 4"
                id="kal-berat"
                v-model="form.berat_badan"
                label="Berat badan"
                unit="kg"
                hint="Timbang pagi hari sebelum makan agar hasil konsisten."
                :error="fieldError('berat_badan')"
                @blur="touched.berat_badan = true"
            />

            <fieldset v-else class="flex flex-col gap-3">
                <legend class="mb-1.5 text-sm font-semibold">
                    Tingkat aktivitas
                </legend>
                <label
                    v-for="(o, i) in aktivitasOpsi"
                    :key="o.kode"
                    class="flex cursor-pointer flex-wrap items-center gap-x-4 gap-y-1 rounded-2xl border-2 px-5 py-4"
                    :class="[focus, form.aktivitas === o.kode ? chosen : idle]"
                    ><input
                        v-model="form.aktivitas"
                        type="radio"
                        name="aktivitas"
                        :value="o.kode"
                        class="sr-only"
                    /><SignalBars
                        :level="Math.min(3, Math.ceil(((i + 1) * 3) / 5))"
                    />
                    <span class="text-lg font-semibold">{{ o.label }}</span>
                    <span
                        class="w-full text-sm text-[#5e5e5e] sm:ml-auto sm:w-auto dark:text-[#a3a3a3]"
                        >{{ o.deskripsi }}</span
                    ></label
                >
                <p
                    v-if="form.errors.aktivitas"
                    class="text-sm text-[#a52f08] dark:text-red-300"
                    role="alert"
                >
                    {{ form.errors.aktivitas }}
                </p>
            </fieldset>
        </form>

        <template #aside>
            <PreviewCard title="Perkiraan sejauh ini">
                <dl
                    v-if="summary.length"
                    class="flex flex-col gap-2 border-b border-[#eceae5] pb-4 text-sm dark:border-[#2e2e2e]"
                >
                    <div
                        v-for="r in summary"
                        :key="r[0]"
                        class="flex justify-between gap-4"
                    >
                        <dt class="text-[#5e5e5e] dark:text-[#a3a3a3]">
                            {{ r[0] }}
                        </dt>
                        <dd class="text-right font-semibold">{{ r[1] }}</dd>
                    </div>
                </dl>
                <p class="mt-4 text-sm font-semibold">Kebutuhan harian</p>
                <p
                    v-if="preview?.kkal"
                    class="mt-1 font-display text-3xl font-bold"
                >
                    {{ idNumber.format(preview.kkal) }}
                    <span
                        class="text-sm font-semibold text-[#5e5e5e] dark:text-[#a3a3a3]"
                        >kkal</span
                    >
                </p>
                <p
                    v-else
                    class="mt-1 text-base text-[#5e5e5e] dark:text-[#a3a3a3]"
                >
                    {{ preview?.alasan ?? 'Belum bisa dihitung' }}
                </p>
                <p
                    v-if="preview?.kkal"
                    class="mt-2 text-xs text-[#5e5e5e] dark:text-[#a3a3a3]"
                >
                    Dihitung dari rumus standar dan tingkat aktivitasmu.
                </p>
            </PreviewCard>
        </template>

        <template #actions>
            <WizardActions
                form-id="kalori-form"
                :show-back="step > 1"
                :next-label="
                    step < TOTAL
                        ? 'Lanjut'
                        : form.processing
                          ? 'Menghitung…'
                          : 'Hitung Kebutuhan Kalori'
                "
                :disabled="!stepOk || form.processing"
                @back="step--"
            />
        </template>
    </WizardShell>
</template>
