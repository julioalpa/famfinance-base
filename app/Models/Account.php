<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'family_group_id',
        'user_id',
        'name',
        'type',
        'currency',
        'closing_day',
        'due_day',
        'credit_limit',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'credit_limit' => 'decimal:2',
            'closing_day'  => 'integer',
            'due_day'      => 'integer',
        ];
    }

    public function familyGroup(): BelongsTo
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    public function isCredit(): bool
    {
        return $this->type === 'credit';
    }

    public function isCash(): bool
    {
        return $this->type === 'cash';
    }

    public function isDigital(): bool
    {
        return $this->type === 'digital';
    }

    /**
     * Calcula el balance de la cuenta sumando ingresos y restando gastos.
     * Para cuentas de crédito devuelve el total consumido (deuda).
     */
    public function getBalanceAttribute(): float
    {
        $income  = $this->transactions()->where('type', 'income')->sum('amount');
        $expense = $this->transactions()->where('type', 'expense')->sum('amount');

        return $this->isCredit()
            ? $expense - $income   // crédito: saldo deudor
            : $income - $expense;  // cash/digital: saldo disponible
    }

    /**
     * Cuotas pendientes del próximo mes calendario para esta cuenta de crédito.
     */
    public function getUpcomingInstallments(int $month, int $year): \Illuminate\Database\Eloquent\Collection
    {
        return $this->installments()
            ->whereYear('due_date', $year)
            ->whereMonth('due_date', $month)
            ->where('is_paid', false)
            ->with('transaction.category')
            ->get();
    }
}
