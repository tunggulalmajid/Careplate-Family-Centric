<?php

namespace App\Http\Requests\Calculator;

use App\Services\Calculator\BodyCalculator;
use Illuminate\Validation\Rule;

/** Body of the calorie save. Under 18 years is refused with the reason, as the design says. */
class KaloriRequest extends CalculatorRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'usia' => ['required', 'integer', 'min:18', 'max:120'],
            'tinggi_badan' => ['required', 'numeric', 'between:50,250'],
            'berat_badan' => ['required', 'numeric', 'between:10,300'],
            'aktivitas' => ['required', Rule::in(array_column(BodyCalculator::activityLevels(), 'kode'))],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'jenis_kelamin.required' => 'Pilih jenis kelamin.',
            'jenis_kelamin.in' => 'Pilih Laki-laki atau Perempuan.',
            'usia.required' => 'Isi usia.',
            'usia.integer' => 'Usia harus berupa bilangan bulat.',
            'usia.min' => 'Target kalori tidak ditampilkan untuk anggota di bawah 18 tahun.',
            'usia.max' => 'Usia paling tinggi 120 tahun.',
            'tinggi_badan.required' => 'Isi tinggi badan.',
            'tinggi_badan.numeric' => 'Tinggi badan harus berupa angka.',
            'tinggi_badan.between' => 'Tinggi badan harus antara 50 dan 250 cm.',
            'berat_badan.required' => 'Isi berat badan.',
            'berat_badan.numeric' => 'Berat badan harus berupa angka.',
            'berat_badan.between' => 'Berat badan harus antara 10 dan 300 kg.',
            'aktivitas.required' => 'Pilih tingkat aktivitas.',
            'aktivitas.in' => 'Tingkat aktivitas tidak dikenal.',
        ];
    }
}
