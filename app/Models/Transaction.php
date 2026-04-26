<?php

namespace App\Models;

use App\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected function casts(): array
    {
        return [
            'date'              => 'date',
            'has_installments'  => 'boolean',
            'amount'            => 'decimal:2',
            'installment_amount'=> 'decimal:2',
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
}
