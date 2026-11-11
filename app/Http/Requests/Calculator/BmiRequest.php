<?php

namespace App\Http\Requests\Calculator;

/** Body of the BMI preview and save: height in cm, weight in kg. */
class BmiRequest extends CalculatorRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'tinggi_badan' => ['required', 'numeric', 'between:50,250'],
            'berat_badan' => ['required', 'numeric', 'between:10,300'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'tinggi_badan.required' => 'Isi tinggi badan.',
            'tinggi_badan.numeric' => 'Tinggi badan harus berupa angka.',
            'tinggi_badan.between' => 'Tinggi badan harus antara 50 dan 250 cm.',
            'berat_badan.required' => 'Isi berat badan.',
            'berat_badan.numeric' => 'Berat badan harus berupa angka.',
            'berat_badan.between' => 'Berat badan harus antara 10 dan 300 kg.',
        ];
    }
}
