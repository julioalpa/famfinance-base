<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayMonthlyPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $linking = filled($this->input('existing_transaction_id'));

        return [
            'existing_transaction_id' => ['nullable', 'integer', 'exists:transactions,id'],
            'amount' => [$linking ? 'nullable' : 'required', 'numeric', 'min:0.01'],
            'date'   => [$linking ? 'nullable' : 'required', 'date'],
            'notes'  => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'El monto es obligatorio.',
            'amount.numeric'  => 'El monto debe ser un número.',
            'amount.min'      => 'El monto debe ser mayor a cero.',
            'date.required'   => 'La fecha es obligatoria.',
            'date.date'       => 'La fecha no es válida.',
            'existing_transaction_id.exists' => 'El movimiento seleccionado no existe.',
        ];
    }
}
