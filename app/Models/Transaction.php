<?php

namespace App\Models;

use App\Services\ExchangeRateResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'family_group_id',
        'user_id',
        'account_id',
        'category_id',
        'type',
        'income_source',
        'adjustment_direction',
        'amount',
        'currency',
        'date',
        'description',
        'has_installments',
        'installments_count',
        'installment_amount',
        'target_account_id',
        'notes',
        'recurring_expense_id',
        'is_card_payment',
        'is_avoidable',
        'balance_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'has_installments' => 'boolean',
            'is_card_payment' => 'boolean',
            'is_avoidable' => 'boolean',
            'amount' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'balance_snapshot' => 'decimal:2',
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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function targetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'target_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class)->orderBy('installment_number');
    }

    public function recurringExpense(): BelongsTo
    {
        return $this->belongsTo(RecurringExpense::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    public function isTransfer(): bool
    {
        return $this->type === 'transfer';
    }

    public function isAdjustment(): bool
    {
        return $this->type === 'adjustment';
    }

    public function amountInArs(?ExchangeRate $rate): float
    {
        $amt = (float) $this->amount;
        if ($this->currency === 'ARS' || $rate === null) {
            return $amt;
        }

        return $rate->convert($amt, $this->currency);
    }

    /**
     * Monto en ARS usando la tasa vigente a la fecha de la transacción.
     *
     * A diferencia de amountInArs(), que aplica una tasa fija (la más
     * reciente), este método valoriza cada operación con la cotización
     * vigente a su propia fecha, evitando distorsiones históricas.
     */
    public function amountInArsOn(ExchangeRateResolver $resolver): float
    {
        return $resolver->convert((float) $this->amount, $this->currency, $this->date);
    }
}
