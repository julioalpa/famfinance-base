<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id'   => ['required', 'exists:accounts,id'],
            'category_id'  => ['nullable', 'exists:categories,id'],
            'description'  => ['required', 'string', 'max:255'],
            'currency'     => ['required', 'in:ARS,USD'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'is_active'    => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.required'  => 'Seleccioná una cuenta.',
            'account_id.exists'    => 'La cuenta seleccionada no existe.',
            'description.required' => 'El nombre del pago es obligatorio.',
            'description.max'      => 'El nombre no puede superar 255 caracteres.',
            'currency.required'    => 'Seleccioná la moneda.',
            'currency.in'          => 'La moneda debe ser ARS o USD.',
            'day_of_month.integer' => 'El día debe ser un número entero.',
            'day_of_month.min'     => 'El día debe ser entre 1 y 31.',
            'day_of_month.max'     => 'El día debe ser entre 1 y 31.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
