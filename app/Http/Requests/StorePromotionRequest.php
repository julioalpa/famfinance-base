<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_item_id'      => ['nullable', 'exists:payment_items,id'],
            'name'                 => ['required', 'string', 'max:255'],
            'provider'             => ['nullable', 'string', 'max:255'],
            'discount_type'        => ['required', 'in:percentage,fixed_amount'],
            'discount_value'       => ['required', 'numeric', 'min:0.01'],
            'currency'             => ['required', 'in:ARS,USD'],
            'original_amount'      => ['nullable', 'numeric', 'min:0.01'],
            'starts_at'            => ['nullable', 'date'],
            'expires_at'           => ['required', 'date', 'after_or_equal:today'],
            'reminder_days_before' => ['required', 'integer', 'min:1', 'max:365'],
            'is_active'            => ['boolean'],
            'notes'                => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                 => 'El nombre de la promoción es obligatorio.',
            'name.max'                      => 'El nombre no puede superar 255 caracteres.',
            'discount_type.required'        => 'Seleccioná el tipo de descuento.',
            'discount_type.in'              => 'El tipo debe ser porcentaje o monto fijo.',
            'discount_value.required'       => 'Ingresá el valor del descuento.',
            'discount_value.numeric'        => 'El valor debe ser un número.',
            'discount_value.min'            => 'El valor debe ser mayor a cero.',
            'currency.required'             => 'Seleccioná la moneda.',
            'currency.in'                   => 'La moneda debe ser ARS o USD.',
            'original_amount.numeric'       => 'El monto original debe ser un número.',
            'original_amount.min'           => 'El monto original debe ser mayor a cero.',
            'expires_at.required'           => 'La fecha de vencimiento es obligatoria.',
            'expires_at.date'               => 'La fecha de vencimiento no es válida.',
            'expires_at.after_or_equal'     => 'La fecha de vencimiento no puede ser en el pasado.',
            'reminder_days_before.required' => 'Indicá cuántos días antes querés el recordatorio.',
            'reminder_days_before.integer'  => 'Los días deben ser un número entero.',
            'reminder_days_before.min'      => 'El mínimo es 1 día antes.',
            'reminder_days_before.max'      => 'El máximo es 365 días antes.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
