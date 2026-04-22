<?php

namespace App\Models;

use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'family_group_id',
        'user_id',
        'name',
        'brand',
        'type',
        'currency',
        'closing_day',
        'due_day',
        'credit_limit',
        'initial_balance',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active'       => 'boolean',
            'credit_limit'    => 'decimal:2',
            'initial_balance' => 'decimal:2',
            'closing_day'     => 'integer',
            'due_day'         => 'integer',
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

    public function isCredit(): bool  { return $this->type === 'credit'; }
    public function isCash(): bool    { return $this->type === 'cash'; }
    public function isDigital(): bool { return $this->type === 'digital'; }
    public function isLoan(): bool    { return $this->type === 'loan'; }

    public function isLiability(): bool
    {
        return in_array($this->type, ['credit', 'loan']);
    }

    public function balanceInArs(?ExchangeRate $rate): float
    {
        $bal = $this->balance;
        if ($this->currency === 'ARS' || $rate === null) {
            return $bal;
        }
        return $rate->convert($bal, $this->currency);
    }

    /**
     * Saldo de la cuenta incluyendo transferencias:
     *  - cash/digital : ingresos − gastos + transferencias_entrantes − transferencias_salientes
     *  - credit/loan  : lo mismo pero invertido (positivo = debés)
     *
     * Las transferencias se almacenan como un único registro:
     *   account_id = origen, target_account_id = destino, type = 'transfer'
     */
    public function getBalanceAttribute(): float
    {
        $income        = (float) $this->transactions()->where('type', 'income')->sum('amount');
        $expense       = (float) $this->transactions()->where('type', 'expense')->sum('amount');
        $transferOut   = (float) $this->transactions()->where('type', 'transfer')->sum('amount');
        $transferIn    = (float) DB::table('transactions')
            ->where('target_account_id', $this->id)
            ->where('type', 'transfer')
            ->whereNull('deleted_at')
            ->sum('amount');
        $adjustmentIn  = (float) $this->transactions()->where('type', 'adjustment')->where('adjustment_direction', 'in')->sum('amount');
        $adjustmentOut = (float) $this->transactions()->where('type', 'adjustment')->where('adjustment_direction', 'out')->sum('amount');

        return match ($this->type) {
            'credit' => $expense - $income + $transferOut - $transferIn + $adjustmentOut - $adjustmentIn,
            'loan'   => (float) ($this->initial_balance ?? 0) + $expense - $income + $transferOut - $transferIn + $adjustmentOut - $adjustmentIn,
            default  => $income - $expense + $transferIn - $transferOut + $adjustmentIn - $adjustmentOut,
        };
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
