<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:100'],
            'type'            => ['required', 'in:cash,digital,credit,loan'],
            'currency'        => ['required', 'in:ARS,USD'],
            'closing_day'     => ['nullable', 'required_if:type,credit', 'integer', 'min:1', 'max:31'],
            'due_day'         => ['nullable', 'required_if:type,credit', 'integer', 'min:1', 'max:31'],
            'credit_limit'    => ['nullable', 'numeric', 'min:0'],
            'initial_balance' => ['nullable', 'required_if:type,loan', 'numeric', 'min:0.01'],
            'brand'           => ['nullable', 'in:mercadopago,bbva,provincia,visa,mastercard'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'El nombre de la cuenta es obligatorio.',
            'type.required'            => 'Seleccioná el tipo de cuenta.',
            'type.in'                  => 'Tipo de cuenta inválido.',
            'currency.required'        => 'Seleccioná la moneda.',
            'closing_day.required_if'  => 'Para tarjetas de crédito indicá el día de cierre.',
            'due_day.required_if'      => 'Para tarjetas de crédito indicá el día de vencimiento.',
            'closing_day.min'          => 'El día de cierre debe ser entre 1 y 31.',
            'due_day.min'              => 'El día de vencimiento debe ser entre 1 y 31.',
        ];
    }
}
