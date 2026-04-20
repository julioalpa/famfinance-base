<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rate'  => ['required', 'numeric', 'min:0.0001'],
            'date'  => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'rate.required' => 'Ingresá el tipo de cambio.',
            'rate.min'      => 'El tipo de cambio debe ser mayor a cero.',
            'date.required' => 'La fecha es obligatoria.',
        ];
    }
}
