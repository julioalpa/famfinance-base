<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El middleware se encarga del grupo
    }

    public function rules(): array
    {
        return [
            'account_id'         => ['required', 'integer', 'exists:accounts,id'],
            'category_id'        => ['nullable', 'integer', 'exists:categories,id'],
            'type'               => ['required', 'in:expense,income,transfer'],
            'income_source'      => ['nullable', 'required_if:type,income', 'in:salary,credit,cash,loan,other'],
            'amount'             => ['required', 'numeric', 'min:0.01'],
            'currency'           => ['required', 'in:ARS,USD'],
            'date'               => ['required', 'date'],
            'description'        => ['nullable', 'string', 'max:255'],
            'has_installments'   => ['boolean'],
            'installments_count' => ['nullable', 'required_if:has_installments,true', 'integer', 'min:2', 'max:120'],
            'target_account_id'  => ['nullable', 'required_if:type,transfer', 'integer', 'exists:accounts,id', 'different:account_id'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'payment_item_id'    => ['nullable', 'integer', 'exists:payment_items,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.required'         => 'Seleccioná una cuenta.',
            'type.required'               => 'Indicá si es gasto, ingreso o transferencia.',
            'type.in'                     => 'Tipo inválido.',
            'amount.required'             => 'Ingresá el monto.',
            'amount.min'                  => 'El monto debe ser mayor a cero.',
            'currency.in'                 => 'Moneda inválida. Usá ARS o USD.',
            'date.required'               => 'La fecha es obligatoria.',
            'installments_count.required_if' => 'Indicá la cantidad de cuotas.',
            'installments_count.min'      => 'Mínimo 2 cuotas.',
            'installments_count.max'      => 'Máximo 120 cuotas.',
            'target_account_id.required_if'  => 'Seleccioná la cuenta destino para la transferencia.',
            'target_account_id.different' => 'La cuenta destino no puede ser la misma que la origen.',
            'income_source.required_if'   => 'Indicá el origen del ingreso.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalizar has_installments
        $this->merge([
            'has_installments' => $this->boolean('has_installments'),
        ]);

        // Limpiar campos de cuotas si no aplica
        if (! $this->has_installments || $this->type !== 'expense') {
            $this->merge([
                'has_installments'   => false,
                'installments_count' => null,
            ]);
        }
    }
}
