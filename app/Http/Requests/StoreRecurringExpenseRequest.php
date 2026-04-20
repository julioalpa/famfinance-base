<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringExpenseRequest extends FormRequest
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
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'currency'     => ['required', 'in:ARS,USD'],
            'day_of_month' => ['required', 'integer', 'min:1', 'max:31'],
            'is_active'    => ['boolean'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.required'   => 'Seleccioná una cuenta.',
            'account_id.exists'     => 'La cuenta seleccionada no existe.',
            'description.required'  => 'El nombre del débito es obligatorio.',
            'description.max'       => 'El nombre no puede superar 255 caracteres.',
            'amount.required'       => 'El monto es obligatorio.',
            'amount.numeric'        => 'El monto debe ser un número.',
            'amount.min'            => 'El monto debe ser mayor a cero.',
            'currency.required'     => 'Seleccioná la moneda.',
            'currency.in'           => 'La moneda debe ser ARS o USD.',
            'day_of_month.required' => 'El día de ejecución es obligatorio.',
            'day_of_month.integer'  => 'El día debe ser un número entero.',
            'day_of_month.min'      => 'El día debe ser entre 1 y 31.',
            'day_of_month.max'      => 'El día debe ser entre 1 y 31.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
