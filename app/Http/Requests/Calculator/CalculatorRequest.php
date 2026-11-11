<?php

namespace App\Http\Requests\Calculator;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base of the calculator bodies. Indonesian users type a decimal comma ("54,5"); it is turned into a point before
 * validation so that "54,5" and "54.5" mean the same.
 */
abstract class CalculatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $fixed = [];
        foreach (['tinggi_badan', 'berat_badan'] as $key) {
            $value = $this->input($key);
            if (is_string($value)) {
                $fixed[$key] = str_replace(',', '.', trim($value));
            }
        }
        $this->merge($fixed);
    }
}
