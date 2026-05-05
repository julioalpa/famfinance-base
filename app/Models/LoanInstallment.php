<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanInstallment extends Model
{
    protected $fillable = [
        'account_id',
        'family_group_id',
        'installment_number',
        'due_date',
        'amount',
        'currency',
        'is_paid',
        'paid_at',
        'transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_paid'  => 'boolean',
            'paid_at'  => 'datetime',
            'amount'   => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function isOverdue(): bool
    {
        return ! $this->is_paid && $this->due_date->isPast();
    }
}
