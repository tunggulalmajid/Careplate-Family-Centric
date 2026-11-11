<?php

namespace App\Http\Requests\Calculator;

/** Live preview while the wizard is filled: never fails, the controller says "not yet" for incomplete data. */
class KaloriPreviewRequest extends CalculatorRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
